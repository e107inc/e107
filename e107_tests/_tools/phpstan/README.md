# Static analysis

PHPStan reads every shipping PHP file at level 8 and compares what it finds
with `baseline.neon`, a recorded list of what the tree already had when the
gate landed.

## Why this exists

Static analysis catches, mechanically, a class of defect that only shows up on
a live site: a variable read before it is written. PHP 9 promotes that from a
warning to a thrown `Error`, so every latent instance in core becomes a fatal
on the next major PHP, and the baseline records hundreds of them.

Recording is not excusing. An entry in the baseline is a line on a worklist,
and the gate fails two ways: on an error that is not recorded, and on a
recorded entry that no longer matches anything. The second is what stops the
list turning into a permanent exemption. Fix a finding, and its entry comes out
in the same commit.

## Running it

```sh
# One-time, or after the lock changes
composer install --working-dir=e107_tests/_tools/phpstan

# The whole shipping tree, from anywhere in the worktree
e107_tests/_tools/phpstan/bin/phpstan-analyse

# One file or directory, while you are fixing it
e107_tests/_tools/phpstan/bin/phpstan-analyse e107_plugins/news/news.php

# The same, with everything the baseline excuses reported as well
e107_tests/_tools/phpstan/bin/phpstan-analyse --ignore-baseline e107_plugins/news/news.php
```

PHP 8.1 or newer runs the analyser itself; the analysed *code* is read for the
runtime window in `analysis.neon` (PHP 8.0 to 8.5), which is what the root
`composer.json` requires and what CI runs, not what your CLI happens to be.

The whole-tree run hands PHPStan the top-level directories of the tracked set,
never a list of files: given files alone, PHPStan silently stops reporting a
baseline entry that matches nothing, and drops its result cache as well.

The analyser reads the directories as they sit on disk, so PHP of your own
that is not in git is analysed too. A plugin of yours reports findings the
baseline does not have, and a theme of yours can change what PHPStan concludes
about the core handlers that call it, so an entry recorded against one of them
can stop matching. That is a local result, not a failing build: CI analyses a
clean checkout. Name the path you are working on if the noise is in the way.

`--write-baseline` is the exception: it copies the tracked files to a scratch
directory and records there, with no result cache, reading the list of
tracked files from this checkout's git index. Local PHP would move entries as
described above, and a cache remembers a file that is no longer on disk.
Either would write a baseline only this checkout can satisfy.

One wrinkle worth knowing before you read an entry: three bundled themes declare
their own `tablestyle()`, and PHPStan sees one function, so a couple of entries
against `e107_handlers/e_render_class.php` are recorded against whichever
declaration it resolved. Editing one of those themes can move them.

## Taking a file to zero

1. Read the work already recorded against it. The entries are in
   `baseline.neon`, each carrying the `path` it was recorded for, and this
   prints the same errors with their line numbers:

   ```sh
   e107_tests/_tools/phpstan/bin/phpstan-analyse --ignore-baseline <path>
   ```

   Read that list knowing what it is. Handing PHPStan one file gives it one
   file's symbols, so the view is not the gate's: it invents errors the gate
   never reports (`Function vartrue not found`, defined in
   `e107_handlers/core_functions.php`, is the usual one) and it hides errors the
   gate does report, because unresolved symbols widen the types that flow from
   them. It is a worklist, not a verdict. The verdict is a whole-tree run.

2. Fix the errors. Prefer the fix PHPStan is pointing at over a narrowing
   PHPDoc: a docblock that merely quiets the analyser leaves the defect.

3. Re-record the baseline:

   ```sh
   e107_tests/_tools/phpstan/bin/phpstan-analyse --write-baseline
   ```

4. Commit `baseline.neon` in the same commit as the fixes. The diff should only
   remove entries for the file you worked on. Anything else in it is a finding
   your change introduced somewhere else.

## When the gate fails

**An error is reported that the baseline does not record.** It is new. Fix it:
adding an entry to the baseline for code you are writing today defeats the
point of having one.

**An error is `ignore.unmatched`.** A recorded entry matches nothing, so
something is fixed and its entry is stale. Re-record the baseline as above and
commit it with the fix.

The job runs the same script CI does, so a local run reproduces either failure.

## What is not analysed

The test tree and `.github` are not read at all. The vendored dependencies and
the bundled plugins' own tests are read for the symbols they define but never
analysed: a dependency's findings are not e107's worklist.

`bootstrap.php` tells PHPStan what e107 declares at run time, which no analyser
follows:

- `e_db` and `e_db_common`, the `class_alias()` registrations in
  `e107_handlers/e_db_interface.php`. Without them the two database handlers
  cannot be analysed at all.
- Every constant the tracked tree defines: each `define()` whose name is a
  string literal, in any scope (`e_PLUGIN` is defined inside a method), and
  every key of the array a language file returns. `src/DefinedConstants.php`
  reads them with the tokenizer, and `dynamic-constants.php` types each one by
  the values the tree gives it rather than by one value, so a check against a
  constant is never reported as always true or always false.

A constant nothing in the tree defines is still reported, which is how a
misspelt `LAN_` name surfaces. A new `define()` or language key is picked up on
the next run, with nothing to regenerate.

`release/v2.3.x` is not gated. That tree is PHP 5.6 source against a different
runtime window and needs a baseline of its own.
