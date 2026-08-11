<?php

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream decorator trait
 *
 * @property StreamInterface $stream
 */
trait StreamDecoratorTrait
{
    /**
     * @param StreamInterface $stream Stream to decorate
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Magic method used to create a new stream if streams are not added in
     * the constructor of a decorator (e.g., LazyOpenStream).
     *
     * @return StreamInterface
     * @param string $name
     */
    public function __get($name)
    {
        if ($name === 'stream') {
            $this->stream = $this->createStream();

            return $this->stream;
        }

        throw new \UnexpectedValueException("$name not found on class");
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }

            return $this->getContents();
        } catch (\Throwable $e) {
            if (\PHP_VERSION_ID >= 70400) {
                throw $e;
            }
            trigger_error(sprintf('%s::__toString exception: %s', self::class, (string) $e), E_USER_ERROR);

            return '';
        } catch (\Exception $e) {
            if (\PHP_VERSION_ID >= 70400) {
                throw $e;
            }
            trigger_error(sprintf('%s::__toString exception: %s', self::class, (string) $e), E_USER_ERROR);
            return '';
        }
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return Utils::copyToString($this);
    }

    /**
     * Allow decorators to implement custom methods
     *
     * @return mixed
     * @param string $method
     */
    public function __call($method, array $args)
    {
        /** @var callable $callable */
        $callable = [$this->stream, $method];
        $result = call_user_func($callable, ...$args);

        // Always return the wrapped object if the result is a return $this
        return $result === $this->stream ? $this : $result;
    }

    /**
     * @return void
     */
    public function close()
    {
        $this->stream->close();
    }

    /**
     * @return mixed
     */
    public function getMetadata($key = null)
    {
        return $this->stream->getMetadata($key);
    }

    public function detach()
    {
        return $this->stream->detach();
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->stream->getSize();
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return $this->stream->eof();
    }

    /**
     * @return int
     */
    public function tell()
    {
        return $this->stream->tell();
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return $this->stream->isReadable();
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return $this->stream->isWritable();
    }

    /**
     * @return bool
     */
    public function isSeekable()
    {
        return $this->stream->isSeekable();
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * @return void
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->stream->seek($offset, $whence);
    }

    /**
     * @return string
     */
    public function read($length)
    {
        return $this->stream->read($length);
    }

    /**
     * @return int
     */
    public function write($string)
    {
        return $this->stream->write($string);
    }

    /**
     * Implement in subclasses to dynamically create streams when requested.
     *
     * @throws \BadMethodCallException
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function createStream()
    {
        throw new \BadMethodCallException('Not implemented');
    }
}
