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

        $response->assertSeeInOrder(['Un seul', 'produit', 'par jour,', 'à 18 h.'])
            ->assertSee('Comment ça marche')
            ->assertSee('Je paie')
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

    public function test_home_shows_eight_catalog_products_half_with_photo(): void
    {
        foreach (range(1, 12) as $i) {
            CatalogProduct::create([
                'name' => "Produit {$i}", 'description' => 'x', 'price_cents' => 5000, 'sizes' => ['S'],
                'image_path' => $i <= 6 ? "catalog/produit-{$i}.jpg" : null,
            ]);
        }

        $html = $this->get('/')->assertOk()->assertSee('12 produits. Un seul par jour.')->getContent();

        $this->assertSame(8, substr_count($html, 'class="nom"'));
        $this->assertSame(4, preg_match_all('#<img src="[^"]*/catalog/produit-\d+\.jpg"#', $html));
    }

    public function test_product_without_photo_gets_its_drawn_illustration_with_the_logo(): void
    {
        $drop = $this->makeDrop('+1 hour');
        $drop->update(['title' => 'T-shirt Manches Longues Rayé']);

        $this->get('/')
            ->assertOk()
            ->assertSee('class="product-art"', false)
            ->assertSee('Illustration : T-shirt Manches Longues Rayé', false)
            ->assertSee('<use href="#fleur"', false);
    }

    public function test_unknown_product_without_photo_falls_back_to_the_flower_plate(): void
    {
        $this->makeDrop('+1 hour');

        $this->get('/')->assertOk()->assertSee('class="plate-art"', false)->assertSee('id="fleur"', false);
    }

    public function test_hero_cta_and_nav_cta_point_to_the_drop(): void
    {
        $drop = $this->makeDrop('+1 hour');
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(3, substr_count($html, 'href="'.route('drops.show', $drop).'"'));
        $this->assertStringContainsString('class="btn btn-lg"', $html);
    }

    public function test_inner_pages_keep_a_header_that_goes_back_home(): void
    {
        $drop = $this->makeDrop('-1 hour');
        $reservation = app(\App\Services\ReservationService::class)->reserve($drop, $drop->sizes()->value('id'), 'a@example.fr');

        foreach ([route('drops.show', $drop), route('reservations.show', $reservation), route('archives'), route('credits')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('class="site-header"', false)
                ->assertSee('href="'.route('home').'"', false)
                ->assertSee('aria-label="Drop, retour à l\'accueil"', false);
        }
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
