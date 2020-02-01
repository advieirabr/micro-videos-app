#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh
cp .env.example .env
cp .env.testing.example .env.testing
php artisan key:generate
composer install
php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan route:clear



php-fpm
