@extends('adminlte::page')
@section('title', 'Surat Ijin')

@section('content_header')
    <h1 class="m-0 text-dark">Rekapitulasi Surat Ijin</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>Rekapitulasi Surat Ijin</h3>
                        <a href="{{ route('terlambat.create') }}" class="btn btn-primary">+ Buat Surat Ijin</a>
                    </div>

                    <div class="mt-2">
                        @include('layouts.partials.messages')
                    </div>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pegawai</th>
                                <th>Jenis Ijin</th>
                                <th>Jam</th>
                                <th>Hari</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suratIjins as $ijin)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $ijin->pegawai->gelar_dpn ?? '' }}{{ $ijin->pegawai->gelar_dpn ? ' ' : '' }}{{ $ijin->pegawai->nama }}{{ $ijin->pegawai->gelar_blk ? ', ' . $ijin->pegawai->gelar_blk : '' }}
                                    </td>
                                    <td>{{ $ijin->jenis_ijin }}</td>
                                    <td>{{ $ijin->jam }}</td>
                                    <td>{{ $ijin->hari }}</td>
                                    <td>{{ \Carbon\Carbon::parse($ijin->tanggal)->translatedFormat('d F Y') }}</td>
                                    <td>
                                        <a href="{{ route('terlambat.edit', $ijin->access_token) }}" class="btn btn-sm btn-primary">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada data surat ijin.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
@stop
