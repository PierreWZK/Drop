<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paiement (simulation) · Drop</title>
    <meta name="robots" content="noindex">
    @include('partials.style')
</head>
<body>
<main>
    @include('partials.site-header', ['statut' => 'simulation'])

    <h1>Paiement</h1>
    <div class="recap">
        {{ $reservation->drop->title }} · taille <b>{{ $reservation->size->label }}</b><br>
        Total : <b>{{ number_format($reservation->drop->price_cents / 100, 2, ',', ' ') }} €</b>
    </div>
    <p class="info">Mode simulation : aucune carte n'est débitée. En production, ce formulaire est remplacé par Stripe Checkout.</p>

    <form method="post" action="{{ route('checkout.confirm', $reservation) }}">
        @csrf
        <div class="champs" style="flex-direction: column">
            <input name="name" placeholder="Nom complet" required maxlength="120" value="{{ old('name') }}">
            <input name="address" placeholder="Adresse de livraison" required maxlength="500" value="{{ old('address') }}">
        </div>
        @error('name')<p class="message erreur">{{ $message }}</p>@enderror
        @error('address')<p class="message erreur">{{ $message }}</p>@enderror
        <button class="bouton" type="submit">Payer {{ number_format($reservation->drop->price_cents / 100, 2, ',', ' ') }} €</button>
    </form>

    <a class="retour" href="{{ route('reservations.show', $reservation) }}">← Retour à la réservation</a>
</main>
</body>
</html>
