<?php

namespace Database\Factories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'chat_id' => $this->faker->unique()->numberBetween(10000000000, 99999999999),
            'user_name' => $this->faker->name,
            'processable_id' => null, // Set in seeder
            'processable_type' => null, // Set in seeder
            'process_status' => $this->faker->randomElement(['in_progress', 'completed', 'rejected']),
        ];
    }
}