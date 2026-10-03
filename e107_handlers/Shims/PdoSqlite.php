<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims;

use PDOException;
use PDOStatement;

/**
 * pdo_sqlite the same on every PHP: before 7.3.20 and 7.4.8 an empty result describes no column.
 */
final class PdoSqlite
{
	/**
	 * {@see PDOStatement::getColumnMeta()}, but false instead of an error where pdo_sqlite cannot describe the column.
	 *
	 * @param PDOStatement $statement
	 * @param int $column zero-based
	 * @return array|false
	 */
	public static function columnMeta(PDOStatement $statement, $column)
	{
		try
		{
			return $statement->getColumnMeta($column);
		}
		catch(PDOException $e)
		{
			return false;
		}
	}
}
