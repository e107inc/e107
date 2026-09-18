<?php

/**
 * cron.php over HTTP and from the command line, and what a refusal reaches.
 *
 * Both entry points share one token. Over HTTP an accepted token answers
 * 200 "OK" and runs the due tasks as a guest; a missing or wrong token answers
 * 403 with nothing the caller sent echoed back and is recorded for the admin
 * area. From the command line the tasks run as the first administrator and a
 * refused token exits 1.
 *
 * Nothing a refused caller does mails anybody. The site owner used to be
 * mailed once a day about a wrong token, which handed any stranger the
 * decision of when the owner is mailed; the record is read on Schedule Tasks
 * and on the dashboard instead, and the administrator can dismiss it until the
 * token is next regenerated.
 *
 * The validation assertions are driven through a probe that calls
 * validateToken() directly as well as through cron.php, so that neither entry
 * point can turn them into tests that measure nothing.
 */
class CronRefusalCest
{
	const PROBE_FILE = 'e107_tests_p6_cron_probe.php';
	const ADDON_DIR = 'e107_plugins/e107_tests_cronprobe';

	const SCHEDULE_TASKS = '/e107_admin/cron.php';
	const SETUP_PAGE = '/e107_admin/cron.php?mode=main&action=setup';
	const DASHBOARD = '/e107_admin/admin.php';

	/** The sentence LAN_CRON_REFUSED_SUMMARY renders. */
	const REFUSED = 'request(s) to cron.php have been refused';

	/**
	 * @var string the wrong token every request in a burst presents; the same on
	 * every request, so a coalescing record counts one run.
	 */
	private $marker;

	public function _before(AcceptanceTester $I)
	{
		$this->marker = 'P6CRONMARKER'.uniqid('', false);

		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
		$I->amOnProbe('act=setup');
		$I->seeInSource('PROBE_OK');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnProbe('act=teardown');
	}

	public function aCorrectTokenValidatesAndMailsNobody(AcceptanceTester $I)
	{
		$I->wantTo('keep a correctly configured cron working silently');

		$I->probe('act=clearmaillog');

		$out = $I->grabProbe('act=validate&token='.$this->cronPassword());
		$I->assertStringContainsString('VALIDATE=1', $out, 'the configured token must validate');

		$I->assertSame(0, substr_count($I->grabProbe('act=maillog'), 'Mail-ID='),
			'a cron run with the right token must mail nobody');
	}

	public function cronPhpRunsOverHttpWithTheToken(AcceptanceTester $I)
	{
		$I->wantTo('run the scheduled tasks from a web request that carries the token');

		$I->probe('act=unlinkstamp');

		$I->amOnPage('/cron.php?token='.$this->cronPassword());
		$I->seeResponseCodeIs(200);
		$I->assertStringContainsString('text/plain', $I->grabHttpHeader('Content-Type'));
		$I->assertStringContainsString('no-store', $I->grabHttpHeader('Cache-Control'));
		$I->assertSame("OK\n", $I->grabResponseBody());

		$I->assertStringContainsString('STAMP=1', $I->grabProbe('act=stamp'),
			'an accepted web request must reach the scheduler');

		$run = $I->grabProbeJson('act=lastrun');
		$I->assertSame('http', $run['via']);
	}

	/**
	 * One burst, not one request per assertion, because the record coalesces:
	 * the count it reports is only meaningful against the burst that produced it.
	 */
	public function aWrongTokenOverHttpIs403AndMailsNobody(AcceptanceTester $I)
	{
		$I->wantTo('refuse a wrong token over HTTP without saying anything useful to the caller or mailing anyone');

		$I->probe('act=clearmaillog');
		$I->probe('act=clearrefusals');
		$I->probe('act=unlinkstamp');

		$burst = 6;
		for($i = 0; $i < $burst; $i++)
		{
			$I->amOnPage('/cron.php?token='.$this->marker);
			$I->seeResponseCodeIs(403);
			$body = $I->grabResponseBody();
			$I->assertStringNotContainsString($this->marker, $body, 'the refusal must not echo the token sent');
			$I->assertStringNotContainsString($this->cronPassword(), $body, 'the refusal must not leak the real token');
			$I->assertStringNotContainsString('OK', $body);
		}

		$I->assertStringContainsString('STAMP=0', $I->grabProbe('act=stamp'),
			'a refused request must not reach the scheduler');

		$I->assertSame(0, substr_count($I->grabProbe('act=maillog'), 'Mail-ID='),
			"$burst wrong-token web requests must mail nobody");

		$refusal = $I->grabProbeJson('act=refusal');
		$I->assertNotNull($refusal, 'the refusals must be recorded for the admin area');
		$I->assertGreaterThanOrEqual($burst, $refusal['count']);
		$I->assertSame('wrong', $refusal['token']);
		$I->assertSame('http', $refusal['via']);
	}

	public function aMissingTokenOverHttpIs403AndMailsNobody(AcceptanceTester $I)
	{
		$I->wantTo('refuse a request with no token at all, silently');

		$I->probe('act=clearmaillog');
		$I->probe('act=clearrefusals');

		$I->amOnPage('/cron.php');
		$I->seeResponseCodeIs(403);
		$I->assertStringNotContainsString($this->cronPassword(), $I->grabResponseBody());

		$I->assertSame(0, substr_count($I->grabProbe('act=maillog'), 'Mail-ID='),
			'a request without a token is noise');

		$refusal = $I->grabProbeJson('act=refusal');
		$I->assertSame('missing', $refusal['token']);
	}

	public function cronPhpStillRunsFromTheCommandLine(AcceptanceTester $I)
	{
		$I->wantTo('keep cron.php runnable from the command line');

		$out = $I->grabProbe('act=cli&token='.$this->cronPassword());

		$I->assertStringContainsString('CLI_STATUS=0', $out,
			'a command line run must finish, and within its timeout');
		$I->assertStringContainsString('CLI_RAN=1', $out,
			'a command line run must reach the scheduler');
		$I->assertStringNotContainsString('OK', (string) substr($out, strpos($out, 'CLI_OUT:')),
			'a command line run does not print the HTTP answer');

		$run = $I->grabProbeJson('act=lastrun');
		$I->assertSame('cli', $run['via']);
	}

	public function aWrongTokenFromTheCommandLineExitsNonZeroAndMailsNobody(AcceptanceTester $I)
	{
		$I->wantTo('tell a crontab that its token was refused through the exit status alone');

		$I->probe('act=clearmaillog');
		$out = $I->grabProbe('act=cli&token='.$this->marker);

		$I->assertStringContainsString('CLI_STATUS=1', $out);
		$I->assertStringContainsString('CLI_RAN=0', $out);
		$I->assertSame(0, substr_count($I->grabProbe('act=maillog'), 'Mail-ID='));
	}

	/**
	 * The split has to read the request, not PHP_SAPI: on a great deal of shared
	 * hosting the command line binary is a CGI build, and a crontab line calling
	 * it would otherwise be answered as a web request. The container has only a
	 * cli binary, so the two shapes are told apart here the way the CGI SAPI
	 * itself tells them apart, by the request variables a web server puts in
	 * the environment and a shell does not.
	 */
	public function aShellRunThatCarriesARequestAnswersAsHttp(AcceptanceTester $I)
	{
		$I->wantTo('answer as a web request whenever the environment says a web server sent it');

		$out = $I->grabProbe('act=cli&env=1&token='.$this->cronPassword());

		$I->assertStringContainsString('CLI_RAN=1', $out, 'the request environment must still reach the scheduler');
		$I->assertStringContainsString('OK', (string) substr($out, strpos($out, 'CLI_OUT:')),
			'an invocation carrying REQUEST_METHOD answers as HTTP whatever the SAPI is');

		$run = $I->grabProbeJson('act=lastrun');
		$I->assertSame('http', $run['via']);
	}

	public function cronJobsRunAsAGuestOverHttpAndAsTheAdministratorFromTheCommandLine(AcceptanceTester $I)
	{
		$I->wantTo('run tasks as a guest over HTTP and as the administrator from the command line');

		$I->writeAppFile(self::ADDON_DIR.'/e_cron.php', $this->addonSource());
		$I->probe('act=addcron');

		try
		{
			$this->waitForTheDueWindow();
			$I->probe('act=delrecord');
			$I->amOnPage('/cron.php?token='.$this->cronPassword());
			$I->seeResponseCodeIs(200);

			$http = $I->grabProbeJson('act=readrecord');
			$I->assertNotNull($http, 'the probe task must have run over HTTP');
			$I->assertFalse($http['admin'], 'a web request must not run tasks as an administrator');
			$I->assertSame(0, $http['userid']);
			$I->assertFalse($http['cli']);

			$this->waitForTheDueWindow();
			$I->probe('act=delrecord');
			$out = $I->grabProbe('act=cli&token='.$this->cronPassword());
			$I->assertStringContainsString('CLI_STATUS=0', $out);

			$cli = $I->grabProbeJson('act=readrecord');
			$I->assertNotNull($cli, 'the probe task must have run from the command line');
			$I->assertTrue($cli['admin']);
			$I->assertSame(1, $cli['userid']);
			$I->assertTrue($cli['cli']);
		}
		finally
		{
			$I->probe('act=delcron');
			$I->deleteAppFile(self::ADDON_DIR.'/e_cron.php');
		}
	}

	/**
	 * Last in the file because it logs in: the cases above are a stranger's.
	 */
	public function aRefusalIsReportedInTheAdminAreaUntilDismissedOrTheTokenChanges(AcceptanceTester $I)
	{
		$I->wantTo('learn about refused requests in the admin area, and stop being told once I have said so');

		$I->probe('act=clearrefusals');
		$I->probe('act=undismiss');
		$I->probe('act=unlinkstamp');

		$I->amOnPage('/cron.php?token='.$this->marker);
		$I->seeResponseCodeIs(403);

		$I->loginAsAdmin();

		$I->amOnPage(self::SCHEDULE_TASKS);
		$I->see(self::REFUSED);
		$I->see('They carried a token that does not match.');

		$I->amOnPage(self::DASHBOARD);
		$I->seeInSource('id="admin-notifications"');
		$I->see(self::REFUSED);

		$I->amOnPage($this->publishedLink($I, self::SCHEDULE_TASKS,
			'#cron\.php\?mode=main&amp;action=list&amp;dismiss=cron-refused&amp;e-token=[^\'"]+#'));

		$state = $I->grabProbeJson('act=state');
		$I->assertNotNull($state['refusal'], 'the refusal record must survive being dismissed: '.json_encode($state));
		$I->assertTrue($state['suppressed'], 'the dismissal must be recorded: '.json_encode($state));

		$I->amOnPage(self::SCHEDULE_TASKS);
		$page = $I->grabPageSource();
		$after = $I->grabProbeJson('act=state');
		$I->assertStringNotContainsString(self::REFUSED, $page,
			'dismissed, so Schedule Tasks must not warn; state after loading it: '.json_encode($after));

		$I->amOnPage('/cron.php?token='.$this->marker);
		$I->seeResponseCodeIs(403);

		$I->amOnPage(self::SCHEDULE_TASKS);
		$I->dontSee(self::REFUSED);
		$I->amOnPage(self::DASHBOARD);
		$I->dontSee(self::REFUSED);

		$I->amOnPage(self::SETUP_PAGE);
		$I->submitForm('#cron-token', array('generate_pwd' => 1));
		$I->see('A new cron token has been generated.');

		$I->amOnPage('/cron.php?token='.$this->marker);
		$I->seeResponseCodeIs(403);

		$I->amOnPage(self::SCHEDULE_TASKS);
		$I->see(self::REFUSED);
		$I->amOnPage(self::DASHBOARD);
		$I->see(self::REFUSED);
	}

	/**
	 * Follow the link the page publishes rather than a URL of the test's own.
	 *
	 * @param AcceptanceTester $I
	 * @param string $page
	 * @param string $pattern
	 * @return string path to request
	 */
	private function publishedLink(AcceptanceTester $I, $page, $pattern)
	{
		$I->amOnPage($page);

		$matches = array();
		if(!preg_match($pattern, $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException($page.' published no link matching '.$pattern);
		}

		return '/e107_admin/'.str_replace('&amp;', '&', $matches[0]);
	}

	/**
	 * A task on '* * * * *' is due for the first 45 seconds of each minute; the three requests that follow need to land inside it.
	 */
	private function waitForTheDueWindow()
	{
		\Test\Poll::until(function ()
		{
			return (int) date('s') < 30;
		}, 60);
	}

	/**
	 * @return string
	 */
	private function cronPassword()
	{
		return 'p6cronpassword';
	}

	/**
	 * @return string
	 */
	private function addonSource()
	{
		return <<<'PHP'
<?php
// Fixture for CronRefusalCest. Removed again by the Cest.
if(!defined('e107_INIT')) { exit; }

class e107_tests_cronprobe_cron
{
	public function record()
	{
		file_put_contents(e_CACHE.'e107_tests_cronprobe.json', json_encode(array(
			'admin'  => defined('ADMIN') ? (bool) ADMIN : null,
			'userid' => defined('USERID') ? (int) USERID : null,
			'classes' => defined('USERCLASS_LIST') ? USERCLASS_LIST : null,
			'sapi'   => PHP_SAPI,
			'cli'    => e107::isCli(),
		)));

		return true;
	}
}
PHP;
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$pwd = $this->cronPassword();

		return <<<PHP
<?php
// Fixture for CronRefusalCest.
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
require_once(e_HANDLER.'cron_class.php');
header('Content-Type: text/plain');

// Every request in the container arrives from the bridge address, so a Cest
// that makes more than a handful of them bans itself part way through.
e107::getDb()->delete('online');
e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';
\$config = e107::getConfig('core');
\$logFile = e_LOG.'mailoutlog.log';
\$stamp = e_CACHE.'cronLastLoad.php';
\$record = e_CACHE.'e107_tests_cronprobe.json';

switch(\$act)
{
	case 'setup':
		// e107 serialises the whole preference array itself, so this has to go
		// through e_pref rather than an UPDATE.
		\$config->set('e107_tests_p6_mail_backup', \$config->get('mail_log_options', ''));
		\$config->set('e107_tests_p6_cron_backup', \$config->get('e_cron_pwd', ''));
		// 1 is dry run: log it, do not send it, and let the caller believe it
		// went. 2 would attempt a real send, and there is no sendmail here.
		\$config->set('mail_log_options', '1,1');
		\$config->set('e_cron_pwd', '$pwd');
		\$config->save(false, true, false);
		echo "PROBE_OK sapi=".PHP_SAPI." log=".\$logFile."\n";
		break;

	case 'teardown':
		\$config->set('mail_log_options', \$config->get('e107_tests_p6_mail_backup', ''));
		\$config->set('e_cron_pwd', \$config->get('e107_tests_p6_cron_backup', ''));
		\$config->remove('e107_tests_p6_mail_backup');
		\$config->remove('e107_tests_p6_cron_backup');
		\$config->save(false, true, false);
		@unlink(\$logFile);
		@unlink(\$record);
		cronScheduler::clearRefusals();
		(new e107\\Admin\\NoticeSuppression(e107::getConfig(), e107::getLog(), 0))->release(e107\\Admin\\Notices::CRON_REFUSED);
		echo "PROBE_OK\n";
		break;

	case 'clearmaillog':
		@unlink(\$logFile);
		echo "PROBE_OK\n";
		break;

	case 'maillog':
		echo "PROBE_OK\n";
		echo is_readable(\$logFile) ? file_get_contents(\$logFile) : '';
		break;

	case 'validate':
		\$cron = new cronScheduler();
		echo "PROBE_OK VALIDATE=".(\$cron->validateToken() ? 1 : 0)."\n";
		break;

	case 'unlinkstamp':
		@unlink(\$stamp);
		@unlink(e_CACHE.'cronLastRun.php');
		echo "PROBE_OK\n";
		break;

	case 'stamp':
		clearstatcache();
		echo "PROBE_OK STAMP=".(file_exists(\$stamp) ? 1 : 0)."\n";
		break;

	case 'lastrun':
		echo "PROBE_OK\n".json_encode(cronScheduler::lastRun())."\n";
		break;

	case 'refusal':
		echo "PROBE_OK\n".json_encode(cronScheduler::lastRefusal())."\n";
		break;

	case 'clearrefusals':
		cronScheduler::clearRefusals();
		echo "PROBE_OK\n";
		break;

	case 'undismiss':
		(new e107\\Admin\\NoticeSuppression(e107::getConfig(), e107::getLog(), 0))->release(e107\\Admin\\Notices::CRON_REFUSED);
		echo "PROBE_OK\n";
		break;

	case 'state':
		\$suppression = new e107\\Admin\\NoticeSuppression(e107::getConfig(), e107::getLog(), 0);
		\$fingerprint = sha1((string) e107::getPref('e_cron_pwd'));
		echo "PROBE_OK\n".json_encode(array(
			'reported'    => cronScheduler::refusalNotice()->toReport() !== null,
			'suppressed'  => \$suppression->isSuppressed(e107\\Admin\\Notices::CRON_REFUSED, \$fingerprint),
			'fingerprint' => substr(\$fingerprint, 0, 12),
			'refusal'     => cronScheduler::lastRefusal(),
			'lastrun'     => cronScheduler::lastRun(),
			'records'     => e107::getConfig()->get(e107\\Admin\\NoticeSuppression::PREF),
		))."\n";
		break;

	case 'addcron':
		e107::getDb()->delete('cron', "cron_function='e107_tests_cronprobe::record'");
		e107::getDb()->insert('cron', array(
			'cron_name' => 'e107_tests probe',
			'cron_category' => 'plugin',
			'cron_description' => 'Fixture for CronRefusalCest',
			'cron_function' => 'e107_tests_cronprobe::record',
			'cron_tab' => '* * * * *',
			'cron_active' => 1,
		));
		echo "PROBE_OK\n";
		break;

	case 'delcron':
		e107::getDb()->delete('cron', "cron_function='e107_tests_cronprobe::record'");
		@unlink(\$record);
		echo "PROBE_OK\n";
		break;

	case 'delrecord':
		@unlink(\$record);
		echo "PROBE_OK\n";
		break;

	case 'readrecord':
		clearstatcache();
		echo "PROBE_OK\n".(is_readable(\$record) ? file_get_contents(\$record) : 'null')."\n";
		break;

	case 'cli':
		// The positive control for the mode split. cronLastLoad.php is written
		// by cronScheduler::run() once the token is accepted, so its
		// reappearance is proof the command line reached the scheduler.
		@unlink(\$stamp);
		\$token = isset(\$_GET['token']) ? \$_GET['token'] : '';
		// Bounded: the child runs the site's whole scheduler, and a job that
		// blocks would otherwise hold this request open until the web server
		// gives up, which reads as a hung suite rather than a failure.
		// env=1 puts a web server's request variables in the child's
		// environment, which is what the CGI SAPI reads a request out of.
		\$env = empty(\$_GET['env']) ? '' : 'REQUEST_METHOD=GET SERVER_PROTOCOL=HTTP/1.1 HTTP_HOST='.escapeshellarg(\$_SERVER['HTTP_HOST']).' ';
		\$cmd = 'cd '.escapeshellarg(e_ROOT).' && '.\$env.'timeout 30 php cron.php token='.escapeshellarg(\$token).' 2>&1';
		\$out = array();
		\$status = 1;
		exec(\$cmd, \$out, \$status);
		clearstatcache();
		echo "PROBE_OK CLI_RAN=".(file_exists(\$stamp) ? 1 : 0)." CLI_STATUS=".\$status."\n";
		echo "CLI_OUT:".implode("\n", \$out)."\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;
	}
}
