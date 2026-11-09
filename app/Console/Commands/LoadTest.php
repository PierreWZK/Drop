<?php

namespace App\Console\Commands;

use App\Models\Drop;
use App\Models\DropSize;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

/**
 * Test de charge : N acheteurs (processus PHP distincts, donc vraie concurrence en base)
 * se disputent S exemplaires. Doit produire exactement S commandes.
 * Passe par le service de réservation et le paiement, pas par HTTP (la limite de débit par IP
 * bloquerait les requêtes d'une seule machine).
 */
class LoadTest extends Command
{
    protected $signature = 'drops:load-test {--requests=200} {--stock=20} {--concurrency=50} {--keep}';

    protected $description = 'Prouve qu\'aucune survente n\'est possible sous concurrence';

    public function handle(): int
    {
        $requests = (int) $this->option('requests');
        $stock = (int) $this->option('stock');
        $concurrency = max(1, (int) $this->option('concurrency'));

        $drop = Drop::create([
            'slug' => 'loadtest-'.uniqid(),
            'title' => 'LOAD TEST',
            'description' => 'Drop temporaire du test de charge',
            'price_cents' => 1000,
            'opens_at' => now()->subMinute(),
            'closes_at' => now()->addHour(),
            'reservation_minutes' => 10,
        ]);
        DropSize::create(['drop_id' => $drop->id, 'label' => 'Unique', 'stock' => $stock]);

        $this->info("{$requests} acheteurs, stock {$stock}, {$concurrency} processus en parallèle, base : ".DB::getDriverName());

        $pending = range(1, $requests);
        $running = [];
        $results = [];
        $start = microtime(true);

        while ($pending || $running) {
            while ($pending && count($running) < $concurrency) {
                $n = array_shift($pending);
                $p = new Process([PHP_BINARY, base_path('artisan'), 'drops:load-test-worker', $drop->id, $n], base_path());
                $p->start();
                $running[] = $p;
            }
            foreach ($running as $i => $p) {
                if (! $p->isRunning()) {
                    $results[] = trim($p->getOutput()) ?: 'error:'.trim($p->getErrorOutput() ?: 'sortie vide');
                    unset($running[$i]);
                }
            }
            usleep(10_000);
        }

        $elapsed = round(microtime(true) - $start, 1);
        $counts = array_count_values(array_map(fn ($r) => str_starts_with($r, 'error') ? 'error' : $r, $results));
        $errors = array_values(array_unique(array_filter($results, fn ($r) => str_starts_with($r, 'error'))));

        $expected = min($stock, $requests);
        $orders = Order::where('drop_id', $drop->id)->count();
        $size = DropSize::where('drop_id', $drop->id)->first();
        $active = Reservation::where('drop_id', $drop->id)->where('status', 'reserved')->count();

        $this->table(['résultat', 'nombre'], collect($counts)->map(fn ($v, $k) => [$k, $v])->values()->all());
        $this->line("durée : {$elapsed} s");
        $this->line("commandes en base : {$orders} (attendu {$expected})");
        $this->line("sold : {$size->sold}, reserved : {$size->reserved}, réservations actives : {$active}");
        foreach (array_slice($errors, 0, 5) as $e) {
            $this->warn($e);
        }

        $ok = $orders === $expected && $size->sold === $expected && $size->reserved === 0 && $active === 0
            && ($counts['order'] ?? 0) === $expected && ! isset($counts['error']);

        if (! $this->option('keep')) {
            $drop->delete();
        }

        $ok ? $this->info("OK : exactement {$expected} commandes, aucune survente.") : $this->error('ÉCHEC : le compte ne correspond pas.');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
