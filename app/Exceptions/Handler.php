<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    // ... your existing methods and properties

    public function render($request, Throwable $exception)
    {
        // 1. Handle Validation Errors
        if ($exception instanceof ValidationException) {
            $errors = $exception->errors();

            // Map keys to friendly names (adjust as needed)
            $friendlyKeys = [
                'school_name' => 'School Name',
                'email' => 'Email Address',
                'password' => 'Password',
                // Add more field mappings here
            ];

            $friendlyErrors = [];
            foreach ($errors as $key => $messages) {
                $friendlyKey = $friendlyKeys[$key] ?? $key;
                $friendlyErrors[$friendlyKey] = $messages;
            }

            return response()->json([
                'message' => 'There was a validation error.',
                'errors' => $friendlyErrors,
            ], 422);
        }

        // 2. Handle Database Constraint Violations
        if ($exception instanceof QueryException) {
            if ($request->expectsJson()) {
                $errorCode = $exception->getCode();

                // SQLSTATE error code mappings (add more if needed)
                $messages = [
                    '23000' => 'A database error occurred. Possible duplicate entry or constraint violation.',
                    '23505' => 'Duplicate entry detected.', // PostgreSQL unique violation
                    // Add other SQLSTATE codes here
                ];

                $message = $messages[$errorCode] ?? 'A database error occurred. Please check your data.';

                // Log the error details (for debugging)
                Log::error('Database error', [
                    'message' => $exception->getMessage(),
                    'code' => $errorCode,
                    'sql' => $exception->getSql(),
                    'bindings' => $exception->getBindings(),
                ]);

                return response()->json([
                    'message' => $message,
                    'error_code' => $errorCode,
                ], 409);
            }
        }

        // 3. Handle Authentication Errors
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated. Please login to access this resource.',
            ], 401);
        }

        // 4. Handle Authorization Errors
        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to perform this action.',
            ], 403);
        }

        // 5. For all other exceptions, keep Laravel's default behavior
        return parent::render($request, $exception);
    }
}

