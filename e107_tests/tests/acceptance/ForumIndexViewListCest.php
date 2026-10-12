<?php

/**
 * The forum index lists the forums its caller may view, and none to a caller who may view none.
 */
class ForumIndexViewListCest
{
	const INDEX = '/e107_plugins/forum/forum.php';

	/** e_UC_MEMBER */
	const CLASS_MEMBERS = 253;

	/** A class nobody below is in. */
	const CLASS_STAFF = 202;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->purgeForumPermCache();
		$I->dropForumProbe();
	}

	public function aGuestWhoMayViewNoForumIsListedNone(AcceptanceTester $I)
	{
		$this->haveForumIn($I, 'Members', self::CLASS_MEMBERS);
		$I->purgeForumPermCache();

		$I->resetAllCookies();
		$I->amOnPage(self::INDEX);

		$I->dontSee('Fixture Members Category');
		$I->dontSee('Fixture Members Forum');
		$I->see('No forums yet');
	}

	public function aMemberWhoMayViewNoForumIsListedNone(AcceptanceTester $I)
	{
		$this->haveForumIn($I, 'Staff', self::CLASS_STAFF);
		$I->haveForumMember('indexreader', (string) self::CLASS_MEMBERS);
		$I->purgeForumPermCache();

		$I->loginToForum('indexreader');
		$I->amOnPage(self::INDEX);

		$I->dontSee('Fixture Staff Category');
		$I->dontSee('Fixture Staff Forum');
		$I->see('No forums yet');

		$this->haveForumIn($I, 'Members', self::CLASS_MEMBERS);
		$I->purgeForumPermCache();
		$I->amOnPage(self::INDEX);

		$I->see('Fixture Members Forum');
		$I->dontSee('Fixture Staff Forum');
	}

	public function aGuestWhoMayViewOneForumIsListedThatOneAlone(AcceptanceTester $I)
	{
		$this->haveForumIn($I, 'Members', self::CLASS_MEMBERS);
		$this->haveForumIn($I, 'Public', 0);
		$I->purgeForumPermCache();

		$I->resetAllCookies();
		$I->amOnPage(self::INDEX);

		$I->see('Fixture Public Forum');
		$I->dontSee('Fixture Members Category');
		$I->dontSee('Fixture Members Forum');
		$I->dontSee('No forums yet');
	}

	/**
	 * A category open to $categoryClass holding a forum, a thread and a post open to everyone.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label
	 * @param int $categoryClass
	 * @return void
	 */
	private function haveForumIn(AcceptanceTester $I, $label, $categoryClass)
	{
		$sef = 'fixture-'.strtolower($label);

		$category = $I->haveForum('Fixture '.$label.' Category', $sef.'-category', 0, 0, 1, $categoryClass);
		$forum = $I->haveForum('Fixture '.$label.' Forum', $sef.'-forum', $category, \Helper\ForumFixture::CLASS_MOD_A, 2);
		$thread = $I->haveForumThread('Fixture '.$label.' Thread', $forum, 1);
		$I->haveForumPost('Opening post in '.$label, $thread, $forum, 1);
	}
}
