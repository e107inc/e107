<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * usersettings.php edits the member who is signed in and nobody else; an administrator's old usersettings.php?<id> link opens the admin user editor (#6004).
 */
class usersettingsAdminEditTest extends \Test\Unit
{
	public function testAnAdministratorsNumericLinkOpensTheAdminUserEditor()
	{
		$page = $this->numericLinkAs('e107::getUser()->setData(e107::user(USERID)); ');

		self::assertStringContainsString('REDIRECTED TO '.e_ADMIN_ABS.'users.php?mode=main&action=edit&id=5', $page);
		self::assertStringNotContainsString("name='updatesettings'", $page, 'no settings form is rendered for anyone');
	}

	public function testAMembersNumericLinkIsNotSentToTheAdminUserEditor()
	{
		$page = $this->numericLinkAs("e107::getUser()->setData(array('user_admin' => 0, 'user_perms' => '') + e107::user(USERID)); ");

		self::assertStringNotContainsString('REDIRECTED TO '.e_ADMIN_ABS, $page);
		self::assertStringNotContainsString("name='updatesettings'", $page, 'a numeric query never opens a settings form');
	}

	/**
	 * Opens usersettings.php?5 as the user $signIn sets up, with every redirect printed instead of sent.
	 *
	 * @param string $signIn PHP run in the booted child before the page
	 * @return string
	 */
	private function numericLinkAs($signIn)
	{
		$seed = $signIn.'e107::getRedirect(); ';
		$seed .= 'class usersettingsPrintedRedirect extends redirection { public function go($url = "", $replace = true, $http_response_code = null, $preventCache = true, $allowOffsite = false) { echo "REDIRECTED TO ".$url."\n"; } } ';
		$seed .= "e107::setRegistry('core/e107/singleton/redirection', new usersettingsPrintedRedirect()); ";

		return $this->renderInBootedCli('usersettings.php', $seed, '', array('e_QUERY' => '5'));
	}
}
