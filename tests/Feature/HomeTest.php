<?php

namespace Tests\Feature;

use App\Models\CatalogProduct;
use App\Models\Drop;
use App\Models\DropSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeDrop(string $opensIn, string $closesIn = '+2 hours', array $stocks = ['S' => 5], ?string $image = null): Drop
    {
        $drop = Drop::create([
            'slug' => 'drop-'.uniqid(),
            'title' => 'Veste Vitrine',
            'description' => 'Une veste pour la vitrine.',
            'price_cents' => 12900,
            'image_path' => $image,
            'opens_at' => now()->modify($opensIn),
            'closes_at' => now()->modify($closesIn),
            'reservation_minutes' => 10,
        ]);
        foreach ($stocks as $label => $stock) {
            DropSize::create(['drop_id' => $drop->id, 'label' => $label, 'stock' => $stock]);
        }

        return $drop;
    }

    public function test_home_explains_the_site_and_has_one_h1(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('Un seul produit par jour, à 18 h.')
            ->assertSee('Comment ça marche')
            ->assertSee('Tu as dix minutes.')
            ->assertSee('Questions fréquentes');
        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_home_shows_a_countdown_to_opening_and_links_to_the_drop(): void
    {
        Carbon::setTestNow('2026-11-10 10:00:00');
        $drop = $this->makeDrop('+8 hours'); // ouverture à 18 h

        $this->get('/')
            ->assertOk()
            ->assertSee('08:00:00')
            ->assertSee('avant l\'ouverture')
            ->assertSee('Veste Vitrine')
            ->assertSee(route('drops.show', $drop), false);
    }

    public function test_home_shows_time_left_while_the_drop_is_live(): void
    {
        Carbon::setTestNow('2026-11-10 18:20:00');
        $this->makeDrop('-20 minutes', '+40 minutes');

        $this->get('/')
            ->assertOk()
            ->assertSee('00:40:00')
            ->assertSee('avant la fin de la vente');
    }

    public function test_home_works_without_any_drop(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('00:00:00')
            ->assertSee('Voir les archives')
            ->assertSee('Le prochain drop est tiré chaque jour.');
    }

    public function test_home_shows_three_catalog_products(): void
    {
        foreach (range(1, 5) as $i) {
            CatalogProduct::create(['name' => "Produit {$i}", 'description' => 'x', 'price_cents' => 5000, 'sizes' => ['S']]);
        }

        $this->get('/')->assertOk()->assertSee('5 produits. Un seul sort chaque jour.');
        $this->assertSame(3, substr_count($this->get('/')->getContent(), 'class="vignette"'));
    }

    public function test_product_without_photo_gets_the_flower_plate(): void
    {
        $this->makeDrop('+1 hour');

        $this->get('/')->assertOk()->assertSee('class="plate-art"', false)->assertSee('id="fleur"', false);
    }

    public function test_product_with_photo_gets_its_image(): void
    {
        $this->makeDrop('+1 hour', image: 'catalog/besace-cuir-cognac.jpg');

        $this->get('/')->assertOk()->assertSee('/catalog/besace-cuir-cognac.jpg', false);
    }

    public function test_credits_page_lists_the_photos(): void
    {
        $this->get(route('credits'))->assertOk()->assertSee('Crédits photos')->assertSee('Wikimedia Commons');
    }

    public function test_state_endpoint_still_works_for_the_countdown(): void
    {
        $drop = $this->makeDrop('-1 hour');

        $this->getJson(route('drops.state', $drop))->assertOk()->assertJsonPath('status', 'live');
    }
}
