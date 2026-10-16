<?php

/**
 * A forum submission is saved where its form was authorised, and nowhere else.
 */
class ForumPostTargetCest
{
	/** @var array */
	private $ids;

	/** @var int a forum the member may neither read nor post in */
	private $staffForum;

	/** @var int an open thread in the staff forum */
	private $staffThread;

	/** @var int the member's own post in a thread a moderator has since locked */
	private $lockedPost;

	/** @var int the member's own post in thread A */
	private $openPost;

	/** @var int */
	private $member;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);

		$I->haveForumPost('A post in no thread, so that post ids and thread ids differ', 0, 0, 1);
		$this->ids = $I->haveForumStructure();
		$I->haveSitePrefUntilAfter('antiflood1', 0);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');
		$this->staffForum = $I->haveForum('Fixture Staff Forum', 'fixture-staff-forum', $this->ids['category'],
			\Helper\ForumFixture::CLASS_MOD_B, 3, \Helper\ForumFixture::CLASS_MOD_B);
		$this->staffThread = $I->haveForumThread('Fixture Staff Thread', $this->staffForum, 1);
		$I->haveForumPost('Opening post in the staff thread', $this->staffThread, $this->staffForum, 1);

		$this->member = $I->haveForumMember('targetmember');

		$lockedThread = $I->haveForumThread('Fixture Locked Thread', $this->ids['forumA'], $this->member, 0);
		$this->lockedPost = $I->haveForumPost('Written before the lock', $lockedThread, $this->ids['forumA'], $this->member);
		$this->openPost = $I->haveForumPost('Written in an open thread', $this->ids['threadA'], $this->ids['forumA'], $this->member);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
		$I->loginToForum('targetmember');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	/**
	 * A reply form's id is the thread that was checked, never a forum to start a thread in.
	 */
	public function aReplyFormCannotStartAThreadInTheForumNamedByItsThreadId(AcceptanceTester $I)
	{
		$page = '/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA'];

		$I->sendPostRequest($page, array(
			'e-token'   => $I->grabForumToken($page),
			'subject'   => 'A thread from a reply form',
			'post'      => 'Posted through a reply form',
			'newthread' => 'Post thread',
			'action'    => 'nt',
		));

		$I->dontSeeInDatabase('e107_forum_thread', array('thread_name' => 'A thread from a reply form'));
		$I->seeInDatabase('e107_forum_post', array(
			'post_thread' => $this->ids['threadA'], 'post_entry like' => '%Posted through a reply form%',
		));
	}

	/**
	 * A new-thread form's id is the forum that was checked, never a thread to reply in.
	 */
	public function aNewThreadFormCannotReplyToTheThreadNamedByItsForumId(AcceptanceTester $I)
	{
		$page = '/e107_plugins/forum/forum_post.php?f=nt&id='.$this->ids['forumA'];

		$I->sendPostRequest($page, array(
			'e-token' => $I->grabForumToken($page),
			'subject' => 'A thread from a new-thread form',
			'post'    => 'Posted through a new-thread form',
			'reply'   => 'Post reply',
			'action'  => 'rp',
		));

		$thread = array('thread_forum_id' => $this->ids['forumA'], 'thread_name' => 'A thread from a new-thread form');
		$post = array('post_entry like' => '%Posted through a new-thread form%');

		$I->seeInDatabase('e107_forum_thread', $thread);
		$I->seeNumRecords(1, 'e107_forum_post', $post);
		$I->seeInDatabase('e107_forum_post', $post + array(
			'post_thread' => $I->grabFromDatabase('e107_forum_thread', 'thread_id', $thread),
		));
	}

	/**
	 * A quoted reply is saved after a preview, when its form posts back "quote" instead of "rp".
	 */
	public function aQuotedReplyIsSavedAfterAPreview(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=quote&id='.$this->ids['threadA'].'&post='.$this->ids['postA']);
		$I->submitForm('#dataform', array('post' => 'Quoted after a preview'), 'fpreview');
		$I->submitForm('#dataform', array('post' => 'Quoted after a preview'), 'reply');

		$I->seeInDatabase('e107_forum_post', array(
			'post_thread' => $this->ids['threadA'], 'post_entry like' => '%Quoted after a preview%',
		));
	}

	/**
	 * A quote is saved in the quoted post's thread, whatever thread the URL names.
	 */
	public function aQuoteCannotBeSavedInAThreadTheMemberMayNotPostIn(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=quote&id='.$this->staffThread.'&post='.$this->ids['postA']);
		$I->submitForm('#dataform', array('post' => 'A quote aimed at the staff thread'), 'reply');

		$post = array('post_entry like' => '%A quote aimed at the staff thread%');
		$I->dontSeeInDatabase('e107_forum_post', $post + array('post_thread' => $this->staffThread));
		$I->seeInDatabase('e107_forum_post', $post + array('post_thread' => $this->ids['threadA']));
	}

	/**
	 * A post's thread decides its forum, so a post filed under another forum opens nothing.
	 */
	public function aQuoteOfAPostFiledUnderAnotherForumIsCheckedAgainstItsThreadsForum(AcceptanceTester $I)
	{
		$filed = $I->haveForumPost('Filed under forum A', $this->staffThread, $this->ids['forumA'], $this->member);
		$page = '/e107_plugins/forum/forum_post.php?f=quote&id='.$this->ids['threadA'].'&post='.$filed;

		$I->sendPostRequest($page, array(
			'e-token' => $I->grabForumToken('/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA']),
			'post'    => 'A quote of a post filed under another forum',
			'reply'   => 'Post reply',
			'action'  => 'rp',
		));

		$I->dontSeeInDatabase('e107_forum_post', array('post_entry like' => '%A quote of a post filed under another forum%'));
		$I->amOnPage($page);
		$I->seeInSource('forum-post-unauthorized');
	}

	/**
	 * A split's default destination is the forum its form names, the thread's own.
	 */
	public function aDefaultSplitOfAPostFiledUnderAnotherForumStaysInTheThreadsForum(AcceptanceTester $I)
	{
		$filed = $I->haveForumPost('Filed under forum A, then split away', $this->staffThread, $this->ids['forumA'], $this->member);
		$I->haveForumMember('targetstaffmod', '253,'.\Helper\ForumFixture::CLASS_MOD_B);
		$I->logoutFromForum();
		$I->loginToForum('targetstaffmod');

		$I->sendPostRequest('/e107_plugins/forum/forum_post.php?f=split&id='.$this->staffThread.'&post='.$filed, array(
			'e-token'          => $I->grabForumToken('/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA']),
			'split_thread'     => 1,
			'forum_split'      => '',
			'new_thread_title' => 'Split from the staff thread',
		));

		$I->seeInDatabase('e107_forum_thread', array(
			'thread_name' => 'Split from the staff thread', 'thread_forum_id' => $this->staffForum,
		));
	}

	public function aMemberStillEditsTheirOwnPostInAnOpenThread(AcceptanceTester $I)
	{
		$this->editPost($I, $this->openPost, 'Edited in an open thread');

		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->openPost, 'post_entry like' => '%Edited in an open thread%'));
	}

	/**
	 * The lock that is checked is the post's own thread's, whatever thread the URL names.
	 */
	public function anEditCannotReachAPostInALockedThread(AcceptanceTester $I)
	{
		$this->editPost($I, $this->lockedPost, 'Edited after the lock');

		$I->dontSeeInDatabase('e107_forum_post', array('post_id' => $this->lockedPost, 'post_entry like' => '%Edited after the lock%'));
		$I->amOnPage('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$this->lockedPost);
		$I->seeInSource('forum-post-locked');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $postId
	 * @param string $text
	 */
	private function editPost(AcceptanceTester $I, $postId, $text)
	{
		$I->sendPostRequest('/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$postId, array(
			'e-token'      => $I->grabForumToken('/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA']),
			'post'         => $text,
			'update_reply' => 'Update reply',
			'action'       => 'edit',
		));
	}
}
