<?php

namespace App\Exceptions;

use RuntimeException;

class ReservationException extends RuntimeException
{
    public const MESSAGES = [
        'not_open' => 'La vente n\'est pas encore ouverte.',
        'closed' => 'La vente est terminée.',
        'soldout' => 'Plus d\'exemplaire disponible dans cette taille.',
        'size_unavailable' => 'Taille inconnue pour ce drop.',
        'already_reserved' => 'Une réservation est déjà en cours pour cet e-mail.',
    ];

    public function __construct(public readonly string $reason)
    {
        parent::__construct(self::MESSAGES[$reason] ?? $reason);
    }
}
