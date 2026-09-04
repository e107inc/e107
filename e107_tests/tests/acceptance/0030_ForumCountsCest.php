<?php

/**
 * The bookkeeping the forum keeps about itself.
 *
 *  - forum_lastpost_info means "when the last post happened, and in which
 *    thread"; postAdd() writes post_datestamp into it. The recalculation read
 *    thread_datestamp instead, which is when the thread was *started*, and
 *    ordered by it, so a forum's last post became its newest thread, dated to
 *    that thread's birth. postDelete() and threadDelete() both trigger the
 *    recalculation, so deleting one spam post could point a busy forum at a
 *    thread nobody had touched in years.
 *  - thread_total_replies excludes the opening post everywhere it is read.
 *    threadUpdateCounts() stored the raw row count, leaving a split topic one
 *    reply heavy at both ends, which the reader turns into a phantom page.
 *  - "Mark all forums read" passes no forum id, and the branch that means
 *    "all of them" tested against 0 rather than emptiness, so the request took
 *    the per-forum path, matched nothing and redirected having done nothing.
 */
class ForumCountsCest
{
	/** @var array */
	private $ids;

	/** @var int */
	private $threadOld;

	/** @var int */
	private $threadNew;

	/** @var int the moment threadOld was last posted in */
	private $recentPost;

	/** @var int the moment threadNew was started */
	private $newThreadStart;

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

		// An old thread that is still being posted in, and a young one that is
		// not. Ordering by when each began puts them the wrong way round.
		$this->recentPost = time() - 120;
		$this->newThreadStart = time() - 1800;

		$this->threadOld = $I->haveForumThread('Fixture Thread Old', $this->ids['forumA'], 1, 1,
			time() - 604800, $this->recentPost);
		$this->threadNew = $I->haveForumThread('Fixture Thread New', $this->ids['forumA'], 1, 1,
			$this->newThreadStart, $this->newThreadStart);

		$I->haveForumPost('Opening post in the old thread', $this->threadOld, $this->ids['forumA'], 1);
		$I->haveForumPost('Opening post in the new thread', $this->threadNew, $this->ids['forumA'], 1);

		$this->alice = $I->haveForumMember('countalice');
		$I->haveForumMember('countmoda', '253,'.\Helper\ForumFixture::CLASS_MOD_A);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	/**
	 * Delete a post, then ask the forum where its last post is. It has to be the
	 * thread that was last posted in, dated when that happened.
	 */
	public function deletingAPostLeavesTheForumPointingAtItsNewestActivity(AcceptanceTester $I)
	{
		$doomed = $I->haveForumPost('a reply about to go', $this->threadNew, $this->ids['forumA'], 1);

		$I->loginToForum('countmoda');
		$this->deletePost($I, $this->threadNew, $doomed);
		$I->dontSeeInDatabase('e107_forum_post', array('post_id' => $doomed));

		$I->seeInDatabase('e107_forum', array(
			'forum_id' => $this->ids['forumA'],
			'forum_lastpost_info' => $this->recentPost.'.'.$this->threadOld,
		));
	}

	/**
	 * The same value must not name the thread that merely started most recently.
	 */
	public function theForumsLastPostIsNotSimplyItsNewestThread(AcceptanceTester $I)
	{
		$doomed = $I->haveForumPost('a reply about to go', $this->threadNew, $this->ids['forumA'], 1);

		$I->loginToForum('countmoda');
		$this->deletePost($I, $this->threadNew, $doomed);

		$I->dontSeeInDatabase('e107_forum', array(
			'forum_id' => $this->ids['forumA'],
			'forum_lastpost_info' => $this->newThreadStart.'.'.$this->threadNew,
		));
	}

	/**
	 * Three posts in a thread is two replies, because every reader of this
	 * column adds one back for the post that opened it.
	 */
	public function recountingAThreadCountsRepliesRatherThanPosts(AcceptanceTester $I)
	{
		$I->haveForumPost('first reply', $this->threadOld, $this->ids['forumA'], 1);
		$I->haveForumPost('second reply', $this->threadOld, $this->ids['forumA'], 1);

		$I->recountForumThread($this->threadOld);

		$I->seeInDatabase('e107_forum_thread', array(
			'thread_id' => $this->threadOld,
			'thread_total_replies' => 2,
		));
	}

	/**
	 * A thread with nothing but its opening post has no replies, and the count
	 * must not go negative on an empty one either.
	 */
	public function aThreadWithOnlyItsOpeningPostHasNoReplies(AcceptanceTester $I)
	{
		$I->recountForumThread($this->threadNew);

		$I->seeInDatabase('e107_forum_thread', array(
			'thread_id' => $this->threadNew,
			'thread_total_replies' => 0,
		));
	}

	/**
	 * Mark all forums read. The request carries no id at all, which is exactly
	 * the case the branch test got wrong. It carries a token because marking
	 * threads read is a write, and forum.php now refuses one that does not.
	 */
	public function markingAllForumsReadMarksThemRead(AcceptanceTester $I)
	{
		$I->loginToForum('countalice');
		// The update needs a row to land on; a member created through the
		// ordinary signup would have one already.
		$I->haveForumThreadsRead($this->alice, array());

		$I->amOnPage('/e107_plugins/forum/forum.php?f=mfar&e-token='
			.$I->grabForumToken('/e107_plugins/forum/forum.php'));

		$I->seeInDatabase('e107_user_extended', array(
			'user_extended_id' => $this->alice,
			'user_plugin_forum_viewed like' => '%'.$this->threadOld.'%',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $threadId the topic page the request is made from
	 * @param int $postId
	 */
	private function deletePost(AcceptanceTester $I, $threadId, $postId)
	{
		$page = '/e107_plugins/forum/forum_viewtopic.php?id='.$threadId;

		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->sendPostRequest($page, array(
			'action'  => 'deletepost',
			'post'    => $postId,
			'e-token' => $I->grabForumToken($page),
		));
	}
}
