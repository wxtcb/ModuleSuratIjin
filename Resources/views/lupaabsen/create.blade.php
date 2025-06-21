@extends('adminlte::page')
@section('title', 'Tambah Surat Ijin')

@section('content_header')
<h1 class="m-0 text-dark">Form Surat Ijin</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Informasi Pegawai -->
        <div class="card">
            <div class="card-body">
                <h1>Informasi Pegawai</h1>
                <div class="row">
                    <!-- Identitas Pegawai -->
                    <div class="col-md-6">
                        <h6 style="color: grey">
                            <center>Identitas Pegawai</center>
                        </h6>
                        <table class="table">
                            <tr>
                                <td>Nama</td>
                                <td>:
                                    {{ $pegawai->gelar_dpn ?? '' }}{{ $pegawai->gelar_dpn ? ' ' : '' }}{{ $pegawai->nama }}{{ $pegawai->gelar_blk ? ', ' . $pegawai->gelar_blk : '' }}
                                </td>
                            </tr>
                            <tr>
                                <td>NIP/NIPPK</td>
                                <td>: {{ $pegawai->nip }}</td>
                            </tr>
                            <tr>
                                <td>Unit Kerja</td>
                                <td>: {{ $tim->unit->nama }}</td>
                            </tr>
                        </table>
                    </div>
                    <!-- Identitas Atasan -->
                    <div class="col-md-6">
                        <h6 style="color: grey">
                            <center>Identitas Atasan</center>
                        </h6>
                        <table class="table">
                            <tr>
                                <td>Nama</td>
                                <td>:
                                    {{ $ketua->pegawai->gelar_dpn ?? '' }}{{ $ketua->pegawai->gelar_dpn ? ' ' : '' }}{{ $ketua->pegawai->nama }}{{ $ketua->pegawai->gelar_blk ? ', ' . $ketua->pegawai->gelar_blk : '' }}
                                </td>
                            </tr>
                            <tr>
                                <td>NIP/NIPPK</td>
                                <td>: {{ $ketua->pegawai->nip }}</td>
                            </tr>
                            <tr>
                                <td>Unit Kerja</td>
                                <td>: {{ $ketua->unit->nama ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Surat Izin -->
        <div class="card">
            <div class="card-body">
                <h1>Form Pengajuan Surat Izin</h1>
                <div class="mt-2">
                    @include('layouts.partials.messages')
                    @if (session('error'))
                    <div class="alert alert-warning" role="alert">
                        {{ session('error') }}
                    </div>
                    @endif
                </div>
                <form method="POST" action="{{ route('lupa.store') }}">
                    @csrf
                    <input type="hidden" name="pegawai_id" value="{{ $pegawai->id }}">
                    <input type="hidden" name="pejabat_id" value="{{ $ketua->id }}">
                    <input type="hidden" name="tim_kerja_id" value="{{ $tim->id }}">

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label for="jenis_ijin" class="form-label">Jenis Izin</label>
                            <select name="jenis_ijin" id="jenis_ijin" class="form-control" required disabled>
                                <option value="">-- Pilih Jenis Izin --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" min="{{ $tanggalMin }}" max="{{ $tanggalMax }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="hari" class="form-label">Hari</label>
                            <input type="text" name="hari" id="hari" class="form-control" readonly required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="alasan" class="form-label">Alasan</label>
                        <textarea name="alasan" id="alasan" class="form-control" rows="3" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('lupa.index') }}" class="btn btn-default">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tanggalInput = document.getElementById('tanggal');
        const hariInput = document.getElementById('hari');
        const jenisIjinSelect = document.getElementById('jenis_ijin');
        const pegawaiId = '{{ $pegawai->id }}';

        tanggalInput.addEventListener('change', function () {
            const tanggal = this.value;
            if (!tanggal) return;

            fetch(`{{ route('lupa.getLupa') }}?tanggal=${tanggal}&pegawai_id=${pegawaiId}`)
                .then(response => response.json())
                .then(data => {
                    // Reset field
                    hariInput.value = '';
                    jenisIjinSelect.innerHTML = '<option value="">-- Pilih Jenis Izin --</option>';
                    jenisIjinSelect.disabled = true;

                    if (!data || !data.status_presensi) return;

                    // Isi hari
                    hariInput.value = data.hari;

                    // Isi dan kunci jenis izin
                    const option = document.createElement('option');
                    option.value = data.status_presensi;
                    option.textContent = data.status_presensi;
                    option.selected = true;

                    jenisIjinSelect.appendChild(option);
                    jenisIjinSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Gagal memuat data presensi:', error);
                    hariInput.value = '';
                    jenisIjinSelect.innerHTML = '<option value="">-- Pilih Jenis Izin --</option>';
                    jenisIjinSelect.disabled = true;
                });
        });
    });
</script>
@stop
