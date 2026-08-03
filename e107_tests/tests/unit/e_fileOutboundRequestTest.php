<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Outbound request policy for e_file, completing GHSA-92fr-7h4f-22pp /
 * CVE-2026-43936.
 *
 * The published fix validates the URL as typed and then hands it to cURL with
 * CURLOPT_FOLLOWLOCATION on, so libcurl re-issues the request at whatever the
 * server answers with. The validation is therefore one redirect deep. These
 * tests drive a real redirect chain against the container's own Apache, so
 * "every hop is revalidated" is measured rather than asserted.
 *
 * COVERAGE GAPS left behind by this file, deliberately and knowingly:
 *
 *  1. Every hop here is served by 127.0.0.1, which the real policy refuses.
 *     The chain tests therefore substitute the policy predicate for a
 *     recording double (@see E107P3RecordingFile) and measure *which URLs the
 *     production code hands to the predicate*. The predicate itself is tested
 *     separately and for real in testResolveOutboundTargetRejects*(). What is
 *     not covered end to end is a redirect from a genuinely public host to a
 *     private one; that needs a redirector service with a routable address in
 *     e107_tests/docker/compose.yml, which is out of scope for this package.
 *
 *  2. The DNS rebinding window itself, two lookups with the attacker owning
 *     the interval, is closed by the pin and is not reproduced here: that
 *     needs a controllable DNS server in the compose stack. What *is* covered
 *     is that the pin is honoured (testCurlConnectsToThePinnedAddress(),
 *     testStreamFallbackConnectsToThePinnedAddress(): a `.test` name with no
 *     DNS record anywhere can only be reached if it was) and that a peer the
 *     policy did not resolve is refused even when the pin is silently
 *     discarded (testCurlRefusesAPeerTheAddressPolicyDidNotResolve()).
 *
 *  3. e107_handlers/mail_validation_class.php still opens an unpolicied SMTP
 *     connection to a caller-supplied domain's MX. It is out of this package
 *     and untested here; see the advisory.
 */

class e_fileOutboundRequestTest extends \Codeception\Test\Unit
{
	/** Fixture served by the container's own Apache. @see WorkspaceCleanup */
	const HOP_FIXTURE = 'e107_tests_p3_hop.php';

	/** Emitted by the fixture only once it has stopped redirecting. */
	const FINAL_SENTINEL = 'P3_FINAL_SENTINEL';

	/** Emitted by the fixture in the body of each 302, alongside the Location. */
	const HOP_SENTINEL = 'P3_HOP_BODY';

	/** Highest hop count e_file is willing to follow. */
	const MAX_REDIRECTS = 5;

	/** A routable literal, so no test in this file needs working DNS. */
	const PUBLIC_IP = '93.184.216.34';

	/** A second loopback literal, so a cross-origin hop needs no DNS either. */
	const OTHER_HOST = '127.0.0.2';

	/** RFC 6761 reserves .test: nothing anywhere resolves this name. */
	const PINNED_HOST = 'p3-pin.test';

	/** The vhost certificate the harness generates. @see docker/Dockerfile */
	const CA_BUNDLE = '/etc/ssl/e107/web.crt';

	/** @var e_file */
	protected $fl;

	/** @var string absolute path of the fixture in the served tree */
	private $fixturePath;

	/** @var string file getRemoteFile() downloads into */
	private $downloadPath;

	/** @var string file the fixture appends to when asked to count its hits */
	private $hitPath;

	protected function _before()
	{
		require_once(codecept_data_dir('e_fileOutboundRequestProbes.php'));

		$this->fl = new e_file();

		$this->downloadPath = e_TEMP . 'e107_tests_p3_download.txt';
		@unlink($this->downloadPath);

		$this->hitPath = e_TEMP . 'e107_tests_p3_hits.txt';
		$this->resetFixtureHits();

		$this->fixturePath = APP_PATH . '/' . self::HOP_FIXTURE;
		file_put_contents($this->fixturePath, $this->fixtureSource());

		$reachable = @file_get_contents($this->hopUrl(0, 0));
		self::assertSame(self::FINAL_SENTINEL, $reachable,
			'The redirect fixture is not being served from ' . $this->hopUrl(0, 0)
			. '. Every chain assertion below would be vacuous, so stop here.');
	}

	protected function _after()
	{
		@unlink($this->fixturePath);
		@unlink($this->downloadPath);
		@unlink($this->hitPath);
	}

	/**
	 * A redirector that answers with a 3xx until the requested hop count is
	 * reached, then with a sentinel. It boots nothing: no class2.php, so no
	 * flood protection and no session, and a twenty hop chain costs nothing.
	 *
	 * Query parameters: hop, stop, code (default 302), host (send the next hop
	 * somewhere else), sleep (seconds each redirecting hop takes), echo (report
	 * the method, the body and the headers of the final request).
	 *
	 * @return string
	 */
	private function fixtureSource()
	{
		$php = "<?php\n";
		$php .= "\$hop = isset(\$_GET['hop']) ? (int) \$_GET['hop'] : 0;\n";
		$php .= "\$stop = isset(\$_GET['stop']) ? (int) \$_GET['stop'] : 0;\n";
		$php .= "\$code = isset(\$_GET['code']) ? (int) \$_GET['code'] : 302;\n";
		$php .= "\$host = isset(\$_GET['host']) ? \$_GET['host'] : '';\n";
		$php .= "\$echo = isset(\$_GET['echo']);\n";
		$php .= "if(isset(\$_GET['log']))\n{\n";
		$php .= "\tfile_put_contents('" . $this->hitPath . "', 'x', FILE_APPEND | LOCK_EX);\n}\n";
		$php .= "if(\$hop < \$stop)\n{\n";
		$php .= "\tsleep(isset(\$_GET['sleep']) ? (int) \$_GET['sleep'] : 0);\n";
		$php .= "\t\$next = '/" . self::HOP_FIXTURE . "?hop=' . (\$hop + 1) . '&stop=' . \$stop;\n";
		$php .= "\t\$next .= (\$code !== 302) ? '&code=' . \$code : '';\n";
		$php .= "\t\$next .= (\$host !== '') ? '&host=' . rawurlencode(\$host) : '';\n";
		$php .= "\t\$next .= isset(\$_GET['sleep']) ? '&sleep=' . (int) \$_GET['sleep'] : '';\n";
		$php .= "\t\$next .= \$echo ? '&echo=1' : '';\n";
		$php .= "\t\$next .= isset(\$_GET['log']) ? '&log=1' : '';\n";
		$php .= "\t\$next = ((\$host !== '') ? 'http://' . \$host : '') . \$next;\n";
		$php .= "\theader('Location: ' . \$next, true, \$code);\n";
		$php .= "\techo '" . self::HOP_SENTINEL . "_' . \$hop;\n";
		$php .= "\texit;\n}\n";
		$php .= "echo '" . self::FINAL_SENTINEL . "';\n";
		$php .= "if(!\$echo)\n{\n\texit;\n}\n";
		$php .= "\$headers = function_exists('getallheaders') ? getallheaders() : array();\n";
		$php .= "\$headers = array_change_key_case(\$headers, CASE_LOWER);\n";
		$php .= "echo '|method=' . \$_SERVER['REQUEST_METHOD'];\n";
		$php .= "echo '|body=' . file_get_contents('php://input');\n";
		$php .= "echo '|authorization=' . (isset(\$headers['authorization']) ? \$headers['authorization'] : '');\n";
		$php .= "echo '|mark=' . (isset(\$headers['x-p3-mark']) ? \$headers['x-p3-mark'] : '');\n";

		return $php;
	}

	/**
	 * @param int    $hop
	 * @param int    $stop
	 * @param string $scheme
	 * @param string $extra  further fixture parameters, leading '&' included
	 * @return string
	 */
	private function hopUrl($hop, $stop, $scheme = 'http', $extra = '')
	{
		return $scheme . '://127.0.0.1/' . self::HOP_FIXTURE . '?hop=' . $hop . '&stop=' . $stop . $extra;
	}

	/**
	 * Every URL the chain visits, in order, for a chain of $stop redirects.
	 *
	 * @param int $stop
	 * @return string[]
	 */
	private function hopChain($stop)
	{
		$chain = array();
		for($i = 0; $i <= $stop; $i++)
		{
			$chain[] = $this->hopUrl($i, $stop);
		}

		return $chain;
	}

	/**
	 * The option array cURL is actually driven with. Asserting on the array
	 * rather than on the handle is what catches the commonest regression in
	 * this area: someone flips a default back and nothing notices, because a
	 * cURL handle will not tell you what was set on it.
	 */
	public function testCurlOptionArrayCarriesTheHardenedDefaults()
	{
		$options = $this->fl->curlOptions('http://' . self::PUBLIC_IP . '/x.zip');

		// Positive control: a legitimate public URL still yields a usable
		// request, so nothing below can be satisfied by refusing everything.
		self::assertIsArray($options, 'A public HTTP URL must still be fetchable.');
		self::assertSame('http://' . self::PUBLIC_IP . '/x.zip', $options[CURLOPT_URL]);

		self::assertFalse($options[CURLOPT_FOLLOWLOCATION],
			'libcurl must not follow redirects on its own: it would do so without revalidating the target.');
		self::assertSame(self::MAX_REDIRECTS, $options[CURLOPT_MAXREDIRS],
			'The redirect chain must be capped.');
		self::assertSame(CURLPROTO_HTTP | CURLPROTO_HTTPS, $options[CURLOPT_PROTOCOLS]);
		self::assertSame(CURLPROTO_HTTP | CURLPROTO_HTTPS, $options[CURLOPT_REDIR_PROTOCOLS]);
	}

	/**
	 * Deltik's decision: verify the peer unconditionally, no preference and no
	 * new-installs-only carve out.
	 */
	public function testCurlOptionArrayVerifiesTheTlsPeer()
	{
		$options = $this->fl->curlOptions('https://' . self::PUBLIC_IP . '/x.zip');

		self::assertIsArray($options, 'A public HTTPS URL must still be fetchable.');
		self::assertTrue($options[CURLOPT_SSL_VERIFYPEER],
			'The certificate chain must be verified.');
		self::assertSame(2, $options[CURLOPT_SSL_VERIFYHOST],
			'The certificate must be verified to match the host.');
	}

	/**
	 * initCurl() is public API and third-party plugins call it directly, so the
	 * policy has to live in the option builder rather than only in e_file's own
	 * callers. No core caller outside file_class.php remains.
	 */
	public function testCurlOptionsRefusesAnUnsafeAddress()
	{
		// Positive control first, so a blanket refusal cannot pass this test.
		self::assertIsArray($this->fl->curlOptions('http://' . self::PUBLIC_IP . '/'));

		self::assertFalse($this->fl->curlOptions('http://127.0.0.1/'));
		self::assertFalse($this->fl->curlOptions('http://169.254.169.254/latest/meta-data/'));
		self::assertFalse($this->fl->curlOptions('file:///etc/passwd'));
	}

	/**
	 * The address that passed the policy is the address libcurl must connect
	 * to. Without the pin the validating lookup and the connecting lookup are
	 * two different lookups and DNS rebinding still works.
	 */
	public function testCurlOptionsPinsTheValidatedAddresses()
	{
		$fl = new E107P3ResolverFile();
		$fl->addresses = array(self::PUBLIC_IP, '8.8.8.8');

		$options = $fl->curlOptions('http://p3-pin.test/x.zip');
		self::assertIsArray($options);
		self::assertSame(array('p3-pin.test:80:' . self::PUBLIC_IP . ',8.8.8.8'), $options[CURLOPT_RESOLVE]);

		$options = $fl->curlOptions('https://p3-pin.test/x.zip');
		self::assertSame(array('p3-pin.test:443:' . self::PUBLIC_IP . ',8.8.8.8'), $options[CURLOPT_RESOLVE],
			'The pin has to carry the port cURL will connect on.');

		$options = $fl->curlOptions('https://p3-pin.test:8443/x.zip');
		self::assertSame(array('p3-pin.test:8443:' . self::PUBLIC_IP . ',8.8.8.8'), $options[CURLOPT_RESOLVE]);

		$fl->addresses = array('2606:4700:4700::1111');
		$options = $fl->curlOptions('http://p3-pin.test/x.zip');
		self::assertSame(array('p3-pin.test:80:[2606:4700:4700::1111]'), $options[CURLOPT_RESOLVE],
			'IPv6 literals have to be bracketed inside a CURLOPT_RESOLVE entry.');
	}

	/**
	 * Comma separated addresses in one CURLOPT_RESOLVE entry arrived in
	 * libcurl 7.59.0. An older build silently discards an entry it cannot
	 * parse, which would leave the connection unpinned, so only the first
	 * address is offered there.
	 */
	public function testCurlOptionsPinsASingleAddressOnLegacyLibcurl()
	{
		$fl = new E107P3ResolverFile();
		$fl->addresses = array(self::PUBLIC_IP, '8.8.8.8');
		$fl->versionNumber = 0x073A00; // 7.58.0

		$options = $fl->curlOptions('http://p3-pin.test/x.zip');
		self::assertSame(array('p3-pin.test:80:' . self::PUBLIC_IP), $options[CURLOPT_RESOLVE]);
	}

	/**
	 * An address literal was never resolved, so there is nothing to pin and an
	 * entry would only be noise.
	 */
	public function testCurlOptionsDoesNotPinAnAddressLiteral()
	{
		$options = $this->fl->curlOptions('http://' . self::PUBLIC_IP . '/x.zip');
		self::assertArrayNotHasKey(CURLOPT_RESOLVE, $options);
	}

	/**
	 * A name whose RRset mixes a public address with a private one is the
	 * cheap form of rebinding: resolve twice, get a different answer. One bad
	 * record poisons the whole name.
	 */
	public function testResolveOutboundTargetRejectsAMixedRrset()
	{
		$fl = new E107P3ResolverFile();

		// Positive control: an all-public RRset resolves to a usable target.
		$fl->addresses = array(self::PUBLIC_IP, '8.8.8.8');
		$target = $fl->resolveOutboundTarget('http://p3-pin.test/');
		self::assertIsArray($target);
		self::assertSame(array(self::PUBLIC_IP, '8.8.8.8'), $target['addresses']);
		self::assertSame('p3-pin.test', $target['host']);
		self::assertSame(80, $target['port']);

		$fl->addresses = array(self::PUBLIC_IP, '127.0.0.1');
		self::assertFalse($fl->resolveOutboundTarget('http://p3-pin.test/'));

		$fl->addresses = array(self::PUBLIC_IP, '169.254.169.254');
		self::assertFalse($fl->resolveOutboundTarget('http://p3-pin.test/'));

		$fl->addresses = array();
		self::assertFalse($fl->resolveOutboundTarget('http://p3-pin.test/'),
			'A name with no A or AAAA record has nothing to pin to.');
	}

	/**
	 * The per-hop predicate has to refuse everything isUrlSafe() refuses,
	 * because it is what isUrlSafe() now answers from.
	 */
	public function testResolveOutboundTargetRejectsTheKnownEvasions()
	{
		// Positive controls, one per rejected shape below.
		self::assertIsArray($this->fl->resolveOutboundTarget('http://' . self::PUBLIC_IP . '/'));
		self::assertIsArray($this->fl->resolveOutboundTarget('https://' . self::PUBLIC_IP . '/'));
		self::assertIsArray($this->fl->resolveOutboundTarget('HTTP://' . self::PUBLIC_IP . '/'),
			'The scheme is case insensitive.');
		self::assertIsArray($this->fl->resolveOutboundTarget('http://127.0.0.1@' . self::PUBLIC_IP . '/'),
			'Userinfo that looks like a private address is not the host.');

		// Loopback and the unspecified address.
		self::assertFalse($this->fl->resolveOutboundTarget('http://127.0.0.1/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://127.1.2.3/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://0.0.0.0/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://[::1]/'));

		// Link local, including the cloud metadata endpoint.
		self::assertFalse($this->fl->resolveOutboundTarget('http://169.254.169.254/latest/meta-data/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://[fe80::1]/'));

		// RFC 1918 and IPv6 unique local.
		self::assertFalse($this->fl->resolveOutboundTarget('http://10.0.0.1/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://172.16.0.1/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://192.168.1.1/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://[fd00::1]/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://[::ffff:127.0.0.1]/'));

		// Decimal and octal spellings of 127.0.0.1.
		self::assertFalse($this->fl->resolveOutboundTarget('http://2130706433/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://0x7f000001/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://0177.0.0.1/'));

		// Userinfo hiding the real authority.
		self::assertFalse($this->fl->resolveOutboundTarget('http://' . self::PUBLIC_IP . '@127.0.0.1/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http://user:pass@169.254.169.254/'));

		// Scheme confusion.
		self::assertFalse($this->fl->resolveOutboundTarget('file:///etc/passwd'));
		self::assertFalse($this->fl->resolveOutboundTarget('gopher://' . self::PUBLIC_IP . '/'));
		self::assertFalse($this->fl->resolveOutboundTarget('ftp://' . self::PUBLIC_IP . '/'));
		self::assertFalse($this->fl->resolveOutboundTarget('http:' . self::PUBLIC_IP));
		self::assertFalse($this->fl->resolveOutboundTarget('//' . self::PUBLIC_IP . '/'));
		self::assertFalse($this->fl->resolveOutboundTarget(''));
	}

	/**
	 * isUrlSafe() keeps its published meaning: it is the boolean face of the
	 * per-hop predicate, not a second implementation of it.
	 */
	public function testIsUrlSafeAnswersFromThePerHopPredicate()
	{
		self::assertTrue($this->fl->isUrlSafe('http://' . self::PUBLIC_IP . '/'));
		self::assertFalse($this->fl->isUrlSafe('http://127.0.0.1/'));
	}

	/**
	 * The vulnerability itself. A real three URL chain is served by the
	 * container's Apache; the recording double reports which URLs the fetch
	 * put to the policy. With CURLOPT_FOLLOWLOCATION on, only the first one
	 * ever is: libcurl walks the rest by itself.
	 */
	public function testGetRemoteContentValidatesEveryRedirectHop()
	{
		$fl = new E107P3RecordingFile();

		$body = $fl->getRemoteContent($this->hopUrl(0, 2));

		// Positive control: the walk still ends where libcurl's would have.
		self::assertSame(self::FINAL_SENTINEL, $body,
			'Following the chain by hand must still deliver the final body.');

		self::assertSame($this->hopChain(2), $fl->uniqueSeen(),
			'Every URL in the chain has to be put to the policy, not just the first.');
	}

	/**
	 * A hop the policy refuses stops the fetch. Paired with the positive
	 * control above it, so a fetch that refuses everything fails this test.
	 */
	public function testGetRemoteContentRefusesAHopThePolicyRejects()
	{
		$permissive = new E107P3RecordingFile();
		self::assertSame(self::FINAL_SENTINEL, $permissive->getRemoteContent($this->hopUrl(0, 2)),
			'Positive control: the same chain succeeds when the policy allows every hop.');

		$fl = new E107P3RecordingFile();
		$fl->allowHops = 2; // the third URL is refused

		$body = $fl->getRemoteContent($this->hopUrl(0, 2));

		self::assertFalse($body, 'A refused hop must abort the fetch.');
		self::assertStringContainsString('private/reserved IP', $fl->getErrorMessage());
		self::assertSame($this->hopChain(2), $fl->uniqueSeen());
	}

	/**
	 * An open ended chain is a denial of service against the site itself, and
	 * libcurl's own default for CURLOPT_MAXREDIRS is unlimited.
	 */
	public function testGetRemoteContentCapsTheRedirectChain()
	{
		$atCap = new E107P3RecordingFile();
		self::assertSame(self::FINAL_SENTINEL, $atCap->getRemoteContent($this->hopUrl(0, self::MAX_REDIRECTS)),
			'Positive control: a chain exactly at the cap still resolves.');

		$overCap = new E107P3RecordingFile();
		self::assertFalse($overCap->getRemoteContent($this->hopUrl(0, self::MAX_REDIRECTS + 1)),
			'One hop past the cap must be refused.');
		self::assertCount(self::MAX_REDIRECTS + 1, $overCap->uniqueSeen(),
			'The walk must stop at the cap rather than keep asking.');
	}

	/**
	 * getRemoteFile() is the download path (marketplace, theme and plugin
	 * installs) and shares initCurl(), so it shares the defect.
	 */
	public function testGetRemoteFileValidatesEveryRedirectHop()
	{
		$fl = new E107P3RecordingFile();

		$result = $fl->getRemoteFile($this->hopUrl(0, 2), basename($this->downloadPath), 'temp');

		// Positive control: the download still lands, and lands complete.
		self::assertTrue($result, 'A legitimate chain must still download.');
		self::assertSame(self::FINAL_SENTINEL, file_get_contents($this->downloadPath),
			'Only the body of the final hop belongs in the file.');

		self::assertSame($this->hopChain(2), $fl->uniqueSeen());
	}

	/**
	 * A refused hop must not leave the caller holding a file full of redirect
	 * bodies that it will then try to unzip.
	 */
	public function testGetRemoteFileRefusesAHopThePolicyRejects()
	{
		// Positive control: the same chain lands, and lands complete, when the
		// policy allows every hop.
		$permissive = new E107P3RecordingFile();
		self::assertTrue($permissive->getRemoteFile($this->hopUrl(0, 2), basename($this->downloadPath), 'temp'));
		self::assertSame(self::FINAL_SENTINEL, file_get_contents($this->downloadPath));

		$fl = new E107P3RecordingFile();
		$fl->allowHops = 2;

		self::assertFalse($fl->getRemoteFile($this->hopUrl(0, 2), basename($this->downloadPath), 'temp'));
		self::assertFileExists($this->downloadPath,
			'The download was opened before the walk began, so "refused" has to mean truncated rather than absent.');
		self::assertSame('', file_get_contents($this->downloadPath),
			'The bodies of the 3xx answers are not the download.');
	}

	/**
	 * initCurl() is public API and third-party plugins call it directly, so the
	 * refusal has to live in the option builder rather than only in e_file's
	 * own callers. It is also the only place the option array is ever applied.
	 */
	public function testInitCurlRefusesAnUnsafeAddress()
	{
		// Positive control: a public URL still yields a usable handle, so the
		// refusal below cannot be a blanket one.
		$cu = $this->fl->initCurl('http://' . self::PUBLIC_IP . '/');
		self::assertNotFalse($cu, 'A public HTTP URL must still yield a cURL handle.');

		self::assertFalse($this->fl->initCurl('http://127.0.0.1/'));
		self::assertStringContainsString('private/reserved IP', $this->fl->getErrorMessage());

		self::assertFalse($this->fl->initCurl('file:///etc/passwd'));
	}

	/**
	 * curl_setopt_array() applies the options in array order and stops at the
	 * first one libcurl refuses, returning false and ignoring the rest.
	 * CURLOPT_RESOLVE is ordered ahead of the proxy, the POST body and the
	 * custom headers, so a handle that half took the policy must not be handed
	 * out looking usable.
	 */
	public function testInitCurlFailsClosedWhenLibcurlRefusesAnOption()
	{
		// Positive control: without the refused option the same URL builds a handle.
		self::assertNotFalse($this->fl->initCurl('http://' . self::PUBLIC_IP . '/'));

		$fl = new E107P3RejectedOptionFile();

		self::assertFalse($fl->initCurl('http://' . self::PUBLIC_IP . '/'));
		self::assertStringContainsString('Could not apply the outbound request options', $fl->getErrorMessage());
	}

	/**
	 * The pin, observed rather than read out of an option array. p3-pin.test
	 * has no DNS record anywhere (RFC 6761 reserves .test), so the request can
	 * only arrive if CURLOPT_RESOLVE was accepted and honoured.
	 */
	public function testCurlConnectsToThePinnedAddress()
	{
		$fl = new E107P3PinnedFile();
		$fl->addresses = array('127.0.0.1');

		self::assertSame(self::FINAL_SENTINEL, $fl->getRemoteContent($this->pinnedUrl()));

		// Negative control: the same fetch with nothing to pin cannot even
		// resolve the name, which is what makes the assertion above evidence
		// that the pin did the work.
		$unpinned = new E107P3PinnedFile();
		$unpinned->addresses = array();

		self::assertFalse($unpinned->getRemoteContent($this->pinnedUrl()));
		self::assertStringContainsString('Curl error: ' . CURLE_COULDNT_RESOLVE_HOST, $unpinned->getErrorMessage());
	}

	/**
	 * libcurl discards a CURLOPT_RESOLVE entry it cannot parse, and ignores the
	 * option outright behind a proxy, in both cases without an error. The peer
	 * it actually reached is therefore read back and checked.
	 */
	public function testCurlRefusesAPeerTheAddressPolicyDidNotResolve()
	{
		// An address literal carries no CURLOPT_RESOLVE entry, so this is what
		// a silently discarded pin looks like from e107's side of libcurl.
		$fl = new E107P3PinnedFile();
		$fl->addresses = array('127.0.0.1');
		self::assertSame(self::FINAL_SENTINEL, $fl->getRemoteContent($this->hopUrl(0, 0)),
			'Positive control: the peer reached is one the policy resolved.');

		$decoyed = new E107P3PinnedFile();
		$decoyed->addresses = array(self::PUBLIC_IP);

		self::assertFalse($decoyed->getRemoteContent($this->hopUrl(0, 0)));
		self::assertStringContainsString('did not resolve', $decoyed->getErrorMessage());
	}

	/**
	 * The POST passthrough, and the method downgrade the walk owns now that
	 * CURLOPT_FOLLOWLOCATION is off.
	 */
	public function testCurlFollowCarriesAPostAndDowngradesItOnA302()
	{
		$fl = new E107P3RecordingFile();
		$echo = $fl->getRemoteContent($this->hopUrl(0, 0, 'http', '&echo=1'), array('post' => 'p3=posted'));
		self::assertStringContainsString('|method=POST', $echo, 'A POST has to reach the server as a POST.');
		self::assertStringContainsString('|body=p3=posted', $echo);

		$fl = new E107P3RecordingFile();
		$echo = $fl->getRemoteContent($this->hopUrl(0, 1, 'http', '&echo=1'), array('post' => 'p3=posted'));
		self::assertStringContainsString('|method=GET', $echo,
			'301, 302 and 303 fall back to GET, which is what CURLOPT_FOLLOWLOCATION did.');
		self::assertStringContainsString('|body=|', $echo, 'The body goes with the method.');

		$fl = new E107P3RecordingFile();
		$echo = $fl->getRemoteContent($this->hopUrl(0, 1, 'http', '&code=307&echo=1'), array('post' => 'p3=posted'));
		self::assertStringContainsString('|method=POST', $echo,
			'307 preserves the method, which is also what CURLOPT_FOLLOWLOCATION did.');
		self::assertStringContainsString('|body=p3=posted', $echo);
	}

	/**
	 * Owning the walk means owning the cross-hop rules libcurl used to apply.
	 * It has stripped a credential on a host change since 7.58.0, for
	 * CVE-2018-1000007.
	 */
	public function testCurlFollowDropsCredentialHeadersOnACrossOriginHop()
	{
		$headers = array('Authorization: Bearer p3-secret', 'X-P3-Mark: kept');

		// Positive control: same scheme, same host, same port, nothing dropped.
		$fl = new E107P3RecordingFile();
		$echo = $fl->getRemoteContent($this->hopUrl(0, 1, 'http', '&echo=1'), array('header' => $headers));
		self::assertStringContainsString('|authorization=Bearer p3-secret', $echo);
		self::assertStringContainsString('|mark=kept', $echo);

		$fl = new E107P3RecordingFile();
		$echo = $fl->getRemoteContent($this->hopUrl(0, 1, 'http', '&host=' . self::OTHER_HOST . '&echo=1'),
			array('header' => $headers));
		self::assertStringContainsString('|authorization=|', $echo,
			'A credential a caller supplied for one origin must not be replayed at another.');
		self::assertStringContainsString('|mark=kept', $echo,
			'Only the credential-bearing headers go.');
	}

	/**
	 * CURLOPT_FOLLOWLOCATION spent the caller's timeout on the whole transfer,
	 * redirects included. A hand-rolled walk that gives every hop the full
	 * timeout turns a slow chain into CURL_MAX_REDIRECTS + 1 times the wait.
	 */
	public function testTheRedirectWalkSpendsOneTimeBudgetOnTheWholeChain()
	{
		// Positive control: the same chain, with the budget to finish it.
		$roomy = new E107P3RecordingFile();
		self::assertSame(self::FINAL_SENTINEL,
			$roomy->getRemoteContent($this->hopUrl(0, 3, 'http', '&sleep=1'), array('timeout' => 30)));

		$fl = new E107P3RecordingFile();
		$started = microtime(true);
		$body = $fl->getRemoteContent($this->hopUrl(0, 3, 'http', '&sleep=1'), array('timeout' => 2));
		$elapsed = microtime(true) - $started;

		self::assertFalse($body, 'The walk must stop once the budget is spent.');
		self::assertStringContainsString('Ran out of time', $fl->getErrorMessage());
		self::assertLessThan(10, $elapsed, 'The budget is for the chain, not for each hop of it.');
	}

	/**
	 * The 'decode' option rewrites the URL, so it has to run before the policy:
	 * what is validated has to be what is fetched.
	 */
	public function testTheDecodeOptionRunsBeforeThePolicy()
	{
		$fl = new E107P3RecordingFile();
		$encoded = 'http://127.0.0.1/' . self::HOP_FIXTURE . '%3Fhop%3D0%26stop%3D0';

		self::assertSame(self::FINAL_SENTINEL, $fl->getRemoteContent($encoded, array('decode' => true)),
			'Positive control: the decoded URL is the one that gets fetched.');
		self::assertSame(array($this->hopUrl(0, 0)), $fl->uniqueSeen(),
			'The policy has to be shown the URL that is fetched, not the one before decode rewrote it.');
	}

	/**
	 * End to end against the harness's own self signed vhost. This is also the
	 * backwards compatibility cost of the change, measured: a host whose
	 * certificate does not verify is now unreachable.
	 */
	public function testCurlRefusesAnUnverifiableTlsPeer()
	{
		$fl = new E107P3RecordingFile();

		// Positive control over plain HTTP: the same fixture, same policy.
		self::assertSame(self::FINAL_SENTINEL, $fl->getRemoteContent($this->hopUrl(0, 0)));

		$fl = new E107P3RecordingFile();
		self::assertFalse($fl->getRemoteContent($this->hopUrl(0, 0, 'https')),
			'A self signed certificate must not be accepted.');
		self::assertStringContainsString('Curl error: ' . CURLE_SSL_CACERT, $fl->getErrorMessage());
	}

	/**
	 * The other half of that cost, and the half with no proof otherwise:
	 * turning CURLOPT_SSL_VERIFYPEER on is worthless if it turns every HTTPS
	 * fetch off. CURLE_SSL_CACERT is also exactly what a missing CA bundle
	 * answers with, so the refusal above cannot tell the two apart on its own.
	 */
	public function testCurlAcceptsATlsPeerItCanVerify()
	{
		self::assertFileExists(self::CA_BUNDLE, 'The harness generates this at build time.');

		$body = "\$fl = new E107P3RecordingFile(); ";
		$body .= "\$r = \$fl->getRemoteContent('" . addslashes($this->hopUrl(0, 0, 'https')) . "'); ";
		$body .= $this->reportResult();

		$out = $this->runPhp($body, '-d curl.cainfo=' . self::CA_BUNDLE);

		self::assertSame(self::FINAL_SENTINEL, $this->resultOf($out),
			"A certificate that does verify, and a host the certificate is for, must still be fetchable.\n" . $out);
	}

	/**
	 * The stream fallback taken when cURL is absent had verify_peer and
	 * verify_peer_name explicitly false, which is worse than the default.
	 * curl_init() is disabled in a subprocess so the fallback is genuinely the
	 * code under test rather than something asserted about by inspection.
	 */
	public function testStreamFallbackVerifiesTheTlsPeer()
	{
		// Positive control: the fallback is reached and does fetch.
		self::assertSame(self::FINAL_SENTINEL, $this->fetchWithoutCurl($this->hopUrl(0, 0)));

		self::assertSame('false', $this->fetchWithoutCurl($this->hopUrl(0, 0, 'https')),
			'The stream fallback must refuse a certificate it cannot verify.');

		self::assertSame(self::FINAL_SENTINEL,
			$this->fetchWithoutCurl($this->hopUrl(0, 0, 'https'), '-d openssl.cafile=' . self::CA_BUNDLE),
			'... and must still fetch one it can.');
	}

	/**
	 * The same walk on the transport that has no libcurl behind it. Without
	 * this the policy is one hop deep wherever ext/curl is absent, which is
	 * ordinary shared hosting with curl_init in disable_functions.
	 */
	public function testStreamFallbackValidatesEveryRedirectHop()
	{
		$body = "\$fl = new E107P3RecordingFile(); ";
		$body .= "\$r = \$fl->getRemoteContent('" . addslashes($this->hopUrl(0, 2)) . "'); ";
		$body .= $this->reportResult();

		$out = $this->withoutCurl($body);

		self::assertSame(self::FINAL_SENTINEL, $this->resultOf($out),
			"Following the chain by hand must still deliver the final body.\n" . $out);
		self::assertSame($this->hopChain(2), $this->seenBy($out),
			"Every URL in the chain has to be put to the policy, not just the first.\n" . $out);
	}

	/**
	 * A hop the policy refuses stops the fetch here too.
	 */
	public function testStreamFallbackRefusesAHopThePolicyRejects()
	{
		$body = "\$fl = new E107P3RecordingFile(); \$fl->allowHops = 2; ";
		$body .= "\$r = \$fl->getRemoteContent('" . addslashes($this->hopUrl(0, 2)) . "'); ";
		$body .= $this->reportResult();

		$out = $this->withoutCurl($body);

		self::assertSame('false', $this->resultOf($out), "A refused hop must abort the fetch.\n" . $out);
		self::assertSame($this->hopChain(2), $this->seenBy($out), $out);
	}

	/**
	 * And the cap applies here too: PHP's http wrapper follows twenty by
	 * default.
	 */
	public function testStreamFallbackCapsTheRedirectChain()
	{
		$body = "\$fl = new E107P3RecordingFile(); ";
		$body .= "\$r = \$fl->getRemoteContent('" . addslashes($this->hopUrl(0, self::MAX_REDIRECTS + 1)) . "'); ";
		$body .= $this->reportResult();

		$out = $this->withoutCurl($body);

		self::assertSame('false', $this->resultOf($out), "One hop past the cap must be refused.\n" . $out);
		self::assertCount(self::MAX_REDIRECTS + 1, $this->seenBy($out),
			"The walk must stop at the cap rather than keep asking.\n" . $out);
	}

	/**
	 * CURLOPT_RESOLVE has no stream equivalent, so the validated address goes
	 * into the URL and the name goes into the Host header and the certificate
	 * check. p3-pin.test resolves nowhere, so arriving proves it happened.
	 */
	public function testStreamFallbackConnectsToThePinnedAddress()
	{
		$body = "\$fl = new E107P3PinnedFile(); \$fl->addresses = array('127.0.0.1'); ";
		$body .= "\$r = \$fl->getRemoteContent('" . addslashes($this->pinnedUrl()) . "'); ";
		$body .= $this->reportResult();

		self::assertSame(self::FINAL_SENTINEL, $this->resultOf($this->withoutCurl($body)));

		$body = "\$fl = new E107P3PinnedFile(); \$fl->addresses = array(); ";
		$body .= "\$r = \$fl->getRemoteContent('" . addslashes($this->pinnedUrl()) . "'); ";
		$body .= $this->reportResult();

		self::assertSame('false', $this->resultOf($this->withoutCurl($body)),
			'Negative control: with nothing to pin the name cannot be resolved at all.');
	}

	/**
	 * isValidURL() probes with get_headers(), which follows a Location with
	 * nothing revalidating the target. It only takes a context argument from
	 * PHP 7.1, and these fixes are backported to a branch whose CI runs 5.6.
	 */
	public function testIsValidUrlDoesNotFollowRedirects()
	{
		$fl = new E107P3RecordingFile();

		// Positive control: a URL that answers 200 is still reported valid.
		self::assertTrue((bool) $fl->isValidURL($this->hopUrl(0, 0, 'http', '&log=1')));
		self::assertSame(1, $this->fixtureHits());

		$this->resetFixtureHits();
		self::assertTrue((bool) $fl->isValidURL($this->hopUrl(0, 2, 'http', '&log=1')),
			'A 302 already counted as reachable, so the answer is unchanged.');
		self::assertSame(1, $this->fixtureHits(),
			'Only the URL the policy was shown may be requested.');

		// The default stream context has to be put back: leaving follow_location
		// off would quietly change every unrelated stream read on the request.
		$this->resetFixtureHits();
		@file_get_contents($this->hopUrl(0, 2, 'http', '&log=1'));
		self::assertSame(3, $this->fixtureHits(),
			'isValidURL() must leave the default stream context as it found it.');
	}

	/**
	 * @return int requests the fixture has answered since the last reset
	 */
	private function fixtureHits()
	{
		clearstatcache(true, $this->hitPath);

		return is_file($this->hitPath) ? (int) filesize($this->hitPath) : 0;
	}

	private function resetFixtureHits()
	{
		@unlink($this->hitPath);

		// The fixture is answered by Apache, which is not the user running the
		// suite, so the file it appends to has to exist and be writable first.
		@touch($this->hitPath);
		@chmod($this->hitPath, 0666);
	}

	/**
	 * @return string a URL on a name that resolves nowhere
	 */
	private function pinnedUrl()
	{
		return 'http://' . self::PINNED_HOST . '/' . self::HOP_FIXTURE . '?hop=0&stop=0';
	}

	/**
	 * @return string PHP that reports $r and, when there is one, $fl's record
	 */
	private function reportResult()
	{
		$php = "echo \"\\nP3RESULT:\" . (is_string(\$r) ? \$r : var_export(\$r, true)) . ':END'; ";
		$php .= "if(isset(\$fl) && method_exists(\$fl, 'uniqueSeen')) ";
		$php .= "{ echo \"\\nP3SEEN:\" . implode(' ', \$fl->uniqueSeen()) . ':END'; } ";

		return $php;
	}

	/**
	 * @param string $url
	 * @param string $ini extra `php -d` arguments
	 * @return string what getRemoteContent() returned, var_export'd when it is
	 *                not a string
	 */
	private function fetchWithoutCurl($url, $ini = '')
	{
		$body = "define('e_REMOTE_FILE_ALLOW_PRIVATE', true); ";
		$body .= "\$r = e107::getFile()->getRemoteContent('" . addslashes($url) . "'); ";
		$body .= $this->reportResult();

		return $this->resultOf($this->withoutCurl($body, $ini));
	}

	/**
	 * @param string $body PHP to run once class2.php and the probes are loaded
	 * @param string $ini  extra `php -d` arguments
	 * @return string everything the subprocess wrote
	 */
	private function withoutCurl($body, $ini = '')
	{
		$out = $this->runPhp($body, '-d disable_functions=curl_init ' . $ini);

		self::assertStringContainsString('P3CURL:false:END', $out,
			"The subprocess still had cURL, so the stream fallback was never the code under test.\n" . $out);

		return $out;
	}

	/**
	 * @param string $body
	 * @param string $ini
	 * @return string
	 */
	private function runPhp($body, $ini = '')
	{
		$php = "error_reporting(E_ALL); ini_set('display_errors', 1); ";
		$php .= "\$_E107 = array('cli' => true); ";
		$php .= "require_once('" . addslashes(APP_PATH . '/class2.php') . "'); ";
		$php .= "require_once('" . addslashes(codecept_data_dir('e_fileOutboundRequestProbes.php')) . "'); ";
		$php .= "echo \"\\nP3CURL:\" . var_export(function_exists('curl_init'), true) . ':END'; ";
		$php .= $body;
		$php .= "while(ob_get_level() > 0) { @ob_end_flush(); } ";

		$output = array();
		$status = 0;
		exec(sprintf('timeout 60 php %s -r %s 2>&1', $ini, escapeshellarg($php)), $output, $status);
		$out = implode("\n", $output);

		self::assertNotSame(124, $status,
			"The subprocess wedged and had to be killed, so nothing below was measured.\n" . $out);

		return $out;
	}

	/**
	 * @param string $out
	 * @return string
	 */
	private function resultOf($out)
	{
		if(!preg_match('/P3RESULT:(.*?):END/s', $out, $match))
		{
			self::fail("The subprocess reported no result at all.\n" . $out);
		}

		return $match[1];
	}

	/**
	 * @param string $out
	 * @return string[] every URL the subprocess put to the policy
	 */
	private function seenBy($out)
	{
		if(!preg_match('/P3SEEN:(.*?):END/s', $out, $match))
		{
			self::fail("The subprocess reported no policy record at all.\n" . $out);
		}

		return ($match[1] === '') ? array() : explode(' ', $match[1]);
	}
}
