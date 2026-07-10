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
            /* color:#8B6B00; */
        }

        .card{
            position:relative;
            width:242.65pt;
            height:153.07pt;
            overflow:hidden;
            background-image:url("{{ public_path('card/background.png') }}");
            background-size:100% 100%;
            background-repeat:no-repeat;
        }

        /* ================= BACKGROUND ================= */

        .bg{
            position:absolute;
            top:0;
            left:0;
            width:100%;
            height:100%;
            z-index:0;
        }

        .bg img{
            width:100%;
            height:100%;
        }

        /* ================= HEADER ================= */

        .header{
            position:absolute;
            top:5pt;
            left:8pt;
            right:8pt;
            text-align:center;
            border-bottom:1.4pt solid #000;
            padding-bottom:4pt;
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
        
        .header-line{
            display:none;
        }

        /* ================= FOTO ================= */

        .photo{
            position:absolute;
            left:10pt;
            top:70pt;
            width:52pt;
            height:62pt;
            /*border:1pt solid #000;*/
            background:#efefef;
            overflow:hidden;
        }

        .photo img{
            width:100%;
            height:100%;
            object-fit:cover;
        }

        /* ================= DATA ================= */

        .content{
            position:absolute;
            left:70pt;
            top:40pt;
            width:100pt;
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

        /* ================= QR ================= */
        .qr{
            position:absolute;
            right:10pt;
            bottom:28pt;
            width:34pt;
            height:34pt;
            /*border:1pt solid #000;*/
            text-align:center;
            overflow:hidden;
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
        }

        .header,
        .photo,
        .content,
        .qr,
        .code{
            position:absolute;
            z-index:2;
        }

    </style>

</head>

<body>

<div class="card">

    {{-- ================= HEADER ================= --}}

    <div class="header">
        <div class="header-title">
            KELUARGA BESAR
        </div>

        <div class="header-subtitle">
            SILSILAH KELUARGA
        </div>
    </div>

    {{-- ================= FOTO ================= --}}

    <div class="photo">

        @if(!empty($person['photo']) && file_exists( public_path('storage/' . $person['photo']) ))
            <img src="{{ public_path('storage/' . $person['photo']) }}">
        @else
            {{-- Placeholder kosong --}}
        @endif

    </div>

    {{-- ================= DATA ================= --}}

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

        </div>

    </div>

    {{-- ================= QR ================= --}}

    <div class="qr-wrapper">

        <div class="qr">
            <img src="data:image/svg+xml;base64,{{ $person['qr'] }}">
        </div>

        <div class="code">
            {{ $person['person_code'] }}
        </div>

    </div>

</div>

{{-- <div style="page-break-after:always;"></div> --}}

<div class="card">

    {{-- Background (Opsional) --}}
    

    <div style="
        position:absolute;
        top:5pt;
        left:12pt;
        right:12pt;
        bottom:10pt;
        z-index:2;
        font-size:6.2pt;
        line-height:1.45;
    ">

        <!-- Logo -->
        <div style="text-align:center; margin-bottom:2pt;">
            <img
                src="{{ public_path('card/logo.png') }}"
                style="width:50pt; height:50pt;"
            >
        </div>


        <!-- Papatah -->
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


    </div>

</div>
</body>
</html>