<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProvinceSeeder::class,
            CitySeeder::class,
            FeeConfigSeeder::class,
            AdminUserSeeder::class,
            UserSeeder::class,
            ProgramSeeder::class,
            EventSeeder::class,
            PacerSeeder::class,
        ]);
    }
}
