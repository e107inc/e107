<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function sortedDefaults()
{
    sort([3, 1, 2]);
    $decoder = new Decoder(['RS256' => true]);

    return $decoder->read('token', [-1, 2.5, 'key' => null, 'strict' => FALSE]);
}
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function sortedDefaults()
{
    $array = [3, 1, 2];
    sort($array);
    $options = ['RS256' => true];
    $decoder = new Decoder($options);
    $claims = [-1, 2.5, 'key' => null, 'strict' => FALSE];

    return $decoder->read('token', $claims);
}
