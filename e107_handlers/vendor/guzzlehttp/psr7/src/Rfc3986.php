<?php

namespace GuzzleHttp\Psr7;

/**
 * @internal
 */
final class Rfc3986
{
    /**
     * Sub-delims for use in a regex.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc3986#section-2.2
     */
    const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

    /**
     * Unreserved characters for use in a regex.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc3986#section-2.3
     */
    const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';
}
