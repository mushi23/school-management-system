<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CurriculumLevel;

class CurriculumLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'Early Years Education', 'code' => 'EYE'],
            ['name' => 'Middle School Education', 'code' => 'MSE'],
            ['name' => 'Senior School', 'code' => 'SS'],
        ];

        foreach ($levels as $level) {
            CurriculumLevel::firstOrCreate(['code' => $level['code']], $level);
        }
    }
}

