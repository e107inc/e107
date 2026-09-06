<?php

namespace GuzzleHttp\Psr7;

use Psr\Http\Message\StreamInterface;

/**
 * Stream that when read returns bytes for a streaming multipart or
 * multipart/form-data stream.
 */
final class MultipartStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /** @var string */
    private $boundary;

    /** @var StreamInterface */
    private $stream;

    /**
     * @param array  $elements Array of associative arrays, each containing a
     *                         required "name" key mapping to the form field,
     *                         name, a required "contents" key mapping to a
     *                         StreamInterface/resource/string, an optional
     *                         "headers" associative array of custom headers,
     *                         and an optional "filename" key mapping to a
     *                         string to send as the filename in the part.
     * @param string $boundary You can optionally provide a specific boundary
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $elements = [], $boundary = null)
    {
        $this->boundary = $boundary ?: bin2hex(random_bytes(20));
        $this->stream = $this->createStream($elements);
    }

    /**
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * Get the headers needed before transferring the content of a POST file
     *
     * @param string[] $headers
     * @return string
     */
    private function getHeaders(array $headers)
    {
        $str = '';
        foreach ($headers as $key => $value) {
            $str .= "{$key}: {$value}\r\n";
        }

        return "--{$this->boundary}\r\n".trim($str)."\r\n\r\n";
    }

    /**
     * Create the aggregate stream that will be used to upload the POST data
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function createStream(array $elements = [])
    {
        $stream = new AppendStream();

        foreach ($elements as $element) {
            if (!is_array($element)) {
                throw new \UnexpectedValueException('An array is expected');
            }
            $this->addElement($stream, $element);
        }

        // Add the trailing boundary with CRLF
        $stream->addStream(Utils::streamFor("--{$this->boundary}--\r\n"));

        return $stream;
    }

    /**
     * @return void
     */
    private function addElement(AppendStream $stream, array $element)
    {
        foreach (['contents', 'name'] as $key) {
            if (!array_key_exists($key, $element)) {
                throw new \InvalidArgumentException("A '{$key}' key is required");
            }
        }

        $element['contents'] = Utils::streamFor($element['contents']);

        if (empty($element['filename'])) {
            $uri = $element['contents']->getMetadata('uri');
            if ($uri && \is_string($uri) && \substr($uri, 0, 6) !== 'php://' && \substr($uri, 0, 7) !== 'data://') {
                $element['filename'] = $uri;
            }
        }

        list($body, $headers) = $this->createElement(
            $element['name'],
            $element['contents'],
            isset($element['filename']) ? $element['filename'] : null,
            isset($element['headers']) ? $element['headers'] : []
        );

        $stream->addStream(Utils::streamFor($this->getHeaders($headers)));
        $stream->addStream($body);
        $stream->addStream(Utils::streamFor("\r\n"));
    }

    /**
     * @param string[] $headers
     *
     * @return array{0: StreamInterface, 1: string[]}
     * @param string|null $filename
     * @param string $name
     */
    private function createElement($name, StreamInterface $stream, $filename, array $headers)
    {
        // Set a default content-disposition header if one was no provided
        $disposition = self::getHeader($headers, 'content-disposition');
        if (!$disposition) {
            $headers['Content-Disposition'] = ($filename === '0' || $filename)
                ? sprintf(
                    'form-data; name="%s"; filename="%s"',
                    $name,
                    basename($filename)
                )
                : "form-data; name=\"{$name}\"";
        }

        // Set a default content-length header if one was no provided
        $length = self::getHeader($headers, 'content-length');
        if (!$length) {
            if ($length = $stream->getSize()) {
                $headers['Content-Length'] = (string) $length;
            }
        }

        // Set a default Content-Type if one was not supplied
        $type = self::getHeader($headers, 'content-type');
        if (!$type && ($filename === '0' || $filename)) {
            $headers['Content-Type'] = MimeType::fromFilename($filename) !== null ? MimeType::fromFilename($filename) : 'application/octet-stream';
        }

        return [$stream, $headers];
    }

    /**
     * @param string[] $headers
     * @return string|null
     * @param string $key
     */
    private static function getHeader(array $headers, $key)
    {
        $lowercaseHeader = strtolower($key);
        foreach ($headers as $k => $v) {
            if (strtolower((string) $k) === $lowercaseHeader) {
                return $v;
            }
        }

        return null;
    }
}
