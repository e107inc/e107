<?php

/**
 * forum.php?new, the "threads with new activity" listing.
 *
 * It is a public route, and three separate things were wrong with the query
 * behind it:
 *
 *  - USERLV, which class2.php only defines for a signed-in visitor, was
 *    dereferenced bare. On PHP 8 an undefined constant is a fatal, so every
 *    guest and every crawler asking for this page got nothing at all.
 *  - No forum_class predicate, unlike every other listing the plugin ships, so
 *    threads out of forums the caller cannot open were handed to the page
 *    alongside the ones they can.
 *  - The already-read filter compared thread ids against thread_forum_id, so
 *    reading a single thread hid every thread in whichever forum happened to
 *    carry that id.
 *
 * The fatal is asserted against the page, because that is where it lands. The
 * other two are asserted against the query, because the page does not print
 * thread names at all: forum.php:170 hands the thread rows to the template
 * built for forum rows, so a v2 theme renders the section empty. That is a
 * fourth defect, reported separately; asserting "the name is absent" on a page
 * that prints no names would pass whatever the query returned.
 *
 * The read-state filter is asserted from both sides on purpose. The two ids
 * come from separate auto-increment sequences and may collide by chance, and
 * either way round the unfixed query fails one of the two: with no collision
 * nothing is hidden, and with one the whole forum is.
 */
class ForumNewListingCest
{
	const NEW_LISTING = '/e107_plugins/forum/forum.php?new';

	/** @var array */
	private $ids;

	/** @var int */
	private $threadC;

	/** @var int */
	private $threadD1;

	/** @var int */
	private $threadD2;

	/** @var int */
	private $alice;

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();

		// Readable by class 201 only, which the member below is not in.
		$forumC = $I->haveForum('Fixture Forum C', 'fixture-forum-c',
			$this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 3,
			\Helper\ForumFixture::CLASS_MOD_B);
		$this->threadC = $I->haveForumThread('Fixture Thread C', $forumC, 1);
		$I->haveForumPost('Opening post in C', $this->threadC, $forumC, 1);

		// Two threads in one forum, for the read-state filter.
		$forumD = $I->haveForum('Fixture Forum D', 'fixture-forum-d', $this->ids['category'],
			\Helper\ForumFixture::CLASS_MOD_A, 4);
		$this->threadD1 = $I->haveForumThread('Fixture Thread D1', $forumD, 1);
		$this->threadD2 = $I->haveForumThread('Fixture Thread D2', $forumD, 1);
		$I->haveForumPost('Opening post in D1', $this->threadD1, $forumD, 1);
		$I->haveForumPost('Opening post in D2', $this->threadD2, $forumD, 1);

		$this->alice = $I->haveForumMember('newalice');

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	/**
	 * A visitor with no account. Nothing here needs a session, so the page has
	 * to answer rather than die reading a constant that only exists for members.
	 */
	public function theNewListingDoesNotFatalForAVisitorWithNoAccount(AcceptanceTester $I)
	{
		$I->amOnPage(self::NEW_LISTING);

		$I->seeResponseCodeIs(200);
		$I->dontSee('USERLV');
		$I->dontSee('Fatal error');
	}

	/**
	 * The control for the restrictions below: the listing has to offer the
	 * threads the caller may read, or an empty result would satisfy everything.
	 */
	public function theNewListingOffersAMemberAThreadTheyCanRead(AcceptanceTester $I)
	{
		$I->loginToForum('newalice');

		$I->assertContains($this->ids['threadA'], $I->grabForumNewThreadIds(),
			'a member should be offered a thread in a forum they can open');
	}

	/**
	 * Every other listing this plugin ships filters on forum_class. This one
	 * carried the thread and its last poster out of the forum regardless.
	 */
	public function theNewListingWithholdsAThreadFromAForumTheMemberCannotOpen(AcceptanceTester $I)
	{
		$I->loginToForum('newalice');

		$I->assertNotContains($this->threadC, $I->grabForumNewThreadIds(),
			'a member should not be offered a thread from a forum they are refused');
	}

	/**
	 * The same restriction for a visitor with no account at all, which is also
	 * the control proving the guest path returns rows rather than nothing.
	 */
	public function theNewListingWithholdsARestrictedThreadFromAVisitorToo(AcceptanceTester $I)
	{
		$listed = $I->grabForumNewThreadIds();

		$I->assertContains($this->ids['threadA'], $listed, 'a visitor should be offered the public thread');
		$I->assertNotContains($this->threadC, $listed, 'a visitor should not be offered the restricted thread');
	}

	/**
	 * Read state is per thread. Marking one read must not empty out its
	 * neighbours, which is what comparing it against the forum column did.
	 */
	public function readingOneThreadDoesNotHideTheRestOfItsForum(AcceptanceTester $I)
	{
		$I->loginToForum('newalice');
		// After signing in: the plugin's login handler clears this column.
		$I->haveForumThreadsRead($this->alice, array($this->threadD1));

		$listed = $I->grabForumNewThreadIds();

		$I->assertNotContains($this->threadD1, $listed, 'the thread that was read should drop out');
		$I->assertContains($this->threadD2, $listed, 'its neighbour should not drop out with it');
	}
}
