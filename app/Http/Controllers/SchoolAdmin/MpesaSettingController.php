<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolMpesaSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MpesaSettingController extends Controller
{
    /**
     * Display M-PESA settings for the school.
     */
    public function index()
    {
        $school = auth()->user()->school;
        $settings = $school->mpesaSettings()->orderBy('created_at', 'desc')->get();
        $activeSetting = $school->activeMpesaSetting;

        return inertia('SchoolAdmin/MpesaSettings/Index', [
            'settings' => $settings,
            'activeSetting' => $activeSetting,
            'school' => $school
        ]);
    }

    /**
     * Show the form for creating a new M-PESA setting.
     */
    public function create()
    {
        return inertia('SchoolAdmin/MpesaSettings/Create');
    }

    /**
     * Store a newly created M-PESA setting.
     */
    public function store(Request $request)
    {
        \Log::info('M-PESA Setting store request received', [
            'user_id' => auth()->id(),
            'school_id' => auth()->user()->school_id,
            'request_data' => $request->except(['consumer_secret', 'passkey']),
            'all_fields_present' => [
                'consumer_key' => $request->has('consumer_key'),
                'consumer_secret' => $request->has('consumer_secret'),
                'shortcode' => $request->has('shortcode'),
                'passkey' => $request->has('passkey'),
                'environment' => $request->has('environment'),
            ],
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson()
        ]);

        \Log::info('Starting validation for M-PESA setting');
        
        try {
            $request->validate([
                'consumer_key' => 'required|string|max:255',
                'consumer_secret' => 'required|string|max:255',
                'shortcode' => 'required|string|max:10',
                'passkey' => 'required|string|max:1000',
                'environment' => 'required|in:sandbox,production',
                'callback_url' => 'nullable|url|max:500',
                'description' => 'nullable|string|max:500',
                'is_active' => 'boolean'
            ]);
            \Log::info('M-PESA Setting validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('M-PESA Setting validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['consumer_secret', 'passkey'])
            ]);
            throw $e;
        }

        $school = auth()->user()->school;

        \Log::info('Creating M-PESA setting for school', [
            'school_id' => $school->id,
            'school_name' => $school->name
        ]);

        // If this setting is active, deactivate others
        if ($request->boolean('is_active')) {
            \Log::info('Deactivating other M-PESA settings for school');
            $school->mpesaSettings()->update(['is_active' => false]);
        }

        try {
            $setting = $school->mpesaSettings()->create([
                'consumer_key' => $request->consumer_key,
                'consumer_secret' => $request->consumer_secret,
                'shortcode' => $request->shortcode,
                'passkey' => $request->passkey,
                'environment' => $request->environment,
                'callback_url' => $request->callback_url,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active')
            ]);
            \Log::info('M-PESA setting created successfully', [
                'setting_id' => $setting->id,
                'shortcode' => $setting->shortcode,
                'environment' => $setting->environment
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create M-PESA setting', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'M-PESA settings created successfully.',
                'setting' => $setting
            ]);
        }

        return redirect()->route('schooladmin.mpesa-settings.index')
            ->with('success', 'M-PESA settings created successfully.');
    }

    /**
     * Show the form for editing the specified M-PESA setting.
     */
    public function edit(SchoolMpesaSetting $mpesaSetting)
    {
        // Ensure the setting belongs to the authenticated user's school
        if ($mpesaSetting->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        return inertia('SchoolAdmin/MpesaSettings/Edit', [
            'setting' => $mpesaSetting
        ]);
    }

    /**
     * Update the specified M-PESA setting.
     */
    public function update(Request $request, SchoolMpesaSetting $mpesaSetting)
    {
        // Ensure the setting belongs to the authenticated user's school
        if ($mpesaSetting->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $request->validate([
            'consumer_key' => 'required|string|max:255',
            'consumer_secret' => 'required|string|max:255',
            'shortcode' => 'required|string|max:10',
            'passkey' => 'required|string|max:1000',
            'environment' => 'required|in:sandbox,production',
            'callback_url' => 'nullable|url|max:500',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean'
        ]);

        // If this setting is being activated, deactivate others
        if ($request->boolean('is_active')) {
            $mpesaSetting->school->mpesaSettings()
                ->where('id', '!=', $mpesaSetting->id)
                ->update(['is_active' => false]);
        }

        $mpesaSetting->update([
            'consumer_key' => $request->consumer_key,
            'consumer_secret' => $request->consumer_secret,
            'shortcode' => $request->shortcode,
            'passkey' => $request->passkey,
            'environment' => $request->environment,
            'callback_url' => $request->callback_url,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active')
        ]);

        return redirect()->route('schooladmin.mpesa-settings.index')
            ->with('success', 'M-PESA settings updated successfully.');
    }

    /**
     * Remove the specified M-PESA setting.
     */
    public function destroy(SchoolMpesaSetting $mpesaSetting)
    {
        // Ensure the setting belongs to the authenticated user's school
        if ($mpesaSetting->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $mpesaSetting->delete();

        return redirect()->route('schooladmin.mpesa-settings.index')
            ->with('success', 'M-PESA setting deleted successfully.');
    }

    /**
     * Test M-PESA credentials for the specified setting.
     */
    public function testCredentials(SchoolMpesaSetting $mpesaSetting)
    {
        // Ensure the setting belongs to the authenticated user's school
        if ($mpesaSetting->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        try {
            $baseUrl = $mpesaSetting->environment === 'sandbox' 
                ? 'https://sandbox.safaricom.co.ke' 
                : 'https://api.safaricom.co.ke';

            $url = $baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
            
            $response = Http::withBasicAuth($mpesaSetting->consumer_key, $mpesaSetting->consumer_secret)
                ->get($url);

            if ($response->successful()) {
                $tokenData = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'Credentials are valid',
                    'access_token' => $tokenData['access_token'] ?? null,
                    'environment' => $mpesaSetting->environment
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials: ' . $response->body(),
                    'status' => $response->status()
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing credentials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a specific M-PESA setting.
     */
    public function activate(SchoolMpesaSetting $mpesaSetting)
    {
        // Ensure the setting belongs to the authenticated user's school
        if ($mpesaSetting->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $mpesaSetting->activate();

        return redirect()->route('schooladmin.mpesa-settings.index')
            ->with('success', 'M-PESA setting activated successfully.');
    }

    /**
     * Test production M-PESA credentials with detailed feedback.
     */
    public function testProductionCredentials(Request $request)
    {
        $request->validate([
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'shortcode' => 'required|string',
            'passkey' => 'required|string',
            'callback_url' => 'nullable|url'
        ]);

        try {
            $baseUrl = 'https://api.safaricom.co.ke';
            $url = $baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
            
            \Log::info('Testing production M-PESA credentials', [
                'consumer_key' => $request->consumer_key,
                'shortcode' => $request->shortcode,
                'callback_url' => $request->callback_url
            ]);

            $response = Http::withBasicAuth($request->consumer_key, $request->consumer_secret)
                ->timeout(30)
                ->get($url);

            if ($response->successful()) {
                $tokenData = $response->json();
                
                // Test STK Push with minimal amount (1 KES)
                $accessToken = $tokenData['access_token'];
                $timestamp = date('YmdHis');
                $password = base64_encode($request->shortcode . $request->passkey . $timestamp);
                
                $stkPayload = [
                    'BusinessShortCode' => $request->shortcode,
                    'Password' => $password,
                    'Timestamp' => $timestamp,
                    'TransactionType' => 'CustomerPayBillOnline',
                    'Amount' => 1,
                    'PartyA' => '254700000000', // Test phone number
                    'PartyB' => $request->shortcode,
                    'PhoneNumber' => '254700000000',
                    'CallBackURL' => $request->callback_url ?: config('mpesa.callback_url'),
                    'AccountReference' => 'PROD_TEST_' . time(),
                    'TransactionDesc' => 'Production Test Payment'
                ];

                $stkResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ])->timeout(30)->post($baseUrl . '/mpesa/stkpush/v1/processrequest', $stkPayload);

                return response()->json([
                    'success' => true,
                    'message' => 'Production credentials are valid!',
                    'access_token' => $accessToken,
                    'stk_push_success' => $stkResponse->successful(),
                    'stk_response' => $stkResponse->json(),
                    'recommendations' => [
                        '✅ Credentials are working',
                        '✅ Access token generated successfully',
                        $stkResponse->successful() ? '✅ STK Push test successful' : '⚠️ STK Push test failed - check shortcode/passkey',
                        '🔒 Ready for production use'
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid production credentials',
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'recommendations' => [
                        '❌ Check your Consumer Key and Consumer Secret',
                        '❌ Ensure you have production access in Daraja portal',
                        '❌ Verify your account is activated for production'
                    ]
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Production credentials test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error testing production credentials: ' . $e->getMessage(),
                'recommendations' => [
                    '❌ Network connectivity issue',
                    '❌ Check your internet connection',
                    '❌ Verify the API endpoints are accessible'
                ]
            ], 500);
        }
    }
}
