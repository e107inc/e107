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
 * Splits a `*_sql.php` schema file into one {@see DeclaredTable} per
 * `CREATE TABLE` statement.
 *
 * This is a statement splitter, not a schema parser. It finds where each
 * declaration starts and ends, lifts the table name and the table options, and
 * hands the body on verbatim; it never looks inside the body, because the
 * server is the only thing entitled to say what a column declaration means.
 *
 * The statement and table-option expressions are ported unchanged from
 * `db_verify::getSqlFileTables()`, along with the behaviour that has grown
 * around them over the years: block comments stripped first, the `e107_`
 * prefix dropped from the table name, `TYPE=` accepted as the pre-4.0 spelling
 * of `ENGINE=`, four spellings of the character-set option, tabs stripped from
 * the body, and `MYISAM` folded back to `MyISAM`. That expression is the one
 * part of the legacy parser worth keeping: it has been read against every
 * schema file e107 and its plugins ship for well over a decade.
 *
 * What is not ported is the shape of the answer. The legacy method returns four
 * parallel arrays keyed by ordinal, and the engine and character set arrays omit
 * any table that declares no options at all, so once one such table appears every
 * later engine in the file is attributed to the table before it. #5910 is the
 * same class of mistake one level up - a table filed under the wrong schema
 * file - and both come of keeping a table's facts beside it rather than on it.
 * Here each table is one object carrying its own declaring file, name, body,
 * engine and character set, so there is no index left to slip.
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

	/**
	 * @var string one `CREATE TABLE` statement: 1 = name, 2 = body, 3 = the raw
	 *      table-options tail. Ported verbatim from `db_verify::getSqlFileTables()`.
	 */
	const TABLE_REGEX = "/CREATE TABLE (?:IF NOT EXISTS )?`?(\w*)`?\s*?\(([^;]*)\)\s*((?:[\w\s]+=[^;]+)+\s*)*;/i";

	/** @var string one `name=value` table option within that tail */
	const OPTION_REGEX = "/([\w\s]+=\s?\w+)+?\s*/";

	/** @var string the dump prefix a schema file may spell its tables with */
	const DUMP_PREFIX = 'e107_';

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

		if(preg_match_all(self::TABLE_REGEX, $sqlText, $match) === false)
		{
			throw new RuntimeException('Could not read the CREATE TABLE statements in "'.$sqlFile.'": '.$this->_pcreError().'.');
		}

		if(empty($match[1]))
		{
			return $tables;
		}

		foreach($match[1] as $i => $rawName)
		{
			$name = $this->_stripPrefix($rawName);

			if($name === '')
			{
				throw new InvalidArgumentException('Unnamed CREATE TABLE statement in "'.$sqlFile.'" (statement '.($i + 1).').');
			}

			$options = $this->_parseTableOptions(isset($match[3][$i]) ? $match[3][$i] : '');

			$tables[$name] = new DeclaredTable(
				$sqlFile,
				$name,
				$this->_cleanBody($match[2][$i]),
				$options['engine'],
				$options['charset']
			);
		}

		return $tables;
	}

	/**
	 * Drop the dump prefix a schema file may have been exported with, so that
	 * `e107_news` and `news` are the same table.
	 *
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
	 * Engine and character set from the raw table-options tail.
	 *
	 * Only the options the verify acts on are read; `AUTO_INCREMENT`, `COLLATE`,
	 * `COMMENT` and the rest are matched and discarded. An option the expression
	 * cannot see - most notably the equals-less `DEFAULT CHARACTER SET utf8mb4`
	 * spelling - reads as "not declared", exactly as it did before.
	 *
	 * @param string $raw
	 * @return array ['engine' => string|null, 'charset' => string|null]
	 */
	private function _parseTableOptions($raw)
	{
		$engine = null;
		$charset = null;

		$raw = (string) $raw;

		if($raw !== '' && preg_match_all(self::OPTION_REGEX, $raw, $split))
		{
			foreach($split[0] as $option)
			{
				$parts = explode('=', $option, 2);

				if(count($parts) < 2)
				{
					continue;
				}

				$optionName = strtoupper(trim($parts[0]));
				$optionValue = trim($parts[1]);

				switch($optionName)
				{
					case 'ENGINE':
					case 'TYPE':
						$engine = $optionValue;
						break;
					case 'DEFAULT CHARSET':
					case 'DEFAULT CHARACTER SET':
					case 'CHARSET':
					case 'CHARACTER SET':
						$charset = $optionValue;
						break;
				}
			}
		}

		if($engine !== null)
		{
			$engine = str_replace('MYISAM', 'MyISAM', $engine);
		}

		return array('engine' => $engine, 'charset' => $charset);
	}

	/**
	 * The last PCRE failure, named where the runtime can name it.
	 *
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
