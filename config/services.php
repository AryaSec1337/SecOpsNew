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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'virustotal' => [
        'api_key' => env('VIRUSTOTAL_API_KEY'),
    ],

    'ipinfo' => [
        'token' => env('IPINFO_TOKEN'),
    ],

    'greynoise' => [
        'key' => env('GREYNOISE_KEY'),
    ],

    'alienvault' => [
        'key' => env('OTX_KEY'),
    ],

    'abuseipdb' => [
        'key' => env('ABUSEIPDB_KEY'),
    ],

    'google' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
    ],

    'wazuh' => [
        'url' => env('WAZUH_URL', 'https://192.168.1.100:55000'),
        'username' => env('WAZUH_USERNAME', 'wazuh-wui'),
        'password' => env('WAZUH_PASSWORD', 'wazuh'),
    ],

];
