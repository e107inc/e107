<?php

namespace E107\Rector\Tests\DowngradePhp81\Rector\FunctionLike\NameNullForSkippedNewInInitializerRector\Fixture;

enum Suit: string
{
    case Hearts = 'h';
}

class Anchor
{
}

const DEFAULT_ANCHOR = new Anchor();

class SkipDefaultsWithoutANew
{
    const FIRST = 1;

    public static function make($first = self::FIRST, Suit $suit = Suit::Hearts, Anchor $anchor = DEFAULT_ANCHOR, $last = null)
    {
        return $suit;
    }

    public static function call()
    {
        return self::make(last: 3);
    }
}
