#!/bin/bash

apt-get update
apt-get install -y git zip libzip-dev

pecl install zip
docker-php-ext-enable zip
pecl install xdebug
docker-php-ext-enable xdebug
docker-php-ext-install pdo_mysql

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

php composer.phar install

git submodule update --init --recursive --remote

cp ./lib/ci/config.ci.yml ./config.yml