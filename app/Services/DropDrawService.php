<?php

namespace App\Services;

use App\Models\CatalogProduct;
use App\Models\Drop;
use App\Models\DropSize;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DropDrawService
{
    /**
     * Crée le drop d'un jour en tirant un produit au hasard dans le catalogue.
     * Idempotent : si un drop ouvre déjà ce jour-là, ne fait rien (retourne null).
     * Sans $allowPast, ne crée pas un drop dont l'heure d'ouverture est déjà passée.
     */
    public function drawFor(CarbonInterface $day, bool $allowPast = false): ?Drop
    {
        $opens = $day->copy()->setTimeFromTimeString(config('drop.draw.opens_at'));
        $closes = $opens->copy()->addMinutes((int) config('drop.draw.duration_minutes'));

        if (! $allowPast && $opens->lte(now())) {
            return null;
        }

        return DB::transaction(function () use ($opens, $closes) {
            $already = Drop::whereBetween('opens_at', [$opens->copy()->startOfDay(), $opens->copy()->endOfDay()])->exists();
            if ($already) {
                return null;
            }

            $product = $this->pickProduct();
            if ($product === null) {
                return null;
            }

            $drop = Drop::create([
                'catalog_product_id' => $product->id,
                'slug' => Str::slug($product->name).'-'.Str::lower(Str::random(6)),
                'title' => $product->name,
                'description' => $product->description,
                'price_cents' => $product->price_cents,
                'image_path' => $product->image_path,
                'opens_at' => $opens,
                'closes_at' => $closes,
                'reservation_minutes' => 10,
            ]);

            foreach ($this->splitStock($product->sizes) as $label => $stock) {
                DropSize::create(['drop_id' => $drop->id, 'label' => $label, 'stock' => $stock]);
            }

            return $drop;
        });
    }

    /** Produit pas encore tiré dans le cycle ; quand tous sont passés, nouveau cycle. */
    private function pickProduct(): ?CatalogProduct
    {
        $left = CatalogProduct::whereNull('drawn_at')->lockForUpdate()->get();

        if ($left->isEmpty()) {
            CatalogProduct::query()->update(['drawn_at' => null]);
            $left = CatalogProduct::lockForUpdate()->get();
        }

        if ($left->isEmpty()) {
            return null;
        }

        $product = $left->random();
        $product->update(['drawn_at' => now(), 'times_drawn' => $product->times_drawn + 1]);

        return $product;
    }

    /** Stock total tiré entre stock_min et stock_max, réparti entre les tailles (au moins 1 par taille). */
    private function splitStock(array $sizes): array
    {
        $sizes = array_values($sizes);
        $total = max(random_int((int) config('drop.draw.stock_min'), (int) config('drop.draw.stock_max')), count($sizes));

        $base = intdiv($total, count($sizes));
        $extra = $total % count($sizes);
        $bonus = array_rand(array_flip(range(0, count($sizes) - 1)), max($extra, 1));
        $bonus = $extra === 0 ? [] : (array) $bonus;

        $stocks = [];
        foreach ($sizes as $i => $label) {
            $stocks[$label] = $base + (in_array($i, $bonus, true) ? 1 : 0);
        }

        return $stocks;
    }
}
