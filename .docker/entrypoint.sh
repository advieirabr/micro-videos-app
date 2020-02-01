#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
cp .env.example .env
cp .env.testing.example .env.testing
composer install
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan key:generate
php artisan migrate


php-fpm
