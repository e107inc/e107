<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Upstream's xxhash downgrade rule dereferences these four at class-definition
// time, which is why rector.php skips it for a const-free vendored copy. The
// command line never builds a skipped rule; the test container builds every
// registered one, so the names have to exist for the suite to boot at all.
foreach (['MHASH_XXH32', 'MHASH_XXH64', 'MHASH_XXH3', 'MHASH_XXH128'] as $index => $mhashAlgorithm) {
    if (! defined($mhashAlgorithm)) {
        define($mhashAlgorithm, $index);
    }
}
