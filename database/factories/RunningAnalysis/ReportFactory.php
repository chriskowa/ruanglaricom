<?php

namespace Database\Factories\RunningAnalysis;

use App\Models\RunningAnalysis\Report;
use App\Models\RunningAnalysis\Trial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'trial_id'       => Trial::factory(),
            'runner_id'      => User::factory()->runner(),
            'report_version' => 1,
            'status'         => Report::STATUS_DRAFT,
        ];
    }
}
