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

        <div class="member-card root-card" style="text-align: center;">
            @if(!empty($root['photo_path']))
                <div style="margin-bottom: 16px;">
                    <img src="{{ storage_path('app/public/'.$root['photo_path']) }}"
                         style="width: 180px; height: 180px; object-fit: cover; border: 3px solid #3498db; border-radius: 50%;"
                         alt="Foto">
                </div>
            @endif

            <div class="member-name" style="font-size:16pt; margin-bottom:12px;">
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

             @if(!empty($root['marriage_summary']))
                 <div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid #e9ecef; text-align: justify; font-style: italic; color: #6c757d;">
                    {{ $root['marriage_summary'] }}
                </div>
            @endif

            @if(!empty($root['bio']))
                <div style="margin-top: 8px; padding-top: 6px; border-top: 1px solid #e9ecef; text-align: justify; font-style: italic; color: #6c757d;">
                    {{ $root['bio'] }}
                </div>
            @endif
        </div>
    </div>
    @endif

    @if(!empty($generations))
        @foreach($generations as $generation => $members)
            <div class="generation">
                <h2 class="generation-title">
                    {{ $generation }}
                </h2>

                @foreach($members as $memberGroup)
                    @if(!empty($memberGroup['parent_info']))
                        <div style="margin: 8px 0 10px; font-style:italic; color:#6c757d; padding: 6px 12px; background:#f8f9fa; border-radius:4px;">
                            {{ $memberGroup['parent_info'] }}
                        </div>
                    @endif

                    @foreach($memberGroup['members'] ?? [] as $member)
                        <div class="member-card">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td valign="top">
                                        <div class="member-heading">
                                            <span class="member-number">
                                                {{ $member['number'] ?? '' }}
                                            </span>
                                            <span class="member-name">
                                                {{ $member['full_name_with_nasab'] ?? '-' }}
                                            </span>
                                        </div>

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

                                            @if(!empty($member['marriages']))
                                                @foreach($member['marriages'] as $marriage)
                                                    <tr>
                                                        <td colspan="3" style="padding-top:2px;">
                                                            <div style="border-top:1px solid #e9ecef; padding-top:3px;">
                                                                <strong style="color:#3498db;">Pernikahan {{ $marriage['order'] ?? '' }}</strong>
                                                                @if(!empty($marriage['status']))
                                                                    <span style="color:#6c757d;">({{ $marriage['status'] }})</span>
                                                                @endif
                                                            </div>
                                                            @if(!empty($marriage['spouse']['full_name']))
                                                                <div>Pasangan: {{ $marriage['spouse']['full_name'] }}</div>
                                                            @endif
                                                            @if(!empty($marriage['marriage_date']))
                                                                <div>Tanggal Nikah: {{ $marriage['marriage_date'] }}</div>
                                                            @endif
                                                            @if(!empty($marriage['divorce_date']) && $marriage['divorce_date'] !== '-')
                                                                <div>Tanggal Cerai: {{ $marriage['divorce_date'] }}</div>
                                                            @endif
                                                            @if(isset($marriage['children_count']))
                                                                <div>Jumlah Anak: {{ $marriage['children_count'] }}</div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </table>
                                    </td>

                                    <td width="110" align="right" valign="top">
                                        @if(!empty($member['photo_path']))
                                            <img src="{{ storage_path('app/public/'.$member['photo_path']) }}"
                                                 class="member-photo" alt="Foto">
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            @if(!empty($member['children']) && count($member['children']) > 0)
                                <div class="member-children">
                                    <div class="children-title">
                                        Anak ({{ $member['children_count'] ?? count($member['children']) }})
                                    </div>
                                    <ul class="children-list">
                                        @foreach($member['children'] as $child)
                                            <li>
                                                {{ $child['full_name'] ?? '-' }}
                                                @if(!empty($child['order']))
                                                    (Anak ke-{{ $child['order'] }})
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
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
