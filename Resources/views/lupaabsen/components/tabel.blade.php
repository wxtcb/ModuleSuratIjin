<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Pegawai</th>
            <th>Jenis Ijin</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($lupa_absen as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
                {{ $item->pegawai->gelar_dpn ? $item->pegawai->gelar_dpn . ' ' : '' }}
                {{ $item->pegawai->nama }}
                {{ $item->pegawai->gelar_blk ? ', ' . $item->pegawai->gelar_blk : '' }}
            </td>
            <td>{{ $item->jenis_ijin}}</td>
            <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
            <td class="text-center">
                <span class="badge rounded-pill bg-{{ $item->badgeClass }}"><a href="{{route('lupa.scan', $item->access_token)}}">{{ ucfirst($item->status) }}</a>
                </span>
            </td>
            <td>
                <a class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalDetail-{{ $item->access_token }}">
                    <i class="nav-icon fas fa-eye"></i>
                </a>
                @if ($item->status === 'Disetujui')
                <a class="btn btn-success btn-sm" href="{{ route('lupa.print', $item->access_token) }}">
                    <i class="nav-icon fas fa-book"></i>
                </a>
                @endif
                @if (
                    (auth()->user()->role_aktif === 'admin' || auth()->user()->username === $item->pegawai->username) &&
                    !in_array($item->status, ['Disetujui', 'Ditolak'])
                )
                <a class="btn btn-warning btn-sm" href="{{ route('lupa.edit', $item->access_token) }}">
                    <i class="nav-icon fas fa-edit"></i>
                </a>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="text-center">Belum ada data Surat Ijin.</td>
        </tr>
        @endforelse
    </tbody>
</table>