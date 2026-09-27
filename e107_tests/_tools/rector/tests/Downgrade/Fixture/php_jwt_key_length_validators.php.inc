<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

class JWT
{
    /**
     * Validate RSA key length
     *
     * @param \OpenSSLAsymmetricKey $key RSA key material
     * @throws \DomainException Provided key is too short
     * @return void
     */
    private static function validateRsaKeyLength(\OpenSSLAsymmetricKey $key)
    {
        openssl_pkey_get_details($key);
    }
}
-----
<?php

namespace E107\Rector\Tests\Downgrade\Fixture;

class JWT
{
    /**
     * Validate RSA key length
     *
     * @param \OpenSSLAsymmetricKey $key RSA key material
     * @throws \DomainException Provided key is too short
     * @return void
     */
    private static function validateRsaKeyLength($key)
    {
        openssl_pkey_get_details($key);
    }
}
