<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'drops:expire-reservations';

    protected $description = 'Rend au stock les réservations expirées';

    public function handle(ReservationService $service): int
    {
        $this->info($service->expireStale().' réservation(s) libérée(s).');

        return self::SUCCESS;
    }
}
