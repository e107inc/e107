<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * A forum's thread list prints a code written in a thread's name as text, as the thread itself does.
 */
class ForumThreadListParsedOnceCest
{
	/** @var array ids from Helper\ForumFixture::haveForumStructure() */
	private $ids;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
	}

	public function aCodeInAThreadNameStaysText(AcceptanceTester $I)
	{
		$thread = $I->haveForumThread('Help {PRINT_ITEM=a&quot; autofocus tabindex=1 onfocus=&quot;alert(1)&quot; b^news.1} please', $this->ids['forumA'], 1);
		$I->haveForumPost('Opening post', $thread, $this->ids['forumA'], 1);
		$sticky = $I->haveForumThread('Read this first', $this->ids['forumA'], 1);
		$I->haveForumPost('Opening post', $sticky, $this->ids['forumA'], 1);
		$I->updateInDatabase('e107_forum_thread', array('thread_sticky' => 1), array('thread_id' => $sticky));
		$I->purgeForumPermCache();

		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA']);

		$I->see('Help {PRINT_ITEM=a');
		$I->dontSeeElement('[onfocus]');
		$I->dontSeeInSource('{LAN=');
	}
}
