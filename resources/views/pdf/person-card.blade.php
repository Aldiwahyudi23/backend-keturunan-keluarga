<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        @page {
            margin: 0;
            size: A6 landscape;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            background: #1a1a2e;
        }

        * {
            box-sizing: border-box;
        }

        /* ==================== HALAMAN DEPAN ==================== */
        .card {
            width: 100%;
            height: 100vh;
            border: 6px solid #2d2d44;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            position: relative;
            overflow: hidden;
            page-break-after: always;
            box-shadow: inset 0 0 50px rgba(0, 0, 0, 0.3);
        }

        /* Efek garis dekoratif */
        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(255, 215, 0, 0.03) 0%, transparent 70%);
            pointer-events: none;
        }

        .header {
            background: linear-gradient(135deg, #2d2d44 0%, #1a1a2e 100%);
            color: #f0e6d3;
            padding: 14px 25px;
            text-align: center;
            border-bottom: 2px solid rgba(255, 215, 0, 0.2);
            position: relative;
        }

        .header::after {
            content: '✦';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            color: #c9a84c;
            font-size: 14px;
            background: #1a1a2e;
            padding: 0 10px;
        }

        .header-title {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #c9a84c;
            text-shadow: 0 0 20px rgba(201, 168, 76, 0.2);
        }

        .header-subtitle {
            font-size: 10px;
            margin-top: 3px;
            opacity: .7;
            color: #a8a8b3;
            letter-spacing: 3px;
        }

        .content {
            padding: 20px 25px;
            height: calc(100vh - 120px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .photo-column {
            width: 155px;
            text-align: center;
            vertical-align: middle;
            padding-right: 20px;
        }

        .photo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .photo-wrapper {
            position: relative;
            padding: 3px;
            background: linear-gradient(135deg, #c9a84c, #f0d68a);
            border-radius: 6px;
            box-shadow: 0 4px 20px rgba(201, 168, 76, 0.2);
        }

        .photo {
            width: 130px;
            height: 160px;
            object-fit: cover;
            border-radius: 3px;
            display: block;
        }

        .photo-empty {
            width: 130px;
            height: 160px;
            border: 2px dashed rgba(201, 168, 76, 0.3);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666677;
            font-size: 10px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(5px);
        }

        .code {
            display: inline-block;
            padding: 4px 16px;
            background: linear-gradient(135deg, #c9a84c, #b8943a);
            color: #1a1a2e;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 10px rgba(201, 168, 76, 0.3);
        }

        .info {
            padding-left: 15px;
            vertical-align: middle;
        }

        .name {
            font-size: 22px;
            font-weight: bold;
            color: #f0e6d3;
            line-height: 1.3;
            margin-bottom: 4px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .nasab {
            margin-top: 2px;
            margin-bottom: 10px;
            color: #c9a84c;
            font-style: italic;
            font-size: 13px;
            opacity: 0.8;
        }

        .divider {
            margin: 10px 0 12px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
        }

        .divider::after {
            content: '◆';
            position: absolute;
            top: -8px;
            right: 0;
            color: #c9a84c;
            font-size: 10px;
            opacity: 0.5;
        }

        .info-table {
            width: 100%;
            border-spacing: 0;
        }

        .info-table tr {
            height: 22px;
        }

        .info-table td {
            padding: 3px 0;
            vertical-align: middle;
            font-size: 11px;
            line-height: 1.4;
            color: #d0d0d8;
        }

        .label {
            width: 80px;
            color: #888899;
            font-weight: 500;
            font-size: 10px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .label::after {
            content: ':';
            margin-left: 2px;
        }

        .value {
            color: #f0e6d3;
            font-weight: 400;
        }

        .footer {
            position: absolute;
            bottom: 12px;
            left: 25px;
            right: 25px;
            text-align: center;
            font-size: 8px;
            color: #666677;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 8px;
            letter-spacing: 1px;
        }

        /* ==================== HALAMAN BELAKANG ==================== */
        .card-back {
            width: 100%;
            height: 100vh;
            border: 6px solid #2d2d44;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 0 50px rgba(0, 0, 0, 0.3);
        }

        .card-back::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 70% 50%, rgba(201, 168, 76, 0.03) 0%, transparent 70%);
            pointer-events: none;
        }

        .back-content {
            display: flex;
            flex-direction: row;
            align-items: stretch;
            gap: 30px;
            text-align: center;
            padding: 30px 40px;
            width: 100%;
            max-width: 100%;
            position: relative;
            z-index: 1;
        }

        /* QR Code - Sebelah Kanan */
        .qr-section {
            flex: 0 0 140px;
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid rgba(201, 168, 76, 0.15);
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            order: 2;
        }

        .qr-image {
            width: 120px;
            height: 120px;
            display: block;
            filter: drop-shadow(0 2px 10px rgba(201, 168, 76, 0.1));
        }

        .qr-label {
            font-size: 8px;
            color: #888899;
            margin-top: 8px;
            font-style: italic;
            letter-spacing: 0.5px;
        }

        /* Ayat - Sebelah Kiri (lebih besar) */
        .ayat-section {
            flex: 1;
            padding: 20px 30px;
            background: linear-gradient(135deg, rgba(45, 45, 68, 0.8) 0%, rgba(26, 26, 46, 0.9) 100%);
            border-radius: 8px;
            border: 1px solid rgba(201, 168, 76, 0.1);
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            order: 1;
        }

        .ayat-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #c9a84c;
            border-bottom: 1px solid rgba(201, 168, 76, 0.15);
            padding-bottom: 8px;
        }

        .ayat-text {
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 10px;
            font-style: italic;
            color: #f0e6d3;
            font-weight: 300;
        }

        .ayat-reference {
            font-size: 10px;
            color: #c9a84c;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .ayat-translation {
            font-size: 10px;
            line-height: 1.6;
            color: #b0b0c0;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 10px;
            font-style: normal;
        }

        .back-footer {
            position: absolute;
            bottom: 12px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 8px;
            color: #555566;
            letter-spacing: 1px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 8px;
            z-index: 1;
        }

        .back-footer span {
            color: #c9a84c;
            opacity: 0.6;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .back-content {
                flex-direction: column;
                padding: 20px;
                gap: 15px;
            }
            .qr-section {
                flex: 0 0 auto;
                order: 1;
                padding: 10px;
            }
            .qr-image {
                width: 80px;
                height: 80px;
            }
            .ayat-section {
                order: 2;
                padding: 15px 20px;
            }
            .ayat-text {
                font-size: 12px;
            }
            .photo {
                width: 100px;
                height: 130px;
            }
            .photo-empty {
                width: 100px;
                height: 130px;
            }
            .photo-column {
                width: 120px;
                padding-right: 10px;
            }
            .name {
                font-size: 18px;
            }
        }
    </style>

</head>

<body>

    <!-- ==================== HALAMAN DEPAN (FRONT) ==================== -->
    <div class="card">

        <div class="header">
            <div class="header-title">✦ KARTU KELUARGA ✦</div>
            <div class="header-subtitle">SISTIM SILSILAH KELUARGA MAHAYA</div>
        </div>

        <div class="content">
            <table>
                <tr>
                    <td class="photo-column">
                        <div class="photo-section">
                            <div class="photo-wrapper">
                                @if($person['photo'])
                                    <img class="photo" src="{{ storage_path('app/public/'.$person['photo']) }}">
                                @else
                                    <div class="photo-empty">FOTO</div>
                                @endif
                            </div>
                            <div class="code">{{ $person['person_code'] }}</div>
                        </div>
                    </td>

                    <td class="info">
                        <div class="name">{{ strtoupper($person['full_name']) }}</div>

                        @if($person['nasab'])
                            <div class="nasab">{{ $person['full_name_with_nasab'] }}</div>
                        @endif

                        <div class="divider"></div>

                        <table class="info-table">
                            <tr>
                                <td class="label">Ayah</td>
                                <td class="value">{{ $person['father_name'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Ibu</td>
                                <td class="value">{{ $person['mother_name'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Lahir</td>
                                <td class="value">{{ $person['birth_date'] }}</td>
                            </tr>
                            <tr>
                                <td class="label">Status</td>
                                <td class="value">{{ $person['marital_status'] }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">✦ Dokumen otomatis • Sistem Informasi Silsilah Keluarga ✦</div>

    </div>

    <!-- ==================== HALAMAN BELAKANG (BACK) ==================== -->
    <div class="card-back">

        <div class="back-content">

            <!-- AYAT AL-QURAN SECTION (SEBELAH KIRI) -->
            <div class="ayat-section">
                <div class="ayat-title">✦ Ajaran Islam tentang Keluarga ✦</div>

                <div class="ayat-text">
                    "Dan Kami perintahkan kepada manusia (untuk berbuat baik) kepada dua orang ibu-bapaknya..."
                </div>

                <div class="ayat-reference">— QS. Al-Isra' (17): 23 —</div>

                {{-- <div class="ayat-translation">
                    Keluarga adalah fondasi masyarakat yang baik. Mari jaga silsilah keluarga kita dengan saling menghormati, menyayangi, dan saling mendukung sesama anggota keluarga Mahaya.
                </div> --}}
            </div>

            <!-- QR CODE SECTION (SEBELAH KANAN) -->
            <div class="qr-section">
                @if(!empty($person['qr']))
                    <img class="qr-image" src="data:image/svg+xml;base64,{{ $person['qr'] }}">
                    <div class="qr-label">◈ Pindai untuk silsilah lengkap ◈</div>
                @endif
            </div>

        </div>

        {{-- <div class="back-footer">
            <span>◈</span> Keluarga Mahaya • Menjaga Amanah, Membangun Generasi Baik <span>◈</span>
        </div> --}}

    </div>

</body>

</html>