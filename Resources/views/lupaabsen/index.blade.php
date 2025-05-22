@extends('adminlte::page')
@section('title', 'Lupa Absen')

@section('content_header')
<h1 class="m-0 text-dark">Rekapitulasi Lupa Absen</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Rekapitulasi Lupa Absen</h3>
                    <a href="{{ route('lupa.create') }}" class="btn btn-primary">+ Buat Lupa Absen</a>
                </div>

                <div class="mt-2">
                    @include('layouts.partials.messages')
                </div>

                <ul class="nav nav-tabs mb-3" id="lupaTab" role="tablist">
                    @if ($lupa_pribadi)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ !$lupa_anggota && !$lupa_absen ? 'active' : '' }}"
                                id="pribadi-tab" data-bs-toggle="tab"
                                data-bs-target="#pribadi" type="button" role="tab">
                                Surat Pribadi
                            </button>
                        </li>
                    @endif

                    @if ($lupa_anggota)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ (!$lupa_pribadi && !$lupa_absen) || $user->role_aktif === 'kajur' ? 'active' : '' }}"
                                id="anggota-tab" data-bs-toggle="tab"
                                data-bs-target="#anggota" type="button" role="tab">
                                Surat Anggota
                            </button>
                        </li>
                    @endif

                    @if ($lupa_absen && $user->role_aktif === 'admin')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ !$lupa_pribadi && !$lupa_anggota ? 'active' : '' }}"
                                id="semua-tab" data-bs-toggle="tab"
                                data-bs-target="#semua" type="button" role="tab">
                                Semua Surat
                            </button>
                        </li>
                    @endif
                </ul>

                <div class="tab-content" id="lupaTabContent">
                    @if ($lupa_pribadi)
                        <div class="tab-pane fade {{ !$lupa_anggota && !$lupa_absen ? 'show active' : '' }}" id="pribadi" role="tabpanel">
                            @include('suratijin::lupaabsen.components.tabel', ['lupa_absen' => $lupa_pribadi])
                        </div>
                    @endif

                    @if ($lupa_anggota)
                        <div class="tab-pane fade {{ (!$lupa_pribadi && !$lupa_absen) || $user->role_aktif === 'kajur' ? 'show active' : '' }}" id="anggota" role="tabpanel">
                            @include('suratijin::lupaabsen.components.tabel', ['lupa_absen' => $lupa_anggota])
                        </div>
                    @endif

                    @if ($lupa_absen && $user->role_aktif === 'admin')
                        <div class="tab-pane fade {{ !$lupa_pribadi && !$lupa_anggota ? 'show active' : '' }}" id="semua" role="tabpanel">
                            @include('suratijin::lupaabsen.components.tabel', ['lupa_absen' => $lupa_absen])
                        </div>
                    @endif
                </div>

                <!-- Modal show dan Button setujui -->
                @foreach ([$lupa_pribadi, $lupa_anggota, $lupa_absen] as $list)
                    @if ($list)
                        @foreach ($list as $lupa)
                            <div class="modal fade" id="modalDetail-{{ $lupa->access_token }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $lupa->access_token }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel{{ $lupa->access_token }}">Detail Surat Izin</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <h6 class="text-muted">Informasi Pegawai</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td><strong>Nama</strong></td>
                                                    <td>:</td>
                                                    <td>{{ $lupa->pegawai->gelar_dpn ?? '' }}{{ $lupa->pegawai->gelar_dpn ? ' ' : '' }}{{ $lupa->pegawai->nama }}{{ $lupa->pegawai->gelar_blk ? ', ' . $lupa->pegawai->gelar_blk : '' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>NIP/NIPPK</strong></td>
                                                    <td>:</td>
                                                    <td>{{ $lupa->pegawai->nip }}</td>
                                                </tr>
                                            </table>

                                            <h6 class="text-muted">Informasi Izin</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td><strong>Jenis lupa</strong></td>
                                                    <td>:</td>
                                                    <td>{{ $lupa->jenis_ijin }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Hari, Tanggal</strong></td>
                                                    <td>:</td>
                                                    <td>{{ $lupa->hari }}, {{ \Carbon\Carbon::parse($lupa->tanggal)->translatedFormat('d F Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Alasan</strong></td>
                                                    <td>:</td>
                                                    <td>{{ $lupa->alasan }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            @if ($lupa->isKetuaTim && $lupa->status === 'Diproses')
                                                <form action="{{ route('lupa.approve', $lupa->access_token) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Setujui</button>
                                                </form>
                                            @endif
                                            @if ($lupa->isKetuaTim && $lupa->status === 'Diproses')
                                                <form action="{{ route('lupa.reject', $lupa->access_token) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger">Tolak</button>
                                                </form>
                                            @endif
                                            @if ($user->role_aktif === 'admin' && $lupa->status === 'Diajukan')
                                                <form action="{{ route('lupa.approve-kepegawaian', $lupa->access_token) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary">Teruskan Ke Atasan</button>
                                                </form>
                                            @endif
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endforeach

            </div>
        </div>
    </div>
</div>
@stop

@section('adminlte_js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stop