<?php

/**
 * A forum's page lists the sub-forums under it that its caller may view, and no others.
 */
class ForumViewforumSubforumsCest
{
	const VIEWFORUM = '/e107_plugins/forum/forum_viewforum.php';

	/** e_UC_MEMBER */
	const CLASS_MEMBERS = 253;

	/** e_UC_ADMIN */
	const CLASS_ADMINS = 254;

	/** A class nobody below is in. */
	const CLASS_STAFF = 202;

	/** @var int */
	private $category;

	/** @var int */
	private $general;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');

		$this->category = $I->haveForum('Fixture Public Category', 'fixture-public-category', 0, 0, 1, 0);
		$this->general = $I->haveForum('Fixture General Forum', 'fixture-general-forum', $this->category, \Helper\ForumFixture::CLASS_MOD_A, 2, 0);
		$thread = $I->haveForumThread('Fixture General Thread', $this->general, 1);
		$I->haveForumPost('Opening post in General', $thread, $this->general, 1);

		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->purgeForumPermCache();
		$I->dropForumProbe();
	}

	public function aGuestIsListedOnlyTheSubForumsTheyMayView(AcceptanceTester $I)
	{
		$this->haveSubForum($I, 'Admins', self::CLASS_ADMINS);
		$this->haveSubForum($I, 'Open', 0);
		$I->purgeForumPermCache();

		$I->resetAllCookies();
		$I->amOnPage(self::VIEWFORUM.'?id='.$this->general);

		$I->see('Fixture General Thread');
		$I->dontSee('Fixture Admins Sub');
		$I->see('Fixture Open Sub');
	}

	public function aMemberIsListedOnlyTheSubForumsTheyMayView(AcceptanceTester $I)
	{
		$this->haveSubForum($I, 'Staff', self::CLASS_STAFF);
		$this->haveSubForum($I, 'Members', self::CLASS_MEMBERS);
		$I->haveForumMember('subreader', (string) self::CLASS_MEMBERS);
		$I->purgeForumPermCache();

		$I->loginToForum('subreader');
		$I->amOnPage(self::VIEWFORUM.'?id='.$this->general);

		$I->see('Fixture General Thread');
		$I->dontSee('Fixture Staff Sub');
		$I->see('Fixture Members Sub');
	}

	public function aGuestIsListedNoSubForumsWhenTheyMayViewNone(AcceptanceTester $I)
	{
		$this->haveSubForum($I, 'Admins', self::CLASS_ADMINS);
		$I->purgeForumPermCache();

		$I->resetAllCookies();
		$I->amOnPage(self::VIEWFORUM.'?id='.$this->general);

		$I->see('Fixture General Thread');
		$I->dontSee('Fixture Admins Sub');
		$I->dontSee('Sub-forum');
	}

	/**
	 * A sub-forum of the General forum, open to $viewClass.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label
	 * @param int $viewClass
	 * @return int forum id
	 */
	private function haveSubForum(AcceptanceTester $I, $label, $viewClass)
	{
		return $I->haveForum('Fixture '.$label.' Sub', 'fixture-'.strtolower($label).'-sub', $this->category,
			\Helper\ForumFixture::CLASS_MOD_A, 3, $viewClass, null, null, $this->general);
	}
}
