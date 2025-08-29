<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Se define el array de datos para crear una instancia de Message
        return [
            // Se usa un UUID para simular un conversation_id único
            'conversation_id' => $this->faker->uuid(),
            // Se usa un número de teléfono aleatorio, puede ser nulo
            'phone' => $this->faker->phoneNumber(),
            // Se usa un nombre aleatorio, puede ser nulo
            'name' => $this->faker->name(),
            // Se genera una oración de texto para el mensaje
            'message' => $this->faker->sentence(),
            // Se elige aleatoriamente entre 'user' y 'bot' para el rol
            'role' => $this->faker->randomElement(['user', 'bot']),
        ];
    }
}
