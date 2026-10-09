<?php
namespace Test;

/**
 * Reads token_get_all() output for the tests that read source: what is next to a token past whitespace and comments, and what a token declares or calls.
 */
final class Tokens
{
	/**
	 * @param array|string $token
	 * @return bool whether the token is neither whitespace nor a comment
	 */
	public static function isCode($token)
	{
		return !is_array($token) || !in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true);
	}

	/**
	 * @param array|string|null $token
	 * @return int|string|null the token's id, or the character a one-character token is
	 */
	public static function id($token)
	{
		return is_array($token) ? $token[0] : $token;
	}

	/**
	 * @param array|string $token
	 * @return bool whether the token opens a brace, counting the `{$` and `${` that open one inside a string
	 */
	public static function opensBrace($token)
	{
		return $token === '{' || (is_array($token) && in_array($token[0], array(T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES), true));
	}

	/**
	 * @param array $tokens
	 * @param int $i
	 * @param int $direction 1 for the next token, -1 for the previous
	 * @return array|string|null the nearest token that is not whitespace or a comment
	 */
	public static function neighbour(array $tokens, $i, $direction)
	{
		$index = self::neighbourIndex($tokens, $i, $direction);

		return $index === null ? null : $tokens[$index];
	}

	/**
	 * @param array $tokens
	 * @param int $i
	 * @param int $direction 1 for the next token, -1 for the previous
	 * @return int|null the index of {@see Tokens::neighbour()}
	 */
	public static function neighbourIndex(array $tokens, $i, $direction)
	{
		for ($j = $i + $direction; isset($tokens[$j]); $j += $direction)
		{
			if (self::isCode($tokens[$j]))
			{
				return $j;
			}
		}

		return null;
	}

	/**
	 * @param array $tokens
	 * @param int $i index of a class or function keyword
	 * @return string|null the name it declares, a method's even when it is a keyword such as list; null for `::class`, an anonymous class or a closure
	 */
	public static function declaredName(array $tokens, $i)
	{
		$name = self::neighbourIndex($tokens, $i, 1);

		if ($name !== null && (is_array($tokens[$name]) ? $tokens[$name][1] : $tokens[$name]) === '&')
		{
			$name = self::neighbourIndex($tokens, $name, 1);
		}

		if ($name === null || !is_array($tokens[$name]))
		{
			return null;
		}

		return $tokens[$name][0] === T_STRING || ($tokens[$i][0] === T_FUNCTION && self::neighbour($tokens, $name, 1) === '(') ? $tokens[$name][1] : null;
	}

	/**
	 * @param array $tokens
	 * @param int $i index of a name token
	 * @return bool whether $tokens[$i] is a method called on a variable other than $this, which is how a Cest reaches a module
	 */
	public static function isCalledOnAnActor(array $tokens, $i)
	{
		$arrow = self::neighbourIndex($tokens, $i, -1);

		if ($arrow === null || !is_array($tokens[$arrow]) || $tokens[$arrow][0] !== T_OBJECT_OPERATOR)
		{
			return false;
		}

		$object = self::neighbour($tokens, $arrow, -1);

		return is_array($object) && $object[0] === T_VARIABLE && $object[1] !== '$this';
	}
}
