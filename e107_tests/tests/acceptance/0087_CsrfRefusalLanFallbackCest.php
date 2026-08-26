<?php

/**
 * The refusal message must not assume its own language file was loaded.
 *
 * e_signup is a handler, and nothing about it loads lan_signup.php: signup.php
 * calls e107::coreLan('signup') on the way in, and every other caller is on
 * its own. A refusal path that reads LAN_SIGNUP_REFUSED_TOKEN_MISSING as a
 * bare constant therefore answers with a thrown Error from PHP 8 on, and the
 * test matrix runs to PHP 8.5, so the administrator asking why no mail was
 * sent gets a 500 in place of the answer.
 *
 * rate.php does load its own language file, so it fails the other way. On
 * release/v2.3.x e107::includeLan() substitutes English only for a language
 * file it cannot read at all, so a translation pack shipping its own
 * lan_rate.php, which every pack does, leaves a term added to the English file
 * after that pack was translated undefined and the file is included having
 * defined nothing for it. The second case reproduces that state by taking the
 * term out of the language file the site is actually running, which is the
 * only half of the scenario that decides anything: what the file is missing,
 * not which pack it came from.
 *
 * That language file is tracked by git, so the copy the fixture takes of it is
 * parked beside it and registered with Extension\WorkspaceCleanup, which puts
 * a parked copy back on the way into a run as well as on the way out. A backup
 * in a temp directory can be discarded on its own - a rebuild, a different
 * container, down --volumes - and the next run would then take its copy of the
 * stripped file and strip it again to no effect, leaving a shipped English
 * string deleted for good while the case still passed.
 *
 * e107_web/js/plupload/upload.php already read its own string through
 * defset(). This holds the other two refusals to the same standard.
 *
 * @see e107_handlers/e107_class.php  e107::includeLan()
 * @see Extension\WorkspaceCleanup    restoreBackups()
 */
class CsrfRefusalLanFallbackCest
{
	const PROBE_FILE = 'e107_tests_lan_fallback.php';

	/** The fallback e107_web/js/plupload/upload.php set the pattern for. */
	const SIGNUP_FALLBACK = 'Test email refused.';

	const RATE_FALLBACK = 'Vote refused.';

	/** rate_table the forged ballot names, so no real content is disturbed. */
	const TABLE = 'ratelanfallback';

	/** @var string what a caller shows to prove it is this run of this case */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$this->secret = substr(hash('sha256', uniqid('', true).mt_rand()), 0, 32);
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'act=restore');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * The refusal is echoed straight into the page, so the constant is read at
	 * the moment the administrator is being told why no mail was sent.
	 */
	public function theSignupRefusalSurvivesAnUnloadedLanguageFile(AcceptanceTester $I)
	{
		$I->amOnPage($this->probeUrl(''));

		$I->seeInSource(self::SIGNUP_FALLBACK);
	}

	/**
	 * The refusal is handed to the message stack and rendered after a redirect,
	 * so the term is read while the member is being told the vote did not
	 * count.
	 */
	public function theRateRefusalSurvivesALanguageFileMissingTheTerm(AcceptanceTester $I)
	{
		$I->assertSame('PROBE_OK', $this->probe($I, 'act=strip'));
		$I->assertSame('TERM=0', $this->probe($I, 'act=state'),
			'the fixture must be able to leave the term undefined');

		$I->amOnPage('/rate.php?'.self::TABLE.'^1^/^10');

		$I->seeInSource(self::RATE_FALLBACK);
	}

	/**
	 * The probe rewrites a language file, so a caller that cannot show this
	 * run's secret has to get nothing at all. A probe left in the docroot by a
	 * run that died is otherwise an anonymous way to delete a shipped string.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=strip');

		$I->seeResponseCodeIs(403);
		$I->assertSame('TERM=1', $this->probe($I, 'act=state'),
			'a refused caller must not have stripped the language file');
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
\$lan = e_LANGUAGEDIR.e_LANGUAGE.'/lan_rate.php';
\$backup = \$lan.'.bak';

switch(\$act)
{
	case 'strip':
		if(!file_exists(\$backup))
		{
			copy(\$lan, \$backup);
		}

		\$src = preg_replace('/^.*RATELAN_REFUSED_TOKEN_MISSING.*\\n/m', '', file_get_contents(\$backup));
		unlink(\$lan);
		file_put_contents(\$lan, \$src);
		clearstatcache();
		echo 'PROBE_OK';
		break;

	case 'restore':
		if(file_exists(\$backup))
		{
			unlink(\$lan);
			rename(\$backup, \$lan);
			clearstatcache();
		}

		echo 'PROBE_OK';
		break;

	case 'state':
		e107::includeLan(\$lan);
		echo 'TERM='.(defined('RATELAN_REFUSED_TOKEN_MISSING') ? 1 : 0);
		break;

	default:
		require_once(e_HANDLER.'e_signup_class.php');

		\$suObj = new e_signup;
		\$suObj->run('test');
		break;
}
PHP;
	}
}
