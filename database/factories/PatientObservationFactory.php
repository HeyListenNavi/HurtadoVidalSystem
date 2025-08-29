<?php

namespace Database\Factories;

use App\Models\PatientObservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientObservationFactory extends Factory
{
    protected $model = PatientObservation::class;

    public function definition(): array
    {
        return [
            'patient_id' => null, // Set in seeder
            'observation_date' => $this->faker->date(),
            'notes' => $this->faker->paragraph,
            'attached_photo' => $this->faker->optional()->imageUrl(),
        ];
    }
}