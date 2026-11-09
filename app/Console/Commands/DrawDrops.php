<?php

namespace App\Console\Commands;

use App\Services\DropDrawService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DrawDrops extends Command
{
    protected $signature = 'drops:draw {--date= : Jour précis (AAAA-MM-JJ), même si l\'heure d\'ouverture est passée}';

    protected $description = 'Tire au hasard dans le catalogue le drop d\'aujourd\'hui (si pas encore ouvert) et de demain';

    public function handle(DropDrawService $draw): int
    {
        $dates = $this->option('date')
            ? [[Carbon::parse($this->option('date')), true]]
            : [[now(), false], [now()->addDay(), false]];

        foreach ($dates as [$day, $allowPast]) {
            $drop = $draw->drawFor($day, $allowPast);
            $this->line($day->toDateString().' : '.($drop
                ? "« {$drop->title} » ({$drop->stockTotal()} ex.)"
                : 'rien à faire (drop déjà présent, heure passée, ou catalogue vide)'));
        }

        return self::SUCCESS;
    }
}
