<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Competency;

class CompetencySeeder extends Seeder
{
    public function run(): void
    {
        $competencies = [
            'Communication and Collaboration',
            'Self-efficacy',
            'Critical Thinking and Problem Solving',
            'Creativity and Imagination',
            'Citizenship',
            'Digital Literacy',
            'Learning to Learn',
        ];

        foreach ($competencies as $name) {
            Competency::firstOrCreate(
                ['name' => $name],
                ['description' => $name . ' description']
            );
        }
    }
}

