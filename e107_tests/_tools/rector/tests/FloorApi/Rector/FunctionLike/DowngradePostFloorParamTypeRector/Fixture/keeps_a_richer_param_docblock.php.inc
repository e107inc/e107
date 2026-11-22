<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\DowngradePostFloorParamTypeRector\Fixture;

class SomeDocumentedSigner
{
    /**
     * @param \OpenSSLAsymmetricKey $key RSA key material
     * @return void
     */
    private static function validateRsaKeyLength(\OpenSSLAsymmetricKey $key)
    {
    }
}
-----
<?php

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\DowngradePostFloorParamTypeRector\Fixture;

class SomeDocumentedSigner
{
    /**
     * @param \OpenSSLAsymmetricKey $key RSA key material
     * @return void
     */
    private static function validateRsaKeyLength($key)
    {
    }
}
