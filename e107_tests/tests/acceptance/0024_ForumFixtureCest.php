<?php

/**
 * The forum plugin has never had a test, so before anything asserts that a user
 * cannot do something, this asserts that the fixture can tell the difference.
 *
 * Every "X cannot do Y" test in the forum suite is worthless if the fixture
 * grants nobody anything, and a forum seeded even slightly wrong does exactly
 * that: _getForumPermList() only grants on a forum whose parent is not 0 and
 * whose parent passes the same class test, so a top-level forum silently denies
 * everyone and every negative assertion passes without touching the code it
 * names. These tests are the guard against that, and they are why the ones that
 * follow mean anything.
 */
class ForumFixtureCest
{
	/** @var array */
	private $ids;

	/** @var int */
	private $alice;

	/** @var int */
	private $moda;

	/** @var int */
	private $restricted;

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();
		$I->haveForumPluginInstalled();

		// Pinned, not inherited. 0023 removes the preference in its teardown and
		// runs immediately before this file, so an unset value here would resolve
		// to a mode that reads no token and wants a header PhpBrowser never sends.
		$I->haveForumCsrfMode(2);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();

		// A forum only members of mod_b may see, for the negative assertions.
		$this->restricted = $I->haveForum(
			'Fixture Restricted', 'fixture-restricted', $this->ids['category'],
			\Helper\ForumFixture::CLASS_MOD_B, 3, \Helper\ForumFixture::CLASS_MOD_B
		);

		$this->alice = $I->haveForumMember('fixturealice');
		$this->moda = $I->haveForumMember('fixturemoda', '253,'.\Helper\ForumFixture::CLASS_MOD_A);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	/**
	 * The plugin has to be installed, not merely have its tables: every
	 * front-end page redirects away on the plug_installed preference.
	 */
	public function theForumIndexRendersOnceInstalled(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum.php');

		$I->seeResponseCodeIs(200);
		$I->see('Fixture Forum A');
		$I->dontSee('Site Configuration Issue');
	}

	/**
	 * A public forum must be reachable, or every later "was this refused?"
	 * assertion is measuring the wrong refusal.
	 */
	public function aGuestCanReadThePublicForum(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA']);

		$I->seeResponseCodeIs(200);
		$I->see('Fixture Thread A');
	}

	/**
	 * The other half, and the one that matters: a restricted forum must actually
	 * restrict. If this passes because everything is restricted, the test above
	 * fails, so the two together pin the fixture from both sides.
	 */
	public function aMemberCannotReachARestrictedForum(AcceptanceTester $I)
	{
		$I->loginToForum('fixturealice');
		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->restricted);

		$I->dontSee('Fixture Restricted');
	}

	/**
	 * A seeded member must be able to sign in. The password goes in as a plain
	 * md5 because UserHandler reads any 32 character hash that way whatever the
	 * site's encoding is, and this proves that rather than assuming it.
	 *
	 * Proven against usersettings.php, which redirects a caller who is not USER.
	 * The guest half is needed too: without it a page that renders for everyone
	 * would satisfy the check and prove nothing about the session.
	 */
	public function aSeededMemberCanSignIn(AcceptanceTester $I)
	{
		$I->amOnPage('/usersettings.php');
		$I->dontSeeInCurrentUrl('usersettings.php');

		$I->loginToForum('fixturealice');

		$I->dontSeeElement('input[name=userpass]');

		$I->amOnPage('/usersettings.php');
		$I->seeInCurrentUrl('usersettings.php');
	}

	/**
	 * The moderator classes have to differ between the two forums, or a test
	 * that a moderator of one cannot act on the other has nothing to detect.
	 */
	public function theTwoFixtureForumsHaveDifferentModerators(AcceptanceTester $I)
	{
		$I->assertNotSame(
			\Helper\ForumFixture::CLASS_MOD_A,
			\Helper\ForumFixture::CLASS_MOD_B,
			'forum A and forum B must not share a moderator class'
		);

		$I->seeInDatabase('e107_forum', array(
			'forum_id' => $this->ids['forumA'],
			'forum_moderators' => \Helper\ForumFixture::CLASS_MOD_A,
		));

		$I->seeInDatabase('e107_forum', array(
			'forum_id' => $this->ids['forumB'],
			'forum_moderators' => \Helper\ForumFixture::CLASS_MOD_B,
		));

		// forum_moderators is a tinyint, so a class id over 255 would be stored
		// truncated and the fixture would quietly moderate the wrong class.
		$I->assertLessThan(256, \Helper\ForumFixture::CLASS_MOD_B);
	}

	/**
	 * Neither fixture member may be an admin. getperms('0') short-circuits every
	 * MODERATOR test in the plugin, so an admin would pass the authorisation
	 * checks the later tests exist to exercise.
	 */
	public function neitherFixtureMemberIsAnAdmin(AcceptanceTester $I)
	{
		foreach (array($this->alice, $this->moda) as $userId)
		{
			$I->seeInDatabase('e107_user', array('user_id' => $userId, 'user_admin' => 0, 'user_perms' => ''));
		}
	}

	/**
	 * The forums are under a category rather than at the top level. A forum with
	 * forum_parent 0 is granted to nobody by _getForumPermList(), which would
	 * make every negative assertion in this suite pass for free.
	 */
	public function bothForumsSitUnderACategory(AcceptanceTester $I)
	{
		foreach (array('forumA', 'forumB') as $key)
		{
			$I->seeInDatabase('e107_forum', array(
				'forum_id' => $this->ids[$key],
				'forum_parent' => $this->ids['category'],
			));

			$I->assertNotEquals(0, $this->ids['category'], 'the category must exist for the forums to inherit from');
		}
	}
}
