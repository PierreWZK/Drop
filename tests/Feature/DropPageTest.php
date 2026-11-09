<?php

namespace Tests\Feature;

use App\Models\Drop;
use App\Models\DropSize;
use App\Models\WaitlistEntry;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DropPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeDrop(string $opensIn, string $closesIn = '+2 hours', array $stocks = ['S' => 5, 'M' => 15]): Drop
    {
        $drop = Drop::create([
            'slug' => 'veste-test-'.uniqid(),
            'title' => 'Veste Test',
            'description' => 'Description',
            'price_cents' => 12900,
            'opens_at' => now()->modify($opensIn),
            'closes_at' => now()->modify($closesIn),
            'reservation_minutes' => 10,
        ]);
        foreach ($stocks as $label => $stock) {
            DropSize::create(['drop_id' => $drop->id, 'label' => $label, 'stock' => $stock]);
        }

        return $drop;
    }

    public function test_status_upcoming_live_soldout_closed(): void
    {
        $upcoming = $this->makeDrop('+1 hour');
        $this->assertSame('upcoming', $upcoming->status());

        $live = $this->makeDrop('-1 hour');
        $this->assertSame('live', $live->status());

        $soldout = $this->makeDrop('-1 hour', '+2 hours', ['S' => 2]);
        DropSize::where('drop_id', $soldout->id)->update(['sold' => 2]);
        $this->assertSame('soldout', $soldout->fresh()->status());

        $closed = $this->makeDrop('-2 hours', '-1 hour');
        $this->assertSame('closed', $closed->status());
    }

    public function test_status_respects_given_clock(): void
    {
        $drop = $this->makeDrop('+1 hour');
        $this->assertSame('live', $drop->status(CarbonImmutable::parse($drop->opens_at)->addSecond()));
    }

    public function test_state_endpoint_returns_stock_per_size(): void
    {
        $drop = $this->makeDrop('-1 hour');

        $this->getJson(route('drops.state', $drop))
            ->assertOk()
            ->assertHeaderMissing('ETag')
            ->assertJsonPath('status', 'live')
            ->assertJsonPath('stock_total', 20)
            ->assertJsonPath('sold', 0)
            ->assertJsonPath('sizes.0.label', 'S')
            ->assertJsonPath('sizes.0.available', 5)
            ->assertJsonPath('sizes.1.available', 15);
    }

    public function test_page_shows_disabled_button_before_opening(): void
    {
        $drop = $this->makeDrop('+1 hour');

        $this->get(route('drops.show', $drop))
            ->assertOk()
            ->assertSee('Veste Test')
            ->assertSee('129,00')
            ->assertSee('id="bouton" type="submit" disabled', false);
    }

    public function test_page_enables_button_while_live(): void
    {
        $drop = $this->makeDrop('-1 hour');

        $this->get(route('drops.show', $drop))
            ->assertOk()
            ->assertDontSee('id="bouton" type="submit" disabled', false);
    }

    public function test_waitlist_accepts_email_once_before_opening(): void
    {
        $drop = $this->makeDrop('+1 hour');

        $this->postJson(route('drops.waitlist', $drop), ['email' => 'Ami@Example.fr'])->assertOk();
        $this->postJson(route('drops.waitlist', $drop), ['email' => 'ami@example.fr'])->assertOk();

        $this->assertSame(1, WaitlistEntry::where('drop_id', $drop->id)->count());
        $this->assertSame('ami@example.fr', WaitlistEntry::first()->email);
    }

    public function test_waitlist_refused_once_open(): void
    {
        $drop = $this->makeDrop('-1 hour');

        $this->postJson(route('drops.waitlist', $drop), ['email' => 'ami@example.fr'])
            ->assertStatus(422);
        $this->assertSame(0, WaitlistEntry::count());
    }

    public function test_waitlist_rejects_bad_email(): void
    {
        $drop = $this->makeDrop('+1 hour');

        $this->postJson(route('drops.waitlist', $drop), ['email' => 'pas-un-mail'])
            ->assertStatus(422);
    }

    public function test_home_shows_upcoming_drop(): void
    {
        $this->makeDrop('+1 hour');

        $this->get('/')->assertOk()->assertSee('Veste Test');
    }
}
