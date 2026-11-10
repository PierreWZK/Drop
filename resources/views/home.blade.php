@php
    $status = $state['status'] ?? null;
    $target = match ($status) {
        'upcoming' => $drop->opens_at,
        'live' => $drop->closes_at,
        default => null,
    };
    $secs = $target ? max(0, (int) now()->diffInSeconds($target, false)) : 0;
    $hms = sprintf('%02d:%02d:%02d', intdiv($secs, 3600), intdiv($secs % 3600, 60), $secs % 60);
    $libelles = ['upcoming' => 'avant l\'ouverture', 'live' => 'avant la fin de la vente', 'soldout' => 'sold out', 'closed' => 'drop terminé'];
    $libelle = $libelles[$status] ?? 'prochain drop bientôt';
    $cta = $drop ? route('drops.show', $drop) : route('archives');
    $ctaTexte = $drop ? 'Voir le drop du jour' : 'Voir les archives';
    $photo = $drop?->imageUrl();
    $prix = fn ($cents) => number_format($cents / 100, 2, ',', ' ').' €';
    $mots = ['UN PRODUIT', 'UN JOUR', '18 H', 'STOCK LIMITÉ', 'ZÉRO SURVENTE'];
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Drop · un produit, un jour, pas un de plus</title>
    <meta name="description" content="Chaque jour à 18 h, un seul produit de mode en stock limité. Premiers servis, dix minutes pour payer, zéro survente.">
    <script>document.documentElement.className += ' js';</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://api.fontshare.com">
    <link rel="preconnect" href="https://cdn.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=general-sans@400,500,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/vitrine.css') }}">
</head>
<body id="top">
@include('partials.fleur-symbol')
@include('partials.fleur-geante')

<header class="hero">
    <div class="hero-shape" aria-hidden="true">
        <svg class="geante a" viewBox="0 0 800 800"><use href="#fleur-geante"/></svg>
        <svg class="geante b" viewBox="0 0 800 800"><use href="#fleur-geante"/></svg>
    </div>

    <div class="bar">
        <a class="logo" href="{{ route('home') }}" aria-label="Drop, accueil">
            <svg viewBox="0 0 100 100" aria-hidden="true"><use href="#fleur"/></svg>
            <span>drop</span>
        </a>
        <nav class="nav" aria-label="Navigation principale">
            <a href="#comment">Comment ça marche</a>
            <a href="#du-jour">Le drop du jour</a>
            <a href="#catalogue">Catalogue</a>
            <a href="#questions">Questions</a>
            <a href="{{ route('archives') }}">Archives</a>
            <a class="btn btn-nav" href="{{ $cta }}">{{ $ctaTexte }}</a>
        </nav>
    </div>

    <div class="hero-core">
        <h1 class="hero-title">
            <span class="l1"><span>Un seul</span> <span>produit</span></span>
            <span>par jour,</span>
            <span>à 18 h.</span>
        </h1>
        <p class="hero-sub">Du stock limité, des premiers servis, dix minutes pour payer.</p>
    </div>

    <div class="dock">
        <div class="dock-timer">
            <div class="digits" id="digits" role="timer" aria-label="{{ $hms }} {{ $libelle }}">{{ $hms }}</div>
            <p class="libelle" id="libelle">{{ $libelle }}</p>
        </div>
        <p class="dock-product">
            @if ($drop)
                <strong>{{ $drop->title }}</strong>
                <span>{{ $prix($drop->price_cents) }} · {{ $state['stock_total'] }} exemplaires</span>
            @else
                <strong>Le prochain drop est tiré chaque jour.</strong>
                <span>Ouverture à 18 h, heure de Paris.</span>
            @endif
        </p>
        <a class="btn btn-lg" href="{{ $cta }}">{{ $ctaTexte }}</a>
    </div>
</header>

<div class="marquee" aria-hidden="true">
    <div class="track">
        @for ($k = 0; $k < 2; $k++)
            <span>
                @foreach ($mots as $mot)
                    {{ $mot }}
                    <svg viewBox="0 0 100 100"><use href="#fleur"/></svg>
                @endforeach
            </span>
        @endfor
    </div>
</div>

<main>
    <section class="du-jour" id="du-jour">
        @if ($drop)
            <div class="dj-grid">
                <div class="dj-visual" data-reveal>
                    <div class="frame">
                        @include('partials.visual', ['name' => $drop->title, 'photo' => $photo, 'seed' => $drop->id, 'lazy' => true])
                    </div>
                </div>
                <div class="dj-info">
                    <h2 class="h2" data-reveal>Le drop du jour</h2>
                    <p class="dj-title" data-reveal style="--d: 70ms">{{ $drop->title }}</p>
                    <p class="dj-desc" data-reveal style="--d: 110ms">{{ $drop->description }}</p>
                    <p class="price" data-reveal style="--d: 150ms">{{ $prix($drop->price_cents) }}</p>
                    <div class="sizes" id="tailles" data-reveal style="--d: 190ms">
                        @foreach ($state['sizes'] as $size)
                            <span class="size {{ $size['available'] === 0 ? 'epuise' : '' }}" data-taille="{{ $size['id'] }}">{{ $size['label'] }}<b>{{ $size['available'] }}</b></span>
                        @endforeach
                    </div>
                    <div class="stock" data-reveal style="--d: 230ms">
                        <div class="jauge" aria-hidden="true"><div id="jauge" style="width: {{ $state['stock_total'] > 0 ? round($state['sold'] / $state['stock_total'] * 100, 2) : 0 }}%"></div></div>
                        <p class="vendus" id="vendus">{{ $state['sold'] }} / {{ $state['stock_total'] }} vendus</p>
                    </div>
                    <div class="actions" data-reveal style="--d: 270ms">
                        <a class="btn btn-navy" href="{{ route('drops.show', $drop) }}">Voir le produit</a>
                        @if ($status === 'upcoming')
                            <a class="link" href="{{ route('drops.show', $drop) }}#attente">Me prévenir à l'ouverture</a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="dj-empty">
                <h2 class="h2">Le drop du jour</h2>
                <p class="dj-title">Le prochain drop est tiré chaque jour.</p>
                <p class="dj-desc">Un produit est choisi au hasard dans le catalogue et ouvre à 18 h, heure de Paris.</p>
                <div class="actions"><a class="btn btn-navy" href="{{ route('archives') }}">Voir les archives</a></div>
            </div>
        @endif
    </section>

    <section class="how" id="comment">
        <div class="how-head">
            <h2 class="h2" data-reveal>Comment ça marche</h2>
            <p class="lead" data-reveal style="--d: 90ms">Un produit par jour, ouvert à 18 h. Le premier servi l'emporte, et le serveur tient le compte.</p>
        </div>
        <ol class="steps">
            <li data-reveal>
                <svg viewBox="0 0 100 100" aria-hidden="true"><use href="#fleur"/></svg>
                <h3>J'attends</h3>
                <p>Chaque jour à 18 h, un seul produit de mode ouvre. Avant l'heure, tu vois le produit, le stock, le compte à rebours, et tu peux t'inscrire pour être prévenu.</p>
            </li>
            <li data-reveal style="--d: 80ms">
                <svg viewBox="0 0 100 100" aria-hidden="true"><use href="#fleur"/></svg>
                <h3>Je réserve</h3>
                <p>À 18 h, le bouton s'allume. Un clic met un exemplaire de côté dans ta taille. Le serveur refuse tout clic avant l'heure, même si on bidouille la page.</p>
            </li>
            <li data-reveal style="--d: 160ms">
                <svg viewBox="0 0 100 100" aria-hidden="true"><use href="#fleur"/></svg>
                <h3>Je paie</h3>
                <p>Tu as dix minutes pour finir. Une seule réservation à la fois par adresse e-mail, et aucun compte à créer.</p>
            </li>
            <li data-reveal style="--d: 240ms">
                <svg viewBox="0 0 100 100" aria-hidden="true"><use href="#fleur"/></svg>
                <h3>Sinon, ça repart</h3>
                <p>Sans paiement dans le délai, l'exemplaire retourne en vente pour le suivant. À zéro, la page affiche sold out.</p>
            </li>
        </ol>
    </section>

    <section class="catalogue" id="catalogue">
        <div class="cat-head">
            <h2 class="h2" data-reveal>{{ $catalogCount }} produits. Un seul par jour.</h2>
            <p class="lead" data-reveal style="--d: 90ms">Le produit du jour est tiré au hasard dans un catalogue de {{ $catalogCount }} articles de mode. Aucun ne ressort avant que tous soient passés. Voici quelques-uns de ceux qui attendent leur tour.</p>
        </div>
        <ul class="cards">
            @foreach ($selection as $i => $item)
                <li data-reveal style="--d: {{ ($i % 4) * 70 }}ms">
                    <div class="frame">
                        @include('partials.visual', ['name' => $item->name, 'photo' => $item->image_path ? asset($item->image_path) : null, 'seed' => $item->id, 'lazy' => true])
                    </div>
                    <p class="nom">{{ $item->name }}</p>
                    <p class="prix-carte">{{ $prix($item->price_cents) }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="preuve" id="zero-survente">
        <h2 class="h2" data-reveal>Zéro survente.</h2>
        <div class="chiffres">
            <div data-reveal><b>200</b><span>acheteurs en parallèle</span></div>
            <div data-reveal style="--d: 90ms"><b>20</b><span>exemplaires en stock</span></div>
            <div data-reveal style="--d: 180ms"><b>20</b><span>commandes, pas une de plus</span></div>
        </div>
        <p class="note" data-reveal>Mesuré par un test de charge sur la base MySQL de ce site : trois essais, trois fois exactement 20 commandes. Le stock est verrouillé à chaque réservation, jamais vendu deux fois.</p>
    </section>

    <section class="faq" id="questions">
        <div class="faq-side">
            <h2 class="h2" data-reveal>Questions fréquentes</h2>
            <p class="lead" data-reveal style="--d: 90ms">Tout ce qu'il faut savoir avant 18 h.</p>
            <svg class="faq-fleur" viewBox="0 0 100 100" aria-hidden="true"><use href="#fleur"/></svg>
        </div>
        <div class="faq-list">
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
                <p>Non : le catalogue est fictif, c'est un site de démonstration. Les photos sont des images libres de droits qui illustrent la catégorie, et les autres produits sont des illustrations dessinées pour le site.</p>
            </details>
            <details data-reveal>
                <summary>Où retrouver les anciens drops ?</summary>
                <p>Dans <a href="{{ route('archives') }}">les archives</a>, avec le temps qu'il a fallu pour épuiser chaque stock.</p>
            </details>
        </div>
    </section>

    <section class="fin">
        <svg class="geante c" viewBox="0 0 800 800" aria-hidden="true"><use href="#fleur-geante"/></svg>
        <h2 class="fin-titre" data-reveal>Rendez-vous<br>à 18 h.</h2>
        <div class="fin-bas">
            <p data-reveal>Un produit, un jour, pas un de plus.</p>
            <a class="btn btn-lg" href="{{ $cta }}">{{ $ctaTexte }}</a>
        </div>
    </section>
</main>

<footer>
    <div class="foot-grid">
        <div class="foot-brand">
            <a class="logo" href="{{ route('home') }}" aria-label="Drop, accueil">
                <svg viewBox="0 0 100 100" aria-hidden="true"><use href="#fleur"/></svg>
                <span>drop</span>
            </a>
            <p>Une boutique éphémère : un produit, un jour, du stock limité.</p>
        </div>
        <div class="foot-cols">
            <ul>
                <li><a href="{{ $cta }}">Le drop du jour</a></li>
                <li><a href="#comment">Comment ça marche</a></li>
                <li><a href="#catalogue">Le catalogue</a></li>
            </ul>
            <ul>
                <li><a href="{{ route('archives') }}">Archives</a></li>
                <li><a href="#questions">Questions</a></li>
                <li><a href="{{ route('credits') }}">Crédits photos</a></li>
            </ul>
            <ul>
                <li>Heure de Paris</li>
                <li>Ouverture à 18 h</li>
                <li>Une heure de vente</li>
            </ul>
        </div>
    </div>
    <div class="legal">
        <span>Site de démonstration : produits fictifs, paiement fermé.</span>
        <a href="#top">Retour en haut</a>
    </div>
    <div class="mot" aria-hidden="true">DROP</div>
</footer>

<script type="application/json" id="vitrine-state">{!! json_encode(['state' => $state, 'stateUrl' => $drop ? route('drops.state', $drop) : null], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('js/vitrine.js') }}" defer></script>
</body>
</html>
