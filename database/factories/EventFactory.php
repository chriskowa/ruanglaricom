<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $name = 'Event ' . fake()->unique()->words(3, true);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->randomNumber(5),
            'short_description' => fake()->sentence(),
            'full_description' => fake()->paragraph(),
            'start_at' => now()->addDays(fake()->numberBetween(1, 90))->setTime(6, 0),
            'end_at' => now()->addDays(fake()->numberBetween(1, 90))->setTime(12, 0),
            'location_name' => fake()->city(),
            'status' => 'published',
            'is_featured' => false,
            'is_active' => true,
        ];
    }
}

