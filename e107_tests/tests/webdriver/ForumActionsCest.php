<?php

/**
 * The forum's click handlers, in a browser that actually runs them.
 *
 * Four defects live entirely in forum.js and its neighbour in core, and none of
 * them is reachable from PhpBrowser, which runs no JavaScript at all:
 *
 *  - forum.js filtered its elements with jQuery's one(), a one-time event
 *    binder, where the jQuery-Once idiom once() was meant. Given a name and no
 *    handler, one() returns the set untouched, so nothing was marked and
 *    nothing filtered. Every successful track and quick reply calls
 *    attachBehaviors() again, so each one bound another click handler to every
 *    action on the page.
 *  - Core's a[data-confirm] handler returned the answer from confirm(), and
 *    jQuery turns a false return into preventDefault() plus stopPropagation().
 *    stopPropagation() only stops the event reaching ancestors; a second
 *    handler on the same element still runs. forum.js binds one, and binds it
 *    first, so a moderator who clicked Cancel on "delete this thread?" deleted
 *    the thread.
 *  - The quick-reply text was read by calling getContent() on whatever
 *    tinymce.get() returned, for every action including moderator links on
 *    pages with no quick-reply box at all. A page carrying TinyMCE for
 *    anything else threw before the request was made.
 *  - No error callback, so a refused or broken request produced no message and
 *    no trace, which looks exactly like a click that never fired.
 *
 * csrf_enforce is pinned to 0 for every case that asserts an absence. Those
 * are about what the browser does with a click; a request refused for a token
 * reason would satisfy "nothing happened" without the dialog having anything to
 * do with it. The token modes have their own coverage in CsrfOverTlsCest and
 * CsrfPlainHttpCest. The one case that asserts a presence lifts the pin, so the
 * token the synthesised POST appends is actually validated somewhere.
 */
class ForumActionsCest
{
	/** Counts calls rather than completions, so there is no race to lose. */
	const COUNT_AJAX = <<<'JS'
window.__forumAjax = 0;
var original = jQuery.ajax;
jQuery.ajax = function () { window.__forumAjax++; return original.apply(this, arguments); };
JS;

	const CAPTURE_ERRORS = <<<'JS'
window.__forumErrors = [];
var original = console.error;
console.error = function () {
	window.__forumErrors.push(Array.prototype.slice.call(arguments).join(' '));
	return original.apply(console, arguments);
};
JS;

	/** @var array */
	private $ids;

	public function _before(WebDriverTester $I)
	{
		$I->resetForumFloodProtection();
		$I->haveForumPluginInstalled();
		$I->haveForumCsrfMode(0);

		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_A, 'fixture_mod_a');
		$I->haveUserClass(\Helper\ForumFixture::CLASS_MOD_B, 'fixture_mod_b');

		$this->ids = $I->haveForumStructure();

		$I->haveForumMember('wdalice');
		$I->haveForumMember('wdmoda', '253,'.\Helper\ForumFixture::CLASS_MOD_A);

		$I->purgeForumPermCache();
		$I->logoutFromForum();
	}

	public function _after(WebDriverTester $I)
	{
		$I->haveForumCsrfMode('default');
		$I->dropForumProbe();
	}

	/**
	 * The control for the two below: the confirmed delete has to work, or
	 * "Cancel left the thread alone" would pass on a route that deletes nothing
	 * whatever the visitor answers.
	 */
	public function confirmingADeleteStillDeletesTheThread(WebDriverTester $I)
	{
		$I->loginToForum('wdmoda');
		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA']);

		$this->clickDeleteLink($I, $this->ids['threadA']);
		$I->acceptPopup();

		$threadId = $this->ids['threadA'];
		\Test\Poll::until(function () use ($I, $threadId)
		{
			return $I->grabNumRecords('e107_forum_thread', array('thread_id' => $threadId)) === 0;
		}, 10);

		$I->dontSeeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadA']));
	}

	/**
	 * Cancel means cancel.
	 */
	public function cancellingADeleteLeavesTheThreadAlone(WebDriverTester $I)
	{
		$I->loginToForum('wdmoda');
		$I->amOnPage('/e107_plugins/forum/forum_viewforum.php?id='.$this->ids['forumA']);

		$I->executeJS(self::COUNT_AJAX);
		$this->clickDeleteLink($I, $this->ids['threadA']);
		$I->cancelPopup();

		$I->assertEquals(0, $I->executeJS('return window.__forumAjax;'),
			'Cancel must send nothing, or the rows below would only be there because the request was still in flight');
		$I->seeInDatabase('e107_forum_thread', array('thread_id' => $this->ids['threadA']));
		$I->seeInDatabase('e107_forum_post', array('post_id' => $this->ids['postA']));
	}

	/**
	 * One click, one request.
	 *
	 * attachBehaviors() runs again after every successful track and quick
	 * reply, which is what made the handlers pile up; calling it here is that
	 * same call, without needing a successful round trip first. Counted at the
	 * point jQuery.ajax is invoked, so nothing depends on how fast the server
	 * answers.
	 */
	public function reattachingBehavioursDoesNotDoubleTheRequest(WebDriverTester $I)
	{
		$I->loginToForum('wdalice');
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);
		$I->seeElement('#forum-track-button');

		$I->executeJS('e107.attachBehaviors();');
		$I->executeJS(self::COUNT_AJAX);

		$I->click('#forum-track-button');
		$I->waitForJS('return window.__forumAjax >= 1;', 10);

		$I->assertEquals(1, $I->executeJS('return window.__forumAjax;'),
			'one click on the track button should send exactly one request');
	}

	/**
	 * TinyMCE loaded, but not on the quick-reply box.
	 *
	 * tinymce.get() answers null for a field it is not attached to, and the
	 * handler called getContent() on that answer for every action on the page.
	 * The stub is that exact condition and nothing more.
	 */
	public function anActionStillFiresWhenTinymceHasNoQuickReplyEditor(WebDriverTester $I)
	{
		$I->loginToForum('wdalice');
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$I->executeJS('window.tinymce = { get: function () { return null; } };');
		$I->executeJS(self::COUNT_AJAX);

		$I->click('#forum-track-button');
		$I->waitForJS('return window.__forumAjax >= 1;', 10);

		$I->assertEquals(1, $I->executeJS('return window.__forumAjax;'),
			'the click should still reach the server with no editor on the page');
	}

	/**
	 * A request that fails has to say so somewhere.
	 */
	public function aFailedRequestLeavesATrace(WebDriverTester $I)
	{
		$I->loginToForum('wdalice');
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		// The handler posts to whatever the element's src names, so pointing it
		// at nothing is the shortest honest failure.
		$I->executeJS("document.getElementById('forum-track-button')"
			." .setAttribute('src', '/e107_tests_no_such_endpoint.php');");
		$I->executeJS(self::CAPTURE_ERRORS);

		$I->click('#forum-track-button');

		$I->waitForJS('return window.__forumErrors.length > 0;', 10);
	}

	/**
	 * The mis-click the carry-over exists for: something typed into the quick
	 * reply box, then Post Reply, which was plain GET navigation and dropped it.
	 *
	 * The one case here that lifts the csrf_enforce pin. It asserts a presence
	 * rather than an absence, so a token-related refusal reds it instead of
	 * passing it, and the harness serves plain HTTP from a non-loopback host,
	 * where 'default' degrades to a mode that publishes a token. That makes it
	 * the only coverage the token append has.
	 */
	public function postReplyCarriesTheQuickReplyTextIntoTheFullForm(WebDriverTester $I)
	{
		$typed = 'carried across by the mis-click';

		$I->haveForumCsrfMode('default');

		$I->loginToForum('wdalice');
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$I->fillField('#forum-quickreply-text', $typed);
		$this->clickPostReply($I);

		$I->seeInCurrentUrl('f=rp');
		$I->seeInField("textarea[name='post']", $typed);
	}

	/**
	 * An empty box has nothing to carry, so the link is followed as the link it
	 * looks like, which is what it did before any of this.
	 */
	public function postReplyWithAnEmptyQuickReplyIsPlainNavigation(WebDriverTester $I)
	{
		$I->loginToForum('wdalice');
		$I->amOnPage('/e107_plugins/forum/forum_viewtopic.php?id='.$this->ids['threadA']);

		$this->clickPostReply($I);

		$I->seeInCurrentUrl('f=rp');
		$I->seeInField("textarea[name='post']", '');
	}

	/**
	 * @param WebDriverTester $I
	 */
	private function clickPostReply(WebDriverTester $I)
	{
		$selector = "a[data-forum-action='postreply']";

		$I->seeElementInDOM($selector);
		$I->executeJS("document.querySelector(\"".$selector."\").click();");
		\Test\Poll::until(function () use ($I)
		{
			return strpos($I->grabFromCurrentUrl(), 'f=rp') !== false;
		}, 10);
	}

	/**
	 * The link sits in a closed dropdown, so it is clicked through the DOM
	 * rather than by pointer. The click event is the same one a visitor
	 * produces, which is all these tests are about.
	 *
	 * @param WebDriverTester $I
	 * @param int $threadId
	 */
	private function clickDeleteLink(WebDriverTester $I, $threadId)
	{
		$selector = "a[data-forum-action='delete'][data-forum-thread='".(int) $threadId."']";

		// Localise a miss: on the forum page at all, seeing the thread, and
		// seeing it as someone who may moderate it.
		$I->see('Fixture Forum A');
		$I->see('Fixture Thread A');
		$I->seeElementInDOM($selector);
		$I->executeJS("document.querySelector(\"".$selector."\").click();");
	}
}
