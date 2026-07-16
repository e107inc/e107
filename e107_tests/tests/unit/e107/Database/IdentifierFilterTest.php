<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

namespace e107\Database;

	class IdentifierFilterTest extends \Codeception\Test\Unit
	{

		protected function _before()
		{
			require_once(e_HANDLER."Database/IdentifierFilter.php");
		}

		public function testIdentifierValid()
		{
			$this->assertSame('`user_name`', IdentifierFilter::identifier('user_name'));
			$this->assertSame('`u`.`user_name`', IdentifierFilter::identifier('u.user_name'));
		}

		public function testIdentifierInvalid()
		{
			$this->assertFalse(IdentifierFilter::identifier('a.b.c'));
			$this->assertFalse(IdentifierFilter::identifier('a-b'));
			$this->assertFalse(IdentifierFilter::identifier(''));
			$this->assertFalse(IdentifierFilter::identifier('a`b'));
		}

		public function testDirection()
		{
			$this->assertSame('DESC', IdentifierFilter::direction('desc'));
			$this->assertSame('ASC', IdentifierFilter::direction('garbage'));
			$this->assertSame('DESC', IdentifierFilter::direction('garbage', 'DESC'));
		}

		public function testOrderByValid()
		{
			$this->assertSame('`col1` ASC', IdentifierFilter::orderBy('col1'));
			$this->assertSame('`col1` DESC', IdentifierFilter::orderBy('col1 DESC'));
			$this->assertSame('`col1` DESC, `t`.`col2` ASC', IdentifierFilter::orderBy('col1 desc, t.col2 ASC'));
			$this->assertSame('`col1` DESC', IdentifierFilter::orderBy('ORDER BY col1 DESC'));
			$this->assertSame('`col1` DESC, `col2` ASC', IdentifierFilter::orderBy('  col1   DESC  ,  col2  '));
			$this->assertSame('`2fa_state` ASC', IdentifierFilter::orderBy('2fa_state'));
		}

		public function testOrderByAllowlist()
		{
			$this->assertSame('`col1` ASC', IdentifierFilter::orderBy('col1', array('col1')));
			$this->assertSame('`COL1` ASC', IdentifierFilter::orderBy('COL1', array('col1')));
			$this->assertFalse(IdentifierFilter::orderBy('col2', array('col1')));
		}

		public function testOrderByInjectionAttempts()
		{
			$this->assertFalse(IdentifierFilter::orderBy("user_name; DROP TABLE e107_user"));
			$this->assertFalse(IdentifierFilter::orderBy("IF(1=1,user_name,user_password)"));
			$this->assertFalse(IdentifierFilter::orderBy("RAND()"));
			$this->assertFalse(IdentifierFilter::orderBy("user_name DESC -- comment"));
			$this->assertFalse(IdentifierFilter::orderBy("user_name DESC, (SELECT 1)"));
			$this->assertFalse(IdentifierFilter::orderBy("name` DESC, IF(1=1,1,2) -- "));
			$this->assertFalse(IdentifierFilter::orderBy("user_name INTO OUTFILE '/tmp/x'"));
			$this->assertFalse(IdentifierFilter::orderBy("user_name\0DESC"));
		}

		public function testOrderByMalformed()
		{
			$this->assertFalse(IdentifierFilter::orderBy("user_name ASCC"));
			$this->assertFalse(IdentifierFilter::orderBy("user_name DESC extra"));
			$this->assertFalse(IdentifierFilter::orderBy(''));
			$this->assertFalse(IdentifierFilter::orderBy("col1,,col2"));
		}

		public function testFilterOrderBy()
		{
			$this->assertSame('user_name', IdentifierFilter::filterOrderBy('user_name', array('user_id', 'user_name'), 'user_id'));
			$this->assertSame('user_id', IdentifierFilter::filterOrderBy('evil', array('user_id', 'user_name'), 'user_id'));
		}
	}
