<?php

namespace E107\Rector\Tests\FloorApi\Rector\ConstFetch\DowngradePostFloorConstantRector\Fixture;

function preg_last_error_message()
{
    switch (preg_last_error()) {
        case \PREG_BACKTRACK_LIMIT_ERROR:
            return 'Backtrack limit exhausted';
        case \PREG_JIT_STACKLIMIT_ERROR:
            return 'JIT stack limit exhausted';
        default:
            return PHP_OS_FAMILY;
    }
}
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\ConstFetch\DowngradePostFloorConstantRector\Fixture;

function preg_last_error_message()
{
    switch (preg_last_error()) {
        case \PREG_BACKTRACK_LIMIT_ERROR:
            return 'Backtrack limit exhausted';
        case 6:
            return 'JIT stack limit exhausted';
        default:
            return PHP_OS_FAMILY;
    }
}
