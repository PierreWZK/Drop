<?php

return [
    'admin_user' => env('ADMIN_USER'),
    'admin_password' => env('ADMIN_PASSWORD'),

    // simulated : paiement factice en local. stripe : Stripe Checkout (clés de test).
    // Le mode simulé est refusé en production, quelle que soit cette valeur.
    'payment_mode' => env('DROP_PAYMENT_MODE', 'simulated'),
    'stripe_secret' => env('STRIPE_SECRET'),
    'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
