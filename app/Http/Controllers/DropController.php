<?php

namespace App\Http\Controllers;

use App\Models\Drop;
use App\Models\WaitlistEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DropController extends Controller
{
    /** Drop en cours ou à venir, sinon le dernier drop fermé. */
    public function current(): View
    {
        $drop = Drop::query()
            ->where('closes_at', '>', now())
            ->orderBy('opens_at')
            ->first()
            ?? Drop::query()->orderByDesc('closes_at')->first();

        abort_if($drop === null, 404, 'Aucun drop pour le moment.');

        return $this->show($drop);
    }

    public function show(Drop $drop): View
    {
        $drop->load('sizes');

        return view('drop.show', [
            'drop' => $drop,
            'state' => $this->state($drop),
        ]);
    }

    public function state(Drop $drop): array
    {
        $drop->loadMissing('sizes');

        return [
            'status' => $drop->status(),
            'server_now' => now()->toIso8601String(),
            'opens_at' => $drop->opens_at->toIso8601String(),
            'closes_at' => $drop->closes_at->toIso8601String(),
            'stock_total' => $drop->stockTotal(),
            'sold' => $drop->soldCount(),
            'available' => $drop->available(),
            'sizes' => $drop->sizes->map(fn ($size) => [
                'id' => $size->id,
                'label' => $size->label,
                'available' => $size->available(),
            ])->values(),
        ];
    }

    public function stateJson(Drop $drop): JsonResponse
    {
        return response()->json($this->state($drop))->header('Cache-Control', 'no-store');
    }

    public function joinWaitlist(Request $request, Drop $drop): RedirectResponse|JsonResponse
    {
        if ($drop->status() !== 'upcoming') {
            return $this->reply($request, false, 'Ce drop est déjà ouvert ou terminé.');
        }

        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
        ]);

        WaitlistEntry::firstOrCreate([
            'drop_id' => $drop->id,
            'email' => mb_strtolower($data['email']),
        ]);

        return $this->reply($request, true, 'Tu seras prévenu à l\'ouverture.');
    }

    private function reply(Request $request, bool $ok, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'message' => $message], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'waitlist_ok' : 'waitlist_error', $message);
    }
}
