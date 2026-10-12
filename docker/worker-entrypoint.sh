#!/bin/sh
set -e

cd /app
export HOME=/tmp

echo "Waiting for the CMS to finish migrations..."
until [ -f var/cms-ready ]; do
    sleep 2
done

exec php bin/console messenger:consume async -vv --time-limit=3600 --memory-limit=256M
