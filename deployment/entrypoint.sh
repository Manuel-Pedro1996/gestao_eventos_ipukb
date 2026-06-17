#!/bin/sh
php artisan migrate --force
php artisan optimize
exec "$@"