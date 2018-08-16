<?php
spl_autoload_register(function($class_name) {
	$candidate_path = __DIR__ . "/$class_name.php";
	if (file_exists($candidate_path))
	{
		include_once($candidate_path);
	}
});
#include_once("$deployers_path/Deployer.php");
#foreach (glob("$deployers_path/*.php") as $path)
#{
#	include_once($path);
#}

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class DeployerFactory
{
	/**
	 * @return \Deployer
	 */
	public static function create()
	{
		$params = unserialize(PARAMS_SERIALIZED);

		$deployer = new NoopDeployer();
		switch ($params['deployer'])
		{
			case "local":
				$deployer = new LocalDeployer($params);
				break;
			case "sftp":
				$deployer = new SFTPDeployer($params);
				break;
			case "cpanel":
				$deployer = new cPanelDeployer($params);
				break;
		}
		return $deployer;
	}
}
