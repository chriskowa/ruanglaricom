<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class ProgramLandingPageController extends Controller
{
    public function program5k()
    {
        $programs = Program::where('is_published', true)
            ->where('is_active', true)
            ->where('distance_target', '5k')
            ->with(['coach', 'city'])
            ->limit(6)
            ->get();

        $title = 'Program Latihan Lari 5K Terstruktur untuk Pemula & Intermediate | Ruang Lari';
        $metaDesc = 'Ingin lari 5K tanpa cedera atau mengejar target sub-25 menit? Temukan program latihan lari 5K terstruktur dari coach profesional Ruang Lari di sini.';
        $h1 = 'Program Latihan Lari 5K Terbaik';
        
        $content = '
            <p class="mb-4">Mencapai garis finish lari 5K pertama adalah pencapaian luar biasa bagi setiap pelari. Jarak 5 kilometer (atau sekitar 3,1 mil) adalah jarak yang sangat ideal bagi pelari pemula yang baru memulai petualangan lari mereka, maupun pelari berpengalaman yang ingin meningkatkan kecepatan (pace) mereka. Di Ruang Lari, kami menyediakan berbagai pilihan program latihan lari 5K terstruktur yang dirancang oleh coach lari profesional terverifikasi.</p>
            
            <h3 class="text-xl font-bold text-white mt-6 mb-3">Mengapa Memilih Jarak 5K?</h3>
            <p class="mb-4">Jarak 5K tidak membutuhkan volume latihan mingguan (weekly mileage) yang terlalu besar seperti marathon, sehingga sangat cocok bagi Anda yang memiliki jadwal kerja padat. Latihan 5K berfokus pada pembangunan kapasitas aerobik dasar (aerobic base building) dan pengenalan latihan kecepatan secara aman. Dengan mengikuti training plan 5K yang tepat, Anda dapat menghindari cedera lutut (runner\'s knee) dan kram otot yang sering menyerang pemula.</p>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Komponen Utama dalam Program Lari 5K</h3>
            <p class="mb-4">Setiap program 5K di Ruang Lari disusun dengan pendekatan ilmiah yang mencakup beberapa elemen kunci:</p>
            <ul class="list-disc pl-6 mb-4 space-y-2">
                <li><strong>Easy Run:</strong> Lari santai dengan pace yang nyaman (conversational pace) untuk membangun daya tahan kardiovaskular.</li>
                <li><strong>Interval Training:</strong> Lari cepat jarak pendek dengan jeda istirahat untuk meningkatkan kapasitas paru-paru (VO2 Max) dan kecepatan kaki.</li>
                <li><strong>Strength Training:</strong> Latihan kekuatan otot kaki dan core untuk menjaga stabilitas tubuh saat berlari dan mencegah cedera sendi.</li>
                <li><strong>Rest Days:</strong> Hari pemulihan total yang krusial untuk regenerasi sel otot setelah menerima beban latihan.</li>
            </ul>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Perbedaan Program Lari 5K Pemula vs. Advanced</h3>
            <p class="mb-4">Bagi pemula, program 5K biasanya berdurasi 8-12 minggu dengan kombinasi metode <em>run-walk</em> (lari-jalan) untuk membiasakan jantung dan persendian secara bertahap. Tujuannya adalah menyelesaikan 5K secara kontinu tanpa berhenti.</p>
            <p class="mb-4">Sementara bagi pelari intermediate atau advanced, program 5K berfokus pada efisiensi biomekanika, peningkatan kekuatan anaerobik, dan taktik lari untuk menembus target waktu tertentu (misalnya sub-25 menit atau bahkan sub-20 menit).</p>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Keuntungan Berlatih Bersama Coach Online</h3>
            <p class="mb-4">Menjalankan program latihan secara mandiri sering kali berujung pada overtraining karena motivasi yang berlebihan. Dengan bimbingan coach profesional secara online, intensitas latihan Anda akan terpantau dengan baik. Coach akan membantu Anda memahami pace zona detak jantung (heart rate zone) Anda dan menyesuaikan program latihan secara dinamis jika Anda merasa kelelahan.</p>
        ';

        $faqs = [
            [
                'question' => 'Apakah program 5K ini cocok untuk pemula yang belum pernah lari?',
                'answer' => 'Ya, kami memiliki program khusus pemula yang menggunakan metode kombinasi lari-jalan (run-walk) untuk membangun daya tahan secara aman tanpa membebani jantung dan sendi secara ekstrem.'
            ],
            [
                'question' => 'Berapa kali latihan dalam seminggu untuk program 5K?',
                'answer' => 'Rata-rata program 5K membutuhkan 3 hingga 4 hari latihan per minggu, dengan sisa harinya digunakan untuk latihan kekuatan ringan (strength) dan istirahat total (recovery).'
            ],
            [
                'question' => 'Apakah saya memerlukan smartwatch olahraga?',
                'answer' => 'Meskipun sangat direkomendasikan memiliki smartwatch GPS untuk melacak pace, Anda tetap bisa memulai latihan dengan menggunakan aplikasi smartphone gratis (seperti Strava) untuk mencatat jarak dan waktu lari Anda.'
            ]
        ];

        return view('programs.landing', compact('programs', 'title', 'metaDesc', 'h1', 'content', 'faqs'));
    }

    public function program5kPemula()
    {
        $programs = Program::where('is_published', true)
            ->where('is_active', true)
            ->where('distance_target', '5k')
            ->where('difficulty', 'beginner')
            ->with(['coach', 'city'])
            ->limit(6)
            ->get();

        $title = 'Program Lari 5K Pemula Terstruktur & Bebas Cedera | Ruang Lari';
        $metaDesc = 'Mulailah perjalanan lari Anda dengan training plan 5K pemula selama 8-12 minggu. Panduan langkah-demi-langkah, metode run-walk, dan tips pencegahan cedera.';
        $h1 = 'Program Lari 5K Pemula (Couch to 5K)';

        $content = '
            <p class="mb-4">Bagi Anda yang baru saja memutuskan untuk mulai berlari, jarak 5K adalah target pertama yang paling realistis dan menyenangkan. Jangan biarkan rasa takut lelah menghalangi langkah Anda. Dengan program training plan lari pemula yang tepat, siapapun dapat menyelesaikan jarak 5 km dengan penuh percaya diri.</p>
            
            <h3 class="text-xl font-bold text-white mt-6 mb-3">Metode Run-Walk: Rahasia Sukses Pelari Pemula</h3>
            <p class="mb-4">Kesalahan terbesar pelari pemula adalah langsung berlari secepat mungkin sejak menit pertama, yang berujung pada nafas ngos-ngosan dan kapok berlari lagi. Program pemula kami menggunakan metode interval lari-jalan (run-walk) yang dipopulerkan oleh atlet legendaris Jeff Galloway. Contohnya, Anda akan diajak berlari santai selama 1 menit, diikuti jalan kaki selama 2 menit, diulang beberapa kali. Secara bertahap, durasi lari akan bertambah dan durasi jalan kaki akan berkurang hingga Anda mampu berlari 5K penuh tanpa henti.</p>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Mengapa Mengikuti Program Terstruktur Sangat Penting?</h3>
            <p class="mb-4">Saat pertama kali berlari, otot, tendon, dan persendian Anda memerlukan waktu adaptasi yang lebih lama dibanding sistem pernapasan Anda. Program lari pemula di Ruang Lari didesain dengan konsep <em>progressive overload</em> secara hati-hati agar sendi lutut dan pergelangan kaki Anda tidak terkejut, meminimalkan risiko cedera shin splints (nyeri tulang kering) dan plantar fasciitis (nyeri tumit).</p>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Tips Persiapan Memulai Latihan 5K Pemula</h3>
            <ul class="list-disc pl-6 mb-4 space-y-2">
                <li><strong>Gunakan Sepatu Lari yang Tepat:</strong> Hindari menggunakan sneakers fashion. Gunakan sepatu lari (running shoes) dengan bantalan yang baik untuk meredam benturan kaki dengan aspal.</li>
                <li><strong>Fokus pada Konsistensi, Bukan Kecepatan:</strong> Jangan pedulikan seberapa lambat pace Anda berlari. Di level pemula, yang terpenting adalah melatih durasi jantung Anda bekerja (aerobic endurance).</li>
                <li><strong>Lakukan Pemanasan & Pendinginan:</strong> Selalu lakukan peregangan dinamis sebelum berlari dan peregangan statis setelah selesai berlari untuk menjaga kelenturan otot Anda.</li>
            </ul>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Dukungan Komunitas & Coach Online Ruang Lari</h3>
            <p class="mb-4">Berlatih sendirian terkadang membosankan dan menurunkan motivasi. Melalui platform Ruang Lari, Anda dapat terhubung dengan ribuan runners lainnya serta berkonsultasi langsung dengan coach profesional. Dapatkan feedback mengenai postur lari Anda dan tips nutrisi harian agar tubuh Anda tetap bugar selama masa latihan.</p>
        ';

        $faqs = [
            [
                'question' => 'Berapa lama waktu yang dibutuhkan untuk menyelesaikan program 5K pemula?',
                'answer' => 'Umumnya berkisar antara 8 hingga 12 minggu, tergantung pada tingkat kebugaran awal Anda. Latihan dilakukan sebanyak 3 kali dalam seminggu.'
            ],
            [
                'question' => 'Bagaimana jika saya melewatkan satu sesi latihan?',
                'answer' => 'Tidak perlu panik atau menggabungkan sesi latihan di hari berikutnya. Cukup lanjutkan jadwal latihan Anda sesuai urutan, dan pastikan Anda tetap mendengarkan sinyal kelelahan tubuh Anda.'
            ],
            [
                'question' => 'Apakah program lari ini gratis?',
                'answer' => 'Kami menyediakan pilihan program dasar yang 100% gratis untuk diakses, serta program premium yang didampingi langsung oleh coach bersertifikat.'
            ]
        ];

        return view('programs.landing', compact('programs', 'title', 'metaDesc', 'h1', 'content', 'faqs'));
    }

    public function program10k()
    {
        $programs = Program::where('is_published', true)
            ->where('is_active', true)
            ->where('distance_target', '10k')
            ->with(['coach', 'city'])
            ->limit(6)
            ->get();

        $title = 'Program Latihan Lari 10K Terstruktur & Peningkatan Pace | Ruang Lari';
        $metaDesc = 'Naik kelas dari 5K ke 10K dengan program latihan terstruktur 12 minggu. Tingkatkan volume lari, stamina aerobik, dan capai personal best baru bersama coach.';
        $h1 = 'Program Latihan Lari 10K';

        $content = '
            <p class="mb-4">Setelah Anda berhasil menaklukkan jarak 5K, langkah logis berikutnya adalah naik kelas ke jarak 10K (10 kilometer). Jarak 10K menawarkan tantangan baru yang memadukan kekuatan daya tahan (endurance) dan kontrol kecepatan yang matang. Di Ruang Lari, kami menghadirkan training plan 10K terstruktur untuk membantu Anda menyelesaikan jarak ini dengan nyaman atau memecahkan rekor waktu personal best baru Anda.</p>
            
            <h3 class="text-xl font-bold text-white mt-6 mb-3">Tantangan Jarak 10K: Lebih dari Sekadar Lari Dua Kali Lipat 5K</h3>
            <p class="mb-4">Banyak pelari salah kaprah dengan menganggap latihan 10K hanyalah lari sejauh 5K sebanyak dua kali. Secara fisiologis, berlari selama 50 hingga 90 menit kontinu membutuhkan sistem metabolisme energi yang lebih efisien. Tubuh Anda harus dilatih untuk membakar lemak sebagai sumber bahan bakar utama (fat adaptation) guna menghemat cadangan glikogen otot.</p>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Jenis Latihan Spesifik dalam Program 10K</h3>
            <p class="mb-4">Untuk melatih tubuh Anda secara komprehensif, program latihan 10K kami melibatkan variasi menu lari yang lebih dinamis:</p>
            <ul class="list-disc pl-6 mb-4 space-y-2">
                <li><strong>Tempo Run (Threshold Run):</strong> Lari dengan kecepatan yang "cukup menantang namun terkontrol" untuk meningkatkan ambang laktat (lactate threshold), sehingga Anda tidak cepat lelah saat berlari dengan kecepatan konstan dalam durasi lama.</li>
                <li><strong>Long Run:</strong> Lari jarak jauh mingguan di akhir pekan (antara 8-12 km) dengan pace lambat untuk membangun daya tahan otot kaki dan ketahanan mental Anda.</li>
                <li><strong>Fartlek (Speed Play):</strong> Lari dengan variasi kecepatan acak untuk melatih adaptasi jantung terhadap perubahan intensitas mendadak di lintasan.</li>
            </ul>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Mencapai Target Waktu: Sub-60 Menit 10K</h3>
            <p class="mb-4">Salah satu target terpopuler bagi pelari intermediate adalah menyelesaikan 10K di bawah 1 jam (Sub-60 menit). Ini membutuhkan pace rata-rata 6:00 per kilometer. Program latihan kami membantu Anda membagi target tersebut menjadi blok latihan mingguan yang logis, menggabungkan interval 800 meter dan latihan kekuatan sendi untuk menopang ketahanan lari Anda.</p>
        ';

        $faqs = [
            [
                'question' => 'Berapa minggu durasi program latihan lari 10K?',
                'answer' => 'Sebagian besar program lari 10K kami berdurasi 10 hingga 12 minggu. Ini memberikan waktu yang aman bagi tubuh untuk meningkatkan volume lari mingguan tanpa risiko cedera.'
            ],
            [
                'question' => 'Apakah saya harus bisa lari 5K tanpa henti sebelum ikut program 10K?',
                'answer' => 'Sangat disarankan Anda sudah terbiasa menyelesaikan lari 5K (atau lari kontinu selama 30 menit) sebelum memulai program latihan 10K agar fondasi daya tahan tubuh Anda sudah terbentuk.'
            ],
            [
                'question' => 'Bagaimana cara mencegah kram otot saat long run 10K?',
                'answer' => 'Kram otot biasanya disebabkan oleh dehidrasi, kekurangan elektrolit, atau kelelahan otot yang berlebih. Pastikan Anda mengonsumsi cairan elektrolit yang cukup sebelum berlari dan menjaga pace tetap lambat di paruh pertama lari.'
            ]
        ];

        return view('programs.landing', compact('programs', 'title', 'metaDesc', 'h1', 'content', 'faqs'));
    }

    public function programHalfMarathon()
    {
        $programs = Program::where('is_published', true)
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('distance_target', '21k')
                  ->orWhere('distance_target', 'hm');
            })
            ->with(['coach', 'city'])
            ->limit(6)
            ->get();

        $title = 'Program Latihan Half Marathon 21K Terstruktur | Ruang Lari';
        $metaDesc = 'Siapkan fisik dan mental Anda untuk race Half Marathon 21K pertama atau kejar target sub-2 jam dengan program latihan terstruktur dari coach profesional.';
        $h1 = 'Program Latihan Half Marathon (21.0975K)';

        $content = '
            <p class="mb-4">Half Marathon dengan jarak 21,0975 kilometer adalah salah satu pencapaian prestisius bagi seorang pelari. Jarak ini menuntut komitmen latihan yang serius, pemahaman hidrasi yang matang, serta strategi pembagian tenaga yang presisi. Di Ruang Lari, kami merancang program latihan Half Marathon (HM) terstruktur untuk membantu Anda menyelesaikan race dengan sehat dan bertenaga dari start hingga finish line.</p>
            
            <h3 class="text-xl font-bold text-white mt-6 mb-3">Mengapa Program Latihan Terstruktur Sangat Krusial untuk 21K?</h3>
            <p class="mb-4">Berbeda dengan jarak 5K atau 10K, berlari sejauh 21 kilometer akan menekan batas ketahanan fisik Anda hingga mendekati ambang kelelahan glikogen (biasanya terjadi setelah 90 menit berlari). Tanpa perencanaan volume lari mingguan (weekly mileage) yang terukur, Anda sangat rentan mengalami cedera sendi lutut, cedera IT Band, atau kelelahan kronis (overtraining syndrome).</p>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Komponen Latihan Half Marathon yang Efektif</h3>
            <p class="mb-4">Program HM Ruang Lari dirancang secara komprehensif menggunakan kombinasi metode latihan modern:</p>
            <ul class="list-disc pl-6 mb-4 space-y-2">
                <li><strong>Base Mileage Progression:</strong> Peningkatan jarak total lari mingguan secara bertahap (tidak lebih dari 10% per minggu) untuk membangun daya tahan otot jantung dan kekuatan otot kaki secara aman.</li>
                <li><strong>Weekly Long Run:</strong> Sesi lari jarak jauh mingguan di akhir pekan (jarak antara 12 hingga 18 km) dengan pace santai untuk melatih ketahanan mental dan adaptasi tubuh terhadap deplesi energi.</li>
                <li><strong>Race Pace Run:</strong> Lari tempo dengan target kecepatan persis seperti target race untuk membiasakan otot dengan tingkat ketegangan tertentu.</li>
                <li><strong>Tapering Phase:</strong> Pengurangan volume latihan secara terstruktur di 2 minggu terakhir menjelang race agar tubuh Anda berada dalam kondisi puncak di hari H.</li>
            </ul>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Nutrisi dan Strategi Hidrasi Selama 21K</h3>
            <p class="mb-4">Dalam latihan Half Marathon, Anda tidak hanya melatih kaki Anda, tetapi juga melatih sistem pencernaan Anda untuk menerima asupan energi saat berlari. Program kami memberikan panduan mengenai konsumsi <em>energy gel</em>, asupan karbohidrat pra-latihan (carbo-loading), serta pola hidrasi yang ideal untuk menghindari hiponatremia atau dehidrasi ekstrem.</p>
        ';

        $faqs = [
            [
                'question' => 'Berapa lama durasi program persiapan Half Marathon?',
                'answer' => 'Durasi program latihan berkisar antara 12 hingga 16 minggu, tergantung pada tingkat daya tahan dasar yang Anda miliki saat memulai program.'
            ],
            [
                'question' => 'Seberapa sering latihan long run dilakukan?',
                'answer' => 'Latihan long run dilakukan satu kali seminggu, biasanya di hari Sabtu atau Minggu, dengan jarak yang bertambah secara progresif mulai dari 10 km hingga puncaknya 18 km sebelum fase tapering.'
            ],
            [
                'question' => 'Apakah program ini cocok untuk target Sub-2 jam (Sub-2 HM)?',
                'answer' => 'Ya, kami memiliki varian program spesifik untuk pelari intermediate yang menargetkan finish Half Marathon di bawah 2 jam (pace rata-rata 5:41 per km).'
            ]
        ];

        return view('programs.landing', compact('programs', 'title', 'metaDesc', 'h1', 'content', 'faqs'));
    }

    public function programSub20()
    {
        $programs = Program::where('is_published', true)
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('slug', 'like', '%sub-20%')
                  ->orWhere('title', 'like', '%sub 20%')
                  ->orWhere('distance_target', '5k');
            })
            ->with(['coach', 'city'])
            ->limit(6)
            ->get();

        $title = 'Program Latihan Lari 5K Sub-20 Menit | Ruang Lari';
        $metaDesc = 'Pecahkan rekor lari 5K Anda di bawah 20 menit dengan program latihan intensitas tinggi 10-12 minggu. Fokus pada VO2 Max, ambang laktat, dan kekuatan kaki.';
        $h1 = 'Program Lari 5K Sub-20 Menit';

        $content = '
            <p class="mb-4">Menyelesaikan lari 5K di bawah 20 menit (Sub-20) adalah target idaman sekaligus penanda bahwa Anda telah memasuki kategori pelari cepat (advanced runner). Ini membutuhkan pace rata-rata 4:00 per kilometer secara konsisten selama 5 kilometer penuh. Target ini tidak bisa dicapai hanya dengan lari santai harian. Anda memerlukan program latihan berintensitas tinggi, kedisiplinan tingkat tinggi, serta pemahaman ilmiah mengenai ambang laktat tubuh Anda.</p>
            
            <h3 class="text-xl font-bold text-white mt-6 mb-3">Tuntutan Fisiologis Lari 5K Sub-20</h3>
            <p class="mb-4">Berlari dengan pace 4:00 menit/km menuntut kapasitas VO2 Max yang tinggi dan efisiensi biomekanika lari yang sempurna. Tubuh Anda akan menghasilkan asam laktat dengan sangat cepat, sehingga Anda harus melatih otot Anda untuk tetap bekerja optimal dalam kondisi asam (tolerance training). Di sinilah program spesifik Sub-20 Ruang Lari berperan penting membantu Anda melakukan adaptasi fisiologis tersebut secara terukur.</p>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Menu Latihan Kunci dalam Program Sub-20</h3>
            <p class="mb-4">Program latihan ini didominasi oleh latihan kecepatan berkualitas tinggi:</p>
            <ul class="list-disc pl-6 mb-4 space-y-2">
                <li><strong>VO2 Max Intervals:</strong> Latihan interval berat seperti 5 x 1000 meter dengan pace target 5K (sekitar 3:50 - 3:55 per km) dengan jeda jogging ringan 3 menit.</li>
                <li><strong>Lactate Threshold Runs:</strong> Lari tempo sejauh 4-6 km dengan pace sedikit lebih lambat dari target 5K (pace 4:15 - 4:20 per km) untuk melatih efisiensi pernapasan.</li>
                <li><strong>Hill Repeats:</strong> Lari menanjak berulang kali untuk melatih kekuatan eksplosif otot kuadrisep, betis, dan daya dorong kaki.</li>
                <li><strong>Speed Endurance:</strong> Lari interval jarak pendek (misal 200m atau 400m) dengan kecepatan maksimal untuk melatih frekuensi langkah kaki (cadence).</li>
            </ul>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Pentingnya Pemulihan & Pencegahan Cedera di Level Advanced</h3>
            <p class="mb-4">Karena intensitas latihan yang sangat tinggi, risiko cedera otot seperti hamstring strain, tendonitis achilles, dan shin splints meningkat berkali-lipat. Program latihan Sub-20 di Ruang Lari mengintegrasikan latihan kekuatan khusus (hip mobility, hamstring strengthening) serta menjadwalkan hari pemulihan aktif (active recovery) yang tidak boleh Anda lewatkan.</p>
        ';

        $faqs = [
            [
                'question' => 'Apa syarat awal untuk memulai program 5K Sub-20?',
                'answer' => 'Sangat direkomendasikan Anda sudah memiliki catatan waktu 5K pribadi (PB) setidaknya di bawah 22 menit atau 23 menit sebelum memulai program ini agar transisi latihan tidak terlalu ekstrem.'
            ],
            [
                'question' => 'Berapa kali latihan lari dalam seminggu?',
                'answer' => 'Program ini memerlukan 4 hingga 5 hari latihan per minggu, mencakup latihan kecepatan, lari tempo, easy run, dan long run.'
            ],
            [
                'question' => 'Apakah program ini membantu memperbaiki postur lari (running form)?',
                'answer' => 'Ya, coach kami akan memberikan panduan teknik biomekanika lari, termasuk foot strike yang efisien dan peningkatan cadence (frekuensi langkah kaki) untuk menghemat energi Anda saat berlari cepat.'
            ]
        ];

        return view('programs.landing', compact('programs', 'title', 'metaDesc', 'h1', 'content', 'faqs'));
    }

    public function coachOnline()
    {
        $programs = Program::where('is_published', true)
            ->where('is_active', true)
            ->whereHas('coach', function($q) {
                $q->where('role', 'coach');
            })
            ->with(['coach', 'city'])
            ->limit(6)
            ->get();

        $title = 'Coach Lari Online - Pelatihan Personal Terverifikasi | Ruang Lari';
        $metaDesc = 'Dapatkan training plan lari personal yang disesuaikan khusus dengan kondisi fisik, detak jantung, dan target race Anda bersama Coach Lari Online Ruang Lari.';
        $h1 = 'Layanan Coach Lari Online Profesional';

        $content = '
            <p class="mb-4">Apakah Anda sering mengalami cedera lari? Atau merasa progres kecepatan Anda jalan di tempat meskipun sudah rajin berlari? Setiap tubuh manusia memiliki keunikan biomekanika, kapasitas paru-paru, dan daya pemulihan yang berbeda-beda. Di sinilah pentingnya memiliki bimbingan dari seorang Coach Lari Online profesional. Di Ruang Lari, kami menghubungkan Anda dengan pelatih lari berlisensi resmi yang siap membantu Anda berlari lebih cepat, lebih jauh, dan lebih aman.</p>
            
            <h3 class="text-xl font-bold text-white mt-6 mb-3">Mengapa Berlatih dengan Coach Lari Online?</h3>
            <p class="mb-4">Mendownload template training plan umum dari internet sering kali tidak menyelesaikan masalah karena program tersebut tidak memperhitungkan kesibukan harian, riwayat cedera, atau tingkat detak jantung (heart rate) Anda. Coach lari online di Ruang Lari memberikan pendekatan personal yang ilmiah:</p>
            <ul class="list-disc pl-6 mb-4 space-y-2">
                <li><strong>Program Latihan Custom:</strong> Program latihan Anda dibuat secara dinamis menyesuaikan hasil tes kebugaran awal Anda (seperti VDOT test atau detak jantung maksimum).</li>
                <li><strong>Analisis Postur Lari (Running Form Analysis):</strong> Anda dapat mengirimkan video saat berlari, dan coach akan menganalisis efisiensi langkah kaki, kemiringan tubuh, dan gerakan lengan Anda.</li>
                <li><strong>Pemantauan Berkala:</strong> Coach akan meninjau data latihan harian Anda yang tersinkronisasi dari smartwatch Garmin, Coros, atau Strava, lalu memberikan masukan berharga.</li>
                <li><strong>Konsultasi Nutrisi & Strategi Race:</strong> Dapatkan saran mengenai makanan sebelum latihan, cara mengatasi dehidrasi, hingga taktik membagi energi saat race day.</li>
            </ul>

            <h3 class="text-xl font-bold text-white mt-6 mb-3">Bagaimana Cara Kerja Pembinaan Online di Ruang Lari?</h3>
            <p class="mb-4">Proses pembinaan lari online kami didesain sangat praktis untuk menunjang kesibukan harian Anda:</p>
            <ol class="list-decimal pl-6 mb-4 space-y-2">
                <li><strong>Konsultasi Awal:</strong> Anda melakukan wawancara mendalam mengenai target lari Anda (misal finish marathon pertama) dan riwayat kesehatan.</li>
                <li><strong>Pengiriman Program Mingguan:</strong> Coach mengirimkan program latihan mingguan langsung ke dashboard personal Anda di Ruang Lari.</li>
                <li><strong>Sinkronisasi Aktivitas Lari:</strong> Anda melakukan latihan lari dan menyinkronkan data GPS lari Anda ke platform kami.</li>
                <li><strong>Feedback Mingguan:</strong> Coach menganalisis data pace, cadence, dan heart rate Anda, lalu melakukan penyesuaian jika program terlalu berat atau terlalu ringan.</li>
            </ol>
        ';

        $faqs = [
            [
                'question' => 'Bagaimana cara berkonsultasi dengan coach lari?',
                'answer' => 'Anda bisa memilih profil coach di halaman daftar coach kami, melihat program yang mereka buat, lalu menekan tombol konsultasi untuk memulai obrolan langsung atau berlangganan bimbingan privat.'
            ],
            [
                'question' => 'Apakah bimbingan online ini seefektif latihan tatap muka?',
                'answer' => 'Ya. Dengan bantuan data GPS yang akurat (seperti pace, heart rate, dan cadence), coach kami dapat mendiagnosis tingkat kelelahan dan performa Anda dengan sangat presisi, hampir sama efektifnya dengan bimbingan tatap muka.'
            ],
            [
                'question' => 'Apakah program ini cocok untuk persiapan event lari jarak jauh?',
                'answer' => 'Tentu. Coach kami memiliki spesialisasi melatih dari jarak 5K, 10K, Half Marathon, Full Marathon, bahkan Ultra Marathon.'
            ]
        ];

        return view('programs.landing', compact('programs', 'title', 'metaDesc', 'h1', 'content', 'faqs'));
    }
}
