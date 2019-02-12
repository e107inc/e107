<?php
namespace Helper;
include_once(codecept_root_dir() . "lib/preparers/PreparerFactory.php");

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Lib\ModuleContainer;

abstract class E107Base extends Base
{
	const APP_PATH_E107_CONFIG = APP_PATH."/e107_config.php";
	const E107_MYSQL_PREFIX = 'e107_';
	protected $preparer = null;

	public function __construct(ModuleContainer $moduleContainer, $config = null)
	{
		parent::__construct($moduleContainer, $config);
		$this->preparer = \PreparerFactory::create();
	}

	public function _beforeSuite($settings = array())
	{
		$this->backupLocalE107Config();
		$this->preparer->snapshot();
		parent::_beforeSuite($settings);
		$this->writeLocalE107Config();
	}

	protected function backupLocalE107Config()
	{
		if(file_exists(self::APP_PATH_E107_CONFIG))
		{
			rename(self::APP_PATH_E107_CONFIG, APP_PATH.'/e107_config.php.bak');
		}
	}

	protected function writeLocalE107Config()
	{
		$twig_loader = new \Twig_Loader_Array([
			'e107_config.php' => file_get_contents(codecept_data_dir()."/e107_config.php.sample")
		]);
		$twig = new \Twig_Environment($twig_loader);

		$db = $this->getModule('\Helper\DelayedDb');

		$e107_config = [];
		$e107_config['mySQLserver'] = $db->_getDbHostname();
		$e107_config['mySQLuser'] = $db->_getDbUsername();
		$e107_config['mySQLpassword'] = $db->_getDbPassword();
		$e107_config['mySQLdefaultdb'] = $db->_getDbName();
		$e107_config['mySQLprefix'] = self::E107_MYSQL_PREFIX;

		$e107_config_contents = $twig->render('e107_config.php', $e107_config);
		file_put_contents(self::APP_PATH_E107_CONFIG, $e107_config_contents);
	}

	public function _afterSuite()
	{
		parent::_afterSuite();
		$this->revokeLocalE107Config();
		$this->preparer->rollback();
		$this->restoreLocalE107Config();
	}

	protected function revokeLocalE107Config()
	{
		if (file_exists(self::APP_PATH_E107_CONFIG))
			unlink(self::APP_PATH_E107_CONFIG);
	}

	protected function restoreLocalE107Config()
	{
		if(file_exists(APP_PATH."/e107_config.php.bak"))
		{
			rename(APP_PATH.'/e107_config.php.bak', self::APP_PATH_E107_CONFIG);
		}
	}

}
