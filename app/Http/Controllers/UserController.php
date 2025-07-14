<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SchoolAccountCreated;

class UserController extends Controller
{
    // List users with optional filters (e.g., role, search)
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->role($request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        return $query->paginate(15);
    }

    // View single user
    public function show(User $user)
    {
        return response()->json($user);
    }

    // Update user details
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json(['message' => 'User updated successfully.']);
    }

    // Soft disable a user
    public function disable(User $user)
    {
        $user->active = false; // Make sure 'active' boolean exists
        $user->save();

        return response()->json(['message' => 'User disabled.']);
    }

    // Enable a disabled user
    public function enable(User $user)
    {
        $user->active = true;
        $user->save();

        return response()->json(['message' => 'User enabled.']);
    }

    // Reset user password and send email
    public function resetPassword(User $user)
    {
        $newPassword = Str::random(10);
        $user->password = Hash::make($newPassword);
        $user->save();

        Mail::to($user->email)->send(new SchoolAccountCreated($user, $newPassword));

        return response()->json(['message' => 'Password reset and sent to user.']);
    }

    // Delete user (hard delete)
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}

