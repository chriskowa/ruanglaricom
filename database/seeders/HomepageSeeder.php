<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageTemplate;

class HomepageSeeder extends Seeder
{
    public function run(): void
    {
        $template = PageTemplate::where('slug', 'event-discovery-homepage')->first();
        
        if ($template) {
            Page::updateOrCreate(
                ['slug' => 'homepage'],
                [
                    'title' => 'RuangLari - Platform Event Lari Terlengkap',
                    'template_id' => $template->id,
                    'status' => 'published',
                    'template_data' => [
                        'headline' => 'Temukan Event Lari Terbaik di Indonesia',
                        'subheadline' => 'Cari, bandingkan, dan daftar event lari di seluruh Indonesia dengan mudah.',
                        'hero_image' => '/images/default-hero-light.jpg'
                    ]
                ]
            );
        }
    }
}
