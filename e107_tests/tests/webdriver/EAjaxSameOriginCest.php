<?php

/**
 * An e-ajax link fetches only a page or JSON from this site: its address can come from a post's markup, and what it fetches goes into the page, scripts included.
 */
class EAjaxSameOriginCest
{
	const POST = 'Links <span id=&quot;ea-away&quot;></span><a id=&quot;ea-xo&quot; class=&quot;e-ajax&quot; href=&quot;http://attacker.example/x#ea-away&quot;>away</a> <span id=&quot;ea-file&quot;></span><a id=&quot;ea-txt&quot; class=&quot;e-ajax&quot; data-ajax-type=&quot;GET&quot; href=&quot;/ea-probe.txt#ea-file&quot;>file</a> <a id=&quot;ea-js&quot; class=&quot;e-ajax&quot; data-ajax-type=&quot;GET&quot; href=&quot;/ea-probe.js#ea-file&quot;>script</a> <span id=&quot;ea-here&quot;></span><a id=&quot;ea-so&quot; class=&quot;e-ajax&quot; data-ajax-type=&quot;GET&quot; href=&quot;/ea-probe.html#ea-here&quot;>here</a> end';

	/** @var array */
	private $ids;

	public function _before(WebDriverTester $I)
	{
		$I->haveForumPluginInstalled();
		$this->ids = $I->haveForumStructure();
		$I->haveForumPost(self::POST, $this->ids['threadA'], $this->ids['forumA'], 1);
		$I->purgeForumPermCache();
		$I->haveProbe('ea-probe.txt', 'Sent as text <script>window.__ea = 1;</script>');
		$I->haveProbe('ea-probe.js', 'window.__ea = 2;');
		$I->haveProbe('ea-probe.html', '<b>here</b>');
	}

	public function _after(WebDriverTester $I)
	{
		$I->dropForumProbe();
	}

	public function aLinkToAnotherSiteFetchesNothing(WebDriverTester $I)
	{
		$this->openTopic($I);

		$I->click('#ea-xo');
		$I->click('#ea-so');
		$I->waitForJS("return document.getElementById('ea-here').textContent.indexOf('here') !== -1;", 10);

		$opened = $I->grabRequestsOpened();
		$I->assertStringContainsString('/ea-probe.html', $opened);
		$I->assertStringNotContainsString('attacker.example', $opened, 'the link to another site was requested');
		$I->assertSame('', $I->executeJS("return document.getElementById('ea-away').innerHTML;"));
	}

	public function aFileThisSiteSendsAsItIsGoesNowhere(WebDriverTester $I)
	{
		$this->openTopic($I);

		$I->click('#ea-txt');
		$I->click('#ea-js');
		$I->waitForRequestsEnded(2);
		$I->click('#ea-so');
		$I->waitForJS("return document.getElementById('ea-here').textContent.indexOf('here') !== -1;", 10);

		$I->assertNull($I->executeJS('return window.__ea === undefined ? null : window.__ea;'));
		$I->assertSame('', $I->executeJS("return document.getElementById('ea-file').innerHTML;"));
	}

	/**
	 * @param WebDriverTester $I
	 */
	private function openTopic(WebDriverTester $I)
	{
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->startRecordingRequests();
	}
}
