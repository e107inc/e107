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
	public function sendPostRequest($uri, array $params = [])
	{
		$this->getModule('PhpBrowser')->_request('POST', $uri, $params);
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
