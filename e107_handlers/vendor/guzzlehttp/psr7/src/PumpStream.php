<?php

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Provides a read only stream that pumps data from a PHP callable.
 *
 * When invoking the provided callable, the PumpStream will pass the suggested
 * number of bytes to read to the callable. The callable can choose to ignore
 * this value and return fewer or more bytes than requested. Any extra data
 * returned by the callable is buffered internally until drained using the
 * read() function of the PumpStream. The callable MUST return false or null
 * when there is no more data to read.
 *
 * Userland callables that declare no parameters are tolerated by PHP, but
 * length-aware callables remain the recommended formal shape.
 */
final class PumpStream implements StreamInterface
{
    /** @var callable|null */
    private $source;

    /** @var int|null */
    private $size;

    /** @var int */
    private $tellPos = 0;

    /** @var array */
    private $metadata;

    /** @var BufferStream */
    private $buffer;

    /**
     * @param (callable(): (string|false|null))|(callable(int): (string|false|null)) $source  Source of the stream data. The callable receives
     *                                                                                        the suggested number of bytes to read, may ignore
     *                                                                                        that value, and may return fewer or more bytes.
     *                                                                                        Extra bytes are buffered. The callable MUST return
     *                                                                                        a string when called, or false|null on error or EOF.
     *                                                                                        Userland callables that declare no parameters are
     *                                                                                        tolerated by PHP, but length-aware callables remain
     *                                                                                        the recommended formal shape.
     * @param array{size?: int, metadata?: array}                                    $options Stream options:
     *                                                                                        - metadata: Hash of metadata to use with stream.
     *                                                                                        - size: Size of the stream, if known.
     */
    public function __construct(callable $source, array $options = [])
    {
        $this->source = $source;
        $this->size = isset($options['size']) ? $options['size'] : null;
        $this->metadata = isset($options['metadata']) ? $options['metadata'] : [];
        $this->buffer = new BufferStream();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return Utils::copyToString($this);
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
     * @return void
     */
    public function close()
    {
        $this->detach();
    }

    public function detach()
    {
        $this->tellPos = 0;
        $this->source = null;

        return null;
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function tell()
    {
        return $this->tellPos;
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return $this->source === null;
    }

    /**
     * @return bool
     */
    public function isSeekable()
    {
        return false;
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
        if (!\is_int($offset)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to StreamInterface::seek() is deprecated; guzzlehttp/psr7 3.0 requires int for $offset.',
                \get_debug_type($offset)
            );
        }

        if (!\is_int($whence)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to StreamInterface::seek() is deprecated; guzzlehttp/psr7 3.0 requires int for $whence.',
                \get_debug_type($whence)
            );
        }

        throw new \RuntimeException('Cannot seek a PumpStream');
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * @return int
     */
    public function write($string)
    {
        if (!\is_string($string)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to StreamInterface::write() is deprecated; guzzlehttp/psr7 3.0 requires string for $string.',
                \get_debug_type($string)
            );
        }

        throw new \RuntimeException('Cannot write to a PumpStream');
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * @return string
     */
    public function read($length)
    {
        if (!\is_int($length)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to StreamInterface::read() is deprecated; guzzlehttp/psr7 3.0 requires int for $length.',
                \get_debug_type($length)
            );
        }

        $data = $this->buffer->read($length);
        $readLen = strlen($data);
        $this->tellPos += $readLen;
        $remaining = $length - $readLen;

        if ($remaining) {
            $this->pump($remaining);
            $data .= $this->buffer->read($remaining);
            $this->tellPos += strlen($data) - $readLen;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        $result = '';
        while (!$this->eof()) {
            $result .= $this->read(1000000);
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getMetadata($key = null)
    {
        if ($key !== null && !\is_string($key)) {
            \trigger_deprecation(
                'guzzlehttp/psr7',
                '2.11',
                'Passing %s to StreamInterface::getMetadata() is deprecated; guzzlehttp/psr7 3.0 requires string|null for $key.',
                \get_debug_type($key)
            );
        }

        if (!$key) {
            return $this->metadata;
        }

        return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }

    /**
     * @return void
     * @param int $length
     */
    private function pump($length)
    {
        if ($this->source !== null) {
            do {
                $data = call_user_func($this->source, $length);
                if ($data === false || $data === null) {
                    $this->source = null;

                    return;
                }
                $this->buffer->write($data);
                $length -= strlen($data);
            } while ($length > 0);
        }
    }
}
