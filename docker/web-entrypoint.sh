#!/bin/sh
set -e

cd /app
export HOME=/tmp

if [ "${CMS_COMPOSER_INSTALL:-0}" = "1" ]; then
    composer install --no-interaction --prefer-dist
fi

php bin/console doctrine:database:create --if-not-exists --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console assets:install public --no-interaction
php bin/console app:bootstrap --no-interaction

# Dev: clearing cache on every container start blocks php -S; nginx then returns 502 for
# /_wdt/* and the Web Debug Toolbar stays on "Loading…".
if [ "${CMS_CACHE_CLEAR_ON_START:-0}" = "1" ]; then
    php bin/console cache:clear --no-interaction
fi

mkdir -p var
touch var/cms-ready

exec php -S 0.0.0.0:8000 -t public /opt/cms/router.php
