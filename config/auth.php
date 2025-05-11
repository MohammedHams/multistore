<?php

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
        'store-owner' => [
            'driver' => 'session',
            'provider' => 'store-owners',
        ],
        'store-staff' => [
            'driver' => 'session',
            'provider' => 'store-staff',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
        'store-owners' => [
            'driver' => 'eloquent',
            'model' => App\Models\StoreOwner::class,
        ],
        'store-staff' => [
            'driver' => 'eloquent',
            'model' => App\Models\StoreStaff::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        'admins' => [
            'provider' => 'admins',
            'table' => 'admin_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'store-owners' => [
            'provider' => 'store-owners',
            'table' => 'store_owner_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'store-staff' => [
            'provider' => 'store-staff',
            'table' => 'store_staff_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
