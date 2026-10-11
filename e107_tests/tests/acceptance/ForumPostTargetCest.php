<?php

/**
 * A forum submission is saved where its form was authorised, and nowhere else.
 */
class ForumPostTargetCest
{
	/** @var array */
	private $ids;

	/** @var string the site's flood switch before this Cest changed it */
	private $antifloodWas;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);

		$this->ids = $I->haveForumStructure();
		$this->antifloodWas = $I->haveSitePref('antiflood1', 0);

		$I->haveForumMember('targetmember');

		$I->purgeForumPermCache();
		$I->logoutFromForum();
		$I->loginToForum('targetmember');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveSitePref('antiflood1', $this->antifloodWas === '' ? null : $this->antifloodWas);
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
}
