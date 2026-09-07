<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Userclass;

use e107\Database\IdentifierFilter;
use e107\Database\SqlFragment;
use InvalidArgumentException;

/**
 * The userclasses a visitor belongs to, as an SQL predicate over a column that
 * stores a class rule: a class id, the negative of one for "all but that
 * class", or a comma list of either. The predicate decides exactly what
 * {@see check_class()} decides for the same rule, with one exception: a blank
 * or non-numeric single value admits everyone, as the readers that compared
 * the column with the visitor's ids as numbers always did. The ids are bound
 * as integers, so a varchar column is compared the same way here.
 *
 * <code>
 * $qb->where(Membership::current()->predicate('fb_class'));
 *
 * $rule = Membership::current()->predicate('n.news_class');
 * $sql->execute('SELECT ... FROM #news AS n WHERE '.$rule->getSql(), $rule->getParameters());
 * </code>
 */
final class Membership
{
	/** @var int[] the class ids, each 0 or more, no duplicates */
	private $classes;

	/** @var int names every placeholder this request mints, so two predicates in one query never collide */
	private static $sequence = 0;

	/**
	 * @param int[] $classes
	 */
	private function __construct(array $classes)
	{
		$this->classes = $classes;
	}

	/**
	 * The visitor of this request, read from the same constant {@see check_class()} defaults to.
	 *
	 * @return Membership
	 */
	public static function current()
	{
		return self::fromList(defset('USERCLASS_LIST', '0'));
	}

	/**
	 * @param \e_user_model $user
	 * @return Membership
	 */
	public static function of(\e_user_model $user)
	{
		return self::fromList($user->getClassList());
	}

	/**
	 * @param int[]|string $classes class ids, or the comma-separated form USERCLASS_LIST takes
	 * @return Membership
	 * @throws InvalidArgumentException when the list is empty or holds anything but a class id
	 */
	public static function fromList($classes)
	{
		if(!is_array($classes))
		{
			$classes = explode(',', (string) $classes);
		}

		$ids = array();
		foreach($classes as $class)
		{
			$class = trim((string) $class);
			if($class === '' || !ctype_digit($class))
			{
				throw new InvalidArgumentException(sprintf('"%s" is not a userclass id; a membership holds ids of 0 or more.', $class));
			}
			$ids[(int) $class] = (int) $class;
		}

		if($ids === array())
		{
			throw new InvalidArgumentException('A membership needs at least one userclass; check_class() denies an empty one.');
		}

		return new self(array_values($ids));
	}

	/**
	 * The rows whose class rule in $column admits this membership. Every value is
	 * bound under a name no other predicate of this request uses, so the fragment
	 * drops into {@see \e107\Database\QueryBuilder::where()} or, through getSql()
	 * and getParameters(), into any execute() call.
	 *
	 * @param string $column a column name, or alias.column
	 * @return SqlFragment
	 * @throws InvalidArgumentException when $column is not an identifier
	 */
	public function predicate($column)
	{
		$quoted = IdentifierFilter::identifier($column);
		if($quoted === false)
		{
			throw new InvalidArgumentException(sprintf('"%s" is not a column name.', $column));
		}

		$params = array();
		$excluded = array();
		foreach($this->classes as $id)
		{
			if($id !== 0)
			{
				$excluded[] = -$id;
			}
		}

		$single = $quoted.' IN ('.$this->bind($this->classes, $params).')';
		$inverted = $quoted.' < 0';
		if($excluded !== array())
		{
			$inverted .= ' AND '.$quoted.' NOT IN ('.$this->bind($excluded, $params).')';
		}

		$alternation = implode('|', $this->classes);
		$entries = 'REPLACE('.$quoted.", ' ', '')";
		$list = $entries." NOT REGEXP '(^,|,,|,$)'"
			.' AND '.$entries.' REGEXP '.$this->bind(array('(^|,)('.$alternation.')(,|$)'), $params)
			.' AND '.$entries.' NOT REGEXP '.$this->bind(array('(^|,)-('.$alternation.')(,|$)'), $params);

		$sql = '(('.$quoted." NOT LIKE '%,%' AND (".$single.' OR ('.$inverted.')))'
			.' OR ('.$quoted." LIKE '%,%' AND ".$list.'))';

		return SqlFragment::raw($sql, $params);
	}

	/**
	 * @param array $values
	 * @param array $params filled with name => value for each placeholder returned
	 * @return string the placeholders, comma-separated
	 */
	private function bind(array $values, array &$params)
	{
		$placeholders = array();
		foreach($values as $value)
		{
			$name = 'uc'.(++self::$sequence);
			$params[$name] = $value;
			$placeholders[] = ':'.$name;
		}

		return implode(', ', $placeholders);
	}
}
