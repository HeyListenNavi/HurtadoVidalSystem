<?php

namespace Database\Factories;

use App\Models\AppointmentResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentResponseFactory extends Factory
{
    protected $model = AppointmentResponse::class;

    public function definition(): array
    {
        return [
            'appointment_id' => null, // Set in seeder
            'question_id' => null, // Set in seeder
            'question_text_snapshot' => $this->faker->sentence,
            'user_response' => $this->faker->sentence,
        ];
    }
}