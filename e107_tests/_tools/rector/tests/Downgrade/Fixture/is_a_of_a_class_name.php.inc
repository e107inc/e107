<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

class IsAOfAClassName
{
    public $data;

    public function isImagick()
    {
        return is_a($this->data, 'Imagick');
    }

    public function isImage()
    {
        return is_a($this->data, '\Intervention\Image\Image');
    }

    public function isNotCollection($data)
    {
        return !is_a($data, 'Collection');
    }

    public function isLaravel()
    {
        return function_exists('app') && is_a($app = app(), 'Illuminate\Foundation\Application');
    }
}
-----
<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

class IsAOfAClassName
{
    public $data;

    public function isImagick()
    {
        return $this->data instanceof \Imagick;
    }

    public function isImage()
    {
        return $this->data instanceof \Intervention\Image\Image;
    }

    public function isNotCollection($data)
    {
        return !$data instanceof \Collection;
    }

    public function isLaravel()
    {
        return function_exists('app') && ($app = app()) instanceof \Illuminate\Foundation\Application;
    }
}
