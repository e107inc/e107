<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	class e_db_filterTest extends \Codeception\Test\Unit
	{

		protected function _before()
		{
			require_once(e_HANDLER."e_db_filter_class.php");
		}

		public function testIdentifierValid()
		{
			$this->assertSame('`user_name`', e_db_filter::identifier('user_name'));
			$this->assertSame('`u`.`user_name`', e_db_filter::identifier('u.user_name'));
		}

		public function testIdentifierInvalid()
		{
			$this->assertFalse(e_db_filter::identifier('a.b.c'));
			$this->assertFalse(e_db_filter::identifier('a-b'));
			$this->assertFalse(e_db_filter::identifier(''));
			$this->assertFalse(e_db_filter::identifier('a`b'));
		}

		public function testDirection()
		{
			$this->assertSame('DESC', e_db_filter::direction('desc'));
			$this->assertSame('ASC', e_db_filter::direction('garbage'));
			$this->assertSame('DESC', e_db_filter::direction('garbage', 'DESC'));
		}

		public function testOrderByValid()
		{
			$this->assertSame('`col1` ASC', e_db_filter::orderBy('col1'));
			$this->assertSame('`col1` DESC', e_db_filter::orderBy('col1 DESC'));
			$this->assertSame('`col1` DESC, `t`.`col2` ASC', e_db_filter::orderBy('col1 desc, t.col2 ASC'));
			$this->assertSame('`col1` DESC', e_db_filter::orderBy('ORDER BY col1 DESC'));
			$this->assertSame('`col1` DESC, `col2` ASC', e_db_filter::orderBy('  col1   DESC  ,  col2  '));
			$this->assertSame('`2fa_state` ASC', e_db_filter::orderBy('2fa_state'));
		}

		public function testOrderByAllowlist()
		{
			$this->assertSame('`col1` ASC', e_db_filter::orderBy('col1', array('col1')));
			$this->assertSame('`COL1` ASC', e_db_filter::orderBy('COL1', array('col1')));
			$this->assertFalse(e_db_filter::orderBy('col2', array('col1')));
		}

		public function testOrderByInjectionAttempts()
		{
			$this->assertFalse(e_db_filter::orderBy("user_name; DROP TABLE e107_user"));
			$this->assertFalse(e_db_filter::orderBy("IF(1=1,user_name,user_password)"));
			$this->assertFalse(e_db_filter::orderBy("RAND()"));
			$this->assertFalse(e_db_filter::orderBy("user_name DESC -- comment"));
			$this->assertFalse(e_db_filter::orderBy("user_name DESC, (SELECT 1)"));
			$this->assertFalse(e_db_filter::orderBy("name` DESC, IF(1=1,1,2) -- "));
			$this->assertFalse(e_db_filter::orderBy("user_name INTO OUTFILE '/tmp/x'"));
			$this->assertFalse(e_db_filter::orderBy("user_name\0DESC"));
		}

		public function testOrderByMalformed()
		{
			$this->assertFalse(e_db_filter::orderBy("user_name ASCC"));
			$this->assertFalse(e_db_filter::orderBy("user_name DESC extra"));
			$this->assertFalse(e_db_filter::orderBy(''));
			$this->assertFalse(e_db_filter::orderBy("col1,,col2"));
		}

		public function testFilterOrderBy()
		{
			$this->assertSame('user_name', e_db_filter::filterOrderBy('user_name', array('user_id', 'user_name'), 'user_id'));
			$this->assertSame('user_id', e_db_filter::filterOrderBy('evil', array('user_id', 'user_name'), 'user_id'));
		}
	}
