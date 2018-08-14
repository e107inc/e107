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
		// cPanel Environment
		$this->deployer->unlinkAppFile("e107_config.php");

		// Local Environment
		if (file_exists(APP_PATH."/e107_config.php"))
		{
			unlink(APP_PATH."/e107_config.php");
		}
	}
}
