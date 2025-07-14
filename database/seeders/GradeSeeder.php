<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\CurriculumLevel;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['name' => 'Pre-Primary 1', 'code' => 'PP1', 'level_code' => 'EYE'],
            ['name' => 'Pre-Primary 2', 'code' => 'PP2', 'level_code' => 'EYE'],
            ['name' => 'Grade 1', 'code' => 'G1', 'level_code' => 'EYE'],
            ['name' => 'Grade 2', 'code' => 'G2', 'level_code' => 'EYE'],
            ['name' => 'Grade 3', 'code' => 'G3', 'level_code' => 'EYE'],
            ['name' => 'Grade 4', 'code' => 'G4', 'level_code' => 'MSE'],
            ['name' => 'Grade 5', 'code' => 'G5', 'level_code' => 'MSE'],
            ['name' => 'Grade 6', 'code' => 'G6', 'level_code' => 'MSE'],
            ['name' => 'Grade 7', 'code' => 'G7', 'level_code' => 'MSE'],
            ['name' => 'Grade 8', 'code' => 'G8', 'level_code' => 'MSE'],
            ['name' => 'Grade 9', 'code' => 'G9', 'level_code' => 'MSE'],
            ['name' => 'Grade 10', 'code' => 'G10', 'level_code' => 'SS'],
            ['name' => 'Grade 11', 'code' => 'G11', 'level_code' => 'SS'],
            ['name' => 'Grade 12', 'code' => 'G12', 'level_code' => 'SS'],
        ];

        foreach ($grades as $grade) {
            $level = CurriculumLevel::where('code', $grade['level_code'])->first();
            if ($level) {
                Grade::firstOrCreate(
                    ['code' => $grade['code']],
                    ['name' => $grade['name'], 'curriculum_level_id' => $level->id]
                );
            }
        }
    }
}

