<?php

namespace App\Console\Commands;

use App\Exceptions\ReservationException;
use App\Models\Drop;
use App\Services\OrderService;
use App\Services\ReservationService;
use Illuminate\Console\Command;

/** Un « acheteur » du test de charge : réserve puis paie. Lancé par drops:load-test. */
class LoadTestWorker extends Command
{
    protected $signature = 'drops:load-test-worker {drop} {n}';

    protected $description = 'Interne : un acheteur du test de charge';

    protected $hidden = true;

    public function handle(ReservationService $reservations, OrderService $orders): int
    {
        $drop = Drop::findOrFail($this->argument('drop'));
        $sizeId = $drop->sizes()->value('id');
        $n = (int) $this->argument('n');

        try {
            $reservation = $reservations->reserve($drop, $sizeId, "acheteur{$n}@loadtest.invalid");
            $order = $orders->markPaid($reservation, $drop->price_cents, "lt_{$n}", null);
            $this->line($order ? 'order' : 'error:markPaid null');
        } catch (ReservationException $e) {
            $this->line($e->reason === 'soldout' ? 'soldout' : 'error:'.$e->reason);
        } catch (\Throwable $e) {
            $this->line('error:'.class_basename($e).' '.substr($e->getMessage(), 0, 120));
        }

        return self::SUCCESS;
    }
}
