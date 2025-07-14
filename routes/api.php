<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\SchoolStructureController;
use Inertia\Inertia;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\TeacherController;



// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public routes - outside auth middleware
Route::get('/verify-email/{token}', [EmailVerificationController::class, 'verifyEmail']);
Route::post('/verify-email/reset-password', [EmailVerificationController::class, 'resetPassword']);

Route::post('/admin/test', function () {
    return response()->json(['message' => 'Test route works']);
});

Route::get('/test-curriculum/{curriculumKey}', [\App\Http\Controllers\SchoolStructureController::class, 'getStructureByCurriculumKey']);
Route::get('/holidays/{year?}', [HolidayController::class, 'getKenyanHolidays']);
Route::get('/holidays/sync/{year?}', [HolidayController::class, 'syncKenyanHolidays']);

// Protected routes
// Protected routes with authentication and active status check
// ✅ School Admin–specific routes
Route::middleware(['auth:sanctum', 'check.active', 'role:school_admin'])->prefix('school')->group(function () {
    Route::get('/curriculum-structure/{curriculumKey}', [SchoolStructureController::class, 'getStructureByCurriculumKey']);
    Route::post('/update-curriculum', [SchoolController::class, 'updateCurriculum']);
    Route::post('/structure/multiple', function (Request $request) {
        $ids = $request->input('curriculum_level_ids', []);
        $structures = [];

        foreach ($ids as $id) {
            $file = match((int) $id) {
                1 => resource_path('data/curriculums/cbc.json'),
                2 => resource_path('data/curriculums/844.json'),
                default => null,
            };

            if ($file && file_exists($file)) {
                $structures[] = json_decode(file_get_contents($file), true);
            }
        }

        return response()->json($structures);
    });
    Route::post('/school-structure/update', [SchoolStructureController::class, 'update']);
    Route::post('/school-structure/create', [SchoolStructureController::class, 'create']);
});

Route::middleware(['auth:sanctum', 'check.active', 'role:school_admin'])->prefix('school-admin')->group(function () {
    Route::get('/teachers', [\App\Http\Controllers\TeacherController::class, 'index']);
    Route::post('/teachers/{id}/disable', [\App\Http\Controllers\TeacherController::class, 'disable']);
    Route::post('/teachers/{id}/enable', [\App\Http\Controllers\TeacherController::class, 'enable']);
    Route::delete('/teachers/{id}', [\App\Http\Controllers\TeacherController::class, 'destroy']);
    Route::post('/teachers/{id}/reset-password', [\App\Http\Controllers\TeacherController::class, 'resetPassword']);
    Route::post('/teachers/{id}/restore', [\App\Http\Controllers\TeacherController::class, 'restore']);
    Route::get('/students', [\App\Http\Controllers\StudentController::class, 'index']);
    Route::post('/students/{id}/disable', [\App\Http\Controllers\StudentController::class, 'disable']);
    Route::post('/students/{id}/enable', [\App\Http\Controllers\StudentController::class, 'enable']);
    Route::delete('/students/{id}', [\App\Http\Controllers\StudentController::class, 'destroy']);
    Route::post('/students/{id}/reset-password', [\App\Http\Controllers\StudentController::class, 'resetPassword']);
    Route::post('/students/{id}/restore', [\App\Http\Controllers\StudentController::class, 'restore']);
});

Route::middleware(['auth:sanctum', 'check.active'])->group(function () {
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::post('/create-user', [AuthController::class, 'createUserByAdmin']);

        Route::controller(SchoolController::class)->prefix('schools')->group(function () {
            Route::post('/', 'store');
            Route::get('/', 'index');
            Route::post('{school}/disable', 'disable');
            Route::post('{school}/enable', 'enable');
            Route::delete('{school}', 'destroy');
            Route::post('{school}/restore', 'restore');
            Route::delete('{school}/force', 'forceDestroy');
            Route::post('{school}/reset-password', 'resetPassword');
            Route::get('{school}/users', [SchoolController::class, 'getUsers']);

        });
    });
                                                           
    Route::get('/user/school', function () {
        $user = auth()->user();
        $school = $user->school;
        return response()->json([
            'success' => true,
            'school' => [
                'id' => optional($school)->id,
                'name' => optional($school)->name ?? 'No School Assigned',
                'slogan' => optional($school)->slogan,
                'brand_color' => optional($school)->brand_color,
                'curriculum_key' => optional($school)->curriculum_key,
                'logo' => optional($school)->logo_url,
                'background' => optional($school)->background_url,
            ]
        ]);
    });

    Route::get('/user/school-structures', function () {
        $user = auth()->user();
        $school = $user->school;
        $structures = $school
            ? $school->structures()->select('id', 'title', 'structure')->get()->map(function ($s) {
                return [
                    'id' => $s->id,
                    'title' => $s->title,
                    'structure' => is_string($s->structure) ? json_decode($s->structure, true) : $s->structure,
                ];
            })
            : [];
        return response()->json($structures);
    });

    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// M-PESA STK Push Routes
Route::middleware(['auth:sanctum', 'check.active'])->group(function () {
    Route::post('/mpesa/initiate-payment', [App\Http\Controllers\MpesaController::class, 'initiatePayment']);
    Route::get('/mpesa/check-status', [App\Http\Controllers\MpesaController::class, 'checkPaymentStatus']);
});

// Test routes (remove in production)
Route::get('/mpesa/test-credentials', [App\Http\Controllers\MpesaController::class, 'testCredentials']);
Route::get('/mpesa/test-sandbox', [App\Http\Controllers\MpesaController::class, 'testSandboxConfig']);
Route::get('/mpesa/test-c2b', [App\Http\Controllers\MpesaController::class, 'testC2BSimulation']);
Route::get('/mpesa/test-connectivity', [App\Http\Controllers\MpesaController::class, 'testSandboxConnectivity']);
Route::post('/mpesa/simulate-sandbox-payment', [App\Http\Controllers\MpesaController::class, 'simulateSandboxPayment']);

// M-PESA Callback (no auth required - called by Safaricom)
Route::post('/mpesa/callback', [App\Http\Controllers\MpesaController::class, 'handleCallback']);

// Receipt API routes
Route::middleware(['auth:sanctum', 'check.active'])->group(function () {
    Route::post('/receipts/generate/{payment}', [\App\Http\Controllers\ReceiptController::class, 'generate']);
    Route::get('/receipts/{receipt}/view', [\App\Http\Controllers\ReceiptController::class, 'view']);
    Route::get('/receipts/{receipt}/download', [\App\Http\Controllers\ReceiptController::class, 'download']);
});


