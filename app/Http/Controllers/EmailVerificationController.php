<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmailVerificationController extends Controller
{
    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired verification token.'], 404);
        }

        // Check token expiry if you track created_at for token, e.g.:
        // if ($user->token_created_at->lt(now()->subHours(24))) {
        //     return response()->json(['message' => 'Verification token expired.'], 410);
        // }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        return response()->json([
            'message' => 'Token valid. Proceed to reset password.',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ],
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|string|min:8|confirmed', // password + password_confirmation
        ]);

        $user = User::find($validated['user_id']);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Update password & verify email
        $user->password = Hash::make($validated['password']);
        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        return response()->json(['message' => 'Password set successfully. Email verified.']);
    }

}

