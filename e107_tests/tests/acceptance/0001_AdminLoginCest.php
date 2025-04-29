<?php


class AdminLoginCest
{
	public function _before(AcceptanceTester $I)
	{
	}

	public function _after(AcceptanceTester $I)
	{
	}

	/**
	 * @see https://github.com/e107inc/e107/issues/4779
	 */
	public function testAdminFailedLogin(AcceptanceTester $I)
	{
		$I->wantTo("See a login failure message in the admin area if I put in the wrong credentials");
		$I->amOnPage("/e107_admin/admin.php");
		$I->fillField('authname', 'e107');
		$I->fillField('authpass', 'wrong password');
		$I->click('authsubmit');
		$I->see("Your login details don't match any registered user");
	}

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


	public function testForMissingLans(AcceptanceTester $I)
	{
		$this->e107Login($I);

		$I->amOnPage('/e107_admin/search.php');
		$I->dontSee("LAN_PLUGIN_");
		$I->see("Pages");

		$I->amOnPage('/e107_plugins/gallery/admin_gallery.php');
		$I->dontSee("LAN_PLUGIN_");
	}


	public function testAdminURLS(AcceptanceTester $I)
	{

		$this->e107Login($I);

		$urls = array(
			'admin.php?[debug=basic+]',
			'admin_log.php',
			'admin_log.php?mode=audit&action=list',
			'admin_log.php?mode=main&action=maintenance',
			'admin_log.php?mode=main&action=prefs',
			'admin_log.php?mode=rolling&action=list',
			'administrator.php',
			'banlist.php',
			'banlist.php?mode=failed&action=list',
			'banlist.php?mode=main&action=create',
			'banlist.php?mode=main&action=options',
			'banlist.php?mode=main&action=times',
			'banlist.php?mode=main&action=transfer',
			'banlist.php?mode=white&action=create',
			'banlist.php?mode=white&action=list',
			'cache.php',
			'comment.php',
			'comment.php?mode=main&action=prefs',
			'comment.php?mode=main&action=tools',
			'cpage.php',
			'cpage.php?mode=cat&action=create',
			'cpage.php?mode=cat&action=list',
			'cpage.php?mode=menu&action=list&tab=2',
			'cpage.php?mode=page&action=create',
			'cpage.php?mode=page&action=list',
			'cpage.php?mode=page&action=prefs',
			'credits.php',
			'cron.php',
			'db.php',
			'db.php?mode=backup',
			'db.php?mode=convert_to_utf8',
			'db.php?mode=db_update',
			'db.php?mode=exportForm',
			'db.php?mode=importForm',
			'db.php?mode=plugin_scan',
			'db.php?mode=pref_editor',
			'db.php?mode=sc_override_scan',
			'db.php?mode=verify_sql',
			'docs.php',
			'emoticon.php',
			'eurl.php',
			'eurl.php?mode=main&action=alias',
			'eurl.php?mode=main&action=settings',
			'eurl.php?mode=main&action=simple',
			'fileinspector.php',
			'frontpage.php',
			'frontpage.php?mode=create',
			'history.php',
			'image.php',
			'image.php?mode=cat&action=create',
			'image.php?mode=cat&action=list',
			'image.php?mode=main&action=avatar',
			'image.php?mode=main&action=import',
			'image.php?mode=main&action=prefs',
			'language.php',
			'language.php?mode=main&action=db',
			'language.php?mode=main&action=deprecated',
			'language.php?mode=main&action=tools',
			'links.php',
			'links.php?mode=main&action=create',
			'links.php?mode=main&action=prefs',
			'links.php?mode=main&action=tools',
			'mailout.php',
			'mailout.php?mode=held&action=list',
			'mailout.php?mode=main&action=create',
			'mailout.php?mode=main&action=preview&id=notify',
			'mailout.php?mode=main&action=preview&id=quickadduser',
			'mailout.php?mode=main&action=preview&id=signup',
			'mailout.php?mode=main&action=preview&id=whatsnew',
			'mailout.php?mode=main&action=templates',
			'mailout.php?mode=maint&action=maint',
			'mailout.php?mode=pending&action=list',
			'mailout.php?mode=prefs&action=prefs',
			'mailout.php?mode=recipients&action=list',
			'mailout.php?mode=sent&action=list',
			'menus.php',
			'menus.php?configure=sidebar',
			'meta.php',
			'newspost.php',
			'newspost.php?mode=cat&action=create',
			'newspost.php?mode=cat&action=list',
			'newspost.php?mode=main&action=create',
			'newspost.php?mode=main&action=prefs',
			'newspost.php?mode=sub&action=list',
			'notify.php',
			'phpinfo.php',
			'plugin.php',
			'plugin.php?mode=avail&action=list',
			'plugin.php?mode=avail&action=upload',
			'plugin.php?mode=create&action=build',
			'plugin.php?mode=online&action=grid',
			'prefs.php',
			'search.php',
			'search.php?settings',
			'theme.php',
			'theme.php?mode=convert&action=main',
			'theme.php?mode=main&action=admin',
			'theme.php?mode=main&action=choose',
			'theme.php?mode=main&action=online',
			'theme.php?mode=main&action=upload',
			'ugflag.php',
			'updateadmin.php',
			'upload.php',
			'userclass2.php',
			'userclass2.php?mode=main&action=create',
			'userclass2.php?mode=main&action=initial',
			'userclass2.php?mode=main&action=options',
			'users.php',
			'users.php?mode=main&action=add',
			'users.php?mode=main&action=maintenance',
			'users.php?mode=main&action=prefs',
			'users.php?mode=ranks&action=list',
			'users_extended.php',
			'users_extended.php?mode=cat&action=create',
			'users_extended.php?mode=cat&action=list',
			'users_extended.php?mode=main&action=add',
			'users_extended.php?mode=main&action=create',
			'wmessage.php',
			'wmessage.php?mode=main&action=create',
			'wmessage.php?mode=main&action=prefs'
		);

		foreach($urls as $url)
		{
			$I->amOnPage('/e107_admin/'.$url);

			$I->dontSee("syntax error");
			$I->dontSee("Fatal error");
		}

	}


}
