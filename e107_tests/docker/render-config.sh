#!/bin/sh
# Prints an e107_config.php for one database, from the test suite's template.
# Usage: render-config.sh <server> <user> <password> <database> <prefix> <site folder>
set -eu
sed -e "s/{{ mySQLserver }}/$1/" \
    -e "s/{{ mySQLuser }}/$2/" \
    -e "s/{{ mySQLpassword }}/$3/" \
    -e "s/{{ mySQLdefaultdb }}/$4/" \
    -e "s/{{ mySQLprefix }}/$5/" \
    -e "s/000000test/$6/" \
    "$(dirname "$0")/../tests/_data/e107_config.php.sample"
