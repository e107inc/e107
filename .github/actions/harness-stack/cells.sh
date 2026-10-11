#!/usr/bin/env bash
# Harness envs on one runner, brought up and run at once: one per cell.
#
#   cells.sh plan --php X [--db IMAGE] [<e107-tests up flags>...] < cells
#   cells.sh images
#   cells.sh up
#   cells.sh run <e107-tests command> [args...]
#   cells.sh collect <dir>
#
# A cell is one line, `[name] [up flags...] [-- run flags...]`, split on
# whitespace; a line that starts with a flag has no name. No lines means one
# cell in the checkout itself. Every other cell runs in its own copy of the
# checkout under $RUNNER_TEMP/e107-cells, because the suites write into the
# app root and two runs sharing a tree delete each other's state; a separate
# path is all the harness needs to give a cell its own compose project,
# config and vendor volume. A cell is named "PHP X, <db>[, <name>]" in its log
# group, its annotation and its row of the job summary.
#
# A cell reproduces locally with the usual two commands, its up flags on the
# first and its run flags on the second:
#   e107_tests/bin/e107-tests up --php X --db Y [up flags]
#   e107_tests/bin/e107-tests <command> [args] [run flags]
set -euo pipefail

src=${GITHUB_WORKSPACE:-$(git rev-parse --show-toplevel)}
root=${RUNNER_TEMP:-/tmp}/e107-cells
list=$root/cells.tsv
summary=${GITHUB_STEP_SUMMARY:-/dev/null}
. "$(dirname -- "$(readlink -f -- "$0")")/retry.sh"

slug() { printf '%s' "$1" | tr -c 'A-Za-z0-9\n' '-'; }
strip() { sed 's/\x1b\[[0-9;]*m//g' "$@"; }
# The numbered entries of Codeception's failure and error lists.
failures() {
    awk '/^There (was|were) [0-9]+ (failure|error)s?:/ { on = 1; next }
         /^There (was|were) / || /^(FAILURES|ERRORS)!/ { on = 0 }
         on && /^[0-9]+\) / { print }'
}
# Workflow commands: a message escapes %, CR and LF, a property also , and :
esc_data() { local s=${1//%/%25}; s=${s//$'\r'/%0D}; printf '%s' "${s//$'\n'/%0A}"; }
esc_prop() { local s; s=$(esc_data "$1"); s=${s//:/%3A}; printf '%s' "${s//,/%2C}"; }

# Reads the cell list into one row per cell: id, label, tree, database, and
# its up and run flags quoted for the shell, so an empty value such as
# --base-path '' holds.
plan() {
    local php='' db='' common=() line name up run words w i id tree cell_db dbflag sep
    while [ $# -gt 0 ]; do
        case $1 in
            --php) php=$2; shift 2 ;;
            --db) db=$2; shift 2 ;;
            *) common+=("$1"); shift ;;
        esac
    done
    [ -n "$php" ] || { echo "cells.sh plan needs --php" >&2; exit 2; }
    mkdir -p "$root"
    : > "$list"
    local lines=()
    while IFS= read -r line; do
        [ -n "${line//[[:space:]]/}" ] && lines+=("$line")
    done
    [ ${#lines[@]} -gt 0 ] || lines=('')
    for line in "${lines[@]}"; do
        read -ra words <<< "$line"
        name='' up=() run=()
        if [ ${#words[@]} -gt 0 ] && [ "${words[0]#-}" = "${words[0]}" ]; then
            name=${words[0]}
            words=("${words[@]:1}")
        fi
        sep=no
        for w in ${words[@]+"${words[@]}"}; do
            if [ "$sep" = no ] && [ "$w" = -- ]; then sep=yes
            elif [ "$sep" = no ]; then up+=("$w")
            else run+=("$w"); fi
        done
        cell_db=$db dbflag=(--db "$db")
        for ((i = 0; i < ${#up[@]}; i++)); do
            [ "${up[i]}" != --db ] || { cell_db=${up[i + 1]:-} dbflag=(); }
        done
        [ -n "$cell_db" ] || { echo "cells.sh plan: cell '$line' has no database" >&2; exit 2; }
        id=$(slug "${name:-$cell_db}")
        tree=$root/$id
        [ ${#lines[@]} -gt 1 ] || [ -n "$line" ] || tree=$src
        up=(--php "$php" ${dbflag[@]+"${dbflag[@]}"} ${common[@]+"${common[@]}"} ${up[@]+"${up[@]}"})
        printf '%s\t%s\t%s\t%s\t%s\t%s\n' "$id" "PHP $php, $cell_db${name:+, $name}" "$tree" "$cell_db" \
            "$(printf '%q ' "${up[@]}")" \
            "$([ ${#run[@]} -eq 0 ] || printf '%q ' "${run[@]}")" >> "$list"
    done
}

# Runs "$@" once per cell with $label, $tree and its flags set, all at once,
# each with its output in a file, then reports every cell in list order: a
# log group, an annotation naming each failed one, and a row of the job
# summary for every cell after a run.
fan_out() {
    local phase=$1 pids=() rc=0 id label tree upf runf log s t0 took tests failed result note
    shift
    t0=$(date +%s)
    while IFS=$'\t' read -r id label tree _ upf runf; do
        log=$root/$id.$phase
        ( s=0
          if [ "$phase" != up ] && [ "$(cat "$root/$id.up.rc")" != 0 ]; then
              echo "not run: this cell's env did not come up" > "$log.log"; s=skipped
          else
              "$@" > "$log.log" 2>&1 || s=$?
          fi
          echo $s > "$log.rc"; date +%s > "$log.end" ) < /dev/null &
        pids+=($!)
    done < "$list"
    for p in "${pids[@]}"; do wait "$p" || true; done
    [ "$phase" = up ] || printf '| Cell | Result | Time | Tests |\n|---|---|---|---|\n' >> "$summary"
    while IFS=$'\t' read -r id label tree _ upf runf; do
        log=$root/$id.$phase
        s=$(cat "$log.rc")
        took=$(( $(cat "$log.end") - t0 ))
        case $s in
            0) result=passed note="passed in ${took}s" ;;
            skipped) result='not run' note='not run, as its env did not come up' ;;
            124) result='timed out' note="TIMED OUT (exit 124) in ${took}s" ;;
            *) result=failed note="FAILED (exit $s) in ${took}s" ;;
        esac
        echo "::group::$(esc_data "$label"): $phase $note"
        cat "$log.log"
        echo "::endgroup::"
        if [ "$phase" != up ]; then
            tests=$(strip "$log.log" | grep -aE '^(OK \(|Tests: )' | tail -n 1 || true)
            printf '| %s | %s | %ss | %s |\n' "$label" \
                "$([ "$s" = 0 ] && echo "$result" || echo "**$result**")" "$took" "$tests" >> "$summary"
        fi
        [ "$s" != 0 ] || continue
        rc=1
        if [ "$s" != skipped ]; then
            failed=$(strip "$log.log" | failures | head -n 5 || true)
            echo "::error title=$(esc_prop "$label")::$(esc_data "$phase $result (exit $s); the log is the group of that name in this step.${failed:+
$failed}")"
        fi
    done < "$list"
    return $rc
}

up_cell() {
    if [ "$tree" != "$src" ]; then
        mkdir -p "$tree"
        tar -C "$src" --exclude=./.git --exclude=./e107_tests/config.docker.yml -cf - . | tar -C "$tree" -xf - || return
    fi
    eval "set -- $upf"
    retry "$tree/e107_tests/bin/e107-tests" up "$@"
}

run_cell() {
    eval "set -- \"\$@\" $runf"
    timeout -k 60 1800 "$tree/e107_tests/bin/e107-tests" "$@"
}

cmd=${1:?usage: cells.sh plan|images|up|run|collect}
shift
case $cmd in
plan)
    plan "$@"
    ;;
images)
    cut -f4 "$list" | sort -u
    ;;
up)
    fan_out up up_cell
    ;;
run)
    fan_out run run_cell "$@"
    ;;
collect)
    dest=${1:?usage: cells.sh collect <dir>}
    while IFS=$'\t' read -r id label tree _ upf runf; do
        log=$root/$id
        [ "$(cat "$log".*.rc 2>/dev/null | sort -u | tr -d '\n')" != 0 ] || continue
        d=$dest/$id
        mkdir -p "$d"
        cp -a "$tree/e107_tests/tests/_output" "$d/" 2>/dev/null || true
        cp -a "$log".*.log "$d/" 2>/dev/null || true
        "$tree/e107_tests/bin/e107-tests" logs > "$d/container.log" 2>&1 || true
    done < "$list"
    ;;
*)
    echo "unknown command: $cmd" >&2
    exit 2
    ;;
esac
