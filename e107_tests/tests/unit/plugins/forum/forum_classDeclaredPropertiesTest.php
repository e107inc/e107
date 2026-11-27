<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 */
class forum_classDeclaredPropertiesTest extends \Test\Unit
{
	use \Test\ForumRows;

	protected function _before()
	{
		$this->haveForumTables();

		require_once(e_PLUGIN.'forum/forum_class.php');
	}

	protected function _after()
	{
		$this->dropForumRows();
	}

	public function testConstructingTheForumCreatesNoUndeclaredProperty()
	{
		self::assertHoldsOnlyDeclaredProperties(new e107forum());
	}
}
