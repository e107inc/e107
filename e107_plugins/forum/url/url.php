<?php

/*
 * Copyright (C) 2008-2011 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * Forum Default URL configuration
 * TODO - SEF URL configuration
*/
class plugin_forum_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'noSingleEntry' => true,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_PLUGIN}forum/forum.php', // this config won't work in single entry point mod (legacy not used at all), so just set this to default plugin file to notify router it's legacy module
				'format'		=> 'get', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfParse' 	=> false,	// [optional] default false; use only this->parse() method, no core routine URL parsing
				'selfCreate' 	=> true,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> 'forum/main', // [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'legacyQuery' => '' // default legacy query string template, null to disable, empty - use current QUERY_STRING
			),
			
			// rule set array
			'rules' => array() 
		);
	}

	/**
	 * NOTE we have double 'forum' but this is the best way to map new-old forum URLs to the new routing engine
	 * Additionally, 'forum' controller is descriptive, and leading 'forum' module name could be easiely changed (URL aliases administration page)
	 */
	public function create($route, $params = array(), $options = array())
	{
		$amp = varset($options['encode']) ? '&amp;' : '&';
		if(is_string($route)) $route = explode('/', $route, 2);
		if(!varset($route[0]) || 'index' == $route[0]) $route[0] = 'forum';
		if(!varset($route[1])) $route[1] = 'main';
		$base = e107::getInstance()->getFolder('plugins').'forum/';
		
		//var_dump($options, $route, $params);
		if($route[0] == 'forum')
		{
			if(!isset($params['id']) && isset($params['forum_id'])) $params['id'] = $params['forum_id'];
			// if(isset($params['forum_name'])) $params['name'] = $params['forum_name']; - not used in this config
			switch($route[1])
			{
				case 'view':
					$page = (varset($params['page']) ? $amp.'p='.$params['page'] : '');
					return $base."forum_viewforum.php?id={$params['id']}{$page}";
					break;
		
				case 'track':
					return $base.'forum.php?track';
					break;
		
				case 'index':
				case 'main':
					return $base.'forum.php';
					break;
		
				case 'post':
					return $base."forum_post.php?f={$params['type']}{$amp}id={$params['id']}";
					break;
		
				case 'rules':
					return $base.'forum.php?f=rules';
					break;
		
				case 'mfar':
					return $base.'forum.php?f=mfar'.$amp.'id='.$params['id'];
					break;
		
			}
		}
		elseif($route[0] == 'thread')
		{
			if(!isset($params['id']) && isset($params['thread_id'])) $params['id'] = $params['thread_id'];
			// if(isset($params['thread_name'])) $params['name'] = $params['thread_name']; - not used in this config
			switch($route[1])
			{
				case 'new':
					return $base."forum_post.php?f=nt{$amp}id={$params['id']}";
					break;
		
				case 'reply':
					return $base."forum_post.php?f=rp{$amp}id={$params['id']}";
					break;
		
				case 'view':
					$page = (varset($params['page']) ? $amp.'p='.$params['page'] : '');
					return $base."forum_viewtopic.php?id={$params['id']}{$page}";
					break;
		
				case 'last':
					return $base."forum_viewtopic.php?id={$params['id']}{$amp}last=1";
					break;
		
				case 'post':
					return $base."forum_viewtopic.php?f=post{$amp}id={$params['id']}";
					break;
		
				case 'report':
					$page = (isset($params['page']) ? (int)$params['page'] : 0 );
					return $base."forum_viewtopic.php?f=report{$amp}id={$params['id']}{$amp}post={$params['post']}{$amp}p={$page}";
					break;
		
				case 'edit':
					return $base."forum_post.php?f=edit{$amp}id={$params['id']}{$amp}post={$params['post']}";
					break;
		
				case 'move':
					return $base."forum_conf.php?f=move{$amp}id={$params['id']}";
					break;
		
				case 'split':
					return $base."forum_conf.php?f=split{$amp}id={$params['id']}";
					break;
		
				case 'quote':
					return $base."forum_post.php?f=quote{$amp}id={$params['id']}{$amp}post={$params['post']}";
					break;
		
				case 'next':
					return $base."forum_viewtopic.php?f=next{$amp}id={$params['id']}";
					break;
		
				case 'prev':
					return $base."forum_viewtopic.php?f=prev{$amp}id={$params['id']}";
					break;
		
				case 'track':
					return $base."forum_viewtopic.php?f=track{$amp}id={$params['id']}";
					break;
		
				case 'untrack':
					return $base."forum_viewtopic.php?f=untrack{$amp}id={$params['id']}";
					break;
		
				case 'track_toggle':
					return $base."forum_viewtopic.php?f=track_toggle{$amp}id={$params['id']}";
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
		return false; // whole file deprecated.
	/*
		// static may be used for performance
		e107::plugLan('forum', 'lan_forum_url');
		static $admin = array(
			'labels' => array(
				'name' => LAN_PLUGIN_FORUM_NAME, // Module name
				'label' => FORUM_LAN_URL_DEFAULT_LABEL, // Current profile name
				'description' => FORUM_LAN_URL_DEFAULT_DESCR, //
				'examples'  => array("{e_PLUGIN_ABS}forum/forum_viewtopic.php?id=3&p=2")
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		*/
		return $admin;
	}
}
