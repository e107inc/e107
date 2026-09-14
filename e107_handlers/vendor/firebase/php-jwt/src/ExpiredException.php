<?php

namespace Firebase\JWT;

class ExpiredException extends \UnexpectedValueException implements JWTExceptionWithPayloadInterface
{
    /**
     * @var object
     */
    private $payload;

    /**
     * @var int|null
     */
    private $timestamp;

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

    /**
     * @return void
     * @param int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return int|null
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
