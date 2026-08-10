<?php

/**
 * The CAPTCHA as a whole, driven the way a visitor drives it.
 *
 * secure_imageTest exercises the handler in process, and 0046_ContactFormCest
 * asserts that the contact form has a CAPTCHA at all. Neither of them can say
 * whether a challenge served to a browser survives the round trip, and the
 * round trip is the entire design: the answer leaves the server sealed inside
 * the token, comes back in a POST, and is spent there.
 *
 * Six of the seven tests here are about that round trip. The seventh is about
 * what the round trip is for. e107 used to write a row or a file for every
 * CAPTCHA it drew, which meant a web spider fetching images it would never
 * answer could fill the site's storage for the price of a GET. Rendering must
 * now cost nothing, and "nothing" is only worth claiming if it is measured, so
 * the last test weighs the same pages with and without a CAPTCHA on them and
 * proves the measurement can see a write by taking one.
 *
 * Outbound mail is captured with e107's own dry run transport, as in
 * 0046_ContactFormCest: mail_log_options is set to "1,1" for the duration, so
 * a delivered message is a line in e107_system/<site path>/logs/mailoutlog.log
 * and nothing leaves the container.
 */
class CaptchaLifecycleCest
{
	const PROBE_FILE = 'e107_tests_captcha_probe.php';

	/** How many challenges the spider test draws without answering any. */
	const RENDER_COUNT = 5;

	public function _before(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->amOnPage('/'.self::PROBE_FILE.'?act=setup');
		$I->seeInSource('PROBE_OK');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=teardown');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * The answer is not in the document, and it is not in the token either.
	 *
	 * The second half is the one that matters. Its predecessor signed the
	 * challenge and did not encrypt it, so the answer sat in the middle segment
	 * of the token in plain base64: in the form's hidden field, and in the query
	 * string of the image the page went on to fetch. Every segment of the token
	 * is therefore decoded here and searched, which is exactly the work a
	 * visitor would have had to do to read the answer off the page.
	 */
	public function theAnswerIsNowhereInWhatTheVisitorIsServed(AcceptanceTester $I)
	{
		$I->wantTo('keep the CAPTCHA answer out of the document and out of the token');

		$I->amOnPage('/contact.php');
		$source = $I->grabPageSource();

		$token = $this->tokenFromSource($I, $source);
		$answer = $this->solve($I, $token);

		$I->assertNotSame('', $answer, 'the site must be able to open the challenge it just issued');
		$I->assertCount(5, explode('.', $token), 'the challenge must be a compact JWE');

		$I->assertStringNotContainsString($answer, $source,
			'the answer must not appear in the document the visitor was served');

		$I->assertSame($token, $this->imageTokenFromSource($I, $source),
			'the image must draw the challenge the form is going to submit');

		$segments = explode('.', $token);

		// The header segment is meant to decode, and decoding it is what says
		// the search below ran over the bytes of the token rather than over
		// nothing at all.
		$I->assertStringContainsString('"alg"', $this->decodeSegment($segments[0]),
			'the first segment must decode to a JOSE header');

		foreach($segments as $index => $segment)
		{
			$I->assertStringNotContainsString($answer, $this->decodeSegment($segment),
				'segment '.$index.' of the token must not decode to the answer');
		}
	}

	/**
	 * The positive control the rest of the file rests on. Without it every
	 * refusal below is satisfied by a CAPTCHA nobody can ever pass.
	 */
	public function theRenderedChallengeCanBeAnswered(AcceptanceTester $I)
	{
		$I->wantTo('deliver a contact message that answers the CAPTCHA as rendered');

		$this->probe($I, 'act=clearmaillog');

		$I->sendPostRequest('/contact.php', $this->answeredSubmission($I));

		$I->assertStringContainsString('Mail-ID=', $this->mailLog($I),
			'a correct answer must be accepted');
	}

	public function aWrongAnswerIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('refuse a contact message that answers the CAPTCHA wrongly');

		$this->probe($I, 'act=clearmaillog');

		$post = $this->answeredSubmission($I);
		$post['code_verify'] = 'not-the-answer';

		$I->sendPostRequest('/contact.php', $post);

		$I->assertStringNotContainsString('Mail-ID=', $this->mailLog($I),
			'a wrong answer must be refused');
	}

	/**
	 * A challenge is spent by being answered, not by being answered correctly.
	 *
	 * This is what stops a visitor sitting on one image and working through the
	 * five characters: without it the token stays good for its whole life and
	 * every wrong guess costs the attacker nothing but a request.
	 */
	public function aChallengeReplayedAfterAWrongAnswerIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('refuse a second attempt at a challenge that was already answered wrongly');

		$this->probe($I, 'act=clearmaillog');

		$post = $this->answeredSubmission($I);
		$right = $post['code_verify'];

		$post['code_verify'] = 'not-the-answer';
		$I->sendPostRequest('/contact.php', $post);

		$I->assertStringNotContainsString('Mail-ID=', $this->mailLog($I),
			'the wrong answer must be refused before the replay is tested');

		$post['code_verify'] = $right;
		$I->sendPostRequest('/contact.php', $post);

		$I->assertStringNotContainsString('Mail-ID=', $this->mailLog($I),
			'a challenge already answered wrongly must not accept the right answer afterwards');
	}

	public function aChallengeReplayedAfterACorrectAnswerIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('refuse a solved challenge that is submitted a second time');

		$this->probe($I, 'act=clearmaillog');

		$post = $this->answeredSubmission($I);
		$I->sendPostRequest('/contact.php', $post);

		$I->assertStringContainsString('Mail-ID=', $this->mailLog($I),
			'the first submission must be accepted before the replay is tested');

		$this->probe($I, 'act=clearmaillog');
		$I->sendPostRequest('/contact.php', $post);

		$I->assertStringNotContainsString('Mail-ID=', $this->mailLog($I),
			'a solved challenge must not be spendable twice');
	}

	/**
	 * A challenge belongs to the form that issued it.
	 *
	 * Both pages are fetched by the same client in the same jar, so the token
	 * moved here differs from an acceptable one in one respect only: the form
	 * it was issued for. Answering the password reset form and spending the
	 * answer on the contact form is the resale this refuses.
	 */
	public function aChallengeIssuedForAnotherFormIsRefused(AcceptanceTester $I)
	{
		$I->wantTo('refuse a challenge that was issued for a different form');

		$this->probe($I, 'act=clearmaillog');

		$I->amOnPage('/fpw.php');
		$foreign = $this->tokenFromSource($I, $I->grabPageSource());
		$answer = $this->solve($I, $foreign);
		$I->assertNotSame('', $answer, 'the password reset form must issue a challenge of its own');

		$I->amOnPage('/contact.php');
		$post = array_merge($this->submission(), array(
			'e-token'     => $this->csrfTokenFromSource($I, $I->grabPageSource()),
			'rand_num'    => $foreign,
			'code_verify' => $answer,
		));

		$I->sendPostRequest('/contact.php', $post);

		$I->assertStringNotContainsString('Mail-ID=', $this->mailLog($I),
			'a challenge issued for the password reset form must not be answerable on the contact form');
	}

	/**
	 * Drawing a CAPTCHA writes nothing down, which is the whole reason the
	 * answer travels with the visitor.
	 *
	 * Measured as a difference, not as an absolute, because a request to this
	 * site is not free whatever it asks for: e107 may start a session, prime a
	 * system cache or note the visitor, and none of that belongs to the CAPTCHA.
	 * So each half of the challenge is weighed against the same page serving the
	 * same visitor with the CAPTCHA taken out of it. The password reset form has
	 * a preference for exactly that, and the image endpoint answers a request
	 * carrying no token by exiting before any CAPTCHA code runs. What survives
	 * the subtraction is what the CAPTCHA cost.
	 *
	 * Recorded here because the subtraction hides it: a request to
	 * e107_images/secimg.php leaves a session row behind whether it carries a
	 * challenge or not. That is class2.php's doing on an endpoint this work does
	 * not touch, it predates the sealed token, and it is the remaining reason a
	 * spider fetching CAPTCHA images is not quite free.
	 *
	 * The spent markers are asserted absolutely on top of the differences,
	 * because a marker is written under a name of its own and has nothing to
	 * hide behind. The control at the end answers one challenge properly and
	 * requires exactly one marker to appear, so a run where the instrument was
	 * looking in the wrong directory cannot pass as a run where nothing was
	 * written.
	 */
	public function drawingChallengesWritesNothingServerSide(AcceptanceTester $I)
	{
		$I->wantTo('draw CAPTCHAs without writing anything down for them');

		$opened = $this->snapshot($I);

		$this->probe($I, 'act=fpwcode&v=0');
		$I->amOnPage('/fpw.php');
		$I->dontSeeInSource('rand_num');
		$without = $this->footprint($I, array('/fpw.php'));

		$this->probe($I, 'act=fpwcode&v=1');
		$I->amOnPage('/fpw.php');
		$I->seeInSource('rand_num');
		$with = $this->footprint($I, array('/fpw.php'));

		$this->compare($I, $without, $with, 'minting a challenge on the password reset form');

		$I->amOnPage('/contact.php');
		$imageUrl = $this->imageUrlFromSource($I, $I->grabPageSource());

		$bare = $this->footprint($I, array('/e107_images/secimg.php'));
		$drawn = $this->footprint($I, array($imageUrl));

		$this->compare($I, $bare, $drawn, 'drawing a challenge at the image endpoint');

		$drew = $this->snapshot($I);

		$I->assertSame($opened['spent'], $drew['spent'],
			'drawing a challenge must not write a spent marker');

		// The control. An answered challenge does write, and the instrument
		// above has to be able to see it.
		$this->probe($I, 'act=clearmaillog');
		$I->sendPostRequest('/contact.php', $this->answeredSubmission($I));
		$I->assertStringContainsString('Mail-ID=', $this->mailLog($I),
			'the control submission must be accepted, or it wrote no marker to find');

		$spent = $this->snapshot($I);

		$I->assertSame($drew['spent'] + 1, $spent['spent'],
			'answering a challenge must write exactly one spent marker');
	}

	/**
	 * What RENDER_COUNT passes over a list of URLs leave behind, as a cookieless
	 * visitor who never comes back.
	 *
	 * One pass runs before the measurement starts, so that a cache primed the
	 * first time anything asks for it is charged to nobody.
	 *
	 * @param AcceptanceTester $I
	 * @param array $urls fetched in order, once per pass
	 * @return array measure => int
	 */
	private function footprint(AcceptanceTester $I, array $urls)
	{
		$this->pass($I, $urls);

		$before = $this->snapshot($I);

		for($i = 0; $i < self::RENDER_COUNT; $i++)
		{
			$this->pass($I, $urls);
		}

		$after = $this->snapshot($I);

		$footprint = array();

		foreach($before as $measure => $value)
		{
			$footprint[$measure] = $after[$measure] - $value;
		}

		return $footprint;
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $urls
	 * @return void
	 */
	private function pass(AcceptanceTester $I, array $urls)
	{
		foreach($urls as $url)
		{
			$I->resetAllCookies();
			$I->amOnPage($url);
		}
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $without footprint of the same requests without a CAPTCHA
	 * @param array $with footprint of the requests carrying one
	 * @param string $what named in the failure message
	 * @return void
	 */
	private function compare(AcceptanceTester $I, array $without, array $with, $what)
	{
		foreach(array('sessionfiles', 'sessionrows', 'cachefiles') as $measure)
		{
			$I->assertSame($without[$measure], $with[$measure],
				$what.' must leave nothing behind, measured in '.$measure
				.' over '.self::RENDER_COUNT.' cookieless requests');
		}
	}

	/**
	 * A contact submission that is valid apart from the CAPTCHA fields.
	 *
	 * @return array
	 */
	private function submission()
	{
		return array(
			'author_name'    => 'CAPTCHA Lifecycle Tester',
			'email_send'     => 'captcha-lifecycle@example.com',
			'subject'        => 'CAPTCHA lifecycle subject',
			'body'           => 'This body is comfortably longer than the fifteen characters the form insists on.',
			'send-contactus' => 'Send',
		);
	}

	/**
	 * Open the contact form and come back with everything a browser would post,
	 * including the answer to the challenge the page was actually served.
	 *
	 * @param AcceptanceTester $I
	 * @return array
	 */
	private function answeredSubmission(AcceptanceTester $I)
	{
		$I->amOnPage('/contact.php');
		$source = $I->grabPageSource();

		$token = $this->tokenFromSource($I, $source);
		$answer = $this->solve($I, $token);

		$I->assertNotSame('', $answer, 'the site must be able to open the challenge it just issued');

		return array_merge($this->submission(), array(
			'e-token'     => $this->csrfTokenFromSource($I, $source),
			'rand_num'    => $token,
			'code_verify' => $answer,
		));
	}

	/**
	 * One segment of a compact serialisation, as anybody holding the token can
	 * read it.
	 *
	 * @param string $segment
	 * @return string
	 */
	private function decodeSegment($segment)
	{
		return (string) base64_decode(strtr($segment, '-_', '+/'), false);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $source rendered markup
	 * @return string the sealed challenge the form will submit
	 */
	private function tokenFromSource(AcceptanceTester $I, $source)
	{
		$matched = preg_match('/name=[\'"]rand_num[\'"] value=[\'"]([^\'"]+)[\'"]/', $source, $m);
		$I->assertSame(1, $matched, 'the rendered form must carry a rand_num field');

		return $m[1];
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $source rendered markup
	 * @return string the challenge the image tag asks for
	 */
	private function imageTokenFromSource(AcceptanceTester $I, $source)
	{
		$matched = preg_match('/secimg\.php\?id=([^\'"&]+)/', $source, $m);
		$I->assertSame(1, $matched, 'the rendered form must carry a CAPTCHA image');

		return urldecode($m[1]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $source rendered markup
	 * @return string
	 */
	private function imageUrlFromSource(AcceptanceTester $I, $source)
	{
		$matched = preg_match('#([\w./-]*secimg\.php\?id=[^\'"]+)#', $source, $m);
		$I->assertSame(1, $matched, 'the rendered form must carry a CAPTCHA image');

		return html_entity_decode($m[1]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $source rendered markup
	 * @return string
	 */
	private function csrfTokenFromSource(AcceptanceTester $I, $source)
	{
		$matched = preg_match('/name="e-token" value="([^"]+)"/', $source, $m);
		$I->assertSame(1, $matched, 'the contact page must publish a CSRF token to its forms');

		return $m[1];
	}

	/**
	 * Read the answer back out of a challenge, which only the site can do.
	 *
	 * That a test needs a fixture inside the application for this is the point
	 * of the change: the answer used to be a base64 decode away for anybody
	 * holding the token.
	 *
	 * @param AcceptanceTester $I
	 * @param string $token
	 * @return string
	 */
	private function solve(AcceptanceTester $I, $token)
	{
		$out = $this->probe($I, 'act=solve&t='.urlencode($token));

		$matched = preg_match('/ANSWER=(\S*)/', $out, $m);
		$I->assertSame(1, $matched, 'the probe must answer with a solution line');

		return $m[1];
	}

	/**
	 * What the server has written down, as the probe counts it.
	 *
	 * Called twice and read from the second call. The probe is itself a request
	 * to the site, and the tests clear the cookie jar, so its first call after a
	 * clearing may start a session of its own. Reading the second call means
	 * every snapshot carries the same self-inflicted cost and the differences
	 * between them stay honest.
	 *
	 * @param AcceptanceTester $I
	 * @return array measure => int
	 */
	private function snapshot(AcceptanceTester $I)
	{
		$this->probe($I, 'act=snapshot');
		$out = $this->probe($I, 'act=snapshot');

		$snapshot = array();

		foreach(array('sessionfiles', 'sessionrows', 'cachefiles', 'spent') as $measure)
		{
			$matched = preg_match('/'.strtoupper($measure).'=(-?\d+)/', $out, $m);
			$I->assertSame(1, $matched, 'the probe must report '.$measure);
			$snapshot[$measure] = (int) $m[1];
		}

		return $snapshot;
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string mail log contents
	 */
	private function mailLog(AcceptanceTester $I)
	{
		return $this->probe($I, 'act=maillog');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?'.$query);

		return $I->grabPageSource();
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		return <<<'PHP'
<?php
// Fixture for 0072_CaptchaLifecycleCest. Removed again in the Cest's _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

// Every request in the container arrives from the bridge address, so a Cest
// that makes more than a handful of them bans itself part way through.
e107::getDb()->delete('online');
e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$config = e107::getConfig('core');
$logFile = e_LOG.'mailoutlog.log';

/**
 * Where PHP is keeping its session files, whatever the site asked for.
 *
 * session_save_path() answers '' for the compiled-in default, and answers
 * "N;/path" when a depth was configured, so neither form can be handed
 * straight to glob().
 *
 * @return string
 */
function e107_test_session_path()
{
	$path = (string) session_save_path();

	if(strpos($path, ';') !== false)
	{
		$parts = explode(';', $path);
		$path = end($parts);
	}

	return $path === '' ? sys_get_temp_dir() : $path;
}

/**
 * @param string $pattern
 * @return int
 */
function e107_test_count($pattern)
{
	$found = glob($pattern);

	return is_array($found) ? count($found) : 0;
}

switch($act)
{
	case 'setup':
		$config->set('e107_tests_captcha_mail_backup', $config->get('mail_log_options', ''));
		$config->set('e107_tests_captcha_visibility_backup', $config->get('contact_visibility', ''));
		$config->set('e107_tests_captcha_contacts_backup', $config->get('sitecontacts', ''));
		$config->set('e107_tests_captcha_fpwcode_backup', $config->get('fpwcode', ''));
		// 1 is dry run: log it, do not send it, and let the caller believe it
		// went. 2 would attempt a real send, and there is no sendmail here.
		$config->set('mail_log_options', '1,1');
		$config->set('contact_visibility', '0');
		$config->set('sitecontacts', '250');
		$config->set('fpwcode', '1');
		$config->save(false, true, false);
		echo "PROBE_OK\n";
		break;

	case 'teardown':
		$config->set('mail_log_options', $config->get('e107_tests_captcha_mail_backup', ''));
		$config->set('contact_visibility', $config->get('e107_tests_captcha_visibility_backup', ''));
		$config->set('sitecontacts', $config->get('e107_tests_captcha_contacts_backup', ''));
		$config->set('fpwcode', $config->get('e107_tests_captcha_fpwcode_backup', ''));
		$config->remove('e107_tests_captcha_mail_backup');
		$config->remove('e107_tests_captcha_visibility_backup');
		$config->remove('e107_tests_captcha_contacts_backup');
		$config->remove('e107_tests_captcha_fpwcode_backup');
		$config->save(false, true, false);
		@unlink($logFile);
		echo "PROBE_OK\n";
		break;

	case 'fpwcode':
		// The one core form with a preference for whether it carries a CAPTCHA,
		// which is what lets the Cest weigh the same page with and without one.
		$config->set('fpwcode', (string) intval($_GET['v']));
		$config->save(false, true, false);
		echo "PROBE_OK\n";
		break;

	case 'solve':
		// Reading the answer out of a challenge is something only the site can
		// do, so the test has to come here for it.
		$claims = e107::getSealedToken(secure_image::TOKEN_PURPOSE)->open(varset($_GET['t'], ''));
		echo "PROBE_OK\n";
		echo 'ANSWER='.(isset($claims['solution']) ? $claims['solution'] : '')."\n";
		break;

	case 'snapshot':
		clearstatcache();
		$sessionRows = -1;
		try
		{
			$sessionRows = (int) e107::getDb()->createQueryBuilder()->from('session')->count();
		}
		catch(Exception $e)
		{
			// A site storing sessions in files has no rows to count, and the
			// constant answer keeps it out of every difference the Cest takes.
		}
		echo "PROBE_OK\n";
		echo 'SESSIONFILES='.e107_test_count(rtrim(e107_test_session_path(), '/').'/sess_*')."\n";
		echo 'SESSIONROWS='.$sessionRows."\n";
		echo 'CACHEFILES='.e107_test_count(e_CACHE_CONTENT.'*')."\n";
		echo 'SPENT='.e107_test_count(e_CACHE_CONTENT.secure_image::SPENT_DIRECTORY.'*'.secure_image::SPENT_SUFFIX)."\n";
		break;

	case 'clearmaillog':
		@unlink($logFile);
		echo "PROBE_OK\n";
		break;

	case 'maillog':
		clearstatcache();
		echo "PROBE_OK\n";
		echo is_readable($logFile) ? file_get_contents($logFile) : '';
		break;

	default:
		echo "unknown action\n";
}
PHP;
	}
}
