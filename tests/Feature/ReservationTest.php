<?php

namespace Tests\Feature;

use App\Exceptions\ReservationException;
use App\Models\Drop;
use App\Models\DropSize;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    private function makeDrop(string $opensIn = '-1 hour', string $closesIn = '+2 hours', array $stocks = ['S' => 5, 'M' => 15]): Drop
    {
        $drop = Drop::create([
            'slug' => 'drop-'.uniqid(),
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

    private function sizeId(Drop $drop, string $label): int
    {
        return $drop->sizes()->where('label', $label)->value('id');
    }

    public function test_reserve_creates_reservation_and_takes_stock(): void
    {
        $drop = $this->makeDrop();

        $response = $this->post(route('drops.reserve', $drop), [
            'email' => 'Ami@Example.fr',
            'drop_size_id' => $this->sizeId($drop, 'S'),
        ]);

        $reservation = Reservation::firstOrFail();
        $response->assertRedirect(route('reservations.show', $reservation));
        $this->assertSame('ami@example.fr', $reservation->email);
        $this->assertSame('reserved', $reservation->status);
        $this->assertSame(1, DropSize::find($this->sizeId($drop, 'S'))->reserved);
        $this->assertTrue($reservation->expires_at->between(now()->addMinutes(9), now()->addMinutes(10)->addSecond()));

        $this->get(route('reservations.show', $reservation))->assertOk()->assertSee('Il te reste');
    }

    public function test_forced_post_before_opening_is_refused(): void
    {
        $drop = $this->makeDrop('+1 hour');

        $this->post(route('drops.reserve', $drop), [
            'email' => 'ami@example.fr',
            'drop_size_id' => $this->sizeId($drop, 'S'),
        ])->assertSessionHas('reserve_error', ReservationException::MESSAGES['not_open']);

        $this->assertSame(0, Reservation::count());
        $this->assertSame(0, (int) DropSize::sum('reserved'));
    }

    public function test_post_after_closing_is_refused(): void
    {
        $drop = $this->makeDrop('-3 hours', '-1 hour');

        $this->post(route('drops.reserve', $drop), [
            'email' => 'ami@example.fr',
            'drop_size_id' => $this->sizeId($drop, 'S'),
        ])->assertSessionHas('reserve_error', ReservationException::MESSAGES['closed']);

        $this->assertSame(0, Reservation::count());
    }

    public function test_one_active_reservation_per_email_and_drop(): void
    {
        $drop = $this->makeDrop();

        $this->post(route('drops.reserve', $drop), ['email' => 'ami@example.fr', 'drop_size_id' => $this->sizeId($drop, 'S')]);
        // Autre navigateur (sans session) : refusé, et le lien n'est pas révélé.
        $this->flushSession();
        $this->post(route('drops.reserve', $drop), ['email' => 'AMI@example.fr', 'drop_size_id' => $this->sizeId($drop, 'M')])
            ->assertSessionHas('reserve_error', ReservationException::MESSAGES['already_reserved']);

        $this->assertSame(1, Reservation::count());
        $this->assertSame(0, DropSize::find($this->sizeId($drop, 'M'))->reserved);
    }

    public function test_same_browser_is_sent_back_to_its_reservation(): void
    {
        $drop = $this->makeDrop();
        $payload = ['email' => 'ami@example.fr', 'drop_size_id' => $this->sizeId($drop, 'S')];

        $this->post(route('drops.reserve', $drop), $payload);
        $this->post(route('drops.reserve', $drop), $payload)
            ->assertRedirect(route('reservations.show', Reservation::firstOrFail()));

        $this->assertSame(1, Reservation::count());
    }

    public function test_stock_is_never_exceeded(): void
    {
        $drop = $this->makeDrop(stocks: ['S' => 5]);
        $sizeId = $this->sizeId($drop, 'S');
        $service = app(ReservationService::class);

        $ok = 0;
        for ($i = 0; $i < 30; $i++) {
            try {
                $service->reserve($drop, $sizeId, "acheteur{$i}@example.fr");
                $ok++;
            } catch (ReservationException $e) {
                $this->assertSame('soldout', $e->reason);
            }
        }

        $this->assertSame(5, $ok);
        $this->assertSame(5, Reservation::where('status', 'reserved')->count());
        $this->assertSame(5, DropSize::find($sizeId)->reserved);
    }

    public function test_size_from_another_drop_is_refused(): void
    {
        $drop = $this->makeDrop();
        $other = $this->makeDrop();

        $this->expectException(ReservationException::class);
        app(ReservationService::class)->reserve($drop, $this->sizeId($other, 'S'), 'ami@example.fr');
    }

    public function test_expired_reservation_returns_to_stock_via_command(): void
    {
        $drop = $this->makeDrop(stocks: ['S' => 1]);
        $sizeId = $this->sizeId($drop, 'S');
        app(ReservationService::class)->reserve($drop, $sizeId, 'a@example.fr');
        $this->assertSame(1, DropSize::find($sizeId)->reserved);

        $this->travel(11)->minutes();
        $this->artisan('drops:expire-reservations')->assertSuccessful();

        $this->assertSame(0, DropSize::find($sizeId)->reserved);
        $this->assertSame('expired', Reservation::first()->status);

        // Le même acheteur peut reprendre un exemplaire.
        app(ReservationService::class)->reserve($drop, $sizeId, 'a@example.fr');
        $this->assertSame(1, DropSize::find($sizeId)->reserved);
    }

    public function test_expiry_is_also_checked_on_new_reservation_without_the_scheduler(): void
    {
        $drop = $this->makeDrop(stocks: ['S' => 1]);
        $sizeId = $this->sizeId($drop, 'S');
        $service = app(ReservationService::class);
        $service->reserve($drop, $sizeId, 'a@example.fr');

        $this->travel(11)->minutes();
        $b = $service->reserve($drop, $sizeId, 'b@example.fr');

        $this->assertSame('reserved', $b->status);
        $this->assertSame(1, DropSize::find($sizeId)->reserved);
        $this->assertSame('expired', Reservation::where('email', 'a@example.fr')->first()->status);
    }

    public function test_paid_reservation_is_not_expired(): void
    {
        $drop = $this->makeDrop(stocks: ['S' => 1]);
        $sizeId = $this->sizeId($drop, 'S');
        $reservation = app(ReservationService::class)->reserve($drop, $sizeId, 'a@example.fr');
        $reservation->update(['status' => 'paid']);

        $this->travel(11)->minutes();
        $this->assertSame(0, app(ReservationService::class)->expireStale());
        $this->assertSame('paid', $reservation->fresh()->status);
    }

    public function test_reserve_endpoint_is_rate_limited(): void
    {
        $drop = $this->makeDrop();
        $payload = ['email' => 'pas-un-mail', 'drop_size_id' => 1];

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('drops.reserve', $drop), $payload)->assertStatus(302);
        }
        $this->post(route('drops.reserve', $drop), $payload)->assertStatus(429);
    }

    public function test_expired_reservation_page_shows_message(): void
    {
        $drop = $this->makeDrop();
        $reservation = app(ReservationService::class)->reserve($drop, $this->sizeId($drop, 'S'), 'a@example.fr');

        $this->travel(11)->minutes();
        $this->get(route('reservations.show', $reservation))
            ->assertOk()
            ->assertSee('Le délai est dépassé');
    }
}
