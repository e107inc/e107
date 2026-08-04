<?php

declare(strict_types=1);

/**
 * e107 SQL-injection-elimination scanner.
 *
 * Enumerates every call to the e107 e_db database object's raw-SQL methods and
 * classifies each by SAFETY (static / bound-safe / assumed-safe-array /
 * unsafe-concat) and convertibility TIER (T1-T4). Built on nikic/php-parser;
 * this same engine is the per-file verify check and the future CI gate.
 *
 * Usage:
 *   php sqli_scan.php [PATH] [--out=FILE] [--root=DIR] [--quiet] [--summary]
 *                     [--baseline=FILE] [--write-baseline=FILE]
 *
 *   PATH        Optional FILE or DIRECTORY to scan. When a file, only that file
 *               is scanned; when a directory, the in-scope tree under it. When
 *               omitted, the whole repo tree is scanned. Paths in the output are
 *               always relative to the repo root.
 *   --out=FILE  Deliberately WRITE the catalog JSON to FILE (the master-catalog
 *               regeneration path). Without it the tool is NON-DESTRUCTIVE.
 *   --root=DIR  Repo root for path relativization and the default scan target
 *               (default: two levels up from this tool).
 *   --quiet     Print only a count and the failing (unsafe-concat) sites.
 *   --summary   Print the per-method summary table (default when --out is given
 *               and no PATH is supplied; i.e. full master regen). Otherwise the
 *               default output is the catalog JSON array on stdout.
 *   --baseline=FILE        Subtract the reviewed/deferred unsafe-concat sites
 *               recorded in FILE before the gate's exit-code decision, so only
 *               NET-NEW concatenation fails the build. A stale baseline entry
 *               (recorded but no longer present in the code) also fails the gate,
 *               so the frozen set cannot silently drift out of sync.
 *   --write-baseline=FILE  Freeze the current unsafe-concat set to FILE (the
 *               baseline regeneration path).
 *
 * Output (default, no --out): the catalog JSON array is printed to stdout and
 * the catalog file is NOT touched.
 *
 * Exit code: 1 when the scanned scope has an unsafe-concat site not covered by
 * --baseline, or (with --baseline) a stale baseline entry that no longer matches
 * any site, so the tool doubles as the per-file verify / CI gate; 0 when the
 * scope is clean and the baseline is in sync. static / bound-safe /
 * assumed-safe-array sites never fail the gate. Exit 2 on a usage/setup error.
 *
 * Examples:
 *   php sqli_scan.php e107_plugins/poll/poll_class.php
 *       scan one file, print findings, exit 1 if unsafe-concat present, do NOT
 *       touch catalog.json.
 *   php sqli_scan.php --out=e107_tests/_tools/catalog.json
 *       regenerate the full master catalog and print the summary table.
 */

namespace E107\SqliScan;

$autoload = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php; run `composer install` in " . __DIR__ . "\n");
    exit(2);
}
require $autoload;

$options = parseArgs($argv);

$root = $options['root'] ?? realpath(__DIR__ . '/../../');
if ($root === false || !is_dir($root)) {
    fwrite(STDERR, 'Invalid --root: ' . ($options['root'] ?? '(auto)') . "\n");
    exit(2);
}
$root = rtrim($root, '/');

// Resolve the scan target: the positional PATH (file or dir), else the root.
$target = $root;
if (isset($options['_positional'])) {
    $target = resolveTarget($options['_positional'], $root);
    if ($target === null) {
        fwrite(STDERR, 'Path not found: ' . $options['_positional'] . "\n");
        exit(2);
    }
}

// Oracle ordering (also the reconciliation reference) from the dual-oracle run.
$oracleCounts = [
    'gen' => 360, 'select' => 324, 'update' => 195, 'insert' => 114,
    'retrieve' => 113, 'count' => 99, 'delete' => 76, 'execute' => 23,
    'escape' => 21, 'replace' => 4, 'max' => 1,
];

// Wire the single-responsibility collaborators.
$taxonomy = new ReceiverTaxonomy();
$catalog = new MethodCatalog();
$rules = new SafetyRules();
$taint = new TaintAnalyzer($rules);
$safetyClassifier = new SafetyClassifier($catalog, $taint);
$tierClassifier = new TierClassifier();
$excerpter = new SqlExcerptExtractor();
$taintTracker = new RequestTaintTracker();
$visitor = new CallSiteVisitor(
    $taxonomy,
    $catalog,
    $safetyClassifier,
    $tierClassifier,
    $excerpter,
    $taintTracker
);
$scanner = new Scanner($visitor);

$sites = $scanner->scan($target, $root);
$reporter = new Reporter();

// Deliberate catalog write (master-catalog regeneration path).
if (isset($options['out'])) {
    $reporter->writeCatalog($sites, (string) $options['out']);
}

// Optional deferral baseline: the reviewed/known unsafe-concat residue the gate
// tolerates, so it fails only on NET-NEW concatenation.
$baseline = null;
if (isset($options['baseline'])) {
    try {
        $baseline = DeferralBaseline::load((string) $options['baseline']);
    } catch (\RuntimeException $e) {
        fwrite(STDERR, $e->getMessage() . "\n");
        exit(2);
    }
}

// Deliberate baseline regeneration: freeze the current unsafe-concat set.
if (isset($options['write-baseline'])) {
    DeferralBaseline::fromSites($sites)->write((string) $options['write-baseline']);
    fwrite(STDOUT, 'baseline written: ' . $options['write-baseline'] . "\n");
}

// Choose the stdout rendering.
if (!empty($options['quiet'])) {
    fwrite(STDOUT, $reporter->quietReport($sites, $baseline));
} elseif (!empty($options['summary']) || (isset($options['out']) && !isset($options['_positional']))) {
    // Summary mode: explicit, or the full master regeneration.
    if (isset($options['out'])) {
        fwrite(STDOUT, 'catalog written: ' . $options['out'] . "\n");
    }
    fwrite(STDOUT, $reporter->summary($sites, $oracleCounts));
    foreach (array_slice($scanner->errors(), 0, 20) as $err) {
        fwrite(STDOUT, "  warning: $err\n");
    }
} else {
    // Default, non-destructive: the catalog JSON array on stdout.
    fwrite(STDOUT, $reporter->toJson($sites) . "\n");
}

// Exit code for gate/verify use: with a baseline, fail on NET-NEW unsafe-concat
// or on a stale baseline entry (a recorded site that no longer matches, so the
// frozen set has drifted and must be re-baselined on purpose); without a
// baseline, fail on any unsafe-concat.
if ($baseline !== null) {
    $clean = $baseline->passes($sites);
} else {
    $clean = true;
    foreach ($sites as $site) {
        if ($site->safety === CallSite::SAFETY_UNSAFE) {
            $clean = false;
            break;
        }
    }
}
exit($clean ? 0 : 1);

/**
 * Resolve a user-supplied path (absolute or relative to cwd or repo root) to an
 * existing file/dir, or null if it cannot be found.
 */
function resolveTarget(string $path, string $root): ?string
{
    $candidates = [$path];
    if ($path !== '' && $path[0] !== '/') {
        $candidates[] = $root . '/' . $path;
        $candidates[] = getcwd() . '/' . $path;
    }
    foreach ($candidates as $candidate) {
        $real = realpath($candidate);
        if ($real !== false && (is_file($real) || is_dir($real))) {
            return $real;
        }
    }
    return null;
}

/**
 * @param list<string> $argv
 * @return array<string, string|bool> parsed options; '_positional' holds the
 *                                     first non-flag argument (the PATH)
 */
function parseArgs(array $argv): array
{
    $opts = [];
    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--quiet') {
            $opts['quiet'] = true;
        } elseif ($arg === '--summary') {
            $opts['summary'] = true;
        } elseif (preg_match('/^--([a-z-]+)=(.*)$/', $arg, $m)) {
            $opts[$m[1]] = $m[2];
        } elseif (!str_starts_with($arg, '--') && !isset($opts['_positional'])) {
            $opts['_positional'] = $arg;
        }
    }
    return $opts;
}
