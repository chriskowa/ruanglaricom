<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_ref' => 'RL'.$this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'event_id' => \App\Models\Event::factory(),
            'user_id' => \App\Models\User::factory(),
            'pic_data' => [
                'name' => $this->faker->name,
                'email' => $this->faker->safeEmail,
                'phone' => $this->faker->phoneNumber,
            ],
            'total_original' => 100000,
            'discount_amount' => 0,
            'admin_fee' => 0,
            'final_amount' => 100000,
            'payment_status' => 'paid',
            'payment_gateway' => 'midtrans',
            'paid_at' => now(),
        ];
    }
}
