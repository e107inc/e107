<?php

/**
 * cron.php over HTTP and from the command line.
 *
 * Both entry points share one token. Over HTTP an accepted token answers
 * 200 "OK" and runs the due tasks as a guest; a missing or wrong token answers
 * 403 with nothing the caller sent echoed back, is recorded for the admin
 * page, and mails the site owner at most once a day without an environment
 * dump (P6 item 2: cronScheduler::validateToken() used to mail print_a($_SERVER),
 * print_a($_ENV) and print_a($_GET), unthrottled, on every wrong-token
 * request). From the command line the tasks run as the first administrator
 * and a refused token exits 1.
 *
 * The mail assertions are driven through a probe that calls validateToken()
 * directly as well as through cron.php, so that neither entry point can turn
 * them into tests that measure nothing.
 */
class CronMisconfigMailCest
{
	const PROBE_FILE = 'e107_tests_p6_cron_probe.php';
	const ADDON_DIR = 'e107_plugins/e107_tests_cronprobe';

	/**
	 * @var string the wrong token every request in a burst presents.
	 *
	 * It is the marker as well as the token: the pre-fix mail echoed the
	 * submitted token back at "Sent from cron: ", so a marker travelling in some
	 * other parameter would measure the $_GET dump and leave the echo untested.
	 * It is the same on every request in a burst, so a deduplicating mailer
	 * still sees one message.
	 */
	private $marker;

	public function _before(AcceptanceTester $I)
	{
		$this->marker = 'P6CRONMARKER'.uniqid('', false);

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
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string probe output
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?'.$query);

		return $I->grabPageSource();
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return array|null the JSON the probe printed after PROBE_OK
	 */
	private function probeJson(AcceptanceTester $I, $query)
	{
		$out = $this->probe($I, $query);
		$json = trim((string) substr($out, strpos($out, "\n")));

		return json_decode($json, true);
	}

	/**
	 * One test, not four, because a rate limiter is stateful: a separate test
	 * asserting "the owner is told at least once" would run with the budget
	 * already spent by the test above it and fail for a reason that is not the
	 * defect. Every assertion here is made against the same burst.
	 */
	public function theMisconfigurationMailIsThrottledAndCarriesNoEnvironmentDump(AcceptanceTester $I)
	{
		$I->wantTo('stop an anonymous caller mailing the site owner the server environment on demand');

		$this->probe($I, 'act=clearmaillog');

		$burst = 6;
		for($i = 0; $i < $burst; $i++)
		{
			$out = $this->probe($I, 'act=validate&token='.$this->marker);
			$I->assertStringContainsString('VALIDATE=0', $out,
				'the wrong token must not validate (request '.($i + 1).')');
		}

		$log = $this->probe($I, 'act=maillog');

		$I->assertSame(array(), $this->mailProblems($log, $burst),
			"cron misconfiguration mail, after $burst anonymous requests with the wrong token:\n  - "
			.implode("\n  - ", $this->mailProblems($log, $burst))."\n");
	}

	/**
	 * @param string $log the mail log
	 * @param int $burst how many refused requests preceded it
	 * @return string[] every defect the log shows, so that fixing one cannot hide the others
	 */
	private function mailProblems($log, $burst)
	{
		$sent = substr_count($log, 'Mail-ID=');
		$problems = array();

		if($sent < 1)
		{
			$problems[] = 'no mail was sent at all: a misconfigured cron must still tell the site owner once';
		}

		if($sent > 1)
		{
			$problems[] = $burst.' identical failed requests produced '.$sent
				.' mails: the mail must be rate limited and deduplicated';
		}

		if(strpos($log, $this->marker) !== false)
		{
			$problems[] = 'the mail body echoes the token the caller submitted back to the site owner';
		}

		if(strpos($log, $this->cronPassword()) !== false)
		{
			$problems[] = 'the mail body carries the site\'s own cron password';
		}

		foreach(array('_SERVER', '_ENV', 'DOCUMENT_ROOT', 'DB_PASSWORD') as $dump)
		{
			if(strpos($log, $dump) !== false)
			{
				$problems[] = 'the mail body carries '.$dump;
			}
		}

		return $problems;
	}

	/**
	 * Positive control for the throttle: a correctly configured cron must still
	 * be accepted, and must not mail anybody.
	 */
	public function aCorrectTokenStillValidatesAndMailsNobody(AcceptanceTester $I)
	{
		$I->wantTo('keep a correctly configured cron working silently');

		$this->probe($I, 'act=clearmaillog');

		$out = $this->probe($I, 'act=validate&token='.$this->cronPassword());
		$I->assertStringContainsString('VALIDATE=1', $out, 'the configured token must validate');

		$log = $this->probe($I, 'act=maillog');
		$I->assertSame(0, substr_count($log, 'Mail-ID='),
			'a cron run with the right token must mail nobody');
	}

	public function cronPhpRunsOverHttpWithTheToken(AcceptanceTester $I)
	{
		$I->wantTo('run the scheduled tasks from a web request that carries the token');

		$this->probe($I, 'act=unlinkstamp');

		$I->amOnPage('/cron.php?token='.$this->cronPassword());
		$I->seeResponseCodeIs(200);
		$I->assertStringContainsString('text/plain', $I->grabHttpHeader('Content-Type'));
		$I->assertStringContainsString('no-store', $I->grabHttpHeader('Cache-Control'));
		$I->assertSame("OK\n", $I->grabResponseBody());

		$I->assertStringContainsString('STAMP=1', $this->probe($I, 'act=stamp'),
			'an accepted web request must reach the scheduler');

		$run = $this->probeJson($I, 'act=lastrun');
		$I->assertSame('http', $run['via']);
	}

	public function aWrongTokenOverHttpIs403AndStillMailsOnce(AcceptanceTester $I)
	{
		$I->wantTo('refuse a wrong token over HTTP without saying anything useful to the caller');

		$this->probe($I, 'act=clearmaillog');
		$this->probe($I, 'act=clearrefusals');
		$this->probe($I, 'act=unlinkstamp');

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

		$I->assertStringContainsString('STAMP=0', $this->probe($I, 'act=stamp'),
			'a refused request must not reach the scheduler');

		$log = $this->probe($I, 'act=maillog');
		$I->assertSame(array(), $this->mailProblems($log, $burst),
			"after $burst wrong-token web requests:\n  - ".implode("\n  - ", $this->mailProblems($log, $burst))."\n");

		$refusal = $this->probeJson($I, 'act=refusal');
		$I->assertNotNull($refusal, 'the refusals must be recorded for the admin page');
		$I->assertGreaterThanOrEqual($burst, $refusal['count']);
		$I->assertSame('wrong', $refusal['token']);
		$I->assertSame('http', $refusal['via']);
	}

	public function aMissingTokenOverHttpIs403AndMailsNobody(AcceptanceTester $I)
	{
		$I->wantTo('refuse a request with no token at all, silently');

		$this->probe($I, 'act=clearmaillog');
		$this->probe($I, 'act=clearrefusals');

		$I->amOnPage('/cron.php');
		$I->seeResponseCodeIs(403);
		$I->assertStringNotContainsString($this->cronPassword(), $I->grabResponseBody());

		$log = $this->probe($I, 'act=maillog');
		$I->assertSame(0, substr_count($log, 'Mail-ID='), 'a request without a token is noise, not a misconfiguration');

		$refusal = $this->probeJson($I, 'act=refusal');
		$I->assertSame('missing', $refusal['token']);
	}

	public function cronPhpStillRunsFromTheCommandLine(AcceptanceTester $I)
	{
		$I->wantTo('keep cron.php runnable from the command line');

		$out = $this->probe($I, 'act=cli&token='.$this->cronPassword());

		$I->assertStringContainsString('CLI_STATUS=0', $out,
			'a command line run must finish, and within its timeout');
		$I->assertStringContainsString('CLI_RAN=1', $out,
			'a command line run must reach the scheduler');
		$I->assertStringNotContainsString('OK', (string) substr($out, strpos($out, 'CLI_OUT:')),
			'a command line run does not print the HTTP answer');

		$run = $this->probeJson($I, 'act=lastrun');
		$I->assertSame('cli', $run['via']);
	}

	public function aWrongTokenFromTheCommandLineExitsNonZero(AcceptanceTester $I)
	{
		$I->wantTo('tell a crontab that its token was refused through the exit status');

		$this->probe($I, 'act=clearmaillog');
		$out = $this->probe($I, 'act=cli&token='.$this->marker);

		$I->assertStringContainsString('CLI_STATUS=1', $out);
		$I->assertStringContainsString('CLI_RAN=0', $out);
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

		$out = $this->probe($I, 'act=cli&env=1&token='.$this->cronPassword());

		$I->assertStringContainsString('CLI_RAN=1', $out, 'the request environment must still reach the scheduler');
		$I->assertStringContainsString('OK', (string) substr($out, strpos($out, 'CLI_OUT:')),
			'an invocation carrying REQUEST_METHOD answers as HTTP whatever the SAPI is');

		$run = $this->probeJson($I, 'act=lastrun');
		$I->assertSame('http', $run['via']);
	}

	public function cronJobsRunAsAGuestOverHttpAndAsTheAdministratorFromTheCommandLine(AcceptanceTester $I)
	{
		$I->wantTo('run tasks as a guest over HTTP and as the administrator from the command line');

		$I->writeAppFile(self::ADDON_DIR.'/e_cron.php', $this->addonSource());
		$this->probe($I, 'act=addcron');

		try
		{
			$this->waitForTheDueWindow();
			$this->probe($I, 'act=delrecord');
			$I->amOnPage('/cron.php?token='.$this->cronPassword());
			$I->seeResponseCodeIs(200);

			$http = $this->probeJson($I, 'act=readrecord');
			$I->assertNotNull($http, 'the probe task must have run over HTTP');
			$I->assertFalse($http['admin'], 'a web request must not run tasks as an administrator');
			$I->assertSame(0, $http['userid']);
			$I->assertFalse($http['cli']);

			$this->waitForTheDueWindow();
			$this->probe($I, 'act=delrecord');
			$out = $this->probe($I, 'act=cli&token='.$this->cronPassword());
			$I->assertStringContainsString('CLI_STATUS=0', $out);

			$cli = $this->probeJson($I, 'act=readrecord');
			$I->assertNotNull($cli, 'the probe task must have run from the command line');
			$I->assertTrue($cli['admin']);
			$I->assertSame(1, $cli['userid']);
			$I->assertTrue($cli['cli']);
		}
		finally
		{
			$this->probe($I, 'act=delcron');
			$I->deleteAppFile(self::ADDON_DIR.'/e_cron.php');
		}
	}

	/**
	 * A task on '* * * * *' is due for the first 45 seconds of each minute.
	 */
	private function waitForTheDueWindow()
	{
		$second = (int) date('s');

		if($second >= 38)
		{
			sleep(61 - $second);
		}
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
// Fixture for 0045_CronMisconfigMailCest. Removed again by the Cest.
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
// Fixture for 0045_CronMisconfigMailCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
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
		echo "PROBE_OK\n";
		break;

	case 'clearmaillog':
		@unlink(\$logFile);
		// The throttle records the last notice of each kind in e_CACHE, so a
		// burst that started with the record already written would send nothing
		// and read as a fix regression rather than as leftover state.
		foreach((array) glob(e_CACHE.'cronNotice_*.php') as \$notice)
		{
			@unlink(\$notice);
		}
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

	case 'addcron':
		e107::getDb()->delete('cron', "cron_function='e107_tests_cronprobe::record'");
		e107::getDb()->insert('cron', array(
			'cron_name' => 'e107_tests probe',
			'cron_category' => 'plugin',
			'cron_description' => 'Fixture for 0045_CronMisconfigMailCest',
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
