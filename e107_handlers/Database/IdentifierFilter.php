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
 * Database-free guard for SQL identifiers and ORDER BY fragments.
 *
 * Validates input against a strict whitelist grammar and fails closed:
 * anything outside the grammar returns false rather than a "cleaned" guess.
 * Intended for legacy code paths that interpolate column names or ORDER BY
 * clauses into SQL strings.
 */
class IdentifierFilter
{
	/**
	 * Validate and backtick-quote a `column` or `table.column` identifier.
	 *
	 * Each dot-separated part (max 2) must match /^[A-Za-z0-9_]+$/.
	 *
	 * @param string $name
	 * @return string|false Quoted identifier, or false on any violation.
	 */
	public static function identifier($name)
	{
		if(!is_string($name))
		{
			return false;
		}

		$parts = explode('.', trim($name));

		if(count($parts) > 2)
		{
			return false;
		}

		foreach($parts as $part)
		{
			if(!preg_match('/^[A-Za-z0-9_]+$/D', $part))
			{
				return false;
			}
		}

		return '`'.implode('`.`', $parts).'`';
	}

	/**
	 * Normalize a sort direction to 'ASC' or 'DESC'.
	 *
	 * @param string $dir Case-insensitive direction.
	 * @param string $default Returned when $dir is not ASC/DESC.
	 * @return string 'ASC' or 'DESC' (or $default verbatim).
	 */
	public static function direction($dir, $default = 'ASC')
	{
		if(!is_string($dir))
		{
			return $default;
		}

		$dir = strtoupper(trim($dir));

		if($dir === 'ASC' || $dir === 'DESC')
		{
			return $dir;
		}

		return $default;
	}

	/**
	 * Validate a multi-column ORDER BY fragment and re-emit it in canonical
	 * quoted form, e.g. "col1 DESC, t.col2 asc" => "`col1` DESC, `t`.`col2` ASC".
	 *
	 * An optional leading "ORDER BY" keyword is tolerated and stripped.
	 * Each comma-separated item must be a valid identifier optionally followed
	 * by ASC or DESC (default ASC). Any invalid item fails the whole call.
	 *
	 * @param string $orderBy
	 * @param array|null $columns Optional allowlist of permitted column names
	 *                            (full "tbl.col" form, compared case-insensitively).
	 * @return string|false Canonical ORDER BY fragment, or false on any violation.
	 */
	public static function orderBy($orderBy, $columns = null)
	{
		if(!is_string($orderBy))
		{
			return false;
		}

		$orderBy = preg_replace('/^ORDER\s+BY\s+/i', '', trim($orderBy));

		if($orderBy === '')
		{
			return false;
		}

		if($columns !== null)
		{
			$columns = array_map('strtolower', $columns);
		}

		$out = array();

		foreach(explode(',', $orderBy) as $item)
		{
			$tokens = preg_split('/\s+/', trim($item), -1, PREG_SPLIT_NO_EMPTY);

			if(empty($tokens) || count($tokens) > 2)
			{
				return false;
			}

			$quoted = self::identifier($tokens[0]);

			if($quoted === false)
			{
				return false;
			}

			$dir = 'ASC';

			if(isset($tokens[1]))
			{
				$dir = strtoupper($tokens[1]);

				if($dir !== 'ASC' && $dir !== 'DESC')
				{
					return false;
				}
			}

			if($columns !== null && !in_array(strtolower($tokens[0]), $columns, true))
			{
				return false;
			}

			$out[] = $quoted.' '.$dir;
		}

		return implode(', ', $out);
	}

	/**
	 * Return $input only when it is an exact (strict) member of $allowed.
	 *
	 * Explicit-allowlist helper for fixed dropdown values; no parsing.
	 *
	 * @param mixed $input
	 * @param array $allowed
	 * @param mixed $default Returned when $input is not in $allowed.
	 * @return mixed $input or $default.
	 */
	public static function filterOrderBy($input, array $allowed, $default)
	{
		return in_array($input, $allowed, true) ? $input : $default;
	}
}
