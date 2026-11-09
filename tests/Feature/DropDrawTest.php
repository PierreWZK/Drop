<?php

namespace Tests\Feature;

use App\Models\CatalogProduct;
use App\Models\Drop;
use App\Services\DropDrawService;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DropDrawTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function smallCatalog(int $n): void
    {
        for ($i = 1; $i <= $n; $i++) {
            CatalogProduct::create([
                'name' => "Produit {$i}", 'description' => 'Test', 'price_cents' => 5000, 'sizes' => ['S', 'M', 'L'],
            ]);
        }
    }

    public function test_seeder_creates_fifty_distinct_products_and_is_idempotent(): void
    {
        $this->seed(CatalogSeeder::class);
        $this->seed(CatalogSeeder::class);

        $this->assertSame(50, CatalogProduct::count());
        $this->assertSame(0, CatalogProduct::where('price_cents', '<=', 0)->count());
        $this->assertSame(0, CatalogProduct::whereNull('description')->count());
    }

    public function test_draw_creates_a_drop_at_18h_for_one_hour_with_stock_10_to_30(): void
    {
        Carbon::setTestNow('2026-11-10 09:00:00');
        $this->seed(CatalogSeeder::class);

        $drop = app(DropDrawService::class)->drawFor(now());

        $this->assertNotNull($drop);
        $this->assertSame('2026-11-10 18:00:00', $drop->opens_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-10 19:00:00', $drop->closes_at->format('Y-m-d H:i:s'));
        $this->assertSame('upcoming', $drop->status());
        $this->assertNotNull($drop->catalog_product_id);
        $this->assertSame(CatalogProduct::find($drop->catalog_product_id)->name, $drop->title);
        $this->assertGreaterThanOrEqual(10, $drop->stockTotal());
        $this->assertLessThanOrEqual(30, $drop->stockTotal());
        $this->assertSame(0, $drop->sizes()->where('stock', '<', 1)->count());
    }

    public function test_stock_is_always_between_10_and_30_for_every_size_count(): void
    {
        Carbon::setTestNow('2026-11-10 09:00:00');
        $this->seed(CatalogSeeder::class);
        $service = app(DropDrawService::class);

        for ($i = 0; $i < 50; $i++) {
            $drop = $service->drawFor(now()->addDays($i));
            $this->assertGreaterThanOrEqual(10, $drop->stockTotal(), $drop->title);
            $this->assertLessThanOrEqual(30, $drop->stockTotal(), $drop->title);
            $this->assertSame(0, $drop->sizes()->where('stock', '<', 1)->count(), $drop->title);
        }
    }

    public function test_drawing_the_same_day_twice_creates_one_drop(): void
    {
        Carbon::setTestNow('2026-11-10 09:00:00');
        $this->seed(CatalogSeeder::class);
        $service = app(DropDrawService::class);

        $this->assertNotNull($service->drawFor(now()));
        $this->assertNull($service->drawFor(now()));
        $this->assertSame(1, Drop::count());
    }

    public function test_no_product_repeats_until_the_whole_catalog_has_been_drawn(): void
    {
        Carbon::setTestNow('2026-11-10 09:00:00');
        $this->smallCatalog(5);
        $service = app(DropDrawService::class);

        $firstCycle = [];
        for ($i = 0; $i < 5; $i++) {
            $firstCycle[] = $service->drawFor(now()->addDays($i))->catalog_product_id;
        }
        $this->assertCount(5, array_unique($firstCycle));

        // Le 6e tirage ouvre un nouveau cycle : tous les produits redeviennent tirables.
        $sixth = $service->drawFor(now()->addDays(5));
        $this->assertNotNull($sixth);
        $this->assertSame(1, CatalogProduct::whereNotNull('drawn_at')->count());
        $this->assertSame(2, CatalogProduct::find($sixth->catalog_product_id)->times_drawn);
    }

    public function test_command_draws_today_and_tomorrow_before_opening(): void
    {
        Carbon::setTestNow('2026-11-10 10:00:00');
        $this->seed(CatalogSeeder::class);

        $this->artisan('drops:draw')->assertSuccessful();

        $this->assertSame(
            ['2026-11-10 18:00:00', '2026-11-11 18:00:00'],
            Drop::orderBy('opens_at')->get()->map(fn ($d) => $d->opens_at->format('Y-m-d H:i:s'))->all(),
        );

        // Relancé : rien de plus.
        $this->artisan('drops:draw')->assertSuccessful();
        $this->assertSame(2, Drop::count());
    }

    public function test_command_skips_today_once_opening_time_has_passed(): void
    {
        Carbon::setTestNow('2026-11-10 19:30:00');
        $this->seed(CatalogSeeder::class);

        $this->artisan('drops:draw')->assertSuccessful();

        $this->assertSame(1, Drop::count());
        $this->assertSame('2026-11-11 18:00:00', Drop::first()->opens_at->format('Y-m-d H:i:s'));
    }

    public function test_command_with_date_can_force_a_past_opening(): void
    {
        Carbon::setTestNow('2026-11-10 19:30:00');
        $this->seed(CatalogSeeder::class);

        $this->artisan('drops:draw', ['--date' => '2026-11-10'])->assertSuccessful();

        $this->assertSame(1, Drop::count());
    }

    public function test_empty_catalog_creates_nothing_and_does_not_crash(): void
    {
        Carbon::setTestNow('2026-11-10 09:00:00');

        $this->artisan('drops:draw')->assertSuccessful();

        $this->assertSame(0, Drop::count());
    }

    public function test_home_page_shows_the_drawn_drop(): void
    {
        Carbon::setTestNow('2026-11-10 09:00:00');
        $this->seed(CatalogSeeder::class);
        $drop = app(DropDrawService::class)->drawFor(now());

        $this->get('/')->assertOk()->assertSee($drop->title);
    }

    public function test_catalog_photo_is_served_from_public_catalog_folder(): void
    {
        $drop = new Drop(['image_path' => 'catalog/veste.jpg']);
        $this->assertStringEndsWith('/catalog/veste.jpg', $drop->imageUrl());

        $upload = new Drop(['image_path' => 'drops/abc.jpg']);
        $this->assertStringEndsWith('/storage/drops/abc.jpg', $upload->imageUrl());

        $this->assertNull((new Drop)->imageUrl());
    }
}
