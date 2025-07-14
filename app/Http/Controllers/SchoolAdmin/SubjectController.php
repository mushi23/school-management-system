<?php

namespace App\Http\Controllers\SchoolAdmin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Models\SchoolStructure;
use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;

class SubjectController extends Controller
{
    public function showSubjectPage($level, $stream, $subject)
    {
        // Find the SchoolStructure for the given level
        $schoolId = auth()->user()->school_id;
        $structure = SchoolStructure::where('school_id', $schoolId)
            ->where('title', $level)
            ->first();
        $teacher = null;
        if ($structure && isset($structure->structure['streams'])) {
            foreach ($structure->structure['streams'] as $streamObj) {
                $streamName = is_array($streamObj) ? ($streamObj['name'] ?? $streamObj[0] ?? null) : $streamObj;
                if ($streamName == $stream) {
                    $subjectTeachers = $streamObj['subject_teachers'] ?? [];
                    $teacherId = $subjectTeachers[$subject] ?? null;
                    if ($teacherId) {
                        $teacherModel = Teacher::find($teacherId);
                        if ($teacherModel) {
                            $teacher = [
                                'name' => $teacherModel->full_name,
                                'email' => $teacherModel->email,
                                'profile_picture' => $teacherModel->profile_picture
                                    ? Storage::url($teacherModel->profile_picture)
                                    : null,
                            ];
                        }
                    }
                }
            }
        }
        // Determine combined/separate status (placeholder logic)
        $combinedStatus = 'Separate';
        if ($structure && isset($structure->structure['streams'])) {
            foreach ($structure->structure['streams'] as $streamObj) {
                $streamName = is_array($streamObj) ? ($streamObj['name'] ?? $streamObj[0] ?? null) : $streamObj;
                if ($streamName == $stream) {
                    $subjectTeachers = $streamObj['subject_teachers'] ?? [];
                    $teacherId = $subjectTeachers[$subject] ?? null;
                    // Example: if this teacher is assigned to this subject in more than one stream, mark as Combined
                    $count = 0;
                    foreach ($structure->structure['streams'] as $s) {
                        if (($s['subject_teachers'][$subject] ?? null) === $teacherId) {
                            $count++;
                        }
                    }
                    if ($teacherId && $count > 1) {
                        $combinedStatus = 'Combined';
                    }
                }
            }
        }
        return Inertia::render('SchoolAdmin/Subjects/SubjectPage', [
            'level' => $level,
            'stream' => $stream,
            'subject' => $subject,
            'teacher' => $teacher,
            'combined_status' => $combinedStatus,
        ]);
    }
} 