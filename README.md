# Drop · Un produit, un jour, pas un de plus

> Idée pour le défi **30 jours, 30 projets** · Laravel, MySQL · statut : 💡 idée

**Drop** est une boutique éphémère. Chaque jour, **un seul produit** est mis en vente, en **stock limité**, à une heure précise. Un compte à rebours fait monter l'attente ; à l'ouverture, les premiers servis l'emportent. Quand le stock est épuisé ou que la vente se termine, la page ferme jusqu'au prochain drop.

Le principe vient du streetwear et des sneakers (les « drops » de Supreme ou de Nike SNKRS) : peu d'exemplaires, beaucoup d'acheteurs au même moment.

## Le scénario

> **Drop #12 · Ouverture à 18 h 00**
> Sneakers « Midnight Runner », édition de 20 paires · 129 €
> ⏳ Ouverture dans 02:14:37

1. Avant l'ouverture, la page montre le produit, le stock annoncé et un compte à rebours. On peut s'inscrire pour être prévenu.
2. À 18 h, le bouton « Je le veux » s'active. Un clic **réserve un exemplaire pendant 10 minutes**.
3. Pendant ces 10 minutes, l'acheteur remplit ses coordonnées et paie (Stripe en mode test).
4. Sans paiement dans le temps imparti, l'exemplaire **retourne dans le stock** pour le suivant.
5. Le compteur « reste 7 / 20 » se met à jour en direct. À zéro, la vente affiche **« Sold out »**.

## Le défi technique

Le cœur du projet, c'est de **ne jamais vendre plus que le stock**, même quand 50 personnes cliquent sur le dernier exemplaire à la même milliseconde.

- La réservation décrémente le stock dans une **transaction**, avec un verrou sur la ligne du drop (`lockForUpdate`) ou une mise à jour conditionnelle (`UPDATE … SET reserved = reserved + 1 WHERE stock - reserved > 0`).
- Les réservations expirées sont libérées par une tâche planifiée (chaque minute) **et** vérifiées à chaque nouvelle réservation, pour ne pas dépendre de la tâche.
- Une seule réservation active par acheteur et par drop.
- **Limite de débit** sur le bouton, pour freiner les robots.
- Un **test de charge** (par exemple 200 requêtes simultanées sur un stock de 20) prouve qu'il y a exactement 20 ventes.

## MVP — ce qui doit fonctionner

1. **Back-office** : créer un drop (produit, photos, prix, stock, date et heure d'ouverture, durée).
2. **Page du drop** : avant, pendant et après la vente, avec compte à rebours et stock restant.
3. **Réservation de 10 minutes** avec minuteur visible, libération automatique à l'expiration.
4. **Paiement** Stripe Checkout en mode test, confirmation par webhook, e-mail de confirmation.
5. **Zéro survente**, prouvé par un test automatisé de concurrence.
6. **Archives** des drops passés (« Sold out en 3 min 12 s »).

### Périmètre volontairement limité

- Un seul produit par drop, sans variantes (une taille unique, ou la taille choisie dans la réservation en bonus).
- Pas de compte client obligatoire : un e-mail suffit.
- Paiement en mode test uniquement.

## Données

| Table | Contenu |
| --- | --- |
| `drops` | Produit, description, prix (centimes), stock, `opens_at`, `closes_at`, photos |
| `reservations` | Drop, e-mail, `expires_at`, statut (réservée, payée, expirée) |
| `orders` | Réservation payée, montant, identifiant Stripe, adresse |
| `waitlist` | E-mails à prévenir à l'ouverture |

## Interface

- Esthétique streetwear : grand visuel du produit, typographie forte, compteur géant.
- Le compteur de stock se met à jour toutes les 2 secondes (requête légère), ou en temps réel avec Reverb en bonus.
- Une barre de progression pendant la vente : « 13 / 20 vendus ».

## Critères de réussite

- [ ] 200 achats simultanés sur un stock de 20 donnent exactement 20 commandes.
- [ ] Une réservation non payée revient dans le stock après 10 minutes.
- [ ] Le bouton ne s'active pas avant l'heure d'ouverture, même en trichant côté navigateur.
- [ ] Un paiement réussi crée une commande et envoie l'e-mail, une seule fois (webhook idempotent).

## Bonus

- File d'attente virtuelle à l'ouverture (« Vous êtes 34ᵉ »).
- Tirage au sort (« raffle ») au lieu du premier arrivé.
- Choix de la taille, avec un stock par taille.
- Temps réel avec Reverb.
- Page de statistiques du drop : vitesse de vente, taux d'abandon.

## Stack

Laravel, MySQL, Blade + un peu de JavaScript (compte à rebours, minuteur), Stripe Checkout (mode test), tâches planifiées Laravel. Hébergement Coolify, sous-domaine `drop.projectp.fr`.
