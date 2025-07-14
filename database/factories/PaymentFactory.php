<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Payment;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'invoice_id' => \App\Models\Invoice::factory(),
            'student_id' => \App\Models\Student::factory(), // Required by your migration
            'payment_date' => $this->faker->dateTimeBetween('-1 months', 'now'),
            'amount' => $this->faker->randomFloat(2, 500, 10000), // ✅ Matches 'amount'
            'method' => $this->faker->randomElement(['cash', 'card', 'mobile_money']), // ✅ Matches 'method'
            'reference' => $this->faker->unique()->numerify('CONF###'), // ✅ Matches 'reference'
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}

