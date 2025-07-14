<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Student;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'admission_number' => $this->faker->unique()->numerify('########'),
            'dob' => $this->faker->date('Y-m-d', '2010-01-01'),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'school_id' => \App\Models\School::factory(),
            'guardian_name' => $this->faker->name('female'),
            'guardian_contact' => $this->faker->phoneNumber,
        ];
    }
}

