<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmation;
use App\Models\Drop;
use App\Models\DropSize;
use App\Models\Order;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_test_local';

    protected function setUp(): void
    {
        parent::setUp();
        config(['drop.payment_mode' => 'simulated', 'drop.stripe_webhook_secret' => self::SECRET]);
    }

    private function makeDrop(string $opensIn = '-1 hour', array $stocks = ['S' => 5], int $priceCents = 12900): Drop
    {
        $drop = Drop::create([
            'slug' => 'drop-'.uniqid(),
            'title' => 'Veste Test',
            'description' => 'Description',
            'price_cents' => $priceCents,
            'opens_at' => now()->modify($opensIn),
            'closes_at' => now()->modify('+2 hours'),
            'reservation_minutes' => 10,
        ]);
        foreach ($stocks as $label => $stock) {
            DropSize::create(['drop_id' => $drop->id, 'label' => $label, 'stock' => $stock]);
        }

        return $drop;
    }

    private function reservation(Drop $drop, string $email = 'ami@example.fr', string $label = 'S'): Reservation
    {
        return app(ReservationService::class)->reserve(
            $drop,
            $drop->sizes()->where('label', $label)->value('id'),
            $email,
        );
    }

    private function sizeRow(Drop $drop, string $label): DropSize
    {
        return DropSize::where('drop_id', $drop->id)->where('label', $label)->firstOrFail();
    }

    private function signedWebhook(array $object, ?int $timestamp = null): array
    {
        $payload = json_encode(['id' => 'evt_'.uniqid(), 'object' => 'event', 'type' => 'checkout.session.completed', 'data' => ['object' => $object]]);
        $t = $timestamp ?? time();
        $sig = hash_hmac('sha256', $t.'.'.$payload, self::SECRET);

        return [$payload, "t={$t},v1={$sig}"];
    }

    private function sessionObject(Reservation $r, string $id = 'cs_test_1', int $amount = 12900): array
    {
        return [
            'id' => $id,
            'object' => 'checkout.session',
            'payment_status' => 'paid',
            'amount_total' => $amount,
            'metadata' => ['reservation_id' => (string) $r->id],
            'shipping_details' => [
                'name' => 'Ami Test',
                'address' => ['line1' => '1 rue de la Paix', 'line2' => null, 'postal_code' => '75002', 'city' => 'Paris', 'country' => 'FR'],
            ],
        ];
    }

    public function test_simulated_checkout_creates_one_order_and_moves_stock_to_sold(): void
    {
        Mail::fake();
        $drop = $this->makeDrop();
        $r = $this->reservation($drop);

        $this->post(route('checkout.start', $r))->assertRedirect(route('checkout.simulate', $r));
        $this->get(route('checkout.simulate', $r))->assertOk()->assertSee('Payer 129,00');

        $this->post(route('checkout.confirm', $r), ['name' => 'Ami Test', 'address' => '1 rue de la Paix, Paris'])
            ->assertRedirect(route('reservations.show', $r));

        $this->assertSame('paid', $r->fresh()->status);
        $this->assertSame(1, Order::count());
        $this->assertSame(12900, Order::first()->amount_cents);
        $this->assertSame(0, $this->sizeRow($drop, 'S')->reserved);
        $this->assertSame(1, $this->sizeRow($drop, 'S')->sold);
        Mail::assertSent(OrderConfirmation::class, 1);
    }

    public function test_confirming_twice_creates_one_order_and_one_email(): void
    {
        Mail::fake();
        $drop = $this->makeDrop();
        $r = $this->reservation($drop);
        $data = ['name' => 'Ami Test', 'address' => '1 rue de la Paix'];

        $this->post(route('checkout.confirm', $r), $data);
        $this->post(route('checkout.confirm', $r), $data);

        $this->assertSame(1, Order::count());
        $this->assertSame(1, $this->sizeRow($drop, 'S')->sold);
        Mail::assertSent(OrderConfirmation::class, 1);
    }

    public function test_pay_refused_when_reservation_expired(): void
    {
        $drop = $this->makeDrop();
        $r = $this->reservation($drop);

        $this->travel(11)->minutes();
        $this->post(route('checkout.start', $r))->assertSessionHas('pay_error');
        $this->get(route('checkout.simulate', $r))->assertStatus(410);
        $this->assertSame(0, Order::count());
    }

    public function test_confirm_after_expiry_creates_no_order(): void
    {
        Mail::fake();
        $drop = $this->makeDrop();
        $r = $this->reservation($drop);

        $this->travel(11)->minutes();
        $this->post(route('checkout.confirm', $r), ['name' => 'Ami', 'address' => 'x'])
            ->assertSessionHas('pay_error');

        $this->assertSame(0, Order::count());
        $this->assertSame('expired', $r->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_simulated_routes_are_off_in_production(): void
    {
        $drop = $this->makeDrop();
        $r = $this->reservation($drop);
        config(['drop.payment_mode' => 'stripe']);

        $this->get(route('checkout.simulate', $r))->assertNotFound();
        $this->post(route('checkout.confirm', $r), ['name' => 'a', 'address' => 'b'])->assertNotFound();
        $this->assertSame(0, Order::count());
    }

    public function test_webhook_with_valid_signature_creates_order_once(): void
    {
        Mail::fake();
        $drop = $this->makeDrop();
        $r = $this->reservation($drop);
        [$payload, $header] = $this->signedWebhook($this->sessionObject($r));

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $header, 'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        // Rejeu du même événement (Stripe réessaie parfois) : rien de nouveau.
        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $header, 'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        $this->assertSame(1, Order::count());
        $order = Order::first();
        $this->assertSame('cs_test_1', $order->stripe_session_id);
        $this->assertStringContainsString('Paris', $order->shipping_address);
        $this->assertSame(1, $this->sizeRow($drop, 'S')->sold);
        Mail::assertSent(OrderConfirmation::class, 1);
    }

    public function test_webhook_with_bad_signature_is_rejected(): void
    {
        $drop = $this->makeDrop();
        $r = $this->reservation($drop);
        [$payload] = $this->signedWebhook($this->sessionObject($r));

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't='.time().',v1=deadbeef', 'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertStatus(400);

        $this->assertSame(0, Order::count());
    }

    public function test_webhook_with_wrong_amount_creates_no_order(): void
    {
        $drop = $this->makeDrop();
        $r = $this->reservation($drop);
        [$payload, $header] = $this->signedWebhook($this->sessionObject($r, 'cs_test_2', 1));

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $header, 'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        $this->assertSame(0, Order::count());
        $this->assertSame('reserved', $r->fresh()->status);
    }

    public function test_webhook_late_payment_after_expiry_creates_no_order(): void
    {
        $drop = $this->makeDrop();
        $r = $this->reservation($drop);
        $this->travel(11)->minutes();
        [$payload, $header] = $this->signedWebhook($this->sessionObject($r, 'cs_late'));

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $header, 'CONTENT_TYPE' => 'application/json',
        ], $payload)->assertOk();

        $this->assertSame(0, Order::count());
    }

    public function test_sold_out_time_is_recorded_and_archived(): void
    {
        $drop = $this->makeDrop('-3 minutes', ['S' => 1]);
        // Dernier exemplaire réservé à 3 min 12 s après l'ouverture.
        $drop->update(['opens_at' => now()->subSeconds(192)]);
        $this->travel(0)->seconds();
        $this->reservation($drop);

        $this->assertNotNull($drop->fresh()->sold_out_at);
        $this->assertSame('3 min 12 s', $drop->fresh()->soldOutDuration());

        $this->get(route('archives'))->assertOk()->assertSee('Sold out en 3 min 12 s');
    }

    public function test_archives_list_closed_drop_without_sold_out(): void
    {
        $drop = $this->makeDrop('-3 hours');
        $drop->update(['closes_at' => now()->subHour()]);

        $this->get(route('archives'))->assertOk()->assertSee('Veste Test')->assertSee('Non épuisé à la fermeture');
    }
}
