<?php

namespace Modules\SuratIjin\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Cuti\Services\AtasanService;
use Modules\Pengaturan\Entities\Anggota;
use Modules\Pengaturan\Entities\Pegawai;
use Modules\Pengaturan\Entities\Pejabat;
use Modules\Pengaturan\Entities\TimKerja;
use Modules\SuratIjin\Entities\Terlambat;
use Illuminate\Support\Str;
use Modules\Setting\Entities\Libur;
use Modules\SuratIjin\Entities\TerlambatLogs;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TerlambatController extends Controller
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

        $suratIjins = null;
        $surat_pribadi = null;
        $surat_anggota = null;

        if ($role === 'admin') {
            $suratIjins = Terlambat::with('pegawai')->orderBy('tanggal', 'desc')->get();
        } elseif ($role === 'kajur') {
            $surat_anggota = Terlambat::where('pejabat_id', $pejabat_id)
                ->whereHas('logs', function ($query) {
                    $query->where('status', 'Telah diteruskan ke atasan');
                })->with('pegawai')->orderBy('tanggal', 'desc')->get();

            $surat_pribadi = Terlambat::where('pegawai_id', $pegawai_id)
                ->with('pegawai')->orderBy('tanggal', 'desc')->get();
        } elseif ($role === 'dosen') {
            $surat_pribadi = Terlambat::where('pegawai_id', $pegawai_id)
                ->with('pegawai')->orderBy('tanggal', 'desc')->get();
        }

        foreach ([$suratIjins, $surat_anggota, $surat_pribadi] as &$collection) {
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

        return view('suratijin::terlambat.index', compact(
            'suratIjins', 'surat_pribadi', 'surat_anggota', 'pejabat_id', 'user'
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

        $today = Carbon::today();
        $libur = Libur::pluck('tanggal')->map(fn($tgl) => Carbon::parse($tgl)->format('Y-m-d'))->toArray();

        $tanggalMundur = $this->hitungHariKerja($today, 5, 'mundur', $libur);
        $tanggalMaju = $this->hitungHariKerja($today, 5, 'maju', $libur);

        $tanggalMin = $tanggalMundur[4]->format('Y-m-d');
        $tanggalMax = $tanggalMaju[4]->format('Y-m-d');

        return view('suratijin::terlambat.create', compact(
            'pegawai', 'tim', 'anggota', 'ketua', 'tanggalMin', 'tanggalMax'
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

        $today = Carbon::today();
        $tanggalInput = Carbon::parse($request->tanggal);
        $libur = Libur::pluck('tanggal')->map(fn($tgl) => Carbon::parse($tgl)->format('Y-m-d'))->toArray();

        $tanggalMundur = $this->hitungHariKerja($today, 5, 'mundur', $libur);
        $tanggalMaju = $this->hitungHariKerja($today, 5, 'maju', $libur);
        $tanggalValid = collect($tanggalMundur)->merge($tanggalMaju)->map->format('Y-m-d')->toArray();

        if (!in_array($tanggalInput->format('Y-m-d'), $tanggalValid)) {
            return back()->withErrors(['tanggal' => 'Tanggal harus dalam rentang 5 hari kerja dari hari ini.'])->withInput();
        }

        $request->validate([
            'pegawai_id' => 'required|exists:pegawais,id',
            'pejabat_id' => 'required|exists:pejabats,id',
            'jenis_ijin' => 'required',
            'jam' => 'required',
            'hari' => 'required',
            'tanggal' => 'required|date',
            'alasan' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $access_token = substr(Str::uuid()->toString(), 0, 12);

            $terlambat = Terlambat::create([
                'pegawai_id' => $request->pegawai_id,
                'pejabat_id' => $request->pejabat_id,
                'jenis_ijin' => $request->jenis_ijin,
                'jam' => $request->jam,
                'hari' => $request->hari,
                'tanggal' => $request->tanggal,
                'alasan' => $request->alasan,
                'access_token' => $access_token,
                'tim_kerja_id' => $request->tim_kerja_id,
                'status' => 'Diajukan',
            ]);

            TerlambatLogs::create([
                'terlambat_id' => $terlambat->id,
                'status' => 'Diajukan',
                'updated_by' => $username_pegawai,
            ]);

            DB::commit();
            return redirect()->route('terlambat.index')->with('success', 'Surat Izin berhasil diajukan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('terlambat.index')->with('danger', 'Surat Izin gagal diajukan.');
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

        // Cek apakah user login adalah operator
        $id_pejabat_login = null;
        if ($user_login->role_aktif === 'operator') {
            $id_user_login = $user_login->id;
            $pegawai_login = Pegawai::where('id', $id_user_login)->first();
            $pejabat_login = Pejabat::where('pegawai_id', $pegawai_login->id)->first();
            $id_pejabat_login = optional($pejabat_login)->id;
        }

        // Ambil data surat ijin berdasarkan access token
        $ijin = Terlambat::where('access_token', $access_token)->firstOrFail();

        // Ambil data pegawai yang mengajukan ijin
        $pegawai = Pegawai::find($ijin->pegawai_id);

        // Ambil data anggota dan tim kerja
        $anggota = Anggota::where('pegawai_id', $ijin->pegawai_id)->first();
        $tim = TimKerja::find(optional($anggota)->tim_kerja_id);

        // Ambil atasan
        $atasanService = new AtasanService();
        $ketua = $atasanService->getAtasanPegawai($ijin->pegawai_id);

        // Hitung batas tanggal berdasarkan hari kerja
        $today = Carbon::today();
        $libur = Libur::pluck('tanggal')->map(fn($tgl) => Carbon::parse($tgl)->format('Y-m-d'))->toArray();

        $tanggalMundur = $this->hitungHariKerja($today, 5, 'mundur', $libur);
        $tanggalMaju = $this->hitungHariKerja($today, 5, 'maju', $libur);

        $tanggalMin = $tanggalMundur[4]->format('Y-m-d');
        $tanggalMax = $tanggalMaju[4]->format('Y-m-d');

        return view('suratijin::terlambat.edit', compact(
            'ijin',
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
            'jam' => 'required',
            'hari' => 'required',
            'tanggal' => 'required|date',
            'alasan' => 'required',
        ]);

        // Ambil data terlambat berdasarkan access_token
        $ijin = Terlambat::where('access_token', $access_token)->firstOrFail();

        // Mulai transaksi
        DB::beginTransaction();
        try {
            // Update data utama
            $ijin->update([
                'jenis_ijin' => $request->jenis_ijin,
                'jam' => $request->jam,
                'hari' => $request->hari,
                'tanggal' => $request->tanggal,
                'alasan' => $request->alasan,
            ]);

            // Simpan log pembaruan
            TerlambatLogs::create([
                'terlambat_id' => $ijin->id,
                'status' => 'Diperbarui',
                'updated_by' => $username_pegawai,
            ]);

            // Commit transaksi
            DB::commit();

            return redirect()->route('terlambat.index')->with('success', 'Surat Izin berhasil diperbarui.');
        } catch (\Throwable $th) {
            // Rollback jika terjadi error
            DB::rollBack();
            return redirect()->route('terlambat.index')->with('danger', 'Gagal memperbarui Surat Izin.');
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
            return redirect()->route('terlambat.index')->with('danger', 'Anda tidak memiliki hak akses untuk menyetujui surat izin.');
        }

        // Ambil data surat izin berdasarkan access_token
        $terlambat = Terlambat::where('access_token', $access_token)->first();

        if (!$terlambat) {
            return redirect()->route('terlambat.index')->with('danger', 'Surat izin tidak ditemukan.');
        }

        // Mulai transaksi DB
        DB::beginTransaction();

        try {
            $username_login = auth()->user()->username;
            $pegawai_id = Pegawai::where('username', $username_login)->first()->id;

            // Update status dan catatan dari kepegawaian
            $terlambat->update([
                'status' => 'Diproses',
            ]);

            // Tambahkan log status ke tabel terlambat_logs
            TerlambatLogs::create([
                'terlambat_id' => $terlambat->id,
                'status' => 'Telah diteruskan ke atasan',
                'updated_by' => $pegawai_id,
            ]);

            DB::commit();

            return redirect()->route('terlambat.index')->with('success', 'Surat izin berhasil diteruskan ke atasan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('terlambat.index')->with('danger', 'Gagal meneruskan surat izin: ' . $th->getMessage());
        }
    }

    public function approve($access_token)
    {
        // Ambil data surat izin
        $terlambat = Terlambat::with('pegawai')->where('access_token', $access_token)->firstOrFail();

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            $username_login = auth()->user()->username;
            $username_pegawai = Pegawai::where('username', $username_login)->first()->id;

            // Update status dan tanggal persetujuan
            $terlambat->update([
                'status' => 'Disetujui',
                'tanggal_disetujui_pejabat' => now(),
            ]);

            // Tambahkan log status ke tabel terlambat_logs
            TerlambatLogs::create([
                'terlambat_id' => $terlambat->id,
                'status' => 'Telah disetujui atasan',
                'updated_by' => $username_pegawai,
            ]);

            // Commit transaksi
            DB::commit();

            return redirect()->back()->with('success', 'Surat izin berhasil disetujui.');
        } catch (\Throwable $th) {
            // Rollback jika terjadi error
            DB::rollBack();

            return redirect()->back()->with('danger', 'Surat izin gagal disetujui karena: ' . $th->getMessage());
        }
    }

    public function reject(Request $request, $access_token)
    {
        $terlambat = Terlambat::with('pegawai')->where('access_token', $access_token)->firstOrFail();

        DB::beginTransaction();

        try {
            $username_login = auth()->user()->username;
            $id_pegawai = Pegawai::where('username', $username_login)->first()->id;

            $terlambat->update([
                'status' => 'Ditolak',
                'tanggal_disetujui_pejabat' => now(),
            ]);

            TerlambatLogs::create([
                'terlambat_id' => $terlambat->id,
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
        $terlambat = Terlambat::where('access_token', $access_token)->firstOrFail();

        $atasanService = new AtasanService();
        $atasan = $atasanService->getAtasanPegawai($terlambat->pegawai_id);

        $qrCodeImage = null;

        if ($terlambat->status === 'Disetujui') {
            $qrCodeUrl = url("/scan/" . $terlambat->access_token); 
            $qrCodeImage = QrCode::format('svg')->size(100)->generate($qrCodeUrl);
        }
        
        return view('suratijin::pdf.terlambat', compact('terlambat', 'atasan', 'qrCodeImage'));
    }

    public function scan($access_token)
    {
        $terlambat = Terlambat::where('access_token', $access_token)->first();
        $logs = TerlambatLogs::where('terlambat_id', $terlambat->id)->get();
        return view('suratijin::terlambat.scan', compact('terlambat', 'logs'));
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
}
