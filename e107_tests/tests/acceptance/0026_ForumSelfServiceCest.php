<?php

/**
 * "Your own post" has to mean your own post.
 *
 * The self-service delete authorised on `USERID == $row['post_user']` and
 * nothing else. USERID is 0 for a caller with no account, and an anonymous post
 * stores post_user 0, so every unauthenticated visitor owned every anonymous
 * post on the site. Such a request carries no cookie, so the CSRF rule does not
 * challenge it either, and neither that handler nor postDelete() ever looks at
 * which forum the post is in. Reproduced against a running install before this
 * was written: one request, no session, and the row was gone.
 *
 * The same pseudo-identity sat in the edit path, in isAuthor().
 *
 * Three further rules the endpoint's name, its docblock and the control that
 * offers it all promise, and which the server never checked: the post must be
 * the last in its thread, must not be the one that opened it, and the thread
 * must still be open.
 */
class ForumSelfServiceCest
{
	/** @var array */
	private $ids;

	/** @var int */
	private $alice;

	/** @var int */
	private $anonPost;

	/** @var int */
	private $aliceReply;

	public function _before(AcceptanceTester $I)
	{
		$I->resetForumFloodProtection();
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();
		$this->alice = $I->haveForumMember('selfalice');

		// An anonymous post, as a forum with guest posting enabled produces.
		$this->anonPost = $I->haveForumPost(
			'Anonymous post', $this->ids['threadA'], $this->ids['forumA'], 0);

		// Alice's own reply, last in the thread and not the opening post.
		$this->aliceReply = $I->haveForumPost(
			'Reply by alice', $this->ids['threadA'], $this->ids['forumA'], $this->alice);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	/**
	 * The one that was live: no account, no session, no token.
	 */
	public function aGuestCannotDeleteAnAnonymousPost(AcceptanceTester $I)
	{
		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->sendPostRequest($this->topic(), array(
			'action' => 'deletepost',
			'post'   => $this->anonPost,
		));

		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->anonPost));
	}

	/**
	 * A guest must not be able to edit one either. Asserted on the stored text,
	 * because a refusal that still wrote would read as a pass on the response.
	 */
	public function aGuestCannotEditAnAnonymousPost(AcceptanceTester $I)
	{
		// id is required: checkForumJump() bounces a request without one before
		// the edit is ever considered, which would make this pass for the wrong
		// reason.
		$I->sendPostRequest(
			'/e107_plugins/forum/forum_post.php?f=edit&id='.$this->ids['threadA'].'&post='.$this->anonPost,
			array('update_reply' => 1, 'post' => 'REWRITTEN BY A GUEST')
		);

		$I->seeInDatabase('e107_forum_post', array(
			'post_id' => $this->anonPost,
			'post_entry' => 'Anonymous post',
		));
	}

	/**
	 * The control. Self-service deletion must still work, or every refusal in
	 * this file passes because the feature stopped functioning.
	 */
	public function aMemberCanStillDeleteTheirOwnLastPost(AcceptanceTester $I)
	{
		$I->loginToForum('selfalice');

		$this->deletePost($I, $this->aliceReply);

		$I->dontSeeInDatabase('e107_forum_post', array('post_id' => $this->aliceReply));
	}

	/**
	 * Not the last post in the thread. The control that offers this only appears
	 * on the last one, and the server never agreed.
	 */
	public function aMemberCannotDeleteAPostThatIsNotTheLast(AcceptanceTester $I)
	{
		$earlier = $I->haveForumPost(
			'Earlier reply by alice', $this->ids['threadA'], $this->ids['forumA'], $this->alice);

		$I->haveForumPost('Someone else replied after', $this->ids['threadA'], $this->ids['forumA'], 1);

		$I->loginToForum('selfalice');

		$this->deletePost($I, $earlier);

		$I->seeInDatabase('e107_forum_post', array('post_id' => $earlier));
	}

	/**
	 * The opening post is the thread. Removing it this way left the thread row
	 * behind with nothing to open it.
	 */
	public function aMemberCannotDeleteThePostThatOpenedTheThread(AcceptanceTester $I)
	{
		$thread = $I->haveForumThread('Alice started this', $this->ids['forumA'], $this->alice);
		$opening = $I->haveForumPost('Opening post by alice', $thread, $this->ids['forumA'], $this->alice);

		$I->loginToForum('selfalice');

		$this->deletePost($I, $opening);

		$I->seeInDatabase('e107_forum_post', array('post_id' => $opening));
		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $thread));
	}

	/**
	 * A locked thread is locked for its authors too. forum_post.php's own
	 * checkPerms() has always honoured thread_active; this route did not.
	 */
	public function aMemberCannotDeleteTheirPostInALockedThread(AcceptanceTester $I)
	{
		$locked = $I->haveForumThread('Locked thread', $this->ids['forumA'], 1, 0);
		$I->haveForumPost('Opening post', $locked, $this->ids['forumA'], 1);
		$reply = $I->haveForumPost('Alice reply in locked', $locked, $this->ids['forumA'], $this->alice);

		$I->loginToForum('selfalice');

		$this->deletePost($I, $reply);

		$I->seeInDatabase('e107_forum_post', array('post_id' => $reply));
	}

	/**
	 * Somebody else's post, which the ownership test was supposed to be for.
	 */
	public function aMemberCannotDeleteSomebodyElsesPost(AcceptanceTester $I)
	{
		$theirs = $I->haveForumPost(
			'Post by another member', $this->ids['threadA'], $this->ids['forumA'], 1);

		$I->loginToForum('selfalice');

		$this->deletePost($I, $theirs);

		$I->seeInDatabase('e107_forum_post', array('post_id' => $theirs));
	}

	/**
	 * @return string
	 */
	private function topic()
	{
		return '/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA'];
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $postId
	 */
	private function deletePost(AcceptanceTester $I, $postId)
	{
		$page = $this->topic();
		$token = $I->grabForumToken($page);

		$I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
		$I->sendPostRequest($page, array(
			'action'  => 'deletepost',
			'post'    => $postId,
			'e-token' => $token,
		));
	}
}
