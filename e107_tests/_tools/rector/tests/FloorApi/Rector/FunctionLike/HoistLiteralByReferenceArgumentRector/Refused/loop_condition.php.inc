<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Refused;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function loop($tokens, $pem)
{
    while (Decoder::decode(array_pop($tokens), $pem, ['RS256'])) {
    }
}
