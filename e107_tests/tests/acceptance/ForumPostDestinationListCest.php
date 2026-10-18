<?php

/**
 * The split and move forms list a moderator only the forums they moderate and may view.
 */
class ForumPostDestinationListCest
{
	/** e_UC_NOBODY, a view class no member and no administrator holds. */
	const CLASS_NOBODY = 255;

	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	/** @var int a second forum the moderator of forum A moderates */
	private $forumA2;

	/** @var int a forum the moderator of forum A moderates and may not view */
	private $hidden;

	/** @var int a reply in thread A, for the split form to start from */
	private $replyA;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();
		$this->forumA2 = $I->haveForum('Fixture Forum A2', 'fixture-forum-a2', $this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 3);
		$this->hidden = $I->haveForum('Fixture Hidden Forum', 'fixture-hidden-forum', $this->ids['category'], \Helper\ForumFixture::CLASS_MOD_A, 4, self::CLASS_NOBODY);
		$this->replyA = $I->haveForumPost('A reply in thread A', $this->ids['threadA'], $this->ids['forumA'], 1);

		$I->haveForumMember('destmoda', '253,'.\Helper\ForumFixture::CLASS_MOD_A);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
	}

	public function aModeratorIsOfferedAnotherForumTheyModerate(AcceptanceTester $I)
	{
		$I->loginToForum('destmoda');

		foreach($this->pickers() as $page => $select)
		{
			$I->amOnPage($page);

			$I->seeElement($this->option($select, $this->forumA2));
			$I->see('Fixture Forum A(Current)', $select.' option[value=""]');
		}
	}

	public function aModeratorIsOfferedNoForumTheyDoNotModerateOrMayNotView(AcceptanceTester $I)
	{
		$I->loginToForum('destmoda');

		foreach($this->pickers() as $page => $select)
		{
			$I->amOnPage($page);

			$I->seeElement($select);
			$I->dontSeeElement($this->option($select, $this->ids['forumB']));
			$I->dontSeeElement($this->option($select, $this->hidden));
			$I->dontSeeInSource('Fixture Hidden Forum');
		}
	}

	/**
	 * A main administrator moderates every forum, and is offered each one as before, viewable or not.
	 */
	public function aMainAdministratorIsOfferedEveryOtherForum(AcceptanceTester $I)
	{
		$I->loginAsAdmin();

		$forums = $I->grabColumnFromDatabase('e107_forum', 'forum_id', array('forum_parent !=' => 0));
		$unlisted = $I->grabColumnFromDatabase('e107_forum', 'forum_id', array('forum_parent !=' => 0, 'forum_name like' => '*%'));
		$unlisted[] = $this->ids['forumA'];
		$expected = array_map('intval', array_values(array_diff($forums, $unlisted)));
		sort($expected);

		foreach($this->pickers() as $page => $select)
		{
			$I->amOnPage($page);

			$offered = array_map('intval', array_filter($I->grabMultiple($select.' option', 'value'), 'strlen'));
			sort($offered);

			$I->assertSame($expected, $offered, $select);
		}
	}

	/**
	 * Both forms, for thread A in forum A.
	 *
	 * @return array page => the CSS selector of its forum list
	 */
	private function pickers()
	{
		return array(
			'/e107_plugins/forum/forum_post.php?f=split&id='.$this->ids['threadA'].'&post='.$this->replyA => 'select[name=forum_split]',
			'/e107_plugins/forum/forum_post.php?f=move&id='.$this->ids['threadA']                         => 'select[name=forum_move]',
		);
	}

	/**
	 * @param string $select
	 * @param int $forumId
	 * @return string CSS selector
	 */
	private function option($select, $forumId)
	{
		return $select.' option[value="'.(int) $forumId.'"]';
	}
}
