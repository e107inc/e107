<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Refused;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function classConstant($token, $pem)
{
    return Decoder::decode($token, $pem, [Decoder::ALGORITHM]);
}
