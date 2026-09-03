# Docker test environments

A self-contained way to spin up isolated, per-worktree LAMP stacks so you (or
an agent) can verify changes against a specific PHP + MySQL/MariaDB combo
without contending for a shared dev VM.

## Why this exists

The legacy flow assumed one developer with one configured VM (described by
`e107_tests/config.yml`). That model breaks down as soon as more than one
parallel worker (human or AI) wants to run tests at the same time, or wants
to try a different PHP/DB version. This harness gives every worktree its own
short-lived stack, ready in under a minute and torn down with one command.

## Requirements

- Docker (or Podman with `podman-docker` so the `docker` CLI works), with the
  `docker compose` plugin.
- A worktree of this repo. The worktree itself is bind-mounted into the
  container; your edits are live immediately.

## Quickstart

```sh
# Default: PHP 8.5 + MariaDB 10.11
e107_tests/bin/e107-tests up

# See where the running site lives, plus DB credentials
e107_tests/bin/e107-tests urls

# Run tests
e107_tests/bin/e107-tests run unit
e107_tests/bin/e107-tests run acceptance

# WebDriver runs over either scheme; some tests only exist on one of them
e107_tests/bin/e107-tests run webdriver --env http
e107_tests/bin/e107-tests run webdriver --env tls

# Want a browsable, installed e107 site instead of a bare stack?
e107_tests/bin/e107-tests up --install-site     # admin login: admin / x107

# Serve the site from a subdirectory, the way most e107 installs are deployed
e107_tests/bin/e107-tests up --base-path e107   # site at http://web/e107/

# Editing anything under docker/? compose keeps the image it already has
e107_tests/bin/e107-tests up --rebuild

# When you're done
e107_tests/bin/e107-tests down
```

`e107-tests help` is the canonical command reference.

### Docroot or subdirectory

`--base-path DIR` aliases `DIR` onto the same document root and points the
suites at it. One tree, reachable both ways, so the only thing that changes is
the request path.

That is worth having because e107 derives `e_HTTP` from the request and scopes
its session cookie, its redirects and every generated link to it. A test that
assumes `/` therefore passes on a docroot stack and cannot pass on a
subdirectory one, and the failure looks like the application misbehaving rather
than the test asking the wrong question. CI runs both layouts for exactly this
reason: the subdirectory legs have already caught assertions that were quietly
passing for the wrong reason.

`--no-selenium` brings up `db` and `web` only. The browser image is over a
gigabyte and nothing but the WebDriver suite needs it.

### HTTP and HTTPS

The web container serves the same document root on both `http://web/` and
`https://web/`, with a self-signed certificate generated when the image is
built. The second scheme is not a nicety: a browser appends the `Sec-Fetch-*`
headers only when the request URL is a potentially trustworthy URL, so e107's
browser-only CSRF modes can only be exercised over TLS, and their softening
back to a token can only be exercised over plain HTTP. `--env tls` sets
`acceptInsecureCerts`, which leaves the page a secure context and the headers
intact.

## Choosing a matrix combo

```sh
e107_tests/bin/e107-tests up --php 8.4 --db mysql:8.0
e107_tests/bin/e107-tests up --php 8.1 --db mariadb:10.6 --xdebug
e107_tests/bin/e107-tests up --php 5.6 --db mariadb:10.6   # yes, really
```

Each combo gets its own compose project, its own random host ports, its own
tmpfs database, and its own dependency (vendor) volume. You can run several
at once; `e107-tests list` shows every harness env on the machine, across
all worktrees, and marks this worktree's active one with `*`.

Validated PHP versions: `5.6`, `7.0`, `8.1`, `8.2`, `8.3`, `8.4`, `8.5`.
Validated DB images: `mariadb:10.6`, `mariadb:10.11`, `mariadb:11.4`,
`mysql:5.7`, `mysql:8.0`. Anything supported by the upstream
`php:<ver>-apache` or `mariadb`/`mysql` Docker Hub images should work.

DB flavor notes (the wrapper handles these automatically):

- `mysql:5.*` doesn't know the `utf8mb3` charset alias, so the server is
  started with plain `utf8` there.
- `mysql:8.0`-`8.3` are started with
  `--default-authentication-plugin=mysql_native_password` so pre-7.4 PHP
  clients can authenticate. `mysql:8.4` only gets the plugin enabled, and
  `mysql:9` removed it entirely; don't pair legacy PHP with those.

## Legacy PHP (5.6 / 7.0)

`php:5.6-apache` and `php:7.0-apache` are Debian Stretch images whose apt
repositories moved to `archive.debian.org`. The Dockerfile detects the dead
repos and rewires them (with the archive's signing constraints relaxed; these
are throwaway test containers), picks version-appropriate gd/xdebug builds,
and installs the same Composer bootstrap, which auto-selects Composer 2.2 LTS
on old PHP.

`master`'s `e107_tests/composer.json` carries union constraints (for example
`codeception/codeception: "^4.2 || >=5.0.11"`), and `composer.php5.6.lock`
pins what they resolve to on PHP 5.6, so legacy containers install a known
dependency set on this branch too: see "One lock per PHP range" below.
Grafted old tags and `release/v2.3.x` worktrees bring their own constraints
and locks; on a tree whose constraints can't resolve on the container's PHP,
`install` surfaces composer's error untouched.

## Dependencies: per-env vendor, no lock churn

Every env mounts a named volume over `e107_tests/vendor`, so:

- Two stacks with different PHP versions never poison each other's vendor
  tree, even in the same worktree.
- The repo's lock files are rewritten by `relock` alone, never by `up`,
  `install` or `run`.

`up`/`install` run `composer install` against the newest committed lock the
env's PHP can take (CI parity). Only when no committed lock fits does the
harness say so on one loud line and fall back to a per-env `composer update`
whose lock lives inside the volume, not in your worktree; that line means a
PHP range has no lock yet (see below). `run` preflights for drift
(composer.json or lock edits, branch switches, PHP changes) and reinstalls
automatically.

The worktree's own `e107_tests/vendor/` directory is yours: the harness
neither writes nor reads it. If you want IDE autocompletion for Codeception,
run `composer install` in `e107_tests/` on the host yourself.

### One lock per PHP range

`composer.json` uses union constraints because one file has to serve every
cell in the PHP 5.6-8.5 matrix, and composer picks the highest branch the
running PHP can satisfy. What it picks depends on where upstream dropped a
PHP version, so one lock cannot serve the whole range; there is one per
range instead, each resolved on the oldest PHP it serves so every newer PHP
in the range installs it unchanged (JSON can't carry comments, so the
rationale lives here):

- `composer.lock`: resolved on the PHP that `config.platform.php` in
  `composer.json` declares (8.1), and installed as-is from there up.
- `composer.php5.6.lock`: resolved on PHP 5.6 and installed on 5.6 and 7.0.
  Codeception 4.2 with the 1.x modules, Twig 1.x and PHPUnit 5.7.

An env tries `composer.lock` first, then each `composer.php<floor>.lock`
from the newest floor down, and installs the first one whose platform
requirements its PHP meets. A PHP that none fits falls back to a live
`composer update`, and says so; the fix is to add an empty
`composer.php<floor>.lock` for that range and run `relock`.

There is deliberately no `behat/gherkin` cap: gherkin >=4.13 requires PHP
8.1, so legacy cells can never reach the release that breaks Codeception
4.x's loader, and modern cells run Codeception 5.x, which is fine with it.

### Upgrading the dependencies

`relock` regenerates every committed lock inside an env on the PHP it is
resolved for, and leaves the diff for you to review:

```sh
e107_tests/bin/e107-tests relock                      # every lock
e107_tests/bin/e107-tests relock composer.php5.6.lock # one of them
e107_tests/bin/e107-tests up --php 8.1 && e107_tests/bin/e107-tests run unit
```

Generating a lock on its own floor matters twice over: the resolution is
the one that PHP's Composer makes (the php:5.6 image ships Composer 2.2,
which predates the advisory block that a current Composer applies to
PHPUnit 5.7), and `check-platform-reqs` is checked on the real interpreter.
`relock` needs `php` on the host to read the floor out of `composer.json`.

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

## State lives in Docker, not in files

There are no state files. Every container and volume the harness creates
carries `e107.tests.*` labels (PHP version, DB image, worktree path, xdebug),
and every command except `up` rediscovers its env from those labels via
`docker compose -p <project>`. Practical consequences:

- `e107-tests list` sees every env on the machine, from any worktree.
- `e107-tests --env <project> <cmd>` drives any env from anywhere, including
  tearing down a stack whose worktree you're not in.
- The only per-worktree file is `config.docker.yml`, which Codeception needs
  anyway; its `Generated for project:` header marks the worktree's active env.
- `e107-tests gc` removes the volumes of envs that no longer have containers
  (a downed stack's vendor/composer caches rebuild on the next `up`), and
  `gc --worktrees-gone` tears down stacks whose worktree was deleted.

Changing a flag on an env that already exists is supported: `up --xdebug` on
a stack brought up without it recreates the containers on the xdebug image
rather than erroring or quietly doing nothing, and says which way it is
switching the env as it goes. The catch is that `up` only rewrites the labels
of the services it brings up, so a partial-scope `up` leaves the others
carrying the labels of the run that created them; `--no-selenium` is the
reachable case, its browser container surviving the switch untouched. `up`
fails if the labels it leaves behind disagree with the flags it was given, and
the answer when that happens is `down` followed by `up` with the flags you
want.

Every diagnostic the harness prints goes to stderr, and stdout carries only a
command's own data. A refusal is therefore invisible to a caller capturing
stdout alone: read verdicts with `2>&1`.

## Self-healing

`up` and the exec-ish commands absorb the environmental flakes we used to
debug by hand:

- **Container DNS**: if external names don't resolve inside the stack
  (a rootless-podman classic), `up` appends a public nameserver to the web
  container's resolv.conf and warns; if even raw-IP egress is broken it
  prints a host-side diagnosis ladder instead of letting composer time out.
- **Stale bind mounts**: a long-lived container that starts failing every
  exec with "chdir to cwd" / "container breakout detected" (podman after
  heavy git churn) is restarted automatically and the command retried.
- **Registry flakes**: `up` pulls the images it is missing three times with
  a growing pause before giving up, and a "denied"/rate-limit refusal doesn't
  kill it when all needed images already exist locally.
- **Package host flakes**: the composer install inside the container gets
  the same three tries, so a package host that answers 504 for a minute
  costs a minute, not the run.
- **Honest exit codes**: `up` verifies db/web/selenium are actually running
  and healthy and fails loudly (with `compose ps` output) if not.

## Composer's download cache

Composer's cache lives in a named volume per env, so a `down` followed by
`up` reinstalls without downloading again. Set `E107_COMPOSER_CACHE` to an
absolute host directory to bind that instead; CI does, wrapped in
`actions/cache`, so a warm run installs from lock without a single request
to a package host. `down --volumes` leaves a bound directory alone. Local
users have no reason to set it.

## Working in a worktree

Every command acts on the worktree the script itself lives in, worked out from
its own path. Your working directory is never consulted, so
`/path/to/tree/e107_tests/bin/e107-tests up` drives that tree from anywhere and
a caller with no stable working directory needs nothing else. To reach a
different tree, name it: `e107-tests --worktree <path> <cmd>` hands over to the
harness shipped in that tree, since the copies drift between branches.

The local deployer (this harness) serves e107 from the app path itself, so it
runs the tests in place via `E107Preparer`. Only deploy-based suites
(sftp/cpanel) isolate the source in a disposable `git worktree`, and only when
git actually works in the app path. Each Docker stack is already isolated, so
running in place is safe here.

For worktrees that predate the harness (old release tags), graft it in:

```sh
git worktree add /tmp/e107-v2.3.0 v2.3.0
e107_tests/bin/e107-tests graft /tmp/e107-v2.3.0
/tmp/e107-v2.3.0/e107_tests/bin/e107-tests up --php 7.0 --install-site
```

## Common operations

```sh
# Inspect
e107-tests status                  # docker compose ps
e107-tests logs web                # recent Apache logs (-f to follow)
e107-tests logs db                 # recent MariaDB logs
e107-tests list                    # all harness envs, all worktrees

# Interact
e107-tests shell                   # bash inside web container
e107-tests db-shell                # DB client in db container
e107-tests sql "SELECT * FROM e107_user"
e107-tests sql < dump.sql
e107-tests exec php -i             # one-shot PHP CLI command

# CI reproduction
e107-tests ci-unit                 # CI's exact unit command

# Coverage, on demand
e107-tests up --xdebug             # coverage-capable image
e107-tests run unit --coverage --coverage-html

# Reset only the DB and test-written app state, keep the stack
e107-tests reset

# Wipe acceptance-test artifacts from the host worktree (see note below)
e107-tests clean

# Tear down everything for this combo
e107-tests down

# Remove caches of downed envs, stacks of deleted worktrees
e107-tests gc --dry-run
e107-tests gc --worktrees-gone
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
- An agent that re-opens a worktree can run `e107-tests urls` to recover
  where it left off; the active env is rediscovered from Docker labels and
  the `config.docker.yml` marker, with no flags to re-pass.

## Files

- `Dockerfile`: PHP + Apache + extensions, built once per `(PHP, xdebug)`
  combo; handles EOL Debian bases for PHP 5.6/7.0.
- `compose.yml`: the db + web + selenium services, parameterized by env,
  plus the `e107.tests.*` labels that serve as the harness's state store.
- `../composer.lock`, `../composer.php<floor>.lock`: one dependency lock per
  PHP range; see "One lock per PHP range".
- `entrypoint.sh`: waits for DB, fixes ownership on the bind mount.
- `apache-vhost.conf` / `php-overrides.ini`: sensible test defaults.
