// Vitrine : compte à rebours géant (horloge du serveur), stock en direct, apparition des sections.
(() => {
    const raw = document.getElementById('vitrine-state');
    const data = raw ? JSON.parse(raw.textContent) : { state: null, stateUrl: null };
    let etat = data.state;
    const $ = (id) => document.getElementById(id);
    const pad = (n) => String(n).padStart(2, '0');

    const libelles = { upcoming: 'avant l\'ouverture', live: 'avant la fin de la vente', soldout: 'sold out', closed: 'drop terminé' };

    // Décalage entre l'horloge du serveur et celle du navigateur : le compteur suit le serveur.
    let decalage = etat ? new Date(etat.server_now).getTime() - Date.now() : 0;
    const maintenant = () => Date.now() + decalage;

    function cible() {
        if (!etat) return null;
        if (etat.status === 'upcoming') return new Date(etat.opens_at).getTime();
        if (etat.status === 'live') return new Date(etat.closes_at).getTime();
        return null;
    }

    function ecrireChiffres(texte) {
        if ($('digits').textContent !== texte) $('digits').textContent = texte;
        $('digits').setAttribute('aria-label', `${texte} ${libelles[etat ? etat.status : ''] || 'Prochain drop bientôt'}`);
    }

    function tic() {
        const t = cible();
        if (t === null) { ecrireChiffres('00:00:00'); return; }
        const reste = Math.max(0, t - maintenant());
        const s = Math.floor(reste / 1000);
        ecrireChiffres(`${pad(Math.floor(s / 3600))}:${pad(Math.floor(s / 60) % 60)}:${pad(s % 60)}`);
        // À l'heure pile, le serveur décide du nouvel état.
        if (reste === 0) rafraichir();
    }

    function rendu() {
        if (!etat) return;
        $('libelle').textContent = libelles[etat.status] || '';
        const jauge = $('jauge');
        if (jauge) jauge.style.width = (etat.stock_total > 0 ? etat.sold / etat.stock_total * 100 : 0) + '%';
        const vendus = $('vendus');
        if (vendus) vendus.textContent = `${etat.sold} / ${etat.stock_total} vendus`;
        etat.sizes.forEach((s) => {
            const el = document.querySelector(`[data-taille="${s.id}"]`);
            if (!el) return;
            el.classList.toggle('epuise', s.available === 0);
            const b = el.querySelector('b');
            if (b) b.textContent = s.available;
        });
    }

    let enCours = false;
    async function rafraichir() {
        if (!data.stateUrl || enCours) return;
        enCours = true;
        try {
            const r = await fetch(data.stateUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' });
            if (r.ok) {
                etat = await r.json();
                decalage = new Date(etat.server_now).getTime() - Date.now();
                rendu();
            }
        } catch (e) { /* réseau coupé : on garde le dernier état */ }
        enCours = false;
    }

    if (etat) {
        tic();
        setInterval(tic, 250);
        setInterval(rafraichir, 2000);
    }

    // Apparition des sections au défilement (le contenu reste visible sans script).
    const items = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.1 });
        items.forEach((el) => io.observe(el));
    } else {
        items.forEach((el) => el.classList.add('in'));
    }
})();
