<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'chat_id' => $this->faker->numberBetween(10000000000, 99999999999),
            'phone' => $this->faker->phoneNumber,
            'name' => $this->faker->firstName,
            'message' => $this->faker->sentence,
            'role' => $this->faker->randomElement(['user', 'bot']),
        ];
    }
}
