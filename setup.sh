#!/bin/bash

cp .env .env.local
sudo mkdir -p ./var/cache/dev
sudo mkdir -p ./var/log
sudo chown $(id -u):$(id -g) ./var/cache
sudo chown $(id -u):$(id -g) ./var/log
docker/bin/composer install
docker/bin/console doctrine:migrations:migrate
docker/bin/console doctrine:fixtures:load

docker composer up