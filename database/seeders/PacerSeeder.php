<?php

namespace Database\Seeders;

use App\Models\Pacer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PacerSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(6)->get();
        if ($users->isEmpty()) return;

        $categories = ['HM (21K)', 'FM (42K)', '10K'];
        $paces = ['04:15','05:00','05:30','06:00','03:55','07:00'];

        foreach ($users as $i => $user) {
            $nickname = ['The Rocket','Diesel Engine','Pace Master','Cruiser','Speedster','Consistent'][$i % 6];
            $cat = $categories[$i % count($categories)];
            $pace = $paces[$i % count($paces)];
            $slugBase = $user->name.'-'.$nickname;
            $slug = Str::slug($slugBase);
            $counter = 1;
            while (Pacer::where('seo_slug', $slug)->exists()) {
                $slug = Str::slug($slugBase.'-'.$counter);
                $counter++;
            }

            Pacer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'seo_slug' => $slug,
                    'nickname' => $nickname,
                    'category' => $cat,
                    'pace' => $pace,
                    'image_url' => 'https://images.unsplash.com/photo-1552674605-5d28c4e1902c?auto=format&fit=crop&q=80&w=800',
                    'whatsapp' => '6281234567890',
                    'verified' => $i % 2 === 0,
                    'total_races' => rand(5, 40),
                    'bio' => 'Pacer profesional. Fokus pada split negatif dan pacing konsisten.',
                    'stats' => [
                        'pb5k' => '18:30',
                        'pb10k' => '38:45',
                        'pbfm' => '3:05:00'
                    ],
                    'tags' => ['NegativeSplit','Marathon','Coach']
                ]
            );
        }
    }
}

