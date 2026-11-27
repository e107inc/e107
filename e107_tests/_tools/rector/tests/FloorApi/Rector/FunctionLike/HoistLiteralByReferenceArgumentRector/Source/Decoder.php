<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector\Source;

class Decoder
{
    const ALGORITHM = 'RS256';

    public function __construct(&$options = null)
    {
    }

    public static function decode($token, $key, &$headers = null)
    {
        return $token;
    }

    public function read($token, &$claims)
    {
        return $token;
    }

    public static function collect(&...$buckets)
    {
    }
}
