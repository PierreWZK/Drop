<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Reservation;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\StripeClient;

class CheckoutController extends Controller
{
    public function __construct(private OrderService $orders) {}

    /** Démarre le paiement d'une réservation active. */
    public function start(Reservation $reservation): RedirectResponse
    {
        if (! config('drop.payments_enabled')) {
            return back()->with('pay_error', 'Le paiement n\'est pas encore ouvert.');
        }

        $this->sweep($reservation);

        if (! $reservation->fresh()->isActive()) {
            return back()->with('pay_error', 'Cette réservation a expiré.');
        }

        if ($this->simulated()) {
            return redirect()->route('checkout.simulate', $reservation);
        }

        if (! config('drop.stripe_secret')) {
            Log::error('Paiement Stripe demandé sans STRIPE_SECRET.');

            return back()->with('pay_error', 'Le paiement est indisponible pour le moment.');
        }

        $reservation->load(['drop', 'size']);

        // Pas de expires_at sur la session : Stripe refuse moins de 30 min. La réservation de 10 min
        // est appliquée côté serveur, au moment du webhook.
        $session = (new StripeClient(config('drop.stripe_secret')))->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $reservation->email,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $reservation->drop->price_cents,
                    'product_data' => ['name' => $reservation->drop->title.' · '.$reservation->size->label],
                ],
            ]],
            'metadata' => ['reservation_id' => $reservation->id],
            'payment_intent_data' => ['metadata' => ['reservation_id' => $reservation->id]],
            'shipping_address_collection' => ['allowed_countries' => ['FR', 'BE', 'CH', 'LU']],
            'success_url' => route('reservations.show', $reservation).'?paid=1',
            'cancel_url' => route('reservations.show', $reservation),
        ]);

        return redirect()->away($session->url);
    }

    public function simulate(Reservation $reservation): View
    {
        abort_unless(config('drop.payments_enabled'), 403, 'Le paiement n\'est pas encore ouvert.');
        abort_unless($this->simulated(), 404);
        $this->sweep($reservation);
        abort_unless($reservation->fresh()->isActive(), 410, 'Réservation expirée.');

        return view('checkout.simulate', ['reservation' => $reservation->load(['drop', 'size'])]);
    }

    public function confirm(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless(config('drop.payments_enabled'), 403, 'Le paiement n\'est pas encore ouvert.');
        abort_unless($this->simulated(), 404);
        $this->sweep($reservation);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:500'],
        ]);

        $order = $this->orders->markPaid(
            $reservation,
            $reservation->drop->price_cents,
            'sim_'.Str::random(24),
            $data['name']."\n".$data['address'],
        );

        if ($order === null) {
            return redirect()->route('reservations.show', $reservation)
                ->with('pay_error', 'Réservation expirée avant le paiement.');
        }

        if ($order->wasRecentlyCreated) {
            Mail::to($order->email)->send(new OrderConfirmation($order));
        }

        return redirect()->route('reservations.show', $reservation)->with('paid', true);
    }

    private function sweep(Reservation $reservation): void
    {
        app(\App\Services\ReservationService::class)->expireStale($reservation->drop_id);
    }

    private function simulated(): bool
    {
        return config('drop.payment_mode') === 'simulated' && ! app()->isProduction();
    }
}
