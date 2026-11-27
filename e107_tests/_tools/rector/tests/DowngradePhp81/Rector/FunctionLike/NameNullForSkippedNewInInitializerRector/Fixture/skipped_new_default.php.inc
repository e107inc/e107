<?php

namespace E107\Rector\Tests\DowngradePhp81\Rector\FunctionLike\NameNullForSkippedNewInInitializerRector\Fixture;

class Pivot
{
}

class SkippedNewDefault
{
    public function __construct(Pivot $pivot = new Pivot(), $init = null)
    {
    }

    public static function init($init = null)
    {
        return new self(init: $init);
    }
}
-----
<?php

namespace E107\Rector\Tests\DowngradePhp81\Rector\FunctionLike\NameNullForSkippedNewInInitializerRector\Fixture;

class Pivot
{
}

class SkippedNewDefault
{
    public function __construct(Pivot $pivot = new Pivot(), $init = null)
    {
    }

    public static function init($init = null)
    {
        return new self(init: $init, pivot: null);
    }
}
