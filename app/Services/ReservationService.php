<?php

namespace App\Services;

use App\Exceptions\ReservationException;
use App\Models\Drop;
use App\Models\DropSize;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationService
{
    /** Rend au stock les réservations expirées. Retourne le nombre libéré. */
    public function expireStale(?int $dropId = null): int
    {
        $stale = fn () => Reservation::query()
            ->where('status', 'reserved')
            ->where('expires_at', '<=', now())
            ->when($dropId, fn ($q) => $q->where('drop_id', $dropId));

        // Cas courant (rien d'expiré) : aucune transaction, aucun verrou.
        if (! $stale()->exists()) {
            return 0;
        }

        return DB::transaction(function () use ($stale) {
            // Même ordre de verrous partout (drop d'abord) : sans cela, MySQL détecte des deadlocks
            // entre cette libération, les réservations et les paiements simultanés.
            Drop::whereIn('id', $stale()->distinct()->pluck('drop_id'))->orderBy('id')->lockForUpdate()->get();

            $released = 0;
            foreach ($stale()->get() as $reservation) {
                // Conditionnel : si un paiement l'a passée en "paid" entre-temps, on n'y touche pas.
                $changed = Reservation::where('id', $reservation->id)
                    ->where('status', 'reserved')
                    ->update(['status' => 'expired']);

                if ($changed === 1) {
                    DropSize::where('id', $reservation->drop_size_id)
                        ->where('reserved', '>', 0)
                        ->decrement('reserved');
                    $released++;
                }
            }

            return $released;
        }, 5);
    }

    /** @throws ReservationException */
    public function reserve(Drop $drop, int $sizeId, string $email): Reservation
    {
        // Ne dépend pas de la tâche planifiée : on libère les expirées avant chaque réservation.
        $this->expireStale($drop->id);

        $email = mb_strtolower(trim($email));

        return DB::transaction(function () use ($drop, $sizeId, $email) {
            // Verrou sur la ligne du drop : sérialise les réservations d'un même drop (MySQL).
            $locked = Drop::whereKey($drop->id)->lockForUpdate()->firstOrFail();

            if (now()->lt($locked->opens_at)) {
                throw new ReservationException('not_open');
            }
            if (now()->gte($locked->closes_at)) {
                throw new ReservationException('closed');
            }

            $hasActive = Reservation::where('drop_id', $locked->id)
                ->where('email', $email)
                ->where('status', 'reserved')
                ->where('expires_at', '>', now())
                ->exists();
            if ($hasActive) {
                throw new ReservationException('already_reserved');
            }

            $size = DropSize::where('id', $sizeId)->where('drop_id', $locked->id)->first();
            if ($size === null) {
                throw new ReservationException('size_unavailable');
            }

            // Mise à jour conditionnelle : n'incrémente que s'il reste du stock.
            $taken = DB::table('drop_sizes')
                ->where('id', $size->id)
                ->whereRaw('stock - reserved - sold > 0')
                ->increment('reserved');
            if ($taken === 0) {
                throw new ReservationException('soldout');
            }

            $reservation = Reservation::create([
                'token' => Str::random(40),
                'drop_id' => $locked->id,
                'drop_size_id' => $size->id,
                'email' => $email,
                'status' => 'reserved',
                'expires_at' => now()->addMinutes($locked->reservation_minutes),
            ]);

            // Épuisé = plus aucun exemplaire libre (réservé ou vendu). Première fois seulement.
            $libre = DB::table('drop_sizes')->where('drop_id', $locked->id)
                ->selectRaw('SUM(stock - reserved - sold) AS libre')->value('libre');
            if ((int) $libre === 0) {
                Drop::whereKey($locked->id)->whereNull('sold_out_at')->update(['sold_out_at' => now()]);
            }

            return $reservation;
        }, 5); // 5 essais : MySQL peut annuler une transaction en cas de deadlock
    }
}
