<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Introspect;

require_once(__DIR__.'/IndexSchema.php');

/**
 * Reads MySQL's own SHOW CREATE TABLE text: the block between its outer parentheses, the options after them, and the
 * one definition line it writes for each column and index. The statement must be written with quoted identifiers
 * (SQL_QUOTE_SHOW_CREATE, MySQL's default).
 */
final class MysqlCreateStatement
{
	/**
	 * A SHOW CREATE TABLE statement cut into the block between its outer parentheses and the options that follow them.
	 *
	 * @param string $create
	 * @return array|null ['body' => string, 'options' => string], everything after the closing parenthesis counting as options; null when the statement has no recognisable outer parentheses.
	 */
	public static function split($create)
	{
		$lines = preg_split('/\r\n|\n|\r/', $create);
		$body = array();
		$options = null;
		$opened = false;

		foreach($lines as $at => $line)
		{
			if(!$opened)
			{
				$opened = (substr(rtrim($line), -1) === '(');
				continue;
			}

			if(substr($line, 0, 1) === ')')
			{
				$trailing = array_merge(array((string) substr($line, 1)), array_slice($lines, $at + 1));
				$options = trim(implode("\n", $trailing));

				break;
			}

			$body[] = $line;
		}

		if(!$opened || $options === null || count($body) === 0)
		{
			return null;
		}

		return array(
			'body'    => implode("\n", $body),
			'options' => self::withoutAutoIncrement($options),
		);
	}

	/**
	 * The create body's lines, without their leading indentation or trailing comma, keyed by the column or index each defines.
	 *
	 * @param string $body
	 * @return array ['columns' => name => string, 'indexes' => name => string]; a line defining neither, such as a CHECK constraint or a foreign key, is left out.
	 */
	public static function definitionsByName($body)
	{
		$columns = array();
		$indexes = array();

		foreach(explode("\n", $body) as $line)
		{
			$fragment = preg_replace('/,$/', '', trim($line));

			if($fragment === '')
			{
				continue;
			}

			if(substr($fragment, 0, 1) === '`')
			{
				$name = self::leadingIdentifier($fragment);

				if($name !== null)
				{
					$columns[$name] = $fragment;
				}

				continue;
			}

			$name = self::indexNameOf($fragment);

			if($name !== null)
			{
				$indexes[$name] = $fragment;
			}
		}

		return array('columns' => $columns, 'indexes' => $indexes);
	}

	/**
	 * The index an index definition line names, as information_schema names it.
	 *
	 * @param string $fragment
	 * @return string|null null when the line does not define an index.
	 */
	private static function indexNameOf($fragment)
	{
		if(!preg_match('/^(PRIMARY KEY|UNIQUE KEY|FULLTEXT KEY|SPATIAL KEY|KEY)\s/i', $fragment, $matches))
		{
			return null;
		}

		if(strcasecmp($matches[1], 'PRIMARY KEY') === 0)
		{
			return IndexSchema::KIND_PRIMARY;
		}

		return self::leadingIdentifier(ltrim((string) substr($fragment, strlen($matches[1]))));
	}

	/**
	 * @param string $fragment
	 * @return string|null The identifier the fragment opens with, unquoted; null when it does not open with a backticked identifier.
	 */
	private static function leadingIdentifier($fragment)
	{
		if(!preg_match('/^`((?:[^`]|``)*)`/', $fragment, $matches))
		{
			return null;
		}

		return str_replace('``', '`', $matches[1]);
	}

	/**
	 * The table options without any AUTO_INCREMENT counter.
	 *
	 * @param string $options
	 * @return string
	 */
	private static function withoutAutoIncrement($options)
	{
		$quote = strpos($options, "'");
		$head = ($quote === false) ? $options : (string) substr($options, 0, $quote);
		$tail = ($quote === false) ? '' : (string) substr($options, $quote);

		return trim(preg_replace('/\bAUTO_INCREMENT\s*=\s*\d+\s*/i', '', $head).$tail);
	}
}
