<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Competency;

class SubjectCompetencySeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all subjects and competencies
        $subjects = Subject::all();
        $competencies = Competency::all();

        if ($subjects->isEmpty() || $competencies->isEmpty()) {
            $this->command->warn('Subjects or Competencies not found. Seed those first.');
            return;
        }

        // Randomly assign 2–4 competencies per subject
        foreach ($subjects as $subject) {
            $subjectCompetencies = $competencies->random(rand(2, 4));
            $subject->competencies()->sync($subjectCompetencies->pluck('id')->toArray());
        }

        $this->command->info('Subject–Competency relationships seeded.');
    }
}

