<?php
namespace Helper;
include_once(__DIR__ . "/../../../lib/deployers/cpanel_deployer.php");

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
	protected $deployer;

	public function _beforeSuite($settings = array())
	{
		$secrets = $settings['secrets'];
		if ($secrets['cpanel']['enabled'] === '1')
		{
			$this->deployer = new \cPanelDeployer($secrets['cpanel']);
			$retcode = $this->deployer->start();
			if ($retcode === true)
			{
				$domain = $this->deployer->getDomain();
		   		$this->getModule('PhpBrowser')->_reconfigure(array('url' => "http://${domain}"));
			}
		}
	}

	public function _afterSuite()
	{
		$this->deployer->stop();
	}
}
