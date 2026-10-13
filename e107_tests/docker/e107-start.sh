#!/bin/sh
# Starts the bundled database and Apache, there being no init system in the
# container. Safe to run again. `e107-start db` starts the database alone.
set -eu

mkdir -p /run/mysqld
chown mysql:mysql /run/mysqld
exec 9> /run/mysqld/e107-start.lock
flock 9
if ! mariadb-admin ping --silent >/dev/null 2>&1; then
    nohup mariadbd >/dev/null 2>&1 9>&- &
    tries=0
    until mariadb-admin ping --silent >/dev/null 2>&1; do
        tries=$((tries + 1))
        if [ "$tries" -ge 120 ]; then
            echo "e107-start: the database did not start; see /var/log/mysql/error.log" >&2
            exit 1
        fi
        sleep 0.5
    done
fi
exec 9>&-

if [ "${1:-}" != db ]; then
    apachectl start
fi
