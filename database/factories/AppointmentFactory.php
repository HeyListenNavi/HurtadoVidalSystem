<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Appointment;
use App\Models\AppointmentQuestion;
use App\Models\AppointmentSetting;

class AppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id' => $this->faker->unique()->numberBetween(10000000000, 99999999999),
            'patient_name' => $this->faker->name,
            'appointment_date' => $this->faker->date(),
            'appointment_time' => $this->faker->time(),
            'reason_for_visit' => $this->faker->sentence,
            'process_status' => $this->faker->randomElement(['in_progress', 'completed', 'rejected']),
            'is_confirmed' => $this->faker->boolean,
        ];
    }
}
