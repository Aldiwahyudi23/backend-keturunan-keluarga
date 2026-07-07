<div class="chapter">
    <h1 class="chapter-title">
        {{ $section['title'] ?? '' }}
    </h1>

    @php
        $data = $section['data'] ?? [];
        $root = $data['root_person'] ?? [];
        $generations = $data['generations'] ?? [];
    @endphp

    @if(!empty($root))
    <div class="generation">
        <h2 class="generation-title">Leluhur Utama</h2>
        <div class="member-card root-card">
            <table class="member-card-inner">
                <tr>
                    <td class="member-card-left" style="text-align: center;">
                        @if(!empty($root['photo_path']))
                            <div style="margin-bottom: 16px;">
                                <img src="{{ storage_path('app/public/'.$root['photo_path']) }}"
                                     style="width: 160px; height: 160px; object-fit: cover; border: 3px solid #c9a84c; border-radius: 50%;"
                                     alt="Foto">
                            </div>
                        @endif

                        <div class="member-name" style="font-size:15pt; margin-bottom:12px;">
                            {{ $root['full_name_with_nasab'] ?? '-' }}
                        </div>

                        <table class="member-table" style="margin: 0 auto; width: auto;">
                            <tr>
                                <td><strong>Kode Anggota</strong></td>
                                <td>:</td>
                                <td>{{ $root['person_code'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Kelamin</strong></td>
                                <td>:</td>
                                <td>{{ $root['gender'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Lahir</strong></td>
                                <td>:</td>
                                <td>{{ $root['birth_date'] ?? '-' }}</td>
                            </tr>
                            @if(!empty($root['death_date']) && $root['death_date'] !== '-' && $root['death_date'] !== null)
                            <tr>
                                <td><strong>Wafat</strong></td>
                                <td>:</td>
                                <td>{{ $root['death_date'] }}</td>
                            </tr>
                            @endif
                        </table>

                        @if(!empty($root['bio']))
                            <div class="member-bio-text">
                                {{ $root['bio'] }}
                            </div>
                        @endif
                    </td>

                    <td class="member-card-right">
                        @if(!empty($root['marriage_summary']))
                            <div class="right-section">
                                <div class="right-section-title">Pasangan</div>
                                <div style="font-size:11pt; font-weight:600; color:#1a2a3a;">
                                    {{ $root['marriage_summary'] }}
                                </div>
                            </div>
                        @endif

                        @php
                            $rootMarriages = $root['marriages'] ?? [];
                        @endphp
                        @if(!empty($rootMarriages))
                            @foreach($rootMarriages as $marriage)
                                <div class="right-section">
                                    <div class="right-section-title">
                                        Pernikahan {{ $marriage['order'] ?? '' }}
                                        @if(!empty($marriage['status']))
                                            <span style="font-weight:400; color:#8a7a5a;">({{ $marriage['status'] }})</span>
                                        @endif
                                    </div>

                                    @if(!empty($marriage['spouse']['full_name']))
                                        <div class="spouse-name">{{ $marriage['spouse']['full_name'] }}</div>
                                    @endif
                                    @if(!empty($marriage['marriage_date']))
                                        <div class="spouse-detail">Menikah: {{ $marriage['marriage_date'] }}</div>
                                    @endif
                                    @if(!empty($marriage['divorce_date']) && $marriage['divorce_date'] !== '-')
                                        <div class="spouse-detail">Bercerai: {{ $marriage['divorce_date'] }}</div>
                                    @endif
                                    @if(isset($marriage['children_count']) && $marriage['children_count'] > 0)
                                        <div class="spouse-detail">Anak: {{ $marriage['children_count'] }} orang</div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    {{-- ===================================================== --}}
    {{-- GENERASI --}}
    {{-- ===================================================== --}}
    @if(!empty($generations))
        @foreach($generations as $generation => $members)
            <div class="generation">
                <h2 class="generation-title">
                    {{ $generation }}
                </h2>

                @foreach($members as $memberGroup)
                    @if(!empty($memberGroup['parent_info']))
                        <div style="margin: 10px 0 14px; font-style:italic; color:#8a7a5a; padding: 6px 14px; background:#faf8f3;">
                            {{ $memberGroup['parent_info'] }}
                        </div>
                    @endif

                    @foreach($memberGroup['members'] ?? [] as $member)
                        <div class="member-card">
                            <table class="member-card-inner">
                                <tr>
                                    {{-- LEFT: Member info --}}
                                    <td class="member-card-left">
                                        <div class="member-heading">
                                            <span class="member-number">
                                                {{ $member['number'] ?? '' }}
                                            </span>
                                            <span class="member-name">
                                                {{ $member['full_name_with_nasab'] ?? '-' }}
                                            </span>
                                        </div>

                                        @if(!empty($member['photo_path']))
                                            <div style="text-align: center; margin-bottom: 10px;">
                                                <img src="{{ storage_path('app/public/'.$member['photo_path']) }}"
                                                     style="width: 90px; height: 90px; object-fit: cover; border: 2px solid #d4c9a8; border-radius: 50%;"
                                                     alt="Foto">
                                            </div>
                                        @endif

                                        <table class="member-table">
                                            <tr>
                                                <td><strong>Kode Anggota</strong></td>
                                                <td>:</td>
                                                <td>{{ $member['person_code'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Jenis Kelamin</strong></td>
                                                <td>:</td>
                                                <td>{{ $member['gender'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Lahir</strong></td>
                                                <td>:</td>
                                                <td>{{ $member['birth_date'] ?? '-' }}</td>
                                            </tr>
                                            @if(!empty($member['death_date']) && $member['death_date'] !== '-' && $member['death_date'] !== null)
                                            <tr>
                                                <td><strong>Wafat</strong></td>
                                                <td>:</td>
                                                <td>{{ $member['death_date'] ?? '-' }}</td>
                                            </tr>
                                            @endif
                                        </table>

                                        @if(!empty($member['bio']))
                                            <div class="member-bio-text">
                                                {{ $member['bio'] }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- RIGHT: Spouse + Children --}}
                                    <td class="member-card-right">
                                        @if(!empty($member['marriages']))
                                            @foreach($member['marriages'] as $marriage)
                                                <div class="right-section">
                                                    <div class="right-section-title">
                                                        Pasangan {{ $marriage['order'] ?? '' }}
                                                        @if(!empty($marriage['status']))
                                                            <span style="font-weight:400; color:#8a7a5a;">({{ $marriage['status'] }})</span>
                                                        @endif
                                                    </div>

                                                    @if(!empty($marriage['spouse']['full_name']))
                                                        <div class="spouse-name">{{ $marriage['spouse']['full_name'] }}</div>
                                                    @endif
                                                    @if(!empty($marriage['marriage_date']))
                                                        <div class="spouse-detail">Menikah: {{ $marriage['marriage_date'] }}</div>
                                                    @endif
                                                    @if(!empty($marriage['divorce_date']) && $marriage['divorce_date'] !== '-')
                                                        <div class="spouse-detail">Bercerai: {{ $marriage['divorce_date'] }}</div>
                                                    @endif

                                                    @if(!empty($member['children']) && count($member['children']) > 0)
                                                        <div style="margin-top:6px;">
                                                            <div style="font-size:9pt; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#c9a84c; margin-bottom:3px;">
                                                                Anak ({{ $member['children_count'] ?? count($member['children']) }})
                                                            </div>
                                                            <ul class="children-list">
                                                                @foreach($member['children'] as $child)
                                                                    <li>
                                                                        {{ $child['full_name'] ?? '-' }}
                                                                        @if(!empty($child['order']))
                                                                            <span style="color:#8a7a5a;">(Anak ke-{{ $child['order'] }})</span>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="right-section">
                                                <div class="right-section-title">Informasi Keluarga</div>
                                                <div style="font-style:italic; color:#8a7a5a; font-size:10pt;">
                                                    Belum terdapat data pasangan dan anak.
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                @endforeach
            </div>

            @unless($loop->last)
                <div class="page-break"></div>
            @endunless
        @endforeach
    @endif
</div>
