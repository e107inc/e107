<?php

/**
 * forum_viewtopic.php?f=post sends a caller to a post only in a forum they may view, and otherwise refuses them as the topic page does.
 */
class ForumPostAddressViewCest
{
	const TOPIC = '/e107_plugins/forum/forum_viewtopic.php';

	/** e_UC_MEMBER */
	const CLASS_MEMBERS = 253;

	/** e_UC_ADMIN */
	const CLASS_ADMINS = 254;

	/** A class nobody below is in. */
	const CLASS_STAFF = 202;

	/** The id of a thread a guest may view, and of a post in a thread they may not. */
	const SHARED_ID = 970001;

	/** No thread has this id. */
	const MISSING_ID = 970002;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(self::CLASS_STAFF, 'fixture_staff');
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->purgeForumPermCache();
		$I->dropForumProbe();
	}

	public function aGuestIsRefusedAPostInAForumTheyMayNotView(AcceptanceTester $I)
	{
		$hidden = $this->havePostIn($I, 'Admins', 0, self::CLASS_ADMINS);
		$I->purgeForumPermCache();

		$I->resetAllCookies();
		$this->seeRefusedLikeTheTopicPage($I, $hidden, 'fixture-admins-thread-title', 'post');
	}

	public function aMemberIsRefusedAPostUnderACategoryTheyMayNotView(AcceptanceTester $I)
	{
		$hidden = $this->havePostIn($I, 'Staff', self::CLASS_STAFF, 0);
		$I->haveForumMember('postreader', (string) self::CLASS_MEMBERS);
		$I->purgeForumPermCache();

		$I->loginToForum('postreader');
		$this->seeRefusedLikeTheTopicPage($I, $hidden, 'fixture-staff-thread-title', 'post');
	}

	public function aGuestIsRefusedAPostWhoseIdNamesAThreadTheyMayView(AcceptanceTester $I)
	{
		$hidden = $this->havePostIn($I, 'Admins', 0, self::CLASS_ADMINS);
		$this->havePostIn($I, 'Public', 0, 0, self::SHARED_ID);
		$I->haveForumPost('Reply in Admins', $hidden['thread'], $hidden['forum'], 1, null, self::SHARED_ID);
		$I->purgeForumPermCache();

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage(self::TOPIC.'?id='.self::SHARED_ID);
		$I->seeResponseCodeIs(200);
		$I->see('Fixture Public Thread Title');

		$this->seeRefusedLikeTheTopicPage($I, array('thread' => $hidden['thread'], 'post' => self::SHARED_ID),
			'fixture-admins-thread-title', 'post ');
	}

	public function aGuestWithDebugOutputOnIsRefusedAHiddenThreadAsAMissingOne(AcceptanceTester $I)
	{
		$hidden = $this->havePostIn($I, 'Admins', 0, self::CLASS_ADMINS);
		$I->purgeForumPermCache();

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage(self::TOPIC.'?id='.$hidden['thread']);
		$refusedTo = $I->grabHttpHeader('Location');
		$I->assertNotSame('', $refusedTo, 'The topic page must refuse with a redirect');

		$I->setCookie('e107_debug_level', 'level=1');
		$I->amOnPage(self::TOPIC.'?id='.$hidden['thread']);
		$I->assertSame($refusedTo, $I->grabHttpHeader('Location'));
		$I->amOnPage(self::TOPIC.'?id='.self::MISSING_ID);
		$I->assertSame($refusedTo, $I->grabHttpHeader('Location'));
	}

	public function aGuestIsSentToAPostTheyMayView(AcceptanceTester $I)
	{
		$open = $this->havePostIn($I, 'Public', 0, 0);
		$I->purgeForumPermCache();

		$I->resetAllCookies();
		$I->stopFollowingRedirects();
		$I->amOnPage(self::TOPIC.'?f=post&id='.$open['post']);

		$I->seeRedirectTo('#post-'.$open['post']);
	}

	public function aMemberIsSentToAPostInAForumOpenToMembers(AcceptanceTester $I)
	{
		$members = $this->havePostIn($I, 'Members', 0, self::CLASS_MEMBERS);
		$I->haveForumMember('postreader', (string) self::CLASS_MEMBERS);
		$I->purgeForumPermCache();

		$I->loginToForum('postreader');
		$I->stopFollowingRedirects();
		$I->amOnPage(self::TOPIC.'?f=post&id='.$members['post']);

		$I->seeRedirectTo('#post-'.$members['post']);
	}

	/**
	 * Asks for the thread's topic page and then for its post's address, and expects the same refusal from both.
	 *
	 * @param AcceptanceTester $I
	 * @param array $ids thread and post
	 * @param string $titleSlug the thread's title as the SEF topic address spells it
	 * @param string $action the f parameter
	 * @return void
	 */
	private function seeRefusedLikeTheTopicPage(AcceptanceTester $I, array $ids, $titleSlug, $action)
	{
		$I->stopFollowingRedirects();
		$I->amOnPage(self::TOPIC.'?id='.$ids['thread']);
		$refusedCode = $I->grabResponseCode();
		$refusedTo = $I->grabHttpHeader('Location');
		$I->assertNotSame('', $refusedTo, 'The topic page must refuse with a redirect');

		$I->amOnPage(self::TOPIC.'?f='.rawurlencode($action).'&id='.$ids['post']);

		$I->seeNoRedirectTo($titleSlug);
		$I->seeNoRedirectTo('post-'.$ids['post']);
		$I->assertSame($refusedTo, $I->grabHttpHeader('Location'));
		$I->assertSame($refusedCode, $I->grabResponseCode());
	}

	/**
	 * A category open to $categoryClass holding a forum open to $forumClass, with a thread and its opening post.
	 *
	 * @param AcceptanceTester $I
	 * @param string $label
	 * @param int $categoryClass
	 * @param int $forumClass
	 * @param int|null $threadId the thread's id, or null for the next one
	 * @return array forum, thread and post ids
	 */
	private function havePostIn(AcceptanceTester $I, $label, $categoryClass, $forumClass, $threadId = null)
	{
		$sef = 'fixture-'.strtolower($label);

		$category = $I->haveForum('Fixture '.$label.' Category', $sef.'-category', 0, 0, 1, $categoryClass);
		$forum = $I->haveForum('Fixture '.$label.' Forum', $sef.'-forum', $category, \Helper\ForumFixture::CLASS_MOD_A, 2, $forumClass);
		$thread = $I->haveForumThread('Fixture '.$label.' Thread Title', $forum, 1, 1, null, null, $threadId);

		return array(
			'forum'  => $forum,
			'thread' => $thread,
			'post'   => $I->haveForumPost('Opening post in '.$label, $thread, $forum, 1),
		);
	}
}
