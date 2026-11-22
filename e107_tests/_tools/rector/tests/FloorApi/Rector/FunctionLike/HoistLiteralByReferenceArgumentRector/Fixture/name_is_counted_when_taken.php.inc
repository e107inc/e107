<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function counted($token, $pem)
{
    Decoder::decode($token, $pem, ['RS256']);
    Decoder::decode($token, $pem, ['HS256']);
    $headers2 = 'taken further down';

    return $headers2;
}
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function counted($token, $pem)
{
    $headers = ['RS256'];
    Decoder::decode($token, $pem, $headers);
    $headers3 = ['HS256'];
    Decoder::decode($token, $pem, $headers3);
    $headers2 = 'taken further down';

    return $headers2;
}
