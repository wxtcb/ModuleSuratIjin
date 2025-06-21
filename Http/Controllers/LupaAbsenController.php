<?php

namespace Modules\SuratIjin\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Cuti\Services\AtasanService;
use Modules\Pengaturan\Entities\Anggota;
use Modules\Pengaturan\Entities\Pegawai;
use Modules\Pengaturan\Entities\Pejabat;
use Modules\Pengaturan\Entities\TimKerja;
use Modules\SuratIjin\Entities\LupaAbsen;
use Modules\SuratIjin\Entities\LupaAbsenLogs;
use Illuminate\Support\Str;
use Modules\Cuti\Services\FonnteService;
use Modules\RekapKehadiran\Entities\KehadiranI;
use Modules\Setting\Entities\Libur;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LupaAbsenController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(AtasanService $atasanService)
    {
        $user = auth()->user();
        $role = $user->role_aktif;
        $username = $user->username;

        $pegawai = Pegawai::where('username', $username)->first();
        $pegawai_id = optional($pegawai)->id;
        $pejabat = Pejabat::where('pegawai_id', $pegawai_id)->first();
        $pejabat_id = optional($pejabat)->id;

        $lupa_absen = null;
        $lupa_pribadi = null;
        $lupa_anggota = null;

        if ($role === 'admin') {
            $lupa_absen = LupaAbsen::with('pegawai')->orderBy('tanggal', 'desc')->get();
        } elseif ($role === 'kajur' || $role === 'kaunit') {
            $lupa_anggota = LupaAbsen::where('pejabat_id', $pejabat_id)
                ->whereHas('logs', function ($query) {
                    $query->where('status', 'Telah diteruskan ke atasan');
                })->with('pegawai')->orderBy('tanggal', 'desc')->get();

            $lupa_pribadi = LupaAbsen::where('pegawai_id', $pegawai_id)
                ->with('pegawai')->orderBy('tanggal', 'desc')->get();
        } elseif ($role === 'dosen' || $role === 'pegawai') {
            $lupa_pribadi = LupaAbsen::where('pegawai_id', $pegawai_id)
                ->with('pegawai')->orderBy('tanggal', 'desc')->get();
        }

        foreach ([$lupa_absen, $lupa_anggota, $lupa_pribadi] as &$collection) {
            $collection = $collection ? $collection->map(function ($item) use ($username, $atasanService) {
                $status = strtolower($item->status ?? '');
                $item->badgeClass = match ($status) {
                    'diproses' => 'info',
                    'disetujui' => 'success',
                    'dibatalkan' => 'danger',
                    default => 'secondary',
                };

                $atasan = $atasanService->getAtasanPegawai($item->pegawai_id);
                $item->isKetuaTim = $atasan && $atasan->pegawai->username === $username;

                return $item;
            }) : collect();
        }

        return view('suratijin::lupaabsen.index', compact(
            'lupa_absen',
            'lupa_pribadi',
            'lupa_anggota',
            'pejabat_id',
            'user'
        ));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $pegawai = Pegawai::where('username', auth()->user()->username)->first();
        $anggota = Anggota::where('pegawai_id', $pegawai->id)->first();
        $tim = TimKerja::find($anggota->tim_kerja_id ?? null);

        $atasanService = new AtasanService();
        $ketua = $atasanService->getAtasanPegawai($pegawai->id);

        // Ambil hari libur
        $today = Carbon::today();
        $libur = Libur::pluck('tanggal')->map(fn($tgl) => Carbon::parse($tgl)->format('Y-m-d'))->toArray();

        $tanggalMundur = $this->hitungHariKerja($today, 5, 'mundur', $libur);
        $tanggalMaju = $this->hitungHariKerja($today, 5, 'maju', $libur);

        $tanggalMin = $tanggalMundur[4]->format('Y-m-d');
        $tanggalMax = $tanggalMaju[0]->format('Y-m-d');

        return view('suratijin::lupaabsen.create', compact(
            'pegawai',
            'tim',
            'anggota',
            'ketua',
            'tanggalMin',
            'tanggalMax'
        ));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $username_login = auth()->user()->username;
        $username_pegawai = Pegawai::where('username', $username_login)->first()->id;
        $pegawai = Pegawai::where('username', $username_login)->first();
        $pegawai_id = $pegawai->id;

        // Validasi inputan
        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'pejabat_id' => 'required|exists:pejabats,id',
            'jenis_ijin' => 'required',
            'hari' => 'required',
            'tanggal' => 'required|date',
            'alasan' => 'required',
        ]);

        // Generate access token
        $uuid = Str::uuid()->toString();
        $access_token = substr($uuid, 0, 12);

        // ✅ Tambahkan validasi jumlah surat izin disetujui bulan ini
        $suratBulanIni = LupaAbsen::where('pegawai_id', $pegawai_id)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->where('status', 'Disetujui') // hanya hitung yang disetujui
            ->count();

        if ($suratBulanIni >= 2) {
            return back()->withErrors([
                'limit' => 'Surat izin disetujui Anda sudah mencapai batas maksimal 2 kali dalam bulan ini.'
            ])->withInput();
        }

        // Mulai transaksi database
        DB::beginTransaction();
        try {
            $lupa_absen = LupaAbsen::create([
                'pegawai_id' => $request->pegawai_id,
                'pejabat_id' => $request->pejabat_id,
                'jenis_ijin' => $request->jenis_ijin,
                'hari' => $request->hari,
                'tanggal' => $request->tanggal,
                'alasan' => $request->alasan,
                'access_token' => $access_token,
                'tim_kerja_id' => $request->tim_kerja_id,
                'status' => 'Diajukan', // Status awal pengajuan
            ]);

            // Simpan log pengajuan ke tabel lupa_absen_logs
            LupaAbsenLogs::create([
                'lupa_absen_id' => $lupa_absen->id,
                'status' => 'Diajukan',
                'updated_by' => $username_pegawai,
            ]);

            // Commit transaksi
            DB::commit();

            // Send Whatsapp via fonnte
            $fonnte = new FonnteService();
            $target = '6285234063886';
            $message = Pegawai::where('id', $username_pegawai)->first()->nama;
            $response = $fonnte->sendText($target, $message . ' mengajukan ' . $request->jenis_ijin, [
                'typing' => true,
                'delay' => 2,
                'countryCode' => '62',
            ]);

            return redirect()->route('lupaa.index')->with('success', 'Surat Izin berhasil diajukan.');
            // Simpan data terlambat ke tabel terlambat
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('lupa.index')->with('danger', 'Surat Izin gagal diajukan.');
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('suratijin::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($access_token)
    {
        $user_login = auth()->user();

        $id_pejabat_login = null;
        if ($user_login->role_aktif === 'operator') {
            $id_user_login = $user_login->id;
            $pegawai_login = Pegawai::where('id', $id_user_login)->first();
            $pejabat_login = Pejabat::where('pegawai_id', $pegawai_login->id)->first();
            $id_pejabat_login = optional($pejabat_login)->id;
        }

        $lupa = LupaAbsen::where('access_token', $access_token)->firstOrFail();
        $pegawai = Pegawai::find($lupa->pegawai_id);
        $anggota = Anggota::where('pegawai_id', $lupa->pegawai_id)->first();
        $tim = TimKerja::find(optional($anggota)->tim_kerja_id);

        $atasanService = new AtasanService();
        $ketua = $atasanService->getAtasanPegawai($lupa->pegawai_id);

        // Hitung batas tanggal
        $today = Carbon::today();
        $libur = Libur::pluck('tanggal')->map(fn($tgl) => Carbon::parse($tgl)->format('Y-m-d'))->toArray();

        $tanggalMundur = $this->hitungHariKerja($today, 5, 'mundur', $libur);
        $tanggalMaju = $this->hitungHariKerja($today, 5, 'maju', $libur);

        $tanggalMin = $tanggalMundur[4]->format('Y-m-d');
        $tanggalMax = $tanggalMaju[4]->format('Y-m-d');

        return view('suratijin::lupaabsen.edit', compact(
            'lupa',
            'pegawai',
            'anggota',
            'tim',
            'ketua',
            'id_pejabat_login',
            'tanggalMin',
            'tanggalMax'
        ));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $access_token)
    {
        $username_login = auth()->user()->username;
        $username_pegawai = Pegawai::where('username', $username_login)->first()->id;

        // Validasi input
        $request->validate([
            'jenis_ijin' => 'required',
            'hari' => 'required',
            'tanggal' => 'required|date',
            'alasan' => 'required',
        ]);

        // Ambil data terlambat berdasarkan access_token
        $lupa = LupaAbsen::where('access_token', $access_token)->firstOrFail();

        // Mulai transaksi
        DB::beginTransaction();
        try {
            // Update data utama
            $lupa->update([
                'jenis_ijin' => $request->jenis_ijin,
                'hari' => $request->hari,
                'tanggal' => $request->tanggal,
                'alasan' => $request->alasan,
            ]);

            // Simpan log pembaruan
            LupaAbsenLogs::create([
                'lupa_absen_id' => $lupa->id,
                'status' => 'Diperbarui',
                'updated_by' => $username_pegawai,
            ]);

            // Commit transaksi
            DB::commit();

            return redirect()->route('lupa.index')->with('success', 'Surat Izin berhasil diperbarui.');
        } catch (\Throwable $th) {
            // Rollback jika terjadi error
            DB::rollBack();
            return redirect()->route('lupa.index')->with('danger', 'Gagal memperbarui Surat Izin.');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function approvedByKepegawaian(Request $request, $access_token)
    {
        // Pastikan hanya role admin (unit kepegawaian) yang bisa menyetujui
        if (auth()->user()->role_aktif !== 'admin') {
            return redirect()->route('lupa.index')->with('danger', 'Anda tidak memiliki hak akses untuk menyetujui surat izin.');
        }

        // Ambil data surat izin berdasarkan access_token
        $lupa_absen = LupaAbsen::where('access_token', $access_token)->first();

        if (!$lupa_absen) {
            return redirect()->route('lupa.index')->with('danger', 'Surat izin tidak ditemukan.');
        }

        // Mulai transaksi DB
        DB::beginTransaction();

        try {
            $username_login = auth()->user()->username;
            $pegawai_id = Pegawai::where('username', $username_login)->first()->id;

            // Update status dan catatan dari kepegawaian
            $lupa_absen->update([
                'status' => 'Diproses',
            ]);

            // Tambahkan log status ke tabel lupa_absen_logs
            LupaAbsenLogs::create([
                'lupa_absen_id' => $lupa_absen->id,
                'status' => 'Telah diteruskan ke atasan',
                'updated_by' => $pegawai_id,
            ]);

            DB::commit();

            // (Opsional) Kirim notifikasi WA
            $fonnte = new FonnteService();
            $target = '6285234063886';
            $message = $username_login->nama . ' (Kepegawaian) meneruskan pengajuan ke atasan ' . $lupa_absen->jenis_ijin;

            $fonnte->sendText($target, $message, [
                'typing' => true,
                'delay' => 2,
                'countryCode' => '62',
            ]);

            return redirect()->route('lupa.index')->with('success', 'Surat izin berhasil diteruskan ke atasan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('lupa.index')->with('danger', 'Gagal meneruskan surat izin: ' . $th->getMessage());
        }
    }

    public function approve($access_token)
    {
        DB::beginTransaction();

        try {
            $lupa_absen = LupaAbsen::with('pegawai')->where('access_token', $access_token)->firstOrFail();
            $username_login = auth()->user()->username;
            $pegawai_login = Pegawai::where('username', $username_login)->firstOrFail();
            $pegawai_id = $lupa_absen->pegawai_id;

            // Setujui surat yang dipilih
            $lupa_absen->update([
                'status' => 'Disetujui',
                'tanggal_disetujui_pejabat' => now(),
            ]);

            LupaAbsenLogs::create([
                'lupa_absen_id' => $lupa_absen->id,
                'status' => 'Telah disetujui atasan',
                'updated_by' => $pegawai_login->id,
            ]);

            // Hitung jumlah surat disetujui di bulan yang sama
            $bulan_ini = Carbon::parse($lupa_absen->tanggal)->month;
            $tahun_ini = Carbon::parse($lupa_absen->tanggal)->year;

            $disetujuiBulanIni = LupaAbsen::where('pegawai_id', $pegawai_id)
                ->whereMonth('tanggal', $bulan_ini)
                ->whereYear('tanggal', $tahun_ini)
                ->where('status', 'Disetujui')
                ->count();

            // Jika kuota maksimal (2) sudah terpenuhi, batalkan sisanya
            if ($disetujuiBulanIni >= 2) {
                $suratLain = LupaAbsen::where('pegawai_id', $pegawai_id)
                    ->whereMonth('tanggal', $bulan_ini)
                    ->whereYear('tanggal', $tahun_ini)
                    ->whereIn('status', ['Diajukan', 'Diproses'])
                    ->where('id', '!=', $lupa_absen->id)
                    ->get();

                foreach ($suratLain as $surat) {
                    $surat->update([
                        'status' => 'Dibatalkan Otomatis',
                    ]);

                    LupaAbsenLogs::create([
                        'lupa_absen_id' => $surat->id,
                        'status' => 'Dibatalkan Otomatis (melebihi batas bulanan)',
                        'updated_by' => $pegawai_login->id,
                    ]);
                }
            }

            DB::commit();

            // (Opsional) Kirim notifikasi WA
            $fonnte = new FonnteService();
            $target = '6285234063886';
            $message = $pegawai_login->nama . ' menyetujui surat ijin ' . $lupa_absen->jenis_ijin;

            $fonnte->sendText($target, $message, [
                'typing' => true,
                'delay' => 2,
                'countryCode' => '62',
            ]);

            return redirect()->back()->with('success', 'Surat izin berhasil disetujui.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('danger', 'Surat izin gagal disetujui: ' . $th->getMessage());
        }
    }

    public function reject(Request $request, $access_token)
    {
        $lupa_absen = LupaAbsen::with('pegawai')->where('access_token', $access_token)->firstOrFail();

        DB::beginTransaction();

        try {
            $username_login = auth()->user()->username;
            $id_pegawai = Pegawai::where('username', $username_login)->first()->id;

            $lupa_absen->update([
                'status' => 'Ditolak',
                'tanggal_disetujui_pejabat' => now(),
            ]);

            LupaAbsenLogs::create([
                'lupa_absen_id' => $lupa_absen->id,
                'status' => 'Ditolak oleh atasan',
                'updated_by' => $id_pegawai,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Surat izin berhasil ditolak.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('danger', 'Gagal menolak surat izin: ' . $th->getMessage());
        }
    }

    public function print($access_token)
    {
        $lupa_absen = LupaAbsen::where('access_token', $access_token)->firstOrFail();

        $atasanService = new AtasanService();
        $atasan = $atasanService->getAtasanPegawai($lupa_absen->pegawai_id);

        $qrCodeImage = null;

        if ($lupa_absen->status === 'Disetujui') {
            $qrCodeUrl = url("/scan_lupa_absen/" . $lupa_absen->access_token);
            $qrCodeImage = QrCode::format('svg')->size(100)->generate($qrCodeUrl);
        }

        return view('suratijin::pdf.lupaabsen', compact('lupa_absen', 'atasan', 'qrCodeImage'));
    }

    public function scan($access_token)
    {
        $lupa_absen = LupaAbsen::where('access_token', $access_token)->first();
        $logs = LupaAbsenLogs::where('lupa_absen_id', $lupa_absen->id)->get();
        return view('suratijin::lupaabsen.scan', compact('lupa_absen', 'logs'));
    }

    private function hitungHariKerja(Carbon $start, int $jumlahHari, string $arah, array $libur = [])
    {
        $hasil = [];
        $step = $arah === 'maju' ? 1 : -1;
        $tanggal = $start->copy();

        while (count($hasil) < $jumlahHari) {
            $tanggal->addDays($step);
            if (!$tanggal->isWeekend() && !in_array($tanggal->format('Y-m-d'), $libur)) {
                $hasil[] = $tanggal->copy();
            }
        }

        return $hasil;
    }

    public function getHariDanKeterlambatan(Request $request)
    {
        $tanggal = Carbon::parse($request->tanggal);
        Carbon::setLocale('id');
        $hari = $tanggal->translatedFormat('l');

        $pegawaiId = $request->pegawai_id;

        // Ambil semua presensi pada tanggal itu
        $presensiHariIni = KehadiranI::where('user_id', $pegawaiId)
            ->whereDate('checktime', $tanggal)
            ->get();

        // Cek keberadaan I dan O
        $hasMasuk = $presensiHariIni->contains(function ($log) {
            return strtoupper($log->checktype) === 'I';
        });

        $hasPulang = $presensiHariIni->contains(function ($log) {
            return strtoupper($log->checktype) === 'O';
        });

        // Hanya tangani dua kondisi
        if ($hasMasuk && !$hasPulang) {
            $statusPresensi = 'Lupa Absensi Pulang';
        } elseif (!$hasMasuk && $hasPulang) {
            $statusPresensi = 'Lupa Absensi Masuk';
        } else {
            // Tidak melakukan apa-apa jika lengkap atau tidak ada presensi
            return response()->json([]);
        }

        return response()->json([
            'hari' => $hari,
            'status_presensi' => $statusPresensi,
        ]);
    }
}
