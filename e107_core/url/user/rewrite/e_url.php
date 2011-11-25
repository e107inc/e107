<?php

class core_user_rewrite_url extends eUrlConfig
{
	public function config()
	{
		return array(
		
			'config' => array(
				'noSingleEntry' => false,	// [optional] default false; disallow this module to be shown via single entry point when this config is used
				'legacy' 		=> '{e_BASE}user.php', // [optional] default empty; if it's a legacy module (no single entry point support) - URL to the entry point script to be included
				'format'		=> 'path', 	// get|path - notify core for the current URL format, if set to 'get' rules will be ignored
				'selfParse' 	=> false,	// [optional] default false; use only this->parse() method, no core routine URL parsing
				'selfCreate' 	=> false,	// [optional] default false; use only this->create() method, no core routine URL creating
				'defaultRoute'	=> 'myprofile/view', // [optional] default empty; route (no leading module) used when module is found with no additional controller/action information e.g. /news/
				'errorRoute'	=> '', 		// [optional] default empty; route (no leading module) used when module is found but no inner route is matched, leave empty to force error 404 page
				'urlSuffix' 	=> '',		// [optional] default empty; string to append to the URL (e.g. .html)
				'mapVars' 		=> array(  // vars mapping (create URL)
					'user_id' => 'id', 
					'user_name' => 'name', 
				),
				'allowVars' 	=> false, // allowed vars (create URL, used for legacyQuery parsing in parse routine as well), false means - disallow all vars beside those required by the rules
				'legacyQuery' => '' // default legacy query string template, null to disable, override possible by rule
			),
			
			// rule set array
			'rules' => array(
				// simple matches first - PERFORMANCE
				'' 					=> array('myprofile/view', 'defaultVars' => array('id' => 0)),
				'Settings' 			=> array('myprofile/edit', 'defaultVars' => array('id' => 0), 'legacy' => '{e_BASE}usersettings.php'),
				'List' 				=> array('profile/list', 'allowVars' => array('page'), 'legacyQuery' => '{page}'),
				'Login' 			=> array('login/index', 'legacy' => '{e_BASE}login.php'),
				'Register' 			=> array('register/index', 'legacy' => '{e_BASE}signup.php'),
				
				// Regex involved next
				//'<id:[\d]+>' 		=> array('profile/view', 'legacyQuery' => 'id.{id}'),
				'Edit/<id:[\d]+>' 	=> array('profile/edit', 'legacy' => '{e_BASE}usersettings.php', 'legacyQuery' => '{id}'),
				
				// Named requests - important to be in the end in this order!
				'Edit/<name:[\w\pL.\-\s]+>' 	=> array('profile/edit', 'legacy' => '{e_BASE}usersettings.php', 'legacyQuery' => '{id}', 'parseCallback' => 'idByName'),
				// Last one - close to catch all!
				'<name:[\w\pL.\-\s]+>' 	=> array('profile/view', 'legacyQuery' => 'id.{id}', 'parseCallback' => 'idByName'),
			) 
		);
	}

	/**
	 * Admin callback
	 * Language file not loaded as all language data is inside the lan_eurl.php (loaded by default on administration URL page)
	 */
	public function admin()
	{
		// static may be used for performance
		static $admin = array(
			'labels' => array(
				'name' => LAN_EURL_CORE_USER, // Module name
				'label' => LAN_EURL_USER_REWRITE_LABEL, // Current profile name
				'description' => LAN_EURL_USER_REWRITE_DESCR, //
			),
			'form' => array(), // Under construction - additional configuration options
			'callbacks' => array(), // Under construction - could be used for e.g. URL generator functionallity
		);
		
		return $admin;
	}
	
	### CUSTOM METHODS ###
	
	/**
	 * profile/edit & profile/view callback
	 * @param eRequest $request
	 */
	public function idByName(eRequest $request)
	{
		$name = $request->getRequestParam('name');
		if(!$name) return;
		
		$sql = e107::getDb('url');
		$name = e107::getParser()->toDB($name);
		if($sql->db_Select('user', 'user_id', "user_name='{$name}'")) // XXX - new user_sef field? Discuss.
		{
			$name = $sql->db_Fetch();
			$request->setRequestParam('id', $name['user_id']);
		}
	}
}
