<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function scopes($tokens, $pem)
{
    $decode = fn($token) => Decoder::decode($token, $pem, ['RS256']);

    return array_map(function ($token) use ($pem) {
        return Decoder::decode($token, $pem, ['RS256']);
    }, $tokens);
}
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

function scopes($tokens, $pem)
{
    $decode = fn($token) => Decoder::decode($token, $pem, ['RS256']);

    return array_map(function ($token) use ($pem) {
        $headers = ['RS256'];
        return Decoder::decode($token, $pem, $headers);
    }, $tokens);
}
