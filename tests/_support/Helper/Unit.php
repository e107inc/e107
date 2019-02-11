<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends E107Base
{
	protected $deployer_components = ['db'];

	public function _beforeSuite($settings = array())
	{
		parent::_beforeSuite($settings);

		global $_E107;
		$_E107 = array();
		$_E107['cli'] = true;
		$_E107['phpunit'] = true;
		#$_E107['debug'] = true;

		codecept_debug("Loading ".APP_PATH."/class2.php…");
		define('E107_DBG_BASIC', true);
		require_once(APP_PATH."/class2.php");
	}
}
