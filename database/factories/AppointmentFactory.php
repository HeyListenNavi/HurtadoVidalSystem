<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'chat_id' => $this->faker->unique()->numberBetween(10000000000, 99999999999),
            'patient_name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'appointment_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'appointment_time' => sprintf(
                '%02d:%s:00',
                $this->faker->numberBetween(8, 18),
                $this->faker->randomElement(['00', '30'])
            ),
            'current_step' => $this->faker->randomElement(['step1', 'step2', 'step3']),
            'current_question_id' => null,
            'process_status' => $this->faker->randomElement(['in_progress', 'completed', 'rejected']),
            'rejection_reason' => $this->faker->optional()->sentence,
        ];
    }
}
