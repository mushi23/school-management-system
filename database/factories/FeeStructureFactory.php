<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FeeStructure;

class FeeStructureFactory extends Factory
{
    protected $model = FeeStructure::class;

    public function definition()
    {
        return [
            'school_id' => \App\Models\School::factory(),
            'academic_year' => '2024/2025',
            'term' => $this->faker->randomElement(['Term 1', 'Term 2', 'Term 3']),
            'amount' => $this->faker->randomFloat(2, 1000, 10000),
            'description' => 'Fee for ' . $this->faker->randomElement(['Grade 8', 'Grade 9', 'Grade 10']),
        ];
    }
}

