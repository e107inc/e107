<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * URL Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/e107Url.php,v $
 * $Revision: 1.8 $
 * $Date: 2008-12-02 00:32:30 $
 * $Author: secretr $
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
	 * @return string URL or '#url-not-found' on error
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

		if($link = call_user_func($this->_link_handlers[$handlerId], $urlItems))
		{
			return $link;
		}
		return '#url-not-found';
	}

	/**
	 * Parse Request string
	 *
	 * @param string $section
	 * @param string $urlType
	 * @param string $request
	 * @return mixed parsed url
	 */
	function parseUrl($section, $urlType, $request = '')
	{
		if (empty($request))
		{
			$request = e_QUERY;
		}

		$handlerId = $section . '/' . $urlType;	
		if (!isset($this->_link_handlers[$handlerId]))
		{
			$this->_link_handlers[$handlerId] = $this->_initHandler($section, $urlType);
		}

		return call_user_func('parse_'.$this->_link_handlers[$handlerId], $request);
	}

	function _initHandler($section, $urlType)
	{
		global $pref; //FIXME pref handler, $e107->prefs instance

		if (strpos($section, ':') === false)
		{
			$section = 'plugin:'.$section;
		}
		
		list($type, $section) = explode(':', $section, 2);
		$handler = 'url_' . $section . '_' . $urlType;

		// Check to see if custom code is active and exists
		if (varsettrue($pref['url_config'][$section]))
		{
			$filePath = str_replace(
				array(
					'core-custom:', 
					'core-profile:', 
					'plugin-custom:',
					'plugin-profile:'
				), 
				array(
					e_FILE.'e_url/custom/core/', 
					e_FILE.'e_url/core/'.$section.'/', 
					e_FILE.'e_url/custom/plugin/',
					e_PLUGIN.$section.'/e_url/',
				), 
				$pref['url_config'][$section]
			);
			$fileName = $filePath.'/'.$urlType.'.php';
			var_dump('FileName: '.$fileName, $handler);
			if (is_readable($fileName))
			{
				include_once ($fileName);
			}
			if (function_exists($handler))
			{
				return $handler;
			}
		}
		
		//Search the default url config - the last station 
		$core = ($type === 'core');
		$handlerId = $section . '/' . $urlType;
		$fileName = ($core ? e_FILE."url/core/{$handlerId}.php" : e_PLUGIN."{$section}/url/{$urlType}.php");
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
