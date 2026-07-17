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
    'iarecep_ai' => [
        'key' => env('IARECEP_AI_KEY'),
        'model' => env('IARECEP_AI_MODEL', 'claude-sonnet-4-6'),
    ],
    'vapi' => [
        'public_key'     => env('VAPI_PUBLIC_KEY'),
        'assistant_id'   => env('VAPI_ASSISTANT_ID'),
        'private_key'    => env('VAPI_PRIVATE_KEY'),
        'webhook_secret' => env('VAPI_WEBHOOK_SECRET'),
        'notify_email'   => env('VAPI_NOTIFY_EMAIL', env('MAIL_FROM_ADDRESS')),
    ],
    'admin' => [
        'code' => env('ADMIN_ACCESS_CODE', '1234'),
    ],

    'papi' => [
        'token' => env('PAPI_TOKEN'),
        'test_mode' => env('PAPI_TEST_MODE', true),
        // Taux de conversion EUR → MGA utilisé uniquement pour transmettre le montant à Papi
        // (le client voit et paie un prix asffiché en euros, Papi n'accepte que le MGA).
        'eur_to_mga_rate' => (float) env('PAPI_EUR_TO_MGA_RATE', 4800),
    ],
];
