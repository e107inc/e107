<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

class Pivot
{
}

class SkippedPivotFactory
{
    public function __construct(
        protected Pivot $pivot = new Pivot(),
        $init = null,
    ) {
    }

    public static function init($init = null)
    {
        return new self(init: $init);
    }
}
-----
<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

class Pivot
{
}

class SkippedPivotFactory
{
    /**
     * @var \E107\Rector\Tests\Downgrade\Fixture\Pivot
     */
    protected $pivot;
    /**
     * @param \E107\Rector\Tests\Downgrade\Fixture\Pivot|null $pivot
     */
    public function __construct($pivot = null, $init = null)
    {
        $pivot = isset($pivot) ? $pivot : new Pivot();
        $this->pivot = $pivot;
    }

    public static function init($init = null)
    {
        return new self(null, $init);
    }
}
