<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/e107Url.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-11-24 18:06:03 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

class eURL
{

	var $core = false;
	var $link = '';
	
	function createURL($section, $urlType, $urlItems)
	{
		if(!is_array($urlItems))
		{
			$urlItems = array($urlItems => 1);
		}
		$functionName = 'url_'.$section.'_'.$urlType;
		if(!function_exists($functionName))
		{
			$fileName = ($this->core ? e_FILE."url/custom/base/{$section}/{$urlType}.php" : e_FILE."url/custom/plugins/{$section}/{$urlType}.php");
			if(is_readable($fileName))
			{
				include_once($fileName);
			}
			else
			{
				$fileName = ($this->core ? e_FILE."url/base/{$section}/{$urlType}.php" : e_PLUGIN."{$section}/url/{$urlType}.php");
				if(is_readable($fileName))
				{
					include_once($fileName);
				}
				else
				{
					return false;
				}
			}
			if(!function_exists($functionName))
			{
				return false;
			}
		}
		
		if($this->link = call_user_func($functionName, $urlItems))
		{
			return true;
		}

		return false;
	}

}
