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
            'appointment_date' => $this->faker->date(),
            'appointment_time' => $this->faker->time(),
            'current_step' => $this->faker->randomElement(['step1', 'step2', 'step3']),
            'current_question_id' => null, // Set in seeder if needed
            'process_status' => $this->faker->randomElement(['in_progress', 'completed', 'rejected']),
            'rejection_reason' => $this->faker->optional()->sentence,
        ];
    }
}
