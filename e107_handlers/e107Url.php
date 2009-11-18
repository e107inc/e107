<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * URL Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/e107Url.php,v $
 * $Revision: 1.14 $
 * $Date: 2009-11-18 01:04:43 $
 * $Author: e107coders $
*/

if (!defined('e107_INIT')) { exit; }

class eURL
{
	/**
	 * @var array
	 */
	protected $_link_handlers = array();

	/**
	 * Create site url
	 * Example: 
	 * <code>e107::getUrl()->create('core::news', 'main', 'action=extend&id=1&sef=Item-SEF-URL');</code>
	 * <code>e107::getUrl()->create('myplug', 'main', 'action=myaction&id=1');</code>
	 * 
	 * @param string $section
	 * @param string $urlType
	 * @param string|array $urlItems
	 * @return string URL or '#url-not-found' on error
	 */
	public function create($section, $urlType, $urlItems = array())
	{
		if (!is_array($urlItems))
		{
			//strange looking... well, I like it
			parse_str($urlItems, $urlItems);
		}
		if($link = call_user_func($this->getHandlerFunction($section, $urlType), $urlItems))
		{
			return $link;
		}
		return '#url-not-found';
	}
	
	/**
	 * Alias of {@link create()}
	 * @param string $section
	 * @param string $urlType
	 * @param array $urlItems [optional]
	 * @return string URL
	 */
	public function getUrl($section, $urlType, $urlItems = array())
	{
		return $this->create($section, $urlType, $urlItems);
	}

	/**
	 * Parse Request
	 *
	 * @param string $section
	 * @param string $urlType
	 * @param string $request
	 * @return mixed parsed url
	 */
	public function parseRequest($section, $urlType, $request = '')
	{
		if (empty($request))
		{
			$request = e_QUERY;
		}
		return call_user_func('parse_'.$this->getHandlerFunction($section, $urlType), $request);
	}
	
	/**
	 * Get required profile function string
	 * NOTE: function existence is not granted
	 * 
	 * @param string $section
	 * @param string $urlType
	 * @return string handler function
	 */
	public function getHandlerFunction($section, $urlType)
	{
		$handlerId = $this->toHandlerId($section, $urlType);
		if (!isset($this->_link_handlers[$handlerId]))
		{
			$this->_link_handlers[$handlerId] = $this->_initHandler($section, $urlType);
		}
		
		return $this->_link_handlers[$handlerId];
	}

	/**
	 * Try to load required url profile script
	 * 
	 * @param string $section
	 * @param string $urlType
	 * @return string function name
	 */
	protected function _initHandler($section, $urlType)
	{
		$section = $this->formatSection($section);

		list($type, $section) = explode(':', $section, 2);
		$handler = 'url_' . $section . '_' . $urlType;

		// Check to see if custom code is active and exists
		$val = e107::findPref('url_config/'.$section);
		if ($val)
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
				
				$val
			);
			$fileName = $filePath.'/'.$urlType.'.php';

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
		$handlerId = $section . '/' . $urlType;
		$fileName = ($type === 'core' ? e_FILE."e_url/core/{$handlerId}.php" : e_PLUGIN."{$section}/e_url/{$urlType}.php");
		if (is_readable($fileName))
		{
			include_once ($fileName);
		}
		return $handler;
	}
	
	/**
	 * Format section to 'core|plugin:section_name' to be safe used in 
	 * all internal routines.
	 * This method is here because plugins are allowed to omit 'plugin:' prefix.
	 * 
	 * @param string $section
	 * @return string formatted section
	 */
	public function formatSection($section)
	{
		if (strpos($section, ':') === false)
		{
			$section = 'plugin:'.$section;
		}
		return $section;
	}
	
	/**
	 * Create the unique key for $_link_handlers 
	 * 
	 * @param string $section
	 * @param string $urlType
	 * @return string 
	 */
	public function toHandlerId($section, $urlType)
	{
		return $this->formatSection($section). '/' . $urlType;
	}
	
	/**
	 * Get profile currently used for a given section
	 * 
	 * @param string $section
	 * @param boolean $strict true - return null if not found, false - return 'main' if not found 
	 * @return string section id
	 */
	public function getProfileId($section, $strict = false)
	{
		$val = e107::findPref('url_config/'.str_replace(array('core:', 'plugin:'), '', $section));
		if($val)
		{
			$val = explode(':', $val, 2);
			return $val[1];
		}
		return (!$strict || e107::getConfig()->isData('url_config/'.$section) ? 'main' : null);
	}
	
	/**
	 * Proxy for undefined methods. It allows quick (less arguments)
	 * call to {@link create()}. 
	 * NOTE that passed to {@link create()} second argument
	 * ($urlType) is always 'main'!
	 * 
	 * Example:
	 * <code>
	 * echo e107::getUrl()->createCoreNews('action=month&value=092009');
	 * //calls internal $this->create('core:news', 'main', 'action=month&value=092009');
	 * 
	 * echo e107::getUrl()->createTagwords('q=tag word');
	 * //calls internal $this->create('plugin:tagwords', 'main', 'q=month&value=092009');
	 * </code>
	 * @param string $method
	 * @param array $arguments array(0 => request string|array)
	 * @return string URL
	 * @throws Exception
	 */
	function __call($method, $arguments) {
		if (strpos($method, "createCore") === 0)
		{
			$section = strtolower(substr($method, 10));
			return $this->create('core:'.$section, 'main', varset($arguments[0]));
		}
		elseif (strpos($method, "create") === 0)
		{
			$section = strtolower(substr($method, 6));
			return $this->create('plugin:'.$section, 'main', varset($arguments[0]));
		}
		throw new Exception('Method '.$method.' does not exist!');//FIXME - e107Exception handler
	}
}
