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

use e107\Database\SqlFragment;

/**
 * The predicate is only right if it agrees with {@see check_class()} on the
 * same rules, so the decisive test runs both against a table of every rule
 * shape the admin can store and compares row by row.
 */
class MembershipTest extends \Test\Unit
{
	const TABLE = 'uc_membership_probe';

	/**
	 * Every shape a class rule takes: ids, inverted ids, the fixed classes,
	 * comma lists mixing them, the two values no widget writes but the readers
	 * have always admitted, and the padded or broken lists a hand edit leaves.
	 *
	 * @var string[]
	 */
	private static $rules = array(
		'0', '5', '-5', '253', '-253', '255', '-255', '7', '-7',
		'', 'abc', ' 5', ' -5 ',
		'5,253', '5,-7', '-5,-7', '253,-5', '-5,253', '7,-5', '253,255', '0,-5',
		'5, 253', '253,abc', 'abc,-5', '0,', ',0', '5,,253',
	);

	protected function _before()
	{
		require_once(e_HANDLER.'Database/IdentifierFilter.php');
		require_once(e_HANDLER.'Database/SqlFragment.php');
		require_once(e_HANDLER.'Userclass/Membership.php');
	}

	protected function _after()
	{
		\e107::getDb()->execute('DROP TEMPORARY TABLE IF EXISTS `#'.self::TABLE.'`');
	}

	public function testFromListReadsIdsFromAnArrayOrTheCommaForm()
	{
		$fromArray = Membership::fromList(array(253, '0', ' 249 '))->predicate('c');
		$fromString = Membership::fromList('253,0,249')->predicate('c');

		$this->assertSame($this->values($fromArray), $this->values($fromString));
		$this->assertSame(array(253, 0, 249, -253, -249), array_slice($this->values($fromArray), 0, 5));
	}

	public function testFromListKeepsEachIdOnce()
	{
		$values = $this->values(Membership::fromList('253,253,0')->predicate('c'));

		$this->assertSame(array(253, 0, -253), array_slice($values, 0, 3));
	}

	/**
	 * @dataProvider notAMembership
	 * @param mixed $classes
	 */
	public function testFromListRefusesAnythingButClassIds($classes)
	{
		$this->expectException('InvalidArgumentException');

		Membership::fromList($classes);
	}

	public function notAMembership()
	{
		return array(
			'an empty string'   => array(''),
			'an empty array'    => array(array()),
			'a negative id'     => array('253,-5'),
			'a class name'      => array('e_UC_MEMBER'),
			'a blank entry'     => array('253,,0'),
		);
	}

	public function testCurrentIsTheVisitorCheckClassSees()
	{
		$expected = array_map('intval', explode(',', USERCLASS_LIST));

		$this->assertSame($expected, array_slice($this->values(Membership::current()->predicate('c')), 0, count($expected)));
	}

	public function testOfReadsTheUsersClassList()
	{
		$user = \e107::getUser();
		$expected = array_map('intval', $user->getClassList());

		$this->assertSame($expected, array_slice($this->values(Membership::of($user)->predicate('c')), 0, count($expected)));
	}

	public function testPredicateBindsEveryValueAndQuotesTheColumn()
	{
		$fragment = Membership::fromList('253,0')->predicate('n.news_class');

		$this->assertInstanceOf(SqlFragment::class, $fragment);
		$this->assertSame(
			"((`n`.`news_class` NOT LIKE '%,%' AND (`n`.`news_class` IN (:p, :p) OR (`n`.`news_class` < 0 AND `n`.`news_class` NOT IN (:p))))"
			." OR (`n`.`news_class` LIKE '%,%' AND REPLACE(`n`.`news_class`, ' ', '') NOT REGEXP '(^,|,,|,$)'"
			." AND REPLACE(`n`.`news_class`, ' ', '') REGEXP :p AND REPLACE(`n`.`news_class`, ' ', '') NOT REGEXP :p))",
			preg_replace('/:uc\d+/', ':p', $fragment->getSql())
		);
		$this->assertSame(
			array(253, 0, -253, '(^|,)(253|0)(,|$)', '(^|,)-(253|0)(,|$)'),
			array_values($fragment->getParameters())
		);
		preg_match_all('/:uc\d+/', $fragment->getSql(), $placeholders);
		$this->assertSame(array_keys($fragment->getParameters()), array_map(function ($placeholder) { return (string) substr($placeholder, 1); }, $placeholders[0]), 'Each placeholder is used once, in parameter order.');
	}

	public function testAMembershipOfEveryoneOnlyHasNoClassToExclude()
	{
		$sql = Membership::fromList('0')->predicate('c')->getSql();

		$this->assertStringContainsString('`c` < 0)', $sql);
		$this->assertStringNotContainsString('NOT IN', $sql);
	}

	public function testPredicateRefusesAnythingButAColumnName()
	{
		$this->expectException('InvalidArgumentException');

		Membership::fromList('0')->predicate('c) OR 1=1 --');
	}

	public function testTwoPredicatesInOneQueryNeverShareAPlaceholder()
	{
		$membership = Membership::fromList('253,0');
		$first = $membership->predicate('f.forum_class');
		$second = $membership->predicate('fp.forum_class');

		$this->assertSame(array(), array_intersect_key($first->getParameters(), $second->getParameters()));
	}

	public function testTheBuilderTakesThePredicateAsAWhereClause()
	{
		$this->createProbeTable();
		$qb = \e107::getDb()->createQueryBuilder();
		$rows = $qb->select('rule')->from(self::TABLE)
			->where(Membership::fromList('253,0,249')->predicate('int_rule'))
			->where('int_rule', '<', 0)
			->orderBy('id')
			->fetchAll();

		$this->assertSame(array('-5', '-255', '-7'), array_column($rows, 'rule'));
	}

	/**
	 * @dataProvider memberships
	 * @param string $classes
	 */
	public function testThePredicateDecidesWhatCheckClassDecides($classes)
	{
		$this->createProbeTable();
		$membership = Membership::fromList($classes);

		foreach(array('int_rule', 'text_rule') as $column)
		{
			$fragment = $membership->predicate($column);
			$admitted = $this->admittedRules($column, $fragment);

			foreach(self::$rules as $rule)
			{
				if($column === 'int_rule' && !$this->isSingleId($rule))
				{
					continue;
				}

				$this->assertSame(
					$this->checkClassAdmits($rule, $classes),
					in_array($rule, $admitted, true),
					sprintf('rule "%s" in %s for classes %s', $rule, $column, $classes)
				);
			}
		}
	}

	public function memberships()
	{
		return array(
			'a member outside class 5' => array('253,0,249'),
			'a member inside class 5'  => array('253,5,0,249'),
			'a guest'                  => array('254,0,249'),
			'everyone only'            => array('0'),
		);
	}

	/**
	 * What check_class() says, except for the two values the numeric readers
	 * always admitted and check_class() does not: a blank, and a single value
	 * that is not a number.
	 *
	 * @param string $rule
	 * @param string $classes
	 * @return bool
	 */
	private function checkClassAdmits($rule, $classes)
	{
		$trimmed = trim($rule);
		if($trimmed === '' || (strpos($trimmed, ',') === false && !is_numeric($trimmed)))
		{
			return true;
		}

		return (bool) check_class($rule, $classes);
	}

	/**
	 * @param string $rule
	 * @return bool
	 */
	private function isSingleId($rule)
	{
		return $rule !== '' && preg_match('/^-?\d+$/', $rule) === 1;
	}

	private function createProbeTable()
	{
		$sql = \e107::getDb();
		$sql->execute('DROP TEMPORARY TABLE IF EXISTS `#'.self::TABLE.'`');
		$sql->execute('CREATE TEMPORARY TABLE `#'.self::TABLE.'` (id int NOT NULL AUTO_INCREMENT PRIMARY KEY, rule varchar(32) NOT NULL, int_rule int NOT NULL DEFAULT 0, text_rule varchar(32) NOT NULL DEFAULT \'\')');

		foreach(self::$rules as $rule)
		{
			$sql->execute(
				'INSERT INTO `#'.self::TABLE.'` (rule, int_rule, text_rule) VALUES (:rule, :int_rule, :text_rule)',
				array('rule' => $rule, 'int_rule' => $this->isSingleId($rule) ? (int) $rule : 0, 'text_rule' => $rule)
			);
		}
	}

	/**
	 * @param string $column
	 * @param SqlFragment $fragment
	 * @return string[] the rules of every row the predicate admits
	 */
	private function admittedRules($column, SqlFragment $fragment)
	{
		$sql = \e107::getDb();
		$sql->execute('SELECT rule FROM `#'.self::TABLE.'` WHERE '.$fragment->getSql(), $fragment->getParameters());

		$rules = array();
		while($row = $sql->fetch())
		{
			$rules[] = $row['rule'];
		}

		return $rules;
	}

	/**
	 * @param SqlFragment $fragment
	 * @return array the bound values in placeholder order
	 */
	private function values(SqlFragment $fragment)
	{
		return array_values($fragment->getParameters());
	}
}
