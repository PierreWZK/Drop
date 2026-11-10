<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ta réservation · Drop</title>
    <meta name="robots" content="noindex">
    @include('partials.style')
</head>
<body>
<main>
    @include('partials.site-header', ['statut' => $active ? 'réservé' : ($reservation->status === 'paid' ? 'payé' : 'expiré')])

    <h1>{{ $reservation->drop->title }}</h1>

    <div class="recap">
        Taille : <b>{{ $reservation->size->label }}</b><br>
        Prix : <b>{{ number_format($reservation->drop->price_cents / 100, 2, ',', ' ') }} €</b><br>
        E-mail : <b>{{ $reservation->email }}</b>
    </div>

    @if ($active)
        <div class="minuteur">
            <div class="libelle">Il te reste</div>
            <div class="chiffres" id="minuteur" data-remaining="{{ $remaining }}">--:--</div>
        </div>
        <p class="info">Ton exemplaire est mis de côté. Sans paiement dans ce délai, il retourne en vente.</p>
        @if (config('drop.payments_enabled'))
            <form method="post" action="{{ route('checkout.start', $reservation) }}">
                @csrf
                <button class="bouton" type="submit">Payer {{ number_format($reservation->drop->price_cents / 100, 2, ',', ' ') }} €</button>
            </form>
        @else
            <button class="bouton" type="button" disabled aria-disabled="true">Payer {{ number_format($reservation->drop->price_cents / 100, 2, ',', ' ') }} €</button>
            <p class="info">Le paiement n'est pas encore ouvert : ton exemplaire reste réservé, mais il ne peut pas être payé pour l'instant.</p>
        @endif
        @if (session('pay_error'))
            <p class="message erreur">{{ session('pay_error') }}</p>
        @endif
    @elseif ($reservation->status === 'paid')
        <p class="info"><b>Merci ! Commande confirmée.</b> Un e-mail de confirmation t'a été envoyé.</p>
    @else
        <p class="message erreur">Le délai est dépassé : l'exemplaire est retourné en vente.</p>
    @endif

    <a class="retour" href="{{ route('drops.show', $reservation->drop) }}">← Retour au drop</a>
</main>

@if ($active)
<script>
(() => {
    const el = document.getElementById('minuteur');
    const fin = Date.now() + Number(el.dataset.remaining) * 1000;
    const pad = (n) => String(n).padStart(2, '0');
    const tic = () => {
        const s = Math.max(0, Math.floor((fin - Date.now()) / 1000));
        el.textContent = `${pad(Math.floor(s / 60))}:${pad(s % 60)}`;
        // À zéro, on recharge : le serveur constate l'expiration et libère l'exemplaire.
        if (s === 0) { location.reload(); }
    };
    tic();
    setInterval(tic, 250);
})();
</script>
@endif
</body>
</html>
