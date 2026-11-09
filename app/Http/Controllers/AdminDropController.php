<?php

namespace App\Http\Controllers;

use App\Models\Drop;
use App\Models\DropSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminDropController extends Controller
{
    public function create(): View
    {
        return view('admin.drops.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'opens_at' => ['required', 'date'],
            'closes_at' => ['required', 'date', 'after:opens_at'],
            'reservation_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'sizes' => ['required', 'array', 'min:1'],
            'sizes.*.label' => ['required', 'string', 'max:20', 'distinct'],
            'sizes.*.stock' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $drop = Drop::create([
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(6)),
            'title' => $data['title'],
            'description' => $data['description'],
            'price_cents' => (int) round($data['price'] * 100),
            'image_path' => $request->file('image')?->store('drops', 'public'),
            'opens_at' => $data['opens_at'],
            'closes_at' => $data['closes_at'],
            'reservation_minutes' => $data['reservation_minutes'],
        ]);

        foreach ($data['sizes'] as $size) {
            DropSize::create([
                'drop_id' => $drop->id,
                'label' => $size['label'],
                'stock' => $size['stock'],
            ]);
        }

        return redirect()->route('admin.drops.create')->with('status', "Drop « {$drop->title} » créé.");
    }
}
