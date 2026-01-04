<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $jawaTimur = Province::where('name', 'Jawa Timur')->first();

        if (! $jawaTimur) {
            $this->command->error('Province Jawa Timur tidak ditemukan. Jalankan ProvinceSeeder terlebih dahulu.');

            return;
        }

        $cities = [
            ['name' => 'Malang Kota'],
            ['name' => 'Malang Kabupaten'],
            ['name' => 'Surabaya'],
            ['name' => 'Jakarta'],
            ['name' => 'Bandung'],
        ];

        foreach ($cities as $city) {
            City::firstOrCreate(
                ['province_id' => $jawaTimur->id, 'name' => $city['name']],
                ['province_id' => $jawaTimur->id, 'name' => $city['name']]
            );
        }
    }
}
