<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Ijin </title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 0.5in;
            color: #000;
            background: #f4f4f4;
            line-height: 1.2;
            font-size: 10pt;
            box-sizing: border-box;
        }

        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: center;
            /* Logo akan rata kiri */
        }

        .kop-surat img {
            width: 90px;
            height: auto;
            margin-right: 15px;
        }

        .kop-surat-text {
            text-align: center;
            font-size: 10pt;
        }

        h2 {
            font-size: 14pt;
            text-align: center;
            margin: 10px 0;
        }

        table.form {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10pt;
        }

        table.form td {
            padding: 3px 6px;
            vertical-align: top;
        }

        .container {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .table-cuti {
            width: 45%;
        }

        .signatures {
            width: 55%;
        }

        .ttd {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sign {
            text-align: center;
        }

        .catatan-kepegawaian {
            margin-top: 10px;
            font-size: 9pt;
        }

        /* Tombol Print hanya muncul di web */
        .web-only {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            display: inline-block;
            padding: 5px 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 9pt;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .btn-print:hover {
            background-color: #0056b3;
            transform: scale(1.05);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .digital-stamp {
            display: flex;
            align-items: center;
            border: 1px solid #000;
            padding: 2px 4px;
            max-width: 260px;
            font-size: 7pt;
            line-height: 1.1;
            margin: 5px auto 0 auto;
            background-color: white;
        }

        .stamp-logo {
            flex-shrink: 0;
            margin-right: 6px;
        }

        .stamp-logo img {
            width: 28px;
            height: auto;
        }

        .stamp-text {
            flex-grow: 1;
        }

        .footer {
            margin-top: 30px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            font-size: 9pt;
            page-break-inside: avoid;
        }

        .signature-info {
            flex-grow: 1;
            margin-left: 10px;
        }

        /* Styling tambahan untuk Preview Web */
        @media screen {
            body {
                display: flex;
                justify-content: center;
                background-color: #f4f4f4;
            }

            .page-wrapper {
                width: 100%;
                max-width: 8.5in;
                background-color: white;
                padding: 20px;
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                border: 1px solid #ddd;
                margin-top: 30px;
                margin-bottom: 30px;
            }
        }

        /* Styling untuk Print */
        @media print {
            .web-only {
                display: none;
            }

            body {
                font-size: 10pt;
                margin: 0.5in;
                max-width: 8.5in;
                background-color: white;
            }

            .page-wrapper {
                box-shadow: none;
                padding: 0;
            }

            .signatures {
                page-break-inside: avoid;
            }
        }

        .qr-footer {
    margin-top: 40px;
    display: flex;
    align-items: flex-start;
    justify-content: flex-start;
    font-size: 8pt;
    position: relative;
    page-break-inside: avoid;
}

.qr-footer .qr-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.qr-footer img {
    width: 60px;
    height: 60px;
}

@media print {
    .qr-footer {
        position: fixed;
        bottom: 0.3in;
        left: 0;
        right: 0;
        width: 100%;
        padding: 0 0.5in;
    }
}
    </style>
</head>

<body>

    <!-- Tombol hanya muncul di browser -->
    <div class="page-wrapper">

        <!-- Tombol Print Preview -->
        <div class="web-only">
            <button class="btn-print" onclick="window.print()">🖨️ Tampilkan Print Preview</button>
        </div>

        <!-- Kop Surat -->
        <div class="kop-surat">
            <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Politeknik Negeri Banyuwangi">
            <div class="kop-surat-text">
                <strong>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET DAN TEKNOLOGI</strong><br>
                <strong>POLITEKNIK NEGERI BANYUWANGI</strong><br>
                Jalan Raya Jember KM 13 Labanasem Kabat-Banyuwangi, 68461<br>
                Telp/Fax: (0333) 636780; E-mail: poliwangi@poliwangi.ac.id; Laman: poliwangi.ac.id
            </div>
        </div>

        <hr style="margin: 10px 0;">

        <h2>Surat Permohonan {{ $lupa_absen->jenis_ijin }}</h2>


        <p>Yang bertanda tangan dibawah ini:</p>

        <table class="form">
            <tr>
                <td>Nama</td>
                <td>:
                    {{ $lupa_absen->pegawai->gelar_dpn ?? '' }}{{ $lupa_absen->pegawai->gelar_dpn ? ' ' : '' }}{{ $lupa_absen->pegawai->nama }}{{ $lupa_absen->pegawai->gelar_blk ? ', ' . $lupa_absen->pegawai->gelar_blk : '' }}
                </td>
            </tr>
            <tr>
                <td>NIP / NIK</td>
                <td>: {{ $lupa_absen->pegawai->nip }}</td>
            </tr>
            <tr>
                <td>Pangkat / Gol.</td>
                <td>: {{ $lupa_absen->pegawai->id_staff }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: {{ $lupa_absen->pegawai->id_staff }}</td>
            </tr>
            <tr>
                <td>Unit Kerja</td>
                <td>: {{ $lupa_absen->timKerja->unit->nama}}</td>
            </tr>
        </table>

        <p>Pada hari {{ $lupa_absen->hari }} tanggal {{ date('d M Y', strtotime($lupa_absen->tanggal)) }} tidak melakukan presensi {{ $lupa_absen->jenis_ijin }} karena alasan yang sah, yaitu {{ $lupa_absen->alasan }}.</p>

        <p>Demikian surat keterangan ini dibuat dengan sesungguhnya untuk dapat dipergunakan sebagaimana mestinya.</p>

        <div class="container">
            <!-- Tabel Cuti -->
            <div class="table-cuti">
                <br>
                <div class="sign">
                    Mengetahui Atasan Langsung,<br>
                    <div class="digital-stamp">
                        <div class="stamp-logo">
                            <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Instansi">
                        </div>
                        <div class="stamp-text">
                            Ditandatangani secara elektronik oleh<br>
                            Direktur Politeknik Negeri Banyuwangi<br>
                            selaku Pejabat yang Berwenang

                        </div>
                    </div>
                    {{ $atasan->pegawai->gelar_dpn ?? '' }}{{ $atasan->pegawai->gelar_dpn ? ' ' : '' }}{{ $atasan->pegawai->nama }}{{ $atasan->pegawai->gelar_blk ? ', ' . $atasan->pegawai->gelar_blk : '' }}<br>
                    NIP/NIPPPK/NIK. {{$atasan->pegawai->nip}}
                </div>
            </div>

            <!-- Kolom Tanda Tangan -->
            <div class="signatures">
                <div class="ttd">
                    <div class="sign">
                        Banyuwangi, {{ date('d M Y', strtotime($lupa_absen->created_at)) }}<br>
                        Yang Menyatakan,<br>
                        <div class="digital-stamp">
                            <div class="stamp-logo">
                                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo Instansi">
                            </div>
                            <div class="stamp-text">
                                Ditandatangani secara elektronik oleh<br>
                                Direktur Politeknik Negeri Banyuwangi<br>
                                selaku Pejabat yang Berwenang

                            </div>
                        </div>
                        {{ $lupa_absen->pegawai->gelar_dpn ?? '' }}{{ $lupa_absen->pegawai->gelar_dpn ? ' ' : '' }}{{ $lupa_absen->pegawai->nama }}{{ $lupa_absen->pegawai->gelar_blk ? ', ' . $lupa_absen->pegawai->gelar_blk : '' }}<br>
                        NIP/NIPPPK/NIK. {{$lupa_absen->pegawai->nip}}
                    </div>

                </div>
            </div>
        </div>
        <!-- Footer dengan QR Code -->

        <p>Tembusan:</p>
        <p>1. Pejabat Eselon II yang bersangkutan <br> 2. Pejabat yang menangani Kepegawaian</p>

 

        @if ($qrCodeImage)
        <div class="qr-footer">
            <div class="qr-wrapper">
                <img src="data:image/svg+xml;base64,{{ base64_encode($qrCodeImage) }}" alt="QR Code">
                <div>
                    Surat ini sudah ditandatangani secara digital,<br>
                    sehingga tidak perlu tanda tangan basah dan stempel.
                </div>
            </div>
        </div>
    @endif
    </div> <!-- Tutup .page-wrapper -->


</body>

</html>