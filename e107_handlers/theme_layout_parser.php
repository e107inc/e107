<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if(!defined('e107_INIT')) { exit; }

/**
 * Reads the layout variables out of a pre-v2.2.2 theme.php without running it.
 *
 * Themes older than v2.2.2 declare their layouts as $HEADER, $FOOTER,
 * $CUSTOMHEADER, $CUSTOMFOOTER and $LAYOUT inside theme.php. The menu manager
 * used to recover them by rewriting the file with a chain of regular
 * expressions and handing the result to eval(); this tokenises the source and
 * folds the assignments instead, so no theme code is ever executed.
 *
 * The grammar covered is what real themes use: string literals, heredocs and
 * nowdocs, array literals, element assignment ($LAYOUT['home'] = '...') and
 * concatenation with constants. An assignment that does not fold, such as one
 * built by a function call, keeps its raw source as the value: the caller only
 * counts {MENU=n} placeholders, and those survive intact.
 */
class e_theme_layout_parser
{
	/**
	 * Variables worth collecting, in $name form.
	 * @var array
	 */
	protected $wanted = array('$HEADER', '$FOOTER', '$CUSTOMHEADER', '$CUSTOMFOOTER', '$LAYOUT');

	/**
	 * Constant values that depend on the theme being read rather than the live one.
	 * @var array
	 */
	protected $constants = array();

	/**
	 * @var array
	 */
	protected $tokens = array();

	/**
	 * @var int
	 */
	protected $count = 0;

	/**
	 * @param string      $source contents of a theme.php
	 * @param string|null $theme  theme folder name, used to resolve THEME and THEME_ABS
	 * @return array keys HEADER, FOOTER, CUSTOMHEADER, CUSTOMFOOTER and LAYOUT, each
	 *               a string, an array, or null when the theme never assigns it
	 */
	public function parse($source, $theme = null)
	{
		$result = array(
			'HEADER'       => null,
			'FOOTER'       => null,
			'CUSTOMHEADER' => null,
			'CUSTOMFOOTER' => null,
			'LAYOUT'       => null,
		);

		if(!function_exists('token_get_all') || !is_string($source) || $source === '')
		{
			return $result;
		}

		$this->constants = $this->themeConstants($theme);
		$this->tokens = @token_get_all($source);
		$this->count = count($this->tokens);

		$pos = 0;

		while($pos < $this->count)
		{
			$token = $this->tokens[$pos];

			if(is_array($token) && $this->opensOwnScope($token[0]))
			{
				$pos = $this->skipScope($pos);
				continue;
			}

			if(is_array($token) && $token[0] === T_VARIABLE && in_array($token[1], $this->wanted, true))
			{
				$pos = $this->readAssignment($pos, $result);
				continue;
			}

			$pos++;
		}

		return $result;
	}

	/**
	 * Declarations whose body eval() would never have run.
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
	 * Fold one `$VAR = ...;` or `$VAR['key'] = ...;` statement into $result.
	 *
	 * @param int   $pos    offset of the T_VARIABLE token
	 * @param array $result collected values, modified in place
	 * @return int offset to resume scanning from
	 */
	protected function readAssignment($pos, &$result)
	{
		$name = (string) substr($this->tokens[$pos][1], 1);
		$next = $this->nextSignificant($pos + 1);
		$key = null;

		if($next !== null && $this->text($next) === '[')
		{
			$keyAt = $this->nextSignificant($next + 1);

			try
			{
				$key = $this->readScalar($keyAt, $after);
			}
			catch(Exception $e)
			{
				return $pos + 1;
			}

			$after = $this->nextSignificant($after);

			if($after === null || $this->text($after) !== ']')
			{
				return $pos + 1;
			}

			$next = $this->nextSignificant($after + 1);
		}

		if($next === null)
		{
			return $pos + 1;
		}

		$append = $this->isId($next, T_CONCAT_EQUAL);

		if(!$append && $this->text($next) !== '=')
		{
			return $pos + 1; // the variable is being read, not written
		}

		$from = $next + 1;
		$to = $this->endOfStatement($from);

		try
		{
			$value = $this->reduce($from, $to);
		}
		catch(Exception $e)
		{
			$value = $this->rawSource($from, $to);
		}

		$this->store($result, $name, $key, $value, $append);

		return $to + 1;
	}

	/**
	 * @param array      $result
	 * @param string     $name
	 * @param string|int $key    null for a whole-variable assignment
	 * @param mixed      $value
	 * @param bool       $append true for `.=`
	 * @return void
	 */
	protected function store(&$result, $name, $key, $value, $append)
	{
		if(!array_key_exists($name, $result))
		{
			return;
		}

		if($key === null)
		{
			if($append && is_string($result[$name]) && is_string($value))
			{
				$result[$name] .= $value;
				return;
			}

			$result[$name] = $value;
			return;
		}

		if(!is_array($result[$name]))
		{
			$result[$name] = array();
		}

		if($append && isset($result[$name][$key]) && is_string($result[$name][$key]) && is_string($value))
		{
			$result[$name][$key] .= $value;
			return;
		}

		$result[$name][$key] = $value;
	}

	/**
	 * @param int $from first token of the expression
	 * @return int offset of the terminating semicolon, or of the last token
	 */
	protected function endOfStatement($from)
	{
		$depth = 0;
		$pos = $from;

		while($pos < $this->count)
		{
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
			elseif($depth <= 0 && ($text === ';' || $this->isId($pos, T_CLOSE_TAG)))
			{
				return $pos;
			}

			$pos++;
		}

		return $this->count - 1;
	}

	/**
	 * Fold a token range into a string or an array.
	 *
	 * @param int $from
	 * @param int $to exclusive: the terminating semicolon
	 * @return string|array
	 * @throws RuntimeException when the range leaves the supported grammar
	 */
	protected function reduce($from, $to)
	{
		$pos = $this->nextSignificant($from, $to);

		if($pos === null)
		{
			throw new RuntimeException('empty expression');
		}

		$value = $this->readValue($pos, $after);
		$after = $this->nextSignificant($after, $to);

		if($after !== null)
		{
			throw new RuntimeException('trailing tokens');
		}

		return $value;
	}

	/**
	 * A value is an array literal or a chain of concatenated terms.
	 *
	 * @param int $pos
	 * @param int $after set to the offset after the value
	 * @return string|array
	 * @throws RuntimeException
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
				throw new RuntimeException('dangling concatenation');
			}

			$value .= $this->readTerm($operand, $after);
			$next = $this->nextSignificant($after);
		}

		$after = $next === null ? $this->count : $next;

		return $value;
	}

	/**
	 * @param int $pos offset of `array` or `[`
	 * @param int $after set to the offset after the closing bracket
	 * @return array
	 * @throws RuntimeException
	 */
	protected function readArray($pos, &$after)
	{
		if($this->isId($pos, T_ARRAY))
		{
			$pos = $this->nextSignificant($pos + 1);

			if($pos === null || $this->text($pos) !== '(')
			{
				throw new RuntimeException("expected '('");
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
				throw new RuntimeException('unterminated array');
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
					throw new RuntimeException('missing array value');
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
				throw new RuntimeException("expected ',' or '".$close."'");
			}

			$pos = $next;
		}
	}

	/**
	 * One operand of a concatenation.
	 *
	 * @param int $pos
	 * @param int $after set to the offset after the term
	 * @return string
	 * @throws RuntimeException
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
	 * @return string|int|float
	 * @throws RuntimeException
	 */
	protected function readScalar($pos, &$after)
	{
		if($pos === null || $pos >= $this->count)
		{
			throw new RuntimeException('unexpected end of input');
		}

		$after = $pos + 1;
		$token = $this->tokens[$pos];

		if(!is_array($token))
		{
			throw new RuntimeException('unexpected "'.$token.'"');
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
				return $this->resolveConstant($token[1]);
		}

		throw new RuntimeException('unsupported token '.token_name($token[0]));
	}

	/**
	 * Collect the literal parts of a heredoc, nowdoc or interpolated string.
	 * Interpolated variables are dropped: nothing here is being executed, and no
	 * layout placeholder is ever built out of one.
	 *
	 * @param int        $pos     first token inside the string
	 * @param int|string $closing T_END_HEREDOC or the closing quote
	 * @param int        $after   set to the offset after the closing token
	 * @param bool       $escapes whether backslash escapes are live (false for a nowdoc)
	 * @return string
	 * @throws RuntimeException
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

			$pos++;
		}

		throw new RuntimeException('unterminated string');
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
	 * @param string $name
	 * @return string
	 */
	protected function resolveConstant($name)
	{
		if(isset($this->constants[$name]))
		{
			return $this->constants[$name];
		}

		if(defined($name))
		{
			$value = constant($name);

			return is_scalar($value) ? (string) $value : '';
		}

		return '';
	}

	/**
	 * THEME and THEME_ABS point at the live theme, not the one being read.
	 *
	 * @param string|null $theme
	 * @return array
	 */
	protected function themeConstants($theme)
	{
		if(empty($theme))
		{
			return array();
		}

		$abs = defined('e_THEME_ABS') ? e_THEME_ABS : '';

		return array(
			'THEME'     => e_THEME.$theme.'/',
			'THEME_ABS' => $abs.$theme.'/',
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
