<?php

/**
 * Pressing Quote under a post opens the reply form with that post in a
 * [quote] block naming its author, bbcode as the author wrote it.
 */
class ForumQuoteCest
{
	/** @var array */
	private $ids;

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

	/**
	 * @param AcceptanceTester $I
	 * @param string $entry the post to quote, written by the administrator
	 * @return string what the reply box holds
	 */
	private function pressQuote(AcceptanceTester $I, $entry)
	{
		$post = $I->haveForumPost($entry, $this->ids['threadA'], $this->ids['forumA'], 1);

		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->click('a[href*="f=quote"][href$="post='.$post.'"]');

		return $I->grabValueFrom('textarea[name=post]');
	}
}
