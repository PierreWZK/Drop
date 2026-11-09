@php
    $status = $state['status'] ?? null;
    $target = match ($status) {
        'upcoming' => $drop->opens_at,
        'live' => $drop->closes_at,
        default => null,
    };
    $secs = $target ? max(0, (int) now()->diffInSeconds($target, false)) : 0;
    $hms = sprintf('%02d:%02d:%02d', intdiv($secs, 3600), intdiv($secs % 3600, 60), $secs % 60);
    $libelles = ['upcoming' => 'avant l\'ouverture', 'live' => 'avant la fin de la vente', 'soldout' => 'Sold out', 'closed' => 'Drop terminé'];
    $libelle = $libelles[$status] ?? 'Prochain drop bientôt';
    $cta = $drop ? route('drops.show', $drop) : route('archives');
    $ctaTexte = $drop ? 'Voir le drop du jour' : 'Voir les archives';
    $photo = $drop?->imageUrl();
    $prix = fn ($cents) => number_format($cents / 100, 2, ',', ' ').' €';
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drop · un produit, un jour, pas un de plus</title>
    <meta name="description" content="Chaque jour à 18 h, un seul produit de mode en stock limité. Premiers servis, dix minutes pour payer, zéro survente.">
    <script>document.documentElement.className += ' js';</script>
    <link rel="preconnect" href="https://api.fontshare.com">
    <link rel="preconnect" href="https://cdn.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=gambarino@400&display=swap" rel="stylesheet">
    <link href="https://api.fontshare.com/v2/css?f[]=general-sans@400,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/vitrine.css') }}">
</head>
<body>
@include('partials.fleur-symbol')

<header class="hero">
    <div class="bar">
        <a class="logo" href="{{ route('home') }}" aria-label="Drop, accueil">
            <svg viewBox="0 0 100 100" aria-hidden="true"><use href="#fleur"/></svg>
            <span>drop</span>
        </a>
        <nav class="nav" aria-label="Navigation principale">
            <a href="#comment">Comment ça marche</a>
            <a href="#du-jour">Le drop du jour</a>
            <a href="#questions">Questions</a>
            <a href="{{ route('archives') }}">Archives</a>
            <a class="btn btn-small" href="{{ $cta }}">{{ $ctaTexte }}</a>
        </nav>
    </div>

    <div class="hero-main grid">
        <div class="plate">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $drop->title }}" width="900" height="900">
            @else
                @include('partials.plate', ['seed' => $drop->id ?? 1, 'label' => 'Motif de fleurs'])
            @endif
        </div>
        <div class="hero-text">
            <h1 class="display">Un seul produit par jour, à 18 h.</h1>
            <p>Du stock limité, des premiers servis, dix minutes pour payer. Ce qui n'est pas payé retourne en vente.</p>
            <a class="btn" href="{{ $cta }}">{{ $ctaTexte }}</a>
        </div>
    </div>

    <div class="monument">
        <div class="digits" id="digits" role="timer" aria-label="{{ $hms }} {{ $libelle }}">
            @foreach (str_split($hms) as $c)
                <span class="{{ $c === ':' ? 'sep' : '' }}">{{ $c }}</span>
            @endforeach
        </div>
        <p class="libelle" id="libelle">{{ $libelle }}</p>
        <a class="btn cta-mobile" href="{{ $cta }}">{{ $ctaTexte }}</a>
    </div>
</header>

<main>
    <section class="how" id="comment">
        <div class="grid"><h2 data-reveal>Comment ça marche</h2></div>
        <ol>
            <li data-reveal>
                <div>
                    <p class="line">D'abord, tu attends.</p>
                    <p>Chaque jour à 18 h, un seul produit de mode ouvre. Avant l'heure, tu vois le produit, le stock et le compte à rebours, et tu peux t'inscrire pour être prévenu.</p>
                </div>
            </li>
            <li data-reveal>
                <div>
                    <p class="line">À 18 h, le bouton s'allume.</p>
                    <p>Un clic réserve un exemplaire dans la taille de ton choix. Le serveur refuse tout clic avant l'heure, même si on bidouille la page.</p>
                </div>
            </li>
            <li data-reveal>
                <div>
                    <p class="line">Tu as dix minutes.</p>
                    <p>Le temps de remplir tes coordonnées. Une seule réservation à la fois par adresse e-mail, et pas de compte à créer.</p>
                </div>
            </li>
            <li data-reveal>
                <div>
                    <p class="line">Sinon, il repart en stock.</p>
                    <p>Sans paiement dans le délai, l'exemplaire retourne en vente pour le suivant. À zéro, la page affiche sold out.</p>
                </div>
            </li>
        </ol>
    </section>

    <section class="show" id="du-jour">
        <div class="grid">
            @if ($drop)
                <div class="plate-wrap" data-reveal>
                    <div class="plate-square">
                        @if ($photo)
                            <img src="{{ $photo }}" alt="{{ $drop->title }}" width="900" height="900" loading="lazy">
                        @else
                            @include('partials.plate', ['seed' => ($drop->id ?? 1) + 1, 'label' => 'Motif de fleurs'])
                        @endif
                    </div>
                </div>
                <div class="detail">
                    <h2 data-reveal>Le drop du jour</h2>
                    <h3 data-reveal style="--d: 80ms">{{ $drop->title }}</h3>
                    <p data-reveal style="--d: 120ms">{{ $drop->description }}</p>
                    <p class="price" data-reveal style="--d: 160ms">{{ $prix($drop->price_cents) }}</p>
                    <div class="tailles" id="tailles" data-reveal style="--d: 200ms">
                        @foreach ($state['sizes'] as $size)
                            <span data-taille="{{ $size['id'] }}" class="{{ $size['available'] === 0 ? 'epuise' : '' }}">{{ $size['label'] }} <b>{{ $size['available'] }}</b></span>
                        @endforeach
                    </div>
                    <div data-reveal style="--d: 240ms">
                        <div class="jauge" aria-hidden="true"><div id="jauge" style="width: {{ $state['stock_total'] > 0 ? round($state['sold'] / $state['stock_total'] * 100, 2) : 0 }}%"></div></div>
                        <p class="vendus" id="vendus">{{ $state['sold'] }} / {{ $state['stock_total'] }} vendus</p>
                    </div>
                    <div class="actions" data-reveal style="--d: 280ms">
                        <a class="btn" href="{{ route('drops.show', $drop) }}">Voir le produit</a>
                        @if ($status === 'upcoming')
                            <a class="link" href="{{ route('drops.show', $drop) }}#attente">Me prévenir à l'ouverture</a>
                        @endif
                    </div>
                </div>
            @else
                <div class="detail" style="grid-column: 1 / span 7">
                    <h2>Le drop du jour</h2>
                    <h3>Le prochain drop est tiré chaque jour.</h3>
                    <p>Un produit est choisi au hasard dans le catalogue et ouvre à 18 h, heure de Paris.</p>
                    <div class="actions"><a class="btn" href="{{ route('archives') }}">Voir les archives</a></div>
                </div>
            @endif
        </div>
    </section>

    <section class="tirage" id="tirage">
        <div class="grid">
            <h2 data-reveal>{{ $catalogCount }} produits. Un seul sort chaque jour.</h2>
            <p class="texte" data-reveal style="--d: 100ms">Le produit du jour est tiré au hasard dans un catalogue de {{ $catalogCount }} articles de mode. Aucun ne ressort avant que tous soient passés. Voici trois de ceux qui attendent leur tour.</p>
        </div>
        <ul class="picks">
            @foreach ($selection as $i => $item)
                <li data-reveal style="--d: {{ $i * 90 }}ms">
                    <div class="vignette">
                        @if ($item->image_path)
                            <img src="{{ asset($item->image_path) }}" alt="{{ $item->name }}" width="900" height="1125" loading="lazy">
                        @else
                            @include('partials.plate', ['seed' => $item->id, 'label' => $item->name])
                        @endif
                    </div>
                    <p class="nom">{{ $item->name }}</p>
                    <p>{{ $prix($item->price_cents) }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="preuve" id="zero-survente">
        <div class="bande">
            <h2 data-reveal>200 acheteurs, 20 exemplaires, 20 commandes.</h2>
            <svg class="fleur-bande" viewBox="0 0 100 100" aria-hidden="true"><use href="#fleur"/></svg>
        </div>
        <p class="note" data-reveal>Un test de charge lance 200 acheteurs en parallèle sur 20 exemplaires, sur la base MySQL de ce site. Trois essais, trois fois exactement 20 commandes : jamais une de plus que le stock.</p>
    </section>

    <section class="faq" id="questions">
        <div class="grid">
            <h2 data-reveal>Questions fréquentes</h2>
            <div class="liste">
                <details data-reveal>
                    <summary>Combien de produits sont en vente ?</summary>
                    <p>Un seul par jour, tiré au hasard dans le catalogue. Le stock est limité : entre 10 et 30 exemplaires, répartis entre les tailles.</p>
                </details>
                <details data-reveal>
                    <summary>À quelle heure ça ouvre, et jusqu'à quand ?</summary>
                    <p>À 18 h, heure de Paris, pendant une heure ou jusqu'à épuisement du stock.</p>
                </details>
                <details data-reveal>
                    <summary>Que se passe-t-il si je ne paie pas ?</summary>
                    <p>Ton exemplaire est mis de côté dix minutes. Passé ce délai, il retourne en stock pour le suivant.</p>
                </details>
                <details data-reveal>
                    <summary>Puis-je payer dès maintenant ?</summary>
                    <p>Pas encore : le paiement n'est pas ouvert sur ce site de test. Tu peux réserver et voir ta page de réservation, mais le bouton « Payer » reste désactivé.</p>
                </details>
                <details data-reveal>
                    <summary>Faut-il un compte ?</summary>
                    <p>Non. Une adresse e-mail suffit pour réserver ou pour être prévenu à l'ouverture.</p>
                </details>
                <details data-reveal>
                    <summary>Les produits existent-ils vraiment ?</summary>
                    <p>Non : le catalogue est fictif, c'est un site de démonstration. Les photos sont des images libres de droits qui illustrent la catégorie (veste, sneakers, sac), pas le produit exact. Les produits sans photo portent un motif de fleurs.</p>
                </details>
                <details data-reveal>
                    <summary>Où retrouver les anciens drops ?</summary>
                    <p>Dans <a href="{{ route('archives') }}">les archives</a>, avec le temps qu'il a fallu pour épuiser chaque stock.</p>
                </details>
            </div>
        </div>
    </section>

    <section class="close">
        <h2 data-reveal>Rendez-vous à 18 h.</h2>
        <div class="bas">
            <p data-reveal>Un produit, un jour, pas un de plus.</p>
            <a class="btn" href="{{ $cta }}">{{ $ctaTexte }}</a>
        </div>
    </section>
</main>

<footer>
    <div class="liens">
        <ul class="lieux">
            <li><a href="{{ $cta }}">Le drop du jour</a></li>
            <li><a href="{{ route('archives') }}">Les archives</a></li>
            <li><a href="#comment">Comment ça marche</a></li>
        </ul>
        <div class="petits">
            <ul>
                <li><a href="#du-jour">Produit du jour</a></li>
                <li><a href="#tirage">Le tirage</a></li>
                <li><a href="#questions">Questions</a></li>
            </ul>
            <ul>
                <li><a href="{{ route('credits') }}">Crédits photos</a></li>
                <li><a href="{{ route('archives') }}">Archives</a></li>
            </ul>
            <ul>
                <li>Heure de Paris</li>
                <li>Ouverture à 18 h</li>
            </ul>
        </div>
    </div>
    <div class="legal">
        <span>Site de démonstration : produits fictifs, aucun paiement ouvert.</span>
        <a href="#top">Retour en haut</a>
    </div>
    <div class="nom" aria-hidden="true">drop<svg viewBox="0 0 100 100"><use href="#fleur"/></svg></div>
</footer>

<script type="application/json" id="vitrine-state">{!! json_encode(['state' => $state, 'stateUrl' => $drop ? route('drops.state', $drop) : null], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('js/vitrine.js') }}" defer></script>
</body>
</html>
