<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Scholarship;

class ScholarshipFactory extends Factory
{
    protected $model = Scholarship::class;

    public function definition()
    {
        return [
            'student_id' => \App\Models\Student::factory(),
            'fee_structure_id' => \App\Models\FeeStructure::factory(),
            'discount_amount' => $this->faker->randomFloat(2, 100, 5000),
            'discount_percent' => $this->faker->optional()->randomFloat(2, 0, 50),
            'reason' => $this->faker->sentence(),
        ];
    }
}

