<?php

use Codeception\Scenario;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Helper\AdminLogin;
use Helper\ProbeGuard;
use Psr\Http\Message\ResponseInterface;

/**
 * The session cookie goes out in the one form PHP sends it, driven through Guzzle because its jar, unlike Codeception's, keeps two cookies of one name as a browser does (#6817).
 */
class SessionCookieSignInCest
{
	const PROBE_FILE = 'e107_tests_session_cookie_probe.php';

	const SESSION_COOKIE = 'PHPSESSID';

	const STALE_ID = 'e107helpstalesessionid0000';

	/** @var string the host the suite reaches the site on */
	private $host = '';

	public function _before(AcceptanceTester $I, Scenario $scenario)
	{
		$this->host = $I->executeInGuzzle(function (Client $client)
		{
			return (string) parse_url((string) $client->getConfig('base_uri'), PHP_URL_HOST);
		});

		if(strpos($this->host, '.') === false || filter_var($this->host, FILTER_VALIDATE_IP) !== false)
		{
			$scenario->skip('e107 scopes no cookie to a whole domain on '.$this->host.', so there is no second session cookie to test for.');
		}

		$I->expectOutboundRequest('e107.org');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->restoreSitePrefs();
	}

	public function aBrowserHoldingTheDomainWideCopySignsIn(AcceptanceTester $I)
	{
		$page = $I->executeInGuzzle(function (Client $client)
		{
			$jar = new CookieJar(false, array(new SetCookie(array(
				'Name'    => self::SESSION_COOKIE,
				'Value'   => self::STALE_ID,
				'Domain'  => '.'.$this->host,
				'Path'    => $this->path($client),
				'Expires' => time() + 3600,
			))));

			return $this->signIn($client, $jar);
		});

		$I->assertStringContainsString(AdminLogin::CONTROL_PANEL_MARKER, $page);
	}

	public function theRefreshRenewsTheSessionCookieInTheFormPhpSentIt(AcceptanceTester $I)
	{
		$sent = $this->cookiesSentByARefreshAt($I, 'index.php');

		$I->assertContains('host-only renewed', $sent);
		$I->assertNotContains('domain-wide renewed', $sent);
	}

	public function aMinimalRequestLeavesAMultiLanguageSubdomainSitesSharedCookieAlone(AcceptanceTester $I)
	{
		$I->haveSitePrefUntilAfter('multilanguage_subdomain', $this->host);

		$sent = $this->cookiesSentByARefreshAt($I, 'e107_images/thumb.php');

		$I->assertNotContains('domain-wide expired', $sent);
		$I->assertNotContains('host-only renewed', $sent);
	}

	/**
	 * Sign in, make the session due for its renewal, request $page and describe each session cookie its answer sets.
	 *
	 * @param AcceptanceTester $I
	 * @param string $page
	 * @return string[] such as 'host-only renewed' or 'domain-wide expired'
	 */
	private function cookiesSentByARefreshAt(AcceptanceTester $I, $page)
	{
		$I->haveProbe(self::PROBE_FILE, <<<'PHP'
<?php
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
$_SESSION['_cookie_session_validate'] = 1;
echo 'PROBE_OK';
PHP
		);

		return $I->executeInGuzzle(function (Client $client) use ($page)
		{
			$jar = new CookieJar();
			$this->signIn($client, $jar);
			$id = $jar->getCookieByName(self::SESSION_COOKIE)->getValue();
			$client->request('GET', self::PROBE_FILE.'?'.ProbeGuard::query(), array('cookies' => $jar, 'http_errors' => false));

			return $this->describeSessionCookies($client->request('GET', $page, array('cookies' => $jar, 'http_errors' => false, 'allow_redirects' => false)), $id);
		});
	}

	/**
	 * @param ResponseInterface $response
	 * @param string $id the session's id
	 * @return string[] one entry per session cookie $response sets
	 */
	private function describeSessionCookies(ResponseInterface $response, $id)
	{
		$described = array();

		foreach($response->getHeader('Set-Cookie') as $header)
		{
			$cookie = SetCookie::fromString($header);

			if($cookie->getName() === self::SESSION_COOKIE)
			{
				$described[] = ($cookie->getDomain() === null ? 'host-only' : 'domain-wide').' '.($cookie->isExpired() ? 'expired' : ($cookie->getValue() === $id ? 'renewed' : 'other'));
			}
		}

		return $described;
	}

	/**
	 * Sign in at the admin login with $jar and return the admin page that follows.
	 *
	 * @param Client $client
	 * @param CookieJar $jar
	 * @return string
	 */
	private function signIn(Client $client, CookieJar $jar)
	{
		$options = array('cookies' => $jar, 'http_errors' => false);
		$form = (string) $client->request('GET', ltrim(AdminLogin::LOGIN_PATH, '/'), $options)->getBody();
		preg_match(AdminLogin::TOKEN_FIELD_PATTERN, $form, $token);

		$client->request('POST', ltrim(AdminLogin::LOGIN_PATH, '/'), $options + array('form_params' => array(
			'authname'   => AdminLogin::ADMIN_USER,
			'authpass'   => AdminLogin::ADMIN_PASS,
			'authsubmit' => 1,
			'e-token'    => isset($token[1]) ? $token[1] : '',
		)));

		return (string) $client->request('GET', ltrim(AdminLogin::LOGIN_PATH, '/'), $options)->getBody();
	}

	private function path(Client $client)
	{
		$path = (string) parse_url((string) $client->getConfig('base_uri'), PHP_URL_PATH);

		return ($path === '') ? '/' : $path;
	}
}
