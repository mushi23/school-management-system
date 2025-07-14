<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SchoolAccountCreated;
use Spatie\Permission\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class SchoolController extends Controller
{
    public function disable(School $school)
    {
        $school->active = false;
        $school->save();

        // Disable all users related to this school
        \App\Models\User::where('school_id', $school->id)->update(['active' => false]);

        return response()->json(['message' => 'School disabled successfully.']);
    }
    
    public function forceDestroy($id)
    {
        DB::transaction(function () use ($id) {
            $school = School::withTrashed()->findOrFail($id);

            // Delete related users too (even soft-deleted)
            User::withTrashed()->where('school_id', $school->id)->forceDelete();

            $school->forceDelete();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($school)
                ->log('Permanently deleted school and related users');
        });

        return response()->json(['message' => 'School and related users permanently deleted.']);
    }

    
    public function enable(School $school)
    {
        $school->active = true;
        $school->save();
        
        // Enable all users related to this school
        \App\Models\User::where('school_id', $school->id)->update(['active' => true]);

        return response()->json(['message' => 'School enabled successfully.']);
    }

    public function destroy(School $school)
    {
        // Soft delete all users linked to the school
        User::where('school_id', $school->id)->delete();

        // Soft delete the school
        $school->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($school)
            ->log('Soft deleted school and related users');

        return response()->json(['message' => 'School deleted successfully (soft delete).']);
    }
    
    public function markSetupComplete(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('school_admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $school = $user->school;

        if (!$school) {
            return response()->json(['message' => 'School not found.'], 404);
        }

        $school->is_setup_complete = true;
        $school->save();

        activity()
            ->causedBy($user)
            ->performedOn($school)
            ->log('Completed onboarding and marked setup as complete');

        return response()->json([
            'message' => 'School setup marked as complete.',
            'school' => $school->fresh(),
        ]);
    }

    
    public function update(Request $request, School $school)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255',
            'active' => 'sometimes|boolean',
            'is_setup_complete' => 'sometimes|boolean',
        ]);

        // Update the school with validated data
        $school->update($validated);

        // Log the activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($school)
            ->withProperties(['changes' => $validated])
            ->log('Updated school details');

        return response()->json([
            'message' => 'School updated successfully',
            'school' => $school->fresh()
        ]);
    }
    
    public function getUsers(School $school)
    {
        $users = $school->users()->get();
        return response()->json($users);
    }
    
    public function restore($id)
    {
        $school = School::withTrashed()->findOrFail($id);
        $school->restore();

        User::withTrashed()
            ->where('school_id', $school->id)
            ->restore();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($school)
            ->log('Restored school and related users');

        return response()->json(['message' => 'School and users restored successfully.']);
    }


    public function resetPassword(School $school)
    {
        $adminUser = User::where('school_id', $school->id)->role('school_admin')->first();

        if (!$adminUser) {
            return response()->json(['message' => 'School admin user not found.'], 404);
        }

        $newPassword = Str::random(10);
        $adminUser->password = Hash::make($newPassword);
        $adminUser->save();

        Mail::to($adminUser->email)->send(new SchoolAccountCreated($adminUser, $newPassword));

        return response()->json(['message' => 'Password reset and emailed to the admin.']);
    }

    // ✅ ENHANCED: Show single school with ALL related users
    public function show(School $school)
    {
        // Load the school with all related users (not just admin)
        $school->load([
            'users' => function($query) {
                $query->select('id', 'name', 'email', 'phone', 'active', 'email_verified_at', 'created_at', 'school_id')
                      ->withTrashed(); // Include soft-deleted users
            },
            // Keep the adminUser relationship for backwards compatibility
            'adminUser' => function($query) {
                $query->select('id', 'name', 'email', 'phone', 'active', 'email_verified_at', 'created_at', 'school_id');
            }
        ]);

        // Add user roles to the response
        $school->users->each(function($user) {
            $user->roles = $user->getRoleNames();
        });

        if ($school->adminUser) {
            $school->adminUser->roles = $school->adminUser->getRoleNames();
        }

        return response()->json([
            'school' => $school,
            'users_count' => $school->users->count(),
            'active_users_count' => $school->users->where('active', true)->count(),
        ]);
    }

    // ✅ ENHANCED: Add search + pagination + admin user info
    public function index(Request $request)
    {
        $query = School::withTrashed();  // includes soft deleted

        // Include admin user info by default
        $query->with(['adminUser' => function($query) {
            $query->select('id', 'name', 'email', 'phone', 'active', 'email_verified_at', 'created_at', 'school_id');
        }]);

        // Search by school name
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by county
        if ($request->has('county') && !empty($request->county)) {
            $query->where('county', $request->county);
        }

        // Filter by sub_county
        if ($request->has('sub_county') && !empty($request->sub_county)) {
            $query->where('sub_county', $request->sub_county);
        }

        // Filter by active status
        if ($request->has('active')) {
            if ($request->active === 'true' || $request->active === '1') {
                $query->where('active', true);
            } elseif ($request->active === 'false' || $request->active === '0') {
                $query->where('active', false);
            }
        }

        // Filter by deleted status
        if ($request->has('deleted')) {
            if ($request->deleted === 'only') {
                $query->onlyTrashed();
            } elseif ($request->deleted === 'false' || $request->deleted === '0') {
                $query->withoutTrashed();
            }
            // If deleted=true or deleted=1, keep withTrashed() (default)
        }

        // Filter by registration number (exact match)
        if ($request->has('registration_number') && !empty($request->registration_number)) {
            $query->where('registration_number', $request->registration_number);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'name'); // default sort by name
        $sortDirection = $request->get('sort_direction', 'asc'); // default ascending

        // Validate sort fields to prevent SQL injection
        $allowedSortFields = ['name', 'county', 'sub_county', 'total_students', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc'); // fallback
        }

        // Pagination
        if ($request->has('paginate') && $request->paginate != 'false') {
            $perPage = $request->get('per_page', 10);
            $perPage = min(max($perPage, 1), 100); // limit between 1-100
            return response()->json($query->paginate($perPage));
        }

        return response()->json($query->get());
    }
    
    public function updateCurriculum(Request $request)
    {
        $school = auth()->user()->school;
        
        // Assume frontend sends "curriculum_key" like 1 or 2
        $school->curriculum_key = $request->input('curriculum_key');
        $school->save();

        return response()->json(['message' => 'Curriculum updated']);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'county' => 'required|string',
            'sub_county' => 'required|string',
            'school_name' => 'required|string|unique:schools,name',
            'school_code' => 'required|string|unique:schools,registration_number',
            'contact_email' => 'required|email|unique:users,email',
            'contact_phone' => ['required', 'string', 'regex:/^\+?\d{7,15}$/'],
            'total_students' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated, &$school, &$user) {
                $school = School::create([
                    'name' => $validated['school_name'],
                    'registration_number' => $validated['school_code'],
                    'county' => $validated['county'],
                    'sub_county' => $validated['sub_county'],
                    'total_students' => $validated['total_students'],
                    'contact_email' => $validated['contact_email'],
                    'contact_phone' => $validated['contact_phone'],
                ]);

                $temporaryPassword = Str::random(10);

                $user = User::create([
                    'name' => $validated['school_name'] . ' Admin',
                    'email' => $validated['contact_email'],
                    'phone' => $validated['contact_phone'],
                    'password' => Hash::make($temporaryPassword),
                    'school_id' => $school->id,
                    'email_verification_token' => Str::uuid(),
                ]);

                if (!Role::where('name', 'school_admin')->where('guard_name', 'web')->exists()) {
                    Role::create(['name' => 'school_admin', 'guard_name' => 'web']);
                }

                $user->assignRole('school_admin');

                Mail::to($user->email)->send(new SchoolAccountCreated($user, $temporaryPassword));
            });

            // Load the created school with its users for the response
            $school->load([
                'users' => function($query) {
                    $query->select('id', 'name', 'email', 'phone', 'active', 'email_verified_at', 'created_at', 'school_id');
                },
                'adminUser' => function($query) {
                    $query->select('id', 'name', 'email', 'phone', 'active', 'email_verified_at', 'created_at', 'school_id');
                }
            ]);

            // Add roles to users
            $school->users->each(function($user) {
                $user->roles = $user->getRoleNames();
            });

            if ($school->adminUser) {
                $school->adminUser->roles = $school->adminUser->getRoleNames();
            }

            return response()->json([
                'message' => 'School and admin account created. A verification email has been sent.',
                'school' => $school,
                'admin_user' => $school->adminUser,
                'users_count' => $school->users->count(),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating school and user: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'message' => 'Failed to create school account. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function users(School $school)
    {
        // assuming a one-to-many relationship
        return response()->json($school->users);
    }
}
