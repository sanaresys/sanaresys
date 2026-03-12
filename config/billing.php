<?php

return [
    'currency' => 'USD',

    'plans' => [
        'monthly' => [
            'code' => 'monthly',
            'name' => 'Plan mensual',
            'price' => 89.90,
            'paypal_plan_id' => env('PAYPAL_PLAN_MONTHLY_ID'),
        ],
        'annual' => [
            'code' => 'annual',
            'name' => 'Plan anual',
            'price' => 919.00,
            'paypal_plan_id' => env('PAYPAL_PLAN_ANNUAL_ID'),
        ],
    ],
];
