<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The Admin History archive holds every stored column of a deleted record, so reading it,
 * and exporting it as CSV, is a main administrator's alone.
 *
 * That the page itself refuses everyone else is asserted against a running site in
 * AdminRoutePermsCest. What is asserted here is that the navigation link agrees with it,
 * or the History icon sits on the admin front page of an administrator the page then
 * turns away.
 */
class historyAdminPageTest extends \Test\Unit
{
	const PAGE = 'history.php';

	protected function _before()
	{
		e107::loadAdminIcons();
		e107::includeLan(e_LANGUAGEDIR . 'English/English.php');
		e107::includeLan(e_LANGUAGEDIR . 'English/admin/lan_admin.php');
		include_once(e_HANDLER . 'user_handler.php');
	}

	public function testTheHistoryLinkIsOfferedToMainAdministratorsAndNobodyElse()
	{
		$link = $this->navigationPerm();

		$this->assertTrue(e_userperms::simulateHasAdminPerms($link, '0'),
			'A main administrator is no longer shown the History link.');

		$this->assertFalse(e_userperms::simulateHasAdminPerms($link, '7'),
			'The retired History permission, which upgraded sites still carry in user_perms, '
			. 'is still shown the History link.');

		$permissions = new e_userperms();
		$everyPermission = implode('.', array_keys($permissions->getPermList('core')));

		$this->assertNotEmpty($everyPermission);
		$this->assertFalse(e_userperms::simulateHasAdminPerms($link, $everyPermission),
			'An administrator holding every grantable permission is still shown the History link.');
	}

	private function navigationPerm()
	{
		foreach(e107::getNav()->adminLinks('legacy') as $link)
		{
			if(basename($link[0]) === self::PAGE)
			{
				return $link[3];
			}
		}

		$this->fail('The core admin navigation no longer has a link to ' . self::PAGE . '.');
	}
}
