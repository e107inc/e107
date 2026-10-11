<?php

/**
 * The posting page authorises each action against the forum and the post it acts on.
 */
class ForumPostAuthorisationCest
{
	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	/** @var int a second forum the moderator of forum A also moderates */
	private $forumA2;

	/** @var int a reply in thread A, for a split to take away */
	private $replyA;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(2);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();
		$this->forumA2 = $I->haveForum('Fixture Forum A2', 'fixture-forum-a2', $this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 3);
		$this->replyA = $I->haveForumPost('A reply in thread A', $this->ids['threadA'], $this->ids['forumA'], 1);

		$I->haveForumMember('authmoda', '253,'.\Helper\ForumFixture::CLASS_MOD_A);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
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

	/**
	 * Split thread A from its reply onwards into $toForum, as the signed-in member.
	 *
	 * @param AcceptanceTester $I
	 * @param int $toForum
	 * @param string $title
	 */
	private function split(AcceptanceTester $I, $toForum, $title)
	{
		$I->sendPostRequest('/e107_plugins/forum/forum_post.php?f=split&id='.$this->ids['threadA'].'&post='.$this->replyA, array(
			'e-token'          => $I->grabForumToken('/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA']),
			'split_thread'     => 1,
			'forum_split'      => $toForum,
			'new_thread_title' => $title,
		));
	}
}
