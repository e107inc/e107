<?php


class AdminLoginCest
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	// tests
	public function testAdminLogin(AcceptanceTester $I)
	{

		$I->wantTo("Test the admin area login process");

		$this->e107Login($I);

		$I->dontSeeInSource('Unauthorized access!');

		$I->see("Latest");

		$I->see("Status");

	}


	private function e107Login(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_admin/admin.php');
		$I->see("Admin Area");
		$I->see("login");

		$I->fillField('authname', 'admin');
		$I->fillField('authpass', 'admin');

		$I->click('authsubmit');

		$I->see("Admin's Control Panel");




	}


	public function testAdminURLS(AcceptanceTester $I)
	{

		$this->e107Login($I);

		$urls = array(
			'admin.php?[debug=basic+]',
			'cache.php',
			'emoticon.php',
			'frontpage.php',
			'frontpage.php?mode=create',
			'language.php',
			'language.php?mode=main&action=db',
			'language.php?mode=main&action=tools',
			'language.php?mode=main&action=deprecated',
			'meta.php',
			'prefs.php',
			'search.php',
			'search.php?settings',
			'links.php',
			'links.php?mode=main&action=create',
			'links.php?mode=main&action=prefs',
			'links.php?mode=main&action=tools',
			'eurl.php',
			'eurl.php?mode=main&action=alias',
			'eurl.php?mode=main&action=simple',
			'eurl.php?mode=main&action=settings',
			'updateadmin.php',
			'administrator.php',
			'banlist.php',
			'banlist.php?mode=main&action=create',
			'banlist.php?mode=white&action=list',
			'banlist.php?mode=white&action=create',
			'banlist.php?mode=failed&action=list',
			'banlist.php?mode=main&action=transfer',
			'banlist.php?mode=main&action=times',
			'banlist.php?mode=main&action=options',
			'users_extended.php',
			'users_extended.php?mode=main&action=add',
			'users_extended.php?mode=main&action=create',
			'users_extended.php?mode=cat&action=list',
			'users_extended.php?mode=cat&action=create',
			'mailout.php',
			'mailout.php?mode=main&action=create',
			'mailout.php?mode=recipients&action=list',
			'mailout.php?mode=pending&action=list',
			'mailout.php?mode=held&action=list',
			'mailout.php?mode=sent&action=list',
			'mailout.php?mode=prefs&action=prefs',
			'mailout.php?mode=maint&action=maint',
			'mailout.php?mode=main&action=templates',
			'userclass2.php',
			'userclass2.php?mode=main&action=create',
			'userclass2.php?mode=main&action=initial',
			'userclass2.php?mode=main&action=options',
			'users.php',
			'users.php?mode=main&action=add',
			'users.php?mode=main&action=prefs',
			'users.php?mode=ranks&action=list',
			'users.php?mode=main&action=maintenance'
		);

		foreach($urls as $url)
		{
			$I->amOnPage('/e107_admin/'.$url);

			$I->dontSee("syntax error");
			$I->dontSee("Fatal error");
		}

	}


}
