#!/bin/sh
# Checks at the end of the build that the site works as README.md describes. A
# failed check is reported, not fatal: the rest of the image is still of use.
set -u

check() {
    label=$1
    shift
    if "$@"; then
        echo "smoke-test: ok: $label"
    else
        echo "WARNING: smoke-test: $label failed" >&2
    fi
}

answers() {
    [ "$(curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1$1")" = "$2" ]
}

admin_signs_in() {
    jar=$(mktemp)
    curl -s -c "$jar" -b "$jar" -o /dev/null http://127.0.0.1/e107_admin/admin.php
    curl -sL -c "$jar" -b "$jar" -H 'Sec-Fetch-Site: same-origin' \
        -d authname=admin -d authpass=x107 -d authsubmit=1 \
        http://127.0.0.1/e107_admin/admin.php | grep -q "s Control Panel"
}

e107-start
check "the login page answers 200" answers /login.php 200
check "e107_system/ answers 403" answers /e107_system/ 403
check "admin signs in" admin_signs_in
check "a unit test file passes" e107-unit tests/unit/UserHandlerTest.php
e107-stop
