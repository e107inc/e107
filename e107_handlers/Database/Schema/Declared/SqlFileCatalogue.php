<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Declared;

use InvalidArgumentException;
use RuntimeException;

/**
 * Splits a `*_sql.php` schema file into one {@see DeclaredTable} per `CREATE TABLE` statement.
 *
 * <code>
 * $tables = (new SqlFileCatalogue())->parse(file_get_contents(e_CORE.'sql/core_sql.php'), 'core');
 * $tables['news']->getDeclaredEngine();   // 'InnoDB'
 * </code>
 */
final class SqlFileCatalogue
{
	/** @var string block comments, stripped before splitting */
	const COMMENT_REGEX = "#\/\*.*?\*\/#mis";

	/** @var string the head of one `CREATE TABLE` statement, up to and including its opening parenthesis: 1 = name */
	const TABLE_REGEX = "/CREATE TABLE (?:IF NOT EXISTS )?`?(\w*)`?\s*?\(/i";

	/** @var string one token of the table-options tail: a quoted string, an `=`, or a bare word */
	const OPTION_TOKEN_REGEX = "/'(?:\\\\.|''|[^'\\\\])*'|\"(?:\\\\.|\"\"|[^\"\\\\])*\"|`(?:``|[^`])*`|=|\w+/";

	/** @var string the dump prefix a schema file may spell its tables with */
	const DUMP_PREFIX = 'e107_';

	/** @var string[] the quote characters a table option or a column comment may be wrapped in */
	private static $quotes = array("'", '"', '`');

	/**
	 * A file that declares the same table twice keeps the last declaration.
	 *
	 * @param string $sqlText Contents of a `*_sql.php` file, or any text containing `CREATE TABLE` statements. A statement is only seen when it ends in a semicolon, which `SHOW CREATE TABLE` output needs appended.
	 * @param string $sqlFile 'core', or the plugin schema file's identity, recorded on every table it declares. Non-empty.
	 * @return DeclaredTable[] keyed by unprefixed table name in declaration order; empty when the text declares no table.
	 * @throws InvalidArgumentException when $sqlFile is empty, or a statement declares a table with no name.
	 * @throws RuntimeException when PCRE gives up on the text.
	 */
	public function parse($sqlText, $sqlFile)
	{
		$sqlFile = trim((string) $sqlFile);

		if($sqlFile === '')
		{
			throw new InvalidArgumentException('SqlFileCatalogue::parse() requires the name of the declaring SQL file.');
		}

		$sqlText = preg_replace(self::COMMENT_REGEX, '', (string) $sqlText);

		if($sqlText === null)
		{
			throw new RuntimeException('Could not strip comments from "'.$sqlFile.'": '.$this->_pcreError().'.');
		}

		$tables = array();
		$length = strlen($sqlText);
		$offset = 0;
		$statement = 0;

		while($offset <= $length)
		{
			$head = $this->_findHead($sqlText, $offset, $sqlFile);

			if($head === null)
			{
				break;
			}

			$bodyStart = $head['offset'] + strlen($head['text']);
			$offset = $bodyStart;

			$bodyEnd = $this->_findBodyEnd($sqlText, $bodyStart);

			if($bodyEnd === null)
			{
				continue;
			}

			$next = $this->_findHead($sqlText, $bodyEnd + 1, $sqlFile);
			$end = $this->_findStatementEnd($sqlText, $bodyEnd + 1, $next === null ? $length : $next['offset']);

			if($end === null)
			{
				continue;
			}

			$statement++;
			$offset = $end + 1;

			$name = $this->_stripPrefix($head['name']);

			if($name === '')
			{
				throw new InvalidArgumentException('Unnamed CREATE TABLE statement in "'.$sqlFile.'" (statement '.$statement.').');
			}

			$options = $this->_parseTableOptions((string) substr($sqlText, $bodyEnd + 1, $end - $bodyEnd - 1), $sqlFile);

			$tables[$name] = new DeclaredTable(
				$sqlFile,
				$name,
				$this->_cleanBody((string) substr($sqlText, $bodyStart, $bodyEnd - $bodyStart)),
				$options['engine'],
				$options['charset']
			);
		}

		return $tables;
	}

	/**
	 * @param string $sqlText
	 * @param int $offset
	 * @param string $sqlFile named in the refusal when PCRE gives up
	 * @return array|null ['offset'=>int, 'text'=>string, 'name'=>string] for the next head at or after $offset, null when there is no further head
	 * @throws RuntimeException
	 */
	private function _findHead($sqlText, $offset, $sqlFile)
	{
		$found = preg_match(self::TABLE_REGEX, $sqlText, $match, PREG_OFFSET_CAPTURE, $offset);

		if($found === false)
		{
			throw new RuntimeException('Could not read the CREATE TABLE statements in "'.$sqlFile.'": '.$this->_pcreError().'.');
		}

		if(!$found)
		{
			return null;
		}

		return array('offset' => $match[0][1], 'text' => $match[0][0], 'name' => $match[1][0]);
	}

	/**
	 * @param string $sqlText
	 * @param int $bodyStart first character after the opening parenthesis
	 * @return int|null offset of the parenthesis balancing the one the head opened, null when the body never closes
	 */
	private function _findBodyEnd($sqlText, $bodyStart)
	{
		$length = strlen($sqlText);
		$depth = 1;

		for($i = $bodyStart; $i < $length; $i++)
		{
			if(in_array($sqlText[$i], self::$quotes, true))
			{
				$i = $this->_skipQuoted($sqlText, $i);
			}
			elseif($sqlText[$i] === '(')
			{
				$depth++;
			}
			elseif($sqlText[$i] === ')' && --$depth === 0)
			{
				return $i;
			}
		}

		return null;
	}

	/**
	 * @param string $sqlText
	 * @param int $tailStart first character after the closing parenthesis
	 * @param int $limit offset the search stops at, being where the next declaration begins
	 * @return int|null offset of the terminating semicolon, null when the statement never terminates
	 */
	private function _findStatementEnd($sqlText, $tailStart, $limit)
	{
		for($i = $tailStart; $i < $limit; $i++)
		{
			if(in_array($sqlText[$i], self::$quotes, true))
			{
				$i = $this->_skipQuoted($sqlText, $i);
			}
			elseif($sqlText[$i] === ';')
			{
				return $i;
			}
		}

		return null;
	}

	/**
	 * @param string $sqlText
	 * @param int $start offset of the opening quote
	 * @return int offset of the closing quote, or the end of the text when the string never closes
	 */
	private function _skipQuoted($sqlText, $start)
	{
		$length = strlen($sqlText);
		$quote = $sqlText[$start];

		for($i = $start + 1; $i < $length; $i++)
		{
			if($sqlText[$i] === '\\' && $quote !== '`')
			{
				$i++;
			}
			elseif($sqlText[$i] === $quote)
			{
				if(!isset($sqlText[$i + 1]) || $sqlText[$i + 1] !== $quote)
				{
					return $i;
				}

				$i++;
			}
		}

		return $length;
	}

	/**
	 * @param string $name
	 * @return string $name without a leading `e107_`
	 */
	private function _stripPrefix($name)
	{
		$name = (string) $name;

		if(strpos($name, self::DUMP_PREFIX) === 0)
		{
			$name = (string) substr($name, strlen(self::DUMP_PREFIX));
		}

		return $name;
	}

	/**
	 * @param string $body
	 * @return string $body with tabs removed, then trimmed
	 */
	private function _cleanBody($body)
	{
		return trim(str_replace("\t", '', (string) $body));
	}

	/**
	 * Only `ENGINE` (or `TYPE`) and the character set are read; every other table option is walked past.
	 *
	 * @param string $raw everything between the closing parenthesis and the semicolon
	 * @param string $sqlFile named in the refusal when PCRE gives up
	 * @return array ['engine' => string|null, 'charset' => string|null], null where the file states nothing
	 * @throws RuntimeException
	 */
	private function _parseTableOptions($raw, $sqlFile)
	{
		$engine = null;
		$charset = null;

		if(preg_match_all(self::OPTION_TOKEN_REGEX, (string) $raw, $found) === false)
		{
			throw new RuntimeException('Could not read the table options in "'.$sqlFile.'": '.$this->_pcreError().'.');
		}

		$tokens = $found[0];
		$count = count($tokens);

		for($i = 0; $i < $count; $i++)
		{
			$keyword = strtoupper($tokens[$i]);

			if($keyword === 'ENGINE' || $keyword === 'TYPE')
			{
				$engine = $this->_optionValue($tokens, $i);
			}
			elseif($keyword === 'CHARSET')
			{
				$charset = $this->_optionValue($tokens, $i);
			}
			elseif($keyword === 'CHARACTER' && isset($tokens[$i + 1]) && strtoupper($tokens[$i + 1]) === 'SET')
			{
				$i++;
				$charset = $this->_optionValue($tokens, $i);
			}
		}

		if($engine !== null)
		{
			$engine = str_replace('MYISAM', 'MyISAM', $engine);
		}

		return array('engine' => $engine, 'charset' => $charset);
	}

	/**
	 * @param array $tokens
	 * @param int $i index of the option name, advanced onto the value when one is found
	 * @return string|null null when the value is quoted or missing
	 */
	private function _optionValue(array $tokens, &$i)
	{
		$at = $i + 1;

		if(isset($tokens[$at]) && $tokens[$at] === '=')
		{
			$at++;
		}

		if(!isset($tokens[$at]) || in_array($tokens[$at][0], self::$quotes, true))
		{
			return null;
		}

		$i = $at;

		return $tokens[$at];
	}

	/**
	 * @return string
	 */
	private function _pcreError()
	{
		if(function_exists('preg_last_error_msg'))
		{
			return preg_last_error_msg();
		}

		return 'preg error '.preg_last_error();
	}
}
