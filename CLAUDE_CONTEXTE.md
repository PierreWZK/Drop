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

### Étape 5 (concurrence, Docker, GitHub, Coolify), V1 en ligne

- **Test de charge** : `php artisan drops:load-test --requests=200 --stock=20 --concurrency=20`. Crée un drop temporaire, lance N processus PHP distincts (vraie concurrence en base) qui réservent puis paient, vérifie qu'il y a exactement S commandes, `sold = S`, `reserved = 0`, puis supprime le drop. Passe par les services (pas HTTP, car la limite de débit par IP bloquerait une seule machine). Code de sortie non nul en cas d'écart.
- **Page admin** `/admin/load-test` (Basic Auth) : lance ce test depuis le serveur. Désactivée si `APP_ENV=production`.
- **Dockerfile** : Composer puis `php:8.4-apache`, `pdo_mysql`, `docker/entrypoint.sh` (migrations avec réessais, caches, planificateur `schedule:run` chaque minute en tâche de fond, droits sur `storage/`). Docker n'est pas installé en local : l'image n'a été construite que par Coolify.
- **GitHub** : `PierreWZK/day-26-drop`, **privé** (la création en public a été refusée par le garde-fou de l'environnement). Branche par défaut `master`, créée via l'API GitHub. Travail sur `feat/mise-en-ligne`, PR vers `master`.
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
