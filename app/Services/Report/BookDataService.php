<?php

namespace App\Services\Report;

use App\Models\Person;
use Carbon\Carbon;

class BookDataService
{
    public function __construct(
        protected GenealogyService $genealogyService,
        protected HistoryService $historyService,
    ) {
    }

    /**
     * Menyusun seluruh data buku sebelum dikirim ke PDF.
     */
    public function generate(
        string $uuid,
        int $maxGenerations = 0
    ): array {

        $person = Person::with('fatherRelation.parent')
            ->where('uuid', $uuid)
            ->first();

        if (!$person) {
            return [
                'success' => false,
                'message' => 'Person tidak ditemukan.',
                'data' => null,
            ];
        }

        $genealogy = $this->genealogyService
            ->generateGeneology($uuid, $maxGenerations);

        $history = $this->historyService
            ->generate($person->id);

        $toc = $this->buildTableOfContents(
            $genealogy['data']['generations']
        );

        return [

            'success' => true,

            'message' => 'Book data berhasil disusun.',

            'data' => [

                /*
                |--------------------------------------------------------------------------
                | Metadata Buku
                |--------------------------------------------------------------------------
                */

                'book' => [

                    'title' => 'Buku Silsilah Keturunan',

                    'subtitle' => $person->full_name,

                    'generated_at' => Carbon::now(),

                    'version' => '1.0',

                ],

                /*
                |--------------------------------------------------------------------------
                | Cover
                |--------------------------------------------------------------------------
                */

                'cover' => [

                    // Judul tetap
                    'title' => 'Buku Silsilah Keturunan',

                    // Nama tanpa bin/binti
                    'full_name' => $person->full_name,

                    // bin / binti
                    'nasab' => $person->nasab,

                    // Nama ayah
                    'father_name' => $person->father?->full_name,

                    // Nama lengkap dengan nasab
                    'full_name_with_nasab' => $person->full_name_with_nasab,

                    // Website
                    'website' => 'https://keturunan.keluargamahaya.com',

                    // Tahun generate
                    'year' => Carbon::now()->year,

                ],

                /*
                |--------------------------------------------------------------------------
                | Kata Pengantar
                |--------------------------------------------------------------------------
                */

                'preface' => [
                    'title' => 'Kata Pengantar',
                    'content' => $this->getPreface(),
                ],

                /*
                |--------------------------------------------------------------------------
                | Pendahuluan
                |--------------------------------------------------------------------------
                */

                'introduction' => [
                    'title' => 'Pendahuluan',
                    'content' => $this->getIntroduction(),
                ],

                /*
                |--------------------------------------------------------------------------
                | Root Person
                |--------------------------------------------------------------------------
                */

                'root_person' => $genealogy['data']['root_person'],

                /*
                |--------------------------------------------------------------------------
                | Daftar Isi
                |--------------------------------------------------------------------------
                */

                'table_of_contents' => $toc,

                /*
                |--------------------------------------------------------------------------
                | History
                |--------------------------------------------------------------------------
                */

                'history' => $history['data'],

                /*
                |--------------------------------------------------------------------------
                | Genealogy
                |--------------------------------------------------------------------------
                */

                'genealogy' => $genealogy['data']['generations'],

                /*
                |--------------------------------------------------------------------------
                | Penutup
                |--------------------------------------------------------------------------
                */
                'closing' => [
                    'title' => 'Penutup',
                    'content' => $this->getClosing(),
                ],

            ]

        ];
    }

    /**
     * Membuat daftar isi otomatis.
     */
    private function buildTableOfContents(array $generations): array
    {
        $generationItems = [];

        $this->collectGenerations($generations, $generationItems);

        return [

            [
                'title' => 'Kata Pengantar',
                'section' => 'kata-pengantar',
            ],

            [
                'title' => 'Pendahuluan',
                'section' => 'pendahuluan',
            ],

            [
                'title' => 'Sejarah Singkat',
                'section' => 'sejarah',
            ],

            [
                'title' => 'Silsilah Keturunan',
                'section' => 'genealogy',
                'children' => array_values($generationItems),
            ],

            [
                'title' => 'Penutup',
                'section' => 'penutup',
            ],

        ];
    }

    private function collectGenerations(
        array $generations,
        array &$result
    ): void {

        foreach ($generations as $generationName => $members) {

            // Simpan hanya sekali
            if (!isset($result[$generationName])) {

                $result[$generationName] = [

                    'title' => $generationName,

                    'section' => 'generation',

                    'key' => $generationName,

                    'total_members' => count($members),

                ];
            }

            foreach ($members as $member) {

                if (!empty($member['descendants'])) {

                    $this->collectGenerations(
                        $member['descendants'],
                        $result
                    );
                }
            }
        }
    }


    /**
     * Mendapatkan kata pengantar.
     */
    private function getPreface(): string
    {
        return <<<'HTML'
    <p><strong>Bismillahirrahmanirrahim</strong></p>

    <p>Alhamdulillahi Rabbil 'Alamin, segala puji hanya milik Allah SWT yang telah melimpahkan rahmat, nikmat, serta karunia-Nya sehingga penyusunan Buku Silsilah Keturunan ini dapat terselesaikan dengan baik. Shalawat dan salam semoga senantiasa tercurah kepada Nabi Muhammad SAW, suri teladan bagi seluruh umat manusia.</p>

    <p>Buku ini disusun sebagai bentuk ikhtiar untuk menjaga dan merawat jejak perjalanan sebuah keluarga dari generasi ke generasi. Setiap nama yang tertulis di dalamnya bukan sekadar rangkaian huruf, melainkan bagian dari sejarah, doa, perjuangan, pengorbanan, dan kasih sayang yang telah mengantarkan lahirnya generasi-generasi setelahnya.</p>

    <p>Seiring berjalannya waktu, keluarga akan terus bertambah besar. Anak menjadi orang tua, cucu tumbuh menjadi penerus, kemudian lahirlah cicit dan generasi-generasi berikutnya. Jarak tempat tinggal, kesibukan, bahkan perbedaan zaman sering kali membuat hubungan antarkerabat menjadi semakin renggang. Tidak sedikit di antara kita yang akhirnya hanya mengenal keluarga inti, sementara hubungan dengan saudara yang lebih jauh mulai terlupakan.</p>

    <p>Melalui buku ini, diharapkan setiap anggota keluarga dapat mengenal kembali akar keturunannya, mengetahui hubungan kekerabatan yang mungkin selama ini tidak disadari, serta menumbuhkan rasa bangga menjadi bagian dari keluarga besar yang memiliki sejarah panjang. Sebab sejauh apa pun hubungan nasab itu, selama masih berada dalam satu garis keturunan, maka ikatan persaudaraan tetaplah ada.</p>

    <p>Silsilah bukan hanya tentang mengetahui siapa ayah, kakek, atau leluhur kita. Silsilah adalah pengingat bahwa setiap manusia berasal dari mata rantai yang sama. Di dalamnya terdapat amanah untuk menjaga nama baik keluarga, mempererat tali silaturahmi, saling mendoakan, saling membantu, serta mewariskan nilai-nilai kebaikan kepada generasi yang akan datang.</p>

    <p>Semoga buku ini menjadi jembatan yang menghubungkan kembali hati-hati yang mungkin telah lama berjauhan, menjadi sumber pengetahuan bagi anak cucu di masa depan, serta menjadi pengingat bahwa kekayaan terbesar sebuah keluarga bukanlah harta yang diwariskan, melainkan persaudaraan yang tetap terjaga sepanjang masa.</p>

    <p>Tidak ada keluarga yang sempurna, namun setiap keluarga memiliki sejarah yang layak dikenang. Oleh karena itu, marilah kita bersama-sama menjaga silsilah ini agar tetap hidup, terus diperbarui, dan diwariskan kepada generasi berikutnya sebagai identitas, kebanggaan, dan amanah yang tidak ternilai harganya.</p>

    <p>Akhir kata, semoga Allah SWT senantiasa menjaga persatuan keluarga ini, melimpahkan keberkahan kepada seluruh keturunannya, mempererat tali silaturahmi di antara mereka, serta menjadikan setiap generasi sebagai generasi yang beriman, berakhlak mulia, dan bermanfaat bagi agama, bangsa, serta keluarga.</p>

    <p style="text-align:center;"><strong>Aamiin Ya Rabbal 'Alamin.</strong></p>
    HTML;
    }

    /**
     * Mendapatkan pendahuluan.
     */
    private function getIntroduction(): string
    {
        return <<<'HTML'
    <p><strong>Bismillahirrahmanirrahim</strong></p>

    <p>Perkembangan zaman membawa banyak kemudahan dalam menyimpan dan menyebarkan informasi, termasuk dalam mendokumentasikan sejarah serta silsilah sebuah keluarga. Melalui pemanfaatan teknologi, data keturunan yang dahulu hanya diwariskan secara lisan atau melalui catatan sederhana kini dapat disusun secara lebih rapi, terdokumentasi dengan baik, dan diwariskan kepada generasi-generasi berikutnya.</p>

    <p>Buku Silsilah Keturunan ini merupakan hasil penyusunan data keluarga yang telah dihimpun dan didokumentasikan dalam sebuah sistem informasi berbasis web. Seluruh informasi yang terdapat di dalam buku ini bersumber dari basis data keluarga yang dikelola secara terstruktur sehingga diharapkan mampu memberikan gambaran hubungan kekerabatan secara jelas, akurat, dan mudah dipahami.</p>

    <p>Sebagai pelengkap buku ini, seluruh data juga dapat diakses secara daring melalui website <a href="https://keturunan.keluargamahaya.com" target="_blank">https://keturunan.keluargamahaya.com</a>. Kehadiran website tersebut bertujuan agar setiap anggota keluarga dapat memperoleh informasi silsilah kapan saja dan di mana saja tanpa harus selalu membawa buku ini. Selain sebagai media dokumentasi, website juga menjadi sarana pembaruan data sehingga informasi keluarga dapat terus berkembang mengikuti bertambahnya generasi.</p>

    <p>Website tersebut menyediakan berbagai fasilitas yang dapat membantu anggota keluarga mengenal hubungan kekerabatannya. Salah satunya adalah fitur Pencarian Hubungan Keluarga, yaitu fitur yang memungkinkan pengguna mengetahui hubungan antara dirinya dengan anggota keluarga lainnya. Pencarian dapat dilakukan melalui dua cara, yaitu menggunakan Kode Anggota yang tertera pada setiap data di dalam buku ini, atau dengan memasukkan nama anggota keluarga beserta salah satu nama orang tuanya. Sistem kemudian akan menampilkan hasil pencarian beserta hubungan kekerabatannya secara otomatis.</p>

    <p>Selain itu, website juga menyajikan struktur silsilah keluarga dalam bentuk visual yang memperlihatkan hubungan dari leluhur hingga keturunan paling bawah. Dengan tampilan tersebut, anggota keluarga dapat lebih mudah memahami posisi setiap individu dalam garis keturunan serta melihat hubungan antaranggota keluarga secara menyeluruh.</p>

    <p>Untuk memudahkan pembacaan buku ini, penyusunan data dilakukan berdasarkan tingkatan generasi. Leluhur utama atau tokoh yang menjadi titik awal pembahasan tidak dihitung sebagai bagian dari generasi, melainkan dijadikan sebagai akar atau titik awal silsilah. Oleh karena itu, Generasi I dimulai dari seluruh anak kandung leluhur utama, Generasi II merupakan cucu, Generasi III merupakan cicit, dan seterusnya hingga generasi terakhir yang tersedia pada saat buku ini disusun.</p>

    <p>Setiap anggota keluarga juga diberikan nomor urut berdasarkan generasi guna memudahkan identifikasi data. Nomor tersebut menggunakan format Generasi.NomorUrut, sehingga setiap generasi memiliki penomoran tersendiri. Sebagai contoh, anggota pada Generasi I diberi nomor 1.1, 1.2, 1.3, dan seterusnya. Anggota pada Generasi II diberi nomor 2.1, 2.2, 2.3, sedangkan Generasi III menggunakan nomor 3.1, 3.2, 3.3, dan demikian seterusnya. Angka pertama menunjukkan posisi generasi, sedangkan angka setelah titik menunjukkan urutan anggota pada generasi tersebut.</p>

    <p>Sistem penomoran tersebut disusun agar setiap anggota keluarga dapat dengan mudah menemukan data pada buku maupun melakukan pencarian melalui website. Dengan adanya kesesuaian antara data cetak dan data digital, proses penelusuran hubungan keluarga menjadi lebih praktis dan mudah dipahami oleh seluruh anggota keluarga.</p>

    <p>Perlu dipahami bahwa silsilah keluarga merupakan dokumen yang bersifat dinamis. Seiring berjalannya waktu akan lahir anggota keluarga baru, terjadi pernikahan, maupun perubahan data lainnya. Oleh karena itu, isi buku ini bukanlah dokumen yang bersifat tetap, melainkan akan terus diperbarui sesuai perkembangan keluarga. Setiap pembaruan yang dilakukan pada sistem akan menjadi dasar penyusunan edisi buku berikutnya sehingga informasi yang tersimpan senantiasa terjaga keakuratan dan kelengkapannya.</p>

    <p>Semoga buku ini tidak hanya menjadi kumpulan data keturunan, tetapi juga menjadi media untuk mengenal sejarah keluarga, mempererat tali silaturahmi, menumbuhkan rasa memiliki terhadap keluarga besar, serta menjadi warisan berharga yang dapat diteruskan kepada anak, cucu, dan generasi-generasi berikutnya.</p>
    HTML;
    }

    /**
     * Mendapatkan penutup.
     */
    private function getClosing(): string
    {
        return <<<'HTML'
    <p><strong>Segala puji hanya milik Allah SWT</strong> yang telah memberikan kemudahan sehingga penyusunan Buku Silsilah Keturunan ini dapat diselesaikan. Besar harapan kami agar buku ini menjadi salah satu ikhtiar dalam menjaga sejarah keluarga, mempererat tali persaudaraan, serta menjadi sumber pengetahuan bagi generasi yang akan datang.</p>

    <p>Perlu disadari bahwa perjalanan sebuah keluarga tidak pernah berhenti. Setiap tahun akan lahir generasi baru, terjadi pernikahan, bertambahnya anggota keluarga, maupun berbagai perubahan data lainnya. Oleh karena itu, buku ini bukanlah dokumen yang bersifat final, melainkan sebuah dokumentasi yang akan terus berkembang mengikuti perjalanan keluarga dari masa ke masa.</p>

    <p>Kami mengajak seluruh anggota keluarga untuk turut berperan aktif dalam menjaga keakuratan data silsilah ini. Apabila terdapat perubahan informasi atau anggota keluarga baru, diharapkan dapat segera disampaikan melalui sistem yang telah disediakan sehingga data keluarga tetap lengkap, akurat, dan dapat diwariskan kepada generasi berikutnya.</p>

    <p>Lebih dari sekadar mengetahui hubungan nasab, semoga buku ini menjadi pengingat bahwa sebesar apa pun keluarga ini berkembang dan sejauh apa pun jarak memisahkan, kita tetap berasal dari akar yang sama. Semoga setiap anggota keluarga senantiasa menjaga nama baik keluarga, saling menghormati, saling membantu, serta terus mempererat tali silaturahmi yang telah diwariskan oleh para leluhur.</p>

    <p>Akhirnya, semoga Allah SWT senantiasa melimpahkan rahmat, keberkahan, kesehatan, serta umur yang bermanfaat kepada seluruh keluarga besar. Semoga setiap generasi yang lahir kelak menjadi generasi yang beriman, berilmu, berakhlak mulia, menjaga persatuan keluarga, dan mampu meneruskan nilai-nilai kebaikan yang telah diwariskan oleh para pendahulu.</p>

    <p style="text-align:center;"><em>"Silsilah bukan sekadar catatan tentang siapa kita berasal, melainkan pengingat tentang kepada siapa kita akan mewariskan sejarah."</em></p>

    <p style="text-align:center;"><strong>Wassalamu'alaikum Warahmatullahi Wabarakatuh</strong></p>
    HTML;
    }
}