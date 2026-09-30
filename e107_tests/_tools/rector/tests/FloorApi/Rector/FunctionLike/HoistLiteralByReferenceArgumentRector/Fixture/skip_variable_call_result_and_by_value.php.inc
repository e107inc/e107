<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Fixture;

use E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source\Decoder;

class Skipped
{
    private $headers = [];

    private static $shared = [];

    public function skipped($token, $pem, $unknown, $list)
    {
        $headers = [];
        Decoder::decode($token, $pem, $headers);
        Decoder::decode($token, $pem, $headers['alg']);
        Decoder::decode($token, $pem, $this->headers);
        Decoder::decode($token, $pem, self::$shared);
        Decoder::decode($token, ['RS256']);
        Decoder::decode($token, $pem, headers: ['RS256']);
        Decoder::decode(...[$token, $pem, ['RS256']]);
        Decoder::collect(['a'], ['b']);
        end(explode(',', $list));
        $unknown->decode($token, $pem, ['RS256']);
    }
}
