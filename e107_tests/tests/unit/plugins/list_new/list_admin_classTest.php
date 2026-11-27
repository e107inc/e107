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
class list_admin_classTest extends \Test\Unit
{
	protected function _before()
	{
		require_once(e_PLUGIN.'list_new/list_admin_class.php');
	}

	public function testConstructingTheAdminHandlerCreatesNoUndeclaredProperty()
	{
		self::assertHoldsOnlyDeclaredProperties(new list_admin(new stdClass()));
	}
}
