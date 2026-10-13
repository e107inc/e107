#!/bin/sh
# Installs the test suite's dependencies into ./vendor from the first committed
# lock, of those named, that this PHP can take; run from a directory holding
# composer.json and the locks. Used by e107-tests and the self-contained image.
set -eu

if [ ! -f composer.json ]; then
    echo ">> no e107_tests/composer.json in this tree; skipping composer (old-tag mode)" >&2
    exit 0
fi
META=vendor/.e107-meta
mkdir -p "$META/locks"
# Composer 2.2, the line PHP 5.6 and 7.0 get, fails when another install
# makes a directory in the cache under it, so installs take turns on it.
LOCKFILE=/root/.composer/cache/.e107-install.lock
touch "$LOCKFILE" 2>/dev/null || LOCKFILE=$META/.lock
exec 9> "$LOCKFILE"
flock -w 900 9
cp -f composer.json "$META/composer.json"
rm -f "$META"/locks/* "$META/composer.lock"
for lock in "$@"; do
    if [ -f "$lock" ]; then
        cp -f "$lock" "$META/locks/$lock"
    fi
done
# Hash the frozen copies now: the stamp must describe what this
# install ran against, even if the worktree changes mid-install.
JSON_SHA=$(sha256sum "$META/composer.json" | cut -d" " -f1)
LOCK_SHA=$(cd "$META/locks" && cat "$@" 2>/dev/null | sha256sum | cut -d" " -f1)
export COMPOSER="$PWD/$META/composer.json"
export COMPOSER_VENDOR_DIR="$PWD/vendor"
PHPVER=$(php -r "echo PHP_MAJOR_VERSION.\".\".PHP_MINOR_VERSION;")
LOCK=
for lock in "$@"; do
    [ -f "$META/locks/$lock" ] || continue
    cp -f "$META/locks/$lock" "$META/composer.lock"
    if composer check-platform-reqs --lock >/dev/null 2>&1; then
        LOCK=$lock
        break
    fi
done
[ -n "$LOCK" ] || rm -f "$META/composer.lock"
[ "$LOCK" = composer.lock ] || composer config --unset platform.php
if [ -n "$LOCK" ]; then
    echo ">> installing from $LOCK" >&2
    composer install --prefer-dist --no-progress
    MODE=install
else
    echo "!! no committed lock ($*) installs on PHP $PHPVER; falling back to \"composer update\" (needs the network; run e107-tests relock after adding composer.php$PHPVER.lock)" >&2
    composer update --prefer-dist --no-progress
    MODE=update
fi
{
    echo "E107_STAMP_JSON_SHA256=$JSON_SHA"
    echo "E107_STAMP_LOCK_SHA256=$LOCK_SHA"
    echo "E107_STAMP_PHP=$PHPVER"
    echo "E107_STAMP_MODE=$MODE"
    echo "E107_STAMP_LOCK=$LOCK"
} > "$META/stamp.env"
