<?php

namespace E107\Rector\Tests\Php86\Rector\FuncCall\IsAOfAClassNameToInstanceofRector\Fixture;

class SkipWhatInstanceofCannotSay
{
    const NAME = 'Imagick';

    public function cases($value)
    {
        return [
            is_a($value, 'Imagick', true),
            is_a($value, $value),
            is_a($value, self::class),
            is_a($value, 'not a class'),
            is_a(object_or_class: $value, class: 'Imagick'),
            is_a('Imagick', 'Imagick'),
            is_a(null, 'Imagick'),
            is_a(PHP_EOL, 'Imagick'),
            is_a(array(), 'Imagick'),
            is_a(self::NAME, 'Imagick'),
            is_a(strlen('a'), 'Imagick'),
            is_a(self::NAME[0], 'Imagick'),
            is_a($value, 'self'),
            is_a($value, 'Static'),
            is_a($value, 'parent'),
            is_a($value, 'int'),
            is_a($value, 'Foo\\List\\Bar'),
            is_a($value, '\\Foo\\Function\\Bar'),
        ];
    }
}
