#!/usr/bin/env bash
# Diagnostic only, not for merging: logs every request each cell's Apache serves,
# with the X-Diag-* headers e107 adds on this branch, and collects the logs.
#   diag.sh arm
#   diag.sh collect <dir>
set -uo pipefail
root=${RUNNER_TEMP:-/tmp}/e107-cells
list=$root/cells.tsv
case ${1:?} in
arm)
    while IFS=$'\t' read -r id label tree _; do
        "$tree/e107_tests/bin/e107-tests" exec sh -c 'cat > /etc/apache2/conf-enabled/zz-diag.conf <<"CONF"
LogFormat "%{%s}t.%{usec_frac}t %D %P k=%k %{Host}i \"%r\" %>s %B c=\"%{Cookie}i\" sc=\"%{Set-Cookie}o\" loc=\"%{Location}o\" db=%{X-Diag-Db}o st=\"%{X-Diag-Start}o\" rd=\"%{X-Diag-Read}o\" v=\"%{X-Diag-Validate}o\" u=\"%{X-Diag-User}o\" lg=\"%{X-Diag-Login}o\" rg=\"%{X-Diag-Regen}o\"" diag
GlobalLog /tmp/diag-access.log diag
CONF
apache2ctl -t && apache2ctl graceful' < /dev/null || echo "arm failed for $id"
    done < "$list"
    ;;
collect)
    dest=${2:?}
    mkdir -p "$dest"
    while IFS=$'\t' read -r id label tree _; do
        "$tree/e107_tests/bin/e107-tests" exec cat /tmp/diag-access.log > "$dest/$id.access.log" 2>/dev/null < /dev/null || true
        "$tree/e107_tests/bin/e107-tests" exec sh -c 'cat /proc/loadavg; apache2ctl -V | head -3' > "$dest/$id.server.txt" 2>&1 < /dev/null || true
        cp -a "$root/$id".*.log "$dest/" 2>/dev/null || true
    done < "$list"
    ;;
esac
