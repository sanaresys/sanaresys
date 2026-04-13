<?php

return [
    'currency' => 'USD',

    'engine' => [
        'timezone' => 'America/Tegucigalpa',
        'grace_days' => 3,
        'max_dunning_attempts' => 3,
        'process_time' => env('BILLING_PROCESS_TIME', '08:00'),
        'consent_text_version' => env('BILLING_CONSENT_TEXT_VERSION', '2026-03-24-v1'),
    ],

    'onboarding' => [
        'free_trial_days' => (int) env('BILLING_ONBOARDING_FREE_TRIAL_DAYS', 30),
    ],

    'modules' => [
        [
            'code' => 'nomina',
            'name' => 'Modulo de nomina',
            'description' => 'Gestion de nominas con compras independientes al plan base.',
            'price_monthly' => 29.90,
            'price_annual' => 358.80,
            'currency' => 'USD',
            'is_active' => true,
        ],
    ],

    'module_billing' => [
        'reminder_offsets' => [7, 3, 1],
        'schedule_time' => env('BILLING_MODULES_SCHEDULE_TIME', '08:00'),
        'schedule_timezone' => env('BILLING_MODULES_SCHEDULE_TIMEZONE', 'America/Tegucigalpa'),
        'default_interval' => 'monthly',
    ],

    'notifications' => [
        'before_charge_days' => 1,
        'channels' => [
            'before_charge' => ['database'],
            'charge_succeeded' => ['database'],
            'charge_failed' => ['database'],
            'before_suspension' => ['database', 'mail'],
            'suspended' => ['database', 'mail'],
        ],
    ],

    'plans' => [
        'monthly' => [
            'code' => 'monthly',
            'name' => 'Plan mensual',
            'interval' => 'monthly',
            'price' => 89.90,
            'paypal_plan_id' => env('PAYPAL_PLAN_MONTHLY_ID'),
        ],
        'annual' => [
            'code' => 'annual',
            'name' => 'Plan anual',
            'interval' => 'annual',
            'price' => 919.00,
            'paypal_plan_id' => env('PAYPAL_PLAN_ANNUAL_ID'),
        ],
    ],
];
