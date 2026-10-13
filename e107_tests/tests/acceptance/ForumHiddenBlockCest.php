<?php

/**
 * A [hide] block on the topic page shows what it holds to a member who has
 * posted in the thread, and a box to everyone else.
 */
class ForumHiddenBlockCest
{
	/** @var array */
	private $ids;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();

		$this->ids = $I->haveForumStructure();
		$I->haveForumPost('Intro [hide]The answer is 42[/hide] outro', $this->ids['threadA'], $this->ids['forumA'], 1);

		$replier = $I->haveForumMember('hidereplier');
		$I->haveForumPost('My own reply', $this->ids['threadA'], $this->ids['forumA'], $replier);
		$I->haveForumMember('hidelurker');

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
	}

	public function aMemberWhoHasPostedInTheThreadSeesTheBlock(AcceptanceTester $I)
	{
		$I->loginToForum('hidereplier');
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$I->see('The answer is 42');
	}

	public function aMemberWhoHasNotPostedInTheThreadSeesABox(AcceptanceTester $I)
	{
		$I->loginToForum('hidelurker');
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$I->see('outro');
		$I->dontSee('The answer is 42');
	}

	public function aGuestSeesABox(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$I->see('outro');
		$I->dontSee('The answer is 42');
	}
}
