<?php

namespace E107\Rector\Tests\Downgrade\Refused;

function lastKey()
{
    return array_key_last([1, 2, 3]);
}
