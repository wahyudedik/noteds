<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $start = now()->addDays(1)->setTime(10, 0);
        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'location' => $this->faker->city(),
            'is_virtual' => false,
            'privacy' => 'public',
            'start_at' => $start,
            'end_at' => (clone $start)->addHour(),
            'timezone' => 'UTC',
            'status' => 'scheduled',
            'share_token' => Str::random(32),
        ];
    }
}
