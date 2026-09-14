<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Polyfill\Php80;

/**
 * @author Fedonyuk Anton <info@ensostudio.ru>
 *
 * @internal
 */
class PhpToken
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $text;
    /**
     * @var -1|positive-int
     */
    public $line;
    /**
     * @var int
     */
    public $pos;
    /**
     * @param -1|positive-int $line
     * @param int $id
     * @param string $text
     * @param int $position
     */
    public function __construct($id, $text, $line = -1, $position = -1)
    {
        $this->id = $id;
        $this->text = $text;
        $this->line = $line;
        $this->pos = $position;
    }
    /**
     * @return string|null
     */
    public function getTokenName()
    {
        if ('UNKNOWN' === $name = token_name($this->id)) {
            $name = \strlen($this->text) > 1 || \ord($this->text) < 32 ? null : $this->text;
        }

        return $name;
    }
    /**
     * @param int|string|array $kind
     * @return bool
     */
    public function is($kind)
    {
        foreach ((array) $kind as $value) {
            if (\in_array($value, [$this->id, $this->text], true)) {
                return true;
            }
        }

        return false;
    }
    /**
     * @return bool
     */
    public function isIgnorable()
    {
        return \in_array($this->id, [\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT, \T_OPEN_TAG], true);
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->text;
    }
    /**
     * @return list<static>
     * @param string $code
     * @param int $flags
     */
    public static function tokenize($code, $flags = 0)
    {
        $line = 1;
        $position = 0;
        $tokens = token_get_all($code, $flags);
        foreach ($tokens as $index => $token) {
            if (\is_string($token)) {
                $id = \ord($token);
                $text = $token;
            } else {
                list($id, $text, $line) = $token;
            }
            $tokens[$index] = new static($id, $text, $line, $position);
            $position += \strlen($text);
        }

        return $tokens;
    }
}
