#!/bin/sh
set -e

php bin/console doctrine:migrations:migrate --no-interaction

php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug

# Start PHP-FPM
php-fpm -D
