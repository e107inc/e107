<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Source;

/**
 * Reads literal values out of PHP source without running it.
 *
 * Covers string literals, heredocs and nowdocs, numbers, array literals and
 * concatenation. A subclass decides what a bare constant, a concatenation and
 * an interpolated variable become through {@see LiteralReader::constantTerm()},
 * {@see LiteralReader::concat()} and {@see LiteralReader::interpolation()}.
 */
abstract class LiteralReader
{
	/**
	 * @var array
	 */
	protected $tokens = array();

	/**
	 * @var int
	 */
	protected $count = 0;

	/**
	 * The value of a bare constant in an expression.
	 *
	 * @param string $name
	 * @return mixed
	 * @throws \RuntimeException when the constant may not appear here
	 */
	abstract protected function constantTerm($name);

	/**
	 * @param mixed $left
	 * @param mixed $right
	 * @return mixed
	 * @throws \RuntimeException
	 */
	protected function concat($left, $right)
	{
		return $left.$right;
	}

	/**
	 * Met for each interpolated part of a heredoc or double-quoted string.
	 *
	 * @param int $pos
	 * @return void
	 * @throws \RuntimeException when interpolation may not appear here
	 */
	protected function interpolation($pos)
	{
	}

	/**
	 * @param string $source
	 * @return void
	 */
	protected function tokenize($source)
	{
		$this->tokens = @token_get_all($source);
		$this->count = count($this->tokens);
	}

	/**
	 * Declarations whose body runs only when called.
	 *
	 * @param int $id
	 * @return bool
	 */
	protected function opensOwnScope($id)
	{
		if($id === T_FUNCTION || $id === T_CLASS || $id === T_INTERFACE)
		{
			return true;
		}

		return defined('T_TRAIT') && $id === T_TRAIT;
	}

	/**
	 * Advance past a declaration, body included.
	 *
	 * @param int $pos offset of the T_FUNCTION/T_CLASS token
	 * @return int offset of the first token after the declaration
	 */
	protected function skipScope($pos)
	{
		$pos++;

		while($pos < $this->count)
		{
			$text = $this->text($pos);

			if($text === ';') // abstract method or interface signature
			{
				return $pos + 1;
			}

			if($text === '{')
			{
				return $this->matchBrace($pos);
			}

			$pos++;
		}

		return $pos;
	}

	/**
	 * @param int $pos offset of an opening brace
	 * @return int offset of the first token after its partner
	 */
	protected function matchBrace($pos)
	{
		$depth = 0;

		while($pos < $this->count)
		{
			$text = $this->text($pos);

			if($text === '{' || $this->isId($pos, T_CURLY_OPEN) || $this->isId($pos, T_DOLLAR_OPEN_CURLY_BRACES))
			{
				$depth++;
			}
			elseif($text === '}')
			{
				$depth--;

				if($depth === 0)
				{
					return $pos + 1;
				}
			}

			$pos++;
		}

		return $pos;
	}

	/**
	 * @param int $from first token of the expression
	 * @return int offset of the terminating semicolon, or of the last token
	 */
	protected function endOfStatement($from)
	{
		return $this->endOfExpression($from, array(';', T_CLOSE_TAG));
	}

	/**
	 * @param int   $from  first token of the expression
	 * @param array $stops characters and token ids that end it outside any bracket
	 * @return int offset of the stop, or of the last token
	 */
	protected function endOfExpression($from, array $stops)
	{
		$depth = 0;

		for($pos = $from; $pos < $this->count; $pos++)
		{
			if($depth <= 0 && $this->isStop($pos, $stops))
			{
				return $pos;
			}

			$text = $this->text($pos);

			if($text === '(' || $text === '[' || $text === '{'
				|| $this->isId($pos, T_CURLY_OPEN) || $this->isId($pos, T_DOLLAR_OPEN_CURLY_BRACES))
			{
				$depth++;
			}
			elseif($text === ')' || $text === ']' || $text === '}')
			{
				$depth--;
			}
		}

		return $this->count - 1;
	}

	/**
	 * @param int   $pos
	 * @param array $stops
	 * @return bool
	 */
	private function isStop($pos, array $stops)
	{
		$token = $this->tokens[$pos];

		return in_array(is_array($token) ? $token[0] : $token, $stops, true);
	}

	/**
	 * Fold a token range into one value.
	 *
	 * @param int $from
	 * @param int $to exclusive
	 * @return mixed
	 * @throws \RuntimeException when the range leaves the supported grammar
	 */
	protected function reduce($from, $to)
	{
		$pos = $this->nextSignificant($from, $to);

		if($pos === null)
		{
			throw new \RuntimeException('empty expression');
		}

		$value = $this->readValue($pos, $after);
		$after = $this->nextSignificant($after, $to);

		if($after !== null)
		{
			throw new \RuntimeException('trailing tokens');
		}

		return $value;
	}

	/**
	 * A value is an array literal or a chain of concatenated terms.
	 *
	 * @param int $pos
	 * @param int $after set to the offset after the value
	 * @return mixed
	 * @throws \RuntimeException
	 */
	protected function readValue($pos, &$after)
	{
		if($this->isId($pos, T_ARRAY) || $this->text($pos) === '[')
		{
			return $this->readArray($pos, $after);
		}

		$value = $this->readTerm($pos, $after);
		$next = $this->nextSignificant($after);

		while($next !== null && $this->text($next) === '.')
		{
			$operand = $this->nextSignificant($next + 1);

			if($operand === null)
			{
				throw new \RuntimeException('dangling concatenation');
			}

			$value = $this->concat($value, $this->readTerm($operand, $after));
			$next = $this->nextSignificant($after);
		}

		$after = $next === null ? $this->count : $next;

		return $value;
	}

	/**
	 * @param int $pos offset of `array` or `[`
	 * @param int $after set to the offset after the closing bracket
	 * @return array
	 * @throws \RuntimeException
	 */
	protected function readArray($pos, &$after)
	{
		if($this->isId($pos, T_ARRAY))
		{
			$pos = $this->nextSignificant($pos + 1);

			if($pos === null || $this->text($pos) !== '(')
			{
				throw new \RuntimeException("expected '('");
			}

			$close = ')';
		}
		else
		{
			$close = ']';
		}

		$pos = $this->nextSignificant($pos + 1);
		$result = array();

		while(true)
		{
			if($pos === null)
			{
				throw new \RuntimeException('unterminated array');
			}

			if($this->text($pos) === $close)
			{
				$after = $pos + 1;
				return $result;
			}

			$first = $this->readValue($pos, $next);
			$next = $this->nextSignificant($next);

			if($next !== null && $this->isId($next, T_DOUBLE_ARROW))
			{
				$valueAt = $this->nextSignificant($next + 1);

				if($valueAt === null)
				{
					throw new \RuntimeException('missing array value');
				}

				if(!is_scalar($first))
				{
					throw new \RuntimeException('unsupported array key');
				}

				$result[$first] = $this->readValue($valueAt, $next);
				$next = $this->nextSignificant($next);
			}
			else
			{
				$result[] = $first;
			}

			if($next !== null && $this->text($next) === ',')
			{
				$pos = $this->nextSignificant($next + 1);
				continue;
			}

			if($next === null || $this->text($next) !== $close)
			{
				throw new \RuntimeException("expected ',' or '".$close."'");
			}

			$pos = $next;
		}
	}

	/**
	 * One operand of a concatenation.
	 *
	 * @param int $pos
	 * @param int $after set to the offset after the term
	 * @return mixed
	 * @throws \RuntimeException
	 */
	protected function readTerm($pos, &$after)
	{
		if($this->isId($pos, T_START_HEREDOC))
		{
			$body = $this->readEncapsed($pos + 1, T_END_HEREDOC, $after, strpos($this->text($pos), "'") === false);

			// the newline before the closing identifier belongs to the delimiter
			return preg_replace('/\r?\n\z/', '', $body);
		}

		if($this->text($pos) === '"')
		{
			return $this->readEncapsed($pos + 1, '"', $after, true);
		}

		return $this->readScalar($pos, $after);
	}

	/**
	 * A single literal or constant.
	 *
	 * @param int $pos
	 * @param int $after set to the offset after the token
	 * @return mixed
	 * @throws \RuntimeException
	 */
	protected function readScalar($pos, &$after)
	{
		if($pos === null || $pos >= $this->count)
		{
			throw new \RuntimeException('unexpected end of input');
		}

		$after = $pos + 1;
		$token = $this->tokens[$pos];

		if(!is_array($token))
		{
			throw new \RuntimeException('unexpected "'.$token.'"');
		}

		switch($token[0])
		{
			case T_CONSTANT_ENCAPSED_STRING:
				return $this->unquote($token[1]);

			case T_LNUMBER:
				return (int) $token[1];

			case T_DNUMBER:
				return (float) $token[1];

			case T_STRING:
				return $this->constantTerm($token[1]);
		}

		throw new \RuntimeException('unsupported token '.token_name($token[0]));
	}

	/**
	 * Collect the literal parts of a heredoc, nowdoc or interpolated string.
	 *
	 * @param int        $pos     first token inside the string
	 * @param int|string $closing T_END_HEREDOC or the closing quote
	 * @param int        $after   set to the offset after the closing token
	 * @param bool       $escapes whether backslash escapes are live (false for a nowdoc)
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function readEncapsed($pos, $closing, &$after, $escapes)
	{
		$value = '';

		while($pos < $this->count)
		{
			$token = $this->tokens[$pos];

			if(is_array($token) && $closing !== '"' && $token[0] === $closing)
			{
				$after = $pos + 1;
				return $value;
			}

			if(!is_array($token) && $token === $closing)
			{
				$after = $pos + 1;
				return $value;
			}

			if(is_array($token) && $token[0] === T_ENCAPSED_AND_WHITESPACE)
			{
				$value .= $escapes ? $this->unescape($token[1]) : $token[1];
			}
			else
			{
				$this->interpolation($pos);
			}

			$pos++;
		}

		throw new \RuntimeException('unterminated string');
	}

	/**
	 * @param string $literal a quoted string as it appears in the source
	 * @return string
	 */
	protected function unquote($literal)
	{
		$quote = substr($literal, 0, 1);
		$body = (string) substr($literal, 1, -1);

		if($quote === "'")
		{
			return preg_replace('/\\\\([\\\\\'])/', '$1', $body);
		}

		return $this->unescape($body);
	}

	/**
	 * Apply the double-quoted escape sequences.
	 *
	 * @param string $text
	 * @return string
	 */
	protected function unescape($text)
	{
		if(strpos($text, '\\') === false)
		{
			return $text;
		}

		$map = array(
			'n' => "\n", 't' => "\t", 'r' => "\r", 'v' => "\v", 'f' => "\f",
			'e' => "\033", '\\' => '\\', '$' => '$', '"' => '"',
		);

		return preg_replace_callback(
			'/\\\\(x[0-9A-Fa-f]{1,2}|[0-7]{1,3}|u\{[0-9A-Fa-f]+\}|.)/s',
			function ($match) use ($map)
			{
				$body = $match[1];

				if(isset($map[$body]))
				{
					return $map[$body];
				}

				if($body[0] === 'x')
				{
					return chr(hexdec((string) substr($body, 1)));
				}

				if($body[0] === 'u' && function_exists('mb_convert_encoding'))
				{
					return mb_convert_encoding('&#'.hexdec((string) substr($body, 2, -1)).';', 'UTF-8', 'HTML-ENTITIES');
				}

				if($body[0] >= '0' && $body[0] <= '7')
				{
					return chr(octdec($body) & 0xFF);
				}

				return $match[0]; // PHP keeps an unrecognised escape verbatim
			},
			$text
		);
	}

	/**
	 * The source text of a token range, used when an expression will not fold.
	 *
	 * @param int $from
	 * @param int $to exclusive
	 * @return string
	 */
	protected function rawSource($from, $to)
	{
		$text = '';

		for($pos = $from; $pos < $to && $pos < $this->count; $pos++)
		{
			$text .= $this->text($pos);
		}

		return trim($text);
	}

	/**
	 * @param int      $pos
	 * @param int|null $limit stop before this offset
	 * @return int|null
	 */
	protected function nextSignificant($pos, $limit = null)
	{
		$limit = $limit === null ? $this->count : min($limit, $this->count);

		while($pos < $limit)
		{
			$token = $this->tokens[$pos];

			if(!is_array($token) || !$this->isNoise($token[0]))
			{
				return $pos;
			}

			$pos++;
		}

		return null;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	protected function isNoise($id)
	{
		if($id === T_WHITESPACE || $id === T_COMMENT || $id === T_OPEN_TAG)
		{
			return true;
		}

		return defined('T_DOC_COMMENT') && $id === T_DOC_COMMENT;
	}

	/**
	 * @param int $pos
	 * @param int $id
	 * @return bool
	 */
	protected function isId($pos, $id)
	{
		return $pos !== null && $pos < $this->count && is_array($this->tokens[$pos]) && $this->tokens[$pos][0] === $id;
	}

	/**
	 * @param int $pos
	 * @return string
	 */
	protected function text($pos)
	{
		if($pos === null || $pos >= $this->count)
		{
			return '';
		}

		$token = $this->tokens[$pos];

		return is_array($token) ? $token[1] : $token;
	}
}
