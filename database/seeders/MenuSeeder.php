<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\MenuItem;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // Create or Update Header Menu
        $menu = Menu::updateOrCreate(
            ['location' => 'header'],
            [
                'name' => 'Main Header',
                'is_active' => true,
            ]
        );

        // Clear existing items to ensure clean slate (optional, but good for seeding)
        $menu->items()->delete();

        // 1. Marketplace
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Marketplace',
            'url' => '/marketplace',
            'order' => 1,
            'is_active' => true,
        ]);

        // 2. Programs
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Programs',
            'url' => '/programs',
            'order' => 2,
            'is_active' => true,
        ]);

        // 3. Coach
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Coach',
            'url' => '/coaches',
            'order' => 3,
            'is_active' => true,
        ]);

        // 4. Calendar
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Calendar',
            'url' => '/calendar',
            'order' => 4,
            'is_active' => true,
        ]);

        // 5. Pacers (Parent)
        $pacers = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Pacers',
            'url' => '#',
            'order' => 5,
            'is_active' => true,
        ]);

        // Pacers Children
        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $pacers->id,
            'title' => 'Find Pacers',
            'url' => '/pacers',
            'order' => 1,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $pacers->id,
            'title' => 'Register Pacer',
            'url' => '/pacer-register',
            'order' => 2,
            'is_active' => true,
        ]);

        // 6. Challenge (Parent)
        $challenge = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'Challenge',
            'url' => '#',
            'order' => 6,
            'is_active' => true,
        ]);

        // Challenge Children
        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $challenge->id,
            'title' => '40 Days Running Challenge',
            'url' => '/challenge/40days', // Assuming route matches this path
            'order' => 1,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $challenge->id,
            'title' => 'Leaderboard 40days',
            'url' => '/challenge', // Assuming route matches this path
            'order' => 2,
            'is_active' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $challenge->id,
            'title' => 'Lapor Aktivitas',
            'url' => '/challenge/submit', // Assuming route matches this path
            'order' => 3,
            'is_active' => true,
        ]);
    }
}
