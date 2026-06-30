<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Person - {{ $person->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 10px;
        }
        
        .card {
            width: 105mm;
            height: 148mm;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 3mm;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            position: relative;
            overflow: hidden;
        }
        
        /* Background pattern */
        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.05) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .card-inner {
            background: white;
            border-radius: 9px;
            height: 100%;
            padding: 5mm;
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Header */
        .card-header {
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 3mm;
            margin-bottom: 4mm;
        }
        
        .card-header .title {
            font-size: 14pt;
            font-weight: 700;
            color: #4a5568;
            letter-spacing: 2px;
        }
        
        .card-header .subtitle {
            font-size: 8pt;
            color: #718096;
            margin-top: 1mm;
        }
        
        /* Foto */
        .photo-container {
            text-align: center;
            margin-bottom: 4mm;
        }
        
        .photo {
            width: 40mm;
            height: 40mm;
            border-radius: 50%;
            border: 3px solid #667eea;
            object-fit: cover;
            background: #edf2f7;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .photo-placeholder {
            width: 40mm;
            height: 40mm;
            border-radius: 50%;
            border: 3px solid #667eea;
            background: linear-gradient(135deg, #e2e8f0, #cbd5e0);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 20pt;
            color: #a0aec0;
            font-weight: bold;
        }
        
        /* Informasi */
        .info {
            flex: 1;
        }
        
        .info-item {
            margin-bottom: 2mm;
            padding: 1.5mm 2mm;
            border-bottom: 1px dashed #e2e8f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-size: 7pt;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 10pt;
            color: #2d3748;
            font-weight: 600;
            margin-top: 0.5mm;
        }
        
        .info-value .code {
            font-family: 'Courier New', monospace;
            color: #667eea;
            font-weight: 700;
        }
        
        /* Badge Gender */
        .gender-badge {
            display: inline-block;
            padding: 1mm 4mm;
            border-radius: 20px;
            font-size: 8pt;
            font-weight: 600;
            color: white;
        }
        
        .gender-badge.male {
            background: #4299e1;
        }
        
        .gender-badge.female {
            background: #ed64a6;
        }
        
        /* Footer */
        .card-footer {
            text-align: center;
            padding-top: 3mm;
            border-top: 2px solid #e2e8f0;
            margin-top: auto;
        }
        
        .card-footer .footer-text {
            font-size: 7pt;
            color: #a0aec0;
            letter-spacing: 1px;
        }
        
        .card-footer .barcode {
            font-family: 'Courier New', monospace;
            font-size: 12pt;
            color: #4a5568;
            letter-spacing: 3px;
            margin-top: 1mm;
        }
        
        /* Watermark */
        .watermark {
            position: absolute;
            bottom: 10mm;
            right: 5mm;
            opacity: 0.05;
            font-size: 60pt;
            font-weight: bold;
            color: #667eea;
            transform: rotate(-15deg);
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-inner">
            <!-- Header -->
            <div class="card-header">
                <div class="title">🏛️ KARTU PERSON</div>
                <div class="subtitle">{{ config('app.name') }}</div>
            </div>
            
            <!-- Foto -->
            <div class="photo-container">
                @if($person->photo_path)
                    <img src="{{ public_path('storage/' . $person->photo_path) }}" alt="Foto" class="photo">
                @else
                    <div class="photo-placeholder">
                        {{ strtoupper(substr($person->full_name, 0, 2)) }}
                    </div>
                @endif
            </div>
            
            <!-- Informasi -->
            <div class="info">
                <div class="info-item">
                    <div class="info-label">👤 Nama Lengkap</div>
                    <div class="info-value">{{ $person->full_name }}</div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">🆔 Kode Person</div>
                    <div class="info-value"><span class="code">{{ $person->person_code }}</span></div>
                </div>
                
                <div class="info-item" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div class="info-label">⚤ Jenis Kelamin</div>
                        <div class="info-value">
                            <span class="gender-badge {{ $person->gender }}">
                                {{ $gender_label }}
                            </span>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div class="info-label">📅 Lahir</div>
                        <div class="info-value" style="font-size: 9pt;">{{ $birth_date_formatted }}</div>
                    </div>
                </div>
                
                @if($person->birth_place)
                <div class="info-item">
                    <div class="info-label">📍 Tempat Lahir</div>
                    <div class="info-value">{{ $person->birth_place }}</div>
                </div>
                @endif
                
                @if($father || $mother)
                <div class="info-item">
                    <div class="info-label">👨‍👩‍👦 Orang Tua</div>
                    <div class="info-value" style="font-size: 9pt;">
                        @if($father)
                            Ayah: {{ $father->full_name }}
                        @endif
                        @if($father && $mother)
                            <br>
                        @endif
                        @if($mother)
                            Ibu: {{ $mother->full_name }}
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Footer -->
            <div class="card-footer">
                <div class="footer-text">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</div>
                <div class="barcode">•••• {{ $person->person_code }} ••••</div>
            </div>
        </div>
        
        <!-- Watermark -->
        <div class="watermark">{{ config('app.name') }}</div>
    </div>
</body>
</html>