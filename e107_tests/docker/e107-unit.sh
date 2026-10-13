#!/bin/sh
# Runs `codecept run unit` with these arguments against the bundled database, on
# a copy of the checkout, so the suite's e107_config.php never replaces the site's.
set -eu

work=/var/lib/e107-unit
e107-start db
mkdir -p "$work"
rsync -a --delete \
    --exclude=/.git \
    --exclude=/e107_config.php \
    --exclude=/e107_tests/vendor \
    --exclude=/e107_tests/tests/_output \
    "${E107_DOCROOT:-/src}/" "$work/"
ln -sfn /opt/e107-tests/vendor "$work/e107_tests/vendor"

E107_TEST_PARAMS=$(php -r 'echo json_encode(array(
    "url" => "http://127.0.0.1/",
    "db"  => array(
        "host"     => getenv("E107_DB_HOST"),
        "port"     => getenv("E107_DB_PORT"),
        "dbname"   => getenv("E107_DB_NAME")."_test",
        "user"     => getenv("E107_DB_USER"),
        "password" => getenv("E107_DB_PASSWORD"),
    ),
));')
export E107_TEST_PARAMS

cd "$work/e107_tests"
exec php -d register_argc_argv=1 vendor/bin/codecept run unit "$@"
