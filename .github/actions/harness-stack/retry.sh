# Three tries with a growing pause, for a step that fails while an upstream
# host is stumbling and succeeds once it is back.
retry() {
    local attempt
    for attempt in 1 2 3; do
        "$@" && return 0
        [ "$attempt" -lt 3 ] || return 1
        echo "::warning::$1 failed (attempt $attempt of 3); retrying in $((attempt * 30))s"
        sleep $((attempt * 30))
    done
}
