<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition()
    {
        return [
            'province_id' => Province::factory(),
            'name' => $this->faker->city,
            'seourl' => $this->faker->slug,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
        ];
    }
}
