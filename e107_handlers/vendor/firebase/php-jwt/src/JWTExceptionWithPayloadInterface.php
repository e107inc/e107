<?php
namespace Firebase\JWT;

interface JWTExceptionWithPayloadInterface
{
    /**
     * Get the payload that caused this exception.
     *
     * @return object
     */
    public function getPayload();

    /**
     * Get the payload that caused this exception.
     *
     * @param object $payload
     * @return void
     */
    public function setPayload($payload);
}
