<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\DowngradePostFloorParamTypeRector\Fixture;

class SomeSigner
{
    private static function validateRsaKeyLength(\OpenSSLAsymmetricKey $key)
    {
    }

    public function resize(?\GdImage $image, int $width)
    {
    }
}
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\DowngradePostFloorParamTypeRector\Fixture;

class SomeSigner
{
    /**
     * @param \OpenSSLAsymmetricKey $key
     */
    private static function validateRsaKeyLength($key)
    {
    }

    /**
     * @param \GdImage|null $image
     */
    public function resize($image, int $width)
    {
    }
}
