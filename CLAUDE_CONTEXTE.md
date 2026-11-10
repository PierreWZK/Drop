# CLAUDE_CONTEXTE — Drop (Jour 26)

Journal de bord du projet. À mettre à jour à chaque étape : demandes, travail fait, échecs, tests.

## Règles de travail (demandées par l'utilisateur)

- Maximum 5 étapes pour obtenir une V1 fonctionnelle.
- Valider chaque étape avec l'utilisateur avant de passer à la suivante.
- Après la V1 : demander à l'utilisateur de tout tester, puis récupérer la liste des modifications à faire.
- Si un dépôt GitHub est créé, la branche principale s'appelle `master`, même si l'utilisateur écrit « main » (erreur de sa part).
- Poser toutes les questions avant de commencer. Fait (voir « Décisions »).
- Ce fichier liste : les demandes (simplifiées), ce qui a été fait, ce qui a échoué, ce qui a été testé.

## Demandes de l'utilisateur (simplifié)

1. Jour 26 : un site de vente journalier, **Drop**, d'après le README fourni (voir `README.md` du dossier).
2. Écrire ce CLAUDE_CONTEXTE.md et le tenir à jour.
3. Une V1 fonctionnelle en 5 étapes maximum.
4. Poser les questions avant de commencer.
5. Palette imposée : `#EAE6DE`, `#226192`, `#EF8557`. L'orange sert aussi à afficher des fleurs (motifs décoratifs et logo).

## Décisions (réponses aux questions)

- Dossier : `day-26-drop` (le dossier `day-26-garage-manager` existe déjà et reste intact).
- Stack : Laravel (PHP 8.4, Composer présents), Blade + JavaScript léger. SQLite en local, MySQL en production. MySQL et Docker ne sont pas installés sur la machine.
- Paiement : Stripe Checkout en mode test, avec un mode simulé local. Webhook idempotent. L'utilisateur mettra lui-même sa clé `sk_test` dans le `.env`.
- E-mails : écrits dans les logs en local. SMTP branché plus tard par l'utilisateur.
- Stock en direct : requête légère toutes les 2 secondes, sans Reverb. Le site est un simple site de test.
- Bonus retenu pour la V1 : choix de la taille avec stock par taille. Aucun autre bonus.
- Produits : tout type de produit, surtout de la mode. Pas de produit imposé.
- Design : fond `#EAE6DE`, texte et éléments principaux `#226192`, accents et fleurs `#EF8557`. Esthétique streetwear, typographie forte, compteur géant. Fleurs orange uniquement en motifs décoratifs, et dans le logo.
- Images produit : vraies photos libres de droits (banques gratuites), aucune image générée.
- Déploiement : repo GitHub (branche `master`) puis Coolify, sous-domaine `drop.projectp.fr`, à l'étape 5.

## Plan en 5 étapes

| # | Étape | Statut |
|---|-------|--------|
| 1 | Socle : projet Laravel, schéma (drops, tailles, reservations, orders, waitlist), design system, back-office pour créer un drop | **Fait, en attente de validation utilisateur** |
| 2 | Page publique du drop : avant / pendant / après, compte à rebours, stock en direct (2 s), barre de progression, liste d'attente | **Validée par l'utilisateur** |
| 3 | Réservation 10 min : transaction + verrou, stock par taille, une réservation active par acheteur, limite de débit, libération des expirées (tâche planifiée + à chaque réservation), minuteur visible | **Fait, en attente de validation utilisateur** |
| 4 | Paiement Stripe Checkout (simulé + réel en test), webhook idempotent, commande, e-mail de confirmation, archives (« Sold out en 3 min 12 s ») | **Fait, en attente de validation utilisateur** |
| 5 | Test automatisé de concurrence (200 requêtes pour un stock de 20), Dockerfile, repo GitHub `master`, déploiement Coolify | **Fait : V1 en ligne, tests utilisateur à faire** |

## Fait

- Questions posées et réponses enregistrées.
- Étape 1 (socle), Laravel 13.40, PHP 8.4, base SQLite (`database/database.sqlite`) :
  - Projet Laravel créé dans `day-26-drop`. `.env` en SQLite, clé générée.
  - Migrations : `drops` (slug, titre, description, prix en centimes, photo, `opens_at`, `closes_at`, durée de réservation), `drop_sizes` (taille, stock, `reserved`, `sold`, unique par drop), `reservations` (email, statut reserved/paid/expired, `expires_at`), `orders` (réservation unique, montant, `stripe_session_id` unique, adresse, `paid_at`), `waitlist` (email unique par drop).
  - Modèles `Drop` et `DropSize`.
  - Back-office : `/admin/drops/create` (formulaire) et `POST /admin/drops`. Accès protégé par Basic Auth (`ADMIN_USER` / `ADMIN_PASSWORD` dans `.env`). Photo stockée sur le disque `public`, lien `storage:link` créé.
  - Charte : fond `#EAE6DE`, texte `#226192`, accent `#EF8557` (formulaire seulement, pas encore la vitrine).
  - Fichiers Laravel `CLAUDE.md` et `AGENTS.md` demandent d'installer Laravel Boost : **non fait**, pas demandé.
  - `README.md` du dossier restauré (Laravel l'avait écrasé pendant la copie).

## Pas réussi / limites connues

- `ADMIN_PASSWORD=change-me-local` est un mot de passe de test. À changer avant déploiement.
- Pas encore de page publique (étape 2).
- Pas encore de réservation, paiement, ni test de concurrence.
- Limite prévue : SQLite verrouille toute la base. Le test de concurrence réel (`lockForUpdate`) doit être rejoué sur MySQL en production.
- Pas de dépôt Git créé.
- Limite prévue : SQLite verrouille toute la base. Le test de concurrence réel (`lockForUpdate`) doit être rejoué sur MySQL en production.

## Tests effectués

- `php artisan migrate` : 2 migrations de drop passent sur SQLite.
- `php -l` sur `AdminBasicAuth.php` et `bootstrap/app.php` : OK.
- `php artisan route:list --path=admin` : 2 routes admin présentes.
- Serveur local (port 8026) :
  - `/admin/drops/create` sans identifiants : 401.
  - Avec identifiants : 200.
  - POST d'un drop test (« Veste Test », 129 €, S=5, M=15) : 302, drop créé avec 12900 centimes et stock total 20.
  - Drop test supprimé après le test.
- Non testé : affichage du formulaire dans un navigateur, upload de photo réel.
- Étape 2 : 10 tests PHPUnit (`php artisan test`) passent : statut, état JSON, bouton avant/pendant l'ouverture, liste d'attente (unique, refusée une fois ouvert, e-mail invalide), accueil.
- Étape 2 : vérification serveur local (port 8026) : `/drops/{slug}/state` en 200, page en 200 avec bouton actif en vente, statut « en vente ». POST waitlist sans CSRF : 419 attendu.
- Test non fait : navigateur réel (compte à rebours, rafraîchissement 2 s à l'écran).
- Étape 3 : 22 tests PHPUnit passent au total (12 nouveaux dans `ReservationTest`) : réservation OK, POST forcé avant l'ouverture et après la fermeture refusés, une réservation par e-mail, même navigateur renvoyé vers sa réservation, 30 tentatives sur un stock de 5 donnent exactement 5 réservations, taille d'un autre drop refusée, expiration par la commande, expiration à la volée sans scheduler, réservation payée jamais expirée, limite de débit (7e requête : 429), page expirée.
- Étape 3 : `php artisan schedule:list` montre la tâche chaque minute.
- Limite des tests de l'étape 3 : séquentiels. Ils ne prouvent pas la concurrence réelle (SQLite, un seul processus). Le test à 200 requêtes simultanées est prévu à l'étape 5, sur MySQL.
- Non testé : formulaire et page de réservation dans un navigateur réel, minuteur à l'écran.
- Étape 4 : 33 tests PHPUnit passent (11 nouveaux dans `PaymentTest`) : flux simulé (1 commande, stock en `sold`, 1 e-mail), double confirmation (1 commande, 1 e-mail), paiement après expiration refusé, routes simulées 404 hors mode simulé, webhook avec signature valide (rejeu compris), signature invalide (400), montant faux (aucune commande), paiement tardif (aucune commande), « Sold out en 3 min 12 s » et archive « Non épuisé ».
- Bug trouvé et corrigé : `confirm` n'appelait pas `expireStale` avant de vérifier la réservation (`start` le faisait). Aligné.
- Non testé : appel réel à l'API Stripe (aucune clé de test fournie). La signature est testée avec un HMAC local, pas avec un vrai événement Stripe.
- Non testé : envoi SMTP réel (log uniquement).
- Non testé : navigateur réel pour le flux de paiement.

- Étape 5, local (SQLite) : `drops:load-test` 200 acheteurs / stock 20 / 30 processus : exactement 20 commandes. Avant correction, SQLite renvoyait des « database is locked » (pas de survente non plus).
- Étape 5, **production (MySQL 8)** : premier essai : 20 commandes, mais 12 deadlocks (échec). Après correctif de l'ordre des verrous, **3 essais sur 3 réussis** : 20 commandes, `sold = 20`, `reserved = 0`, 0 erreur (200 acheteurs, 20 processus en parallèle, 24 à 28 s).
- Étape 5 : 36 tests PHPUnit passent (3 nouveaux : page de test de charge protégée, absente en production).
- Étape 5, en ligne : `/up` 200, `/archives` 200, `/admin/drops/create` 401 sans identifiants, `/stripe/webhook` 400 sans signature valide, `/` 404 (aucun drop).
- Non testé en ligne : création d'un drop, upload de photo, réservation et paiement simulé dans un vrai navigateur, tâche planifiée du conteneur (`schedule:run`), persistance du volume des photos après redéploiement, rendu mobile.
- Non testé : requêtes HTTP concurrentes (le test passe par les services, pas par la limite de débit ni par Apache).

### À faire avant production (étape 5)

- `DROP_PAYMENT_MODE=stripe`, `STRIPE_SECRET` et `STRIPE_WEBHOOK_SECRET` (clés **test** `sk_test_` / `whsec_`, mises par l'utilisateur dans le `.env` du serveur).
- Enregistrer le webhook dans le tableau de bord Stripe : `https://drop.projectp.fr/stripe/webhook`, événement `checkout.session.completed`.
- SMTP réel dans `.env`.
- Cron : `* * * * * php artisan schedule:run`.
- Changer `ADMIN_PASSWORD`.
- Paiement tardif : vérifier les logs `warning` et rembourser à la main.

### Étape 2 (page publique), validée par l'utilisateur

- Routes : `/` (drop en cours ou à venir, sinon le dernier fermé), `/drops/{slug}`, `/drops/{slug}/state` (JSON, `no-store`), `POST /drops/{slug}/waitlist` (throttle 10/min).
- `Drop::status()` : `upcoming` / `live` / `soldout` / `closed`, calculé côté serveur.
- Page : statut, photo si présente (pas de placeholder), prix en €, compte à rebours (horloge serveur), stock par taille, barre « X / Y vendus », bouton « Je le veux » désactivé sauf en `live`, formulaire liste d'attente visible seulement avant l'ouverture.
- JS : interroge `/state` toutes les 2 s, et à l'heure d'ouverture ou de fermeture.
- Bug trouvé et corrigé : le bouton était rendu `disabled` côté serveur même en vente (le JS corrigeait après coup).
- Modèle `WaitlistEntry` (table `waitlist`). Inscription unique par drop et par e-mail (casse ignorée). Refusée une fois le drop ouvert.
- Limite : le bouton n'est pas encore relié à une réservation. Le serveur n'accepte encore aucune réservation (étape 3).

### Étape 3 (réservation), en attente de validation

- `ReservationService` (`app/Services`) :
  - `reserve()` : libère d'abord les expirées du drop, puis transaction avec `lockForUpdate` sur la ligne du drop. Vérifie ouverture et fermeture avec l'horloge serveur, une seule réservation active par e-mail et par drop, puis `UPDATE drop_sizes SET reserved = reserved + 1 WHERE stock - reserved - sold > 0`. Sans ligne modifiée : « soldout ».
  - `expireStale()` : passe les réservations échues en `expired` et rend le stock. Conditionnel sur `status = reserved`, donc une réservation payée n'est jamais touchée.
- Commande `drops:expire-reservations`, planifiée chaque minute (`routes/console.php`). Le scheduler doit tourner en production (à prévoir à l'étape 5, cron `php artisan schedule:run`).
- Routes : `POST /drops/{slug}/reserve` (throttle 6/min par IP), `GET /reservations/{token}` (jeton aléatoire de 40 caractères).
- Page du drop : choix de la taille + e-mail + bouton « Je le veux » (actif seulement en vente). Page de réservation : récap, minuteur, rechargement à zéro, message si expiré.
- Vie privée : si un e-mail a déjà une réservation active, le lien n'est redonné qu'au navigateur qui l'a créée (session). Sinon message d'erreur.
- La base locale a été réinitialisée (`migrate:fresh`) : colonne `token` ajoutée à `reservations`. Aucune donnée perdue de valeur.
- Le paiement n'existe pas encore : la page de réservation affiche « bientôt disponible (étape 4) ».
- CSS commun extrait dans `resources/views/partials/style.blade.php`.

### Étape 4 (paiement, commandes, archives), en attente de validation

- Mode de paiement : `DROP_PAYMENT_MODE` dans `.env` (`simulated` local, `stripe` pour Stripe Checkout). Le mode simulé est **refusé en production** (`app()->isProduction()`), même si la variable est mal réglée.
- Flux simulé : « Payer » → `/reservations/{token}/simulate` (nom + adresse, aucune carte) → `OrderService::markPaid`.
- Flux Stripe : « Payer » → session Checkout (mode `payment`, adresse de livraison FR/BE/CH/LU, `metadata.reservation_id`) → redirection Stripe. Pas de `expires_at` sur la session (Stripe impose 30 min minimum) : la limite de 10 min est appliquée par le serveur au moment du webhook.
- Webhook `POST /stripe/webhook` (hors CSRF) : signature vérifiée par `Stripe\Webhook::constructEvent`. Seul `checkout.session.completed` avec `payment_status = paid` compte. Montant contrôlé contre le prix du drop.
- `OrderService::markPaid` : idempotent. Une commande par réservation (`orders.reservation_id` unique), et un `stripe_session_id` unique. Un rejeu ne crée ni commande ni e-mail. Dans la même transaction : `reserved → sold` et `status = paid`.
- Paiement tardif (réservation expirée) : pas de commande, log `warning`. Remboursement à faire à la main.
- E-mail `OrderConfirmation` envoyé une seule fois, après création de la commande. Local : `MAIL_MAILER=log`.
- Colonne `drops.sold_out_at` : première fois que le stock libre tombe à 0 (réservations comprises). Affichage « Sold out en 3 min 12 s » sur `/archives`, calculé depuis l'ouverture.
- Page de réservation : bouton « Payer », message « Commande confirmée » une fois payée.
- Dépendance ajoutée : `stripe/stripe-php`.

### Demande 2026-11-09 : drops tirés au hasard dans un catalogue de 50 produits

Précision de l'utilisateur : garder la création manuelle de drop, et ajouter un catalogue de ~50 produits dont un est tiré chaque jour.

Réponses enregistrées : tirage automatique chaque jour ; ouverture 18 h, 1 h de vente, stock tiré entre 10 et 30 (réparti entre les tailles, au moins 1 par taille) ; photos réelles libres de droits ; un produit ne ressort pas avant que les 50 soient passés (puis nouveau cycle).

- Fait (code) :
  - Table `catalog_products` (nom unique, description, prix, tailles en JSON, photo, `drawn_at`, `times_drawn`), colonne `drops.catalog_product_id`.
  - `CatalogSeeder` : 50 produits fictifs, idempotent (`firstOrCreate`). Rattache une photo si `public/catalog/<slug>.jpg` existe.
  - `DropDrawService::drawFor()` : tirage sans répétition dans le cycle, verrou, idempotent par jour, refuse une heure d'ouverture passée (sauf `--date`).
  - `drops:draw` : planifié chaque heure (`routes/console.php`), crée aujourd'hui (si 18 h pas passée) et demain.
  - Fuseau horaire passé à `Europe/Paris` (18 h = heure de Paris).
  - Entrypoint Docker : seed + tirage au démarrage.
- Testé : 47 tests PHPUnit passent (seeder idempotent, 18 h → 19 h, stock 10–30 sur 50 tirages, pas de doublon avant le cycle complet, commande horaire idempotente, heure passée, catalogue vide, photo catalogue vs upload). Seed réel : 50 produits, 50 noms distincts. `drops:draw` lancé deux fois en local : un seul drop, « Besace Cuir Cognac », 16 exemplaires.
- **Non fait : photos.** Openverse renvoie 504 sur toutes les requêtes (session, 50 produits, deux termes chacun). Wikimedia Commons répond (200), mais les résultats sont de qualité inégale (« chore jacket » donne des photos de personnes ou de livres). Un script de recherche + téléchargement est prêt dans le scratchpad (`photos.js`), mais il n'a rien trouvé. Aucun fichier dans `public/catalog/`, donc pas de `CREDITS.md`.
- **Non fait : déploiement.** Rien n'est poussé ni redéployé pour cette demande. Les commits restent locaux sur `feat/mise-en-ligne`.

### Demande 2026-11-10 : refonte de la landing, logo, illustrations, header (branche `feat/refonte-landing`)

Demandes : (1) tester le site avec Playwright et corriger les erreurs (dont le CTA du hero) ; (2) une belle fleur pour le logo ; (3) refaire les images « générées » en vraies illustrations de produit (ex. tee manches longues rayé avec le logo) ; (4) garder le header sur la page produit pour revenir à l'accueil ; (5) hero d'après une image de référence (grand titre condensé noir sur forme orange, fond beige) ; (6) refaire chaque section de la landing pour un rendu plus professionnel.

- **Bug du CTA du hero (trouvé avec Playwright)** : à 1920 px, les chiffres géants du compteur (un `span` par chiffre, boîtes qui débordent) recouvraient le bouton du hero (`elementFromPoint` renvoyait un `SPAN`). Le compteur est maintenant séparé du CTA (barre « dock » marine en bas du hero) ; contrôle `elementFromPoint` OK à 390 et 1920 px, clic réel OK jusqu'à la page produit.
- **Logo** : fleur à deux couronnes de pétales (8 grands, 8 petits), nervures, cœur marine avec points beiges (`partials/fleur-symbol`, symbole `#fleur`, couleurs par variables `--p1 --p2 --coeur --pistil`). Grande fleur granuleuse orange pour les décors (`partials/fleur-geante`, symbole `#fleur-geante`, motif de grain en SVG).
- **Hero** : fond beige, titre Anton majuscules sur 3 lignes (4 sur mobile) par-dessus de grandes fleurs orange granuleuses qui tournent lentement, barre marine en bas avec le compteur, le produit du jour et le CTA. Polices : Anton (Google Fonts) + General Sans (Fontshare).
- **Sections refaites** : bandeau défilant (« UN PRODUIT • UN JOUR • 18 H … ») ; Le drop du jour (grande vignette 4:5, prix, tailles avec stock en direct, jauge, boutons) ; Comment ça marche (champ marine, 4 colonnes) ; catalogue (8 produits : 4 avec photo, 4 avec illustration) ; Zéro survente (champ orange, 200 / 20 / 20) ; FAQ (colonne titre + tuiles qui s'ouvrent en marine) ; clôture « Rendez-vous à 18 h. » ; footer avec grand « DROP » rogné en bas.
- **Illustrations** (`app/Support/ProductArt.php`) : 30 illustrations SVG dessinées dans le code pour les produits sans photo, une par produit, chacune avec sa couleur et le logo. Exemple : « T-shirt Manches Longues Rayé » = marinière à rayures bleues et écrues, col et poignets bleus, fleur sur la poitrine. Le nom du produit sert de clé (aucune entrée utilisateur dans le SVG). Les 20 produits avec photo gardent leur photo. `partials/visual` choisit : photo, sinon illustration, sinon plaque de fleurs (produits créés à la main). Pas d'image générée par IA (pas d'outil d'image raster).
- **Header commun** (`partials/site-header`) : logo fleur + « drop » qui ramène à l'accueil, liens Accueil et Archives, pastille de statut. Sur la page produit, la réservation, le paiement simulé, les archives et les crédits. Page produit : visuel carré et contrastes du compteur corrigés.
- **Testé** :
  - 67 tests PHPUnit passent (nouveaux : `ProductArtTest` dont « chaque illustration est un SVG bien formé avec le logo » qui a trouvé 5 produits sans logo, `CatalogArtCoverageTest` : chaque produit du catalogue a une photo ou une illustration, tests du header et du CTA dans `HomeTest`).
  - Playwright (Chromium) : CTA non recouverts à 390 et 1920 px, clic du hero vers la page produit, header avec lien d'accueil, parcours de réservation (taille M, e-mail, redirection vers la réservation, minuteur 10 min), « Payer » désactivé et hors formulaire, `/simulate` forcé en 403, aucune erreur de console (la seule est ce 403 provoqué volontairement), aucun débordement horizontal à 390 / 768 / 1440, aucun titre sur plus de 2 lignes sauf le titre du hero.
  - Inspecteur du skill à 1440 / 768 / 390 px : 0 échec. Autorisés : `palette-claude` (palette imposée), `headline-lines` (le titre du hero fait 3 lignes comme la référence), `eyebrow` (faux positif : la légende du dock, juste avant le bandeau décoratif). Avertissements restants : 14 tailles de police, pas de vidéo.
  - Rendu vérifié sur captures desktop, tablette et mobile.
- **Bugs corrigés en route** : accent du « À » qui écrasait la ligne du dessus ; cœur de la grande fleur de la même couleur que le texte (texte illisible) ; hero trop haut (légende du compteur hors écran) ; débordement horizontal du header sur mobile ; fond du compteur de la page produit en orange sur bleu (contraste) ; chaussures et casquette trop zoomées dans leur cadre.
- **Non fait / non testé** : rien n'est poussé ni déployé pour cette demande. Pas testé : un vrai téléphone, Safari / Firefox, rendu des polices Google Fonts et Fontshare hors ligne (services tiers), animations à l'œil (rotation des fleurs, défilement du bandeau), le compteur à 18 h pile. Les illustrations sont des dessins plats, pas des photos : elles donnent l'idée du produit, pas son rendu réel.

### Demande 2026-11-09 : vitrine, paiement bloqué, images

Demandes : (1) une page vitrine qui dit ce que fait le site, avec un hero « vraiment cool », un CTA sympa et un timer (temps avant l'ouverture, ou temps restant) ; (2) on peut cliquer sur le bouton pour voir le produit, mais « Payer » doit être impossible ; (3) images : prendre celles qu'on trouve, générer les autres.

- **Vitrine sur `/`** (`HomeController`, `resources/views/home.blade.php`, `public/css/vitrine.css`, `public/js/vitrine.js`). Direction « le nom comme monument » (skill design-taste-frontend), le compte à rebours jouant le rôle du nom : `HH:MM:SS` sur toute la largeur de l'écran. Palette imposée #EAE6DE / #226192 / #EF8557, texte courant en bleu foncé #14385a (contraste), polices Gambarino + General Sans (Fontshare).
  - Hero : h1 « Un seul produit par jour, à 18 h. », photo du produit du jour, CTA « Voir le drop du jour », compteur géant (avant l'ouverture / avant la fin de la vente / sold out / terminé), mis à jour par `/drops/{slug}/state` toutes les 2 s, horloge serveur.
  - Sections : Comment ça marche (4 lignes en escalier), Le drop du jour (photo, prix, stock par taille en direct, jauge, boutons), 50 produits un seul sort (3 produits du catalogue), bandeau « 200 acheteurs, 20 exemplaires, 20 commandes » (résultat réel du test de charge), Questions fréquentes, Rendez-vous à 18 h, footer avec grand « drop ».
  - Le drop affiché : celui en cours ou le prochain, sinon le dernier fermé. Sans aucun drop, la vitrine s'affiche quand même (compteur à zéro, lien vers les archives).
  - `Drop::toState()` (état du drop) est partagé par la vitrine, la page du drop et `/state`.
- **Paiement bloqué** : `DROP_PAYMENTS_ENABLED` (défaut `false`, `config/drop.php`). Le bouton « Payer » de la page de réservation est désactivé (aucun formulaire posté), et le serveur refuse `checkout.start` (message), `checkout.simulate` et `checkout.confirm` (403). Pour rouvrir : `DROP_PAYMENTS_ENABLED=true`. Le bouton « Je le veux » et la réservation restent possibles.
- **Images** :
  - 20 photos réelles libres de droits (Wikimedia Commons, CC0 / domaine public / CC BY / CC BY-SA) dans `public/catalog/`, choisies à l'œil sur planche contact parmi 46 candidats. Crédits dans `public/catalog/CREDITS.md`, page `/credits`, lien dans le footer.
  - Écartées : foules, concerts, personnes identifiables, animaux, logos de marque visibles, objets de musée hors sujet.
  - Les 30 produits sans photo affichent une plaque générée : motif de fleurs orange dessiné en SVG (`partials/fleur-symbol`, `partials/plate`). Le logo est une fleur à huit pétales. **Je n'ai pas d'outil de génération d'image raster** : « générer » signifie ici dessiner en SVG dans le code. Pas de photos générées par IA.
  - Les photos illustrent la catégorie (veste, sac…), pas le produit exact : le catalogue est fictif (dit dans la FAQ et les crédits).
  - Les produits du catalogue sans photo et un drop tiré avant l'ajout des photos reprennent la photo du catalogue (`Drop::imageUrl()` : repli sur `catalogProduct`).
- **Testé** : 57 tests PHPUnit passent (9 nouveaux dans `HomeTest`, 1 dans `PaymentTest` pour le paiement bloqué). Inspecteur du skill à 1440 / 768 / 390 px : **0 échec**, 4 avertissements défendus (ci-dessous). Linter du skill : 0 échec. Rendu vérifié sur captures desktop et mobile. `--allow palette-claude` : le skill signale « crème + terracotta » ; c'est la palette imposée par l'utilisateur.
- Avertissements restants, défendus : 10 tailles de police (échelle du monument : compteur, « drop » du footer, titre de clôture) ; pas de vidéo (aucun clip honnête pour un catalogue fictif) ; « vide » dans le hero (faux positif : c'est le compteur, un `span` par chiffre pour qu'il ne bouge pas, car Gambarino n'a pas de chiffres tabulaires) ; texte beige sur bleu à 5,3:1 sur mobile.
- Bug trouvé : le titre du hero était décalé de 30 px sur mobile (`align-self: center` dans une colonne). Corrigé.
- **Non fait / non testé** : rien n'est déployé pour cette demande (Coolify tourne encore sur l'ancien code, sans vitrine ni paiement bloqué). Vitrine non vue sur un vrai téléphone ni sur tablette en capture. Animations (apparition, rétrécissement du compteur au défilement) non vérifiées à l'œil, seulement par l'inspecteur. Polices chargées depuis Fontshare (service tiers). Le compteur de la vitrine n'a pas été regardé à l'instant exact de l'ouverture (18 h).

### Étape 5 (concurrence, Docker, GitHub, Coolify), V1 en ligne

- **Test de charge** : `php artisan drops:load-test --requests=200 --stock=20 --concurrency=20`. Crée un drop temporaire, lance N processus PHP distincts (vraie concurrence en base) qui réservent puis paient, vérifie qu'il y a exactement S commandes, `sold = S`, `reserved = 0`, puis supprime le drop. Passe par les services (pas HTTP, car la limite de débit par IP bloquerait une seule machine). Code de sortie non nul en cas d'écart.
- **Page admin** `/admin/load-test` (Basic Auth) : lance ce test depuis le serveur. Désactivée si `APP_ENV=production`.
- **Dockerfile** : Composer puis `php:8.4-apache`, `pdo_mysql`, `docker/entrypoint.sh` (migrations avec réessais, caches, planificateur `schedule:run` chaque minute en tâche de fond, droits sur `storage/`). Docker n'est pas installé en local : l'image n'a été construite que par Coolify.
- **GitHub** : créé avec `gh repo create PierreWZK/day-26-drop`, mais GitHub l'affiche sous le nom `PierreWZK/Drop` (cause non élucidée ; l'ancienne URL redirige, la PR est sur `PierreWZK/Drop`). **Privé** (la création en public a été refusée par le garde-fou de l'environnement). Branche par défaut `master`, créée via l'API GitHub. Travail sur `feat/mise-en-ligne`, PR vers `master`.
- **Coolify** : projet `ef8f5dauohhoq3itr1dbgnmk` (« Drop »), MySQL 8 `hrekqqyetw8ayibourdc4chv`, application `7r1fdyevbevreg6bzof4dofk`, domaine https://drop.projectp.fr. L'application suit pour l'instant la branche `feat/mise-en-ligne` : **la repasser sur `master` après la fusion de la PR**. Volume persistant `drop-storage` monté sur `/var/www/html/storage/app/public` (photos).
- **Variables d'environnement** (dans Coolify, secrets non recopiés ici) : `APP_ENV=staging`, `APP_DEBUG=false`, `DROP_PAYMENT_MODE=simulated`, `MAIL_MAILER=log`, `DB_*`, `ADMIN_USER=admin`, `ADMIN_PASSWORD` (généré, à lire dans Coolify), `APP_KEY`. `STRIPE_SECRET` et `STRIPE_WEBHOOK_SECRET` ne sont pas définis.
- **`APP_ENV=staging` voulu** : le paiement simulé est refusé quand `APP_ENV=production`. Tant qu'il n'y a pas de clés Stripe de test, le site reste en `staging` pour que le flux de paiement soit testable. N'importe qui peut donc « payer » sans carte sur le site en ligne (site de test, aucun argent). Pour le vrai Stripe : `DROP_PAYMENT_MODE=stripe`, clés de test, `APP_ENV=production`.
- **Bug trouvé par le test sur MySQL, corrigé** : premier essai = exactement 20 commandes (pas de survente) mais 12 deadlocks MySQL (erreur 1213) sur 200. Cause : `expireStale` et `markPaid` verrouillaient les réservations avant le drop, `reserve` l'inverse. Correctif : verrou du drop d'abord partout, et `expireStale` sans transaction quand rien n'a expiré. Aussi : `DB::transaction(..., 5)` pour rejouer en cas de deadlock.
- Piège local : `PHP_BINARY` vaut `apache2` sous mod_php, d'où `PhpExecutableFinder` dans le test de charge.
- SQLite local : `busy_timeout` 15 s, WAL, transactions `IMMEDIATE` (sinon « database is locked » sous charge). N'affecte pas MySQL.
- Le drop de test n'existe pas : `/` renvoie 404 tant qu'aucun drop n'est créé dans `/admin/drops/create`.

### Non fait dans l'étape 2 (à décider)

- Motifs de fleurs orange : demandent de vraies images libres de droits. Pas encore récupérées, pas de dessin fait main.
- Photos produit : dépendent de ce que tu uploades dans le back-office.

## Après la V1

Demander à l'utilisateur de faire tous les tests possibles et de renvoyer la liste des modifications à faire. Ne pas lancer de refonte sans cette liste.
