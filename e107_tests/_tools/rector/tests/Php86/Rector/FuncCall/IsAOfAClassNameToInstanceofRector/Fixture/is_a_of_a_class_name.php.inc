<?php

namespace E107\Rector\Tests\Php86\Rector\FuncCall\IsAOfAClassNameToInstanceofRector\Fixture;

class IsAOfAClassName
{
    public $data;

    public static $cache;

    public function cases($value)
    {
        return [
            is_a($this->data, 'Imagick'),
            is_a($this->data, '\Intervention\Image\Image'),
            is_a($value, \SplFileInfo::class),
            is_a($value, 'Collection', false),
            is_a($app = $this->make(), 'Illuminate\Foundation\Application'),
            is_a($value['key'], 'Imagick'),
            is_a(self::$cache, 'Imagick'),
            is_a(self::make(), 'Imagick'),
            !is_a($value, 'Imagick'),
        ];
    }

    public static function make()
    {
        return null;
    }
}
-----
<?php

namespace E107\Rector\Tests\Php86\Rector\FuncCall\IsAOfAClassNameToInstanceofRector\Fixture;

class IsAOfAClassName
{
    public $data;

    public static $cache;

    public function cases($value)
    {
        return [
            $this->data instanceof \Imagick,
            $this->data instanceof \Intervention\Image\Image,
            $value instanceof \SplFileInfo,
            $value instanceof \Collection,
            ($app = $this->make()) instanceof \Illuminate\Foundation\Application,
            $value['key'] instanceof \Imagick,
            self::$cache instanceof \Imagick,
            self::make() instanceof \Imagick,
            !$value instanceof \Imagick,
        ];
    }

    public static function make()
    {
        return null;
    }
}
