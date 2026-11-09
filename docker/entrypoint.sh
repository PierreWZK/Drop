#!/bin/sh
set -e
cd /var/www/html

# Les commandes artisan tournent en www-data : les fichiers (logs, caches) restent écrivables par Apache.
run() { su -s /bin/sh www-data -c "$*"; }

# Un volume monté sur storage/ (photos des drops) appartient à root au premier démarrage.
chown -R www-data:www-data storage bootstrap/cache

# La base peut mettre quelques secondes à démarrer : on réessaie.
i=0
until run "php artisan migrate --force"; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
        echo "Migrations impossibles après 30 essais" >&2
        exit 1
    fi
    echo "Base indisponible, nouvel essai dans 3 s ($i/30)"
    sleep 3
done

# Catalogue de 50 produits : idempotent, ne modifie jamais un produit déjà en base.
run "php artisan db:seed --class=CatalogSeeder --force"
# Crée tout de suite le drop du jour / de demain s'il n'existe pas (sinon : au prochain passage horaire).
run "php artisan drops:draw" || true

run "php artisan storage:link --force" || true
run "php artisan config:cache"
run "php artisan route:cache"
run "php artisan view:cache"

# Planificateur Laravel : libère les réservations expirées chaque minute.
( while true; do run "php artisan schedule:run" >/dev/null 2>&1; sleep 60; done ) &

exec apache2-foreground
