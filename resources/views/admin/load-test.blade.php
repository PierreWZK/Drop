<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test de charge · Drop</title>
    <meta name="robots" content="noindex">
    @include('partials.style')
</head>
<body>
<main>
    <header>
        <div class="logo">Drop<span>.</span></div>
        <div class="statut">admin</div>
    </header>

    <h1>Test de charge</h1>
    <p class="info">200 acheteurs (processus PHP en parallèle) se disputent 20 exemplaires sur un drop temporaire, supprimé ensuite. Attendu : exactement 20 commandes.</p>

    <form method="post" action="{{ route('admin.load-test.run') }}">
        @csrf
        <button class="bouton" type="submit">Lancer le test</button>
    </form>

    @if ($output !== null)
        <p class="message {{ $ok ? '' : 'erreur' }}">{{ $ok ? 'Réussi' : 'Échec' }}</p>
        <pre class="recap" style="white-space: pre-wrap; overflow-x: auto">{{ $output }}</pre>
    @endif
</main>
</body>
</html>
