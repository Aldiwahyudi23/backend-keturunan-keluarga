<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <style>
        @page{
            margin:0;
            padding:0;
            size:242.65pt 153.07pt;
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family: DejaVu Sans, sans-serif;
            width:242.65pt;
            height:153.07pt;
        }

        .card{
            position:relative;
            width:242.65pt;
            height:153.07pt;
            overflow:hidden;
        }

        .card-bg{
            position:absolute;
            top:0;
            left:0;
            width:100%;
            height:100%;
            z-index:0;
        }

        .card-bg img{
            width:100%;
            height:100%;
        }

        .header{
            position:absolute;
            top:5pt;
            left:8pt;
            right:8pt;
            text-align:center;
            border-bottom:1.4pt solid #000;
            padding-bottom:4pt;
            z-index:2;
        }

        .header-title{
            font-size:11pt;
            font-weight:bold;
            line-height:1;
            text-transform:uppercase;
        }

        .header-subtitle{
            font-size:7pt;
            margin-top:2pt;
            line-height:1;
        }

        .photo{
            position:absolute;
            left:10pt;
            top:70pt;
            width:52pt;
            height:62pt;
            background:#efefef;
            overflow:hidden;
            z-index:2;
        }

        .photo img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .content{
            position:absolute;
            left:70pt;
            top:40pt;
            width:100pt;
            z-index:2;
        }

        .name{
            font-size:8pt;
            font-weight:bold;
            line-height:1.15;
            text-align:center;
        }

        .nasab{
            margin-top:1pt;
            font-size:7pt;
            font-style:italic;
            text-align:center;
        }

        .name-divider{
            margin-top:3pt;
            margin-bottom:3pt;
            border-top:1pt solid #000;
        }

        .info{
            font-size:7pt;
            line-height:1.5;
        }

        .label{
            display:inline-block;
            width:28pt;
            font-weight:bold;
            vertical-align:top;
        }

        .separator{
            display:inline-block;
            width:5pt;
            text-align:center;
            vertical-align:top;
        }

        .value{
            display:inline-block;
            width:60pt;
            vertical-align:top;
        }

        .qr{
            position:absolute;
            right:10pt;
            bottom:28pt;
            width:34pt;
            height:34pt;
            text-align:center;
            overflow:hidden;
            z-index:2;
        }

        .qr img{
            width:100%;
            height:100%;
        }

        .code{
            position:absolute;
            right:10pt;
            bottom:17pt;
            width:34pt;
            text-align:center;
            font-size:5.8pt;
            font-weight:bold;
            letter-spacing:.4pt;
            z-index:2;
        }

        .back-content{
            position:absolute;
            top:5pt;
            left:12pt;
            right:12pt;
            bottom:10pt;
            z-index:2;
            font-size:6.2pt;
            line-height:1.45;
        }

    </style>

</head>

<body>

{{-- ================= PETUNJUK CETAK ================= --}}

{{-- ================= PETUNJUK ================= --}}

<div class="card" style="background:#fff; page-break-after:always;">

    <div style="
        position:absolute;
        top:8pt;
        left:10pt;
        right:10pt;
        bottom:8pt;
        font-family:DejaVu Sans, sans-serif;
        color:#222;
    ">

        <div style="
            text-align:center;
            font-size:10pt;
            font-weight:bold;
            letter-spacing:.5pt;
            border-bottom:1pt solid #999;
            padding-bottom:3pt;
            margin-bottom:6pt;
        ">
            PETUNJUK
        </div>

        <div style="
            font-size:7pt;
            font-weight:bold;
            margin-bottom:2pt;
        ">
            Untuk Pengelola
        </div>

        <div style="
            font-size:6.3pt;
            line-height:1.5;
            margin-left:5pt;
            margin-bottom:6pt;
        ">
            • Periksa kembali nama, foto, dan data anggota sebelum dicetak.<br>
            • Pastikan QR Code dapat dipindai dan mengarah ke halaman yang benar.
        </div>

        <div style="
            font-size:7pt;
            font-weight:bold;
            margin-bottom:2pt;
        ">
            Informasi Cetak
        </div>

        <div style="
            font-size:6.3pt;
            line-height:1.5;
            margin-left:5pt;
        ">
            • Desain sisi belakang kartu dibuat sama untuk seluruh anggota.<br>
            • Template sisi belakang ditempatkan pada halaman paling akhir dokumen.
        </div>


    </div>

</div>

@foreach($persons as $person)

{{-- ================= SISI DEPAN ================= --}}

<div class="card"
    @if(!empty($card['background']) && file_exists(public_path('storage/'.$card['background'])))
        style="
            background-image:url('{{ public_path('storage/'.$card['background']) }}');
            background-size:100% 100%;
            background-repeat:no-repeat;
        "
    @else
        style="
            background-image:url('{{ public_path('card/background.png') }}');
            background-size:100% 100%;
            background-repeat:no-repeat;
        "
    @endif
>

    <div class="header">
        <div class="header-title">
            {{ $card['title'] }}
        </div>

        <div class="header-subtitle">
            {{ $card['subtitle'] }}
        </div>
    </div>

    <div class="photo">
        @if(!empty($person['photo']) && file_exists(public_path('storage/'.$person['photo'])))
            <img src="{{ public_path('storage/'.$person['photo']) }}">
        @endif
    </div>

    <div class="content">

        <div class="name">
            {{ $person['full_name'] }}
        </div>

        <div class="nasab">
            {{ $person['nasab'] ?? '' }} {{ $person['father_name'] ?? 'Pulan' }}
        </div>

        <div class="name-divider"></div>

        <div class="info">

            <div>
                <span class="label">Ayah</span>
                <span class="separator">:</span>
                <span class="value">
                    {{ $person['father_name'] ?: '-' }}
                </span>
            </div>

            <div>
                <span class="label">Ibu</span>
                <span class="separator">:</span>
                <span class="value">
                    {{ $person['mother_name'] ?: '-' }}
                </span>
            </div>

            <div>
                <span class="label">Lahir</span>
                <span class="separator">:</span>
                <span class="value">
                    {{ $person['birth_date'] }}
                </span>
            </div>

            <div>
                <span class="label">Status</span>
                <span class="separator">:</span>
                <span class="value">
                    {{ $person['marital_status'] }}
                </span>
            </div>

            {{-- @if(!empty($person['address']))
            <div>
                <span class="label">Alamat</span>
                <span class="separator">:</span>
                <span class="value">
                    {{ $person['address'] }}
                </span>
            </div>
            @endif --}}

        </div>

    </div>

    <div class="qr-wrapper">

        <div class="qr">
            <img src="data:image/svg+xml;base64,{{ $person['qr'] }}">
        </div>

        <div class="code">
            {{ $person['person_code'] }}
        </div>

    </div>

</div>

@endforeach

{{-- ================= SISI BELAKANG ================= --}}

<div class="card"
    @if(!empty($card['background']) && file_exists(public_path('storage/'.$card['background'])))
        style="
            background-image:url('{{ public_path('storage/'.$card['background']) }}');
            background-size:100% 100%;
            background-repeat:no-repeat;
        "
    @else
        style="
            background-image:url('{{ public_path('card/background.png') }}');
            background-size:100% 100%;
            background-repeat:no-repeat;
        "
    @endif
>

    <div class="back-content">

        <div style="text-align:center; margin-bottom:2pt;">
            @if(!empty($card['logo']) && file_exists(public_path('storage/'.$card['logo'])))
                <img
                    src="{{ public_path('storage/'.$card['logo']) }}"
                    style="width:50pt; height:50pt;"
                >
            @else
                <img
                    src="{{ public_path('card/logo.png') }}"
                    style="width:50pt; height:50pt;"
                >
            @endif
        </div>

        @if(!empty($card['note']))
            <div style="
                text-align:justify;
                font-style:italic;
                font-size:6pt;
                line-height:1.5;
            ">
                {!! $card['note'] !!}
            </div>
        @else
            <div style="
                text-align:center;
                font-weight:bold;
                margin-bottom:4pt;
            ">
                Papatah Karuhun Sunda
            </div>

            <div style="
                text-align:justify;
                font-style:italic;
                font-size:6pt;
                line-height:1.5;
            ">
                "Silih asih, silih asah, silih asuh. Duduluran ulah pegat ku
                amarah, ulah leungit ku harta, ulah jauh ku pangkat. Hormat ka
                nu sepuh, nyaah ka nu ngora. Sauyunan dina kulawarga, sareundeuk
                saigel, sabobot sapihanean. Lamun silaturahmi dijaga, hirup bakal
                rukun, ayem, tengtrem tur pinuh ku rahmat Gusti."
            </div>
        @endif

    </div>

</div>



</body>
</html>
