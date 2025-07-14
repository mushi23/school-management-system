<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\SchoolStructure;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $school = $user->school;
        
        $structures = SchoolStructure::where('school_id', $school->id)
            ->select('id', 'title', 'structure')
            ->get()
            ->map(function ($structure) {
                return [
                    'id' => $structure->id,
                    'title' => $structure->title,
                    'streams' => collect($structure->structure['streams'] ?? [])->values(),
                ];
            });

        // Get real counts
        $studentCount = Student::where('school_id', $school->id)->count();
        $teacherCount = Teacher::where('school_id', $school->id)->count();

        return Inertia::render('SchoolAdmin/Dashboard', [
            'structures' => $structures,
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'slogan' => $school->slogan,
                'brand_color' => $school->brand_color,
                'logo' => $school->logo ? Storage::url($school->logo) : null,
                'background' => $school->background ? Storage::url($school->background) : null,
            ],
            'stats' => [
                'students' => $studentCount,
                'teachers' => $teacherCount,
            ],
        ]);
    }
} 