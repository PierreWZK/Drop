<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://api.fontshare.com">
<link href="https://api.fontshare.com/v2/css?f[]=general-sans@400,500,600&display=swap" rel="stylesheet">
<style>
        :root { --fond: #EAE6DE; --bleu: #226192; --orange: #EF8557; --marine: #14385a; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--fond); color: var(--marine); font-family: "General Sans", system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { max-width: 720px; margin: 0 auto; padding: 16px; }
        .site-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 8px 0 20px; }
        .site-header nav { display: flex; align-items: center; gap: 18px; font-size: .95rem; font-weight: 500; }
        .site-header nav a { color: var(--marine); text-decoration: none; }
        .site-header nav a:hover { text-decoration: underline; text-underline-offset: 4px; }
        .logo { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; color: var(--marine); font-weight: 700; font-size: 1.7rem; letter-spacing: -.03em; }
        .logo svg { width: 2.1rem; height: 2.1rem; }
        .statut { white-space: nowrap; font-weight: 800; text-transform: uppercase; font-size: .85rem; letter-spacing: .08em; border: 2px solid var(--bleu); padding: 4px 10px; }
        .visuel { width: 100%; aspect-ratio: 1 / 1; display: block; }
        h1 { font-family: "Anton", Impact, sans-serif; font-weight: 400; font-size: clamp(2.4rem, 9vw, 4rem); line-height: .98; text-transform: uppercase; margin: 18px 0 8px; }
        .prix { font-size: 1.8rem; font-weight: 800; }
        .prix small { color: var(--orange); font-size: 1rem; }
        .description { margin: 12px 0 0; line-height: 1.5; }
        .compteur { margin-top: 22px; background: var(--marine); color: var(--fond); padding: 18px; text-align: center; }
        .compteur .libelle { font-size: .85rem; text-transform: uppercase; letter-spacing: .08em; }
        .compteur .chiffres { font-size: clamp(2.6rem, 12vw, 5rem); font-weight: 900; font-variant-numeric: tabular-nums; line-height: 1.05; color: var(--orange); }
        .stock { margin-top: 22px; }
        .stock h2 { font-size: 1rem; text-transform: uppercase; letter-spacing: .08em; margin: 0 0 8px; }
        .tailles { display: flex; flex-wrap: wrap; gap: 8px; }
        .taille { border: 2px solid var(--bleu); padding: 8px 12px; min-width: 64px; text-align: center; font-weight: 700; }
        .taille b { display: block; font-size: 1.4rem; font-variant-numeric: tabular-nums; }
        .taille.epuise { opacity: .45; text-decoration: line-through; }
        .barre { margin-top: 18px; height: 16px; border: 2px solid var(--bleu); background: #fff; }
        .barre div { height: 100%; width: 0; background: var(--orange); transition: width .4s ease; }
        .barre-texte { margin-top: 6px; font-weight: 800; font-variant-numeric: tabular-nums; }
        .action { margin-top: 24px; }
        .bouton { width: 100%; padding: 18px; font: inherit; font-size: 1.3rem; font-weight: 900; text-transform: uppercase; border: 0; background: var(--orange); color: var(--bleu); cursor: pointer; }
        .bouton:disabled { background: #cfc9bd; color: #7d7a72; cursor: not-allowed; }
        .info { margin-top: 10px; font-size: .95rem; }
        .attente { margin-top: 28px; border-top: 2px dashed var(--orange); padding-top: 16px; }
        .attente form { display: flex; gap: 8px; flex-wrap: wrap; }
        .attente input { flex: 1 1 220px; padding: 12px; border: 2px solid var(--bleu); font: inherit; color: var(--bleu); background: #fff; }
        .attente button { padding: 12px 18px; font: inherit; font-weight: 800; border: 2px solid var(--bleu); background: var(--bleu); color: var(--fond); cursor: pointer; }
        .message { margin-top: 8px; font-weight: 700; }
        .message.erreur { color: #9b1c1c; }
        footer { margin-top: 40px; font-size: .8rem; text-align: center; opacity: .8; }
        .champs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; }
        .champs select, .champs input { flex: 1 1 200px; padding: 12px; border: 2px solid var(--bleu); font: inherit; color: var(--bleu); background: #fff; }
        .minuteur { margin-top: 22px; background: var(--orange); color: var(--marine); padding: 18px; text-align: center; }
        .minuteur .chiffres { font-size: clamp(3rem, 14vw, 5.5rem); font-weight: 900; font-variant-numeric: tabular-nums; line-height: 1.05; }
        .recap { margin-top: 18px; border: 2px solid var(--bleu); padding: 14px; line-height: 1.6; }
        a.retour { display: inline-block; margin-top: 18px; color: var(--bleu); font-weight: 800; }
        .visuel { position: relative; overflow: hidden; background: #E7E1D4; }
        .visuel img, .visuel svg { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        @media (max-width: 480px) {
            .site-header { gap: 8px; }
            .site-header nav { gap: 10px; font-size: .85rem; }
            .logo { font-size: 1.35rem; gap: 6px; }
            .logo svg { width: 1.8rem; height: 1.8rem; }
            .statut { padding: 3px 8px; font-size: .72rem; letter-spacing: .04em; }
        }
</style>
