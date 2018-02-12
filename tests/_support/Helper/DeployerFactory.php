<?php
namespace Helper;
include_once(__DIR__ . "/../../../lib/deployers/cpanel_deployer.php");

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class DeployerFactory extends \Codeception\Module
{
	public function create()
	{
		return $this->createFromSecrets($this->config['secrets']);
	}

	public function createFromSecrets($secrets)
	{
		$deployer = null;
		if ($secrets['cpanel']['enabled'] === '1')
		{
			$deployer = new \cPanelDeployer($secrets['cpanel']);
		}
		return $deployer;
	}

}
