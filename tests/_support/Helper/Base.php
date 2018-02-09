<?php
namespace Helper;
include_once(__DIR__ . "/../../../lib/deployers/cpanel_deployer.php");

// here you can define custom actions
// all public methods declared in helper class will be available in $I

abstract class Base extends \Codeception\Module
{
        protected $deployer;
        protected $deployer_components = ['db', 'fs'];

	public function _beforeSuite($settings = array())
	{
		$secrets = $settings['secrets'];
		if ($secrets['cpanel']['enabled'] === '1')
		{
			$this->deployer = new \cPanelDeployer($secrets['cpanel']);
			$retcode = $this->deployer->start($this->deployer_components);
			if ($retcode === true)
			{
                                $this->_callbackDeployerStarted();
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
                        $method = "_reconfigure_${component}";
                        $this->$method();
                }
        }

        protected function _reconfigure_fs()
        {
		$url = $this->deployer->getUrl();
		$this->getModule('PhpBrowser')->_reconfigure(array('url' => $url));
        }

        protected function _reconfigure_db()
        {
		$Db_config = array();
		$Db_config['dsn'] = $this->deployer->getDsn();
		$Db_config['user'] = $this->deployer->getDbUsername();
		$Db_config['password'] = $this->deployer->getDbPassword();
		$this->getModule('\Helper\DelayedDb')->_reconfigure($Db_config);
		$this->getModule('\Helper\DelayedDb')->_delayedInitialize();
        }
}
