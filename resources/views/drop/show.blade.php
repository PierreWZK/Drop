<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $drop->title }} · Drop</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($drop->description, 150) }}">
    @include('partials.style')
</head>
<body>
<main>
    @include('partials.site-header', ['statut' => ['upcoming' => 'à venir', 'live' => 'en vente', 'soldout' => 'sold out', 'closed' => 'terminé'][$state['status']], 'statutId' => 'statut'])

    <div class="visuel">@include('partials.visual', ['name' => $drop->title, 'photo' => $drop->imageUrl(), 'seed' => $drop->id, 'lazy' => false])</div>

    <h1>{{ $drop->title }}</h1>
    <div class="prix">{{ number_format($drop->price_cents / 100, 2, ',', ' ') }} € <small>· édition limitée</small></div>
    <p class="description">{{ $drop->description }}</p>

    <div class="compteur">
        <div class="libelle" id="compteur-libelle"></div>
        <div class="chiffres" id="compteur">--:--:--</div>
    </div>

    <section class="stock">
        <h2>Stock restant</h2>
        <div class="tailles" id="tailles">
            @foreach ($state['sizes'] as $size)
                <div class="taille {{ $size['available'] === 0 ? 'epuise' : '' }}">
                    {{ $size['label'] }}<b>{{ $size['available'] }}</b>
                </div>
            @endforeach
        </div>
        <div class="barre" aria-hidden="true"><div id="barre" style="width: {{ $state['stock_total'] > 0 ? round($state['sold'] / $state['stock_total'] * 100, 2) : 0 }}%"></div></div>
        <div class="barre-texte" id="vendus">{{ $state['sold'] }} / {{ $state['stock_total'] }} vendus</div>
    </section>

    <form class="action" method="post" action="{{ route('drops.reserve', $drop) }}">
        @csrf
        {{-- Le serveur refuse toute réservation hors fenêtre, quel que soit l'état du bouton. --}}
        <div class="champs">
            <select name="drop_size_id" id="taille-choix" required aria-label="Taille">
                @foreach ($state['sizes'] as $size)
                    <option value="{{ $size['id'] }}" @disabled($size['available'] === 0) @selected((int) old('drop_size_id') === $size['id'])>
                        {{ $size['label'] }} ({{ $size['available'] }} restants)
                    </option>
                @endforeach
            </select>
            <input type="email" name="email" placeholder="ton@email.fr" required maxlength="190" value="{{ old('email') }}" aria-label="E-mail">
        </div>
        <button class="bouton" id="bouton" type="submit" @disabled($state['status'] !== 'live')>Je le veux</button>
        <p class="info" id="info"></p>
        @if (session('reserve_error'))
            <p class="message erreur">{{ session('reserve_error') }}</p>
        @endif
        @if ($errors->has('email') || $errors->has('drop_size_id'))
            <p class="message erreur">{{ $errors->first('email') ?: $errors->first('drop_size_id') }}</p>
        @endif
    </form>

    <section class="attente" id="attente">
        <h2>Prévenez-moi à l'ouverture</h2>
        <form method="post" action="{{ route('drops.waitlist', $drop) }}" id="form-attente" @if ($state['status'] !== 'upcoming') hidden @endif>
            @csrf
            <input type="email" name="email" placeholder="ton@email.fr" required maxlength="190" value="{{ old('email') }}">
            <button type="submit">M'inscrire</button>
        </form>
        @if (session('waitlist_ok'))
            <p class="message">{{ session('waitlist_ok') }}</p>
        @endif
        @if (session('waitlist_error'))
            <p class="message erreur">{{ session('waitlist_error') }}</p>
        @endif
        @error('email')
            <p class="message erreur">{{ $message }}</p>
        @enderror
    </section>

    <footer>Drop · un produit, un jour, pas un de plus</footer>
</main>

<script type="application/json" id="drop-state">{!! json_encode($state, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script>
(() => {
    const stateUrl = @json(route('drops.state', $drop));
    let etat = JSON.parse(document.getElementById('drop-state').textContent);
    // Décalage entre l'horloge serveur et celle du navigateur : le compte à rebours suit le serveur.
    let decalage = new Date(etat.server_now).getTime() - Date.now();
    const maintenant = () => Date.now() + decalage;

    const libelles = {
        upcoming: ['Ouverture dans', () => new Date(etat.opens_at)],
        live: ['Fermeture dans', () => new Date(etat.closes_at)],
        soldout: ['Sold out', null],
        closed: ['Drop terminé', null],
    };
    const statuts = { upcoming: 'à venir', live: 'en vente', soldout: 'sold out', closed: 'terminé' };

    const $ = (id) => document.getElementById(id);
    const pad = (n) => String(n).padStart(2, '0');

    function rendu() {
        $('statut').textContent = statuts[etat.status];
        $('barre').style.width = (etat.stock_total > 0 ? (etat.sold / etat.stock_total * 100) : 0) + '%';
        $('vendus').textContent = `${etat.sold} / ${etat.stock_total} vendus`;
        $('tailles').innerHTML = etat.sizes.map((s) =>
            `<div class="taille ${s.available === 0 ? 'epuise' : ''}"></div>`).join('');
        document.querySelectorAll('#tailles .taille').forEach((el, i) => {
            const s = etat.sizes[i];
            el.textContent = s.label;
            const b = document.createElement('b');
            b.textContent = s.available;
            el.appendChild(b);
        });

        document.querySelectorAll('#taille-choix option').forEach((o) => {
            const s = etat.sizes.find((x) => String(x.id) === o.value);
            if (s) {
                o.disabled = s.available === 0;
                o.textContent = `${s.label} (${s.available} restants)`;
            }
        });

        const bouton = $('bouton');
        bouton.disabled = etat.status !== 'live';
        const formulaire = $('form-attente');
        formulaire.hidden = etat.status !== 'upcoming';

        const info = $('info');
        info.textContent = {
            upcoming: 'Le bouton s\'active à l\'ouverture.',
            live: 'Un clic réserve un exemplaire.',
            soldout: 'Plus d\'exemplaire disponible.',
            closed: 'La vente est terminée.',
        }[etat.status];

        $('compteur-libelle').textContent = libelles[etat.status][0];
        if (!libelles[etat.status][1]) { $('compteur').textContent = '—'; }
    }

    function tic() {
        const cible = libelles[etat.status][1];
        if (!cible) { return; }
        const reste = Math.max(0, cible().getTime() - maintenant());
        const s = Math.floor(reste / 1000);
        $('compteur').textContent = `${pad(Math.floor(s / 3600))}:${pad(Math.floor(s / 60) % 60)}:${pad(s % 60)}`;
        // Passage à l'heure : on demande l'état au serveur (il décide).
        if (reste === 0) { rafraichir(); }
    }

    async function rafraichir() {
        try {
            const r = await fetch(stateUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (!r.ok) { return; }
            etat = await r.json();
            decalage = new Date(etat.server_now).getTime() - Date.now();
            rendu();
        } catch (e) { /* réseau coupé : on garde le dernier état */ }
    }

    rendu();
    tic();
    setInterval(tic, 250);
    setInterval(rafraichir, 2000);
})();
</script>
</body>
</html>
