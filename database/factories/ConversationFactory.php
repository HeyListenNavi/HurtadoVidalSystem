<?php

namespace Database\Factories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'chat_id' => $this->faker->unique()->numberBetween(10000000000, 99999999999),
            'user_name' => $this->faker->name,
            'current_process' => null,
            'process_id' => null,
            'process_status' => 'in_progress',
        ];
    }
}
