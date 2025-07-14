<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Mail\TeacherAccountCreated;

class TeacherController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('TeacherDashboard');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'dob' => 'nullable|date',
            'phone' => 'nullable|string',
            'gender' => 'required|in:male,female,other',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        $schoolId = auth()->user()->school_id;
        $tempPassword = Str::random(10);

        // Handle profile picture upload
        $profilePath = $request->hasFile('profile_picture')
            ? $request->file('profile_picture')->store('profile_pictures', 'public')
            : null;

        // Create user
        $user = User::create([
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'school_id' => $schoolId,
            'password' => Hash::make($tempPassword),
            'active' => true,
        ]);

        if (!$user || !$user->id) {
            throw new \Exception('User creation failed.');
        }

        // Ensure teacher role exists and assign
        if (!Role::where('name', 'teacher')->where('guard_name', 'web')->exists()) {
            Role::create(['name' => 'teacher', 'guard_name' => 'web']);
        }
        $user->assignRole('teacher');
        $user->refresh();

        // Create teacher record
        $teacherNumber = 'TCH-' . now()->year . '-' . strtoupper(Str::random(6));
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'dob' => $validated['dob'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'gender' => $validated['gender'],
            'profile_picture' => $profilePath,
            'school_id' => $schoolId,
            'teacher_number' => $teacherNumber,
        ]);

        // Send email with credentials and verification link
        Mail::to($user->email)->send(new TeacherAccountCreated($user, $tempPassword));

        return redirect()->back()->with('success', 'Teacher account created and credentials emailed.');
    }

    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $teachers = \App\Models\Teacher::withTrashed()
            ->with(['user' => function($query) {
                $query->withTrashed();
            }])
            ->where('school_id', $schoolId)
            ->get();
        $teachers->each->append('profile_picture_url');
        return response()->json($teachers);
    }

    public function disable($id)
    {
        $teacher = Teacher::findOrFail($id);
        if ($teacher->user) {
            $teacher->user->active = false;
            $teacher->user->save();
        }
        return response()->json(['message' => 'Teacher disabled successfully.']);
    }

    public function enable($id)
    {
        $teacher = Teacher::findOrFail($id);
        if ($teacher->user) {
            $teacher->user->active = true;
            $teacher->user->save();
        }
        return response()->json(['message' => 'Teacher enabled successfully.']);
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        if ($teacher->user) {
            $teacher->user->delete();
        }
        $teacher->delete();
        return response()->json(['message' => 'Teacher deleted successfully.']);
    }

    public function resetPassword($id)
    {
        $teacher = Teacher::findOrFail($id);
        if (!$teacher->user) {
            return response()->json(['message' => 'No user account for this teacher.'], 404);
        }
        $newPassword = \Illuminate\Support\Str::random(10);
        $teacher->user->password = \Illuminate\Support\Facades\Hash::make($newPassword);
        $teacher->user->save();
        \Mail::to($teacher->user->email)->send(new \App\Mail\TeacherAccountCreated($teacher->user, $newPassword));
        return response()->json(['message' => 'Password reset and emailed to the teacher.']);
    }

    public function restore($id)
    {
        $teacher = Teacher::withTrashed()->findOrFail($id);
        if ($teacher->user) {
            $teacher->user->restore();
        }
        $teacher->restore();
        return response()->json(['message' => 'Teacher restored successfully.']);
    }
}
