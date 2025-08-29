<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AppointmentResponse;
use App\Models\Appointment;
use App\Models\AppointmentQuestion;

class AppointmentResponseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AppointmentResponse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'question_id' => AppointmentQuestion::factory(),
            'question_text_snapshot' => $this->faker->sentence . '?',
            'user_response' => $this->faker->word,
        ];
    }
}
