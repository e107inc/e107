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

		public function testWhereReplacesPreviousPredicates()
		{
			$qb = $this->makeQb();
			$qb->select()->from('user')
				->andWhere($qb->expr()->eq('user_id', 1))
				->where($qb->expr()->eq('user_id', 2)); // resets the clause

			$this->assertSame('SELECT * FROM `e107_user` WHERE (`user_id` = :qb2)', $qb->getSQL());
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
	}
