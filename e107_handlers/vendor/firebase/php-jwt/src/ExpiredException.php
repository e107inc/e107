<?php

namespace Firebase\JWT;

class ExpiredException extends \UnexpectedValueException implements JWTExceptionWithPayloadInterface
{
    /**
     * @var object
     */
    private $payload;

    /**
     * @param object $payload
     * @return void
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return object
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
