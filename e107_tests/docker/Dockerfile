# syntax=docker/dockerfile:1.6
#
# e107 test harness — PHP + Apache image
#
# Build args:
#   PHP_VERSION   Tag of the upstream php:<ver>-apache image (default: 8.5)
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
#   * Supports php:5.6 and php:7.0 (Debian Stretch) too: their apt repos
#     moved to archive.debian.org, and gd/xdebug need version-appropriate
#     builds. Those branches mirror release/v2.3.x CI.

ARG PHP_VERSION=8.5
FROM docker.io/library/php:${PHP_VERSION}-apache

ARG WITH_XDEBUG=no

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# EOL Debian bases (Stretch, for php:5.6/7.0): the stock apt sources 404.
# Rewire them to archive.debian.org and relax the archive's staleness
# constraints. Throwaway test containers; a supported base skips all of this.
RUN set -eux; \
    if ! apt-get update; then \
        for f in /etc/apt/sources.list /etc/apt/sources.list.d/debian.sources; do \
            [ -f "$f" ] || continue; \
            sed -i -e 's|https\?://deb\.debian\.org|http://archive.debian.org|g' \
                   -e 's|https\?://security\.debian\.org|http://archive.debian.org|g' "$f"; \
            sed -i -E '/-updates/d' "$f"; \
        done; \
        printf '%s\n' \
            'Acquire::Check-Valid-Until "false";' \
            'Acquire::AllowInsecureRepositories "true";' \
            'APT::Get::AllowUnauthenticated "true";' \
            > /etc/apt/apt.conf.d/99ignore-obsolete; \
        apt-get update; \
        apt-get -y full-upgrade; \
        apt-get install -y debian-archive-keyring; \
        rm -rf /var/lib/apt/lists/*; \
    fi

RUN set -eux; \
    PHPV=$(php -r 'echo PHP_MAJOR_VERSION * 100 + PHP_MINOR_VERSION;'); \
    apt-get update; \
    if [ "$PHPV" -ge 704 ]; then \
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
    else \
        # Stretch: no mariadb-client package; libonig is a 7.4+ (mbstring)
        # need; libvpx feeds PHP 5.6's gd. Mirrors release/v2.3.x CI.
        apt-get install -y --no-install-recommends \
            ca-certificates \
            curl \
            git \
            imagemagick \
            less \
            libbz2-dev \
            libicu-dev \
            libjpeg-dev \
            libpng-dev \
            libvpx-dev \
            libwebp-dev \
            libzip-dev \
            mysql-client \
            rsync \
            unzip \
            wget \
            zip; \
    fi; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    PHPV=$(php -r 'echo PHP_MAJOR_VERSION * 100 + PHP_MINOR_VERSION;'); \
    if [ "$PHPV" -ge 704 ]; then \
        docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
        docker-php-ext-install -j"$(nproc)" \
            bz2 \
            curl \
            gd \
            intl \
            mbstring \
            mysqli \
            pdo_mysql \
            xml \
            zip; \
    else \
        # Pre-7.4 gd uses the --with-*-dir flag form, and 5.6 predates webp
        # support (vpx instead). curl/mbstring/xml already ship compiled into
        # the old official images.
        if [ "$PHPV" -ge 700 ]; then \
            docker-php-ext-configure gd --with-jpeg-dir=/usr --with-webp-dir=/usr; \
        else \
            docker-php-ext-configure gd --with-jpeg-dir=/usr --with-vpx-dir=/usr; \
        fi; \
        docker-php-ext-install -j"$(nproc)" \
            bz2 \
            gd \
            intl \
            mysqli \
            pdo_mysql \
            zip; \
    fi; \
    # OPcache is a separately-built shared extension up to PHP 8.4 but is
    # compiled into the core as of PHP 8.5, where `docker-php-ext-install
    # opcache` fails outright (there is no shared module to build). Install it
    # only when the runtime doesn't already provide it.
    if ! php -m | grep -qi opcache; then \
        docker-php-ext-install -j"$(nproc)" opcache; \
    fi

RUN set -eux; \
    if [ "$WITH_XDEBUG" = "yes" ]; then \
        PHPV=$(php -r 'echo PHP_MAJOR_VERSION * 100 + PHP_MINOR_VERSION;'); \
        # Last Xdebug release supporting each interpreter, per e107 CI.
        if [ "$PHPV" -ge 800 ]; then \
            pecl install xdebug; \
        elif [ "$PHPV" -ge 702 ]; then \
            pecl install xdebug-3.1.6; \
        elif [ "$PHPV" -ge 701 ]; then \
            pecl install xdebug-2.9.8; \
        elif [ "$PHPV" -ge 700 ]; then \
            pecl install xdebug-2.7.2; \
        else \
            pecl install xdebug-2.5.5; \
        fi; \
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
