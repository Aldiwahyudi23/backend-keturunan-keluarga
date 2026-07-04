@php
    $history = $section['data'] ?? [];
@endphp

<div class="chapter">
    <h1 class="chapter-title">
        {{ $section['title'] ?? '' }}
    </h1>

    @if(!empty($history['person']))
    <div class="history-profile">
        <table class="history-table">
            <tr>
                <td width="170"><strong>Kode Anggota</strong></td>
                <td width="15">:</td>
                <td>{{ $history['person']['person_code'] ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Nama Lengkap</strong></td>
                <td>:</td>
                <td>{{ $history['person']['full_name_with_nasab'] ?? '-' }}</td>
            </tr>
            @if(!empty($history['person']['birth_date']))
            <tr>
                <td><strong>Tanggal Lahir</strong></td>
                <td>:</td>
                <td>{{ $history['person']['birth_date'] }}</td>
            </tr>
            @endif
            @if(!empty($history['person']['death_date']) && $history['person']['death_date'] !== '-')
            <tr>
                <td><strong>Tanggal Wafat</strong></td>
                <td>:</td>
                <td>{{ $history['person']['death_date'] }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    @if(!empty($history['histories']) && count($history['histories']) > 0)
        <h2 class="history-section-title">Riwayat Kehidupan</h2>
        @foreach($history['histories'] as $item)
            <div class="history-item">
                <div class="history-item-title">
                    {{ $item['title'] ?? 'Peristiwa' }}
                </div>
                @if(!empty($item['event_date_formatted']))
                    <div class="history-item-date">
                        {{ $item['event_date_formatted'] }}
                    </div>
                @endif
                @if(!empty($item['description']))
                    <div class="history-item-content">
                        {!! $item['description'] !!}
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="history-empty">
            Belum terdapat data sejarah yang terdokumentasi untuk tokoh ini.
        </div>
    @endif
</div>