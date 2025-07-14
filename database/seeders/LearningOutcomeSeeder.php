<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LearningOutcome;
use App\Models\Subject;
use App\Models\Grade;

class LearningOutcomeSeeder extends Seeder
{
    public function run(): void
    {
        $outcomes = [
            [
                'subject_code' => 'ENG',
                'grade_code' => 'G7',
                'description' => 'Acquire proficiency in English for academic and general communication.'
            ],
            [
                'subject_code' => 'ENG',
                'grade_code' => 'G7',
                'description' => 'Develop skills in listening, speaking, reading, writing, and grammar.'
            ],
            [
                'subject_code' => 'ENG',
                'grade_code' => 'G7',
                'description' => 'Engage with literature and apply it contextually.'
            ],
            [
                'subject_code' => 'MATH',
                'grade_code' => 'G7',
                'description' => 'Develop logical reasoning, computation, and problem-solving skills for real-life application.'
            ],
            [
                'subject_code' => 'HE',
                'grade_code' => 'G7',
                'description' => 'Demonstrate knowledge and practice of hygiene, safety, and health.'
            ],
            [
                'subject_code' => 'SS',
                'grade_code' => 'G7',
                'description' => 'Understand civic responsibility and cultural diversity.'
            ],
            // Add more learning outcomes as needed
        ];

        foreach ($outcomes as $item) {
            $subject = Subject::where('code', $item['subject_code'])->first();
            $grade = Grade::where('code', $item['grade_code'])->first();

            if ($subject && $grade) {
                LearningOutcome::firstOrCreate([
                    'subject_id' => $subject->id,
                    'grade_id' => $grade->id,
                    'description' => $item['description']
                ]);
            }
        }
    }
}

