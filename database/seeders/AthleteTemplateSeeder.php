<?php

namespace Database\Seeders;

use App\Models\KalenderPelari\AthleteTemplate;
use Illuminate\Database\Seeder;

class AthleteTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $defaultBgs = [
            '/images/kalender-pelari/bg/815920233_18619567672013478_7707120302547332417_n.jpg', // Sampul
            '/images/kalender-pelari/bg/819207920_18620464486013478_340093240344695805_n.jpg',  // Jan
            '/images/kalender-pelari/bg/825352127_18622491895013478_5874230830227099846_n.jpg',  // Feb
            '/images/kalender-pelari/bg/830540436_18146023228584739_5377399408712608694_n.jpg',  // Mar
            '/images/kalender-pelari/bg/840363425_18625537549013478_2717575911219471235_n.jpg',  // Apr
            '/images/kalender-pelari/bg/849697776_18628898209013478_5441502358773315659_n.jpg',  // Mei
            '/images/kalender-pelari/bg/849773131_18629307187013478_7691590249581363802_n.jpg',  // Jun
            '/images/kalender-pelari/bg/854624223_18630006562013478_8654858690142940290_n.jpg',  // Jul
            '/images/kalender-pelari/bg/862585476_18630953857013478_6407822674789936792_n.jpg',  // Agu
            '/images/kalender-pelari/bg/863019870_18631378483013478_832084893925751159_n.jpg',   // Sep
            '/images/kalender-pelari/bg/870397930_18632498980013478_3637668635151763593_n.jpg',  // Okt
            '/images/kalender-pelari/bg/874399957_18632800849013478_128493180862209871_n.jpg',   // Nov
            '/images/kalender-pelari/bg/880145360_18633549967013478_5585936414933662375_n.jpg',  // Des
        ];

        $templates = [
            [
                'name' => 'Agus Prayogo - Marathon Legend',
                'slug' => 'agus-prayogo',
                'category' => 'Nasional',
                'bio' => 'Peraih 7 Medali Emas SEA Games cabang lari jarak jauh dan maraton Indonesia. Sosok panutan dedikasi dan konsistensi atletik nasional.',
                'cover_image' => $defaultBgs[0],
                'photos' => $defaultBgs,
                'accent_color' => '#E63946',
                'paper_color' => 'white',
                'default_font' => 'Inter Tight',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Odekta Elvina Naibaho - Ratu Jarak Jauh',
                'slug' => 'odekta-naibaho',
                'category' => 'Nasional',
                'bio' => 'Juara SEA Games Marathon Putri berturut-turut. Inspirasi ketangguhan dan semangat pantang menyerah pelari putri Indonesia.',
                'cover_image' => $defaultBgs[1],
                'photos' => array_reverse($defaultBgs),
                'accent_color' => '#0284c7',
                'paper_color' => 'white',
                'default_font' => 'Sora',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Eliud Kipchoge - No Human Is Limited',
                'slug' => 'eliud-kipchoge',
                'category' => 'Dunia',
                'bio' => '2x Juara Marathon Olimpiade & pelari pertama di dunia yang menembus batas sub-2 maraton. Filosofi hidup: No Human Is Limited.',
                'cover_image' => $defaultBgs[3],
                'photos' => $defaultBgs,
                'accent_color' => '#16a34a',
                'paper_color' => 'white',
                'default_font' => 'Inter',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $t) {
            AthleteTemplate::updateOrCreate(
                ['slug' => $t['slug']],
                $t
            );
        }
    }
}

