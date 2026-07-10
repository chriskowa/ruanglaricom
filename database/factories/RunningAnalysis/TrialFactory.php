<?php

namespace Database\Factories\RunningAnalysis;

use App\Models\RunningAnalysis\Trial;
use App\Models\RunningAnalysis\Session;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrialFactory extends Factory
{
    protected $model = Trial::class;

    public function definition(): array
    {
        return [
            'id'             => $this->faker->uuid(),
            'session_id'     => Session::factory(),
            'runner_id'      => User::factory()->runner(),
            'operator_id'    => User::factory()->admin(),
            'attempt_no'     => 1,
            'direction'      => Trial::DIRECTION_LEFT_TO_RIGHT,
            'status'         => Trial::STATUS_CREATED,
            'capture_version' => '1.0',
        ];
    }
}
