<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Payment;
use App\Models\Invoice;

class MpesaController extends Controller
{
    protected $baseUrl;
    protected $consumerKey;
    protected $consumerSecret;
    protected $shortcode;
    protected $passkey;
    protected $callbackUrl;
    protected $environment;

    public function __construct()
    {
        // Default to global config for backward compatibility
        $this->baseUrl = config('mpesa.env') === 'sandbox' 
            ? 'https://sandbox.safaricom.co.ke' 
            : 'https://api.safaricom.co.ke';
        
        $this->consumerKey = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        $this->callbackUrl = config('mpesa.callback_url');
        $this->environment = config('mpesa.env');
    }

    /**
     * Get M-PESA settings for a specific school
     */
    protected function getSchoolMpesaSettings($schoolId)
    {
        $setting = \App\Models\SchoolMpesaSetting::getActiveForSchool($schoolId);
        
        if ($setting) {
            $this->baseUrl = $setting->environment === 'sandbox' 
                ? 'https://sandbox.safaricom.co.ke' 
                : 'https://api.safaricom.co.ke';
            
            $this->consumerKey = $setting->consumer_key;
            $this->consumerSecret = $setting->consumer_secret;
            $this->shortcode = $setting->shortcode;
            $this->passkey = $setting->passkey;
            $this->callbackUrl = $setting->callback_url ?: config('mpesa.callback_url');
            $this->environment = $setting->environment;
            
            return true;
        }
        
        return false;
    }

    /**
     * Generate M-PESA access token
     */
    protected function generateAccessToken()
    {
        $url = $this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
        
        Log::info('Generating M-PESA access token', [
            'url' => $url,
            'consumer_key' => $this->consumerKey,
            'consumer_secret_length' => strlen($this->consumerSecret),
            'base_url' => $this->baseUrl
        ]);
        
        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get($url);

        Log::info('M-PESA token response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'headers' => $response->headers()
        ]);

        if ($response->successful()) {
            $tokenData = $response->json();
            Log::info('Token generation successful', [
                'access_token' => $tokenData['access_token'] ?? 'not found',
                'token_type' => $tokenData['token_type'] ?? 'not found',
                'expires_in' => $tokenData['expires_in'] ?? 'not found'
            ]);
            return $tokenData['access_token'];
        }

        Log::error('Failed to generate M-PESA access token', [
            'response' => $response->body(),
            'status' => $response->status(),
            'headers' => $response->headers()
        ]);

        throw new \Exception('Failed to generate access token: ' . $response->body());
    }

    /**
     * Generate password for STK Push
     */
    protected function generatePassword()
    {
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        
        Log::info('Generated password for STK Push', [
            'shortcode' => $this->shortcode,
            'passkey_length' => strlen($this->passkey),
            'timestamp' => $timestamp,
            'password_length' => strlen($password),
            'password_preview' => substr($password, 0, 20) . '...'
        ]);
        
        return [
            'password' => $password,
            'timestamp' => $timestamp
        ];
    }

    /**
     * Test M-PESA credentials
     */
    public function testCredentials()
    {
        try {
            $accessToken = $this->generateAccessToken();
            
            // Test STK Push with minimal parameters
            $passwordData = $this->generatePassword();
            
            $testPayload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $passwordData['password'],
                'Timestamp' => $passwordData['timestamp'],
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => 1, // Minimum amount for testing
                'PartyA' => '254711111111',
                'PartyB' => $this->shortcode,
                'PhoneNumber' => '254711111111',
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => 'TEST_' . time(),
                'TransactionDesc' => 'Test Payment'
            ];
            
            $stkUrl = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($stkUrl, $testPayload);
            
            return response()->json([
                'success' => true,
                'message' => 'Credentials are valid',
                'access_token' => $accessToken,
                'stk_test_response' => [
                    'status' => $response->status(),
                    'body' => $response->json(),
                    'success' => $response->successful()
                ],
                'config' => [
                    'base_url' => $this->baseUrl,
                    'shortcode' => $this->shortcode,
                    'callback_url' => $this->callbackUrl,
                    'env' => config('mpesa.env'),
                    'passkey_length' => strlen($this->passkey)
                ],
                'test_payload' => $testPayload
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Credentials test failed: ' . $e->getMessage(),
                'config' => [
                    'base_url' => $this->baseUrl,
                    'shortcode' => $this->shortcode,
                    'callback_url' => $this->callbackUrl,
                    'env' => config('mpesa.env')
                ]
            ], 400);
        }
    }

    /**
     * Initiate STK Push payment
     */
    public function initiatePayment(Request $request)
    {
        // Debug authentication
        Log::info('Payment initiation - Auth check', [
            'user' => auth()->user() ? auth()->user()->id : 'not authenticated',
            'session_id' => session()->getId(),
            'headers' => $request->headers->all()
        ]);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone_number' => 'required|string|regex:/^2547\d{8}$/',
            'student_id' => 'required|exists:students,id',
            'term' => 'required|string',
        ]);

        // Get the student and their school
        $student = \App\Models\Student::findOrFail($request->student_id);
        $school = $student->school;
        
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'Student is not associated with any school'
            ], 400);
        }

        // Try to get school-specific M-PESA settings
        $hasSchoolSettings = $this->getSchoolMpesaSettings($school->id);
        
        if (!$hasSchoolSettings) {
            Log::warning('No M-PESA settings found for school', ['school_id' => $school->id]);
            return response()->json([
                'success' => false,
                'message' => 'M-PESA payment is not configured for this school. Please contact the school administrator.'
            ], 400);
        }

        // For sandbox testing, use a test phone number
        $phoneNumber = $request->phone_number;
        $amount = $request->amount;
        
        if ($this->environment === 'sandbox') {
            // Use sandbox test phone number
            $phoneNumber = '254711111111'; // Changed to another test number
            // Alternative test numbers to try:
            // $phoneNumber = '254708374149'; // Original test number
            // $phoneNumber = '254700000000'; // Previous test number
            
            // No amount limit for sandbox testing - use the full amount requested
            // $amount = min($amount, 1000); // Removed limit for better testing
        }

        try {
            // Debug: Log the configuration being used
            Log::info('M-PESA Configuration for payment', [
                'base_url' => $this->baseUrl,
                'consumer_key' => $this->consumerKey,
                'consumer_secret_length' => strlen($this->consumerSecret),
                'shortcode' => $this->shortcode,
                'passkey_length' => strlen($this->passkey),
                'callback_url' => $this->callbackUrl,
                'env' => $this->environment,
                'school_id' => $school->id,
                'student_id' => $student->id
            ]);

            // Test credentials first
            $accessToken = $this->generateAccessToken();
            
            // Debug: Log the access token
            Log::info('Generated access token for payment', [
                'access_token' => $accessToken,
                'token_length' => strlen($accessToken)
            ]);
            
            $passwordData = $this->generatePassword();

            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $passwordData['password'],
                'Timestamp' => $passwordData['timestamp'],
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $amount,
                'PartyA' => $phoneNumber,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => 'STUDENT_' . $request->student_id . '_' . time(),
                'TransactionDesc' => 'School Fees Payment'
            ];

            Log::info('STK Push payload', $payload);

            // Add a small delay to avoid rate limiting
            sleep(2); // Increased from 1 to 2 seconds
            
            $stkUrl = $this->baseUrl . '/mpesa/stkpush/v1/processrequest';
            
            Log::info('Making STK Push request', [
                'url' => $stkUrl,
                'access_token' => $accessToken,
                'token_length' => strlen($accessToken),
                'payload' => $payload
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($stkUrl, $payload);

            Log::info('STK Push response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Store the checkout request for later verification
                DB::table('mpesa_checkout_requests')->insert([
                    'checkout_request_id' => $data['CheckoutRequestID'],
                    'merchant_request_id' => $data['MerchantRequestID'],
                    'student_id' => $request->student_id,
                    'amount' => $amount,
                    'phone_number' => $request->phone_number,
                    'term' => $request->term,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment prompt sent to your phone. Please check your M-PESA app.',
                    'checkout_request_id' => $data['CheckoutRequestID'],
                    'customer_message' => $data['CustomerMessage']
                ]);
            }

            $errorData = $response->json();
            Log::error('STK Push failed', [
                'response' => $response->body(),
                'status' => $response->status(),
                'error_code' => $errorData['errorCode'] ?? null,
                'error_message' => $errorData['errorMessage'] ?? null
            ]);

            // For sandbox environment, provide a mock response when STK Push fails
            if ($this->environment === 'sandbox' && $errorData['errorCode'] === '500.001.1001') {
                Log::info('Sandbox environment detected - providing mock response');
                
                // Generate a mock checkout request ID
                $mockCheckoutRequestId = 'ws_CO_' . date('YmdHis') . '_' . rand(100000, 999999);
                $mockMerchantRequestId = '29115-34620561-1';
                
                // Store the mock checkout request for later verification
                DB::table('mpesa_checkout_requests')->insert([
                    'checkout_request_id' => $mockCheckoutRequestId,
                    'merchant_request_id' => $mockMerchantRequestId,
                    'student_id' => $request->student_id,
                    'amount' => $amount,
                    'phone_number' => $request->phone_number,
                    'term' => $request->term,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Sandbox: Payment prompt sent to your phone. Please check your M-PESA app.',
                    'checkout_request_id' => $mockCheckoutRequestId,
                    'customer_message' => 'Success. Request accepted for processing',
                    'sandbox_mode' => true
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment: ' . ($errorData['errorMessage'] ?? 'Unknown error'),
                'error_code' => $errorData['errorCode'] ?? null
            ], 400);

        } catch (\Exception $e) {
            Log::error('STK Push error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle M-PESA callback
     */
    public function handleCallback(Request $request)
    {
        Log::info('M-PESA Callback received', $request->all());

        try {
            $body = $request->input('Body.stkCallback');
            
            if (!$body) {
                Log::error('Invalid callback format', $request->all());
                return response()->json(['status' => 'error'], 400);
            }

            $checkoutRequestId = $body['CheckoutRequestID'];
            $resultCode = $body['ResultCode'];
            $resultDesc = $body['ResultDesc'];

            // Find the checkout request
            $checkoutRequest = DB::table('mpesa_checkout_requests')
                ->where('checkout_request_id', $checkoutRequestId)
                ->first();

            if (!$checkoutRequest) {
                Log::error('Checkout request not found', ['checkout_request_id' => $checkoutRequestId]);
                return response()->json(['status' => 'error'], 400);
            }

            // Update checkout request status
            DB::table('mpesa_checkout_requests')
                ->where('checkout_request_id', $checkoutRequestId)
                ->update([
                    'status' => $resultCode === 0 ? 'completed' : 'failed',
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc,
                    'updated_at' => now()
                ]);

            if ($resultCode === 0) {
                // Payment successful - extract payment details
                $metadata = $body['CallbackMetadata']['Item'] ?? [];
                $paymentData = [];
                foreach ($metadata as $item) {
                    $paymentData[$item['Name']] = $item['Value'];
                }

                // Determine student_id using phone or AccountReference
                $studentId = $checkoutRequest->student_id;
                $phone = $paymentData['PhoneNumber'] ?? null;
                $accountReference = $body['AccountReference'] ?? null;

                if ($phone && Str::contains($phone, '*')) {
                    // Phone is masked, try to use AccountReference if you store it uniquely
                    Log::info('Phone number is masked, using AccountReference for lookup', [
                        'account_reference' => $accountReference
                    ]);
                    // Example: $studentId = Student::where('reference', $accountReference)->value('id');
                    // If you store AccountReference uniquely, implement lookup here
                }

                // Create payment record
                $payment = Payment::create([
                    'student_id' => $studentId,
                    'amount' => $paymentData['Amount'] ?? $checkoutRequest->amount,
                    'method' => 'mpesa',
                    'reference' => $paymentData['MpesaReceiptNumber'] ?? null,
                    'payment_date' => now(),
                    'notes' => 'M-PESA STK Push payment - ' . ($paymentData['MpesaReceiptNumber'] ?? 'N/A')
                ]);

                // Find and update the invoice
                $invoice = Invoice::where('student_id', $studentId)
                    ->where('term', $checkoutRequest->term)
                    ->first();

                if ($invoice) {
                    $invoice->amount_paid += $payment->amount;
                    $invoice->status = $invoice->amount_paid >= $invoice->amount_due ? 'paid' : 'partial';
                    $invoice->save();

                    // Link payment to invoice
                    $payment->invoice_id = $invoice->id;
                    $payment->save();
                }

                // Generate receipt for the payment
                try {
                    \App\Models\Receipt::createFromPayment($payment, $checkoutRequest->term, date('Y'));
                } catch (\Exception $e) {
                    Log::error('Failed to generate receipt for payment', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage()
                    ]);
                }

                Log::info('Payment processed successfully', [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'receipt' => $paymentData['MpesaReceiptNumber'] ?? null
                ]);
            } else {
                Log::warning('Payment failed', [
                    'checkout_request_id' => $checkoutRequestId,
                    'result_code' => $resultCode,
                    'result_desc' => $resultDesc
                ]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Callback processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Simulate sandbox payment completion (for testing)
     */
    public function simulateSandboxPayment(Request $request)
    {
        $request->validate([
            'checkout_request_id' => 'required|string'
        ]);

        $checkoutRequest = DB::table('mpesa_checkout_requests')
            ->where('checkout_request_id', $request->checkout_request_id)
            ->first();

        if (!$checkoutRequest) {
            return response()->json(['success' => false, 'message' => 'Checkout request not found'], 404);
        }

        // Get the student and check if we're in sandbox mode
        $student = \App\Models\Student::find($checkoutRequest->student_id);
        if (!$student || !$student->school) {
            return response()->json(['success' => false, 'message' => 'Student or school not found'], 404);
        }

        // Get school M-PESA settings to check environment
        $setting = \App\Models\SchoolMpesaSetting::getActiveForSchool($student->school_id);
        if (!$setting || $setting->environment !== 'sandbox') {
            return response()->json(['success' => false, 'message' => 'This endpoint is only available in sandbox mode'], 400);
        }

        $request->validate([
            'checkout_request_id' => 'required|string'
        ]);

        $checkoutRequest = DB::table('mpesa_checkout_requests')
            ->where('checkout_request_id', $request->checkout_request_id)
            ->first();

        if (!$checkoutRequest) {
            return response()->json(['success' => false, 'message' => 'Checkout request not found'], 404);
        }

        // Simulate successful payment
        DB::table('mpesa_checkout_requests')
            ->where('checkout_request_id', $request->checkout_request_id)
            ->update([
                'status' => 'completed',
                'result_code' => 0,
                'result_desc' => 'The service request is processed successfully.',
                'updated_at' => now()
            ]);

        // Create payment record
        $payment = Payment::create([
            'student_id' => $checkoutRequest->student_id,
            'amount' => $checkoutRequest->amount,
            'method' => 'mpesa',
            'reference' => 'SANDBOX_' . time(),
            'payment_date' => now(),
            'notes' => 'M-PESA Sandbox payment simulation'
        ]);

        // Find and update the invoice
        $invoice = Invoice::where('student_id', $checkoutRequest->student_id)
            ->where('term', $checkoutRequest->term)
            ->first();

        if ($invoice) {
            $invoice->amount_paid += $payment->amount;
            $invoice->status = $invoice->amount_paid >= $invoice->amount_due ? 'paid' : 'partial';
            $invoice->save();

            // Link payment to invoice
            $payment->invoice_id = $invoice->id;
            $payment->save();
        }

        // Generate receipt for the payment
        try {
            \App\Models\Receipt::createFromPayment($payment, $checkoutRequest->term, date('Y'));
        } catch (\Exception $e) {
            Log::error('Failed to generate receipt for sandbox payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sandbox payment simulated successfully',
            'payment_id' => $payment->id
        ]);
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'checkout_request_id' => 'required|string'
        ]);

        $checkoutRequest = DB::table('mpesa_checkout_requests')
            ->where('checkout_request_id', $request->checkout_request_id)
            ->first();

        if (!$checkoutRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Payment request not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'status' => $checkoutRequest->status,
            'result_code' => $checkoutRequest->result_code,
            'result_desc' => $checkoutRequest->result_desc
        ]);
    }

    /**
     * Test different sandbox configurations
     */
    public function testSandboxConfig()
    {
        $tests = [];
        
        // Test 1: Current configuration
        try {
            $accessToken = $this->generateAccessToken();
            $passwordData = $this->generatePassword();
            
            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $passwordData['password'],
                'Timestamp' => $passwordData['timestamp'],
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => 1,
                'PartyA' => '254711111111',
                'PartyB' => $this->shortcode,
                'PhoneNumber' => '254711111111',
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => 'TEST_' . time(),
                'TransactionDesc' => 'Test Payment'
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $payload);
            
            $tests['current_config'] = [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            $tests['current_config'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Test 2: Different phone number
        try {
            $accessToken = $this->generateAccessToken();
            $passwordData = $this->generatePassword();
            
            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $passwordData['password'],
                'Timestamp' => $passwordData['timestamp'],
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => 1,
                'PartyA' => '254708374149',
                'PartyB' => $this->shortcode,
                'PhoneNumber' => '254708374149',
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => 'TEST_' . time(),
                'TransactionDesc' => 'Test Payment'
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $payload);
            
            $tests['different_phone'] = [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            $tests['different_phone'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Test 3: Different amount
        try {
            $accessToken = $this->generateAccessToken();
            $passwordData = $this->generatePassword();
            
            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $passwordData['password'],
                'Timestamp' => $passwordData['timestamp'],
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => 10,
                'PartyA' => '254711111111',
                'PartyB' => $this->shortcode,
                'PhoneNumber' => '254711111111',
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => 'TEST_' . time(),
                'TransactionDesc' => 'Test Payment'
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $payload);
            
            $tests['different_amount'] = [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            $tests['different_amount'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Test 4: Different transaction type (Till Number)
        try {
            $accessToken = $this->generateAccessToken();
            $passwordData = $this->generatePassword();
            
            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $passwordData['password'],
                'Timestamp' => $passwordData['timestamp'],
                'TransactionType' => 'CustomerBuyGoodsOnline',
                'Amount' => 1,
                'PartyA' => '254711111111',
                'PartyB' => $this->shortcode,
                'PhoneNumber' => '254711111111',
                'CallBackURL' => $this->callbackUrl,
                'AccountReference' => 'TEST_' . time(),
                'TransactionDesc' => 'Test Payment'
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $payload);
            
            $tests['till_transaction'] = [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            $tests['till_transaction'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Test 5: Without callback URL (some sandbox environments don't require it)
        try {
            $accessToken = $this->generateAccessToken();
            $passwordData = $this->generatePassword();
            
            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $passwordData['password'],
                'Timestamp' => $passwordData['timestamp'],
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => 1,
                'PartyA' => '254711111111',
                'PartyB' => $this->shortcode,
                'PhoneNumber' => '254711111111',
                'AccountReference' => 'TEST_' . time(),
                'TransactionDesc' => 'Test Payment'
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $payload);
            
            $tests['no_callback'] = [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            $tests['no_callback'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        return response()->json([
            'success' => true,
            'tests' => $tests,
            'config' => [
                'base_url' => $this->baseUrl,
                'shortcode' => $this->shortcode,
                'callback_url' => $this->callbackUrl,
                'env' => config('mpesa.env'),
                'passkey_length' => strlen($this->passkey)
            ]
        ]);
    }

    /**
     * Test C2B simulation (alternative to STK Push for sandbox)
     */
    public function testC2BSimulation()
    {
        try {
            $accessToken = $this->generateAccessToken();
            
            $payload = [
                'ShortCode' => $this->shortcode,
                'CommandID' => 'CustomerPayBillOnline',
                'Amount' => 1,
                'Msisdn' => '254711111111',
                'BillReferenceNumber' => 'TEST_' . time()
            ];
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/mpesa/c2b/v1/simulate', $payload);
            
            return response()->json([
                'success' => true,
                'c2b_test' => [
                    'success' => $response->successful(),
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'payload' => $payload
                ],
                'config' => [
                    'base_url' => $this->baseUrl,
                    'shortcode' => $this->shortcode,
                    'env' => config('mpesa.env')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'config' => [
                    'base_url' => $this->baseUrl,
                    'shortcode' => $this->shortcode,
                    'env' => config('mpesa.env')
                ]
            ], 400);
        }
    }

    /**
     * Test basic sandbox connectivity
     */
    public function testSandboxConnectivity()
    {
        $tests = [];
        
        // Test 1: Basic connectivity to sandbox
        try {
            $response = Http::timeout(10)->get($this->baseUrl);
            $tests['basic_connectivity'] = [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 200)
            ];
        } catch (\Exception $e) {
            $tests['basic_connectivity'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Test 2: OAuth endpoint
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');
            $tests['oauth_endpoint'] = [
                'success' => $response->status() === 401, // Should return 401 without auth
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 200)
            ];
        } catch (\Exception $e) {
            $tests['oauth_endpoint'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        // Test 3: STK Push endpoint (without auth)
        try {
            $response = Http::timeout(10)->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', []);
            $tests['stk_push_endpoint'] = [
                'success' => $response->status() === 401, // Should return 401 without auth
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 200)
            ];
        } catch (\Exception $e) {
            $tests['stk_push_endpoint'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        
        return response()->json([
            'success' => true,
            'tests' => $tests,
            'config' => [
                'base_url' => $this->baseUrl,
                'env' => config('mpesa.env')
            ]
        ]);
    }
}
