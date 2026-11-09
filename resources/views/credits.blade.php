<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crédits photos · Drop</title>
    @include('partials.style')
    <style>
        main { max-width: 1100px; }
        .credits table { border-collapse: collapse; width: 100%; font-size: .9rem; }
        .credits th, .credits td { text-align: left; vertical-align: top; padding: 8px 10px; border-bottom: 1px solid var(--bleu); }
        .credits-scroll { overflow-x: auto; }
    </style>
</head>
<body>
<main>
    <header>
        <div class="logo">Drop<span>.</span></div>
        <a class="retour" href="{{ route('home') }}" style="margin:0">Accueil</a>
    </header>
    <div class="credits"><div class="credits-scroll">{!! $html !!}</div></div>
</main>
</body>
</html>
