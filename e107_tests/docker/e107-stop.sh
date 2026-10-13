#!/bin/sh
# Stops what e107-start started.
apachectl stop 2>/dev/null
mariadb-admin shutdown 2>/dev/null
true
