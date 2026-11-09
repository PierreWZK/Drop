<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * Passe une réservation en « payée » et crée la commande.
     * Idempotent : un second appel pour la même réservation renvoie la commande existante
     * (wasRecentlyCreated = false), donc pas de second e-mail.
     * Retourne null si la réservation ne peut plus être payée (expirée) : remboursement à faire à la main.
     */
    public function markPaid(Reservation $reservation, int $amountCents, ?string $sessionId, ?string $address): ?Order
    {
        // 5 essais : MySQL peut annuler une transaction en cas de deadlock, on la rejoue.
        return DB::transaction(function () use ($reservation, $amountCents, $sessionId, $address) {
            $locked = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            $existing = Order::where('reservation_id', $locked->id)->first();
            if ($existing) {
                return $existing;
            }

            if ($locked->status !== 'reserved' || $locked->expires_at->isPast()) {
                Log::warning('Paiement tardif sur une réservation expirée : remboursement manuel requis.', [
                    'reservation_id' => $locked->id,
                    'session' => $sessionId,
                ]);

                return null;
            }

            // reserved -> sold : le stock disponible ne change pas.
            DB::table('drop_sizes')
                ->where('id', $locked->drop_size_id)
                ->where('reserved', '>', 0)
                ->update(['reserved' => DB::raw('reserved - 1'), 'sold' => DB::raw('sold + 1')]);

            $locked->update(['status' => 'paid']);

            return Order::create([
                'reservation_id' => $locked->id,
                'drop_id' => $locked->drop_id,
                'email' => $locked->email,
                'amount_cents' => $amountCents,
                'stripe_session_id' => $sessionId,
                'shipping_address' => $address,
                'paid_at' => now(),
            ]);
        }, 5);
    }
}
