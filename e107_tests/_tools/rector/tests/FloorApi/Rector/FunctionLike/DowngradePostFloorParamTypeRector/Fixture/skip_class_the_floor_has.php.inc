<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\DowngradePostFloorParamTypeRector\Fixture;

class SomeScheduler
{
    public function runAt(\DateTime $moment, array $options, callable $callback)
    {
    }
}
