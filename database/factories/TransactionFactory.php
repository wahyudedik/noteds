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
            'buyer_id' => \App\Models\User::factory(),
            'seller_id' => \App\Models\User::factory(),
            'note_id' => \App\Models\Note::factory(),
            'amount' => fake()->randomFloat(2, 10000, 500000),
            'commission' => fake()->randomFloat(2, 0, 100000),
            'platform_fee' => fake()->randomFloat(2, 0, 100000),
            'creator_commission' => fake()->randomFloat(2, 0, 50000),
            'status' => fake()->randomElement(['pending', 'success', 'failed']),
            'payment_method' => fake()->randomElement(['wallet', 'midtrans']),
            'notes' => fake()->sentence(),
        ];
    }
}
