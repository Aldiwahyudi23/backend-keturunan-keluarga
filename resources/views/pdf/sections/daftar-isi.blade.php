<div class="chapter">

    <h1 class="chapter-title">
        Daftar Isi
    </h1>

    <table class="toc-table">

        @foreach($table_of_contents as $item)

            <tr class="toc-item">

                <td class="toc-title">
                    {{ $item['title'] }}
                </td>

                <td class="toc-dots">
                    ........................................................................................
                </td>

                <td class="toc-page">
                    &nbsp;
                </td>

            </tr>

            @if(!empty($item['children']))

                @foreach($item['children'] as $child)

                    <tr class="toc-child">

                        <td class="toc-title-child">

                            {{ $child['title'] }}

                            <span class="toc-total">

                                ({{ $child['total_members'] }} orang)

                            </span>

                        </td>

                        <td class="toc-dots">
                            ............................................................................
                        </td>

                        <td class="toc-page">
                            &nbsp;
                        </td>

                    </tr>

                @endforeach

            @endif

        @endforeach

    </table>

</div>