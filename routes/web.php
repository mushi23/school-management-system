<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolAdmin\OnboardingController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Middleware\EnsureSchoolIsSetup;
use Inertia\Inertia;

// 🌐 Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::get('/admin/schools', function () {
    return Inertia::render('SchoolList');
});

// 🧪 Email verification routes
Route::get('/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('postlogin.redirect');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/verify-email/{token}', [VerifyEmailController::class, 'verify']);

// 🔐 Authenticated, verified, and active users
Route::middleware(['auth', 'verified', 'check.active'])->group(function () {
    // 🧭 Shared dashboard (default fallback)
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    // 👤 Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
                                                               
                                                               Route::middleware(['role:school_admin', EnsureSchoolIsSetup::class])
                                                                   ->prefix('school-admin')
                                                                   ->name('schooladmin.')
                                                                   ->group(function () {
                                                                       Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
                                                                       Route::patch('/onboarding', [OnboardingController::class, 'update'])->name('onboarding.update');
                                                                       Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
                                                                       Route::get('/dashboard', [\App\Http\Controllers\SchoolAdmin\DashboardController::class, 'index'])->name('dashboard');

                                                                       // ✅ Fix this route to hit the controller
                                                                       // Route::get('/students/create', [\App\Http\Controllers\StudentController::class, 'create'])->name('students.create');

                                                                       Route::post('/students', [\App\Http\Controllers\StudentController::class, 'store'])->name('students.store');
                                                                       Route::post('/teachers', [\App\Http\Controllers\TeacherController::class, 'store'])->name('teachers.store');
                                                                       Route::get('/teachers', function () {
                                                                           return Inertia::render('SchoolAdmin/Teachers/TeacherList');
                                                                       })->name('teachers');
                                                                       
                                                                       // School Profile Routes
                                                                       Route::get('/profile', [\App\Http\Controllers\SchoolAdmin\ProfileController::class, 'index'])->name('profile');
                                                                       Route::post('/profile/branding', [\App\Http\Controllers\SchoolAdmin\ProfileController::class, 'updateBranding'])->name('profile.branding');
                                                                       Route::get('/profile/data', [\App\Http\Controllers\SchoolAdmin\ProfileController::class, 'getSchoolProfile'])->name('profile.data');
                                                                       Route::get('/subjects', function () {
                                                                           return Inertia::render('SchoolAdmin/Subjects/SubjectList');
                                                                       })->name('subjects');
                                                                       Route::get('/students', function () {
                                                                           return Inertia::render('SchoolAdmin/Students/StudentList');
                                                                       })->name('students');
                                                                       
                                                                       // M-PESA Settings Routes
                                                                       Route::resource('mpesa-settings', \App\Http\Controllers\SchoolAdmin\MpesaSettingController::class)->names([
                                                                           'index' => 'mpesa-settings.index',
                                                                           'create' => 'mpesa-settings.create',
                                                                           'store' => 'mpesa-settings.store',
                                                                           'edit' => 'mpesa-settings.edit',
                                                                           'update' => 'mpesa-settings.update',
                                                                           'destroy' => 'mpesa-settings.destroy',
                                                                       ]);
                                                                       Route::post('/mpesa-settings/{mpesaSetting}/test', [\App\Http\Controllers\SchoolAdmin\MpesaSettingController::class, 'testCredentials'])->name('mpesa-settings.test');
Route::post('/mpesa-settings/{mpesaSetting}/activate', [\App\Http\Controllers\SchoolAdmin\MpesaSettingController::class, 'activate'])->name('mpesa-settings.activate');
Route::post('/mpesa-settings/test-production', [\App\Http\Controllers\SchoolAdmin\MpesaSettingController::class, 'testProductionCredentials'])->name('mpesa-settings.test-production');
                                                                   });

    Route::get('/teacher/dashboard', function () {
        return Inertia::render('TeacherDashboard');
    })->name('teacher.dashboard');
    Route::get('/student/dashboard', function () {
        $user = auth()->user();
        $student = \App\Models\Student::with('school')->where('user_id', $user->id)->first();
        $subjects = [];
        $subjectTeachers = [];
        $school = null;
        if ($student) {
            $structure = $student->structure;
            $stream = $student->stream;
            // Try to get subjects from structure JSON
            if ($structure && is_array($structure->structure)) {
                $streams = $structure->structure['streams'] ?? [];
                $streamObj = null;
                foreach ($streams as $s) {
                    if ((is_array($s) && ($s['name'] ?? $s) == $stream) || $s == $stream) {
                        $streamObj = $s;
                        break;
                    }
                }
                $subjects = $structure->structure['subjects'] ?? [];
                if (is_array($streamObj) && isset($streamObj['subjects'])) {
                    $subjects = $streamObj['subjects'];
                }
                // Fallback to curriculum file if no subjects found
                if (empty($subjects)) {
                    $curriculumKey = $structure->school->curriculum_key ?? 1;
                    $curriculumFiles = [
                        1 => resource_path('js/data/curriculums/cbc.json'),
                        2 => resource_path('js/data/curriculums/844.json'),
                        3 => resource_path('js/data/curriculums/igcse.json'),
                        4 => resource_path('js/data/curriculums/alevel.json'),
                    ];
                    $file = $curriculumFiles[$curriculumKey] ?? $curriculumFiles[1];
                    if (file_exists($file)) {
                        $levels = json_decode(file_get_contents($file), true);
                        foreach ($levels as $item) {
                            if (($item['level'] ?? null) == $structure->title) {
                                $subjects = $item['subjects'] ?? $item['courses'] ?? [];
                                break;
                            }
                        }
                    }
                }
                // Get subject-teacher mapping if present
                if (is_array($streamObj) && isset($streamObj['subject_teachers'])) {
                    $subjectTeachers = $streamObj['subject_teachers'];
                }
                $school = $student->school;
            }
            $student->subjects = (array) ($subjects ?? []);
            $student->append('profile_picture_url');
        }
        return Inertia::render('StudentDashboard', [
            'student' => $student,
            'subject_teachers' => $subjectTeachers,
            'school' => $school ? [
                'name' => $school->name,
                'logo' => $school->logo_url,
                'background' => $school->background_url,
                'slogan' => $school->slogan,
            ] : null
        ]);
    })->name('student.dashboard');

    // 🔐 Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    });

    // 🔁 Post-login redirection based on role
    Route::get('/redirect', function () {
        $user = Auth::user();

        return match (true) {
            $user->hasRole('admin')        => redirect()->route('admin.dashboard'),
            $user->hasRole('school_admin') => redirect()->route('schooladmin.dashboard'),
            $user->hasRole('teacher')      => redirect()->route('teacher.dashboard'),
            $user->hasRole('student')      => redirect()->route('student.dashboard'),
            default                        => redirect()->route('dashboard'),
        };
    })->name('postlogin.redirect');

    // School Admin Subjects dynamic subject page
    Route::get('/school-admin/subjects/{level}/{stream}/{subject}', [App\Http\Controllers\SchoolAdmin\SubjectController::class, 'showSubjectPage'])->name('school-admin.subjects.show');
});

// 🔐 Auth routes
require __DIR__.'/auth.php';

    // Receipt routes
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/receipts', [\App\Http\Controllers\ReceiptController::class, 'index'])->name('receipts');
        Route::get('/receipts/{receipt}/view', [\App\Http\Controllers\ReceiptController::class, 'view'])->name('receipts.view');
        Route::get('/receipts/{receipt}/download', [\App\Http\Controllers\ReceiptController::class, 'download'])->name('receipts.download');
    });

    // School admin receipt template routes
    Route::middleware(['role:school_admin'])->prefix('school-admin')->name('schooladmin.')->group(function () {
        Route::get('/receipt-template', function () {
            return Inertia::render('SchoolAdmin/ReceiptTemplate');
        })->name('receipt-template');
        Route::get('/receipt-template/data', [\App\Http\Controllers\ReceiptController::class, 'getTemplate'])->name('receipt-template.get');
        Route::post('/receipt-template', [\App\Http\Controllers\ReceiptController::class, 'updateTemplate'])->name('receipt-template.update');
        Route::post('/receipt-template/logo', [\App\Http\Controllers\ReceiptController::class, 'uploadLogo'])->name('receipt-template.logo');
        Route::post('/receipt-template/signature', [\App\Http\Controllers\ReceiptController::class, 'uploadSignature'])->name('receipt-template.signature');
        Route::delete('/receipt-template/signature', [\App\Http\Controllers\ReceiptController::class, 'deleteSignature'])->name('receipt-template.signature.delete');
        Route::post('/receipt-template/seal', [\App\Http\Controllers\ReceiptController::class, 'uploadSeal'])->name('receipt-template.seal');
        Route::delete('/receipt-template/seal', [\App\Http\Controllers\ReceiptController::class, 'deleteSeal'])->name('receipt-template.seal.delete');
    });

    Route::get('/student/subjects', function () {
        $user = auth()->user();
        $student = \App\Models\Student::with('school')->where('user_id', $user->id)->first();
        $subjects = [];
        $subjectTeachers = [];
        $subjectTeacherNames = [];
        $school = $student?->school;
        if ($student) {
            $structure = $student->structure;
            $stream = $student->stream;
        if ($structure && is_array($structure->structure)) {
            $streams = $structure->structure['streams'] ?? [];
            $streamObj = null;
            foreach ($streams as $s) {
                if ((is_array($s) && ($s['name'] ?? $s) == $stream) || $s == $stream) {
                    $streamObj = $s;
                    break;
                }
            }
            $subjects = $structure->structure['subjects'] ?? [];
            if (is_array($streamObj) && isset($streamObj['subjects'])) {
                $subjects = $streamObj['subjects'];
            }
            // Fallback to curriculum file if no subjects found
            if (empty($subjects)) {
                $curriculumKey = $structure->school->curriculum_key ?? 1;
                $curriculumFiles = [
                    1 => resource_path('js/data/curriculums/cbc.json'),
                    2 => resource_path('js/data/curriculums/844.json'),
                    3 => resource_path('js/data/curriculums/igcse.json'),
                    4 => resource_path('js/data/curriculums/alevel.json'),
                ];
                $file = $curriculumFiles[$curriculumKey] ?? $curriculumFiles[1];
                if (file_exists($file)) {
                    $levels = json_decode(file_get_contents($file), true);
                    foreach ($levels as $item) {
                        if (($item['level'] ?? null) == $structure->title) {
                            $subjects = $item['subjects'] ?? $item['courses'] ?? [];
                            break;
                        }
                    }
                }
            }
            if (is_array($streamObj) && isset($streamObj['subject_teachers'])) {
                $subjectTeachers = $streamObj['subject_teachers'];
                // Fetch teacher names
                $teacherIds = array_values($subjectTeachers);
                $teachers = \App\Models\Teacher::whereIn('id', $teacherIds)->get(['id', 'full_name']);
                foreach ($subjectTeachers as $subject => $teacherId) {
                    $name = $teachers->firstWhere('id', $teacherId)?->full_name;
                    if ($name) {
                        $subjectTeacherNames[$subject] = $name;
                    }
                }
            }
        }
        $student->subjects = (array) ($subjects ?? []);
        $student->append('profile_picture_url');
    }
    return Inertia::render('Student/Subjects/SubjectList', [
        'student' => $student,
        'subject_teachers' => $subjectTeachers,
        'subject_teacher_names' => $subjectTeacherNames,
        'school' => $school ? [
            'name' => $school->name,
            'logo' => $school->logo_url,
            'background' => $school->background_url,
            'slogan' => $school->slogan,
        ] : null
    ]);
})->name('student.subjects');

Route::get('/student/subjects/{level}/{stream}/{subject}', function ($level, $stream, $subject) {
    $user = auth()->user();
    $student = \App\Models\Student::with('school')->where('user_id', $user->id)->first();
    $structure = $student?->structure;
    $subjectTeacher = null;
    $teacher = null;
    if ($structure && is_array($structure->structure)) {
        $streams = $structure->structure['streams'] ?? [];
        $streamObj = null;
        foreach ($streams as $s) {
            if ((is_array($s) && ($s['name'] ?? $s) == $stream) || $s == $stream) {
                $streamObj = $s;
                break;
            }
        }
        if (is_array($streamObj) && isset($streamObj['subject_teachers'][$subject])) {
            $subjectTeacher = $streamObj['subject_teachers'][$subject];
            $teacher = \App\Models\Teacher::find($subjectTeacher);
        }
    }
    $student->append('profile_picture_url');
    return Inertia::render('Student/SubjectPage', [
        'level' => $level,
        'stream' => $stream,
        'subject' => $subject,
        'teacher' => $teacher,
        'structure' => $structure,
        'student' => $student,
        'school' => $student?->school ? [
            'name' => $student->school->name,
            'logo' => $student->school->logo_url,
            'background' => $student->school->background_url,
            'slogan' => $student->school->slogan,
        ] : null
    ]);
})->name('student.subjects.show');

// Student fees page
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/fees', [\App\Http\Controllers\StudentController::class, 'showFees'])->name('student.fees');
});
