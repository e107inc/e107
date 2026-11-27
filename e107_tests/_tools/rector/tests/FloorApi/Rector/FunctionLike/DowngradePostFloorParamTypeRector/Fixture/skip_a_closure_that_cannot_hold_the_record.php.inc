<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\DowngradePostFloorParamTypeRector\Fixture;

function validateKey(\OpenSSLAsymmetricKey $key)
{
    return $key;
}

$resize = function (\GdImage $image) {
    return $image;
};
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\DowngradePostFloorParamTypeRector\Fixture;

/**
 * @param \OpenSSLAsymmetricKey $key
 */
function validateKey($key)
{
    return $key;
}

$resize = function (\GdImage $image) {
    return $image;
};
