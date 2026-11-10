<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Charge le catalogue de 50 produits (idempotent). Les drops sont tirés par `php artisan drops:draw`. */
    public function run(): void
    {
        $this->call(CatalogSeeder::class);
    }
}
