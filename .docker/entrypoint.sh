#!/bin/bash

#On error no such file entrypoint.sh, execute in terminal - dos2unix .docker\entrypoint.sh


## FRONTEND ##
npm config set cache /var/www/.npm-cache --global
cd /var/www/frontend && npm install && cd ..


## BACKEND ##

cd backend

if [ ! -f ".env" ]; then
  cp .env.example .env
fi
if [ ! -f ".env.testing" ]; then
  cp .env.testing.example .env.testing
fi
php artisan key:generate
composer install
php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan route:clear



php-fpm
