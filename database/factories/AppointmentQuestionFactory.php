<?php

namespace Database\Factories;

use App\Models\AppointmentQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentQuestionFactory extends Factory
{
    protected $model = AppointmentQuestion::class;

    public function definition(): array
    {
        return [
            'appointment_setting_id' => null, // Set in seeder
            'question_text' => $this->faker->sentence,
            'approval_criteria' => ['type' => 'text', 'min_length' => 5],
            'order' => $this->faker->unique()->numberBetween(1, 10),
        ];
    }
}