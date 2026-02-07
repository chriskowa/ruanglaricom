<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Community;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityFactory extends Factory
{
    protected $model = Community::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph,
            'city_id' => City::factory(), // Assuming City factory exists, if not we might need to create it or handle it.
            'pic_name' => $this->faker->name,
            'pic_email' => $this->faker->email,
            'pic_phone' => $this->faker->phoneNumber,
            'schedules' => [],
            'captains' => [],
        ];
    }
}
