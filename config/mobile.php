<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Flutter app identity
    |--------------------------------------------------------------------------
    |
    | Used for Sanctum token TTL, social-login policy, FCM, and universal /
    | app links. Team-first: social login never creates a new staff account
    | unless MOBILE_SOCIAL_AUTO_REGISTER is explicitly enabled (future client
    | self-serve).
    |
    */

    'scheme' => env('MOBILE_APP_SCHEME', 'vujade'),

    'token_ttl_days' => (int) env('MOBILE_TOKEN_TTL_DAYS', 90),

    'social_auto_register' => (bool) env('MOBILE_SOCIAL_AUTO_REGISTER', false),

    'android' => [
        'package' => env('MOBILE_ANDROID_PACKAGE', 'com.vujade.portal'),
        'sha256_cert_fingerprints' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('MOBILE_ANDROID_SHA256', ''))
        ))),
    ],

    'ios' => [
        'team_id' => env('MOBILE_IOS_TEAM_ID', env('APPLE_TEAM_ID')),
        'bundle_id' => env('MOBILE_IOS_BUNDLE_ID', 'com.vujade.portal'),
        'app_id' => env('MOBILE_IOS_APP_ID'),
        'paths' => ['/app/*', '/auth/mobile/*'],
    ],

];
