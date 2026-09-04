<?php

/**
 * class2.php turning away an HTTP request to a CLI-only entry point.
 *
 * The refusal is shared: an entry script says it is command line only by
 * setting $_E107['cli'], and class2.php is the one place that decides whether
 * the caller may have it. e107_handlers/bounce_handler.php is the entry point
 * that ships with e107, so it is exercised here alongside a bare fixture that
 * does nothing but set the flag.
 *
 * Which layer turns the handler away depends on the host: e107_handlers ships
 * an .htaccess denying the directory, which a server reading it answers before
 * PHP is reached and a server configured with AllowOverride None ignores
 * entirely. Those cases therefore assert what holds either way, that the
 * handler did not run, and the bare fixture is what pins the bootstrap guard.
 *
 * Every request is made twice, once carrying a User-Agent header and once
 * without one: an HTTP request needs no User-Agent, so a guard reading that
 * header is walked past by omitting it. The requests are issued from a probe
 * inside the web server against $_SERVER['SERVER_ADDR'], because the Host
 * header the suite browses with names a port published outside the container.
 *
 * Both fixtures are docroot files, so both answer nobody who cannot show the
 * secret this run minted. The entry fixture is the reason the guard matters:
 * it asks e107 to bootstrap as the main administrator, which is what
 * e107::isCli() buys a caller in class2.php, so a copy left behind by a killed
 * run is an anonymous route to ADMIN on any docroot whose class2.php predates
 * this branch. It is also the one fixture whose guard stands ahead of the
 * bootstrap rather than behind it, because setting the flag before class2.php
 * loads is the whole of what it reproduces.
 *
 * @see e107_handlers/e107_class.php  e107::isCli()
 */
class CliBootstrapHttpRefusalCest
{
	const PROBE_FILE = 'e107_tests_cli_bootstrap_probe.php';
	const ENTRY_FILE = 'e107_tests_cli_only_entry.php';
	const CANARY_FILE = 'e107_tests_cli_only_canary.txt';
	const BOUNCE_HANDLER = 'e107_handlers/bounce_handler.php';
	const ENTRY_MARKER = 'CLI_ONLY_ENTRY_REACHED';

	/** Documentation-range address, so the fixture ban reaches no real host. */
	const BAN_IP = '192.0.2.107';

	/** @var string what a caller shows to prove it is this run of this case */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		$this->secret = substr(hash('sha256', uniqid('', true).mt_rand()), 0, 32);
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->writeAppFile(self::ENTRY_FILE, $this->entrySource());
	}

	public function _after(AcceptanceTester $I)
	{
		$this->probe($I, 'act=teardown');
		$I->deleteAppFile(self::PROBE_FILE);
		$I->deleteAppFile(self::ENTRY_FILE);
	}

	/**
	 * Runs first, and is the control the others are read against: it proves the
	 * probe's client reaches PHP and that an ordinary web request still boots.
	 */
	public function anOrdinaryRequestStillBootsTheSite(AcceptanceTester $I)
	{
		$I->wantTo('keep serving a request that never claimed to be a command line one');

		$response = $this->request($I, 'target=probe&ua=1');

		$I->assertStringContainsString('PROBE_REACHED', $response,
			'a request with no CLI flag must be answered by the site');
	}

	public function aCliOnlyEntryPointIsRefusedOverHttp(AcceptanceTester $I)
	{
		$I->wantTo('be turned away from a command line entry point I asked for in a browser');

		$response = $this->request($I, 'target=entry&ua=1');

		$I->assertStringNotContainsString(self::ENTRY_MARKER, $response,
			'a script that declared itself command line only must not run for a web request');
	}

	public function aCliOnlyEntryPointIsRefusedWithoutAUserAgentHeader(AcceptanceTester $I)
	{
		$I->wantTo('be turned away from the same entry point when I send no User-Agent');

		$response = $this->request($I, 'target=entry&ua=0');

		$I->assertStringNotContainsString(self::ENTRY_MARKER, $response,
			'omitting a header must not buy the caller the refusal');
	}

	public function theBounceHandlerIsRefusedOverHttp(AcceptanceTester $I)
	{
		$I->wantTo('stop an anonymous caller running the bounce handler over HTTP');

		$this->clearBounceLog($I);
		$this->request($I, 'target=bounce&ua=1', false);
		$this->seeTheBounceHandlerDidNotRun($I);
	}

	public function theBounceHandlerIsRefusedWithoutAUserAgentHeader(AcceptanceTester $I)
	{
		$I->wantTo('stop the same caller getting in by sending no User-Agent');

		$this->clearBounceLog($I);
		$this->request($I, 'target=bounce&ua=0', false);
		$this->seeTheBounceHandlerDidNotRun($I);
	}

	/**
	 * Both fixtures act, so a caller that cannot show this run's secret has to
	 * get nothing at all from either. The bootstrap probe empties the online
	 * table and every flood ban on the way to issuing a request, which is an
	 * anonymous way for a banned caller to lift its own ban; the entry fixture
	 * asks class2.php to bootstrap in command line mode, which is an anonymous
	 * way to reach ADMIN wherever that request is not refused.
	 *
	 * The same two side effects are then asked for with the secret, so that
	 * what the refusal withheld is read against what the fixtures do when
	 * they are allowed to act rather than against nothing.
	 */
	public function theProbeRefusesACallerThatCannotShowTheSecret(AcceptanceTester $I)
	{
		$I->wantTo('get nothing out of either fixture without the secret this run minted');

		$I->assertStringContainsString('BANS=1', $this->probe($I, 'act=seedban'),
			'the fixture ban has to be there for its survival to mean anything');
		$I->assertStringContainsString('CANARY=0', $this->probe($I, 'act=clearcanary'),
			'the canary has to start absent for its appearance to mean anything');

		$I->amOnPage('/'.self::PROBE_FILE.'?act=request&target=probe&ua=1');
		$I->seeResponseCodeIs(403);

		$I->amOnPage('/'.self::ENTRY_FILE);
		$I->seeResponseCodeIs(403);

		$I->assertStringContainsString('BANS=1', $this->probe($I, 'act=countban'),
			'a refused caller must not have emptied the flood bans');
		$I->assertStringContainsString('CANARY=0', $this->probe($I, 'act=readcanary'),
			'a refused caller must not have reached the command line entry point');

		$this->request($I, 'target=entry&ua=1');

		$I->assertStringContainsString('BANS=0', $this->probe($I, 'act=countban'),
			'the same request carrying the secret does empty the flood bans');
		$I->assertStringContainsString('CANARY=1', $this->probe($I, 'act=readcanary'),
			'the same request carrying the secret does reach the entry point');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query target= and ua= for the probe
	 * @param bool $served whether the web server has to hand the target to PHP
	 * @return string the raw response the probe's client read
	 */
	private function request(AcceptanceTester $I, $query, $served = true)
	{
		$out = $this->probe($I, 'act=request&'.$query);

		$I->assertStringNotContainsString('CONNECT_FAILED', $out,
			'the probe could not open a connection to the web server');

		if($served)
		{
			$I->assertSame(1, preg_match('#\nHTTP/1\.[01] 200 #', $out),
				'the target must be served for its refusal to mean anything');
		}

		return $out;
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
	 * @return string probe output
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage($this->probeUrl($query));
		$out = $I->grabPageSource();

		$I->assertStringContainsString('PROBE_OK', $out, 'the probe itself must answer');

		return $out;
	}

	private function clearBounceLog(AcceptanceTester $I)
	{
		$I->assertStringContainsString('BOUNCE_LOG=0', $this->probe($I, 'act=clearbounce'),
			'the bounce log has to start absent for its reappearance to mean anything');
	}

	private function seeTheBounceHandlerDidNotRun(AcceptanceTester $I)
	{
		$I->assertStringContainsString('BOUNCE_LOG=0', $this->probe($I, 'act=readbounce'),
			'the handler appends to the bounce log before it does anything else, '
			.'so a log written by that request means it ran');
	}

	/**
	 * @return string a docroot file that declares itself command line only
	 */
	private function entrySource()
	{
		$secret = $this->secret;
		$canary = self::CANARY_FILE;
		$marker = self::ENTRY_MARKER;

		return <<<PHP
<?php
if(!isset(\$_GET['probe']) || !hash_equals('$secret', \$_GET['probe']))
{
	header('HTTP/1.1 403 Forbidden', true, 403);
	echo 'Unauthorized access!';
	exit;
}

file_put_contents(__DIR__."/$canary", 'reached');
\$_E107['cli'] = true;
require_once(__DIR__."/class2.php");
echo "$marker";
PHP;
	}

	/**
	 * @return string a docroot file that answers ordinary web requests and
	 *                issues raw ones of its own
	 */
	private function probeSource()
	{
		$secret = $this->secret;
		$entry = self::ENTRY_FILE;
		$handler = self::BOUNCE_HANDLER;
		$probe = self::PROBE_FILE;
		$canary = self::CANARY_FILE;
		$banIp = self::BAN_IP;

		return <<<PHP
<?php
require_once(__DIR__."/class2.php");

if(!isset(\$_GET['probe']) || !hash_equals('$secret', \$_GET['probe']))
{
	header('HTTP/1.1 403 Forbidden', true, 403);
	echo 'Unauthorized access!';
	exit;
}

\$targets = array(
	'probe'  => '$probe?probe=$secret',
	'entry'  => '$entry?probe=$secret',
	'bounce' => '$handler',
);
\$log = e_LOG.'bounce.log';
\$canary = __DIR__."/$canary";
\$bans = "banlist_ip = '$banIp' AND banlist_bantype = -2";
\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';

switch(\$act)
{
	case 'clearbounce':
		@unlink(\$log);
		clearstatcache();
		echo "PROBE_OK BOUNCE_LOG=".(file_exists(\$log) ? 1 : 0)."\\n";
		break;

	case 'readbounce':
		clearstatcache();
		echo "PROBE_OK BOUNCE_LOG=".(file_exists(\$log) ? 1 : 0)."\\n";
		break;

	case 'seedban':
		e107::getDb()->delete('banlist', \$bans);
		e107::getDb()->insert('banlist', array('data' => array(
			'banlist_ip'         => '$banIp',
			'banlist_bantype'    => -2,
			'banlist_datestamp'  => time(),
			'banlist_banexpires' => 0,
			'banlist_admin'      => 0,
			'banlist_reason'     => 'e107 tests fixture',
			'banlist_notes'      => '',
		)));
		echo "PROBE_OK BANS=".e107::getDb()->count('banlist', '(*)', 'WHERE '.\$bans)."\\n";
		break;

	case 'countban':
		echo "PROBE_OK BANS=".e107::getDb()->count('banlist', '(*)', 'WHERE '.\$bans)."\\n";
		break;

	case 'clearcanary':
		@unlink(\$canary);
		clearstatcache();
		echo "PROBE_OK CANARY=".(file_exists(\$canary) ? 1 : 0)."\\n";
		break;

	case 'readcanary':
		clearstatcache();
		echo "PROBE_OK CANARY=".(file_exists(\$canary) ? 1 : 0)."\\n";
		break;

	case 'teardown':
		e107::getDb()->delete('banlist', \$bans);
		@unlink(\$canary);
		@unlink(\$log);
		echo "PROBE_OK\\n";
		break;

	case 'request':
		e107::getDb()->delete('online');
		e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

		\$base = rtrim(dirname(\$_SERVER['SCRIPT_NAME']), '/').'/';
		\$lines = array(
			'GET '.\$base.\$targets[\$_GET['target']].' HTTP/1.0',
			'Host: '.\$_SERVER['HTTP_HOST'],
			'Connection: close',
		);

		if(!empty(\$_GET['ua']))
		{
			\$lines[] = 'User-Agent: e107-tests-probe';
		}

		\$serverAddress = \$_SERVER['SERVER_ADDR'];
		\$serverAddressIsIpv6 = strpos(\$serverAddress, ':') !== false;
		\$socket = fsockopen(
			\$serverAddressIsIpv6 ? '['.\$serverAddress.']' : \$serverAddress,
			(int) \$_SERVER['SERVER_PORT'],
			\$errno,
			\$errstr,
			10
		);

		if(!\$socket)
		{
			echo "PROBE_OK CONNECT_FAILED=".\$errno." ".\$errstr."\\n";
			break;
		}

		fwrite(\$socket, implode("\\r\\n", \$lines)."\\r\\n\\r\\n");
		\$response = '';

		while(!feof(\$socket))
		{
			\$response .= fread(\$socket, 8192);
		}

		fclose(\$socket);
		echo "PROBE_OK\\n".\$response;
		break;

	default:
		echo "PROBE_OK PROBE_REACHED\\n";
}
PHP;
	}
}
