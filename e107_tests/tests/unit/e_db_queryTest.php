<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	/**
	 * DB-less tests for {@see e_db_query} and {@see e_db_expr}: every test
	 * asserts the compiled intermediate representation (the SQL string and
	 * the parameter map) against a stub connection, without executing
	 * anything. Round-trip tests against the real backends live in
	 * e_db_abstractTest.
	 */
	class e_db_queryTest extends \Codeception\Test\Unit
	{

		protected function _before()
		{
			require_once(e_HANDLER."e_db_filter_class.php");
			require_once(e_HANDLER."e_db_platform_class.php");
			require_once(e_HANDLER."e_db_query_class.php");
		}

		/**
		 * @return e_db_query
		 */
		private function makeQb(&$stub = null)
		{
			$stub = new e_db_queryTest_dbStub();

			return new e_db_query($stub);
		}

		private function assertThrowsInvalidArgument($callback)
		{
			try
			{
				$callback();
			}
			catch(InvalidArgumentException $e)
			{
				$this->assertInstanceOf('InvalidArgumentException', $e);

				return;
			}

			$this->fail('Expected InvalidArgumentException was not thrown');
		}

		public function testSelectMinimal()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user');

			$this->assertSame('SELECT * FROM `e107_user`', $qb->getSQL());
			$this->assertSame(array(), $qb->getParameters());
		}

		public function testSelectQuotesIdentifiersAndKeepsExpressions()
		{
			$qb = $this->makeQb();
			$qb->select('user_id', 'u.user_name', 'u.*', 'COUNT(*) AS cnt')->from('user', 'u');

			$this->assertSame(
				'SELECT `user_id`, `u`.`user_name`, `u`.*, COUNT(*) AS cnt FROM `e107_user` AS `u`',
				$qb->getSQL()
			);
		}

		public function testSelectAcceptsArray()
		{
			$qb = $this->makeQb();
			$qb->select(array('user_id', 'user_name'))->from('user');

			$this->assertSame('SELECT `user_id`, `user_name` FROM `e107_user`', $qb->getSQL());
		}

		public function testTablePrefixAndHashMarkerResolution()
		{
			$qb = $this->makeQb();
			$qb->select()->from('#user'); // a leading '#' is tolerated, not required

			$this->assertSame('SELECT * FROM `e107_user`', $qb->getSQL());
		}

		public function testWhereExpressionsBindValues()
		{
			$qb = $this->makeQb();
			$qb->select('user_id')->from('user')
				->where($qb->expr()->eq('user_name', 'admin'))
				->andWhere($qb->expr()->gt('user_id', 5));

			$this->assertSame(
				'SELECT `user_id` FROM `e107_user` WHERE (`user_name` = :qb1) AND (`user_id` > :qb2)',
				$qb->getSQL()
			);
			$this->assertSame(array('qb1' => 'admin', 'qb2' => 5), $qb->getParameters());
		}

		public function testWhereAppendsByDefault()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')
				->andWhere($qb->expr()->eq('user_id', 1))
				->where($qb->expr()->eq('user_id', 2)); // appends with AND; there is no reset

			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`user_id` = :qb1) AND (`user_id` = :qb2)',
				$qb->getSQL()
			);
		}

		public function testWhereValueForms()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')
				->where('user_id', 5)
				->where('user_join', '>=', 100)
				->where('user_name', '!=', 'guest'); // != normalises to <>

			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`user_id` = :qb1) AND (`user_join` >= :qb2) AND (`user_name` <> :qb3)',
				$qb->getSQL()
			);
			$this->assertSame(array('qb1' => 5, 'qb2' => 100, 'qb3' => 'guest'), $qb->getParameters());
		}

		public function testWhereArrayForms()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')->where(array('user_class' => 1, 'user_ban' => 0));
			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`user_class` = :qb1 AND `user_ban` = :qb2)',
				$qb->getSQL()
			);

			$qb = $this->makeQb();
			$qb->select()->from('user')->where(array(array('a', '>', 1), array('b', 2)));
			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`a` > :qb1 AND `b` = :qb2)',
				$qb->getSQL()
			);
		}

		public function testWhereThreeArgInDelegatesToIn()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')->where('user_id', 'IN', array(1, 2));

			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`user_id` IN (:qb1, :qb2))',
				$qb->getSQL()
			);
		}

		public function testWhereSingleStringStaysRaw()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')
				->where('user_id = '.$qb->createNamedParameter(1))
				->where($qb->expr()->eq('user_class', 2));

			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (user_id = :qb1) AND (`user_class` = :qb2)',
				$qb->getSQL()
			);
		}

		public function testWhereUnknownOperatorThrows()
		{
			$self = $this;
			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->select()->from('user')->where('a', 'LIKEZ', 1);
			});
		}

		public function testOrWhereAndNestedClosureShareParameterNumbering()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')
				->where('user_class', 1)
				->orWhere(function (e_db_query $q)
				{
					$q->where('user_admin', 1)->where('user_ban', 0);
				});

			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`user_class` = :qb1)'
				.' OR ((`user_admin` = :qb2) AND (`user_ban` = :qb3))',
				$qb->getSQL()
			);
			$this->assertSame(array('qb1' => 1, 'qb2' => 1, 'qb3' => 0), $qb->getParameters());
		}

		public function testWhereNotAndEmptyGroup()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')->where('a', 1)->whereNot(function (e_db_query $q)
			{
				$q->where('b', 2)->orWhere('c', 3);
			});

			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`a` = :qb1) AND (NOT ((`b` = :qb2) OR (`c` = :qb3)))',
				$qb->getSQL()
			);

			$qb = $this->makeQb();
			$qb->select()->from('user')->where(function (e_db_query $q) {});
			$this->assertSame('SELECT * FROM `e107_user` WHERE (1=1)', $qb->getSQL());
		}

		public function testWhereFamilyHelpers()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')->whereNull('x')->orWhereNotNull('y');
			$this->assertSame('SELECT * FROM `e107_user` WHERE (`x` IS NULL) OR (`y` IS NOT NULL)', $qb->getSQL());

			$qb = $this->makeQb();
			$qb->select()->from('user')->whereNotIn('id', array(1, 2))->orWhereIn('k', array(3));
			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`id` NOT IN (:qb1, :qb2)) OR (`k` IN (:qb3))',
				$qb->getSQL()
			);

			$qb = $this->makeQb();
			$qb->select()->from('user')->whereBetween('age', 18, 30)->orWhereNotBetween('age', 40, 50);
			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`age` BETWEEN :qb1 AND :qb2) OR (`age` NOT BETWEEN :qb3 AND :qb4)',
				$qb->getSQL()
			);

			$qb = $this->makeQb();
			$qb->select()->from('user')->whereLike('user_name', 'a%')->orWhereNotLike('user_name', 'b%');
			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`user_name` LIKE :qb1) OR (`user_name` NOT LIKE :qb2)',
				$qb->getSQL()
			);
		}

		public function testWhereColumnBindsNothing()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user', 'u')->whereColumn('u.a', '<', 'u.b')->orWhereColumn('u.c', 'u.d');

			$this->assertSame(
				'SELECT * FROM `e107_user` AS `u` WHERE (`u`.`a` < `u`.`b`) OR (`u`.`c` = `u`.`d`)',
				$qb->getSQL()
			);
			$this->assertSame(array(), $qb->getParameters());

			$self = $this;
			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->select()->from('user')->whereColumn('a; DROP', 'b');
			});
		}

		public function testHavingValueFormAndOrHaving()
		{
			$qb = $this->makeQb();
			$qb->select('user_class', 'COUNT(*) AS c')->from('user')
				->groupBy('user_class')
				->having('c', '>', 5)
				->orHaving('c', '<', 1);

			$this->assertSame(
				'SELECT `user_class`, COUNT(*) AS c FROM `e107_user` GROUP BY `user_class` HAVING (`c` > :qb1) OR (`c` < :qb2)',
				$qb->getSQL()
			);
		}

		public function testSelectErgonomics()
		{
			$qb = $this->makeQb();
			$qb->select('a')->addSelect('b', 'c')->distinct()->from('user');
			$this->assertSame('SELECT DISTINCT `a`, `b`, `c` FROM `e107_user`', $qb->getSQL());

			$qb = $this->makeQb();
			$qb->selectRaw('COUNT(*) AS n')->from('user');
			$this->assertSame('SELECT COUNT(*) AS n FROM `e107_user`', $qb->getSQL());
		}

		public function testAggregateTerminals()
		{
			$stub = null;
			$qb = $this->makeQb($stub);
			$stub->rows = array(array('c' => '42'));
			$this->assertSame(42, $qb->from('user')->where('user_class', 1)->count());
			$this->assertSame('SELECT COUNT(*) FROM `e107_user` WHERE (`user_class` = :qb1)', $stub->lastSql);

			$stub = null;
			$qb = $this->makeQb($stub);
			$stub->rows = array(array('m' => '9'));
			$qb->from('user')->max('user_id');
			$this->assertSame('SELECT MAX(`user_id`) FROM `e107_user`', $stub->lastSql);

			$self = $this;
			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->from('user')->max('x); DROP');
			});
		}

		public function testOrderLimitAndGroupAliases()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')->orderByDesc('user_name')->take(5)->skip(10);
			$this->assertSame('SELECT * FROM `e107_user` ORDER BY `user_name` DESC LIMIT 5 OFFSET 10', $qb->getSQL());

			$qb = $this->makeQb();
			$qb->select('a')->from('user')->groupBy('a')->addGroupBy('b', 'c');
			$this->assertSame('SELECT `a` FROM `e107_user` GROUP BY `a`, `b`, `c`', $qb->getSQL());
		}

		public function testJoinAliases()
		{
			$qb = $this->makeQb();
			$qb->select('*')->from('user', 'u')
				->innerJoin('a', 'ax', 'ax.id = u.id')
				->rightJoin('b', 'bx', 'bx.id = u.id')
				->crossJoin('c', 'cx');

			$this->assertSame(
				'SELECT * FROM `e107_user` AS `u`'
				.' INNER JOIN `e107_a` AS `ax` ON ax.id = u.id'
				.' RIGHT JOIN `e107_b` AS `bx` ON bx.id = u.id'
				.' CROSS JOIN `e107_c` AS `cx`',
				$qb->getSQL()
			);
		}

		public function testSubqueries()
		{
			// whereExists
			$qb = $this->makeQb();
			$qb->select()->from('user', 'u')->whereExists(function (e_db_query $s)
			{
				$s->selectRaw('1')->from('user_extended', 'ue')->where('ue.user_extended_id', '>', 0);
			});
			$this->assertSame(
				'SELECT * FROM `e107_user` AS `u` WHERE (EXISTS (SELECT 1 FROM `e107_user_extended` AS `ue`'
				.' WHERE (`ue`.`user_extended_id` > :qb1)))',
				$qb->getSQL()
			);

			// subquery IN
			$qb = $this->makeQb();
			$qb->select()->from('user')->whereIn('user_id', function (e_db_query $s)
			{
				$s->select('user_id')->from('user_extended')->where('active', 1);
			});
			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (`user_id` IN (SELECT `user_id` FROM `e107_user_extended`'
				.' WHERE (`active` = :qb1)))',
				$qb->getSQL()
			);

			// fromSub
			$qb = $this->makeQb();
			$qb->select('*')->fromSub(function (e_db_query $s)
			{
				$s->select('user_class', 'COUNT(*) AS cnt')->from('user')->groupBy('user_class');
			}, 'counts')->where('cnt', '>', 1);
			$this->assertSame(
				'SELECT * FROM (SELECT `user_class`, COUNT(*) AS cnt FROM `e107_user` GROUP BY `user_class`)'
				.' AS `counts` WHERE (`cnt` > :qb1)',
				$qb->getSQL()
			);

			// joinSub and selectSub
			$qb = $this->makeQb();
			$qb->select('user_id')
				->selectSub(function (e_db_query $s)
				{
					$s->select('COUNT(*)')->from('user_extended')->where('active', 1);
				}, 'ext')
				->from('user', 'u')
				->joinSub(function (e_db_query $s)
				{
					$s->select('user_id')->from('user_extended');
				}, 'ue', 'ue.user_id = u.user_id');
			$this->assertSame(
				'SELECT `user_id`, (SELECT COUNT(*) FROM `e107_user_extended` WHERE (`active` = :qb1)) AS `ext`'
				.' FROM `e107_user` AS `u`'
				.' INNER JOIN (SELECT `user_id` FROM `e107_user_extended`) AS `ue` ON ue.user_id = u.user_id',
				$qb->getSQL()
			);

			// an unshared sub-query builder is rejected
			$self = $this;
			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$qb = $self->makeQb();
				$other = $self->makeQb();
				$other->select('user_id')->from('user_extended');
				$qb->select()->from('user')->whereExists($other);
			});
		}

		public function testMultiRowInsertAndModifiers()
		{
			$qb = $this->makeQb();
			$qb->insert('tmp')->values(array(
				array('a' => 1, 'b' => 2),
				array('a' => 3, 'b' => 4),
			));
			$this->assertSame(
				'INSERT INTO `e107_tmp` (`a`, `b`) VALUES (:qb1, :qb2), (:qb3, :qb4)',
				$qb->getSQL()
			);

			$qb = $this->makeQb();
			$qb->insertOrIgnore('tmp')->values(array('a' => 1));
			$this->assertSame('INSERT IGNORE INTO `e107_tmp` (`a`) VALUES (:qb1)', $qb->getSQL());

			$self = $this;
			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->insert('tmp')->values(array(
					array('a' => 1, 'b' => 2),
					array('a' => 3, 'c' => 4),
				))->getSQL();
			});
		}

		public function testUpsert()
		{
			$qb = $this->makeQb();
			$qb->insert('user')->upsert(array('user_id' => 5, 'user_name' => 'Bob'), 'user_id');
			$this->assertSame(
				'INSERT INTO `e107_user` (`user_id`, `user_name`) VALUES (:qb1, :qb2)'
				.' ON DUPLICATE KEY UPDATE `user_name` = VALUES(`user_name`)',
				$qb->getSQL()
			);

			$qb = $this->makeQb();
			$qb->insert('user')->upsert(
				array('user_id' => 5, 'user_name' => 'Bob', 'user_email' => 'b@example.com'),
				'user_id',
				array('user_name')
			);
			$this->assertSame(
				'INSERT INTO `e107_user` (`user_id`, `user_name`, `user_email`) VALUES (:qb1, :qb2, :qb3)'
				.' ON DUPLICATE KEY UPDATE `user_name` = VALUES(`user_name`)',
				$qb->getSQL()
			);
		}

		public function testIncrementDecrement()
		{
			$qb = $this->makeQb();
			$qb->update('user')->increment('user_visits')->where('user_id', 10);
			$this->assertSame(
				'UPDATE `e107_user` SET `user_visits` = `user_visits` + :qb1 WHERE (`user_id` = :qb2)',
				$qb->getSQL()
			);
			$this->assertSame(array('qb1' => 1, 'qb2' => 10), $qb->getParameters());

			$qb = $this->makeQb();
			$qb->update('user')->decrement('user_score', 5, array('user_name' => 'x'))->where('user_id', 10);
			$this->assertSame(
				'UPDATE `e107_user` SET `user_score` = `user_score` - :qb1, `user_name` = :qb2 WHERE (`user_id` = :qb3)',
				$qb->getSQL()
			);
		}

		public function testUpdateOrInsert()
		{
			// existing row -> UPDATE
			$stub = null;
			$qb = $this->makeQb($stub);
			$stub->rows = array(array('1' => '1'));
			$stub->executeReturn = 1;
			$qb->update('user')->updateOrInsert(array('user_id' => 5), array('user_name' => 'Bob'));
			$this->assertSame('UPDATE `e107_user` SET `user_name` = :qb1 WHERE (`user_id` = :qb2)', $stub->lastSql);

			// no row -> INSERT
			$stub = null;
			$qb = $this->makeQb($stub);
			$stub->rows = array();
			$stub->executeReturn = 1;
			$qb->update('user')->updateOrInsert(array('user_id' => 5), array('user_name' => 'Bob'));
			$this->assertSame('INSERT INTO `e107_user` (`user_id`, `user_name`) VALUES (:qb1, :qb2)', $stub->lastSql);
		}

		public function testLocking()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')->where('user_id', 1)->lockForUpdate();
			$this->assertSame('SELECT * FROM `e107_user` WHERE (`user_id` = :qb1) FOR UPDATE', $qb->getSQL());

			$qb = $this->makeQb();
			$qb->select()->from('user')->setMaxResults(5)->sharedLock();
			$this->assertSame('SELECT * FROM `e107_user` LIMIT 5 LOCK IN SHARE MODE', $qb->getSQL());
		}

		public function testUnion()
		{
			$qb = $this->makeQb();
			$arm = $qb->newUnionQuery();
			$qb->select('user_id')->from('user')->where('user_class', 2);
			$arm->select('user_id')->from('user_extended')->where('active', 1);
			$qb->unionAll($arm);

			$this->assertSame(
				'SELECT `user_id` FROM `e107_user` WHERE (`user_class` = :qb1)'
				.' UNION ALL SELECT `user_id` FROM `e107_user_extended` WHERE (`active` = :qb2)',
				$qb->getSQL()
			);
			$this->assertSame(array('qb1' => 2, 'qb2' => 1), $qb->getParameters());

			// an unshared arm is rejected
			$self = $this;
			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$qb = $self->makeQb();
				$other = $self->makeQb();
				$other->select('user_id')->from('user_extended');
				$qb->select('user_id')->from('user')->union($other);
			});
		}

		public function testExprParityHelpers()
		{
			$qb = $this->makeQb();
			$expr = $qb->expr();

			$this->assertSame('`a` BETWEEN :qb1 AND :qb2', $expr->between('a', 1, 2));
			$this->assertSame('`a` NOT BETWEEN :qb3 AND :qb4', $expr->notBetween('a', 3, 4));
			$this->assertSame('`a` NOT LIKE :qb5', $expr->notLike('a', '%x%'));
			$this->assertSame('`a` >= :qb6', $expr->comparison('a', '>=', 9));
			$this->assertSame('`a` = `b`', $expr->compareColumns('a', 'b'));
			$this->assertSame('(`a` = 1) AND (`b` = 2)', $expr->andX('`a` = 1', '`b` = 2'));
			$this->assertSame('(`a` = 1) OR (`b` = 2)', $expr->orX('`a` = 1', '`b` = 2'));

			$self = $this;
			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->expr()->comparison('a', 'BOGUS', 1);
			});
		}

		public function testInsertUsingAndInsertGetId()
		{
			$qb = $this->makeQb();
			$qb->insert('archive')->insertUsing(array('id', 'name'), function (e_db_query $s)
			{
				$s->select('id', 'name')->from('live')->where('expired', 1);
			});
			$this->assertSame(
				'INSERT INTO `e107_archive` (`id`, `name`) SELECT `id`, `name` FROM `e107_live` WHERE (`expired` = :qb1)',
				$qb->getSQL()
			);

			$qb = $this->makeQb();
			$qb->insert('archive')->insertUsing(array(), function (e_db_query $s)
			{
				$s->select('*')->from('live');
			});
			$this->assertSame('INSERT INTO `e107_archive` SELECT * FROM `e107_live`', $qb->getSQL());

			$stub = null;
			$qb = $this->makeQb($stub);
			$stub->insertId = 99;
			$stub->executeReturn = 1;
			$this->assertSame(99, $qb->insert('tmp')->insertGetId(array('a' => 1)));
			$this->assertSame('INSERT INTO `e107_tmp` (`a`) VALUES (:qb1)', $stub->lastSql);
		}

		public function testNicheOrderingAndHaving()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')->latest('user_join')->oldest('user_id')->inRandomOrder();
			$this->assertSame(
				'SELECT * FROM `e107_user` ORDER BY `user_join` DESC, `user_id` ASC, RAND()',
				$qb->getSQL()
			);

			$qb = $this->makeQb();
			$qb->select('a', 'COUNT(*) AS c')->from('user')->groupBy('a')->havingBetween('c', 1, 10);
			$this->assertSame(
				'SELECT `a`, COUNT(*) AS c FROM `e107_user` GROUP BY `a` HAVING (`c` BETWEEN :qb1 AND :qb2)',
				$qb->getSQL()
			);
		}

		public function testDateWhereHelpers()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')
				->whereDate('user_join', '>=', '2026-01-01')
				->orWhereYear('user_join', 2026);
			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (DATE(`user_join`) >= :qb1) OR (YEAR(`user_join`) = :qb2)',
				$qb->getSQL()
			);

			$qb = $this->makeQb();
			$qb->select()->from('user')
				->whereMonth('user_join', 6)
				->whereDay('user_join', 14)
				->whereTime('user_join', '<', '12:00:00');
			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (MONTH(`user_join`) = :qb1) AND (DAY(`user_join`) = :qb2)'
				.' AND (TIME(`user_join`) < :qb3)',
				$qb->getSQL()
			);
		}

		public function testJsonAndFullTextHelpers()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')
				->whereJsonContains('roles', 'admin')
				->orWhereJsonDoesntContain('roles', 'guest');
			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (JSON_CONTAINS(`roles`, :qb1)) OR (NOT JSON_CONTAINS(`roles`, :qb2))',
				$qb->getSQL()
			);
			$this->assertSame(array('qb1' => '"admin"', 'qb2' => '"guest"'), $qb->getParameters());

			$qb = $this->makeQb();
			$qb->select()->from('user')
				->whereJsonContainsKey('user_prefs', '$.opt')
				->whereJsonLength('user_tags', '>=', 3);
			$this->assertSame(
				'SELECT * FROM `e107_user` WHERE (JSON_CONTAINS_PATH(`user_prefs`, \'one\', :qb1))'
				.' AND (JSON_LENGTH(`user_tags`) >= :qb2)',
				$qb->getSQL()
			);

			$qb = $this->makeQb();
			$qb->select()->from('news')->whereFullText(array('news_title', 'news_body'), 'e107 release');
			$this->assertSame(
				'SELECT * FROM `e107_news` WHERE (MATCH (`news_title`, `news_body`) AGAINST (:qb1))',
				$qb->getSQL()
			);
		}

		public function testWhereIn()
		{
			$qb = $this->makeQb();
			$qb->select('user_name')->from('user')->whereIn('user_id', array(1, 2, 3));

			$this->assertSame(
				'SELECT `user_name` FROM `e107_user` WHERE (`user_id` IN (:qb1, :qb2, :qb3))',
				$qb->getSQL()
			);
			$this->assertSame(array('qb1' => 1, 'qb2' => 2, 'qb3' => 3), $qb->getParameters());
		}

		public function testEmptyInListsCompileToConstantPredicates()
		{
			$qb = $this->makeQb();
			$this->assertSame('1=0', $qb->expr()->in('user_id', array()));
			$this->assertSame('1=1', $qb->expr()->notIn('user_id', array()));
			$this->assertSame(array(), $qb->getParameters());
		}

		public function testJoins()
		{
			$qb = $this->makeQb();
			$qb->select('u.user_id', 'ue.user_extended_id')
				->from('user', 'u')
				->leftJoin('user_extended', 'ue', 'ue.user_extended_id = u.user_id')
				->join('userclass_classes', 'uc', 'uc.userclass_id = u.user_class');

			$this->assertSame(
				'SELECT `u`.`user_id`, `ue`.`user_extended_id` FROM `e107_user` AS `u`'
				.' LEFT JOIN `e107_user_extended` AS `ue` ON ue.user_extended_id = u.user_id'
				.' INNER JOIN `e107_userclass_classes` AS `uc` ON uc.userclass_id = u.user_class',
				$qb->getSQL()
			);
		}

		public function testGroupByAndHaving()
		{
			$qb = $this->makeQb();
			$qb->select('user_class', 'COUNT(*) AS cnt')->from('user')
				->groupBy('user_class')
				->having('COUNT(*) > '.$qb->createNamedParameter(1));

			$this->assertSame(
				'SELECT `user_class`, COUNT(*) AS cnt FROM `e107_user` GROUP BY `user_class` HAVING (COUNT(*) > :qb1)',
				$qb->getSQL()
			);
			$this->assertSame(array('qb1' => 1), $qb->getParameters());
		}

		public function testOrderByTwoArgumentForm()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')->orderBy('user_name', 'desc')->addOrderBy('user_id', 'ASC');

			$this->assertSame(
				'SELECT * FROM `e107_user` ORDER BY `user_name` DESC, `user_id` ASC',
				$qb->getSQL()
			);
		}

		public function testOrderByLegacyFragmentForm()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')->orderBy('user_name DESC, u.user_id');

			$this->assertSame(
				'SELECT * FROM `e107_user` ORDER BY `user_name` DESC, `u`.`user_id` ASC',
				$qb->getSQL()
			);
		}

		public function testLimitAndOffsetAreInlinedAsIntegers()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')->setMaxResults('10; DROP TABLE x')->setFirstResult('20');

			$this->assertSame('SELECT * FROM `e107_user` LIMIT 10 OFFSET 20', $qb->getSQL());

			$qb = $this->makeQb();
			$qb->select()->from('user')->setFirstResult(20); // offset without a limit

			$this->assertSame('SELECT * FROM `e107_user` LIMIT 20, 18446744073709551615', $qb->getSQL());
		}

		public function testInsert()
		{
			$qb = $this->makeQb();
			$qb->insert('tmp')->values(array(
				'tmp_ip'   => '127.0.0.1',
				'tmp_time' => 12345,
				'tmp_info' => 'builder test',
			));

			$this->assertSame(
				'INSERT INTO `e107_tmp` (`tmp_ip`, `tmp_time`, `tmp_info`) VALUES (:qb1, :qb2, :qb3)',
				$qb->getSQL()
			);
			$this->assertSame(
				array('qb1' => '127.0.0.1', 'qb2' => 12345, 'qb3' => 'builder test'),
				$qb->getParameters()
			);
		}

		public function testReplace()
		{
			$qb = $this->makeQb();
			$qb->replace('tmp')->values(array(
				'tmp_ip'   => '127.0.0.1',
				'tmp_info' => 'builder test',
			));

			$this->assertSame(
				'REPLACE INTO `e107_tmp` (`tmp_ip`, `tmp_info`) VALUES (:qb1, :qb2)',
				$qb->getSQL()
			);
			$this->assertSame(
				array('qb1' => '127.0.0.1', 'qb2' => 'builder test'),
				$qb->getParameters()
			);
		}

		public function testUpdate()
		{
			$qb = $this->makeQb();
			$qb->update('tmp')->set('tmp_info', 'changed')
				->where($qb->expr()->eq('tmp_ip', '127.0.0.1'))
				->setMaxResults(1);

			$this->assertSame(
				'UPDATE `e107_tmp` SET `tmp_info` = :qb1 WHERE (`tmp_ip` = :qb2) LIMIT 1',
				$qb->getSQL()
			);
			$this->assertSame(array('qb1' => 'changed', 'qb2' => '127.0.0.1'), $qb->getParameters());
		}

		public function testUpdateSetExpression()
		{
			$qb = $this->makeQb();
			$qb->update('user')
				->setExpression('user_visits', 'user_visits + 1')
				->setExpression('user_score', 'user_score + '.$qb->createNamedParameter(5))
				->set('user_name', 'changed')
				->where($qb->expr()->eq('user_id', 10));

			$this->assertSame(
				'UPDATE `e107_user` SET `user_visits` = user_visits + 1, `user_score` = user_score + :qb1,'
				.' `user_name` = :qb2 WHERE (`user_id` = :qb3)',
				$qb->getSQL()
			);
			$this->assertSame(array('qb1' => 5, 'qb2' => 'changed', 'qb3' => 10), $qb->getParameters());
		}

		public function testDelete()
		{
			$qb = $this->makeQb();
			$qb->delete('tmp')->where($qb->expr()->eq('tmp_ip', '127.0.0.1'));

			$this->assertSame('DELETE FROM `e107_tmp` WHERE (`tmp_ip` = :qb1)', $qb->getSQL());
		}

		public function testCreateNamedParameterTypeOverride()
		{
			$qb = $this->makeQb();
			$placeholder = $qb->createNamedParameter('5', e_db::PARAM_INT);

			$this->assertSame(':qb1', $placeholder);
			$this->assertSame(array('qb1' => array('value' => '5', 'type' => e_db::PARAM_INT)), $qb->getParameters());
		}

		public function testExpressionFragments()
		{
			$qb = $this->makeQb();
			$expr = $qb->expr();

			$this->assertSame('`a` = :qb1', $expr->eq('a', 1));
			$this->assertSame('`a` <> :qb2', $expr->neq('a', 1));
			$this->assertSame('`a` < :qb3', $expr->lt('a', 1));
			$this->assertSame('`a` <= :qb4', $expr->lte('a', 1));
			$this->assertSame('`a` > :qb5', $expr->gt('a', 1));
			$this->assertSame('`a` >= :qb6', $expr->gte('a', 1));
			$this->assertSame('`a` LIKE :qb7', $expr->like('a', 'raw%'));
			$this->assertSame('`a` REGEXP :qb8', $expr->regexp('a', '^foo'));
			$this->assertSame('`a` IS NULL', $expr->isNull('a'));
			$this->assertSame('`a` IS NOT NULL', $expr->isNotNull('a'));
			$this->assertSame('`a` NOT IN (:qb9)', $expr->notIn('a', array('x')));
		}

		public function testLikeHelpersEscapeWildcards()
		{
			$qb = $this->makeQb();
			$expr = $qb->expr();

			$expr->contains('a', '50%');
			$expr->startsWith('a', 'b_c');
			$expr->endsWith('a', 'd\e');

			$this->assertSame(
				array('qb1' => '%50\%%', 'qb2' => 'b\_c%', 'qb3' => '%d\\\\e'),
				$qb->getParameters()
			);
		}

		public function testHostileIdentifiersThrow()
		{
			$self = $this;

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->select()->from('user; DROP TABLE x')->getSQL();
			});

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->select()->from('user', 'u`u')->getSQL();
			});

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$qb = $self->makeQb();
				$qb->expr()->eq('a`b', 1);
			});

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->update('tmp')->set('tmp_info = (SELECT 1)', 'x');
			});

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->select()->from('user')->orderBy('user_name; DROP TABLE x');
			});

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->select()->from('user')->orderBy('IF(1=1,user_name,user_id)');
			});

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->select()->from('user')->orderBy('user_name', 'SIDEWAYS');
			});

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->whereIn('a INTO OUTFILE', array(1));
			});
		}

		public function testIncompleteQueriesThrow()
		{
			$self = $this;

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->select()->getSQL(); // no from()
			});

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->insert('tmp')->getSQL(); // no values()
			});

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->update('tmp')->getSQL(); // no set()
			});

			$this->assertThrowsInvalidArgument(function () use ($self)
			{
				$self->makeQb()->replace('tmp')->getSQL(); // no values()
			});
		}

		public function testExecutePassesCompiledQueryToConnection()
		{
			$stub = null;
			$qb = $this->makeQb($stub);
			$qb->update('tmp')->set('tmp_info', 'x')->where($qb->expr()->eq('tmp_ip', 'ip'));

			$stub->executeReturn = 1;
			$this->assertSame(1, $qb->execute());
			$this->assertSame('UPDATE `e107_tmp` SET `tmp_info` = :qb1 WHERE (`tmp_ip` = :qb2)', $stub->lastSql);
			$this->assertSame(array('qb1' => 'x', 'qb2' => 'ip'), $stub->lastParams);
		}

		public function testFetchTerminalsMapRows()
		{
			$rows = array(
				array('id' => '1', 'name' => 'alpha'),
				array('id' => '2', 'name' => 'beta'),
			);

			// fetchAll
			$stub = null;
			$qb = $this->makeQb($stub);
			$stub->rows = $rows;
			$this->assertSame($rows, $qb->select()->from('tmp')->fetchAll());

			// fetchAll($indexBy)
			$stub->rows = $rows;
			$indexed = $qb->fetchAll('name');
			$this->assertSame(array('alpha', 'beta'), array_keys($indexed));
			$this->assertSame('1', $indexed['alpha']['id']);

			// fetchRow
			$stub->rows = $rows;
			$this->assertSame($rows[0], $qb->fetchRow());

			// fetchOne
			$stub->rows = $rows;
			$this->assertSame('1', $qb->fetchOne());

			// fetchOne with no rows
			$stub->rows = array();
			$this->assertNull($qb->fetchOne());

			// fetchColumn, first and named
			$stub->rows = $rows;
			$this->assertSame(array('1', '2'), $qb->fetchColumn());
			$stub->rows = $rows;
			$this->assertSame(array('alpha', 'beta'), $qb->fetchColumn('name'));

			// fetchPairs, default and named
			$stub->rows = $rows;
			$this->assertSame(array('1' => 'alpha', '2' => 'beta'), $qb->fetchPairs());
			$stub->rows = $rows;
			$this->assertSame(array('alpha' => '1', 'beta' => '2'), $qb->fetchPairs('name', 'id'));

			// terminals fail soft on query error, like retrieve()
			$stub->rows = $rows;
			$stub->executeReturn = false;
			$this->assertSame(array(), $qb->fetchAll());
			$this->assertSame(array(), $qb->fetchRow());
			$this->assertNull($qb->fetchOne());
		}

		public function testPlatformMysql()
		{
			$platform = new e_db_platform_mysql();

			$this->assertInstanceOf('e_db_platform', $platform);
			$this->assertSame('`', $platform->getIdentifierQuoteCharacter());
			$this->assertSame('REGEXP', $platform->getRegexpOperator());
			$this->assertSame('utf8mb4', $platform->getDefaultCharset());
			$this->assertSame('', $platform->getLimitClause(null));
			$this->assertSame(' LIMIT 10', $platform->getLimitClause(10));
			$this->assertSame(' LIMIT 10 OFFSET 20', $platform->getLimitClause(10, 20));
			$this->assertSame(' LIMIT 20, 18446744073709551615', $platform->getLimitClause(null, 20));
			$this->assertSame(
				'REPLACE INTO `e107_tmp` (`a`, `b`) VALUES (:qb1, :qb2)',
				$platform->compileReplace('`e107_tmp`', array('`a`', '`b`'), array(':qb1', ':qb2'))
			);
			$this->assertSame(
				'INSERT INTO `e107_tmp` (`a`, `b`) VALUES (:qb1, :qb2), (:qb3, :qb4)',
				$platform->compileInsert('`e107_tmp`', array('`a`', '`b`'), array('(:qb1, :qb2)', '(:qb3, :qb4)'))
			);
			$this->assertSame(
				'INSERT IGNORE INTO `e107_tmp` (`a`) VALUES (:qb1)',
				$platform->compileInsert('`e107_tmp`', array('`a`'), array('(:qb1)'), 'IGNORE')
			);
			$this->assertSame(
				'INSERT INTO `e107_tmp` (`a`, `b`) VALUES (:qb1, :qb2) ON DUPLICATE KEY UPDATE `b` = VALUES(`b`)',
				$platform->compileUpsert('`e107_tmp`', array('`a`', '`b`'), array('(:qb1, :qb2)'), array('`b` = VALUES(`b`)'))
			);
			$this->assertSame('VALUES(`b`)', $platform->getUpsertValueReference('`b`'));
			$this->assertSame(' FOR UPDATE', $platform->getForUpdateClause());
			$this->assertSame(' LOCK IN SHARE MODE', $platform->getSharedLockClause());
			$this->assertSame(
				'INSERT INTO `t` (`a`, `b`) SELECT `a`, `b` FROM `s`',
				$platform->compileInsertSelect('`t`', array('`a`', '`b`'), 'SELECT `a`, `b` FROM `s`')
			);
			$this->assertSame(
				'INSERT INTO `t` SELECT * FROM `s`',
				$platform->compileInsertSelect('`t`', array(), 'SELECT * FROM `s`')
			);
			$this->assertSame('RAND()', $platform->getRandomFunction());
			$this->assertSame('YEAR(`c`)', $platform->compileDatePart('year', '`c`'));
			$this->assertSame('JSON_CONTAINS(`c`, :p)', $platform->compileJsonContains('`c`', ':p'));
			$this->assertSame("JSON_CONTAINS_PATH(`c`, 'one', :p)", $platform->compileJsonContainsKey('`c`', ':p'));
			$this->assertSame('JSON_LENGTH(`c`)', $platform->compileJsonLength('`c`'));
			$this->assertSame('MATCH (`a`, `b`) AGAINST (:p)', $platform->compileFullText(array('`a`', '`b`'), ':p'));
		}
	}


	/**
	 * Stand-in for an e_db connection: resolves table names with a fixed
	 * prefix and no language routing, records what execute() receives and
	 * serves queued rows through fetch().
	 */
	class e_db_queryTest_dbStub
	{
		public $lastSql = null;
		public $lastParams = null;
		public $rows = array();
		public $executeReturn = 0;
		public $insertId = 0;

		public function resolveTableName($table)
		{
			$table = ltrim((string) $table, '#');

			if(!preg_match('/^[A-Za-z0-9_]+$/D', $table))
			{
				return false;
			}

			return 'e107_'.$table;
		}

		public function quoteIdentifier($identifier)
		{
			return e_db_filter::identifier($identifier);
		}

		public function getPlatform()
		{
			return new e_db_platform_mysql();
		}

		public function execute($sql, $params = array())
		{
			$this->lastSql = $sql;
			$this->lastParams = $params;

			return $this->executeReturn;
		}

		public function fetch()
		{
			$row = array_shift($this->rows);

			return ($row === null) ? false : $row;
		}

		public function lastInsertId()
		{
			return $this->insertId;
		}
	}
