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
		$_E107['debug'] = true;

		codecept_debug("Loading ".APP_PATH."/class2.php…");
		define('E107_DEBUG_LEVEL', 1 << 0);
		require_once(APP_PATH."/class2.php");

		$create_dir = array(e_MEDIA,e_MEDIA_IMAGE,e_MEDIA_ICON,e_SYSTEM,e_CACHE,e_CACHE_CONTENT,e_CACHE_IMAGE, e_CACHE_DB, e_LOG, e_BACKUP, e_CACHE_URL, e_TEMP, e_IMPORT);

		foreach($create_dir as $dr)
		{
			if(!is_dir($dr))
			{
				if(mkdir($dr, 0755))
				{
				//	echo "\n(Creating ".$dr.")";
				}

			}

		}
	}
}
