<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Pegawai</th>
            <th>Jenis Ijin</th>
            <th>Jam</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($suratIjins as $ijin)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                {{ $ijin->pegawai->gelar_dpn ? $ijin->pegawai->gelar_dpn . ' ' : '' }}
                {{ $ijin->pegawai->nama }}
                {{ $ijin->pegawai->gelar_blk ? ', ' . $ijin->pegawai->gelar_blk : '' }}
            </td>
            <td>{{ $ijin->jenis_ijin }}</td>
            <td>{{ $ijin->jam }}</td>
            <td>{{ \Carbon\Carbon::parse($ijin->tanggal)->translatedFormat('d F Y') }}</td>
            <td class="text-center">
                <span class="badge rounded-pill bg-{{ $ijin->badgeClass }}"><a href="{{route('terlambat.scan', $ijin->access_token)}}">{{ ucfirst($ijin->status) }}</a>
                </span>
            </td>
            <td>
                <a class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalDetail-{{ $ijin->access_token }}">
                    <i class="nav-icon fas fa-eye"></i>
                </a>
                @if (auth()->user()->role_aktif === 'admin' || auth()->user()->username === $ijin->pegawai->username)
                <a class="btn btn-warning btn-sm" href="{{ route('terlambat.edit', $ijin->access_token) }}">
                    <i class="nav-icon fas fa-edit"></i>
                </a>
                @endif
                @if ($ijin->status === 'Disetujui')
                <a class="btn btn-info btn-sm" href="{{ route('terlambat.print', $ijin->access_token) }}">
                    <i class="nav-icon fas fa-book"></i>
                </a>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">Belum ada data surat ijin.</td>
        </tr>
        @endforelse
    </tbody>
</table>