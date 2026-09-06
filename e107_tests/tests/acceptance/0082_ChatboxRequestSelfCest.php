<?php

/**
 * Where the chatbox sends a visitor who arrived on a rewritten URL (#5614).
 *
 * The plugin built its form actions and its paginator out of e_SELF, which
 * names the script the request resolved to rather than the address the visitor
 * asked for. On a site with SEF URLs those two differ, so submitting the
 * chatbox or following a page link took the visitor off the URL they were
 * reading and onto the underlying entry script.
 *
 * mod_rewrite is not configured in this docroot, so the divergence is made here
 * through the other intake {@see e107::set_urls()} supports for it: the
 * X-Rewrite-Url header, read before REQUEST_URI so that an IIS rewrite is seen
 * at all. It leaves e_SELF on the executed script and moves e_REQUEST_URL and
 * e_REQUEST_SELF onto the requested address, which is the state an Apache
 * rewrite produces.
 *
 * The addresses here are deliberately slashless, because that is the ordinary
 * SEF shape and it is the one that tells the two constants apart: e_REQUEST_URL
 * is the request verbatim, while e_REQUEST_SELF appends a trailing slash to any
 * path not ending in .php. The paginator wants the second, because it supplies
 * its own query; the three form actions want the first.
 *
 * The menu is exercised through a probe in the docroot: a menu renders inside a
 * page, and placing one in the installed theme's layout would be a test of the
 * layout. The probe supplies the $sql and $ns the menu file expects from the
 * scope that renders it, and nothing else. The form itself is behind USER, so
 * these tests sign in; anonymous posting is off on a stock install.
 */
class ChatboxRequestSelfCest
{
	const PLUGIN = 'chatbox_menu';

	const PROBE_FILE = 'e107_tests_chatbox_request_self_probe.php';

	const CHAT_PAGE = '/e107_plugins/chatbox_menu/chat.php';

	/** The address the visitor asked for, as a rewrite would report it. */
	const REWRITTEN = '/chat/talk-to-us';

	/** The same address with the visitor's own query on it, two parameters deep so the separator is under test too. */
	const REWRITTEN_QUERY = '/chat/talk-to-us?from=older&order=asc';

	/** How that address has to arrive in an attribute: a raw ampersand there is a character reference the browser may decode. */
	const ENCODED_QUERY = '/chat/talk-to-us?from=older&amp;order=asc';

	/** What a rewrite hands the script, which is the script's business and never an address to send the visitor back to. */
	const REWRITE_TARGET_QUERY = 'cb=talk-to-us';

	/** One more than chat.php's page size, so the paginator has a second page. */
	const SEEDED_POSTS = 31;

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled(self::PLUGIN);
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile(self::PROBE_FILE);
		$I->dropPluginInstall(self::PLUGIN);
		$I->dropPluginProbe();
	}

	public function theMenuFormPostsBackToTheRequestedUrl(AcceptanceTester $I)
	{
		$I->wantTo('post a chatbox message without leaving the page I am reading');

		$I->loginAsAdmin();
		$I->haveHttpHeader('X-Rewrite-Url', self::REWRITTEN);
		$I->amOnPage('/' . self::PROBE_FILE);

		$I->assertSame(200, $I->grabResponseCode());
		$I->assertStringEndsWith(
			self::REWRITTEN,
			$this->grabFormAction($I, "#<form id='chatbox' method='post' action='([^']*)'#"),
			'the chatbox menu has to post back to the address the visitor asked for, character for character'
		);
	}

	public function theMenuFormKeepsTheVisitorsOwnQuery(AcceptanceTester $I)
	{
		$I->wantTo('post a chatbox message from page two and still be on page two');

		$I->loginAsAdmin();
		$I->haveHttpHeader('X-Rewrite-Url', self::REWRITTEN_QUERY);
		$I->amOnPage('/' . self::PROBE_FILE . '?' . self::REWRITE_TARGET_QUERY);

		$action = $this->grabFormAction($I, "#<form id='chatbox' method='post' action='([^']*)'#");

		$I->assertStringEndsWith(self::ENCODED_QUERY, $action,
			"the query belongs to the visitor's address, and it has to survive the attribute intact");
		$I->assertStringNotContainsString(self::REWRITE_TARGET_QUERY, $action,
			'the parameters the rewrite synthesised are an address the visitor never asked for');
	}

	public function theModerationFormKeepsTheVisitorsOwnQuery(AcceptanceTester $I)
	{
		$I->wantTo('block a chatbox post and stay on the page I moderated from');

		$I->loginAsAdmin();
		$I->haveHttpHeader('X-Rewrite-Url', self::REWRITTEN_QUERY);
		$I->amOnPage(self::CHAT_PAGE . '?' . self::REWRITE_TARGET_QUERY);

		$action = $this->grabFormAction($I, "#<form method='post' action='([^']*)'>#");

		$I->assertStringEndsWith(self::ENCODED_QUERY, $action,
			'a moderator on page two has to be returned to page two');
		$I->assertStringNotContainsString(self::REWRITE_TARGET_QUERY, $action,
			'the parameters the rewrite synthesised are an address the visitor never asked for');
	}

	public function theModerationFormKeepsItsQueryWithNoRewriteAtAll(AcceptanceTester $I)
	{
		$I->wantTo('moderate from page two of a site that rewrites nothing and stay on page two');

		$I->loginAsAdmin();
		$I->amOnPage(self::CHAT_PAGE . strstr(self::REWRITTEN_QUERY, '?'));

		$I->assertStringEndsWith(
			self::CHAT_PAGE . strstr(self::ENCODED_QUERY, '?'),
			$this->grabFormAction($I, "#<form method='post' action='([^']*)'>#"),
			'most sites rewrite nothing, and the request constants have to hold their query there too'
		);
	}

	public function thePaginatorLinksBackToTheRequestedUrl(AcceptanceTester $I)
	{
		$I->wantTo('follow a chatbox page link without being dropped on the entry script');

		$this->seedPosts($I);

		$I->haveHttpHeader('X-Rewrite-Url', self::REWRITTEN);
		$I->amOnPage(self::CHAT_PAGE);

		$source = $I->grabPageSource();

		$I->assertStringContainsString(self::REWRITTEN . '/?30', $source,
			'the page links have to keep the visitor on the address they asked for, with the trailing slash e_REQUEST_SELF adds so the query composes');
		$I->assertStringNotContainsString('chat.php?30', $source,
			'no page link may drop the visitor on the entry script');
	}

	private function grabFormAction(AcceptanceTester $I, $pattern)
	{
		$source = $I->grabPageSource();

		$I->assertMatchesRegularExpression($pattern, $source,
			'precondition: the form under test has to be on the page at all');
		preg_match($pattern, $source, $match);

		return $match[1];
	}

	private function seedPosts(AcceptanceTester $I)
	{
		for ($i = 0; $i < self::SEEDED_POSTS; $i++)
		{
			$I->haveInDatabase('e107_chatbox', array(
				'cb_nick'      => '1.e107tests',
				'cb_message'   => 'Seeded by 0082_ChatboxRequestSelfCest, post ' . $i,
				'cb_datestamp' => time() - $i,
				'cb_blocked'   => 0,
				'cb_ip'        => '127.0.0.1',
			));
		}
	}

	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0082_ChatboxRequestSelfCest. Removed again in the Cest's _after().
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
require_once(HEADERF);

$sql = e107::getDb();
$ns = e107::getRender();

require(e_PLUGIN.'chatbox_menu/chatbox_menu.php');

require_once(FOOTERF);
PHP;
	}
}
