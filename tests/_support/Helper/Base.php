<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

abstract class Base extends \Codeception\Module
{
	protected $deployer;
	protected $deployer_components = ['db', 'fs'];

	protected $db;

	public function getDbModule()
	{
		return $this->db ?: $this->db = $this->getModule('\Helper\DelayedDb');
	}

	public function getBrowserModule()
	{
		return $this->getModule('PhpBrowser');
	}

	public function _beforeSuite($settings = array())
	{
		$this->deployer = $this->getModule('\Helper\DeployerFactory')->create();
		$this->deployer->setComponents($this->deployer_components);

		$this->deployer->start();
		$this->_callbackDeployerStarted();

		foreach ($this->getModules() as $module)
		{
			if (get_class($module) !== get_class($this))
				$module->_beforeSuite();
		}
	}

	public function _afterSuite()
	{
		$this->deployer->stop();
	}

	protected function _callbackDeployerStarted()
	{
		foreach ($this->deployer_components as $component)
		{
			$method = "reconfigure_${component}";
			if (method_exists($this->deployer, $method))
			{
				$this->deployer->$method($this);
			}
		}
	}

	public function _before(\Codeception\TestCase $test = null)
	{
		$this->_callbackDeployerStarted();
	}
}
