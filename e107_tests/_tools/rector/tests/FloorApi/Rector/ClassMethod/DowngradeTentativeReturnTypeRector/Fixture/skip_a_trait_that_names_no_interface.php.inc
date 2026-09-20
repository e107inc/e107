<?php

namespace E107\Rector\Tests\FloorApi\Rector\ClassMethod\DowngradeTentativeReturnTypeRector\Fixture;

trait JsonBits
{
    public function jsonSerialize()
    {
        return [];
    }
}
