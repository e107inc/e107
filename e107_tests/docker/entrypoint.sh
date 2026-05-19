#!/bin/sh
# e107 test harness — container entrypoint
#
# Responsibilities:
#   1. Wait for the database to accept connections (if E107_DB_HOST is set).
#   2. Make sure www-data (Apache + PHP) can write to the directories e107
#      writes to at runtime, regardless of who owns the bind-mounted worktree.
#   3. Hand off to the upstream php-apache CMD.
#
# We deliberately do NOT `chown` anything on the bind mount: under rootless
# Podman, chown silently remaps host ownership into the subuid range, which
# corrupts the worktree from the host's perspective. `chmod`, by contrast,
# only changes file modes — ownership stays put. We grant write to "other"
# so it works for any container UID, accepting that this is a test env.

set -eu

if [ -n "${E107_DB_HOST:-}" ]; then
    echo "[entrypoint] Waiting for database at ${E107_DB_HOST}:${E107_DB_PORT:-3306}..."
    attempts=60
    i=0
    while [ "$i" -lt "$attempts" ]; do
        # --skip-ssl: recent mariadb-client defaults to requiring TLS, but
        # we're talking over the compose bridge network and the test server
        # doesn't enable TLS by default. Internal-only, so this is safe.
        if mariadb-admin ping \
                --host="${E107_DB_HOST}" \
                --port="${E107_DB_PORT:-3306}" \
                --user="${E107_DB_USER:-root}" \
                --password="${E107_DB_PASSWORD:-}" \
                --skip-ssl \
                --silent 2>/dev/null; then
            echo "[entrypoint] Database is up."
            break
        fi
        i=$((i + 1))
        sleep 1
    done
    if [ "$i" -ge "$attempts" ]; then
        echo "[entrypoint] WARNING: database never responded; continuing anyway." >&2
    fi
fi

if [ -d /var/www/html ]; then
    # The docroot itself must be writable: the acceptance test deployer
    # creates and deletes /var/www/html/e107_config.php directly.
    chmod a+rwx /var/www/html 2>/dev/null || true

    # Directories e107 writes to at runtime (hash dirs under e107_system /
    # e107_media, media uploads, theme/plugin installs). Capital X only
    # adds execute on dirs and already-executable files, which is what
    # we want for traversal.
    for dir in e107_media e107_system e107_languages e107_themes e107_plugins; do
        if [ -d "/var/www/html/$dir" ]; then
            chmod -R a+rwX "/var/www/html/$dir" 2>/dev/null || true
        fi
    done
fi

# Trust git inside the container even though the bind-mounted .git is owned
# by the host UID. Lets `git -C /var/www/html ...` invocations from
# Codeception helpers like PreparerFactory work.
git config --system --add safe.directory '*' 2>/dev/null || true

exec "$@"
