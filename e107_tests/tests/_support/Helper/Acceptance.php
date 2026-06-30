<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends E107Base
{
	protected $deployer_components = ['db', 'fs'];

	protected function writeLocalE107Config()
	{
		// Noop
		// Acceptance tests will install the app themselves
	}

	public function unlinkE107ConfigFromTestEnvironment()
	{
		$this->deployer->unlinkAppFile("e107_config.php");
	}

	/**
	 * Assert the last response did not issue a Location redirect to a URL
	 * containing $needle.
	 *
	 * Codeception's PhpBrowser exposes no seeHttpHeader, so read the Location
	 * header straight off the BrowserKit client. Pair with
	 * $I->stopFollowingRedirects() so the redirect response is captured here
	 * rather than chased to the (possibly off-site) target.
	 *
	 * @param string $needle
	 * @return void
	 */
	public function seeNoRedirectTo($needle)
	{
		$response = $this->getModule('PhpBrowser')->client->getInternalResponse();
		$location = (string) $response->getHeader('Location');
		\PHPUnit\Framework\Assert::assertStringNotContainsString(
			$needle, $location, "Response must not redirect to: $needle");
	}

	/**
	 * Clear the installer's resume cookie at the path it was actually set on.
	 *
	 * The installer scopes e107install_state to e_HTTP (the app's base path,
	 * e.g. /e107/), but Codeception's resetCookie() defaults to "/" and so
	 * leaves the app-path cookie in the jar. Derive the base path from the
	 * suite URL and expire the cookie there (and at "/").
	 *
	 * @return void
	 */
	public function resetInstallStateCookie()
	{
		$browser = $this->getModule('PhpBrowser');
		$base = parse_url((string) $browser->_getConfig('url'), PHP_URL_PATH);
		if (!is_string($base) || $base === '')
		{
			$base = '/';
		}

		// Clear the base path with and without a trailing slash, plus "/", so
		// the jar entry is removed however e_HTTP and the suite URL normalise it.
		$paths = array('/');
		if ($base !== '/')
		{
			$paths[] = '/'.trim($base, '/');
			$paths[] = '/'.trim($base, '/').'/';
		}
		foreach (array_unique($paths) as $path)
		{
			$browser->resetCookie('e107install_state', array('path' => $path));
		}
	}
}
