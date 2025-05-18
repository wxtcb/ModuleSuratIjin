<?php

namespace Modules\SuratIjin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cuti\Services\AtasanService;
use Modules\Pengaturan\Entities\Anggota;
use Modules\Pengaturan\Entities\Pegawai;
use Modules\Pengaturan\Entities\Pejabat;
use Modules\Pengaturan\Entities\TimKerja;
use Modules\SuratIjin\Entities\Terlambat;
use Illuminate\Support\Str;

class TerlambatController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $suratIjins = Terlambat::with('pegawai')->orderBy('tanggal', 'desc')->get();

        return view('suratijin::terlambat.index',  compact('suratIjins'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        // Ambil data pegawai berdasarkan user yang login
        $pegawai = Pegawai::where('username', auth()->user()->username)->first();

        // Ambil data keanggotaan tim kerja pegawai
        $anggota = Anggota::where('pegawai_id', $pegawai->id)->first();

        // Ambil data tim kerja jika ada
        $tim = TimKerja::find($anggota->tim_kerja_id ?? null);

        // Gunakan service untuk ambil data atasan
        $atasanService = new AtasanService();
        $ketua = $atasanService->getAtasanPegawai($pegawai->id);

        // Kirim data ke view
        return view('suratijin::terlambat.create', compact(
            'pegawai',
            'tim',
            'anggota',
            'ketua'
        ));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // Generate access token
        $uuid = Str::uuid()->toString();
        $access_token = substr($uuid, 0, 12);

        // Validasi input
        $request->validate([
            'jenis_ijin' => 'required',
            'jam' => 'required',
            'hari' => 'required',
            'tanggal' => 'required|date',
            'alasan' => 'required',
        ]);

        // Tambahkan access_token ke data request
        $data = $request->all();
        $data['access_token'] = $access_token;

        // Simpan data ke database
        Terlambat::create($data);

        return redirect()->route('terlambat.index')->with('success', 'Surat Izin berhasil disimpan.');
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

        return view('suratijin::terlambat.edit', compact(
            'ijin',
            'pegawai',
            'anggota',
            'tim',
            'ketua',
            'id_pejabat_login'
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
        $request->validate([
            'jenis_ijin' => 'required',
            'jam' => 'required',
            'hari' => 'required',
            'tanggal' => 'required|date',
            'alasan' => 'required',
        ]);

        // Ambil data berdasarkan access token
        $ijin = Terlambat::where('access_token', $access_token)->firstOrFail();

        // Update data
        $ijin->update($request->only([
            'jenis_ijin',
            'jam',
            'hari',
            'tanggal',
            'alasan',
        ]));

        return redirect()->route('terlambat.index')->with('success', 'Surat Izin berhasil diperbarui.');
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
}
