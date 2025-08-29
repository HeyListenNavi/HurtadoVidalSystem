<?php

namespace Database\Factories;

use App\Models\AppointmentSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentSettingFactory extends Factory
{
    protected $model = AppointmentSetting::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'rejection_reason' => $this->faker->optional()->sentence,
        ];
    }
}