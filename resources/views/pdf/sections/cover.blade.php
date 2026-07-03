<div class="cover">

    <img
        src="{{ public_path('book/logo.png') }}"
        class="cover-logo"
        alt="Logo">

    <div class="cover-title">
        {{ strtoupper($cover['title']) }}
    </div>

    <div class="cover-subtitle">
        KELUARGA BESAR
    </div>

    <div class="cover-person">

        <div class="cover-name">
            {{ $cover['full_name'] }}
        </div>

        @if($cover['nasab'] && $cover['father_name'])
            <div class="cover-nasab">
                {{ $cover['nasab'] }}
            </div>

            <div class="cover-father">
                {{ $cover['father_name'] }}
            </div>
        @endif

    </div>

    <div class="cover-divider"></div>

    <div class="cover-quote">

        "Silsilah bukan sekadar catatan tentang siapa kita berasal,
        melainkan pengingat tentang kepada siapa kita akan
        mewariskan sejarah."

    </div>

    <div class="cover-divider"></div>

    <div class="cover-footer">

        <div class="cover-website">
            {{ $cover['website'] }}
        </div>

        <div class="cover-year">
            {{ $cover['year'] }}
        </div>

    </div>

</div>