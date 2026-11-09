<?php

return [
    'admin_user' => env('ADMIN_USER'),
    'admin_password' => env('ADMIN_PASSWORD'),

    // Tant que c'est false, personne ne peut payer : bouton désactivé et refus côté serveur.
    'payments_enabled' => env('DROP_PAYMENTS_ENABLED', false),

    // simulated : paiement factice en local. stripe : Stripe Checkout (clés de test).
    // Le mode simulé est refusé en production, quelle que soit cette valeur.
    'payment_mode' => env('DROP_PAYMENT_MODE', 'simulated'),
    'stripe_secret' => env('STRIPE_SECRET'),
    'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    // Drop du jour tiré au hasard dans le catalogue (heure d'ouverture en heure de Paris, voir config/app.php).
    'draw' => [
        'opens_at' => '18:00',
        'duration_minutes' => 60,
        'stock_min' => 10,
        'stock_max' => 30,
    ],
];
