<?php

namespace Firebase\JWT;

use InvalidArgumentException;
use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use TypeError;

class Key
{
    /**
     * @var string|OpenSSLAsymmetricKey|OpenSSLCertificate
     */
    private $keyMaterial;
    /**
     * @var string
     */
    private $algorithm;
    /**
     * @param string|OpenSSLAsymmetricKey|OpenSSLCertificate $keyMaterial
     * @param string $algorithm
     */
    public function __construct(
        $keyMaterial,
        $algorithm
    ) {
        $this->keyMaterial = $keyMaterial;
        $this->algorithm = $algorithm;
        if (
            !\is_string($keyMaterial)
            && !$keyMaterial instanceof OpenSSLAsymmetricKey
            && !(is_resource($keyMaterial) || $keyMaterial instanceof OpenSSLCertificate)
        ) {
            throw new TypeError('Key material must be a string, OpenSSLCertificate, or OpenSSLAsymmetricKey');
        }

        if (empty($keyMaterial)) {
            throw new InvalidArgumentException('Key material must not be empty');
        }

        if (empty($algorithm)) {
            throw new InvalidArgumentException('Algorithm must not be empty');
        }
    }

    /**
     * Return the algorithm valid for this key
     *
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * @return string|OpenSSLAsymmetricKey|OpenSSLCertificate
     */
    public function getKeyMaterial()
    {
        return $this->keyMaterial;
    }
}
