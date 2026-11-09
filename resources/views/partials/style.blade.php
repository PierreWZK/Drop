<style>
        :root { --fond: #EAE6DE; --bleu: #226192; --orange: #EF8557; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--fond); color: var(--bleu); font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { max-width: 720px; margin: 0 auto; padding: 16px; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 8px 0 16px; }
        .logo { font-weight: 900; font-size: 1.6rem; letter-spacing: .02em; text-transform: uppercase; }
        .logo span { color: var(--orange); }
        .statut { font-weight: 800; text-transform: uppercase; font-size: .85rem; letter-spacing: .08em; border: 2px solid var(--bleu); padding: 4px 10px; }
        .visuel { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border: 2px solid var(--bleu); display: block; }
        h1 { font-size: clamp(2rem, 8vw, 3.4rem); line-height: .95; text-transform: uppercase; margin: 18px 0 8px; font-weight: 900; }
        .prix { font-size: 1.8rem; font-weight: 800; }
        .prix small { color: var(--orange); font-size: 1rem; }
        .description { margin: 12px 0 0; line-height: 1.5; }
        .compteur { margin-top: 22px; background: var(--bleu); color: var(--fond); padding: 18px; text-align: center; }
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
        .minuteur { margin-top: 22px; background: var(--orange); color: var(--bleu); padding: 18px; text-align: center; }
        .minuteur .chiffres { font-size: clamp(3rem, 14vw, 5.5rem); font-weight: 900; font-variant-numeric: tabular-nums; line-height: 1.05; }
        .recap { margin-top: 18px; border: 2px solid var(--bleu); padding: 14px; line-height: 1.6; }
        a.retour { display: inline-block; margin-top: 18px; color: var(--bleu); font-weight: 800; }
</style>
