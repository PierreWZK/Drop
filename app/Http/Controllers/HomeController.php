<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Models\Drop;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Vitrine : drop en cours ou à venir (sinon le dernier fermé), et huit produits du catalogue. */
    public function index(): View
    {
        $drop = Drop::query()
            ->where('closes_at', '>', now())
            ->orderBy('opens_at')
            ->first()
            ?? Drop::query()->orderByDesc('closes_at')->first();

        $drop?->load('sizes', 'catalogProduct');

        $others = CatalogProduct::query()
            ->when($drop?->catalog_product_id, fn ($q, $id) => $q->where('id', '!=', $id));

        // Quatre produits avec photo et quatre avec illustration, mélangés.
        $selection = (clone $others)->whereNotNull('image_path')->inRandomOrder()->limit(4)->get()
            ->concat((clone $others)->whereNull('image_path')->inRandomOrder()->limit(4)->get())
            ->shuffle()
            ->values();

        return view('home', [
            'drop' => $drop,
            'state' => $drop?->toState(),
            'selection' => $selection,
            'catalogCount' => CatalogProduct::count(),
        ]);
    }
}
