<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Archives · Drop</title>
    @include('partials.style')
</head>
<body>
<main>
    @include('partials.site-header')

    <h1>Archives</h1>

    @forelse ($drops as $drop)
        <div class="recap" style="margin-top: 14px">
            <b>{{ $drop->title }}</b> · {{ number_format($drop->price_cents / 100, 2, ',', ' ') }} €<br>
            {{ $drop->soldCount() }} / {{ $drop->stockTotal() }} vendus<br>
            @if ($drop->sold_out_at)
                Sold out en {{ $drop->soldOutDuration() }}
            @else
                Non épuisé à la fermeture
            @endif
            <br>
            <a class="retour" href="{{ route('drops.show', $drop) }}">Voir la page</a>
        </div>
    @empty
        <p class="info">Aucun drop terminé pour le moment.</p>
    @endforelse
</main>
</body>
</html>
