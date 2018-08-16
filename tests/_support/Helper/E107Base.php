<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

abstract class E107Base extends Base
{
	public $e107_mySQLprefix = 'e107_';
	const APP_PATH_E107_CONFIG = APP_PATH."/e107_config.php";

	public function _beforeSuite($settings = array())
	{
		$this->backupLocalE107Config();
		$this->cleanVCS();
		parent::_beforeSuite($settings);
		$this->writeLocalE107Config();
	}

	public function _afterSuite()
	{
		parent::_afterSuite();
		$this->revokeLocalE107Config();
		$this->cleanVCS();
		$this->restoreLocalE107Config();
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
		$e107_config['mySQLprefix'] = $this->e107_mySQLprefix;

		$e107_config_contents = $twig->render('e107_config.php', $e107_config);
		file_put_contents(self::APP_PATH_E107_CONFIG, $e107_config_contents);
	}

	protected function revokeLocalE107Config()
	{
		if (file_exists(self::APP_PATH_E107_CONFIG))
			unlink(self::APP_PATH_E107_CONFIG);
	}

	protected function cleanVCS()
	{
		$descriptorspec = [
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];
		$pipes = [];
		$resource = proc_open('git clean -fdx', $descriptorspec, $pipes, APP_PATH);
		//$stdout = stream_get_contents($pipes[1]);
		//$stderr = stream_get_contents($pipes[2]);
		//foreach ($pipes as $pipe)
		//{
		//	fclose($pipe);
		//}
		//var_dump($stdout);
		//var_dump($stderr);
		proc_close($resource);
	}

	protected function backupLocalE107Config()
	{
		if(file_exists(self::APP_PATH_E107_CONFIG))
		{
			rename(self::APP_PATH_E107_CONFIG, APP_PATH.'/e107_config.bak');
		}
	}

	protected function restoreLocalE107Config()
	{
		if(file_exists(APP_PATH."/e107_config.bak"))
		{
			rename(APP_PATH.'/e107_config.bak', self::APP_PATH_E107_CONFIG);
		}
	}

}
