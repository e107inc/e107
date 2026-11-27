<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Refused;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function extracted(array $row, $pem)
{
    extract($row);

    return Decoder::decode($token, $pem, ['RS256']);
}
