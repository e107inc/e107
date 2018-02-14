<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

abstract class Base extends \Codeception\Module
{
        protected $deployer;
        protected $deployer_components = ['db', 'fs'];

	public function _beforeSuite($settings = array())
	{
		$this->deployer = $this->getModule('\Helper\DeployerFactory')->create();
		if (is_object($this->deployer))
		{
			$this->deployer->start($this->deployer_components);
			$this->_callbackDeployerStarted();
		}
	}

        public function _afterSuite()
        {
		if (is_object($this->deployer))
			$this->deployer->stop();
        }

        protected function _callbackDeployerStarted()
        {
                foreach ($this->deployer_components as $component)
                {
                        $method = "_reconfigure_${component}";
                        $this->$method();
                }
        }

        protected function _reconfigure_fs()
        {
		$url = $this->deployer->getUrl();
		$browser = $this->getModule('PhpBrowser');
		$browser->_reconfigure(array('url' => $url));
		$browser->_beforeSuite();
        }

        protected function _reconfigure_db()
        {
		$db = $this->getModule('\Helper\DelayedDb');
		$Db_config = $db->getConfig();
		$Db_config['dsn'] = $this->deployer->getDsn();
		$Db_config['user'] = $this->deployer->getDbUsername();
		$Db_config['password'] = $this->deployer->getDbPassword();
		$db->_reconfigure($Db_config);
		// Next line is used to make connection available to any code after this point
		//$this->getModule('\Helper\DelayedDb')->_delayedInitialize();
		$db->_beforeSuite();
        }
}
