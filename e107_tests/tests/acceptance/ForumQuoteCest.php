<?php

/**
 * Pressing Quote under a post opens the reply form with that post in a
 * [quote] block naming its author, bbcode as the author wrote it. A [hide]
 * block goes into the quote sealed, and wherever the quote is posted it
 * shows only to a member who has posted in the thread it was quoted from.
 */
class ForumQuoteCest
{
	/** e_UC_ADMIN, a class the members here are not in */
	const ADMINS_ONLY = 254;

	/** @var array */
	private $ids;

	/** @var int the post Quote was last pressed under */
	private $quoted;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();

		$this->ids = $I->haveForumStructure();

		$I->haveForumMember('quotebob');

		$I->purgeForumPermCache();
		$I->logoutFromForum();
		$I->loginToForum('quotebob');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropForumProbe();
	}

	public function quotingAPostFillsTheReplyBoxWithAQuoteBlock(AcceptanceTester $I)
	{
		$box = $this->pressQuote($I, 'A [b]plain[/b] reply to quote');

		$I->assertStringContainsString("[quote=admin]\nA [b]plain[/b] reply to quote\n[/quote]", $box);
	}

	public function hiddenContentIsNotQuoted(AcceptanceTester $I)
	{
		$box = $this->pressQuote($I, 'Shown [HIDE=x]kept back[/HIDE] as well');

		$I->assertStringContainsString("[quote=admin]\nShown ", $box);
		$I->assertStringNotContainsString('kept back', $box);
	}

	public function aQuotedHiddenBlockShowsOnlyToAMemberWhoHasPostedInItsThread(AcceptanceTester $I)
	{
		$page = $this->quoteAHiddenBlockIntoThreadB($I);

		$I->logoutFromForum();
		$I->loginToForum('quoteinA');
		$I->amOnPage($page);
		$I->see('The answer is 42');

		$I->logoutFromForum();
		$I->loginToForum('quoteinB');
		$I->amOnPage($page);
		$I->see('as well');
		$I->dontSee('The answer is 42');

		$I->logoutFromForum();
		$I->amOnPage($page);
		$I->see('as well');
		$I->dontSee('The answer is 42');
	}

	public function aQuotedHiddenBlockStaysShutForAMemberWhoMayNoLongerViewItsThread(AcceptanceTester $I)
	{
		$page = $this->quoteAHiddenBlockIntoThreadB($I);
		$I->updateInDatabase('e107_forum', array('forum_class' => self::ADMINS_ONLY), array('forum_id' => $this->ids['forumA']));
		$I->purgeForumPermCache();

		$I->logoutFromForum();
		$I->loginToForum('quoteinA');
		$I->amOnPage($page);
		$I->see('as well');
		$I->dontSee('The answer is 42');
	}

	public function aQuotedHiddenBlockStaysShutOnceItsPostHasLeftTheThread(AcceptanceTester $I)
	{
		$page = $this->quoteAHiddenBlockIntoThreadB($I);
		$I->updateInDatabase('e107_forum_post', array('post_thread' => 0), array('post_id' => $this->quoted));

		$I->logoutFromForum();
		$I->loginToForum('quoteinA');
		$I->amOnPage($page);
		$I->see('as well');
		$I->dontSee('The answer is 42');
	}

	/**
	 * Quotes a post with a [hide] block from thread A into thread B, where quoteinB posts it; quoteinA has a reply in thread A.
	 *
	 * @param AcceptanceTester $I
	 * @return string the page of thread B
	 */
	private function quoteAHiddenBlockIntoThreadB(AcceptanceTester $I)
	{
		$box = $this->pressQuote($I, 'Shown [hide]The answer is 42[/hide] as well');

		$I->assertStringNotContainsString('The answer is 42', $box);
		$I->assertSame(1, preg_match('#\[hide\][\w.-]+\[/hide\]#', $box), $box);

		$replier = $I->haveForumMember('quoteinA');
		$I->haveForumPost('A reply in A', $this->ids['threadA'], $this->ids['forumA'], $replier);
		$elsewhere = $I->haveForumMember('quoteinB');
		$I->haveForumPost($box, $this->ids['threadB'], $this->ids['forumB'], $elsewhere);

		return '/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadB'];
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $entry the post to quote, written by the administrator
	 * @return string what the reply box holds
	 */
	private function pressQuote(AcceptanceTester $I, $entry)
	{
		$this->quoted = $I->haveForumPost($entry, $this->ids['threadA'], $this->ids['forumA'], 1);

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->click('a[href*="f=quote"][href$="post='.$this->quoted.'"]');

		return $I->grabValueFrom('textarea[name=post]');
	}
}
