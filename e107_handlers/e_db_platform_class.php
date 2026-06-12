<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

/**
 * Minimal SQL dialect description, consulted by {@see e_db_query}.
 *
 * Deliberately small: it answers the few dialect questions the query builder
 * needs (identifier quoting, LIMIT syntax, regular-expression operator,
 * default character set) without attempting schema abstraction or a driver
 * registry. Obtain the connection's platform via {@see e_db::getPlatform()}.
 */
interface e_db_platform
{
	/**
	 * Character used to quote identifiers in this dialect.
	 *
	 * @return string
	 */
	public function getIdentifierQuoteCharacter();

	/**
	 * Build a LIMIT/OFFSET clause for this dialect, including a leading space.
	 *
	 * @param int|null $limit Maximum number of rows, or null for no limit.
	 * @param int|null $offset Number of rows to skip, or null/0 for none.
	 * @return string SQL fragment such as ' LIMIT 10 OFFSET 20', or '' when
	 *                neither a limit nor an offset is set.
	 */
	public function getLimitClause($limit, $offset = null);

	/**
	 * Operator for regular-expression matching in this dialect.
	 *
	 * @return string
	 */
	public function getRegexpOperator();

	/**
	 * Default connection character set for this dialect.
	 *
	 * @return string
	 */
	public function getDefaultCharset();
}


/**
 * MySQL/MariaDB dialect: the only platform e107 ships today. Both database
 * backends ({@see e_db_pdo} and {@see e_db_mysql}) speak it.
 */
class e_db_platform_mysql implements e_db_platform
{
	/**
	 * @return string
	 */
	public function getIdentifierQuoteCharacter()
	{
		return '`';
	}

	/**
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return string
	 */
	public function getLimitClause($limit, $offset = null)
	{
		if($limit === null)
		{
			if($offset === null || (int) $offset <= 0)
			{
				return '';
			}

			// MySQL has no standalone OFFSET; the manual's idiom for
			// "skip $offset rows, no upper bound" is a huge row count.
			return ' LIMIT '.(int) $offset.', 18446744073709551615';
		}

		$clause = ' LIMIT '.(int) $limit;

		if($offset !== null && (int) $offset > 0)
		{
			$clause .= ' OFFSET '.(int) $offset;
		}

		return $clause;
	}

	/**
	 * @return string
	 */
	public function getRegexpOperator()
	{
		return 'REGEXP';
	}

	/**
	 * @return string
	 */
	public function getDefaultCharset()
	{
		return 'utf8mb4';
	}
}
