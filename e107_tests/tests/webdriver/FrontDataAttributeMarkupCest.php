<?php

/**
 * Front-end script puts a data-* value it reads from a post into the page as text, not as markup.
 *
 * cleanHtml() keeps data-* attributes in the HTML of an author allowed to post it, so these values are the author's
 * text whoever reads the page.
 */
class FrontDataAttributeMarkupCest
{
	const POST = 'Gadgets <a class=&quot;e-modal&quot; href=&quot;/news.php&quot; data-modal-caption=&quot;&lt;details open ontoggle=window.__dg=1&gt;m&lt;/details&gt;&quot;>open</a> <span id=&quot;dg-load&quot;></span><a id=&quot;dg-ajax&quot; class=&quot;e-ajax&quot; href=&quot;#&quot; data-src=&quot;/news.php&quot; data-target=&quot;#dg-load&quot; data-loading-target=&quot;#dg-load&quot; data-loading-icon=&#039;x&quot;&gt;&lt;/i&gt;&lt;details open ontoggle=window.__dg=2&gt;a&lt;/details&gt;&lt;i class=&quot;&#039;>load</a> end';

	/** @var array */
	private $ids;

	public function _before(WebDriverTester $I)
	{
		$I->haveForumPluginInstalled();
		$this->ids = $I->haveForumStructure();
		$I->haveForumPost(self::POST, $this->ids['threadA'], $this->ids['forumA'], 1);
		$I->purgeForumPermCache();
	}

	public function _after(WebDriverTester $I)
	{
		$I->dropForumProbe();
	}

	public function aModalCaptionIsShownAsText(WebDriverTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->click('a.e-modal');
		$I->waitForJS("var c = document.querySelector('#uiModal .modal-caption'); return window.__dg !== undefined || (c !== null && c.textContent.indexOf('ontoggle') !== -1);", 10);

		$I->assertNull($I->executeJS('return window.__dg === undefined ? null : window.__dg;'));
		$I->assertStringContainsString('<details open ontoggle=window.__dg=1>m</details>', $I->executeJS("return document.querySelector('#uiModal .modal-caption').textContent;"));
	}

	public function anAjaxLoadingIconStaysAClass(WebDriverTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->executeJS('jQuery.ajax = function () { return jQuery.Deferred(); };');
		$I->click('#dg-ajax');
		$I->waitForJS("return window.__dg !== undefined || document.querySelector('#dg-load i.e-ajax-loading') !== null;", 10);

		$I->assertNull($I->executeJS('return window.__dg === undefined ? null : window.__dg;'));
		$I->dontSeeElement('#dg-load details');
	}
}
