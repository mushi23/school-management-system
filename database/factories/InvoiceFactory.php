<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Invoice;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'student_id' => \App\Models\Student::factory(),
            'fee_structure_id' => \App\Models\FeeStructure::factory(),
            // 'issue_date' => $this->faker->dateTimeBetween('-1 years', 'now'), // REMOVE this line
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'amount_due' => $this->faker->randomFloat(2, 1000, 10000),
            'status' => $this->faker->randomElement(['pending', 'paid', 'overdue']),
        ];
    }
}

