<?php

/**
 * Where no token is ever minted, demanding one refuses the site's own links.
 *
 * e_core_session::check() returns at e107_handlers/session_handler.php before
 * the define, so an install whose e_SECURITY_LEVEL is below SECURITY_LEVEL_LOW
 * never gets an e_TOKEN at all. That is a named setting, "Looking for trouble
 * (none)" in the admin preferences, and e107_config.php can define it, so it
 * is a configuration core supports rather than a broken one.
 *
 * A guard that asks only whether e-token is empty refuses the very link the
 * page just printed: the member is told to vote from the rating box on the
 * page itself while standing on the rating box on the page itself. Comment
 * posting has emitted a token the same way for years and works at this level
 * because nothing reads it, so members have every reason to expect rating to
 * work too.
 *
 * The guards therefore stand down where nothing is minted, hedged with
 * defined('e_TOKEN') as e107_admin/db.php does, and the builders are hedged
 * the same way, because defset('e_TOKEN') yields '' here and an e-token= with
 * nothing after it is a token that was submitted and cannot validate. These
 * cases lower the level to SECURITY_LEVEL_NONE for the duration and follow the
 * links the site itself publishes, which is the only way to see what the
 * builders emit here.
 *
 * The fixture writes e107_config.php, so the probe answers nobody who cannot
 * show the secret this run minted for it, and it loads class2.php before it
 * looks at what it was asked to do. A probe that acted first would be an
 * unauthenticated way for anyone at all to turn token minting off for the
 * whole site, which is a larger hole than the one this package closes.
 *
 * @see e107_handlers/session_handler.php  e_core_session::check()
 */
class SecurityLevelZeroCsrfCest
{
	const PROBE_FILE = 'e107_tests_security_level.php';

	const SIGNUP = '/signup.php';

	const DIALOG = '/e107_admin/image.php?mode=main&action=dialog&for=news&tagid=news_thumbnail&iframe=1&image=1';

	/** rate_table the fixture votes on, so no real content is disturbed. */
	const TABLE = 'ratelevelzero';

	/** Name the uploader is asked to write into e_IMPORT. */
	const UPLOAD = 'e107_tests_security_level.txt';

	/** e107Email writes this for every message it accepts, dry run included. */
	const SENT = 'Mail-ID=';

	/** The fragment every one of the three refusals shares. */
	const REFUSED = 'no security token';

	/** What attest() answers a token it cannot validate with. */
	const UNAUTHORIZED = 'Unauthorized access!';

	/** @var string what a caller shows to prove it is this run of this case */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$this->secret = substr(hash('sha256', uniqid('', true).mt_rand()), 0, 32);
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());

		$this->probe($I, 'act=setup');
		$this->probe($I, 'act=lower');

		$I->assertSame('LEVEL=0 TOKEN=0 STALE=1 UPLOADED=0', $this->probe($I, 'act=state'),
			'the fixture must be able to take an install below SECURITY_LEVEL_LOW');
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'act=restore');
		$this->probe($I, 'act=teardown');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * A member's vote is the one of the three an ordinary visitor casts, and
	 * the rating box is the only place it can be cast from.
	 */
	public function theRatingBoxesOwnLinkStillRecordsAVote(AcceptanceTester $I)
	{
		$ballot = $this->publishedBallot($I);

		$this->seeNoTokenWasPublished($I, $ballot);
		$I->amOnPage($ballot);

		$I->dontSeeInSource(self::REFUSED);
		$I->seeInDatabase('e107_rate', array(
			'rate_table'  => self::TABLE,
			'rate_rating' => 10,
			'rate_votes'  => 1,
		));
	}

	/**
	 * @param AcceptanceTester $I
	 */
	public function theSignupPagesOwnButtonStillSendsTheTestEmail(AcceptanceTester $I)
	{
		$link = $this->publishedTestLink($I);

		$this->seeNoTokenWasPublished($I, $link);
		$I->amOnPage($link);

		$I->dontSeeInSource(self::REFUSED);
		$I->assertStringContainsString(self::SENT, $this->probe($I, 'act=maillog'),
			'the administrator must still be able to send themselves a test');
	}

	/**
	 * @param AcceptanceTester $I
	 */
	public function theMediaManagersOwnUploaderUrlStillReachesTheUploader(AcceptanceTester $I)
	{
		$url = $this->publishedUploaderUrl($I);

		$this->seeNoTokenWasPublished($I, $url);
		$I->amOnPage($url.'&name='.self::UPLOAD);

		$I->dontSeeInSource(self::REFUSED);
		$I->assertStringContainsString('STALE=0', $this->probe($I, 'act=state'),
			'the Media Manager must still be able to reach the uploader');
	}

	/**
	 * A link published here outlives the level that published it: a bookmark,
	 * a copied link, a Media Manager dialog left open while an administrator
	 * raises the level. hasSubmittedToken() tests isset rather than empty, so
	 * a token published empty is one that was submitted and cannot validate,
	 * and attest() then answers the request before rate.php runs at all.
	 */
	public function aBallotPublishedHereSurvivesTheLevelGoingBackUp(AcceptanceTester $I)
	{
		$ballot = $this->publishedBallot($I);

		$this->probe($I, 'act=restore');
		$I->assertStringContainsString('TOKEN=1', $this->probe($I, 'act=state'),
			'the fixture must be able to put the install back above SECURITY_LEVEL_LOW');

		$I->amOnPage($ballot);

		$I->dontSeeInSource(self::UNAUTHORIZED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * The probe rewrites e107_config.php, so a caller that cannot show this
	 * run's secret has to get nothing at all. A probe left in the docroot by a
	 * run that died is otherwise an anonymous way to turn token minting off
	 * for every request the site serves.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=restore');

		$I->seeResponseCodeIs(403);
		$I->assertSame('LEVEL=0 TOKEN=0 STALE=1 UPLOADED=0', $this->probe($I, 'act=state'),
			'a refused caller must not have put the security level back');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $url as the site published it
	 * @return void
	 */
	private function seeNoTokenWasPublished(AcceptanceTester $I, $url)
	{
		$I->assertStringNotContainsString('e-token=', $url,
			'a builder must publish no token at all where none is minted');
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string path to follow, exactly as the rating box published it
	 */
	private function publishedBallot(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl('act=ratebox'));

		if(!preg_match("#<option value='([^']+)'>10</option>#", $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('the rating box published no link for a score of 10');
		}

		return html_entity_decode($matches[1]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string path to follow, exactly as the signup page published it
	 */
	private function publishedTestLink(AcceptanceTester $I)
	{
		$I->amOnPage(self::SIGNUP);

		if(!preg_match("#href='([^']*signup\.php\?test[^']*)'#", $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('the signup page published no test activation link');
		}

		return html_entity_decode($matches[1]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string path to follow, exactly as the Media Manager published it
	 */
	private function publishedUploaderUrl(AcceptanceTester $I)
	{
		$I->amOnPage(self::DIALOG);

		if(!preg_match('#<div id="uploader"[^>]*rel="([^"]+)"#', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('the Media Manager published no uploader URL');
		}

		return html_entity_decode($matches[1]);
	}

	/**
	 * @param string $query
	 * @return string
	 */
	private function probeUrl($query)
	{
		$url = '/'.self::PROBE_FILE.'?probe='.$this->secret;

		return ($query === '') ? $url : $url.'&'.$query;
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage($this->probeUrl($query));

		return trim($I->grabPageSource());
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$secret = $this->secret;
		$table = self::TABLE;
		$upload = self::UPLOAD;

		return <<<PHP
<?php
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}

if(!isset(\$_GET['probe']) || !hash_equals('$secret', \$_GET['probe']))
{
	header('HTTP/1.1 403 Forbidden', true, 403);
	echo 'Unauthorized access!';
	exit;
}

header('Content-Type: text/plain');

\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';
\$config = __DIR__.'/e107_config.php';
\$line = "define('e_SECURITY_LEVEL', 0);\\n";
\$cfg = e107::getConfig('core');
\$log = e_LOG.'mailoutlog.log';
\$stale = e_IMPORT.'e107_tests_security_level_stale.part';
\$upload = e_IMPORT.'$upload';

switch(\$act)
{
	case 'lower':
	case 'restore':
		\$src = str_replace("\\n".\$line, '', file_get_contents(\$config));

		if(\$act === 'lower')
		{
			\$at = strpos(\$src, '<?php') + 5;
			\$src = substr(\$src, 0, \$at)."\\n".\$line.substr(\$src, \$at);
		}

		file_put_contents(\$config, \$src);
		echo 'PROBE_OK';
		break;

	case 'setup':
		\$cfg->set('e107_tests_seclevel_mail_backup', \$cfg->get('mail_log_options', ''));
		\$cfg->set('e107_tests_seclevel_coppa_backup', \$cfg->get('use_coppa', ''));
		\$cfg->set('mail_log_options', '1,1');
		\$cfg->set('use_coppa', '0');
		\$cfg->save(false, true, false);

		if(!is_dir(e_IMPORT))
		{
			@mkdir(e_IMPORT, 0755, true);
		}

		@unlink(\$log);
		@unlink(\$upload);
		@unlink(\$upload.'.part');
		file_put_contents(\$stale, 'stale');
		touch(\$stale, time() - 86400);
		echo 'PROBE_OK';
		break;

	case 'teardown':
		\$cfg->set('mail_log_options', \$cfg->get('e107_tests_seclevel_mail_backup', ''));
		\$cfg->set('use_coppa', \$cfg->get('e107_tests_seclevel_coppa_backup', ''));
		\$cfg->remove('e107_tests_seclevel_mail_backup');
		\$cfg->remove('e107_tests_seclevel_coppa_backup');
		\$cfg->save(false, true, false);
		e107::getDb()->delete('rate', "rate_table = '$table'");
		@unlink(\$log);
		@unlink(\$stale);
		@unlink(\$upload);
		@unlink(\$upload.'.part');
		echo 'PROBE_OK';
		break;

	case 'ratebox':
		echo e107::getRate()->rateselect('', '$table', 1);
		break;

	case 'maillog':
		echo is_readable(\$log) ? file_get_contents(\$log) : '';
		break;

	default:
		clearstatcache();
		echo 'LEVEL='.e_SECURITY_LEVEL.' TOKEN='.(defined('e_TOKEN') ? 1 : 0)
			.' STALE='.(file_exists(\$stale) ? 1 : 0).' UPLOADED='.(file_exists(\$upload) ? 1 : 0);
		break;
}
PHP;
	}
}
