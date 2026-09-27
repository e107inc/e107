<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function subjects($token, $pem, $legacy)
{
    if ($legacy && Decoder::decode($token, $pem, ['RS256'])) {
        return 1;
    } elseif (Decoder::decode($token, $pem, ['HS256'])) {
        return 2;
    }

    echo $legacy ? Decoder::decode($token, $pem, ['RS256']) : '';

    switch (Decoder::decode($token, $pem, [])) {
        case Decoder::decode($token, $pem, [true]):
            break;
    }

    foreach (Decoder::decode($token, $pem, [null]) as $claim) {
    }
}
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function subjects($token, $pem, $legacy)
{
    $headers = ['RS256'];
    $headers2 = ['HS256'];
    if ($legacy && Decoder::decode($token, $pem, $headers)) {
        return 1;
    } elseif (Decoder::decode($token, $pem, $headers2)) {
        return 2;
    }
    $headers3 = ['RS256'];

    echo $legacy ? Decoder::decode($token, $pem, $headers3) : '';
    $headers4 = [];
    $headers5 = [true];

    switch (Decoder::decode($token, $pem, $headers4)) {
        case Decoder::decode($token, $pem, $headers5):
            break;
    }
    $headers6 = [null];

    foreach (Decoder::decode($token, $pem, $headers6) as $claim) {
    }
}
