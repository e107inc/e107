#!/bin/sh
# Installs the self-contained image's site at build time: the database, the
# real unattended installer, every other bundled plugin, and the accounts and
# hosts README.md documents.
set -eu
cd "$E107_DOCROOT"

e107-entrypoint true
e107-start

mariadb <<SQL
CREATE DATABASE \`$E107_DB_NAME\`;
CREATE DATABASE \`${E107_DB_NAME}_test\`;
CREATE USER '$E107_DB_USER'@'%' IDENTIFIED BY '$E107_DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`$E107_DB_NAME\`.* TO '$E107_DB_USER'@'%';
GRANT ALL PRIVILEGES ON \`${E107_DB_NAME}_test\`.* TO '$E107_DB_USER'@'%';
SQL

sh e107_tests/docker/render-config.sh "$E107_DB_HOST" "$E107_DB_USER" "$E107_DB_PASSWORD" \
    "$E107_DB_NAME" e107_ "$(printf '%s' "$E107_DB_NAME.e107_" | md5sum | cut -c1-10)" > e107_config.php

curl -fsS -o /dev/null -G http://127.0.0.1/install.php \
    -d create_tables=1 \
    --data-urlencode "username=$E107_DB_USER" \
    --data-urlencode "password=$E107_DB_PASSWORD" \
    -d admin_user=admin \
    -d admin_password=x107 \
    --data-urlencode admin_email=admin@example.com \
    -d sitename=e107
installed=$(mariadb -N -e "SELECT COUNT(*) FROM \`$E107_DB_NAME\`.e107_core WHERE e107_name = 'SitePrefs'")
if [ "$installed" != 1 ]; then
    echo "install-site: the unattended installer left no site; see e107_system/*/logs" >&2
    exit 1
fi

# The installer renames the tracked e107.htaccess, which git would report as deleted.
[ -f e107.htaccess ] || cp -p .htaccess e107.htaccess

as_apache() {
    su -s /bin/sh -c "php e107_tests/docker/install-site.php $*" www-data
}
for xml in e107_plugins/*/plugin.xml; do
    as_apache plugin "$(basename "$(dirname "$xml")")" || echo "WARNING: install-site: $xml did not install" >&2
done
as_apache finish

e107-stop
