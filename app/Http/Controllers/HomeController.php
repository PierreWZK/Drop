<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Models\Drop;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Vitrine : drop en cours ou à venir (sinon le dernier fermé), et trois produits du catalogue. */
    public function index(): View
    {
        $drop = Drop::query()
            ->where('closes_at', '>', now())
            ->orderBy('opens_at')
            ->first()
            ?? Drop::query()->orderByDesc('closes_at')->first();

        $drop?->load('sizes', 'catalogProduct');

        // Les produits avec photo d'abord, complétés par les autres si le catalogue en manque.
        $selection = CatalogProduct::query()
            ->when($drop?->catalog_product_id, fn ($q, $id) => $q->where('id', '!=', $id))
            ->orderByRaw('image_path IS NULL')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        return view('home', [
            'drop' => $drop,
            'state' => $drop?->toState(),
            'selection' => $selection,
            'catalogCount' => CatalogProduct::count(),
        ]);
    }
}
