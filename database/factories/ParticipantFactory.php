<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_id' => \App\Models\Transaction::factory(),
            'event_package_id' => function (array $attributes) {
                $transactionId = $attributes['transaction_id'] ?? null;
                $transaction = $transactionId ? \App\Models\Transaction::query()->find($transactionId) : null;
                $eventId = $transaction?->event_id ?? \App\Models\Event::factory()->create()->id;

                return \App\Models\EventPackage::query()->create([
                    'event_id' => $eventId,
                    'name' => 'Paket Test',
                    'price' => 100000,
                    'quota' => 1000,
                    'sold_count' => 0,
                    'is_sold_out' => false,
                ])->id;
            },
            'race_category_id' => null,
            'name' => $this->faker->name,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'id_card' => $this->faker->unique()->numerify('################'),
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'province' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'emergency_contact_name' => $this->faker->name,
            'emergency_contact_number' => $this->faker->phoneNumber,
            'date_of_birth' => $this->faker->date(),
            'target_time' => '00:00:00',
            'bib_number' => $this->faker->numberBetween(1000, 9999),
            'jersey_size' => 'L',
            'isApproved' => true,
        ];
    }
}
