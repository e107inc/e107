<?php

/**
 * faqs.php acted on $_POST before it loaded the preference that says who may post.
 *
 * Three handlers wrote to the faqs table with no permission test and no token:
 * faq_submit inserted an entry, faq_edit_submit overwrote one, and
 * submit_a_question, inside the faq class constructor, inserted an unanswered
 * question. check_class($faqpref['add_faq']) guarded only the markup that draws
 * the forms, and $faqpref was not loaded until well after the first two
 * handlers had run. Both preferences ship as user class 255, so on a stock
 * install of the plugin the feature is switched off for everybody and the only
 * thing that still worked was the unauthenticated write.
 *
 * Only the question survives. faq_edit_submit was deleted rather than gated,
 * because the row it updated was chosen by a variable that is never assigned
 * and the edit form posted a faq_id the handler never read. faq_submit went the
 * same way on issue #6376: the route that drew its form has not dispatched
 * since 2014, so the endpoint is asserted here to write nothing at all, for a
 * visitor every preference admits and with the token the page itself published.
 *
 * Every hostile POST here is cookieless, which is what a stranger's script
 * sends. e_core_session::attest() exempts a request that carries no ambient
 * authority, so a cookieless POST reaches the handler and the page's own guard
 * is the whole of what stands in its way. A POST that did carry cookies would
 * be turned away by the framework and would prove nothing about this file.
 *
 * @see e107_plugins/faqs/faqs.php
 * @see e107_handlers/session_handler.php  e_core_session::attest()
 */
class FaqsAnonymousWriteCest
{
	const PROBE_FILE = 'e107_tests_faqs_authz.php';

	const PAGE = '/e107_plugins/faqs/faqs.php';

	/** e_UC_PUBLIC, which the test process does not define. */
	const EVERYONE = 0;

	/** e_session::CSRF_CHECK_SAME_SITE, likewise. */
	const SAME_SITE = 4;

	/** The category faqs_setup seeds, visible to guests. */
	const CATEGORY = 1;

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('faqs');
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
	}

	public function _after(AcceptanceTester $I)
	{
		$this->haveCsrfMode($I, 'default');
		$I->dropPluginInstall('faqs');
		$I->dropPluginProbe();
	}

	/**
	 * The handler the report missed, in the same file and the same shape. It has
	 * been inserting anonymous rows by name since 2015, while the one that was
	 * reported spent most of that time broken on a column mismatch.
	 */
	public function anAnonymousPostDoesNotAskAQuestion(AcceptanceTester $I)
	{
		$I->wantTo('refuse a question from a caller the submit_question class excludes');

		$this->postAnonymously($I, $this->askQuestion('ask-refused'));

		$I->dontSeeInDatabase('e107_faqs', array('faq_question' => $this->marker('ask-refused')));
	}

	/**
	 * A site that opens either feature to guests still has to be able to tell a
	 * visitor of its own from a script posting at the endpoint directly, which
	 * is what the token is for.
	 */
	public function aTokenlessQuestionIsRefusedEvenWhereTheClassAdmitsEveryone(AcceptanceTester $I)
	{
		$I->wantTo('refuse a tokenless question even where the class admits a guest');

		$this->havePermissions($I, self::EVERYONE);

		$this->postAnonymously($I, $this->askQuestion('ask-tokenless'));

		$I->dontSeeInDatabase('e107_faqs', array('faq_question' => $this->marker('ask-tokenless')));
	}

	/**
	 * The control. A guard on a write is worse than the hole it closes if the
	 * people the site meant to admit lose the feature, so this posts what the
	 * page itself published: the token e_token_injector puts in the ask form.
	 */
	public function aVisitorTheClassAdmitsStillAsksAQuestion(AcceptanceTester $I)
	{
		$I->wantTo('let a permitted visitor ask a question');

		$this->havePermissions($I, self::EVERYONE);

		$token = $this->grabPublishedToken($I);

		$I->sendPostRequest(self::PAGE, array_merge($this->askQuestion('ask-permitted'), array('e-token' => $token)));

		$I->seeInDatabase('e107_faqs', array(
			'faq_question' => $this->marker('ask-permitted'),
			'faq_answer'   => '',
		));
	}

	/**
	 * The submission the page has no handler for any more. Nothing is left to
	 * gate, so the strongest request a visitor can make, the class open to
	 * everyone and the token the page published, gets the ordinary listing back
	 * and writes no row.
	 */
	public function theAddRouteIsGoneEvenForAVisitorTheClassAdmits(AcceptanceTester $I)
	{
		$I->wantTo('write no FAQ for a submission the removed handler no longer reads');

		$this->havePermissions($I, self::EVERYONE);

		$token = $this->grabPublishedToken($I);

		$I->sendPostRequest(self::PAGE, array_merge($this->addFaq('add-removed'), array('e-token' => $token)));

		$I->seeResponseCodeIs(200);
		$I->dontSeeInDatabase('e107_faqs', array(
			'faq_question' => $this->marker('add-removed'),
			'faq_parent'   => self::CATEGORY,
		));
	}

	/**
	 * The other half of the control, and the reason the token test above pins a
	 * mode. e_token_injector publishes nothing in a mode that reads no token, so
	 * a page in one of those modes draws a tokenless form, and an endpoint that
	 * demanded a token there would drop the submission of the very visitor the
	 * site admitted. What the framework asks for instead is the browser's word.
	 */
	public function aVisitorStillWritesInAModeThatPublishesNoToken(AcceptanceTester $I)
	{
		$I->wantTo('take a tokenless question in a CSRF mode that mints no token');

		$this->havePermissions($I, self::EVERYONE);
		$this->haveCsrfMode($I, self::SAME_SITE);

		$I->haveHttpHeader('Sec-Fetch-Site', 'same-origin');
		$I->sendPostRequest(self::PAGE, $this->askQuestion('ask-fetch-metadata'));

		$I->seeInDatabase('e107_faqs', array('faq_question' => $this->marker('ask-fetch-metadata')));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $post
	 * @return void
	 */
	private function postAnonymously(AcceptanceTester $I, array $post)
	{
		$I->resetAllCookies();
		$I->sendPostRequest(self::PAGE, $post);
	}

	/**
	 * @param string $case
	 * @return array the submission the removed add-an-FAQ handler read
	 */
	private function addFaq($case)
	{
		return array(
			'faq_submit'   => 'Add',
			'faq_parent'   => self::CATEGORY,
			'faq_question' => $this->marker($case),
			'data'         => 'Injected by '.__CLASS__.'.',
			'faq_comment'  => 0,
		);
	}

	/**
	 * @param string $case
	 * @return array a submission for the ask-a-question handler
	 */
	private function askQuestion($case)
	{
		return array(
			'submit_a_question' => 'Submit',
			'ask_a_question'    => $this->marker($case),
		);
	}

	/**
	 * @param string $case
	 * @return string a question text no other case writes
	 */
	private function marker($case)
	{
		return 'QJ68 anonymous write '.$case;
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int $class user class both features are opened to
	 * @return void
	 */
	private function havePermissions(AcceptanceTester $I, $class)
	{
		$I->amOnProbe('act=prefs&add='.(int) $class.'&ask='.(int) $class);
		$I->seeInSource('PROBE_OK');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param int|string $mode a csrf_enforce value, or 'default' to remove it
	 * @return void
	 */
	private function haveCsrfMode(AcceptanceTester $I, $mode)
	{
		$I->amOnProbe('act=csrf&mode='.urlencode($mode));
		$I->seeInSource('PROBE_OK');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string the token the FAQ page published to its own forms
	 */
	private function grabPublishedToken(AcceptanceTester $I)
	{
		$I->amOnPage(self::PAGE);

		$matched = preg_match('/name="e-token" value="([^"]+)"/', $I->grabPageSource(), $m);
		$I->assertSame(1, $matched, 'the FAQ page must publish a token to the forms it draws');

		return $m[1];
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for FaqsAnonymousWriteCest.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

if(isset($_GET['act']) && $_GET['act'] === 'prefs')
{
	$config = e107::getPlugConfig('faqs');
	$config->set('add_faq', (int) $_GET['add']);
	$config->set('submit_question', (int) $_GET['ask']);
	$config->save(false, true, false);
	echo "PROBE_OK prefs\n";
	exit;
}

if(isset($_GET['act']) && $_GET['act'] === 'csrf')
{
	$config = e107::getConfig('core');
	if($_GET['mode'] === 'default') { $config->remove('csrf_enforce'); }
	else { $config->set('csrf_enforce', (int) $_GET['mode']); }
	$config->save(false, true, false);
	echo "PROBE_OK csrf\n";
	exit;
}

echo "unknown act\n";
PHP;
	}
}
