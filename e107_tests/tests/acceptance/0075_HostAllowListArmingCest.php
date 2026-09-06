<?php

/**
 * When the boot-time host check arms itself, and when it must not.
 *
 * e107 builds SITEURL from the request's Host header, so an installation that
 * has been told which hostnames are its own refuses a request arriving on any
 * other one. What decided whether that refusal was armed used to be the shape
 * of the siteurl preference: a value containing the letters "http". A site that
 * had listed its hostnames in trusted_hosts and left siteurl relative was
 * therefore never checked against the list it had just written, and neither was
 * one whose siteurl was protocol-relative.
 *
 * The other half is the half that has to keep working. An install that has been
 * told no hostname at all cannot be checked against anything, and arming the
 * refusal there would take every default installation offline on upgrade, since
 * install.php seeds siteurl as a path. Those cases are here as controls, and
 * they are why the arming condition asks whether the allow-list holds anything
 * usable rather than whether siteurl looks absolute.
 *
 * The second hostname is the app server's own address rather than an invented
 * one: the request has to reach this site carrying a Host it was not told
 * about, and a name nothing resolves never arrives at all.
 *
 * GHSA-w24r-4r8j-vqgc.
 */
class HostAllowListArmingCest
{
	const PROBE_FILE = 'e107_tests_host_arming_probe.php';

	const SNAPSHOT_FILE = 'e107_tests_host_arming_prefs.php';

	const REFUSAL = 'Site Configuration Issue Detected';

	/** @var string|null scheme://host of the site as the suite reaches it */
	private $ownBase;

	/** @var string|null scheme://address of the same site under a host it was not told about */
	private $otherBase;

	/** @var string the host the suite reaches this site on, without a port */
	private $ownHost;

	/** @var string|null the secret this run's probe answers a restore on */
	private $secret;

	public function _before(AcceptanceTester $I)
	{
		if($this->secret === null)
		{
			$this->secret = sha1(uniqid('host_arming', true) . mt_rand());
		}

		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());

		if($this->ownBase === null)
		{
			$I->amOnPage($this->probeUrl(''));
			$this->learnAddresses($I);
		}
		else
		{
			$I->amOnUrl($this->probeUrl($this->ownBase));
		}
	}

	public function _after(AcceptanceTester $I)
	{
		$this->restore($I);
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * The advisory's own case. Nothing is configured, so nothing can be checked,
	 * and the site answers a Host it was never told about with the page it
	 * always did. This is what every installation looks like the day it is
	 * installed, and a fix that reds this test is a fix that takes those sites
	 * offline.
	 */
	public function anUnconfiguredSiteStillAnswers(AcceptanceTester $I)
	{
		$this->configure($I, '/', '');

		$I->amOnUrl($this->probeUrl($this->otherBase));

		$I->seeResponseCodeIs(200);
		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * A site that listed its hostnames and left siteurl alone. The list was
	 * written to be enforced; before the fix it was read into an allow-list that
	 * nothing ever consulted.
	 */
	public function trustedHostsAloneArmTheCheck(AcceptanceTester $I)
	{
		$this->configure($I, '/', $this->ownHost);

		$I->amOnUrl($this->probeUrl($this->otherBase));

		$I->seeResponseCodeIs(503);
		$I->seeInSource(self::REFUSAL);
		$I->dontSeeInSource('PROBE_REACHED');
	}

	/**
	 * The same configuration, reached the way this site's own visitors reach it.
	 * A guard that cannot tell its own host from anyone else's is not a guard.
	 */
	public function trustedHostsStillAdmitOwnHost(AcceptanceTester $I)
	{
		$this->configure($I, '/', $this->ownHost);

		$I->amOnUrl($this->probeUrl($this->ownBase));

		$I->seeResponseCodeIs(200);
		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * A protocol-relative siteurl names a host as plainly as an absolute one
	 * does, and parse_url() reads it. Only the "http" substring test could not.
	 */
	public function aProtocolRelativeSiteurlArmsTheCheck(AcceptanceTester $I)
	{
		$this->configure($I, '//' . $this->ownHost . '/', '');

		$I->amOnUrl($this->probeUrl($this->otherBase));

		$I->seeResponseCodeIs(503);
		$I->seeInSource(self::REFUSAL);
		$I->dontSeeInSource('PROBE_REACHED');
	}

	/**
	 * The configuration the check was written for, refusing and admitting as it
	 * did before. Nothing about an absolute siteurl changes here.
	 */
	public function anAbsoluteSiteurlBehavesAsBefore(AcceptanceTester $I)
	{
		$this->configure($I, 'http://' . $this->ownHost . '/', '');

		$I->amOnUrl($this->probeUrl($this->otherBase));

		$I->seeResponseCodeIs(503);
		$I->seeInSource(self::REFUSAL);

		$I->amOnUrl($this->probeUrl($this->ownBase));

		$I->seeResponseCodeIs(200);
		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * A trusted_hosts pref holding nothing but blank entries names no host,
	 * however it came to be written. Arming on it would refuse every request the
	 * site could ever receive, the administrator's included, and the only way
	 * back would be out-of-band.
	 */
	public function blankTrustedHostEntriesDoNotArmTheCheck(AcceptanceTester $I)
	{
		$this->configure($I, '/', "\n   \nwww.\n");

		$I->amOnUrl($this->probeUrl($this->otherBase));

		$I->seeResponseCodeIs(200);
		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * A configuration this Cest is allowed to write is one the host check may
	 * refuse, and every request that reaches class2.php is a request the check
	 * decides, the one putting the preferences back included. Locking the site
	 * out and letting the probe undo it is what makes the restore in _after()
	 * worth having: without this case, the first regression in the arming
	 * condition would leave the preferences armed for the rest of the suite.
	 */
	public function aRefusedConfigurationIsRestoredOutOfBand(AcceptanceTester $I)
	{
		$this->configure($I, '/', 'named.example.invalid');

		$I->amOnUrl($this->probeUrl($this->ownBase));

		$I->seeResponseCodeIs(503);
		$I->seeInSource(self::REFUSAL);

		$this->restore($I);

		$I->amOnUrl($this->probeUrl($this->ownBase));

		$I->seeResponseCodeIs(200);
		$I->seeInSource('PROBE_REACHED');
	}

	/**
	 * The probe writes for the run that wrote it and for nobody else. A fixture
	 * that reconfigured a site for anyone who guessed a parameter name would be
	 * a worse thing to leave in a docroot than the defect it is here to cover.
	 */
	public function theProbeRefusesAWriteWithoutThisRunsSecret(AcceptanceTester $I)
	{
		$I->amOnUrl($this->probeUrl($this->ownBase,
			'host_arming_set=1&siteurl=%2F&trusted_hosts=named.example.invalid'));

		$I->seeResponseCodeIs(403);
		$I->dontSeeInSource('CONFIGURED');
	}

	/**
	 * Work out the two addresses every test below is driven through, from the
	 * request the suite has just made under its own configuration.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function learnAddresses(AcceptanceTester $I)
	{
		$scheme = $this->grab($I, 'SCHEME');
		$hostPort = $this->grab($I, 'HOST');

		$parts = explode(':', $hostPort, 2);
		$this->ownHost = $parts[0];
		$port = isset($parts[1]) ? ':' . $parts[1] : '';

		$address = gethostbyname($this->ownHost);
		if($address === $this->ownHost || strpos($address, ':') !== false)
		{
			// No IPv4 answer to work with, so fall back to the loopback name the
			// server also answers on. Both suite configurations reach the app on
			// the same machine as the runner.
			$address = '127.0.0.1';
		}

		$this->ownBase = $scheme . '://' . $hostPort;
		$this->otherBase = $scheme . '://' . $address . $port;

		if($this->otherBase === $this->ownBase)
		{
			throw new \RuntimeException(
				'Every case below needs a second hostname for this site, and ' . $hostPort
				. ' resolves to itself. Point the suite at a name rather than an address.');
		}
	}

	/**
	 * Store the two preferences the arming condition reads.
	 *
	 * @param AcceptanceTester $I
	 * @param string $siteurl
	 * @param string $trustedHosts one host per line; blank removes the pref
	 * @return void
	 */
	private function configure(AcceptanceTester $I, $siteurl, $trustedHosts)
	{
		$I->amOnUrl($this->writeUrl('host_arming_set=1&siteurl=' . urlencode($siteurl)
			. '&trusted_hosts=' . urlencode($trustedHosts)));
		$I->seeInSource('CONFIGURED');
	}

	/**
	 * The probe's address for a request that writes, carrying the probe's own secret as well.
	 *
	 * @param string $query the rest of the query string, without a leading separator
	 * @return string
	 */
	private function writeUrl($query)
	{
		return $this->probeUrl($this->ownBase,
			'host_arming_secret=' . urlencode($this->secret) . '&' . $query);
	}

	/**
	 * Put the preferences back as the probe first found them.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function restore(AcceptanceTester $I)
	{
		$I->amOnUrl($this->writeUrl('host_arming_restore=1'));
		$I->seeInSource('RESTORED');
	}

	/**
	 * The probe's address, carrying this run's guard secret.
	 *
	 * {@see \Helper\ProbeGuard} takes the secret from a header the suite sets
	 * on every request, and amOnUrl() reconfigures the module, which builds a
	 * client without it. Every case here needs an absolute address, so the
	 * secret travels in the query string.
	 *
	 * @param string $base scheme and authority, or '' for a site-relative address
	 * @param string $query further query string, without a leading separator
	 * @return string
	 */
	private function probeUrl($base, $query = '')
	{
		return $base . '/' . self::PROBE_FILE . '?' . \Helper\ProbeGuard::query()
			. ($query === '' ? '' : '&' . $query);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $field one of the [FIELD:value] pairs the probe publishes
	 * @return string
	 */
	private function grab(AcceptanceTester $I, $field)
	{
		if(!preg_match('/\[' . $field . ':([^\]]*)\]/', $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('The probe did not publish ' . $field);
		}

		return html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
	}

	private function probeSource()
	{
		return str_replace(
			array('{{SNAPSHOT}}', '{{SECRET}}'),
			array(self::SNAPSHOT_FILE, $this->secret),
			<<<'PHP'
<?php
// Fixture for 0075_HostAllowListArmingCest. Removed again in the Cest's _after().
// A GET carrying host_arming_set stores the two preferences the boot-time host
// check reads, so the request that follows is decided by the same production
// path a site owner's own configuration goes through. Reaching the echo at all
// is the assertion: the check runs inside class2.php and ends the request when
// it refuses.
// A GET carrying host_arming_restore puts the untouched preferences back
// without booting e107, because the check decides every request that reaches
// class2.php, and a restore the check can refuse cannot undo the configuration
// that made it refuse. Either write answers only to the secret this run baked
// into the file it wrote.
$snapshot = __DIR__.'/{{SNAPSHOT}}';
$offered = isset($_GET['host_arming_secret']) ? (string) $_GET['host_arming_secret'] : '';
if((isset($_GET['host_arming_set']) || isset($_GET['host_arming_restore'])) && !hash_equals('{{SECRET}}', $offered))
{
	header('HTTP/1.1 403 Forbidden');
	exit;
}
if(isset($_GET['host_arming_restore']))
{
	echo is_readable($snapshot) && host_arming_prefs(base64_decode(include $snapshot))
		? 'RESTORED' : 'RESTORE_FAILED';
	exit;
}
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
if(!file_exists($snapshot))
{
	e107::writeFileAtomic($snapshot, '<?php return '.var_export(base64_encode(host_arming_prefs()), true).';');
}
if(isset($_GET['host_arming_set']))
{
	$config = e107::getConfig('core');
	$config->set('siteurl', $_GET['siteurl']);
	if($_GET['trusted_hosts'] === '')
	{
		$config->remove('trusted_hosts');
	}
	else
	{
		// Stored verbatim rather than through normaliseTrustedHostList(), so a
		// Cest can write the entries only a hand-edited pref would hold.
		$config->set('trusted_hosts', preg_split('/\r\n|\r|\n/', $_GET['trusted_hosts']));
	}
	$config->save(false, true, false);
	echo 'CONFIGURED ';
}
$scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
echo 'PROBE_REACHED'
	.' [SCHEME:'.$scheme.']'
	.' [HOST:'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8').']';

/**
 * The stored SitePrefs row, read or written straight over the database.
 *
 * @param string|null $value bytes to store, or null to read the row
 * @return string|bool the row's bytes when reading, whether the write landed when writing
 */
function host_arming_prefs($value = null)
{
	mysqli_report(MYSQLI_REPORT_OFF);

	$config = include __DIR__.'/e107_config.php';
	$database = is_array($config) && isset($config['database']) ? $config['database'] : array(
		'server' => $mySQLserver, 'user' => $mySQLuser, 'password' => $mySQLpassword,
		'db' => $mySQLdefaultdb, 'prefix' => $mySQLprefix);

	$server = $database['server'];
	$port = null;
	if(substr_count($server, ':') === 1)
	{
		list($server, $port) = explode(':', $server, 2);
	}

	$link = $port === null
		? @new mysqli($server, $database['user'], $database['password'], $database['db'])
		: @new mysqli($server, $database['user'], $database['password'], $database['db'], (int) $port);
	if($link->connect_errno)
	{
		return false;
	}

	$name = 'SitePrefs';
	$statement = $value === null
		? $link->prepare('SELECT e107_value FROM `'.$database['prefix'].'core` WHERE e107_name = ?')
		: $link->prepare('UPDATE `'.$database['prefix'].'core` SET e107_value = ? WHERE e107_name = ?');
	if(!$statement)
	{
		return false;
	}

	if($value === null)
	{
		$statement->bind_param('s', $name);
		$statement->execute();
		$statement->bind_result($stored);
		$statement->fetch();
		$result = (string) $stored;
	}
	else
	{
		$statement->bind_param('ss', $value, $name);
		$result = $statement->execute();

		// e_pref serves this row out of the system cache for a day, so a row put
		// back while the cache stands is a row the next request does not read.
		$system = isset($config['paths']['system']) ? $config['paths']['system'] : 'e107_system/';
		foreach(glob(__DIR__.'/'.$system.'*/cache/content/S_Config_*.cache.php') ?: array() as $cached)
		{
			@unlink($cached);
		}
	}

	$statement->close();
	$link->close();

	return $result;
}
PHP
		);
	}
}
