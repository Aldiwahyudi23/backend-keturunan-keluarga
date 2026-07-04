<div class="cover">
    <div class="cover-main-content" style="flex-grow: 1; display: flex; flex-direction: column; justify-content: center;">
        @if(!empty($cover['logo']))
            <img src="{{ public_path($cover['logo']) }}" class="cover-logo" alt="Logo">
        @else
            <img src="{{ public_path('book/logo.png') }}" class="cover-logo" alt="Logo">
        @endif

        <div class="cover-title">
            {{ strtoupper($cover['title'] ?? '') }}
        </div>

        @if(!empty($cover['subtitle']))
            <div class="cover-subtitle">
                {{ strtoupper($cover['subtitle']) }}
            </div>
        @endif

        <div class="cover-person">
            <div class="cover-name">
                {{ strtoupper($cover['full_name'] ?? '') }}
            </div>

            @if(!empty($cover['nasab']) && !empty($cover['father_name']))
                <div class="cover-nasab">
                    {{ $cover['nasab'] }}
                </div>
                <div class="cover-father">
                    {{ strtoupper($cover['father_name']) }}
                </div>
            @endif
        </div>

        @if(!empty($cover['quote']))
            <div class="cover-divider"></div>
            <div class="cover-quote">
                {!! $cover['quote'] !!}
            </div>
        @endif
    </div>

    <div class="cover-footer">
        @if(!empty($cover['footer']))
            <div class="cover-footer-text">
                {!! $cover['footer'] !!}
            </div>
        @endif
        <div class="cover-year">
            {{ $cover['year'] ?? date('Y') }}
        </div>
    </div>
</div>