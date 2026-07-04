
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    @include('pdf.book.style.classic')
</head>

<body>

{{-- =====================================================
     COVER
===================================================== --}}
@if(!empty($book['show_cover']) && $book['show_cover'] === true)
    @include('pdf.book.sections.classic.cover')
    @if(count($sections))
        <div class="page-break"></div>
    @endif
@endif

{{-- =====================================================
     CONTENT
===================================================== --}}
@foreach($sections as $section)
    {{-- Skip TOC if not showing table of contents --}}
    @if(isset($section['key']) && $section['key'] === 'toc' && (empty($book['show_table_of_contents']) || $book['show_table_of_contents'] === false))
        @continue
    @endif

    @if(isset($section['type']))
        @switch($section['type'])
            @case('text')
                @include('pdf.book.sections.classic.text', ['section' => $section])
            @break

            @case('dynamic')
                @if(isset($section['key']))
                    @if($section['key'] === 'toc')
                        @include('pdf.book.sections.classic.toc', ['section' => $section, 'sections' => $sections])
                    @elseif($section['key'] === 'history')
                        @include('pdf.book.sections.classic.history', ['section' => $section])
                    @elseif($section['key'] === 'genealogy')
                        @include('pdf.book.sections.classic.genealogy', ['section' => $section])
                    @endif
                @endif
            @break
        @endswitch
    @endif

    @unless($loop->last)
        <div class="page-break"></div>
    @endunless
@endforeach

</body>
</html>