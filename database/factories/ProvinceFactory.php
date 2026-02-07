<?php

namespace Database\Factories;

use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    public function definition()
    {
        return [
            'name' => $this->faker->state,
            'seourl' => $this->faker->slug,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
        ];
    }
}
