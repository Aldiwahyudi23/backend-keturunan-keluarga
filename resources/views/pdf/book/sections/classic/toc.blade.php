
<div class="chapter">
    <h1 class="chapter-title">
        {{ $section['title'] ?? 'DAFTAR ISI' }}
    </h1>

    @if(!empty($sections) && count($sections) > 0)
        <table class="toc-table">
            @foreach($sections as $item)
                {{-- Skip TOC itself --}}
                @if(isset($item['key']) && $item['key'] === 'toc')
                    @continue
                @endif

                <tr class="toc-item">
                    <td class="toc-title">
                        {{ $item['title'] ?? 'Untitled' }}
                    </td>
                    <td class="toc-dots">
                        .............................................................................................................
                    </td>
                    <td class="toc-page">
                        {{ $loop->iteration + 1 }}
                    </td>
                </tr>

                {{-- Sub items untuk genealogy --}}
                @if(isset($item['type']) && $item['type'] === 'dynamic' && isset($item['key']) && $item['key'] === 'genealogy')
                    @if(isset($item['data']['generations']) && is_array($item['data']['generations']))
                        @foreach($item['data']['generations'] as $generation => $members)
                            <tr class="toc-child">
                                <td class="toc-title-child">
                                    {{ $generation }}
                                    <span class="toc-total">
                                        ({{ is_array($members) ? count($members) : 0 }} generasi)
                                    </span>
                                </td>
                                <td class="toc-dots">
                                    ........................................................................................
                                </td>
                                <td class="toc-page">
                                    {{ $loop->parent->iteration + $loop->iteration + 1 }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endif
            @endforeach
        </table>
    @else
        <div style="text-align:center; padding:20px; color:#666;">
            <p>Tidak ada section yang tersedia untuk ditampilkan.</p>
        </div>
    @endif
</div>