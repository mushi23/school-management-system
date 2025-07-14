<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Grade;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'English', 'code' => 'ENG', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Kiswahili', 'code' => 'KIS', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Mathematics', 'code' => 'MATH', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Integrated Science', 'code' => 'SCI', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Health Education', 'code' => 'HE', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Pre-Technical and Pre-Career Education', 'code' => 'PTC', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Social Studies', 'code' => 'SS', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Religious Education (CRE)', 'code' => 'CRE', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Religious Education (IRE)', 'code' => 'IRE', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Religious Education (HRE)', 'code' => 'HRE', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Business Studies', 'code' => 'BUS', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Agriculture', 'code' => 'AGR', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Life Skills', 'code' => 'LS', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Sports and Physical Education', 'code' => 'PE', 'type' => 'Core', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Visual Arts', 'code' => 'VA', 'type' => 'Optional', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Performing Arts', 'code' => 'PA', 'type' => 'Optional', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Home Science', 'code' => 'HS', 'type' => 'Optional', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Computer Science', 'code' => 'CS', 'type' => 'Optional', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'German', 'code' => 'GER', 'type' => 'Optional', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'French', 'code' => 'FRE', 'type' => 'Optional', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Mandarin', 'code' => 'MAN', 'type' => 'Optional', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Arabic', 'code' => 'ARA', 'type' => 'Optional', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Kenyan Sign Language', 'code' => 'KSL', 'type' => 'Optional', 'grades' => ['G7', 'G8', 'G9']],
            ['name' => 'Indigenous Languages', 'code' => 'IND', 'type' => 'Optional', 'grades' => ['G7', 'G8', 'G9']],
        ];

        foreach ($subjects as $subject) {
            $subjectModel = Subject::firstOrCreate(
                ['code' => $subject['code']],
                ['name' => $subject['name'], 'type' => $subject['type']]
            );

            foreach ($subject['grades'] as $gradeCode) {
                $grade = Grade::where('code', $gradeCode)->first();
                if ($grade) {
                    $subjectModel->grades()->syncWithoutDetaching([$grade->id]);
                }
            }
        }
    }
}
