<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AppointmentQuestion;
use App\Models\AppointmentSetting;

class AppointmentQuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AppointmentQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_setting_id' => AppointmentSetting::all()->first(),
            'question_text' => $this->faker->sentence . '?',
            'approval_criteria' => json_encode(['min_length' => 5]),
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
