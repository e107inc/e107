<?php

declare(strict_types=1);

namespace E107\PhpStan;

/**
 * The global constants e107 defines at run time, read with the tokenizer: literal define() names in any scope and the keys a language file returns to {@see \e107::includeLan()}.
 *
 * @phpstan-type Token array{0: int, 1: string, 2: int}|string
 */
final class DefinedConstants
{
    private const IDENTIFIER = '/^[A-Za-z_\x80-\xff][A-Za-z0-9_\x80-\xff]*\z/';

    private const NOT_A_CALL = [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW, T_CONST];

    private const INSIGNIFICANT = [T_WHITESPACE => true, T_COMMENT => true, T_DOC_COMMENT => true, T_OPEN_TAG => true, T_CLOSE_TAG => true, T_INLINE_HTML => true];

    /** @var array<string, array<string, true>> */
    private array $types = [];

    /** Reads one file; $path is relative to the checkout, and decides whether a returned array defines constants. */
    public function read(string $path, string $source): void
    {
        $isLanguageFile = self::isLanguageFile($path);
        if (!$isLanguageFile && stripos($source, 'define') === false) {
            return;
        }
        $tokens = self::significant($source);
        $this->readDefines($tokens);
        if ($isLanguageFile) {
            $this->readReturnedKeys($tokens);
        }
    }

    /** @return array<string, string> every name read so far, sorted, with the PHPStan type of every value it was given. */
    public function types(): array
    {
        $types = [];
        foreach ($this->types as $name => $seen) {
            $types[$name] = isset($seen['mixed']) ? 'mixed' : implode('|', array_keys($seen));
        }
        ksort($types, SORT_STRING);

        return $types;
    }

    private static function isLanguageFile(string $path): bool
    {
        $segments = explode('/', $path);

        return $segments[0] === 'e107_languages' || in_array('languages', array_slice($segments, 0, -1), true);
    }

    /** @return list<Token> */
    private static function significant(string $source): array
    {
        $kept = [];
        foreach (token_get_all($source) as $token) {
            if (!is_array($token) || !isset(self::INSIGNIFICANT[$token[0]])) {
                $kept[] = $token;
            }
        }

        return $kept;
    }

    /** @param list<Token> $tokens */
    private function readDefines(array $tokens): void
    {
        foreach ($tokens as $i => $token) {
            if (!is_array($token) || !self::isDefineCall($tokens, $i)) {
                continue;
            }
            $name = $tokens[$i + 2] ?? null;
            if (!is_array($name) || $name[0] !== T_CONSTANT_ENCAPSED_STRING || ($tokens[$i + 3] ?? null) !== ',') {
                continue;
            }
            $this->add(substr($name[1], 1, -1), self::typeOf(self::argumentAt($tokens, $i + 4)));
        }
    }

    /** @param list<Token> $tokens */
    private static function isDefineCall(array $tokens, int $i): bool
    {
        $token = $tokens[$i];
        if (!is_array($token) || ($tokens[$i + 1] ?? null) !== '(') {
            return false;
        }
        $isDefine = ($token[0] === T_STRING && strcasecmp($token[1], 'define') === 0)
            || ($token[0] === T_NAME_FULLY_QUALIFIED && strcasecmp($token[1], '\define') === 0);
        $before = $tokens[$i - 1] ?? null;

        return $isDefine && !(is_array($before) && in_array($before[0], self::NOT_A_CALL, true));
    }

    /**
     * @param list<Token> $tokens
     * @return list<Token> the tokens of the argument starting at $start
     */
    private static function argumentAt(array $tokens, int $start): array
    {
        $argument = [];
        $depth = 0;
        for ($i = $start, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];
            if ($depth === 0 && ($token === ',' || self::nesting($token) < 0)) {
                break;
            }
            $depth += self::nesting($token);
            $argument[] = $token;
        }

        return $argument;
    }

    /** @param list<Token> $argument */
    private static function typeOf(array $argument): string
    {
        if (count($argument) === 2 && ($argument[0] === '-' || $argument[0] === '+')) {
            array_shift($argument);
        }
        if (count($argument) !== 1 || !is_array($argument[0])) {
            return 'mixed';
        }
        [$id, $text] = $argument[0];

        return match (true) {
            $id === T_CONSTANT_ENCAPSED_STRING => 'string',
            $id === T_LNUMBER => 'int',
            $id === T_DNUMBER => 'float',
            $id === T_STRING && in_array(strtolower($text), ['true', 'false'], true) => 'bool',
            $id === T_STRING && strtolower($text) === 'null' => 'null',
            default => 'mixed',
        };
    }

    /** @param list<Token> $tokens */
    private function readReturnedKeys(array $tokens): void
    {
        $depth = 0;
        foreach ($tokens as $i => $token) {
            if ($depth === 0 && is_array($token) && $token[0] === T_RETURN) {
                $open = self::arrayOpenedAt($tokens, $i + 1);
                if ($open !== null) {
                    $this->readKeys($tokens, $open);
                }
            }
            $depth += self::nesting($token);
        }
    }

    /**
     * @param list<Token> $tokens
     * @return int|null the index of the bracket that opens the array literal starting at $i
     */
    private static function arrayOpenedAt(array $tokens, int $i): ?int
    {
        $token = $tokens[$i] ?? null;
        if ($token === '[') {
            return $i;
        }
        if (is_array($token) && $token[0] === T_ARRAY && ($tokens[$i + 1] ?? null) === '(') {
            return $i + 1;
        }

        return null;
    }

    /** @param list<Token> $tokens */
    private function readKeys(array $tokens, int $open): void
    {
        $depth = 0;
        for ($i = $open, $count = count($tokens); $i < $count; $i++) {
            $token = $tokens[$i];
            $depth += self::nesting($token);
            if ($depth === 0) {
                return;
            }
            if ($depth === 1 && is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING
                && is_array($tokens[$i + 1] ?? null) && $tokens[$i + 1][0] === T_DOUBLE_ARROW) {
                $this->add(substr($token[1], 1, -1), self::typeOf(self::argumentAt($tokens, $i + 2)));
            }
        }
    }

    /** @param Token $token */
    private static function nesting(array|string $token): int
    {
        if ($token === '(' || $token === '[' || $token === '{' || (is_array($token) && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) {
            return 1;
        }

        return ($token === ')' || $token === ']' || $token === '}') ? -1 : 0;
    }

    private function add(string $name, string $type): void
    {
        if (preg_match(self::IDENTIFIER, $name) === 1) {
            $this->types[$name][$type] = true;
        }
    }
}
