<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2024 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * Covers e107::isAllowedHost(), the private helper that decides whether
 * `$_SERVER['HTTP_HOST']` is acceptable given the configured siteurl host,
 * plus the trusted_hosts admin-form save path it composes with.
 *
 * Backs the Host-Header Injection killswitch in e107::set_urls_deferred()
 * (GHSA-7pmw-jwvr-cq2x).
 */
class e107HostValidationTest extends \Codeception\Test\Unit
{
	/** @var e107 */
	private $e107;

	/** @var ReflectionMethod */
	private $isAllowedHost;

	/** @var ReflectionMethod */
	private $resolveHttpHost;

	protected function _before()
	{
		try
		{
			$this->e107 = e107::getInstance();
		}
		catch(Exception $e)
		{
			self::fail("Couldn't load e107 object");
		}

		$reflection = new ReflectionClass($this->e107);
		$this->isAllowedHost = $reflection->getMethod('isAllowedHost');
		$this->isAllowedHost->setAccessible(true);
		$this->resolveHttpHost = $reflection->getMethod('resolveHttpHost');
		$this->resolveHttpHost->setAccessible(true);
	}

	/**
	 * @dataProvider hostMatchProvider
	 */
	public function testIsAllowedHost($allowedHosts, $httpHost, $expected, $scenario)
	{
		$result = $this->isAllowedHost->invoke($this->e107, $allowedHosts, $httpHost);
		$this->assertSame($expected, $result, "Failed scenario: $scenario");
	}

	/**
	 * @dataProvider trustedHostListProvider
	 */
	public function testNormaliseTrustedHostList($input, $expected, $scenario)
	{
		$result = e107::normaliseTrustedHostList($input);
		$this->assertSame($expected, $result, "Failed scenario: $scenario");
	}

	/**
	 * Resolve the request host from a $_SERVER array. The visited port must
	 * survive (regression for the master-only port-strip that broke admin
	 * login and absolute URLs on non-standard ports, e.g. XAMPP/Docker on
	 * :8080), while a crafted Host falls back to the server-configured
	 * SERVER_NAME so it can never reach a generated URL.
	 *
	 * @dataProvider resolveHttpHostProvider
	 */
	public function testResolveHttpHost($server, $expected, $scenario)
	{
		$result = $this->resolveHttpHost->invoke(null, $server);
		$this->assertSame($expected, $result, "Failed scenario: $scenario");
	}

	public function resolveHttpHostProvider()
	{
		return array(
			'plain host kept' => array(
				array('HTTP_HOST' => 'example.com', 'SERVER_NAME' => 'example.com'),
				'example.com', 'plain host kept'),
			'non-standard port preserved (the regression)' => array(
				array('HTTP_HOST' => 'localhost:8080', 'SERVER_NAME' => 'localhost'),
				'localhost:8080', 'non-standard port preserved (the regression)'),
			'docker random port preserved' => array(
				array('HTTP_HOST' => 'localhost:34487', 'SERVER_NAME' => 'localhost', 'SERVER_PORT' => '34487'),
				'localhost:34487', 'docker random port preserved'),
			'explicit :80 preserved verbatim' => array(
				array('HTTP_HOST' => 'example.com:80', 'SERVER_NAME' => 'example.com'),
				'example.com:80', 'explicit :80 preserved verbatim'),
			'IPv4 host with port' => array(
				array('HTTP_HOST' => '127.0.0.1:8443', 'SERVER_NAME' => '127.0.0.1'),
				'127.0.0.1:8443', 'IPv4 host with port'),
			'bracketed IPv6 with port' => array(
				array('HTTP_HOST' => '[::1]:8443', 'SERVER_NAME' => 'localhost'),
				'[::1]:8443', 'bracketed IPv6 with port'),
			'subdomain with port' => array(
				array('HTTP_HOST' => 'sub.example.com:8080', 'SERVER_NAME' => 'example.com'),
				'sub.example.com:8080', 'subdomain with port'),
			'empty Host falls back to SERVER_NAME' => array(
				array('HTTP_HOST' => '', 'SERVER_NAME' => 'fallback.example.com'),
				'fallback.example.com', 'empty Host falls back to SERVER_NAME'),
			'missing Host key falls back to SERVER_NAME' => array(
				array('SERVER_NAME' => 'fallback.example.com'),
				'fallback.example.com', 'missing Host key falls back to SERVER_NAME'),
			'single-quote injection rejected, uses SERVER_NAME' => array(
				array('HTTP_HOST' => "localhost'onerror=alert(1)", 'SERVER_NAME' => 'safe.example.com'),
				'safe.example.com', 'single-quote injection rejected, uses SERVER_NAME'),
			'double-quote injection rejected, uses SERVER_NAME' => array(
				array('HTTP_HOST' => 'localhost" onx=1', 'SERVER_NAME' => 'safe.example.com'),
				'safe.example.com', 'double-quote injection rejected, uses SERVER_NAME'),
			'path in Host rejected, uses SERVER_NAME' => array(
				array('HTTP_HOST' => 'evil.com/path', 'SERVER_NAME' => 'safe.example.com'),
				'safe.example.com', 'path in Host rejected, uses SERVER_NAME'),
			'whitespace in Host rejected, uses SERVER_NAME' => array(
				array('HTTP_HOST' => 'evil .com', 'SERVER_NAME' => 'safe.example.com'),
				'safe.example.com', 'whitespace in Host rejected, uses SERVER_NAME'),
			'non-numeric port rejected, uses SERVER_NAME' => array(
				array('HTTP_HOST' => 'localhost:zz', 'SERVER_NAME' => 'safe.example.com'),
				'safe.example.com', 'non-numeric port rejected, uses SERVER_NAME'),
			'both sources unusable, empty string' => array(
				array('HTTP_HOST' => 'bad/host', 'SERVER_NAME' => ''),
				'', 'both sources unusable, empty string'),
			'empty server array, empty string' => array(
				array(),
				'', 'empty server array, empty string'),
		);
	}

	public function trustedHostListProvider()
	{
		return array(
			'empty string'                       => array('', array(), 'empty string'),
			'only whitespace'                    => array("   \n  \n", array(), 'only whitespace'),
			'single bare hostname'               => array('example.com', array('example.com'), 'single bare hostname'),
			'IPv4 literal'                       => array('127.0.0.1', array('127.0.0.1'), 'IPv4 literal'),
			'localhost'                          => array('localhost', array('localhost'), 'localhost'),
			'IP plus localhost (review case)'    => array("127.0.0.1\nlocalhost", array('127.0.0.1', 'localhost'), 'IP plus localhost (review case)'),
			'newlines mixed CRLF and LF'         => array("a.com\r\nb.com\nc.com", array('a.com', 'b.com', 'c.com'), 'newlines mixed CRLF and LF'),
			'URL with scheme stripped'           => array('https://example.com/', array('example.com'), 'URL with scheme stripped'),
			'URL with path stripped'             => array('https://staging.example.com/foo/bar?x=1', array('staging.example.com'), 'URL with path stripped'),
			'case lowered'                       => array('EXAMPLE.com', array('example.com'), 'case lowered'),
			'www stripped'                       => array('www.example.com', array('example.com'), 'www stripped'),
			'port stripped'                      => array('localhost:8080', array('localhost'), 'port stripped'),
			'duplicates collapsed'               => array("example.com\nEXAMPLE.COM\nwww.example.com", array('example.com'), 'duplicates collapsed'),
			'blank lines dropped'                => array("\nexample.com\n\nother.com\n", array('example.com', 'other.com'), 'blank lines dropped'),
			'trims surrounding whitespace'       => array("  example.com  \n\t b.com\t", array('example.com', 'b.com'), 'trims surrounding whitespace'),
			'array input'                        => array(array('example.com', 'b.com'), array('example.com', 'b.com'), 'array input'),
			'array input with dupes and case'    => array(array('Example.com', 'EXAMPLE.com', 'b.com'), array('example.com', 'b.com'), 'array input with dupes and case'),
			'garbage line dropped'               => array("://\nexample.com", array('example.com'), 'garbage line dropped'),
		);
	}

	/**
	 * Regression: catches the silent save drop where prefs.php previously
	 * relied on the generic POST loop's $core_pref->update() to persist
	 * trusted_hosts. update() no-ops for keys that aren't already in the
	 * prefs array, which dropped the very first save of trusted_hosts.
	 * prefs.php now seeds the key via set() — this test pins the contract
	 * so a future refactor can't accidentally re-introduce the silent drop.
	 */
	public function testPrefUpdateSilentlyDropsNewKeys()
	{
		$pref = $this->make('e_pref');
		$pref->__construct('core');
		$pref->load();

		self::assertArrayNotHasKey('trusted_hosts_fixture', $pref->getPref(),
			"fixture pref key must be absent before the test runs");

		$pref->update('trusted_hosts_fixture', array('127.0.0.1', 'localhost'));
		self::assertArrayNotHasKey('trusted_hosts_fixture', $pref->getPref(),
			"e_pref::update() must continue to no-op for absent keys (this is the silent-drop "
			. "behaviour that prefs.php compensates for by calling set() directly for trusted_hosts)");

		$pref->set('trusted_hosts_fixture', array('127.0.0.1', 'localhost'));
		self::assertSame(array('127.0.0.1', 'localhost'), $pref->get('trusted_hosts_fixture'),
			"e_pref::set() must add the new key — this is the call path prefs.php takes for trusted_hosts");
	}

	/**
	 * End-to-end of the prefs.php save path for trusted_hosts:
	 * paste a newline-separated list into the admin form, the
	 * normaliser strips scheme/path/port/www/case + de-duplicates,
	 * and the result lands on $core_pref under the `trusted_hosts` key.
	 */
	public function testTrustedHostsSaveRoundTripsThroughPref()
	{
		$pref = $this->make('e_pref');
		$pref->__construct('core');
		$pref->load();

		$input = "127.0.0.1\nlocalhost\nhttps://Staging.Example.com/foo\nwww.parked-domain.com\n\nlocalhost";
		$pref->set('trusted_hosts', e107::normaliseTrustedHostList($input));

		self::assertSame(
			array('127.0.0.1', 'localhost', 'staging.example.com', 'parked-domain.com'),
			$pref->get('trusted_hosts'),
			"trusted_hosts must persist on \$core_pref with the normalised host list"
		);
	}

	public function hostMatchProvider()
	{
		return array(
			'exact match'                                    => array('example.com', 'example.com', true, 'exact match'),
			'exact match, www apex'                          => array('www.example.com', 'www.example.com', true, 'exact match, www apex'),
			'subdomain of configured'                        => array('example.com', 'sub.example.com', true, 'subdomain of configured'),
			'apex configured, www visit'                     => array('example.com', 'www.example.com', true, 'apex configured, www visit'),
			'www configured, apex visit'                     => array('www.example.com', 'example.com', true, 'www configured, apex visit'),
			'deep subdomain'                                 => array('example.com', 'a.b.c.example.com', true, 'deep subdomain'),
			'unrelated host'                                 => array('example.com', 'unrelated.org', false, 'unrelated host'),
			'suffix-only collision (no dot boundary)'        => array('example.com', 'fakeexample.com', false, 'suffix-only collision (no dot boundary)'),
			'suffix-only collision with hyphen'              => array('example.com', 'evil-example.com', false, 'suffix-only collision with hyphen'),
			'empty http host'                                => array('example.com', '', false, 'empty http host'),
			'list with match'                                => array(array('a.com', 'b.com'), 'b.com', true, 'list with match'),
			'list without match'                             => array(array('a.com', 'b.com'), 'c.com', false, 'list without match'),
			'list with empty entry then match'               => array(array('', 'example.com'), 'example.com', true, 'list with empty entry then match'),
			'empty allowed list'                             => array(array(), 'example.com', false, 'empty allowed list'),
			'empty allowed string'                           => array('', 'example.com', false, 'empty allowed string'),
			'list with subdomain match'                      => array(array('a.com', 'example.com'), 'sub.example.com', true, 'list with subdomain match'),
			'www visit against bare list entry'              => array(array('example.com'), 'www.example.com', true, 'www visit against bare list entry'),
			'apex visit against www list entry'              => array(array('www.example.com'), 'example.com', true, 'apex visit against www list entry'),
			'port on visited host'                           => array('localhost', 'localhost:8080', true, 'port on visited host'),
			'port on visited host, www'                      => array('example.com', 'www.example.com:8443', true, 'port on visited host, www'),
			'port on visited subdomain'                      => array('example.com', 'sub.example.com:8080', true, 'port on visited subdomain'),
			'port on allowed host'                           => array('example.com:80', 'example.com', true, 'port on allowed host'),
			'port on both sides'                             => array('example.com:80', 'example.com:8080', true, 'port on both sides'),
			'mismatched host with port still rejected'       => array('example.com', 'evil.com:8080', false, 'mismatched host with port still rejected'),
			'case-insensitive exact match'                   => array('Example.COM', 'example.com', true, 'case-insensitive exact match'),
			'case-insensitive www visit'                     => array('example.com', 'WWW.EXAMPLE.COM', true, 'case-insensitive www visit'),
		);
	}
}
