<?php

/**
 * A reply, and a subscription, belong to the thread they name.
 *
 * ajaxQuickReply() took two unrelated ids out of the request: it asked
 * checkPerm() about $_POST['post'], a forum, and then wrote $_POST['thread']
 * into post_thread. Nothing tied them together, so naming any forum you may
 * post in bought a reply in any thread on the site. It read thread_active
 * nowhere either, while the ordinary posting form refuses a closed thread.
 *
 * ajaxTrack() asked nothing at all beyond "are you signed in". trackEmail()
 * then posts the full body of every later reply out to whoever sits in
 * forum_track, with no per-recipient check, so a subscription to a thread you
 * cannot open is a standing feed of its contents.
 *
 * The cross-forum reply was demonstrated against a running install first: a
 * member redirected away from the restricted forum posted into a thread inside
 * it and the row landed with the forum id she had supplied.
 *
 * Forum C is the restricted one. Forum B is readable, and is what the
 * mismatched-ids test names, so that test is about which id wins rather than
 * about permission; the two failures are separable and both are real.
 */
class ForumPostingCest
{
	/** @var array */
	private $ids;

	/** @var int */
	private $forumC;

	/** @var int */
	private $threadC;

	/** @var int */
	private $lockedThread;

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();

		// Readable by class 201 only, which the member below is not in.
		$this->forumC = $I->haveForum('Fixture Forum C', 'fixture-forum-c',
			$this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 3,
			\Helper\ForumFixture::CLASS_MOD_B);
		$this->threadC = $I->haveForumThread('Fixture Thread C', $this->forumC, 1);
		$I->haveForumPost('Opening post in C', $this->threadC, $this->forumC, 1);

		$this->lockedThread = $I->haveForumThread('Fixture Locked Thread', $this->ids['forumA'], 1, 0);
		$I->haveForumPost('Opening post in the locked thread', $this->lockedThread, $this->ids['forumA'], 1);

		$I->haveForumMember('postalice');
		$I->haveForumMember('postmoda', '253,'.\Helper\ForumFixture::CLASS_MOD_A);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
		$I->loginToForum('postalice');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	/**
	 * The fixture has to keep forum C out of reach, or every refusal below is
	 * satisfied by a restriction that was never there.
	 */
	public function theRestrictedForumIsOutOfReachToBeginWith(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->threadC);
		$I->dontSee('Opening post in C');
	}

	/**
	 * The control. Quick reply must still work where the member may post, or a
	 * fix that refused everything would turn this file green.
	 */
	public function aMemberCanStillQuickReplyWhereTheyMayPost(AcceptanceTester $I)
	{
		$this->quickReply($I, $this->ids['threadA'], array(
			'post'   => $this->ids['forumA'],
			'thread' => $this->ids['threadA'],
			'text'   => 'a perfectly ordinary reply',
		));

		$I->seeInDatabase('e107_forum_post', array(
			'post_thread' => $this->ids['threadA'],
			'post_entry like' => '%a perfectly ordinary reply%',
		));
	}

	/**
	 * The exploit, in the shape it was proven in: authorised against a forum the
	 * member may post in, aimed at a thread inside one she is redirected away
	 * from.
	 */
	public function aQuickReplyCannotBeAimedAtAThreadInAForumYouCannotSee(AcceptanceTester $I)
	{
		$this->quickReply($I, $this->ids['threadA'], array(
			'post'   => $this->ids['forumA'],
			'thread' => $this->threadC,
			'text'   => 'a reply that has no business being here',
		));

		$I->dontSeeInDatabase('e107_forum_post', array('post_thread' => $this->threadC,
			'post_entry like' => '%no business being here%'));
	}

	/**
	 * Which of the two ids decides where the row lands. Both forums here are
	 * readable, so nothing is refused; the reply simply has to be filed under
	 * the forum its thread is in rather than the one the request named.
	 */
	public function aReplyIsFiledUnderTheForumItsThreadIsIn(AcceptanceTester $I)
	{
		$this->quickReply($I, $this->ids['threadA'], array(
			'post'   => $this->ids['forumB'],
			'thread' => $this->ids['threadA'],
			'text'   => 'a reply naming the wrong forum',
		));

		$I->seeInDatabase('e107_forum_post', array(
			'post_entry like' => '%naming the wrong forum%',
			'post_forum' => $this->ids['forumA'],
		));
		$I->dontSeeInDatabase('e107_forum_post', array(
			'post_entry like' => '%naming the wrong forum%',
			'post_forum' => $this->ids['forumB'],
		));
	}

	/**
	 * forum_post.php has refused a closed thread since forever (checkPerms()).
	 * The AJAX path never asked.
	 */
	public function aQuickReplyCannotBeAimedAtALockedThread(AcceptanceTester $I)
	{
		$this->quickReply($I, $this->ids['threadA'], array(
			'post'   => $this->ids['forumA'],
			'thread' => $this->lockedThread,
			'text'   => 'a reply to a closed thread',
		));

		$I->dontSeeInDatabase('e107_forum_post', array('post_thread' => $this->lockedThread,
			'post_entry like' => '%a reply to a closed thread%'));
	}

	/**
	 * The locked-thread check must leave moderators alone, which is the licence
	 * the ordinary form grants them.
	 */
	public function aModeratorCanStillReplyToAThreadTheyClosed(AcceptanceTester $I)
	{
		$I->logoutFromForum();
		$I->loginToForum('postmoda');

		$this->quickReply($I, $this->ids['threadA'], array(
			'post'   => $this->ids['forumA'],
			'thread' => $this->lockedThread,
			'text'   => 'a moderator having the last word',
		));

		$I->seeInDatabase('e107_forum_post', array(
			'post_thread' => $this->lockedThread,
			'post_entry like' => '%having the last word%',
		));
	}

	/**
	 * The control for the two refusals below.
	 */
	public function aMemberCanStillTrackAThreadTheyCanRead(AcceptanceTester $I)
	{
		$this->track($I, $this->ids['threadA'], $this->ids['threadA']);

		$I->seeInDatabase('e107_forum_track', array('track_thread' => $this->ids['threadA']));
	}

	/**
	 * Subscribing is a way of reading, so it answers to the same permission the
	 * forum's own pages do.
	 */
	public function aMemberCannotSubscribeToAThreadInAForumTheyCannotRead(AcceptanceTester $I)
	{
		$this->track($I, $this->ids['threadA'], $this->threadC);

		$I->dontSeeInDatabase('e107_forum_track', array('track_thread' => $this->threadC));
	}

	/**
	 * forum.php dispatches the same handler with no thread context of its own,
	 * so it was the cleaner way in and has to be closed by the same check.
	 */
	public function theForumIndexRouteToTrackingIsGuardedTheSameWay(AcceptanceTester $I)
	{
		$token = $I->grabForumToken('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->sendPostRequest('/e107_plugins/forum/forum.php', array(
			'action'  => 'track',
			'thread'  => $this->threadC,
			'e-token' => $token,
		));

		$I->dontSeeInDatabase('e107_forum_track', array('track_thread' => $this->threadC));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $onThread the topic page the request is made from
	 * @param array $fields
	 */
	private function quickReply(AcceptanceTester $I, $onThread, array $fields)
	{
		$page = '/e107_plugins/forum/forum_viewtopic.php?id='.$onThread;

		$fields['action'] = 'quickreply';
		$fields['insert'] = 0;
		$fields['e-token'] = $I->grabForumToken($page);

		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->sendPostRequest($page, $fields);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $onThread the topic page the request is made from
	 * @param int $threadId the thread named in the body
	 */
	private function track(AcceptanceTester $I, $onThread, $threadId)
	{
		$page = '/e107_plugins/forum/forum_viewtopic.php?id='.$onThread;

		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->sendPostRequest($page, array(
			'action'  => 'track',
			'thread'  => $threadId,
			'e-token' => $I->grabForumToken($page),
		));
	}
}
