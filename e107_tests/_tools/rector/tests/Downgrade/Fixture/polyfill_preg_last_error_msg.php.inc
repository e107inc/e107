<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

final class Php80
{
    public static function preg_last_error_msg()
    {
        switch (preg_last_error()) {
            case \PREG_BACKTRACK_LIMIT_ERROR:
                return 'Backtrack limit exhausted';
            case \PREG_JIT_STACKLIMIT_ERROR:
                return 'JIT stack limit exhausted';
            case \PREG_NO_ERROR:
                return 'No error';
            default:
                return 'Unknown error';
        }
    }
}
-----
<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

final class Php80
{
    public static function preg_last_error_msg()
    {
        switch (preg_last_error()) {
            case \PREG_BACKTRACK_LIMIT_ERROR:
                return 'Backtrack limit exhausted';
            case 6:
                return 'JIT stack limit exhausted';
            case \PREG_NO_ERROR:
                return 'No error';
            default:
                return 'Unknown error';
        }
    }
}
