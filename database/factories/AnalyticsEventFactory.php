<?php

namespace Database\Factories;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnalyticsEventFactory extends Factory
{
    protected $model = AnalyticsEvent::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['feed_sort_change','trending_period_change','server_error','too_many_requests']),
            'payload' => ['code' => $this->faker->randomElement(['ok','server_error','too_many_requests'])],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
