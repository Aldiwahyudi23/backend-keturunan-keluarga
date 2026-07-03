<div class="chapter">

    <h1 class="chapter-title">
        Silsilah Keturunan
    </h1>

    {{-- ===================================================== --}}
    {{-- ROOT PERSON --}}
    {{-- ===================================================== --}}

    <div class="generation">

        <h2 class="generation-title">
            Leluhur Utama
        </h2>

        <div class="member member-root">

            <div class="member-heading">

                <span class="member-name">
                    {{ $root_person['full_name_with_nasab'] }}
                </span>

            </div>

            <div class="member-info">

                <div>
                    <span class="label">Kode Anggota</span>
                    <span class="value">: {{ $root_person['person_code'] }}</span>
                </div>

                <div>
                    <span class="label">Jenis Kelamin</span>
                    <span class="value">: {{ $root_person['gender'] }}</span>
                </div>

                <div>
                    <span class="label">Lahir</span>
                    <span class="value">: {{ $root_person['birth_date'] }}</span>
                </div>

                <div>
                    <span class="label">Wafat</span>
                    <span class="value">: {{ $root_person['death_date'] }}</span>
                </div>

                @if(!empty($root_person['spouse']))
                <div>
                    <span class="label">Pasangan</span>
                    <span class="value">: {{ $root_person['spouse']['full_name'] }}</span>
                </div>
                @endif

                @if(!empty($root_person['marriage_info']))
                <div>
                    <span class="label">Menikah</span>
                    <span class="value">: {{ $root_person['marriage_info']['marriage_date'] }}</span>
                </div>
                @endif

            </div>

            @if(!empty($history['person']['photo_path']))
                <div class="root-photo-wrapper">

                    <img
                        src="{{ public_path($history['person']['photo_path']) }}"
                        class="root-photo"
                    >

                </div>
            @endif

            @if(!empty($history['person']['bio']))
                <div class="root-bio">

                    <h3>Biografi Singkat</h3>

                    <p>
                        {{ $history['person']['bio'] }}
                    </p>

                </div>
            @endif

        </div>

    </div>
    
    <div class="page-break"></div>

    {{-- ===================================================== --}}
    {{-- GENERASI --}}
    {{-- ===================================================== --}}

    @foreach($genealogy as $generation => $members)

        <div class="generation">

            <h2 class="generation-title">
                {{ $generation }}
            </h2>

            @foreach($members as $member)

                <div class="member">

                    <div class="member-heading">

                        <span class="member-number">
                            {{ $member['number'] }}
                        </span>

                        <span class="member-name">
                            {{ $member['full_name_with_nasab'] }}
                        </span>

                    </div>

                    <div class="member-info">

                        <div>
                            <span class="label">Kode Anggota</span>
                            <span class="value">: {{ $member['person_code'] }}</span>
                        </div>

                        <div>
                            <span class="label">Jenis Kelamin</span>
                            <span class="value">: {{ $member['gender'] }}</span>
                        </div>

                        <div>
                            <span class="label">Lahir</span>
                            <span class="value">: {{ $member['birth_date'] }}</span>
                        </div>

                        <div>
                            <span class="label">Wafat</span>
                            <span class="value">: {{ $member['death_date'] }}</span>
                        </div>

                        @if(!empty($member['spouse']))
                        <div>
                            <span class="label">Pasangan</span>
                            <span class="value">: {{ $member['spouse']['full_name'] }}</span>
                        </div>
                        @endif

                        @if(!empty($member['marriage_info']))
                        <div>
                            <span class="label">Menikah</span>
                            <span class="value">: {{ $member['marriage_info']['marriage_date'] }}</span>
                        </div>
                        @endif

                    </div>

                    @if($member['children_count'])

                        <div class="children">

                            <div class="children-title">

                                Anak ({{ $member['children_count'] }})

                            </div>

                            <ol class="children-list">

                                @foreach($member['children'] as $child)

                                    <li>

                                        {{ $child['full_name'] }}

                                    </li>

                                @endforeach

                            </ol>

                        </div>

                    @endif

                </div>

            @endforeach

        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif

    @endforeach

</div>