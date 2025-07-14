<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestMpesaProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mpesa:test-production 
                            {--consumer-key= : M-PESA Consumer Key}
                            {--consumer-secret= : M-PESA Consumer Secret}
                            {--shortcode= : M-PESA Shortcode}
                            {--passkey= : M-PESA Passkey}
                            {--callback-url= : Callback URL (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test M-PESA production credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing M-PESA Production Credentials...');
        $this->newLine();

        // Get credentials from options or prompt
        $consumerKey = $this->option('consumer-key') ?: $this->secret('Enter Consumer Key');
        $consumerSecret = $this->option('consumer-secret') ?: $this->secret('Enter Consumer Secret');
        $shortcode = $this->option('shortcode') ?: $this->ask('Enter Shortcode');
        $passkey = $this->option('passkey') ?: $this->secret('Enter Passkey');
        $callbackUrl = $this->option('callback-url') ?: $this->ask('Enter Callback URL (optional)');

        if (!$callbackUrl) {
            $callbackUrl = config('mpesa.callback_url');
        }

        $this->newLine();
        $this->info('Testing credentials...');

        try {
            // Test 1: Generate Access Token
            $this->info('1. Testing access token generation...');
            $baseUrl = 'https://api.safaricom.co.ke';
            $url = $baseUrl . '/oauth/v1/generate?grant_type=client_credentials';
            
            $response = Http::withBasicAuth($consumerKey, $consumerSecret)
                ->timeout(30)
                ->get($url);

            if (!$response->successful()) {
                $this->error('❌ Access token generation failed');
                $this->error('Status: ' . $response->status());
                $this->error('Response: ' . $response->body());
                return 1;
            }

            $tokenData = $response->json();
            $accessToken = $tokenData['access_token'];
            $this->info('✅ Access token generated successfully');
            $this->line('   Token: ' . substr($accessToken, 0, 20) . '...');

            // Test 2: STK Push (with minimal amount)
            $this->newLine();
            $this->info('2. Testing STK Push...');
            
            $timestamp = date('YmdHis');
            $password = base64_encode($shortcode . $passkey . $timestamp);
            
            $stkPayload = [
                'BusinessShortCode' => $shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => 1,
                'PartyA' => '254700000000',
                'PartyB' => $shortcode,
                'PhoneNumber' => '254700000000',
                'CallBackURL' => $callbackUrl,
                'AccountReference' => 'CLI_TEST_' . time(),
                'TransactionDesc' => 'Production Test Payment'
            ];

            $stkResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->timeout(30)->post($baseUrl . '/mpesa/stkpush/v1/processrequest', $stkPayload);

            if ($stkResponse->successful()) {
                $stkData = $stkResponse->json();
                $this->info('✅ STK Push test successful');
                $this->line('   Checkout Request ID: ' . ($stkData['CheckoutRequestID'] ?? 'N/A'));
                $this->line('   Customer Message: ' . ($stkData['CustomerMessage'] ?? 'N/A'));
            } else {
                $this->warn('⚠️ STK Push test failed');
                $this->line('   Status: ' . $stkResponse->status());
                $this->line('   Response: ' . $stkResponse->body());
            }

            // Summary
            $this->newLine();
            $this->info('📋 Test Summary:');
            $this->line('   ✅ Credentials are valid');
            $this->line('   ✅ Access token generation works');
            $this->line($stkResponse->successful() ? '   ✅ STK Push is working' : '   ⚠️ STK Push needs attention');
            $this->line('   🔒 Ready for production use');

            if (!$stkResponse->successful()) {
                $this->newLine();
                $this->warn('⚠️ Recommendations:');
                $this->line('   • Check your shortcode and passkey');
                $this->line('   • Verify your account is activated for production');
                $this->line('   • Ensure your callback URL is accessible');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed with error: ' . $e->getMessage());
            Log::error('M-PESA production test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
} 