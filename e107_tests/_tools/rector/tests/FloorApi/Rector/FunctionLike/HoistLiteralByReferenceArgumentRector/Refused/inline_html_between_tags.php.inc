<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Refused;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function cell($token, $pem, $legacy)
{
    ?><td><?= $legacy ? Decoder::decode($token, $pem, ['RS256']) : 'modern' ?></td><?php
}
