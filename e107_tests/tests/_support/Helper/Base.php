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
	protected $deployer_components = ['db', 'fs'];

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
		$this->_callbackDeployerStarted();

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

	protected function _callbackDeployerStarted()
	{
		foreach ($this->deployer_components as $component)
		{
			$method = "reconfigure_{$component}";
			if (method_exists($this->deployer, $method))
			{
				$this->deployer->$method($this);
			}
		}
	}

	/**
	 * Signature note: the source-of-truth form used to carry a
	 * \Codeception\TestInterface hint on $test, but Rector's
	 * DowngradeParameterTypeWideningRector treats any typed parameter
	 * defaulting to null as nullable and strips the hint at commit
	 * time, leaving the untyped form below to ship to every cell.
	 * Codeception 4.x's parent class declares the hint and emits an
	 * LSP warning at autoload on PHP 7.0; PHP 8.4 separately emits an
	 * implicit-nullable deprecation for the typed-with-null form.
	 * Both messages are silenced by the display_errors=0 sink in
	 * tests/_bootstrap.php so the warnings never reach stdout and so
	 * never trip e107's session_start() with "headers already sent".
	 *
	 * @param \Codeception\TestInterface $test
	 */
	public function _before(?\Codeception\TestInterface $test = null)
	{
		$this->_callbackDeployerStarted();
	}
}
