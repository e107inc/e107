#!/usr/bin/env bash
# Pulls Docker Hub images through mirror.gcr.io, tagged with the names they
# were asked for so compose finds them locally, with Docker Hub itself as the
# fallback. An image from any other registry, or pinned by digest, is pulled
# from where it was named.
#
#   pull.sh <image>...
set -uo pipefail
. "$(dirname -- "$(readlink -f -- "$0")")/retry.sh"

pull_one() {
    local img=$1 path first
    docker image inspect "$img" >/dev/null 2>&1 && return 0
    path=${img#docker.io/}
    first=${path%%/*}
    if [[ $img == *@* ]] || { [ "$first" != "$path" ] && { [ "$first" = localhost ] || [[ $first == *[.:]* ]]; }; }; then
        retry docker pull -q "$img"
        return
    fi
    [[ $path == */* ]] || path=library/$path
    if docker pull -q "mirror.gcr.io/$path"; then
        docker tag "mirror.gcr.io/$path" "docker.io/$path"
        return
    fi
    echo "::warning::mirror.gcr.io did not supply $img; pulling it from Docker Hub"
    retry docker pull -q "$img"
}

rc=0
for img in "$@"; do
    pull_one "$img" || rc=1
done
exit $rc
