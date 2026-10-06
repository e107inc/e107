<?php
namespace Helper;
include_once(codecept_root_dir() . "lib/deployers/DeployerFactory.php");

// here you can define custom actions
// all public methods declared in helper class will be available in $I

abstract class Base extends \Codeception\Module
{
	/**
	 * @var \Deployer
	 */
	protected $deployer;
	protected $deployer_components = ['fs'];

	public function getDbModule()
	{
		return $this->getModule('\Helper\DelayedDb');
	}

	public function getBrowserModule()
	{
		return $this->getModule('PhpBrowser');
	}

	public function _beforeSuite($settings = array())
	{
		$this->deployer = \DeployerFactory::create();
		$this->deployer->setComponents($this->deployer_components);

		$this->deployer->start();

		foreach ($this->getModules() as $module)
		{
			if (!$module instanceof $this)
			{
				$module->_beforeSuite();
			}
		}
	}

	public function _afterSuite()
	{
		$this->deployer->stop();
	}
}
