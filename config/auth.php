<?php

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => 'users',
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
            'model' => App\Models\User::class,
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
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
