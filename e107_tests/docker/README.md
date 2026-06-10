# Docker test environments

A self-contained way to spin up isolated, per-worktree LAMP stacks so you (or
an agent) can verify changes against a specific PHP + MySQL/MariaDB combo
without contending for a shared dev VM.

## Why this exists

The legacy flow assumed one developer with one configured VM (described by
`e107_tests/config.yml`). That model breaks down as soon as more than one
parallel worker — human or AI — wants to run tests at the same time, or
wants to try a different PHP/DB version. This harness gives every worktree
its own short-lived stack, ready in under a minute and torn down with one
command.

## Requirements

- Docker (or Podman with `podman-docker` so the `docker` CLI works) — version
  with the `docker compose` plugin.
- A worktree of this repo. The worktree itself is bind-mounted into the
  container; your edits are live immediately.

## Quickstart

```sh
# Default: PHP 8.3 + MariaDB 10.11
e107_tests/bin/e107-tests up

# See where the running site lives, plus DB credentials
e107_tests/bin/e107-tests urls

# Run tests
e107_tests/bin/e107-tests run unit
e107_tests/bin/e107-tests run acceptance

# When you're done
e107_tests/bin/e107-tests down
```

`e107-tests help` is the canonical command reference.

## Choosing a matrix combo

```sh
e107_tests/bin/e107-tests up --php 8.4 --db mysql:8.0
e107_tests/bin/e107-tests up --php 8.1 --db mariadb:10.6 --xdebug
```

Each combo gets its own compose project, its own random host ports, and its
own tmpfs database. You can run several at once — `e107-tests list` shows
them all and marks the active one with `*`.

Validated PHP versions: `8.1`, `8.2`, `8.3`, `8.4`.
Validated DB images: `mariadb:10.6`, `mariadb:10.11`, `mariadb:11.4`,
`mysql:8.0`, `mysql:5.7`. Anything supported by the upstream `php:<ver>-apache`
or `mariadb`/`mysql` Docker Hub images should work.

## How the wiring works

- `e107-tests up` writes `e107_tests/config.docker.yml` pointing Codeception
  at the running stack. Config order is **sample → yml → docker → local**,
  so your existing `config.yml` (if any) and `config.local.yml` still work:
  - `config.yml` keeps describing your VM-based flow.
  - `config.docker.yml` (managed by this tool) overrides while a stack is up.
  - `config.local.yml` always wins for personal tweaks.
- The worktree's app root is bind-mounted at `/var/www/html`, so file edits
  on the host show up in Apache immediately; no rebuild for application changes.
- The web container also has PHP CLI, Composer, and git, so `e107-tests run`
  invokes Codeception inside the container against the same filesystem
  Apache is serving.
- The WebDriver suite drives a dedicated `selenium/standalone-chrome` browser
  container. Both the acceptance and WebDriver suites use one app URL (the
  `web` service alias), so they exercise the same HTTP interface; e107 trusts
  the `web` host via the test dump's `trusted_hosts` pref.
- `up` builds the web image only if it is missing and reuses it otherwise, so a
  previously built or CI-cached image is picked up without a rebuild.
- Database state lives on tmpfs. `down` is a true reset; no leftover state.

## Working in a worktree

The local deployer (this harness) serves e107 from the app path itself, so it
runs the tests in place via `E107Preparer`. Only deploy-based suites
(sftp/cpanel) isolate the source in a disposable `git worktree`, and only when
git actually works in the app path. Each Docker stack is already isolated, so
running in place is safe here.

## Common operations

```sh
# Inspect
e107-tests status                  # docker compose ps
e107-tests logs web                # tail Apache logs
e107-tests logs db                 # tail MariaDB logs

# Interact
e107-tests shell                   # bash inside web container
e107-tests db-shell                # mariadb client in db container
e107-tests exec php -i             # one-shot PHP CLI command

# Reset only the DB and test-written app state, keep the stack
e107-tests reset

# Wipe acceptance-test artifacts from the host worktree (see note below)
e107-tests clean

# Tear down everything for this combo
e107-tests down
```

## Worktree dirtying during acceptance tests

The acceptance suite runs a real e107 install against the bind-mounted
worktree, so it leaves real files behind: `e107_config.php`, an install
log, hash directories under `e107_system/` and `e107_media/`, and any
themes the install copied into place. `e107-tests clean` wipes those
well-known artifacts (and never touches anything tracked by git).

Podman supports ephemeral overlay bind mounts (`:O`) that would isolate
these writes from the host, but Docker Compose silently strips the flag
during validation, so we don't rely on it. Treat `clean` as the canonical
"return the worktree to its pre-acceptance state" command.

## Agent ergonomics

- The project name is `e107-<8-char-hash-of-worktree-path>-php<ver>-<db>`,
  so two agents working in different worktrees never collide on container
  names, networks, volumes, or ports.
- All host ports are picked by Docker (`127.0.0.1::80`, `127.0.0.1::3306`).
  `e107-tests urls` is the canonical way to discover them.
- `_active` state is recorded per worktree, so an agent that re-opens a
  worktree can run `e107-tests urls` to recover where it left off without
  needing to re-pass `--php`/`--db` flags.

## Files

- `Dockerfile`: PHP + Apache + extensions, built once per `(PHP, xdebug)` combo.
- `compose.yml`: the db + web + selenium services, parameterized by env.
- `entrypoint.sh`: waits for DB, fixes ownership on the bind mount.
- `apache-vhost.conf` / `php-overrides.ini`: sensible test defaults.
- `.envs/<project>/stack.env`: per-stack state. Hand-edit at your peril;
  re-run `e107-tests up` instead.
