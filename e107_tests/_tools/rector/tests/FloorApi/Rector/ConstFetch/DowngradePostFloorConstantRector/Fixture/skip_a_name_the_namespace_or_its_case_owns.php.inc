<?php

namespace E107\Rector\Tests\FloorApi\Rector\ConstFetch\DowngradePostFloorConstantRector\Fixture;

const PREG_JIT_STACKLIMIT_ERROR = 99;

class Reader
{
    const PREG_JIT_STACKLIMIT_ERROR = 77;

    public function readNamespaced()
    {
        return PREG_JIT_STACKLIMIT_ERROR;
    }

    public function readClassConstant()
    {
        return self::PREG_JIT_STACKLIMIT_ERROR;
    }

    public function readAnotherCase()
    {
        return \preg_jit_stacklimit_error;
    }
}
