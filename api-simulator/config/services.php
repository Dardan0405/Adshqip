<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_CLIENT_SECRET'),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'bitpay' => [
        'mode' => env('BITPAY_MODE', 'test'),
        'api_token' => env('BITPAY_API_TOKEN'),
        'webhook_secret' => env('BITPAY_WEBHOOK_SECRET'),
    ],

    'authorize_net' => [
        'mode' => env('AUTHORIZE_NET_MODE', 'sandbox'),
        'login_id' => env('AUTHORIZE_NET_LOGIN_ID'),
        'transaction_key' => env('AUTHORIZE_NET_TRANSACTION_KEY'),
        'signature_key' => env('AUTHORIZE_NET_SIGNATURE_KEY'),
        'client_key' => env('AUTHORIZE_NET_CLIENT_KEY'),
    ],

];
