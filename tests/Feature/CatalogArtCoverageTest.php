<?php

namespace Tests\Feature;

use App\Models\CatalogProduct;
use App\Support\ProductArt;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogArtCoverageTest extends TestCase
{
    use RefreshDatabase;

    /** Chaque produit du catalogue a soit une photo dans public/catalog, soit une illustration dessinée. */
    public function test_every_catalog_product_has_a_photo_or_an_illustration(): void
    {
        $this->seed(CatalogSeeder::class);

        $sans = CatalogProduct::all()->filter(fn ($p) => $p->image_path === null && ! ProductArt::has($p->name));

        $this->assertSame([], $sans->pluck('name')->all(), 'Produits sans photo ni illustration');
    }

    public function test_photos_and_illustrations_do_not_overlap(): void
    {
        $this->seed(CatalogSeeder::class);

        $deux = CatalogProduct::whereNotNull('image_path')->get()->filter(fn ($p) => ProductArt::has($p->name));

        $this->assertSame([], $deux->pluck('name')->all(), 'Produits qui ont à la fois une photo et une illustration');
    }
}
