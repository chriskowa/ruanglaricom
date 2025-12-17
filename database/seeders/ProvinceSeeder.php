<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            ['name' => 'Jawa Timur'],
        ];

        foreach ($provinces as $province) {
            Province::firstOrCreate(['name' => $province['name']], $province);
        }
    }
}
