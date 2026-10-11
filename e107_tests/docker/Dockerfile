#
# e107 test harness — PHP + Apache image
#
# Two stages, both built from the repository root:
#   harness   What e107-tests and CI run: no source code inside, the
#             worktree is bind-mounted, and the database is a separate
#             container.
#   scanner   The default. A self-contained site: the checkout at /src,
#             installed at build time against a bundled MariaDB, so it runs
#             with no network. See "Self-contained mode" in README.md.
#
# Build args:
#   PHP_VERSION       Tag of the upstream php:<ver>-apache image
#   WITH_XDEBUG       "yes" to install and enable Xdebug for code coverage
#   MARIADB_VERSION   scanner: MariaDB release bundled, with its tarball's
#   MARIADB_SHA256    published SHA-256; the two move together
#
# Examples, from the repository root:
#   docker build --build-arg PHP_VERSION=8.4 --build-arg WITH_XDEBUG=yes \
#       --target harness -f e107_tests/docker/Dockerfile \
#       -t "$(e107_tests/bin/e107-tests image --php 8.4 --xdebug)" .
#   docker build -f e107_tests/docker/Dockerfile -t e107 .
#
# Notes:
#   * The image installs every PHP extension e107 needs to install and run.
#   * Composer, git, rsync, and unzip are baked in so tests can be driven
#     end-to-end inside the container.
#   * nftables fences the container off the Internet while a suite runs
#     (lib/sandbox/Boundary.php).
#   * The harness stage COPYs nothing but files from e107_tests/docker/, so
#     the image tag e107-tests derives from that directory covers all of it.
#   * An image whose Debian release has moved to archive.debian.org gets its
#     apt sources moved there too (debian-archive.sh), so it keeps building
#     after its base leaves support. gd/xdebug need version-appropriate
#     builds on old PHP; those branches mirror release/v2.3.x CI.

ARG PHP_VERSION=5.6
FROM docker.io/library/php:${PHP_VERSION}-apache AS harness

ARG WITH_XDEBUG=no

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

COPY e107_tests/docker/debian-archive.sh /usr/local/bin/e107-debian-archive
RUN chmod +x /usr/local/bin/e107-debian-archive && /usr/local/bin/e107-debian-archive

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
            locales \
            mariadb-client \
            nftables \
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
            locales \
            mysql-client \
            nftables \
            rsync \
            unzip \
            wget \
            zip; \
    fi; \
    localedef -i tr_TR -f UTF-8 tr_TR.UTF-8; \
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

# How many sandboxes (sbN.web) the image serves: compose.yml names that many
# aliases of the web container, and the suite runner asks for no more of them.
ENV E107_SANDBOXES=16

# TLS as well as plain HTTP, because some behaviour only exists over one of them.
#
# The Fetch Metadata headers a browser sends are appended only when the request
# URL is a potentially trustworthy URL, so a browser sends Sec-Fetch-Site over
# HTTPS and never over plain HTTP to a name like 'web'. Anything asserting what
# the browser-only CSRF modes do therefore has to be served over HTTPS, and
# anything asserting that a plain-HTTP site stays usable has to be served over
# HTTP. Both vhosts point at the same document root, so a suite chooses by URL.
#
# Self-signed and generated at build time. mod_ssl's ports.conf adds Listen 443
# on its own once the module is enabled.
RUN set -eux; \
    a2enmod rewrite headers expires ssl vhost_alias; \
    mkdir -p /etc/ssl/e107; \
    # The SAN goes through a config file rather than -addext, which needs
    # OpenSSL 1.1.1. The php:5.6 and php:7.0 images are Debian Stretch with
    # 1.1.0, where -addext is parsed as a digest name and the build dies with
    # "req: Unknown digest addext". The unit matrix starts at 5.6 and `graft`
    # builds old release tags from this Dockerfile too, so both reach this
    # line. A config file works on every version. The sandboxes' names
    # (compose.yml's sbN.web aliases) are listed one by one: TLS clients take
    # no wildcard over a single label, so *.web would match none of them.
    printf '%s\n' \
        '[req]' \
        'distinguished_name = dn' \
        'x509_extensions = v3' \
        'prompt = no' \
        '[dn]' \
        'CN = web' \
        '[v3]' \
        "subjectAltName = DNS:web,$(seq -s, -f 'DNS:sb%g.web' "$E107_SANDBOXES"),DNS:localhost,IP:127.0.0.1" \
        > /tmp/e107-ssl.cnf; \
    openssl req -x509 -nodes -newkey rsa:2048 -days 3650 \
        -config /tmp/e107-ssl.cnf \
        -keyout /etc/ssl/e107/web.key \
        -out    /etc/ssl/e107/web.crt; \
    rm -f /tmp/e107-ssl.cnf; \
    chmod 640 /etc/ssl/e107/web.key

# Each sandbox keeps its PHP sessions apart, in a directory the runner empties
# with the sandbox (lib/sandbox/Overlay.php), so a test counting session files
# sees only its own site's: one block per sandbox compose.yml names.
RUN for n in $(seq "$E107_SANDBOXES"); do \
        printf '<Directory /srv/sb/sb%s>\n    php_admin_value session.save_path /srv/sbstate/sessions/sb%s\n</Directory>\n' "$n" "$n"; \
    done > /etc/apache2/e107-sandbox-sessions.conf

COPY e107_tests/docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY e107_tests/docker/php-overrides.ini /usr/local/etc/php/conf.d/zz-e107-overrides.ini
COPY e107_tests/docker/entrypoint.sh    /usr/local/bin/e107-entrypoint
RUN chmod +x /usr/local/bin/e107-entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/e107-entrypoint"]
CMD ["apache2-foreground"]

FROM harness AS scanner

# MariaDB's own generic build rather than the PHP image's Debian package, which
# on the oldest PHP images is a server far behind the maintained release lines.
ARG MARIADB_VERSION=11.4.13
ARG MARIADB_SHA256=3cebf63914df3154b7cb6d548337eb2be42c773aeb641110446b72e2746cfed7

ENV PATH=/usr/local/mysql/bin:$PATH \
    E107_DOCROOT=/src \
    E107_DB_HOST=127.0.0.1 \
    E107_DB_PORT=3306 \
    E107_DB_NAME=e107 \
    E107_DB_USER=e107 \
    E107_DB_PASSWORD=e107

COPY e107_tests/docker/mariadb.cnf /etc/my.cnf

RUN set -eux; \
    curl -fsSL -o /tmp/mariadb.tar.gz \
        "https://archive.mariadb.org/mariadb-${MARIADB_VERSION}/bintar-linux-systemd-x86_64/mariadb-${MARIADB_VERSION}-linux-systemd-x86_64.tar.gz"; \
    echo "${MARIADB_SHA256}  /tmp/mariadb.tar.gz" | sha256sum -c -; \
    mkdir /usr/local/mysql; \
    tar -xzf /tmp/mariadb.tar.gz -C /usr/local/mysql --strip-components=1 \
        --exclude='*/mariadb-test' --exclude='*/mysql-test' --exclude='*/sql-bench' \
        --exclude='*/include' --exclude='*/man'; \
    rm /tmp/mariadb.tar.gz; \
    getent passwd mysql >/dev/null || useradd --system --user-group --home-dir /var/lib/mysql --shell /usr/sbin/nologin mysql; \
    mkdir -p /var/lib/mysql /var/log/mysql /run/mysqld; \
    chown mysql:mysql /var/lib/mysql /var/log/mysql /run/mysqld; \
    cd /usr/local/mysql; \
    ./scripts/mariadb-install-db --user=mysql --skip-test-db; \
    mariadbd --version

COPY e107_tests/docker/e107-start.sh /usr/local/bin/e107-start
COPY e107_tests/docker/e107-stop.sh  /usr/local/bin/e107-stop
COPY e107_tests/docker/e107-unit.sh  /usr/local/bin/e107-unit
COPY e107_tests/docker/sendmail.sh   /usr/local/bin/e107-sendmail

# Apache serves the checkout, logging to files because e107-start runs it as a
# daemon. PHP logs errors rather than showing them, as on a production host.
RUN set -eux; \
    chmod +x /usr/local/bin/e107-start /usr/local/bin/e107-stop /usr/local/bin/e107-unit /usr/local/bin/e107-sendmail; \
    sed -i "s#/var/www/html#${E107_DOCROOT}#g" /etc/apache2/sites-available/000-default.conf; \
    : > /etc/apache2/e107-base-path.conf; \
    printf 'ServerName 127.0.0.1\n' > /etc/apache2/conf-available/e107-server-name.conf; \
    a2enconf e107-server-name; \
    rm -f /var/log/apache2/*.log; \
    mkdir -p /var/mail/e107; \
    chown www-data:www-data /var/mail/e107; \
    printf '%s\n' \
        'display_errors = Off' \
        'display_startup_errors = Off' \
        'sendmail_path = /usr/local/bin/e107-sendmail' \
        > /usr/local/etc/php/conf.d/zz-e107-self-contained.ini

# The suite's dependencies go outside the checkout, so that it stays as cloned.
COPY e107_tests/composer.json e107_tests/composer*.lock e107_tests/docker/composer-install.sh /opt/e107-tests/
RUN cd /opt/e107-tests && sh composer-install.sh composer.lock $(ls composer.php*.lock | sort -Vr)

COPY . /src
WORKDIR /src

RUN sh e107_tests/docker/install-site.sh

RUN sh e107_tests/docker/smoke-test.sh

RUN if [ -d .git/info ]; then printf '.oss-scanner/\n' >> .git/info/exclude; fi
