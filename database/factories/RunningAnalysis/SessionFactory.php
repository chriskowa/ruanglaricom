<?php

namespace Database\Factories\RunningAnalysis;

use App\Models\RunningAnalysis\Session;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFactory extends Factory
{
    protected $model = Session::class;

    public function definition(): array
    {
        return [
            'name'         => 'Test Session ' . $this->faker->word(),
            'location'     => $this->faker->city(),
            'session_date' => $this->faker->date(),
            'created_by'   => User::factory()->admin(),
            'status'       => Session::STATUS_DRAFT,
        ];
    }
}
