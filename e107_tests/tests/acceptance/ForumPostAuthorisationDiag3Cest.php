<?php

/**
 * The posting page authorises each action against the forum and the post it acts on.
 */
class ForumPostAuthorisationDiag3Cest
{
	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	/** @var int a second forum the moderator of forum A also moderates */
	private $forumA2;

	/** @var int a reply in thread A, for a split to take away */
	private $replyA;

	/** @var int a locked thread in forum A */
	private $lockedA;

	/** @var int a locked thread in forum B */
	private $lockedB;

	/** @var int a pinned thread the administrator started in forum A */
	private $pinned;

	/** @var int the member's reply in it */
	private $memberReply;

	/** @var int a pinned thread the member started in forum A */
	private $memberThread;

	/** @var int the member's opening post in it */
	private $memberOpener;

	/** @var int a forum where members (253) reply and only administrators (254) start threads */
	private $adminStarted;

	/** @var int a thread in it */
	private $adminStartedThread;

	/** @var int a forum where only administrators (254) reply and members (253) start threads */
	private $adminReplied;

	/** Under 256, and clear of the fixture's moderator classes. */
	const CLASS_VIP = 202;

	/** @var int a forum only VIP members may view, left open to every member for posting */
	private $vipForum;

	/** @var int a thread in it */
	private $vipThread;

	/** @var int its opening post */
	private $vipPost;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);
		$I->haveSitePrefUntilAfter('antiflood1', 0);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();
		$this->forumA2 = $I->haveForum('Fixture Forum A2', 'fixture-forum-a2', $this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 3);
		$this->replyA = $I->haveForumPost('A reply in thread A', $this->ids['threadA'], $this->ids['forumA'], 1);

		$this->lockedA = $I->haveForumThread('Fixture Locked Thread A', $this->ids['forumA'], 1, 0);
		$I->haveForumPost('Opening post in locked thread A', $this->lockedA, $this->ids['forumA'], 1);
		$this->lockedB = $I->haveForumThread('Fixture Locked Thread B', $this->ids['forumB'], 1, 0);
		$I->haveForumPost('Opening post in locked thread B', $this->lockedB, $this->ids['forumB'], 1);

		$I->haveForumMember('authmoda', '253,'.\Helper\ForumFixture::CLASS_MOD_A);
		$member = $I->haveForumMember('authmember');

		$this->pinned = $I->haveForumThread('Fixture Pinned Thread', $this->ids['forumA'], 1);
		$I->haveForumPost('Opening post in the pinned thread', $this->pinned, $this->ids['forumA'], 1);
		$this->memberReply = $I->haveForumPost('A member reply in the pinned thread', $this->pinned, $this->ids['forumA'], $member);
		$this->memberThread = $I->haveForumThread('Fixture Member Thread', $this->ids['forumA'], $member);
		$this->memberOpener = $I->haveForumPost('Opening post of the member thread', $this->memberThread, $this->ids['forumA'], $member);
		$I->updateInDatabase('e107_forum_thread', array('thread_sticky' => 1), array('thread_id' => $this->pinned));
		$I->updateInDatabase('e107_forum_thread', array('thread_sticky' => 1), array('thread_id' => $this->memberThread));

		$this->adminStarted = $I->haveForum('Fixture Admin-Started Forum', 'fixture-admin-started-forum', $this->ids['category'], \Helper\ForumFixture::CLASS_MOD_B, 4, 0, 253, 254);
		$this->adminStartedThread = $I->haveForumThread('Fixture Admin-Started Thread', $this->adminStarted, 1);
		$I->haveForumPost('Opening post in the admin-started thread', $this->adminStartedThread, $this->adminStarted, 1);
		$this->adminReplied = $I->haveForum('Fixture Admin-Replied Forum', 'fixture-admin-replied-forum', $this->ids['category'], \Helper\ForumFixture::CLASS_MOD_B, 6, 0, 254, 253);

		$I->haveUserClass(self::CLASS_VIP, 'fixture_vip');
		$I->haveForumMember('authvip', '253,'.self::CLASS_VIP);
		$this->vipForum = $I->haveForum('Fixture VIP Forum', 'fixture-vip-forum', $this->ids['category'], \Helper\ForumFixture::CLASS_MOD_B, 5, self::CLASS_VIP, 253, 253);
		$this->vipThread = $I->haveForumThread('Fixture VIP Thread', $this->vipForum, 1);
		$this->vipPost = $I->haveForumPost('A post only VIP members may read', $this->vipThread, $this->vipForum, 1);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	public function aModeratorRepliesInALockedThreadOfTheirOwnForum(AcceptanceTester $I)
	{
		$I->loginToForum('authmoda');

		$this->submit($I, '?f=rp&id='.$this->lockedA, array('post' => 'A moderator reply in locked thread A', 'reply' => 'Post reply'));

		$I->seeInDatabase('e107_forum_post', array('post_thread' => $this->lockedA, 'post_entry' => 'A moderator reply in locked thread A'));
	}

	/**
	 * A post the moderator names from their own forum makes them no moderator of the thread they reply in.
	 */
	public function aModeratorCannotReplyInALockedThreadOfAnotherForum(AcceptanceTester $I)
	{
		$I->loginToForum('authmoda');
		$page = '?f=rp&id='.$this->lockedB.'&post='.$this->ids['postA'];

		$this->submit($I, $page, array('post' => 'A moderator reply in locked thread B', 'reply' => 'Post reply'));

		$I->dontSeeInDatabase('e107_forum_post', array('post_entry' => 'A moderator reply in locked thread B'));
		$I->amOnPage('/e107_plugins/forum/forum_post.php'.$page);
		$I->seeInSource('forum-post-locked');
	}

	/**
	 * A post the moderator names from their own forum makes them no moderator of the thread the move page loads.
	 */
	public function aModeratorCannotRewriteAnotherForumsOpeningPostFromTheMovePage(AcceptanceTester $I)
	{
		$I->loginToForum('authmoda');

		$this->submit($I, '?f=move&id='.$this->ids['threadB'].'&post='.$this->ids['postA'], array(
			'post' => 'Rewritten from the move page', 'update_reply' => 'Update',
		));

		$I->dontSeeInDatabase('e107_forum_post', array('post_entry' => 'Rewritten from the move page'));
		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->ids['postB'], 'post_entry' => 'Opening post in B'));
	}

	public function aModeratorPinsANewThreadInTheirOwnForum(AcceptanceTester $I)
	{
		$I->loginToForum('authmoda');

		$this->submit($I, '?f=nt&id='.$this->ids['forumA'], array(
			'subject' => 'Pinned in forum A', 'post' => 'A new thread', 'threadtype' => 1, 'newthread' => 'Post thread',
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_name' => 'Pinned in forum A', 'thread_sticky' => 1));
	}

	/**
	 * A post the moderator names from their own forum makes them no moderator of the forum they start a thread in.
	 */
	public function aModeratorCannotPinANewThreadInAnotherForum(AcceptanceTester $I)
	{
		$I->loginToForum('authmoda');

		$this->submit($I, '?f=nt&id='.$this->ids['forumB'].'&post='.$this->ids['postA'], array(
			'subject' => 'Pinned in forum B', 'post' => 'A new thread', 'threadtype' => 2, 'newthread' => 'Post thread',
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_name' => 'Pinned in forum B', 'thread_sticky' => 0));
	}

	public function aThreadStarterRenamesTheirThreadAndItStaysPinned(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$this->submit($I, '?f=edit&id='.$this->memberThread.'&post='.$this->memberOpener, array(
			'subject' => 'Renamed by its starter', 'post' => 'An edited opening post', 'threadtype' => 2, 'update_thread' => 'Update',
		));

		$I->seeInDatabase('e107_forum_thread', array(
			'thread_id' => $this->memberThread, 'thread_name' => 'Renamed by its starter', 'thread_sticky' => 1,
		));
	}

	public function theAuthorOfAReplyCannotRenameOrUnpinTheThread(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$this->submit($I, '?f=edit&id='.$this->pinned.'&post='.$this->memberReply, array(
			'subject' => 'Renamed by a reply', 'post' => 'An edited reply', 'update_thread' => 'Update',
		));

		$I->seeInDatabase('e107_forum_thread', array(
			'thread_id' => $this->pinned, 'thread_name' => 'Fixture Pinned Thread', 'thread_sticky' => 1,
		));
	}

	public function aThreadStarterEditsTheirSameSecondReplyAsAReply(AcceptanceTester $I)
	{
		$reply = $this->haveSameSecondReplyByTheStarter($I);
		$page = '?f=edit&id='.$this->memberThread.'&post='.$reply;
		$I->loginToForum('authmember');

		$I->amOnPage('/e107_plugins/forum/forum_post.php'.$page);
		$I->seeElement('input[name=update_reply]');
		$I->dontSeeElement('input[name=update_thread]');

		$this->submit($I, $page, array('post' => 'An edited same-second reply', 'update_reply' => 'Update'));

		$I->seeInDatabase('e107_forum_post', array('post_id' => $reply, 'post_entry' => 'An edited same-second reply'));
	}

	public function aModeratorIsOfferedNoPinOnAThreadStartersSameSecondReply(AcceptanceTester $I)
	{
		$reply = $this->haveSameSecondReplyByTheStarter($I);
		$I->loginToForum('authmoda');

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->memberThread.'&post='.$reply);

		$I->seeElement('input[name=update_reply]');
		$I->dontSeeElement('input[name=threadtype]');
	}

	public function aModeratorUnpinsAThreadByEditingItsOpeningPost(AcceptanceTester $I)
	{
		$I->loginToForum('authmoda');

		$this->submit($I, '?f=edit&id='.$this->memberThread.'&post='.$this->memberOpener, array(
			'subject' => 'Fixture Member Thread', 'post' => 'Opening post of the member thread', 'threadtype' => 0, 'update_thread' => 'Update',
		));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->memberThread, 'thread_sticky' => 0));
	}

	public function aMemberRepliesWhereOnlyAdministratorsStartThreads(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$this->submit($I, '?f=rp&id='.$this->adminStartedThread, array('post' => 'A member reply where admins start threads', 'reply' => 'Post reply'));

		$I->seeInDatabase('e107_forum_post', array('post_thread' => $this->adminStartedThread, 'post_entry' => 'A member reply where admins start threads'));
	}

	public function aMemberCannotStartAThreadWhereOnlyAdministratorsMay(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');
		$page = '?f=nt&id='.$this->adminStarted;

		$this->submit($I, $page, array('subject' => 'Started by a member', 'post' => 'A new thread', 'newthread' => 'Post thread'));

		$I->dontSeeInDatabase('e107_forum_thread', array('thread_name' => 'Started by a member'));
		$I->amOnPage('/e107_plugins/forum/forum_post.php'.$page);
		$I->seeInSource('forum-post-unauthorized');
	}

	public function aMemberIsOfferedANewThreadOnATopicPageWhereMembersStartThreads(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$I->seeElement($this->newThreadLink($this->ids['forumA']));
	}

	public function aMemberIsOfferedNoNewThreadOnATopicPageWhereOnlyAdministratorsStartThreads(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->adminStartedThread);

		$I->dontSeeElement($this->newThreadLink($this->adminStarted));
	}

	/**
	 * A new thread takes the posting class too, so a member outside it is offered none.
	 */
	public function aMemberWhoMayNotReplyIsOfferedNoNewThread(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->adminReplied);

		$I->dontSeeElement($this->newThreadLink($this->adminReplied));
	}

	public function aModeratorSplitsPostsIntoAnotherForumTheyModerate(AcceptanceTester $I)
	{
		$I->loginToForum('authmoda');

		$this->split($I, $this->forumA2, 'Split into forum A2');

		$I->seeInDatabase('e107_forum_thread', array('thread_name' => 'Split into forum A2', 'thread_forum_id' => $this->forumA2));
	}

	public function aModeratorCannotSplitPostsIntoAForumTheyDoNotModerate(AcceptanceTester $I)
	{
		$I->loginToForum('authmoda');

		$this->split($I, $this->ids['forumB'], 'Split into forum B');

		$I->dontSeeInDatabase('e107_forum_thread', array('thread_name' => 'Split into forum B'));
		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->replyA, 'post_thread' => $this->ids['threadA']));
	}

	public function aMemberWhoMayViewAForumQuotesAndRepliesThere(AcceptanceTester $I)
	{
		$I->loginToForum('authvip');

		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=quote&id='.$this->vipThread.'&post='.$this->vipPost);
		$I->see('A post only VIP members may read');

		$this->submit($I, '?f=rp&id='.$this->vipThread, array('post' => 'A VIP reply', 'reply' => 'Post reply'));
		$I->seeInDatabase('e107_forum_post', array('post_thread' => $this->vipThread, 'post_entry' => 'A VIP reply'));
	}

	public function aMemberWhoMayNotViewAForumIsShownNoPostOfIt(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		foreach(array('quote', 'report', 'edit') as $form)
		{
			$I->amOnPage('/e107_plugins/forum/forum_post.php?f='.$form.'&id='.$this->vipThread.'&post='.$this->vipPost);
			$I->seeInSource('forum-post-unauthorized');
			$I->dontSeeInSource('A post only VIP members may read');
		}
	}

	public function aMemberWhoMayNotViewAForumCannotPostThere(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$this->submit($I, '?f=rp&id='.$this->vipThread, array('post' => 'A reply in a forum the member cannot view', 'reply' => 'Post reply'));
		$this->submit($I, '?f=nt&id='.$this->vipForum, array('subject' => 'Started where the member cannot view', 'post' => 'A new thread', 'newthread' => 'Post thread'));

		$I->dontSeeInDatabase('e107_forum_post', array('post_entry' => 'A reply in a forum the member cannot view'));
		$I->dontSeeInDatabase('e107_forum_thread', array('thread_name' => 'Started where the member cannot view'));
	}

	/**
	 * The topic page checks the thread in its address, and the quick reply writes to the thread in its body.
	 */
	public function aMemberWhoMayNotViewAForumCannotQuickReplyThere(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$I->sendForumQuickReply($this->ids['threadA'], array(
			'thread' => $this->vipThread, 'text' => 'A quick reply in a forum the member cannot view',
		));

		$I->dontSeeInDatabase('e107_forum_post', array('post_entry' => 'A quick reply in a forum the member cannot view'));
	}

	public function aMemberCannotStartAThreadInACategory(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$this->submit($I, '?f=nt&id='.$this->ids['category'], array('subject' => 'Started in a category', 'post' => 'A new thread', 'newthread' => 'Post thread'));

		$I->dontSeeInDatabase('e107_forum_thread', array('thread_name' => 'Started in a category'));
	}

	public function aMemberIsOfferedANewThreadOnAForumPage(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA']);

		$I->seeElement($this->newThreadLink($this->ids['forumA']));
		$I->see('You can start new topics');
	}

	public function aMemberIsOfferedNoNewThreadOnACategorysForumPage(AcceptanceTester $I)
	{
		$I->loginToForum('authmember');

		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['category']);

		$I->dontSeeElement($this->newThreadLink($this->ids['category']));
		$I->see('You cannot start new topics');
	}

	public function aMemberIsOfferedNoNewThreadOnATopicPageOfAThreadStoredInACategory(AcceptanceTester $I)
	{
		$thread = $I->haveForumThread('Fixture Thread In A Category', $this->ids['category'], 1);
		$I->haveForumPost('Opening post of a thread stored in a category', $thread, $this->ids['category'], 1);
		$I->loginToForum('authmember');

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$thread);

		$I->see('Opening post of a thread stored in a category');
		$I->dontSeeElement($this->newThreadLink($this->ids['category']));
	}

	public function aMemberCannotStartAThreadInAContainer(AcceptanceTester $I)
	{
		$this->makeForumAContainer($I);
		$I->loginToForum('authmember');

		$this->submit($I, '?f=nt&id='.$this->ids['forumA'], array('subject' => 'Started in a container', 'post' => 'A new thread', 'newthread' => 'Post thread'));

		$I->dontSeeInDatabase('e107_forum_thread', array('thread_name' => 'Started in a container'));
	}

	public function aMemberIsOfferedNoNewThreadOnATopicPageInAContainer(AcceptanceTester $I)
	{
		$this->makeForumAContainer($I);
		$I->loginToForum('authmember');

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$I->see('A reply in thread A');
		$I->dontSeeElement($this->newThreadLink($this->ids['forumA']));
	}

	public function aModeratorMovesAThreadIntoAnotherForumTheyModerate(AcceptanceTester $I)
	{
		$I->loginToForum('authmoda');

		$this->submit($I, '?f=move&id='.$this->ids['threadA'], array('move_thread' => 1, 'forum_move' => $this->forumA2, 'rename_thread' => 'none'));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadA'], 'thread_forum_id' => $this->forumA2));
	}

	public function aModeratorOfACategoryCannotSplitPostsIntoIt(AcceptanceTester $I)
	{
		$I->updateInDatabase('e107_forum', array('forum_moderators' => \Helper\ForumFixture::CLASS_MOD_A), array('forum_id' => $this->ids['category']));
		$I->loginToForum('authmoda');

		$this->split($I, $this->ids['category'], 'Split into a category');

		$I->dontSeeInDatabase('e107_forum_thread', array('thread_name' => 'Split into a category'));
	}

	public function aModeratorOfACategoryCannotMoveAThreadIntoIt(AcceptanceTester $I)
	{
		$I->updateInDatabase('e107_forum', array('forum_moderators' => \Helper\ForumFixture::CLASS_MOD_A), array('forum_id' => $this->ids['category']));
		$I->loginToForum('authmoda');

		$this->submit($I, '?f=move&id='.$this->ids['threadA'], array('move_thread' => 1, 'forum_move' => $this->ids['category'], 'rename_thread' => 'none'));

		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadA'], 'thread_forum_id' => $this->ids['forumA']));
	}

	/**
	 * Split thread A from its reply onwards into $toForum, as the signed-in member.
	 *
	 * @param AcceptanceTester $I
	 * @param int $toForum
	 * @param string $title
	 */
	private function split(AcceptanceTester $I, $toForum, $title)
	{
		$this->submit($I, '?f=split&id='.$this->ids['threadA'].'&post='.$this->replyA, array(
			'split_thread'     => 1,
			'forum_split'      => $toForum,
			'new_thread_title' => $title,
		));
	}

	/**
	 * A link to the posting page's new-thread form for $forumId.
	 *
	 * @param int $forumId
	 * @return string CSS selector
	 */
	private function newThreadLink($forumId)
	{
		return 'a[href$="?f=nt&id='.(int) $forumId.'"]';
	}

	/**
	 * Name forum A with the leading '*' that makes a forum a container, whose page lists its sub-forums and no threads.
	 *
	 * @param AcceptanceTester $I
	 */
	private function makeForumAContainer(AcceptanceTester $I)
	{
		$I->updateInDatabase('e107_forum', array('forum_name' => '*Fixture Forum A'), array('forum_id' => $this->ids['forumA']));
	}

	/**
	 * Post $fields to the posting page at $query, as the signed-in member.
	 *
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @param array $fields
	 */
	private function submit(AcceptanceTester $I, $query, array $fields)
	{
		$fields['e-token'] = $I->grabForumToken('/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA']);

		$I->sendPostRequest('/e107_plugins/forum/forum_post.php'.$query, $fields);
	}

	/**
	 * A reply by the member thread's starter, stamped with the second the thread was started in.
	 *
	 * @param AcceptanceTester $I
	 * @return int post id
	 */
	private function haveSameSecondReplyByTheStarter(AcceptanceTester $I)
	{
		$thread = array('thread_id' => $this->memberThread);
		$starter = (int) $I->grabFromDatabase('e107_forum_thread', 'thread_user', $thread);
		$reply = $I->haveForumPost('A same-second reply by the starter', $this->memberThread, $this->ids['forumA'], $starter);
		$I->updateInDatabase('e107_forum_post', array('post_datestamp' => $I->grabFromDatabase('e107_forum_thread', 'thread_datestamp', $thread)), array('post_id' => $reply));

		return $reply;
	}
}
