<?php

namespace E107\Rector\Tests\FloorApi\Rector\ConstFetch\DowngradePostFloorConstantRector\Fixture;

if (!defined('PREG_JIT_STACKLIMIT_ERROR')) {
    define('PREG_JIT_STACKLIMIT_ERROR', 6);
}

const SOME_UNCONFIGURED_CONSTANT = PREG_NO_ERROR;
