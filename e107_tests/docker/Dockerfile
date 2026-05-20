# syntax=docker/dockerfile:1.6
#
# e107 test harness — PHP + Apache image
#
# Build args:
#   PHP_VERSION   Tag of the upstream php:<ver>-apache image (default: 8.3)
#   WITH_XDEBUG   "yes" to install and enable Xdebug for code coverage
#
# Example:
#   docker build --build-arg PHP_VERSION=8.4 --build-arg WITH_XDEBUG=yes \
#       -t e107-web:8.4-xdebug e107_tests/docker
#
# Notes:
#   * The image installs every PHP extension e107 needs to install and run.
#   * Composer, git, rsync, and unzip are baked in so tests can be driven
#     end-to-end inside the container.
#   * No source code is COPY'd in. The worktree is bind-mounted at runtime,
#     which is what makes the image reusable across worktrees.

ARG PHP_VERSION=8.3
FROM docker.io/library/php:${PHP_VERSION}-apache

ARG WITH_XDEBUG=no

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        imagemagick \
        less \
        libbz2-dev \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg-dev \
        libonig-dev \
        libpng-dev \
        libwebp-dev \
        libxml2-dev \
        libzip-dev \
        mariadb-client \
        rsync \
        unzip \
        wget \
        zip; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" \
        bz2 \
        curl \
        gd \
        intl \
        mbstring \
        mysqli \
        opcache \
        pdo_mysql \
        xml \
        zip

RUN set -eux; \
    if [ "$WITH_XDEBUG" = "yes" ]; then \
        pecl install xdebug; \
        docker-php-ext-enable xdebug; \
    fi

RUN set -eux; \
    curl -sSLf https://getcomposer.org/installer | \
        php -- --install-dir=/usr/local/bin --filename=composer

RUN set -eux; \
    a2enmod rewrite headers expires

COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY php-overrides.ini /usr/local/etc/php/conf.d/zz-e107-overrides.ini
COPY entrypoint.sh    /usr/local/bin/e107-entrypoint
RUN chmod +x /usr/local/bin/e107-entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/e107-entrypoint"]
CMD ["apache2-foreground"]
