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
                <form method="POST" action="{{ route('terlambat.store') }}">
                    @csrf
                    <input type="hidden" name="pegawai_id" value="{{ $pegawai->id }}">
                    <input type="hidden" name="pejabat_id" value="{{ $ketua->id }}">
                    <input type="hidden" name="tim_kerja_id" value="{{ $tim->id }}">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="jenis_ijin" class="form-label">Jenis Izin</label>
                            <select name="jenis_ijin" id="jenis_ijin" class="form-control" required>
                                <option value="">-- Pilih Jenis Izin --</option>
                                <option value="Terlambat">Terlambat</option>
                                <option value="Pulang Cepat">Pulang Cepat</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" id="tanggal" class="form-control" required min="{{ $tanggalMin }}" max="{{ $tanggalMax }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="hari" class="form-label">Hari</label>
                            <input type="text" name="hari" id="hari" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="jam" class="form-label">Jam</label>
                            <input type="trxt" name="jam" id="jam" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="alasan" class="form-label">Alasan</label>
                        <textarea name="alasan" id="alasan" class="form-control" rows="3" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('terlambat.index') }}" class="btn btn-default">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
@section('adminlte_js')
<script>
    $(document).ready(function() {
        $('#tanggal').on('change', function() {
            let tanggal = $(this).val();
            let pegawai_id = $('input[name="pegawai_id"]').val();

            if (tanggal) {
                $.ajax({
                    url: '{{ route("terlambat.getInfoTanggal") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tanggal: tanggal,
                        pegawai_id: pegawai_id
                    },
                    success: function(response) {
                        $('#hari').val(response.hari); // Isi otomatis hari
                        $('#jam').val(response.total_keterlambatan); // Isi otomatis jam total
                    },
                    error: function() {
                        $('#jam').val('');
                        $('#hari').val('');
                        alert('Gagal memuat data keterlambatan. Silakan coba lagi.');
                    }
                });
            }
        });
    });
</script>

@stop