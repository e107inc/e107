<?php

declare(strict_types=1);

/**
 * Every global constant the tracked shipping tree defines at run time, as name => PHPStan type.
 *
 * The tree is the tracked set, read from git beside this file; PHP's own constants are left to PHPStan.
 */

use E107\PhpStan\DefinedConstants;

require_once __DIR__ . '/src/DefinedConstants.php';

return (static function (string $root): array {
    $listing = shell_exec('git -C ' . escapeshellarg($root) . ' ls-files -z -- ' . escapeshellarg('*.php'));
    if (!is_string($listing) || $listing === '') {
        throw new RuntimeException("defined-constants.php: git lists no tracked PHP under $root.");
    }

    $constants = new DefinedConstants();
    foreach (explode("\0", rtrim($listing, "\0")) as $path) {
        if (str_starts_with($path, 'e107_tests/') || str_contains($path, '/vendor/') || !is_file("$root/$path")) {
            continue;
        }
        $constants->read($path, (string) file_get_contents("$root/$path"));
    }

    $php = get_defined_constants(true);
    unset($php['user']);

    return array_diff_key($constants->types(), array_merge(...array_values($php)));
})(dirname(__DIR__, 3));
