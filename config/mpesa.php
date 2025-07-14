<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-PESA Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for M-PESA Daraja API integration.
    |
    */

    'env' => env('MPESA_ENV', 'sandbox'),

    'consumer_key' => env('MPESA_CONSUMER_KEY'),

    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),

    'shortcode' => env('MPESA_SHORTCODE', '174379'),

    'passkey' => env('MPESA_PASSKEY'),

    'callback_url' => env('MPESA_CALLBACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    |
    | Base URLs for M-PESA API endpoints.
    |
    */

    'base_url' => [
        'sandbox' => 'https://sandbox.safaricom.co.ke',
        'production' => 'https://api.safaricom.co.ke',
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Types
    |--------------------------------------------------------------------------
    |
    | Supported transaction types for M-PESA API.
    |
    */

    'transaction_types' => [
        'paybill' => 'CustomerPayBillOnline',
        'till' => 'CustomerBuyGoodsOnline',
    ],
]; 