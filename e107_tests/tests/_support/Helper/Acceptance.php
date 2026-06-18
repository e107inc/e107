<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends E107Base
{
	protected $deployer_components = ['db', 'fs'];

	/**
	 * Send a plain (non-AJAX) POST request, preserving the browser session.
	 *
	 * InnerBrowser::sendAjaxPostRequest() sets the X-Requested-With header,
	 * which makes e107 define e_AJAX_REQUEST and route admin-ui dispatch to
	 * *Ajax* action methods. Tests posting to ordinary admin form routes
	 * need an unmarked POST instead.
	 *
	 * @param string $uri
	 * @param array $params
	 * @return void
	 */
	public function sendPostRequest($uri, $params = [])
	{
		$this->getModule('PhpBrowser')->_request('POST', $uri, $params);
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
		$paths = ['/'];
		if ($base !== '/')
		{
			$paths[] = '/'.trim($base, '/');
			$paths[] = '/'.trim($base, '/').'/';
		}
		foreach (array_unique($paths) as $path)
		{
			$browser->resetCookie('e107install_state', ['path' => $path]);
		}
	}

	protected function writeLocalE107Config()
	{
		// Noop
		// Acceptance tests will install the app themselves
	}

	public function unlinkE107ConfigFromTestEnvironment()
	{
		$this->deployer->unlinkAppFile("e107_config.php");
	}

	public function writeE107ConfigToTestEnvironment($contents)
	{
		$this->deployer->writeAppFile("e107_config.php", $contents);
	}
}
