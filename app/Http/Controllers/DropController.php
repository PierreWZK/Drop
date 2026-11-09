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
    public function show(Drop $drop): View
    {
        $drop->load('sizes');

        return view('drop.show', [
            'drop' => $drop,
            'state' => $drop->toState(),
        ]);
    }

    public function stateJson(Drop $drop): JsonResponse
    {
        return response()->json($drop->toState())->header('Cache-Control', 'no-store');
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
