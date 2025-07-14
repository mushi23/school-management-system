<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\School;
use Illuminate\Support\Str;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->company . ' School',
            'registration_number' => strtoupper(Str::random(6)),
            'county' => $this->faker->state, // or fixed like 'Mombasa'
            'sub_county' => $this->faker->city,
            'location' => $this->faker->address,
            'verified' => $this->faker->boolean(30), // 30% chance verified
            'curriculum_level_id' => null, // or set to valid id if available
            'contact_email' => $this->faker->unique()->safeEmail,
            'contact_phone' => $this->faker->phoneNumber,
        ];
    }
}
