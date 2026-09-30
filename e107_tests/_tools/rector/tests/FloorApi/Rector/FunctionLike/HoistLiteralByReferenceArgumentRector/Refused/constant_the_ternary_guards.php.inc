<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Refused;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function guarded($token, $pem)
{
    return defined('ALGORITHMS') ? Decoder::decode($token, $pem, \ALGORITHMS) : $token;
}
