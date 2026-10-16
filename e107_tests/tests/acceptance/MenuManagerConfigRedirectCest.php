<?php

/**
 * Where the Menu Manager's mode=conf request sends the administrator: a plugin's menu configuration page and nowhere else.
 */
class MenuManagerConfigRedirectCest
{
	const MENUS = '/e107_admin/menus.php?mode=conf&id=1&path=';

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->stopFollowingRedirects();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
	}

	public function menuConfigurationPagesOfBothNamesStillOpen(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENUS.'login_menu%2Fconfig');
		$I->seeRedirectTo('/e107_plugins/login_menu/config.php?id=1&iframe=1');

		$I->amOnPage(self::MENUS.'user%2Fusertheme_menu_config');
		$I->seeRedirectTo('/e107_plugins/user/usertheme_menu_config.php?id=1&iframe=1');
	}

	public function anotherPageOfTheSiteIsNotOpened(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENUS.'..%2Fnews.php%3Fx%3D');

		$I->seeNoRedirectTo('news.php');
	}

	public function aPluginPageThatIsNoMenuConfigurationIsNotOpened(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENUS.'forum%2Fforum_viewtopic');

		$I->seeNoRedirectTo('forum_viewtopic');
	}
}
