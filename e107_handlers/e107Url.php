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
|     $Revision: 1.2 $
|     $Date: 2008-11-25 16:26:02 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

class eURL
{

	var $_link_handlers = array();

	/**
	 * Get plugin url
	 *
	 * @param string $section
	 * @param string $urlType
	 * @param string|array $urlItems
	 * @return string URL
	 */
	function getUrl($section, $urlType, $urlItems = array())
	{
		if (!is_array($urlItems))
		{
			$urlItems = array($urlItems => 1);
		}

		$handlerId = $section . '/' . $urlType;
		if (!isset($this->_link_handlers[$handlerId]))
		{
			$this->_link_handlers[$handlerId] = $this->_initHandler($section, $urlType);
		}

		return (string )call_user_func($this->_link_handlers[$handlerId], $urlItems);
	}

	function _initHandler($section, $urlType)
	{
		$handlerId = $section . '/' . $urlType;
		$handler = 'url_' . $section . '_' . $urlType;
		$core = false;
		if (strpos($section, ':') !== false)
		{
			list($tmp, $section) = explode(':', $section, 2);
			$core = ($tmp === 'core');
		}

		// Check to see if custom code is active and exists
		if (varsettrue($pref['url_config'][$section]))
		{
			$fileName = ($core ? e_FILE."url/custom/base/{$handlerId}.php" : e_FILE."url/custom/plugins/{$handlerId}.php");
			if (is_readable($fileName))
			{
				include_once ($fileName);
			}
			if (function_exists($handler))
			{
				return $handler;
			}
		}
		$fileName = ($core ? e_FILE."url/base/{$handlerId}.php" : e_PLUGIN."{$section}/url/{$urlType}.php");
		if (is_readable($fileName))
		{
			include_once ($fileName);
		}
		return $handler;
	}

	/*
	Preparing for PHP5
	Exmample future calls (after stopping PHP4 support):
	$e107->url->getForum('post', array('edit' => 10));
	$e107->url->getCoreUser('user', array('id' => 10));

	function __call($method, $arguments) {
	if (strpos($method, "getCore") === 0)
	{
	$section = strtolower(substr($method, 7));
	return $this->getCoreUrl($section, varset($arguments[0]), varset($arguments[1]));
	}
	elseif (strpos($method, "get") === 0)
	{
	$section = strtolower(substr($method, 3));
	return $this->getUrl($section, varset($arguments[0]), varset($arguments[1]));
	}
	return '';
	}
	*/
}
