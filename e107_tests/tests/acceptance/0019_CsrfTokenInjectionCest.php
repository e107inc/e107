<?php

/**
 * Regression tests for GHSA-72q5-94gw-prww: the flush-time token injector.
 *
 * e_form::open() never emitted a token and much of core, plus an unbounded set
 * of third-party plugins, writes raw <form> markup, so the fix adds the token to
 * every eligible form as the finished page leaves the output buffer. Eligibility
 * is narrow on purpose, and two of these cases are about what must NOT happen:
 * a form posting off-site must not be handed the session's CSRF token, and form
 * markup sitting inside a textarea must come back byte-identical, because the
 * language-file editor and several plugin admin areas save what is in there.
 */
class CsrfTokenInjectionCest
{
	const PROBE_FILE = 'e107_tests_token_injection_probe.php';

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
	}

	public function _after(AcceptanceTester $I)
	{
		$I->deleteAppFile(self::PROBE_FILE);
	}

	public function sameOriginPostFormGetsTheToken(AcceptanceTester $I)
	{
		$I->wantTo('Add an e-token to a raw same-origin POST form that never asked for one');

		$I->amOnPage('/' . self::PROBE_FILE);

		$I->seeInSource('id="probe-same"><input type="hidden" name="e-token" value="');
	}

	public function getFormIsLeftAlone(AcceptanceTester $I)
	{
		$I->wantTo('Leave a GET form alone, so the token stays out of the query string');

		$I->amOnPage('/' . self::PROBE_FILE);

		$I->seeInSource('id="probe-get"><input name="q" /></form>');
	}

	public function crossOriginPostFormIsLeftAlone(AcceptanceTester $I)
	{
		$I->wantTo('Leave an off-site POST form alone, so no third party is handed the token');

		$I->amOnPage('/' . self::PROBE_FILE);

		$I->seeInSource('id="probe-cross"><input name="b" /></form>');
	}

	/**
	 * The data-loss case. An input written in here is saved back into the content.
	 */
	public function formMarkupInsideATextareaIsUntouched(AcceptanceTester $I)
	{
		$I->wantTo('Return form markup inside a textarea byte-identical');

		$I->amOnPage('/' . self::PROBE_FILE);

		$I->seeInSource('<textarea id="probe-textarea"><form method="post" action="/x">inner</form></textarea>');
	}

	public function tokenIsPublishedInTheDocumentHead(AcceptanceTester $I)
	{
		$I->wantTo('Publish the token in a meta tag so scripts find it on a form-less page');

		$I->amOnPage('/' . self::PROBE_FILE);

		$I->seeInSource('<meta name="e-token" content="');
	}

	/**
	 * The same output buffer carries the sitemap and the RSS feeds. A hidden input
	 * in either is invalid XML.
	 */
	public function nonHtmlResponsesAreNotTouched(AcceptanceTester $I)
	{
		$I->wantTo('Leave a non-HTML response alone entirely');

		$I->amOnPage('/' . self::PROBE_FILE . '?xml=1');

		$I->seeInSource('<feed><form method="post" action="" id="probe-xml"><input name="a" /></form></feed>');
		$I->dontSeeInSource('e-token');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0019_CsrfTokenInjectionCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}

if(isset($_GET['xml']))
{
	header('Content-Type: application/xml; charset=utf-8');
	echo '<feed><form method="post" action="" id="probe-xml"><input name="a" /></form></feed>';
	require_once(FOOTERF);
	exit;
}

require_once(HEADERF);
echo '<form method="post" action="" id="probe-same"><input name="a" /></form>';
echo '<form method="get" action="/search.php" id="probe-get"><input name="q" /></form>';
echo '<form method="post" action="https://evil.example.net/pay" id="probe-cross"><input name="b" /></form>';
echo '<textarea id="probe-textarea"><form method="post" action="/x">inner</form></textarea>';
require_once(FOOTERF);
PHP;
	}
}
