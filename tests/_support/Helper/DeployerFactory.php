<?php
namespace Helper;
$deployers_path = __DIR__ . "/../../../lib/deployers";
include_once("{$deployers_path}/Deployer.php");
foreach (glob("{$deployers_path}/*.php") as $path)
{
	include_once($path);
}

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class DeployerFactory extends \Codeception\Module
{
	/**
	 * @return \Deployer
	 */
	public function create()
	{
		return $this->createFromSecrets($this->config['secrets']);
	}

	/**
	 * @param $secrets
	 * @return \Deployer
	 */
	public function createFromSecrets($secrets)
	{
		$deployer = new \DummyDeployer();
		if ($secrets['cpanel']['enabled'] === '1')
		{
			$deployer = new \cPanelDeployer($secrets['cpanel']);
		}
		return $deployer;
	}

}
