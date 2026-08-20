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

	const REFUSAL = 'Site Configuration Issue Detected';

	/** @var string|null scheme://host of the site as the suite reaches it */
	private $ownBase;

	/** @var string|null scheme://address of the same site under a host it was not told about */
	private $otherBase;

	/** @var string the host the suite reaches this site on, without a port */
	private $ownHost;

	/** @var string|null the siteurl this site had before the Cest started */
	private $originalSiteurl;

	/** @var string|null the trusted_hosts this site had, one host per line */
	private $originalTrustedHosts;

	public function _before(AcceptanceTester $I)
	{
		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());

		if($this->ownBase === null)
		{
			$I->amOnPage('/' . self::PROBE_FILE);
			$this->learnAddresses($I);
		}
		else
		{
			$I->amOnUrl($this->ownBase . '/' . self::PROBE_FILE);
		}

		if($this->originalSiteurl === null)
		{
			$this->originalSiteurl = $this->grab($I, 'SITEURL_PREF');
			$this->originalTrustedHosts = $this->grab($I, 'TRUSTED_HOSTS');
		}
	}

	public function _after(AcceptanceTester $I)
	{
		$this->configure($I, $this->originalSiteurl, $this->originalTrustedHosts);
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

		$I->amOnUrl($this->otherBase . '/' . self::PROBE_FILE);

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

		$I->amOnUrl($this->otherBase . '/' . self::PROBE_FILE);

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

		$I->amOnUrl($this->ownBase . '/' . self::PROBE_FILE);

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

		$I->amOnUrl($this->otherBase . '/' . self::PROBE_FILE);

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

		$I->amOnUrl($this->otherBase . '/' . self::PROBE_FILE);

		$I->seeResponseCodeIs(503);
		$I->seeInSource(self::REFUSAL);

		$I->amOnUrl($this->ownBase . '/' . self::PROBE_FILE);

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

		$I->amOnUrl($this->otherBase . '/' . self::PROBE_FILE);

		$I->seeResponseCodeIs(200);
		$I->seeInSource('PROBE_REACHED');
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
		$I->amOnUrl($this->ownBase . '/' . self::PROBE_FILE
			. '?host_arming_set=1&siteurl=' . urlencode($siteurl)
			. '&trusted_hosts=' . urlencode($trustedHosts));
		$I->seeInSource('CONFIGURED');
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
		return <<<'PHP'
<?php
// Fixture for 0075_HostAllowListArmingCest. Removed again in the Cest's _after().
// A GET carrying host_arming_set stores the two preferences the boot-time host
// check reads, so the request that follows is decided by the same production
// path a site owner's own configuration goes through. Reaching the echo at all
// is the assertion: the check runs inside class2.php and ends the request when
// it refuses.
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
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
$trusted = e107::getPref('trusted_hosts');
echo 'PROBE_REACHED'
	.' [SCHEME:'.$scheme.']'
	.' [HOST:'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_QUOTES, 'UTF-8').']'
	.' [SITEURL_PREF:'.htmlspecialchars((string) e107::getPref('siteurl'), ENT_QUOTES, 'UTF-8').']'
	.' [TRUSTED_HOSTS:'.htmlspecialchars(implode("\n", (array) $trusted), ENT_QUOTES, 'UTF-8').']';
PHP;
	}
}
