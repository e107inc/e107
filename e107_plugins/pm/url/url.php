<?php

/*
* Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* PM Default URL configuration
* TODO - SEF URL configuration
*/
class plugin_pm_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'noSingleEntry' => true,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_PLUGIN}pm/pm.php', // this config won't work in single entry point mod (legacy not used at all), so just set this to default plugin file to notify router it's legacy module
				'format'		=> 'get', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfCreate' 	=> true,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> 'view/inbox', // [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
			),
			
			// rule set array
			'rules' => array() 
		);

	}

	/**
	 * Describe all pm routes. 
	 * Routes vs legacy queries:
	 * view/inbox -> ?inbox (default route, resolved on index/index)
	 * view/outbox -> ?outbox 
	 * view/message?id=xxx ->  ?show.xxx.inbox
	 * view/sent?id=xxx ->  ?show.xxx.outbox
	 * view/show?id=xxx ->  ?show.xxx (investigate why we have dupps, good time to remove them!)
	 * view/reply?id=xxx -> ?reply.xxx
	 * view/send -> ?send
	 * action/delete-in?id=xx -> ?del.xxx.inbox
	 * action/delete-out?id=xx -> ?del.xxx.outbox
	 * action/delete-blocked?id=xxx -> ?delblocked.xxx
	 * action/block?id=xx -> ?block.xxx
	 * action/unblock?id=xx -> ?unblock.xxx
	 * action/get?id=xxx&index=yyy -> ?get.xxx.yyy
	 */
	public function create($route, $params = array(), $options = array())
	{
		if(is_string($route)) $route = explode('/', $route, 2);
		if(!varset($route[0]) || 'index' == $route[0]) $route[0] = 'view';
		if(!varset($route[1])) $route[1] = 'inbox';
		$base = e107::getInstance()->getFolder('plugins').'pm/';
		
		//var_dump($options, $route, $params);
		if($route[0] == 'view')
		{
			if(!isset($params['id']) && isset($params['pm_id'])) $params['id'] = $params['pm_id'];
			switch($route[1])
			{		
				case 'index':
				case 'inbox':
					$this->legacyQueryString = 'inbox';
					return $base.'pm.php?inbox';
					break;
					
				case 'outbox':
					$this->legacyQueryString = 'outbox';
					return $base.'pm.php?outbox';
					break;
					
				// we could just remove them all and let only 'message' live
				case 'show':
					$this->legacyQueryString = 'show.'.$params['id'];
					return $base.'pm.php?show.'.$params['id'];
					break;
		
				case 'message':
					$this->legacyQueryString = 'show.'.$params['id'].'.inbox';
					return $base.'pm.php?show.'.$params['id'].'.inbox';
					break;
		
				case 'sent':
					$this->legacyQueryString = 'show.'.$params['id'].'.outbox';
					return $base.'pm.php?show.'.$params['id'].'.outbox';
					break;
		
				case 'reply':
					$this->legacyQueryString = 'reply.'.$params['id'];
					return $base.'pm.php?reply.'.$params['id'];
					break;
					
				case 'new':
					$this->legacyQueryString = 'send';
					return $base.'pm.php?send';
					break;
			}
		}
		elseif($route[0] == 'action')
		{
			if(!isset($params['id']) && isset($params['pm_id'])) $params['id'] = $params['pm_id'];
			switch($route[1])
			{						
				case 'delete-in':
					$this->legacyQueryString = 'del.'.$params['id'].'.inbox';
					return $base.'pm.php?del.'.$params['id'].'.inbox';
					break;
					
				case 'delete-out':
					$this->legacyQueryString = 'del.'.$params['id'].'.outbox';
					return $base.'pm.php?del.'.$params['id'].'.outbox';
					break;
					
				case 'delete-blocked':
					$this->legacyQueryString = 'delblocked.'.$params['id'];
					return $base.'pm.php?delblocked.'.$params['id'];
					break;
					
				case 'block':
					$this->legacyQueryString = 'block.'.$params['id'];
					return $base.'pm.php?block.'.$params['id'];
					break;
		
				case 'unblock':
					$this->legacyQueryString = 'unblock.'.$params['id'];
					return $base.'pm.php?unblock.'.$params['id'];
					break;
					
				case 'get':
					$this->legacyQueryString = 'get.'.$params['id'].'.'.$params['index'];
					return $base.'pm.php?get.'.$params['id'].'.'.$params['index'];
					break;
			}
		}

		return false;
	}

	/**
	 * Admin callback
	 * Language file not loaded as all language data is inside the lan_eurl.php (loaded by default on administration URL page)
	 */
	public function admin()
	{
		// static may be used for performance
		e107::plugLan('pm', 'url', true);
		static $admin = array(
			'labels' => array(
				'name' => PM_LAN_URL_NAME, // Module name
				'label' => PM_LAN_URL_DEFAULT_LABEL, // Current profile name
				'description' => PM_LAN_URL_DEFAULT_DESCR, //
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
}
