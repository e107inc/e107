<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database;

/**
 * Splits SQL text into tokens the way MySQL or SQLite reads it, so the inside of a string, a quoted identifier or a
 * comment is never mistaken for SQL.
 *
 * <code>
 * $lexer = SqlLexer::mysql();
 * foreach($lexer->tokenize("SELECT 'it\'s' AS a -- note") as $token)
 * {
 *     // $token['type'] is a SqlLexer::T_* constant, $token['text'] the raw text, $token['value'] the decoded value
 * }
 * $statements = SqlLexer::sqlite()->splitStatements($script);
 * </code>
 */
final class SqlLexer
{
	/** spaces, tabs and line breaks */
	const T_WHITESPACE = 'whitespace';

	/** -- line, # line (MySQL) or block comment */
	const T_COMMENT = 'comment';

	/** a string literal; 'value' holds the decoded string */
	const T_STRING = 'string';

	/** a quoted identifier; 'value' holds the bare name */
	const T_QUOTED_IDENTIFIER = 'quoted_identifier';

	/** an unquoted word: keyword, identifier or function name; 'value' is the text */
	const T_WORD = 'word';

	/** a numeric literal, hexadecimal and binary literals included */
	const T_NUMBER = 'number';

	/** a bound parameter: ?, :name */
	const T_PARAMETER = 'parameter';

	/** a single punctuation or operator character, or a known multi-character operator */
	const T_SYMBOL = 'symbol';

	/** @var bool MySQL's rules rather than SQLite's */
	private $mysql;

	/** @var string[] operators read as one symbol */
	private static $operators = array('<=>', '<>', '!=', '<=', '>=', '||', '&&', '<<', '>>', '::', '->>', '->');

	/**
	 * @param bool $mysql
	 */
	private function __construct($mysql)
	{
		$this->mysql = (bool) $mysql;
	}

	/**
	 * MySQL and MariaDB with their default sql_mode: backslash escapes, "double-quoted" strings, '#' comments, '--' a
	 * comment only before whitespace, and identifiers that may start with a digit.
	 *
	 * @return SqlLexer
	 */
	public static function mysql()
	{
		return new self(true);
	}

	/**
	 * SQLite: standard strings, "double-quoted" identifiers, no '#' comments; backticks and [brackets] quote
	 * identifiers too, and a CREATE TRIGGER runs to its '; END;'.
	 *
	 * @return SqlLexer
	 */
	public static function sqlite()
	{
		return new self(false);
	}

	/**
	 * @param string $sql
	 * @return array[] tokens, each array('type' => SqlLexer::T_*, 'text' => raw text, 'value' => decoded value)
	 */
	public function tokenize($sql)
	{
		$sql = (string) $sql;
		$length = strlen($sql);
		$tokens = array();
		$i = 0;

		while($i < $length)
		{
			$c = $sql[$i];
			$next = ($i + 1 < $length) ? $sql[$i + 1] : '';

			if(ctype_space($c))
			{
				$end = $i + 1;
				while($end < $length && ctype_space($sql[$end]))
				{
					$end++;
				}
				$tokens[] = $this->token(self::T_WHITESPACE, (string) substr($sql, $i, $end - $i));
				$i = $end;
				continue;
			}

			if(($c === '-' && $next === '-' && $this->dashCommentAt($sql, $i + 2)) || ($c === '#' && $this->mysql))
			{
				$end = $this->lineEnd($sql, $i);
				$tokens[] = $this->token(self::T_COMMENT, (string) substr($sql, $i, $end - $i));
				$i = $end;
				continue;
			}

			if($c === '/' && $next === '*')
			{
				$close = strpos($sql, '*/', $i + 2);
				$end = ($close === false) ? $length : $close + 2;
				$tokens[] = $this->token(self::T_COMMENT, (string) substr($sql, $i, $end - $i));
				$i = $end;
				continue;
			}

			if($c === "'" || ($c === '"' && $this->mysql))
			{
				list($end, $value) = $this->readString($sql, $i, $c);
				$tokens[] = $this->token(self::T_STRING, (string) substr($sql, $i, $end - $i), $value);
				$i = $end;
				continue;
			}

			if($c === '`' || $c === '"' || ($c === '[' && !$this->mysql))
			{
				list($end, $value) = $this->readQuotedIdentifier($sql, $i, ($c === '[') ? ']' : $c);
				$tokens[] = $this->token(self::T_QUOTED_IDENTIFIER, (string) substr($sql, $i, $end - $i), $value);
				$i = $end;
				continue;
			}

			if(ctype_digit($c) && $this->mysql && ($end = $this->digitIdentifierEnd($sql, $i)) !== null)
			{
				$tokens[] = $this->token(self::T_WORD, (string) substr($sql, $i, $end - $i));
				$i = $end;
				continue;
			}

			if(ctype_digit($c) || ($c === '.' && ctype_digit($next) && !($this->mysql && $this->followsName($tokens))))
			{
				$end = $this->numberEnd($sql, $i);
				$tokens[] = $this->token(self::T_NUMBER, (string) substr($sql, $i, $end - $i));
				$i = $end;
				continue;
			}

			if(($c === 'x' || $c === 'X' || $c === 'b' || $c === 'B') && $next === "'")
			{
				list($end) = $this->readString($sql, $i + 1, "'");
				$tokens[] = $this->token(self::T_NUMBER, (string) substr($sql, $i, $end - $i));
				$i = $end;
				continue;
			}

			if($this->isWordStart($c))
			{
				$end = $i + 1;
				while($end < $length && $this->isWordPart($sql[$end]))
				{
					$end++;
				}
				$tokens[] = $this->token(self::T_WORD, (string) substr($sql, $i, $end - $i));
				$i = $end;
				continue;
			}

			if($c === '?' || ($c === ':' && $next !== '' && $this->isWordStart($next)))
			{
				$end = $i + 1;
				while($c === ':' && $end < $length && $this->isWordPart($sql[$end]))
				{
					$end++;
				}
				$tokens[] = $this->token(self::T_PARAMETER, (string) substr($sql, $i, $end - $i));
				$i = $end;
				continue;
			}

			$symbol = $c;
			foreach(self::$operators as $operator)
			{
				if((string) substr($sql, $i, strlen($operator)) === $operator)
				{
					$symbol = $operator;
					break;
				}
			}

			$tokens[] = $this->token(self::T_SYMBOL, $symbol);
			$i += strlen($symbol);
		}

		return $tokens;
	}

	/**
	 * The same tokens without whitespace and comments.
	 *
	 * @param string $sql
	 * @return array[]
	 */
	public function significantTokens($sql)
	{
		$tokens = array();

		foreach($this->tokenize($sql) as $token)
		{
			if($token['type'] !== self::T_WHITESPACE && $token['type'] !== self::T_COMMENT)
			{
				$tokens[] = $token;
			}
		}

		return $tokens;
	}

	/**
	 * Cut a script into statements at every ';' that is not inside a string, identifier, comment or SQLite trigger
	 * body.
	 *
	 * @param string $sql
	 * @return string[] the statements, trimmed, without their terminators; empty statements dropped
	 */
	public function splitStatements($sql)
	{
		$statements = array();
		$current = '';
		$significant = array();

		foreach($this->tokenize($sql) as $token)
		{
			if($token['type'] === self::T_WHITESPACE || $token['type'] === self::T_COMMENT)
			{
				$current .= $token['text'];
				continue;
			}

			if($token['type'] === self::T_SYMBOL && $token['text'] === ';' && !$this->insideTrigger($significant))
			{
				if(!empty($significant))
				{
					$statements[] = trim($current);
				}

				$current = '';
				$significant = array();
				continue;
			}

			$current .= $token['text'];
			$significant[] = ($token['type'] === self::T_WORD) ? strtoupper($token['text']) : $token['text'];
		}

		if(!empty($significant))
		{
			$statements[] = trim($current);
		}

		return $statements;
	}

	/**
	 * @param string $sql
	 * @param int $offset just past a '--'
	 * @return bool whether the '--' starts a comment
	 */
	private function dashCommentAt($sql, $offset)
	{
		return !$this->mysql || !isset($sql[$offset]) || ctype_space($sql[$offset]) || ctype_cntrl($sql[$offset]);
	}

	/**
	 * As sqlite3_complete() reads it: a CREATE TRIGGER statement ends only at a ';' that follows '; END'.
	 *
	 * @param string[] $significant the statement so far, words upper-cased
	 * @return bool whether a ';' here belongs to the statement
	 */
	private function insideTrigger(array $significant)
	{
		if($this->mysql)
		{
			return false;
		}

		$words = $significant;

		if(isset($words[0]) && $words[0] === 'EXPLAIN')
		{
			$words = array_slice($words, (isset($words[1], $words[2]) && $words[1] === 'QUERY' && $words[2] === 'PLAN') ? 3 : 1);
		}

		if(!isset($words[0]) || $words[0] !== 'CREATE')
		{
			return false;
		}

		$type = (isset($words[1]) && ($words[1] === 'TEMP' || $words[1] === 'TEMPORARY')) ? 2 : 1;

		if(!isset($words[$type]) || $words[$type] !== 'TRIGGER')
		{
			return false;
		}

		return array_slice($significant, -2) !== array(';', 'END');
	}

	/**
	 * @param string $type
	 * @param string $text
	 * @param string|null $value decoded value; the text when null
	 * @return array
	 */
	private function token($type, $text, $value = null)
	{
		return array('type' => $type, 'text' => $text, 'value' => ($value === null) ? $text : $value);
	}

	/**
	 * @param string $sql
	 * @param int $start
	 * @return int offset just past the line, before its line break
	 */
	private function lineEnd($sql, $start)
	{
		$end = strcspn($sql, "\r\n", $start);

		return $start + $end;
	}

	/**
	 * @param string $sql
	 * @param int $start offset of the opening quote
	 * @param string $quote
	 * @return array array(offset past the closing quote or at the end of an unterminated string, decoded value)
	 */
	private function readString($sql, $start, $quote)
	{
		$length = strlen($sql);
		$value = '';
		$i = $start + 1;

		while($i < $length)
		{
			$c = $sql[$i];

			if($c === '\\' && $this->mysql && $i + 1 < $length)
			{
				$value .= $this->unescape($sql[$i + 1]);
				$i += 2;
				continue;
			}

			if($c === $quote)
			{
				if($i + 1 < $length && $sql[$i + 1] === $quote)
				{
					$value .= $quote;
					$i += 2;
					continue;
				}

				return array($i + 1, $value);
			}

			$value .= $c;
			$i++;
		}

		return array($length, $value);
	}

	/**
	 * MySQL's backslash sequences; '\%' and '\_' keep their backslash, for LIKE patterns.
	 *
	 * @param string $c the character after the backslash
	 * @return string
	 */
	private function unescape($c)
	{
		switch($c)
		{
			case '0': return "\0";
			case 'b': return "\x08";
			case 'n': return "\n";
			case 'r': return "\r";
			case 't': return "\t";
			case 'Z': return "\x1A";
			case '%': return '\\%';
			case '_': return '\\_';
		}

		return $c;
	}

	/**
	 * @param string $sql
	 * @param int $start offset of the opening quote
	 * @param string $close the closing quote
	 * @return array array(offset past the closing quote, bare name)
	 */
	private function readQuotedIdentifier($sql, $start, $close)
	{
		$length = strlen($sql);
		$value = '';
		$i = $start + 1;

		while($i < $length)
		{
			if($sql[$i] === $close)
			{
				if($close !== ']' && $i + 1 < $length && $sql[$i + 1] === $close)
				{
					$value .= $close;
					$i += 2;
					continue;
				}

				return array($i + 1, $value);
			}

			$value .= $sql[$i];
			$i++;
		}

		return array($length, $value);
	}

	/**
	 * @param array[] $tokens
	 * @return bool whether the last token is a name, so a '.' after it qualifies it
	 */
	private function followsName(array $tokens)
	{
		$last = end($tokens);

		return $last !== false && ($last['type'] === self::T_WORD || $last['type'] === self::T_QUOTED_IDENTIFIER);
	}

	/**
	 * @param string $sql
	 * @param int $start at a digit
	 * @return int|null offset just past an identifier that starts with a digit, as MySQL allows one; null for a number
	 */
	private function digitIdentifierEnd($sql, $start)
	{
		$end = $start;

		while(isset($sql[$end]) && $this->isWordPart($sql[$end]))
		{
			$end++;
		}

		$run = (string) substr($sql, $start, $end - $start);
		$signedExponent = isset($sql[$end + 1]) && ($sql[$end] === '+' || $sql[$end] === '-') && ctype_digit($sql[$end + 1]);

		if(ctype_digit($run) || preg_match('/^(?:0x[0-9A-Fa-f]+|0b[01]+|\d+[eE]\d+)$/D', $run) || ($signedExponent && preg_match('/^\d+[eE]$/D', $run)))
		{
			return null;
		}

		return $end;
	}

	/**
	 * @param string $sql
	 * @param int $start
	 * @return int offset just past the number
	 */
	private function numberEnd($sql, $start)
	{
		if(preg_match('/\G(?:0x[0-9A-Fa-f]+|0b[01]+|(?:\d+\.?\d*|\.\d+)(?:[eE][+-]?\d+)?)/', $sql, $match, 0, $start))
		{
			return $start + strlen($match[0]);
		}

		return $start + 1;
	}

	/**
	 * @param string $c
	 * @return bool
	 */
	private function isWordStart($c)
	{
		return ctype_alpha($c) || $c === '_' || $c === '$' || ord($c) >= 0x80;
	}

	/**
	 * @param string $c
	 * @return bool
	 */
	private function isWordPart($c)
	{
		return ctype_alnum($c) || $c === '_' || $c === '$' || ord($c) >= 0x80;
	}
}
