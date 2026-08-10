<?php

/**
 * P6 item 2. cron.php answers an unauthenticated request, and a request with
 * the wrong token makes it mail the site owner.
 *
 * cronScheduler::validateToken() (cron_class.php:1323-1353) builds that mail
 * out of print_a($_SERVER), print_a($_ENV) and print_a($_GET), and sends it
 * with no throttle and no deduplication. An anonymous caller therefore decides
 * how often the site owner is mailed, and each of those mails carries the web
 * server's environment, the site owner's own cron password and whatever the
 * caller put in the query string.
 *
 * The guard at cron.php:37 is the reason any of this is reachable over HTTP: it
 * names 'apache', which is the Apache 1 SAPI, and 'litespeed'. Every SAPI a
 * current site actually runs (apache2handler, fpm-fcgi, cgi-fcgi) is not on the
 * list, so the endpoint answers the web.
 *
 * The mail assertions are driven through a probe that calls validateToken()
 * directly rather than through cron.php, so that closing the SAPI guard does
 * not turn them into tests that measure nothing.
 */
class CronMisconfigMailCest
{
	const PROBE_FILE = 'e107_tests_p6_cron_probe.php';

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
		$sent = substr_count($log, 'Mail-ID=');

		// Every complaint is collected before anything is asserted, because
		// PHPUnit stops at the first failed assertion and each of these is a
		// separate defect. Fixing one of them leaves the list non-empty, so no
		// fix can appear to cover the others.
		$problems = array();

		// Positive control. The owner still has to be told, or "send nothing,
		// ever" would satisfy everything else here.
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

		if(strpos($log, '_SERVER') !== false)
		{
			$problems[] = 'the mail body dumps $_SERVER';
		}

		if(strpos($log, '_ENV') !== false)
		{
			$problems[] = 'the mail body dumps $_ENV';
		}

		if(strpos($log, 'DOCUMENT_ROOT') !== false)
		{
			$problems[] = 'the mail body carries the server environment';
		}

		// Not container trivia. The harness passes the database credentials in
		// as environment variables, which is how a great many php-fpm and
		// container deployments are configured, and $_SERVER carries them
		// straight into the mail.
		if(strpos($log, 'DB_PASSWORD') !== false)
		{
			$problems[] = 'the mail body carries the database credentials';
		}

		$I->assertSame(array(), $problems,
			"cron misconfiguration mail, after $burst anonymous requests with the wrong token:\n  - "
			.implode("\n  - ", $problems)."\n");
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

	/**
	 * The SAPI guard. Every request in this suite arrives at Apache, so if the
	 * endpoint answers here it answers the internet.
	 */
	public function cronPhpIsNotReachableOverHttp(AcceptanceTester $I)
	{
		$I->wantTo('refuse cron.php when it is requested over HTTP');

		foreach(array('', '?token='.$this->marker, '?token='.$this->cronPassword()) as $query)
		{
			$I->amOnPage('/cron.php'.$query);
			$I->seeInSource('Access Denied');
		}
	}

	/**
	 * Positive control for the guard: refusing every SAPI would satisfy the test
	 * above and leave every site's scheduled tasks dead.
	 */
	public function cronPhpStillRunsFromTheCommandLine(AcceptanceTester $I)
	{
		$I->wantTo('keep cron.php runnable from the command line');

		$out = $this->probe($I, 'act=cli&token='.$this->cronPassword());

		$I->assertStringNotContainsString('Access Denied', $out,
			'the command line must not be turned away');
		$I->assertStringContainsString('CLI_STATUS=0', $out,
			'a command line run must finish, and within its timeout');
		$I->assertStringContainsString('CLI_RAN=1', $out,
			'a command line run must reach the scheduler');
	}

	/**
	 * The guard has to read the request, not PHP_SAPI: on a great deal of shared
	 * hosting the command line binary is a CGI build, and a crontab line calling
	 * it would be turned away by a guard that went by name. The container has
	 * only a cli binary, so the two shapes are told apart here the way the CGI
	 * SAPI itself tells them apart - by the request variables a web server puts
	 * in the environment and a shell does not.
	 */
	public function cronPhpRefusesAShellRunThatCarriesARequest(AcceptanceTester $I)
	{
		$I->wantTo('refuse cron.php whenever the environment says a web server sent the request');

		$out = $this->probe($I, 'act=cli&env=1&token='.$this->cronPassword());

		$I->assertStringContainsString('Access Denied', $out,
			'an invocation carrying REQUEST_METHOD must be refused whatever the SAPI is');
		$I->assertStringContainsString('CLI_RAN=0', $out,
			'a refused invocation must not reach the scheduler');
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
	private function probeSource()
	{
		$pwd = $this->cronPassword();

		return <<<PHP
<?php
// Fixture for 0034_CronMisconfigMailCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

// Every request in the container arrives from the bridge address, so a Cest
// that makes more than a handful of them bans itself part way through.
e107::getDb()->delete('online');
e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';
\$config = e107::getConfig('core');
\$logFile = e_LOG.'mailoutlog.log';

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
		require_once(e_HANDLER.'cron_class.php');
		\$cron = new cronScheduler();
		echo "PROBE_OK VALIDATE=".(\$cron->validateToken() ? 1 : 0)."\n";
		break;

	case 'cli':
		// The positive control for the SAPI guard. cronLastLoad.php is written
		// by cronScheduler::run() once the token is accepted, so its
		// reappearance is proof the command line reached the scheduler.
		\$stamp = e_CACHE.'cronLastLoad.php';
		@unlink(\$stamp);
		\$token = isset(\$_GET['token']) ? \$_GET['token'] : '';
		// Bounded: the child runs the site's whole scheduler, and a job that
		// blocks would otherwise hold this request open until the web server
		// gives up, which reads as a hung suite rather than a failure.
		// env=1 puts a web server's request variables in the child's
		// environment, which is what the CGI SAPI reads a request out of.
		\$env = empty(\$_GET['env']) ? '' : 'REQUEST_METHOD=GET SERVER_PROTOCOL=HTTP/1.1 HTTP_HOST=example.com ';
		\$cmd = 'cd '.escapeshellarg(e_ROOT).' && '.\$env.'timeout 30 php cron.php token='.escapeshellarg(\$token).' 2>&1';
		\$out = array();
		\$status = 1;
		exec(\$cmd, \$out, \$status);
		echo "PROBE_OK CLI_RAN=".(file_exists(\$stamp) ? 1 : 0)." CLI_STATUS=".\$status."\n";
		echo "CLI_OUT:".implode("\n", \$out)."\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;
	}
}
