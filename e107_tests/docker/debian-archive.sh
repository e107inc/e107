#!/bin/sh
#
# Moves apt to archive.debian.org once the image's Debian release is served
# there, keeping only the suites the archive has. A release the archive does
# not know is left alone, and so is one the archive could not be asked about.
#
# Reads both apt source formats (one-line sources.list and deb822 *.sources)
# and replaces them with a one-line /etc/apt/sources.list. apt authentication
# is relaxed only when apt reports the archive's repository as not signed.
set -eu

ARCHIVE=http://archive.debian.org
APT_CONF=/etc/apt/apt.conf.d/99debian-archive

die() {
    echo "e107-debian-archive: $*" >&2
    exit 1
}

configured_sources() {
    for f in /etc/apt/sources.list /etc/apt/sources.list.d/*.list; do
        [ -f "$f" ] && sed -nE 's/^deb[[:space:]]+(\[[^]]*\][[:space:]]*)?/deb /p' "$f" || true
    done
    for f in /etc/apt/sources.list.d/*.sources; do
        [ -f "$f" ] || continue
        awk '
            function flush(    u, s, nu, ns, i, j) {
                if (tolower(v["enabled"]) !~ /^(no|false|0|off|disable|without)$/ \
                    && v["types"] ~ /(^|[ \t])deb([ \t]|$)/ && v["uris"] != "" && v["suites"] != "") {
                    nu = split(v["uris"], u, /[ \t]+/)
                    ns = split(v["suites"], s, /[ \t]+/)
                    for (i = 1; i <= nu; i++)
                        for (j = 1; j <= ns; j++)
                            print "deb", u[i], s[j], v["components"]
                }
                split("", v)
                field = ""
            }
            /^#/           { next }
            /^[ \t]*$/     { flush(); next }
            /^[ \t]/       { sub(/^[ \t]+/, ""); sub(/[ \t]+$/, "")
                             if ($0 != ".") v[field] = (v[field] == "" ? $0 : v[field] " " $0)
                             next }
            /^[A-Za-z-]+:/ { field = tolower($0); sub(/:.*/, "", field)
                             sub(/^[A-Za-z-]+:[ \t]*/, ""); sub(/[ \t]+$/, ""); v[field] = $0 }
            END            { flush() }
        ' "$f"
    done
}

sources=$(configured_sources)
[ -n "$sources" ] || die "no apt sources found under /etc/apt"

archived=''
unanswered=''
while read -r _ uri suite components; do
    path=${uri#*://}
    uri=$ARCHIVE/${path#*/}
    code=$(curl -sSL -o /dev/null -w '%{http_code}' --connect-timeout 15 --max-time 60 --retry 2 \
        "$uri/dists/$suite/Release") || true
    case $code in
        200) archived="${archived}deb $uri $suite $components
" ;;
        404) ;;
        *) unanswered="$unanswered${unanswered:+,} $suite (HTTP $code)" ;;
    esac
done <<SOURCES
$sources
SOURCES

if [ -z "$archived" ]; then
    if [ -n "$unanswered" ]; then
        echo "apt sources left alone: $ARCHIVE could not be asked about$unanswered" >&2
    else
        echo "apt sources left alone: $ARCHIVE does not serve this release"
    fi
    exit 0
fi
[ -z "$unanswered" ] || die "$ARCHIVE serves this release but could not be asked about$unanswered"

rm -f /etc/apt/sources.list /etc/apt/sources.list.d/*.list /etc/apt/sources.list.d/*.sources
printf '%s' "$archived" | awk '!seen[$0]++' > /etc/apt/sources.list
echo 'Acquire::Check-Valid-Until "false";' > "$APT_CONF"
relaxed=''
if ! update=$(apt-get -o Acquire::AllowInsecureRepositories=false update 2>&1); then
    printf '%s\n' "$update"
    case $update in
        *' is not signed'*) ;;
        *) die "apt-get update failed against the archive sources" ;;
    esac
    printf '%s\n' 'Acquire::AllowInsecureRepositories "true";' 'APT::Get::AllowUnauthenticated "true";' >> "$APT_CONF"
    apt-get update
    relaxed=", with apt authentication relaxed because the archive's repository is not signed"
fi
rm -rf /var/lib/apt/lists/*
echo "apt sources moved to $ARCHIVE$relaxed:"
cat /etc/apt/sources.list
