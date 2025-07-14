<?php

namespace App\Http\Controllers;

use App\Mail\StudentAccountCreated;
use App\Models\SchoolStructure;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class StudentController extends Controller
{
    /**
     * Show student registration form with available structures and streams.
     */
    public function create()
    {
        $schoolId = auth()->user()->school_id;

        $structures = SchoolStructure::where('school_id', $schoolId)
            ->select('id', 'title', 'structure')
            ->get()
            ->map(fn($structure) => [
                'id' => $structure->id,
                'title' => $structure->title,
                'streams' => collect($structure->structure['streams'] ?? [])->values(),
            ]);

        return Inertia::render('SchoolAdmin/Students/Create', [
            'structures' => $structures,
        ]);
    }

    /**
     * Store a newly created student and user account.
     */
    public function store(Request $request)
    {
        \Log::info('Student store request:', $request->all());
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'email' => 'required|email|unique:users,email',
            'gender' => 'required|in:male,female,other',
            'guardians' => 'required|array|min:1',
            'guardians.*.name' => 'required|string|max:255',
            'guardians.*.phone' => [
                                            'required',
                                            'string',
                                            'regex:/^(?:\+2547|07)\d{8}$/',
                                            'distinct',
                                        ],
            'structure_id' => 'required|exists:school_structures,id',
            'stream' => 'required|string|max:50',
            'profile_picture' => 'nullable|image|max:2048',
        ]);


        try {
            DB::transaction(function () use ($request, $validated) {
                $schoolId = auth()->user()->school_id;
                $tempPassword = Str::random(10);

                // 📤 Upload profile picture
                $profilePath = $request->hasFile('profile_picture')
                    ? $request->file('profile_picture')->store('profile_pictures', 'public')
                    : null;

                // 👤 Create user
                            $user = User::create([
                                'name' => $validated['full_name'],
                                'email' => $validated['email'],
                                'phone' => $validated['guardians'][0]['phone'],
                                'school_id' => $schoolId,
                                'password' => Hash::make($tempPassword),
                            ]);

                            if (!$user || !$user->id) {
                                throw new \Exception('User creation failed.');
                            }
                            
                            \Log::info('✅ User created', ['user_id' => $user->id, 'email' => $user->email]);

                            $this->ensureStudentRoleExists();
                            $user->assignRole('student');
                            $user->refresh(); // 👈 ensures $user->id is available and reliable

                // 🎟 Generate admission number
                $admissionNumber = 'STU-' . now()->year . '-' . strtoupper(Str::random(6));
                            
                \Log::info('🧾 Creating student with guardians:', $validated['guardians']);

                // 🏫 Get compulsory subjects for this structure/stream
                $structure = \App\Models\SchoolStructure::find($validated['structure_id']);
                $compulsorySubjects = $this->getCompulsorySubjectsForStudent($structure, $validated['stream']);

                // 🎓 Create student record
                            $student = Student::create([
                                'user_id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'dob' => $validated['dob'],
                                'gender' => $validated['gender'],
                                'structure_id' => $validated['structure_id'],
                                'stream' => $validated['stream'],
                                'profile_picture' => $profilePath,
                                'admission_number' => $admissionNumber,
                                'school_id' => $schoolId,
                                'guardian_phone' => $validated['guardians'][0]['phone'],
                                'subjects' => $compulsorySubjects,
                            ]);

                            foreach ($validated['guardians'] as $guardian) {
                                $student->guardians()->create([
                                    'name' => $guardian['name'],
                                    'phone' => $guardian['phone'],
                                ]);
                            }
                // ✉️ Send email with credentials
                Mail::to($user->email)->send(new StudentAccountCreated($user, $tempPassword));
            });

            return redirect()->back()->with('success', 'Student account created and credentials emailed.');
        } catch (\Exception $e) {
            \Log::error('🛑 Student creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'form' => ['An error occurred while creating the student. Please try again.'],
            ]);
        }
    }

    /**
     * List all students for the authenticated school admin's school.
     */
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $students = \App\Models\Student::withTrashed()
            ->with(['user' => function($query) {
                $query->withTrashed();
            }, 'guardians', 'structure'])
            ->where('school_id', $schoolId)
            ->get();
        $students->each->append('profile_picture_url');
        return response()->json($students);
    }

    /**
     * Disable a student (set user.active = false)
     */
    public function disable($id)
    {
        $student = Student::findOrFail($id);
        if ($student->user) {
            $student->user->active = false;
            $student->user->save();
        }
        return response()->json(['message' => 'Student disabled successfully.']);
    }

    /**
     * Enable a student (set user.active = true)
     */
    public function enable($id)
    {
        $student = Student::findOrFail($id);
        if ($student->user) {
            $student->user->active = true;
            $student->user->save();
        }
        return response()->json(['message' => 'Student enabled successfully.']);
    }

    /**
     * Soft delete a student and their user account
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        if ($student->user) {
            $student->user->delete();
        }
        $student->delete();
        return response()->json(['message' => 'Student deleted successfully.']);
    }

    /**
     * Reset a student's password and email them
     */
    public function resetPassword($id)
    {
        $student = Student::findOrFail($id);
        if (!$student->user) {
            return response()->json(['message' => 'No user account for this student.'], 404);
        }
        $newPassword = \Illuminate\Support\Str::random(10);
        $student->user->password = \Illuminate\Support\Facades\Hash::make($newPassword);
        $student->user->save();
        \Mail::to($student->user->email)->send(new \App\Mail\StudentAccountCreated($student->user, $newPassword));
        return response()->json(['message' => 'Password reset and emailed to the student.']);
    }

    /**
     * Restore a soft-deleted student and their user account
     */
    public function restore($id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        if ($student->user) {
            $student->user->restore();
        }
        $student->restore();
        return response()->json(['message' => 'Student restored successfully.']);
    }

    /**
     * Show the fees page for the logged-in student.
     */
    public function showFees(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            abort(404, 'Student not found');
        }
        $structure = \App\Models\SchoolStructure::where('school_id', $student->school_id)
            ->where('id', $student->structure_id)
            ->first();
        $student->append('profile_picture_url');
        if (!$structure) {
            return Inertia::render('Student/Fees', [
                'currentTerm' => null,
                'onHoliday' => false,
                'fees' => [],
                'message' => 'No structure found for your level.',
                'school' => $student->school ? [
                    'name' => $student->school->name,
                    'logo' => $student->school->logo_url,
                    'background' => $student->school->background_url,
                    'slogan' => $student->school->slogan,
                ] : null
            ]);
        }
        $currentTerm = $structure->getCurrentTerm();
        if ($currentTerm) {
            $fees = $currentTerm['fees'] ?? [];
            $onHoliday = false;
            // Calculate total fees for the term
            $totalFees = collect($fees)->sum(function($fee) {
                return $fee['price'] ?? 0;
            });
            
            // Ensure invoice exists for this term
            $this->ensureInvoiceExists($student, $currentTerm, $totalFees);
            
            // Calculate total payments for the student for this term
            $termName = $currentTerm['name'] ?? null;
            $payments = \App\Models\Payment::where('student_id', $student->id)
                ->whereHas('invoice', function($q) use ($termName) {
                    $q->where('term', $termName);
                })
                ->sum('amount');
            $feeBalance = $totalFees - $payments;
        } else {
            $fees = [];
            $onHoliday = true;
            $feeBalance = 0;
        }
        return Inertia::render('Student/Fees', [
            'currentTerm' => $currentTerm,
            'onHoliday' => $onHoliday,
            'fees' => $fees,
            'structureTitle' => $structure->title,
            'feeBalance' => $feeBalance,
            'student' => $student, // <-- Add this line
            'school' => $student->school ? [
                'name' => $student->school->name,
                'logo' => $student->school->logo_url,
                'background' => $student->school->background_url,
                'slogan' => $student->school->slogan,
            ] : null
        ]);
    }

    /**
     * Ensure an invoice exists for the student for the current term.
     */
    protected function ensureInvoiceExists($student, $currentTerm, $totalFees)
    {
        $termName = $currentTerm['name'] ?? null;
        if (!$termName || $totalFees <= 0) {
            return;
        }

        // Check if invoice already exists for this term
        $existingInvoice = \App\Models\Invoice::where('student_id', $student->id)
            ->where('term', $termName)
            ->first();

        if (!$existingInvoice) {
            // Create new invoice for this term
            \App\Models\Invoice::create([
                'student_id' => $student->id,
                'amount_due' => $totalFees,
                'amount_paid' => 0,
                'status' => 'unpaid',
                'term' => $termName,
                'due_date' => $currentTerm['end_date'] ?? now()->addMonths(3),
            ]);
        }
    }

    /**
     * Ensure the "student" role exists.
     */
    protected function ensureStudentRoleExists()
    {
        if (!Role::where('name', 'student')->where('guard_name', 'web')->exists()) {
            Role::create(['name' => 'student', 'guard_name' => 'web']);
        }
    }

    /**
     * Get compulsory subjects for a student based on structure and stream.
     */
    protected function getCompulsorySubjectsForStudent($structure, $stream)
    {
        if (!$structure || !is_array($structure->structure)) {
            \Log::debug('No structure or structure is not array', ['structure' => $structure]);
            return [];
        }
        // Find the stream object (if streams are defined)
        $streams = $structure->structure['streams'] ?? [];
        $streamObj = null;
        foreach ($streams as $s) {
            if ((is_array($s) && ($s['name'] ?? $s) == $stream) || $s == $stream) {
                $streamObj = $s;
                break;
            }
        }
        // Get subjects for this structure (level)
        $subjects = $structure->structure['subjects'] ?? $structure->structure['courses'] ?? $structure->structure['courses'] ?? [];
        // If subjects are per stream, override
        if (is_array($streamObj) && isset($streamObj['subjects'])) {
            $subjects = $streamObj['subjects'];
        }
        \Log::debug('Subject extraction for student', [
            'structure_id' => $structure->id,
            'stream' => $stream,
            'streamObj' => $streamObj,
            'subjects_found' => $subjects,
            'structure_json' => $structure->structure,
        ]);
        // Filter out optional subjects (those containing '(optional)')
        $compulsory = collect($subjects)->filter(function($subj) {
            return stripos($subj, '(optional)') === false;
        })->values()->all();
        return $compulsory;
    }
}

