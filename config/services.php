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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'tap' => [
        'api_key' => env('TAP_SECRET_KEY', 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ')
    ],

    'onesignal' => [
        'app_id' => env('ONESIGNAL_APP_ID'),
        'rest_api_key' => env('ONESIGNAL_REST_API_KEY'),
        'user_auth_key' => env('ONESIGNAL_USER_AUTH_KEY'),
    ],

    'kwt_sms' => [
        'username' => env('KWT_SMS_USERNAME', 'halsaffar'),
        'password' => env('KWT_SMS_PASSWORD', 'Hussain97:*'),
        'senderName' => env('KWT_SMS_SENDER', 'Jawib'),
    ],

    'onesignal' => [
        'app_id'                => env('ONESIGNAL_APP_ID'),
        'api_key'               => env('ONESIGNAL_REST_API_KEY'),
        'user_auth_key'         => env('ONESIGNAL_USER_AUTH_KEY'),
        'guzzle_client_timeout' => env('ONESIGNAL_GUZZLE_CLIENT_TIMEOUT', 0),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT_URL'),
    ],


];
