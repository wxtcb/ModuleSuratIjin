@extends('adminlte::page')
@section('title', 'Surat Ijin')

@section('content_header')
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Rekapitulasi Surat Ijin</h3>
                    @if (in_array(auth()->user()->role_aktif, ['pegawai', 'dosen']))
                    <a href="{{ route('terlambat.create') }}" class="btn btn-primary">+ Buat Surat Ijin</a>
                    @endif
                </div>

                <div class="mt-2">
                    @include('layouts.partials.messages')
                </div>

                <ul class="nav nav-tabs mb-3" id="terlambatTab" role="tablist">
                    @if ($surat_pribadi)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ !$surat_anggota && !$suratIjins ? 'active' : '' }}"
                                id="pribadi-tab" data-bs-toggle="tab"
                                data-bs-target="#pribadi" type="button" role="tab">
                                Surat Pribadi
                            </button>
                        </li>
                    @endif

                    @if ($surat_anggota)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ (!$surat_pribadi && !$suratIjins) || $user->role_aktif === 'kajur' ? 'active' : '' }}"
                                id="anggota-tab" data-bs-toggle="tab"
                                data-bs-target="#anggota" type="button" role="tab">
                                Surat Anggota
                            </button>
                        </li>
                    @endif

                    @if ($suratIjins && $user->role_aktif === 'admin')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ !$surat_pribadi && !$surat_anggota ? 'active' : '' }}"
                                id="semua-tab" data-bs-toggle="tab"
                                data-bs-target="#semua" type="button" role="tab">
                                Semua Surat
                            </button>
                        </li>
                    @endif
                </ul>

                <div class="tab-content" id="terlambatTabContent">
                    @if ($surat_pribadi)
                        <div class="tab-pane fade {{ !$surat_anggota && !$suratIjins ? 'show active' : '' }}" id="pribadi" role="tabpanel">
                            @include('suratijin::terlambat.components.tabel', ['suratIjins' => $surat_pribadi])
                        </div>
                    @endif

                    @if ($surat_anggota)
                        <div class="tab-pane fade {{ (!$surat_pribadi && !$suratIjins) || $user->role_aktif === 'kajur' ? 'show active' : '' }}" id="anggota" role="tabpanel">
                            @include('suratijin::terlambat.components.tabel', ['suratIjins' => $surat_anggota])
                        </div>
                    @endif

                    @if ($suratIjins && $user->role_aktif === 'admin')
                        <div class="tab-pane fade {{ !$surat_pribadi && !$surat_anggota ? 'show active' : '' }}" id="semua" role="tabpanel">
                            @include('suratijin::terlambat.components.tabel', ['suratIjins' => $suratIjins])
                        </div>
                    @endif
                </div>

                <!-- Modal show dan Button setujui -->
                @foreach ([$surat_pribadi, $surat_anggota, $suratIjins] as $list)
                    @if ($list)
                        @foreach ($list as $ijin)
                            <div class="modal fade" id="modalDetail-{{ $ijin->access_token }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $ijin->access_token }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel{{ $ijin->access_token }}">Detail Surat Izin</h5>
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
                                                    <td>{{ $ijin->pegawai->gelar_dpn ?? '' }}{{ $ijin->pegawai->gelar_dpn ? ' ' : '' }}{{ $ijin->pegawai->nama }}{{ $ijin->pegawai->gelar_blk ? ', ' . $ijin->pegawai->gelar_blk : '' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>NIP/NIPPK</strong></td>
                                                    <td>:</td>
                                                    <td>{{ $ijin->pegawai->nip }}</td>
                                                </tr>
                                            </table>

                                            <h6 class="text-muted">Informasi Izin</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td><strong>Jenis Ijin</strong></td>
                                                    <td>:</td>
                                                    <td>{{ $ijin->jenis_ijin }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Jam</strong></td>
                                                    <td>:</td>
                                                    <td>{{ $ijin->jam }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Hari, Tanggal</strong></td>
                                                    <td>:</td>
                                                    <td>{{ $ijin->hari }}, {{ \Carbon\Carbon::parse($ijin->tanggal)->translatedFormat('d F Y') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Alasan</strong></td>
                                                    <td>:</td>
                                                    <td>{{ $ijin->alasan }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            @if ($ijin->isKetuaTim && $ijin->status === 'Diproses')
                                                <form action="{{ route('terlambat.approve', $ijin->access_token) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Setujui</button>
                                                </form>
                                            @endif
                                            @if ($ijin->isKetuaTim && $ijin->status === 'Diproses')
                                                <form action="{{ route('terlambat.reject', $ijin->access_token) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger">Tolak</button>
                                                </form>
                                            @endif
                                            @if ($user->role_aktif === 'admin' && $ijin->status === 'Diajukan')
                                                <form action="{{ route('terlambat.approve-kepegawaian', $ijin->access_token) }}" method="POST" class="d-inline">
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