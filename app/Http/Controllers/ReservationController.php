<?php

namespace App\Http\Controllers;

use App\Exceptions\ReservationException;
use App\Models\Drop;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(private ReservationService $service) {}

    public function store(Request $request, Drop $drop): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'drop_size_id' => ['required', 'integer'],
        ]);

        try {
            $reservation = $this->service->reserve($drop, (int) $data['drop_size_id'], $data['email']);
        } catch (ReservationException $e) {
            if ($e->reason === 'already_reserved') {
                $existing = $this->activeFor($drop, $data['email']);
                // On ne donne le lien qu'au navigateur qui a fait la réservation.
                if ($existing && $request->session()->get("reservation.{$drop->id}") === $existing->token) {
                    return redirect()->route('reservations.show', $existing);
                }
            }

            return back()->withInput()->with('reserve_error', $e->getMessage());
        }

        $request->session()->put("reservation.{$drop->id}", $reservation->token);

        return redirect()->route('reservations.show', $reservation);
    }

    public function show(Reservation $reservation): View
    {
        $this->service->expireStale($reservation->drop_id);
        $reservation->refresh()->load(['drop', 'size']);

        return view('reservation.show', [
            'reservation' => $reservation,
            'active' => $reservation->isActive(),
            'remaining' => $reservation->isActive() ? max(0, (int) now()->diffInSeconds($reservation->expires_at, false)) : 0,
        ]);
    }

    private function activeFor(Drop $drop, string $email): ?Reservation
    {
        return Reservation::where('drop_id', $drop->id)
            ->where('email', mb_strtolower(trim($email)))
            ->where('status', 'reserved')
            ->where('expires_at', '>', now())
            ->first();
    }
}
