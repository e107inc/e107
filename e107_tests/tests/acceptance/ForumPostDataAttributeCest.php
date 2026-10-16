<?php

/**
 * A post_html author's markup keeps its data-* attributes only when the author also holds post_script.
 *
 * e107's own page script reads data-* attributes as configuration, and several of its readers put the values into
 * the page as markup or fetch what they name, some of them without a click.
 */
class ForumPostDataAttributeCest
{
	const GADGETS = 'DG <span class="e-rate" id="dgrate-7" data-hint="a,b,c,d,e" data-path="/e107_web/js/rate/img/"></span><span id="e-rate-dgrate-7">r</span>'
		.' <a class="e-modal" href="/news.php" data-modal-caption="c" data-modal-height="1">m</a>'
		.' <a class="e-ajax" href="#" data-src="/news.php" data-target="#dgt" data-loading="l">a</a><span id="dgt"></span>'
		.' <img class="e-ajax" src="/e107_images/button.png" alt="z" data-event="load" data-src="https://example.invalid/x" data-target="#dgt">'
		.' <a class="e-comment-edit" href="#" data-target="x">e</a> END';

	/** @var array */
	private $ids;

	public function _before(AcceptanceTester $I)
	{
		$I->haveForumPluginInstalled();
		$this->ids = $I->haveForumStructure();
		$I->purgeForumPermCache();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();
		$I->dropForumProbe();
	}

	public function anAuthorWithoutScriptAccessKeepsNoDataAttributes(AcceptanceTester $I)
	{
		$stored = $this->replyAsAdmin($I);

		$I->assertStringContainsString('e-modal', $stored);
		$I->assertStringNotContainsString('data-', $stored);
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->dontSeeElement('#dgrate-7[data-hint]');
		$I->dontSeeElement('a.e-modal[data-modal-caption]');
		$I->dontSeeElement('a.e-ajax[data-src]');
		$I->dontSeeElement('img.e-ajax[data-event]');
		$I->dontSeeElement('a.e-comment-edit[data-target]');
	}

	public function anAuthorWithScriptAccessKeepsThem(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('post_script', '250');
		$stored = $this->replyAsAdmin($I);

		$I->assertStringContainsString('data-modal-caption', $stored);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string the stored reply
	 */
	private function replyAsAdmin(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$page = '/e107_plugins/forum/forum_post.php?f=rp&id='.$this->ids['threadA'];
		$I->sendPostRequest($page, array('post' => self::GADGETS, 'reply' => 'Submit', 'e-token' => $I->grabForumToken($page)));

		// Posts made through the form outlive the fixture, so only this test's thread is read.
		return (string) $I->grabFromDatabase('e107_forum_post', 'post_entry',
			array('post_thread' => $this->ids['threadA'], 'post_entry like' => 'DG %'));
	}
}
