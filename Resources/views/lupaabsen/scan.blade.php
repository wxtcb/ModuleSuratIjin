<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Progres Pengajuan Surat Ijin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .progress-bar {
            transition: width 1s ease-in-out;
        }

        .progress-step {
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
        }

        .step-label {
            font-size: 14px;
            margin-top: 5px;
        }

        /* Status dibatalkan */
        .cancelled {
            background-color: #dc3545;
            /* Merah untuk status dibatalkan */
        }

        .cancelled .progress-bar {
            background-color: #dc3545;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container py-5">
        <!-- Header -->
        <div class="text-center mb-5">
            <h2 class="fw-bold">
                Persetujuan Pengajuan Surat Ijin Pegawai
            </h2>
        </div>

        <!-- Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p><strong>Nama:</strong><br>
                            {{ $lupa_absen->pegawai->gelar_dpn ?? '' }}{{ $lupa_absen->pegawai->gelar_dpn ? ' ' : '' }}{{ $lupa_absen->pegawai->nama }}{{ $lupa_absen->pegawai->gelar_blk ? ', ' . $lupa_absen->pegawai->gelar_blk : '' }}
                        </p>
                        <p><strong>NIP:</strong><br> {{ $lupa_absen->pegawai->nip }}</p>
                        <p><strong>Jenis Ijin:</strong><br> {{ $lupa_absen->jenis_ijin }}</p>
                        <p><strong>Status Saat Ini:</strong><br>
                            <span class="fw-bold text-primary">{{ ucfirst($lupa_absen->status) }}</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Tanggal Lupa Mengisi Daftar Hadir:</strong><br>
                            {{ date('d M Y', strtotime($lupa_absen->tanggal)) }}
                        </p>
                        <p><strong>Disetujui Atasan:</strong><br>
                            {{ $lupa_absen->tanggal_disetujui_pejabat ? date('d M Y: H:i:s', strtotime($lupa_absen->tanggal_disetujui_pejabat)) : '-' }}
                        </p>
                    </div>
                </div>

                @php
                    $status = strtolower($lupa_absen->status);
                    $steps = ['Diajukan', 'Diproses', 'Disetujui'];
                    $currentIndex = array_search(ucfirst($lupa_absen->status), $steps);
                    $progress = ($currentIndex / (count($steps) - 1)) * 100; // Calculate percentage
                @endphp

                <div class="progress" style="height: 30px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%"
                        aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-step">{{ $steps[$currentIndex] }}</div>
                    </div>
                </div>

                <!-- Labels for progress steps -->
                <div class="d-flex justify-content-between mt-3">
                    @foreach ($steps as $index => $step)
                        <div class="step-label {{ $index <= $currentIndex ? 'text-primary' : 'text-muted' }}">
                            {{ $step }}</div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- card 2 --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Aktor</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $item)
                            <tr>
                                <td>{{ $item->status }}</td>
                                <td>
                                  {{ $item->pegawai->gelar_dpn ?? '' }}{{ $item->pegawai->gelar_dpn ? ' ' : '' }}{{ $item->pegawai->nama }}{{ $item->pegawai->gelar_blk ? ', ' . $item->pegawai->gelar_blk : '' }}
                                </td>
                                <td>{{ date('d M Y: H:i:s', strtotime($item->created_at)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
