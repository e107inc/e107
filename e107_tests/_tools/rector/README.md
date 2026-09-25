# PHP 5.6 downgrade pipeline

Rector tooling that rewrites the repository's PHP down to the 5.6 language
floor, so the tree we commit is the tree we ship.

## Why this exists

e107 v2 has no build step. A release is a zip of the repository, and the
oldest server we still support runs PHP 5.6, so every shipped file has to
*parse* on PHP 5.6 exactly as it sits in git. At the same time nobody wants to
write PHP 5.6 by hand in 2026.

This pipeline resolves that: contributors write modern PHP, and the downgrade
runs at commit time, in the working tree, in place. **The committed tree is the
shipping artifact.** There is no separate "built" branch to keep in sync and no
release-time transform that can silently differ from what was reviewed. What
you read in a diff is what a user's server executes.

Most of it is a *syntax* floor. The sets rewrite language constructs (null
coalesce, scalar and nullable type hints, spaceship, anonymous classes,
`list()` keys, and so on). They do not polyfill functions, and they do not make
the application's runtime behaviour PHP 5.6 compatible on its own.

Syntax alone is not enough, because PHP 5.6 happily parses a type hint, a
constant read and a method signature whatever they name. A vendored package can
clear the whole downgrade and still fatal on the floor the first time it runs,
which is what `custom-rules/FloorApi/` exists for; see **The API floor** below.

## Running it

```sh
# One-time, or after the lock changes
cd e107_tests/_tools/rector && composer install

# Downgrade the whole tree, from anywhere in the worktree
e107_tests/_tools/rector/bin/rector-downgrade
```

Rector rewrites one file per pass and resolves each file against the *current*
signatures of the others, so a downgrade that spans an inheritance chain needs
one pass per level before it settles. `rector-downgrade` therefore loops until
a pass changes nothing, which is the downgrade's fixed point, and fails if that
takes more passes than its cap (`RECTOR_MAX_PASSES`, default 5). A run that
will not converge almost always means a non-idempotent rule is re-rewriting its
own output; raise the cap only after investigating.

`rector-downgrade` and the CI workflow that enforces this land together in
their own commit; this directory is only the tooling they drive.

## When the idempotency gate fails

CI runs the same downgrade and fails if it produces a diff. That failure means
exactly one thing: something in the tree is above the floor. The fix is
mechanical.

```sh
cd e107_tests/_tools/rector && composer install
e107_tests/_tools/rector/bin/rector-downgrade
git add -A && git commit
```

Review the diff before committing it like any other change. If the diff is not
something you want (the rule is wrong, or it is rewriting a file it should not
touch), that is a tooling bug, and the fix belongs in `rector.php` rather than
in a hand-edit that the next run will undo.

## Rule policy

Most of the work comes from upstream's `DowngradeSetList` sets. On top of those,
`custom-rules/` vendors the PHP 7.1 → 5.6 rules that upstream deleted after
Rector 0.15.1, plus three stock rules re-implemented because their upstream
copies crash or are non-idempotent under Rector 2.x. Every skip in `rector.php`
carries a comment explaining itself; two are worth repeating here.

**Parameter type widening is off.** PHP 5.6 parses an `array`, `callable` or
class parameter hint whatever it names, so keeping them costs the floor
nothing. `DowngradeParameterTypeWideningRector` would strip roughly 116 of them
to guard against a pre-7.2 contravariance hazard this codebase does not have,
and any real widening error surfaces at class load in the legacy unit cells
anyway. A hint naming a class the floor does not *have* is a different problem,
and `DowngradePostFloorParamTypeRector` takes those by name.

**The substr casts are quarantined.** On PHP 5.6 `substr()` can return `false`
where PHP 8 returns `''`, and `DowngradeSubstrFalsyRector` papers over that with
casts at every call site. That is a real behavioural difference, but it is a
large, noisy diff that deserves its own review, so the rule is skipped here. A
later commit removes the skip entry and carries the resulting casts, which keeps
that commit revertible on its own: revert it and the tree is back at a
consistent fixed point instead of a half-cast one.

## The API floor

A parser has no opinion on what a PHP version *has*. Four shapes cleared every
gate this project owns and still killed a request on a real 5.6, and each one
was found by hand and patched by hand in vendored source, which the next
re-vendor rolls straight back. `custom-rules/FloorApi/` and
`DowngradeConflictingUseImportRector` own those four instead:

- **A parameter type naming a class the floor lacks.** firebase/php-jwt 7.x
  types its key-length validators against `OpenSSLAsymmetricKey`, a PHP 8.0
  class, while the caller hands them what `openssl_pkey_get_private()` returns,
  which is a resource below 8.0. `DowngradePostFloorParamTypeRector` moves the
  hint into a `@param` docblock. Its configuration in `rector.php` is the list
  of PHP 8.0 and 8.1 resource-to-object classes; add to that list rather than
  writing a second rule.
- **A constant the floor does not define.** symfony/polyfill-php80 reads
  `\PREG_JIT_STACKLIMIT_ERROR` in a switch arm, and a fully qualified undefined
  constant is a fatal below PHP 7.0 rather than a notice.
  `DowngradePostFloorConstantRector` writes the value the constant holds.
- **A stripped tentative return type.** Removing `jsonSerialize(): string` to
  reach the floor leaves guzzlehttp/psr7's `Uri` violating
  `JsonSerializable`, which PHP 8.1 and above report at class-link time.
  `DowngradeTentativeReturnTypeRector` adds `#[\ReturnTypeWillChange]`, which is
  a `#` comment on the floor. Upstream's own decorator does this for
  `ArrayAccess::offsetGet` alone; the rule carries the rest of the list.
- **An import whose short name its namespace has taken.** PHP below 7.0.13
  refuses `use Hybridauth\Adapter\OpenID;` inside `Hybridauth\Provider`, which
  has an `OpenID` of its own (PHP bug 66773).
  `DowngradeConflictingUseImportRector` aliases the import and rewrites the
  references. This one is a name-resolution difference rather than an API gap,
  so it sits with the other `DowngradePhp70` rules.

None of these can be proved by parsing. Tests that execute the vendored
packages on whichever interpreter the cell is running are the other half, and
they live with the rest of the unit suite.

## Rule tests

```sh
cd e107_tests/_tools/rector && composer install
vendor/bin/phpunit
```

`tests/` holds one directory per custom rule, each with a `config/` naming the
rule under test and a `Fixture/` of before-and-after pairs separated by
`-----`; a fixture with no separator asserts the rule leaves it alone. Writing
one means writing the before half and running `UPDATE_TESTS=1 vendor/bin/phpunit`,
which fills in the after half for you to read.

`tests/Downgrade/` is the exception: it points at the shipping `rector.php`
rather than a single rule, so its fixtures go through every set and rule at
once. That is what covers a rule being written but never registered, and the
order two rules run in. Its four fixtures are the four vendored shapes above,
in the form they had before anybody patched them.

The rule tests run on PHP 8.2 in CI, ahead of the whole-tree pass, because they
take a second and it takes minutes.

## The test-fixture hazard

`e107_tests/tests` is **in scope** for the downgrade. Only `_output` and
`_support/_generated` are skipped, because Codeception regenerates them.

That matters for tests about PHP source itself. A `.php` fixture that
deliberately holds PHP 8 syntax is, to Rector, just another file above the
floor: it gets downgraded, the test's premise quietly evaporates, and the
idempotency gate turns a passing suite into a failing diff. String literals are
safe (Rector parses PHP, it does not parse strings), so the options are:

- embed the modern source as a string or heredoc inside the test, or
- give the fixture a non-`.php` extension, or
- skip its path explicitly in `rector.php`.

Prefer the first. Adding paths to the skip list shrinks the guarantee that the
gate provides.

## `modern-paths.txt`

Some tracked PHP is intentionally modern: CI helpers under `.github`, and the
developer tooling under `e107_tests/_tools` (this pipeline, and the PHP 8.1-only
SQLi scanner next to it). None of it ever runs on a user's server, so the
downgrade skips it, and the floor lint has to skip it too or it would report the
tooling as a violation.

`modern-paths.txt` is that shared list: one repo-relative path prefix per line,
`#` for comments. `bin/php-floor-lint` reads it with a shell `read` loop, and any
PHP consumer can read it with `file()`. It mirrors the correctness path skips in
`rector.php`; when you change one, change the other.

## Files

- `rector.php`: the config. Sets, custom rule registrations, skips, PHP 5.6
  target, cache directory.
- `custom-rules/`: the `E107\Rector` rule suite (vendored 7.1 → 5.6 rules, the
  three re-implemented stock rules, and `FloorApi/` for the API floor).
- `tests/` and `phpunit.xml`: the rule tests, described above.
- `composer.json` / `composer.lock`: the pinned Rector version. The lock is
  committed on purpose, because the idempotency gate is only reproducible
  against a fixed Rector.
- `modern-paths.txt`: floor-lint exemptions, described above.

## Anonymous classes in Codeception test files

The downgrade rewrites an anonymous class into a named class declared on
first use through an `spl_autoload_register()` closure. That construction
is correct PHP everywhere, but Codeception's suite loader reflects every
class declared in a `*Test.php` while loading the suite, which forces the
deferred declaration before any in-method `require` has made its parent
loadable. Do not use anonymous classes in test files; declare a named
fixture class in its own include and `require_once` it inside the test
method (see `e107_tests/tests/unit/fixtures/`).
