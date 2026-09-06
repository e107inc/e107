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

use InvalidArgumentException;

/**
 * One column's participation in an index: a row of
 * information_schema.STATISTICS reduced to the three things that change an
 * index's meaning. Immutable.
 *
 * A part need not name a column: one that indexes an expression names none, and
 * says so through {@see IndexPart::isOverExpression()}. Position within the index
 * is not stored here; it is the position of the part in {@see IndexSchema}'s
 * ordered list.
 *
 * <code>
 * new IndexPart('user_email', 32, 'ASC');    // KEY (`user_email`(32))
 * new IndexPart('post_body', null, null);    // a FULLTEXT part, unordered
 * new IndexPart(null, null, 'ASC', true);    // KEY ((lower(`post_body`)))
 * </code>
 */
final class IndexPart
{
	/** Ascending order, information_schema COLLATION 'A'. */
	const ASC = 'ASC';

	/** Descending order, information_schema COLLATION 'D'. */
	const DESC = 'DESC';

	/** @var string|null COLUMN_NAME; null when this part indexes an expression */
	private $columnName;

	/** @var int|null SUB_PART, the prefix length; null when the whole column is indexed */
	private $subPart;

	/** @var string|null 'ASC', 'DESC', or null when the index type has no order */
	private $direction;

	/**
	 * @param string|null $columnName COLUMN_NAME, and required to be empty when $overExpression is true.
	 * @param int|null $subPart SUB_PART, the indexed prefix length, or null for the whole column.
	 * @param string|null $direction The sort order as COLLATION 'A' or 'D', or as 'ASC' or 'DESC'; null for an index type with no order.
	 * @param bool $overExpression True for a functional index part, which indexes an expression and names no column.
	 * @throws InvalidArgumentException on an empty column name where a column was meant, on a column name given alongside an expression, or on an unrecognised direction.
	 */
	public function __construct($columnName, $subPart, $direction, $overExpression = false)
	{
		$columnName = ($columnName === null) ? '' : (string) $columnName;

		if($overExpression)
		{
			if($columnName !== '')
			{
				throw new InvalidArgumentException('An index part over an expression cannot also name the column "'.$columnName.'".');
			}

			$this->columnName = null;
		}
		else
		{
			if($columnName === '')
			{
				throw new InvalidArgumentException('An index part must name a column.');
			}

			$this->columnName = $columnName;
		}

		$this->subPart = ($subPart === null || $subPart === '') ? null : (int) $subPart;
		$this->direction = self::_normaliseDirection($direction);
	}

	/**
	 * @return string|null null when this part indexes an expression rather than a column.
	 */
	public function getColumnName()
	{
		return $this->columnName;
	}

	/**
	 * Whether this part indexes an expression rather than a column.
	 *
	 * @return bool
	 */
	public function isOverExpression()
	{
		return $this->columnName === null;
	}

	/**
	 * @return int|null the indexed prefix length, null when the whole column is indexed.
	 */
	public function getSubPart()
	{
		return $this->subPart;
	}

	/**
	 * @return string|null 'ASC', 'DESC', or null when the index type has no order.
	 */
	public function getDirection()
	{
		return $this->direction;
	}

	/**
	 * Value equality over all three fields. Two expression parts compare equal on
	 * their sub-part and direction alone, neither of them carrying the expression.
	 *
	 * @param mixed $other
	 * @return bool
	 */
	public function equals($other)
	{
		if(!$other instanceof self)
		{
			return false;
		}

		return $this->columnName === $other->columnName
			&& $this->subPart === $other->subPart
			&& $this->direction === $other->direction;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'columnName' => $this->columnName,
			'subPart'    => $this->subPart,
			'direction'  => $this->direction,
		);
	}

	/**
	 * Map either spelling of a direction, the COLLATION letter or the keyword, onto the canonical one.
	 *
	 * @param string|null $direction
	 * @return string|null
	 * @throws InvalidArgumentException
	 */
	private static function _normaliseDirection($direction)
	{
		if($direction === null || $direction === '')
		{
			return null;
		}

		$direction = strtoupper(trim((string) $direction));

		if($direction === 'A' || $direction === self::ASC)
		{
			return self::ASC;
		}

		if($direction === 'D' || $direction === self::DESC)
		{
			return self::DESC;
		}

		throw new InvalidArgumentException('Invalid index part direction "'.$direction.'": expected A/ASC, D/DESC, or null.');
	}
}
