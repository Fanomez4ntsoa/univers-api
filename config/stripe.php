<?php

return [
    'key'            => env('STRIPE_KEY', ''),
    'secret'         => env('STRIPE_SECRET', ''),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),

    'prices' => [
        'pro_monthly' => env('STRIPE_PRO_MONTHLY_PRICE_ID', ''),
        'pro_yearly'  => env('STRIPE_PRO_YEARLY_PRICE_ID', ''),
    ],
];
