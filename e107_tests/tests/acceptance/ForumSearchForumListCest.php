<?php

/**
 * The forum search's Forum list offers only the forums its caller may view.
 */
class ForumSearchForumListCest
{
	/** e_UC_ADMIN */
	const CLASS_ADMINS = 254;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');

		$category = $I->haveForum('Fixture Public Category', 'fixture-public-category', 0, 0, 1, 0);
		$I->haveForum('Fixture Open Forum', 'fixture-open-forum', $category, \Helper\ForumFixture::CLASS_MOD_A, 2, 0);
		$I->haveForum('Fixture Admins Forum', 'fixture-admins-forum', $category, \Helper\ForumFixture::CLASS_MOD_A, 3, self::CLASS_ADMINS);
		$adminsCategory = $I->haveForum('Fixture Admins Category', 'fixture-admins-category', 0, 0, 4, self::CLASS_ADMINS);
		$I->haveForum('Fixture Forum In The Admins Category', 'fixture-forum-in-admins-category', $adminsCategory,
			\Helper\ForumFixture::CLASS_MOD_A, 5, 0);
		$I->purgeForumPermCache();

		$I->loginAsAdmin();
		$I->amOnPage('/e107_admin/search.php');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->purgeForumPermCache();
		$I->dropForumProbe();
	}

	public function aGuestIsOfferedOnlyTheForumsTheyMayView(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->amOnPage('/search.php?t=forum');

		$I->dontSeeInSource('Fixture Admins Forum');
		$I->dontSeeInSource('Fixture Admins Category');
		$I->dontSeeInSource('Fixture Forum In The Admins Category');
		$I->seeInSource('Fixture Open Forum');
	}

	public function anAdministratorIsOfferedTheForumsTheyMayView(AcceptanceTester $I)
	{
		$I->amOnPage('/search.php?t=forum');

		$I->seeInSource('Fixture Open Forum');
		$I->seeInSource('Fixture Admins Forum');
		$I->seeInSource('Fixture Forum In The Admins Category');
	}
}
