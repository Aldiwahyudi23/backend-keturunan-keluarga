<div class="chapter">

    <h1 class="chapter-title">
        Sejarah Singkat
    </h1>

    <div class="history-profile">

        <table class="history-table">

            <tr>
                <td width="170">Kode Anggota</td>
                <td width="15">:</td>
                <td>{{ $history['person']['person_code'] }}</td>
            </tr>

            <tr>
                <td>Nama Lengkap</td>
                <td>:</td>
                <td>{{ $history['person']['full_name_with_nasab'] }}</td>
            </tr>

        </table>

    </div>

    @if(!empty($history['person']['bio']))

        <div class="history-bio">

            {!! $history['person']['bio'] !!}

        </div>

    @endif


    @if(count($history['histories']))

        <h2 class="history-section-title">
            Riwayat Kehidupan
        </h2>

        @foreach($history['histories'] as $item)

            <div class="history-item">

                <div class="history-item-title">

                    {{ $item['title'] }}

                </div>

                @if(!empty($item['event_date_formatted']))

                    <div class="history-item-date">

                        {{ $item['event_date_formatted'] }}

                    </div>

                @endif

                <div class="history-item-content">

                    {!! $item['description'] !!}

                </div>

            </div>

        @endforeach

    @else

        <div class="history-empty">

            Belum terdapat data sejarah yang terdokumentasi untuk tokoh ini.

        </div>

    @endif

</div>