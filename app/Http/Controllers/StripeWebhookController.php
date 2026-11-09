<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Reservation;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Response;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function handle(Request $request): Response
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature', ''),
                (string) config('drop.stripe_webhook_secret'),
            );
        } catch (\Throwable $e) {
            return response('Signature invalide', 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return response('Ignoré', 200);
        }

        $session = $event->data->object;
        if (($session->payment_status ?? null) !== 'paid') {
            return response('Non payé', 200);
        }

        $reservation = Reservation::with('drop')->find($session->metadata->reservation_id ?? null);
        if ($reservation === null) {
            Log::warning('Webhook Stripe : réservation introuvable.', ['session' => $session->id]);

            return response('OK', 200);
        }

        // Le montant payé doit correspondre au prix du drop.
        if ((int) $session->amount_total !== $reservation->drop->price_cents) {
            Log::error('Webhook Stripe : montant inattendu, commande non créée.', ['session' => $session->id]);

            return response('Montant inattendu', 200);
        }

        $address = $this->address($session);
        $order = $this->orders->markPaid($reservation, (int) $session->amount_total, $session->id, $address);

        // Un nouvel envoi du même événement ne renvoie pas d'e-mail : la commande existe déjà.
        if ($order !== null && $order->wasRecentlyCreated) {
            Mail::to($order->email)->send(new OrderConfirmation($order));
        }

        return response('OK', 200);
    }

    private function address(object $session): ?string
    {
        $adresse = $session->shipping_details->address ?? null;
        if ($adresse === null) {
            return null;
        }

        return trim(implode("\n", array_filter([
            $session->shipping_details->name ?? null,
            $adresse->line1 ?? null,
            $adresse->line2 ?? null,
            trim(($adresse->postal_code ?? '').' '.($adresse->city ?? '')),
            $adresse->country ?? null,
        ])));
    }
}
