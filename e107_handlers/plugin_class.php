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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/plugin_class.php,v $
|     $Revision: 1.38 $
|     $Date: 2008-08-09 17:23:26 $
|     $Author: e107steved $

Mods for extra plugin.xml variables

+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class e107plugin
{
	var	$plugin_addons = array(
		"e_rss", 
		"e_notify", 
		"e_linkgen", 
		"e_list", 
		"e_bb", 
		"e_meta", 
		"e_emailprint", 
		"e_frontpage", 
		"e_latest", 
		"e_status", 
		"e_search", 
		"e_sc", 
		"e_module", 
		"e_comment", 
		"e_sql", 
		"e_userprofile",
		"e_header"
		);

	// List of all plugin variables which need to be checked - install required if one or more set and non-empty
	// Deprecated in 0.8 (used in plugin.php only). Probably delete in 0.9
	var $all_eplug_install_variables = array(
	'eplug_link_url',
	'eplug_link',
	'eplug_prefs',
	'eplug_array_pref',
	'eplug_table_names',
	'eplug_sc',
	'eplug_userclass',
	'eplug_module',
	'eplug_bb',
	'eplug_latest',
	'eplug_status',
	'eplug_comment_ids',
	'eplug_conffile',
	'eplug_menu_name'
	);

	// List of all plugin variables involved in an update (not used ATM, but worth 'documenting')
	// Deprecated in 0.8 (used in plugin.php only). Probably delete in 0.9
	var $all_eplug_update_variables = array (
	'upgrade_alter_tables',
	'upgrade_add_eplug_sc',
	'upgrade_remove_eplug_sc',
	'upgrade_add_eplug_bb',
	'upgrade_remove_eplug_bb',
	'upgrade_add_prefs',
	'upgrade_remove_prefs',
	'upgrade_add_array_pref',
	'upgrade_remove_array_pref'
	);

	// List of all 'editable' DB fields ('plugin_id' is an arbitrary reference which is never edited)
	var $all_editable_db_fields = array (
	'plugin_name',				// Name of the plugin - language dependent
	'plugin_version',			// Version - arbitrary text field
	'plugin_path',				// Name of the directory off e_PLUGIN - unique
	'plugin_installflag',		// '0' = not installed, '1' = installed
	'plugin_addons'				// List of any extras associated with plugin - bbcodes, e_XXX files...
	);

	var $plug_vars;
	var $current_plug;
	var $parsed_plugin;

	function e107plugin()
	{
		$parsed_plugin = array();
	}

	/**
	* Returns an array containing details of all plugins in the plugin table - should normally use e107plugin::update_plugins_table() first to
	* make sure the table is up to date. (Primarily called from plugin manager to get lists of installed and uninstalled plugins.
	* @return array plugin details
	*/
	function getall($flag)
	{

		global $sql;
		if ($sql->db_Select("plugin","*","plugin_installflag = ".(int)$flag." ORDER BY plugin_path ASC"))
		{
			$ret = $sql->db_getList();
			return $ret;
		}
		return false;
	}


	/**
	* Check for new plugins, create entry in plugin table and remove deleted plugins
	*/
	function update_plugins_table()
	{
		global $sql, $sql2, $mySQLprefix, $menu_pref, $tp, $pref;

		require_once(e_HANDLER.'file_class.php');

		$fl = new e_file;
		$pluginList = $fl->get_files(e_PLUGIN, "^plugin\.(php|xml)$", "standard", 1);
		$sp = FALSE;

		// Read all the plugin DB info into an array to save lots of accesses
		$pluginDBList = array();
		if ($sql->db_Select('plugin',"*"))
		{
			while ($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				$pluginDBList[$row['plugin_path']] = $row;
				$pluginDBList[$row['plugin_path']]['status'] = 'read';
				//			echo "Found plugin: ".$row['plugin_path']." in DB<br />";
			}
		}

		foreach($pluginList as $p)
		{
			$plug['plug_action'] = 'scan';			// Make sure plugin.php knows what we're up to
			if(!$this->parse_plugin($p['path']))
			{
				//parsing of plugin.php/plugin.xml failed.
			  echo "Parsing failed - file format error: {$p['path']}<br />";
			  continue;		// Carry on and do any others that are OK
			}
			$plug_info = $this->plug_vars;
			$plugin_path = substr(str_replace(e_PLUGIN,"",$p['path']),0,-1);


			// scan for addons.
			$eplug_addons = $this->getAddons($plugin_path);			// Returns comma-separated list
			//		  $eplug_addons = $this->getAddons($plugin_path,'check');		// Checks opening/closing tags on addon files

			//Ensure the plugin path lives in the same folder as is configured in the plugin.php/plugin.xml
			if ($plugin_path == $plug_info['folder'])
			{
				if(array_key_exists($plugin_path, $pluginDBList))
				{  // Update the addons needed by the plugin
					$pluginDBList[$plugin_path]['status'] = 'exists';
					// If plugin not installed, and version number of files changed, update version as well
					if (($pluginDBList[$plugin_path]['plugin_installflag'] == 0) && ($pluginDBList[$plugin_path]['plugin_version'] != $plug_info['version']))
					{  // Update stored version
						$pluginDBList[$plugin_path]['plugin_version'] = $plug_info['version'];
						$pluginDBList[$plugin_path]['status'] = 'update';
					}
					if ($pluginDBList[$plugin_path]['plugin_addons'] != $eplug_addons)
					{  // Update stored addons list
						$pluginDBList[$plugin_path]['plugin_addons'] = $eplug_addons;
						$pluginDBList[$plugin_path]['status'] = 'update';
					}

					if ($pluginDBList[$plugin_path]['plugin_installflag'] == 0)
					{  // Plugin not installed - make sure $pref not set
						if (isset($pref['plug_installed'][$plugin_path]))
						{
							unset($pref['plug_installed'][$plugin_path]);
							//				  echo "Remove: ".$plugin_path."->".$ep_row['plugin_version']."<br />";
							$sp = TRUE;
						}
					}
					else
					{	// Plugin installed - make sure $pref is set
						if (!isset($pref['plug_installed'][$plugin_path]) || ($pref['plug_installed'][$plugin_path] != $pluginDBList[$plugin_path]['plugin_version']))
						{	// Update prefs array of installed plugins
							$pref['plug_installed'][$plugin_path] = $pluginDBList[$plugin_path]['plugin_version'];
							//				  echo "Add: ".$plugin_path."->".$ep_row['plugin_version']."<br />";
							$sp = TRUE;
						}
					}
				}
				else
				{  // New plugin - not in table yet, so add it. If no install needed, mark it as 'installed'
					if ($plug_info['name'])
					{
//					  echo "New plugin to add: {$plug_info['name']}<br />";
						// Can just add to DB - shouldn't matter that its not in our current table
						//				echo "Trying to insert: ".$eplug_folder."<br />";
						$_installed = ($plug_info['installRequired'] == 'true' || $plug_info['installRequired'] == 1 ? 0 : 1 );
						$sql->db_Insert("plugin", "0, '".$tp -> toDB($plug_info['name'], true)."', '".$tp -> toDB($plug_info['version'], true)."', '".$tp -> toDB($plugin_path, true)."', {$_installed}, '{$eplug_addons}' ");
					}
				}
			}
			else
			{  // May be useful that we ignore what will usually be copies/backups of plugins - but don't normally say anything
//						    echo "Plugin copied to wrong directory. Is in: {$plugin_path} Should be: {$plug_info['folder']}<br /><br />";
			}
		}

		// Now scan the table, updating the DB where needed
		foreach ($pluginDBList as $plug_path => $plug_info)
		{
			if ($plug_info['status'] == 'read')
			{	// In table, not on server - delete it
				$sql->db_Delete('plugin', "`plugin_id`={$plug_info['plugin_id']}");
				//			echo "Deleted: ".$plug_path."<br />";
			}
			if ($plug_info['status'] == 'update')
			{
				$temp = array();
				foreach ($this->all_editable_db_fields as $p_f)
				{
					$temp[] ="`{$p_f}` = '{$plug_info[$p_f]}'";
				}
				$sql->db_Update('plugin', implode(", ",$temp)."  WHERE `plugin_id`={$plug_info['plugin_id']}");
				//			echo "Updated: ".$plug_path."<br />";
			}
		}
		if ($sp) { save_prefs(); }
	}



	/**
	* Returns details of a plugin from the plugin table from it's ID
	*
	* @param int $id
	* @return array plugin info
	*/
	function getinfo($id, $force=false)
	{
		global $sql;
		static $getinfo_results;
		if(!is_array($getinfo_results)) { $getinfo_results = array(); }

		$id = (int)$id;
		if(!isset($getinfo_results[$id]) || $force == true)
		{
			if ($sql->db_Select('plugin', '*', "plugin_id = ".$id))
			{
				$getinfo_results[$id] = $sql->db_Fetch();
			}
			else
			{
				return false;
			}
		}
		return $getinfo_results[$id];
	}



	function manage_userclass($action, $class_name, $class_description)
	{
		global $sql, $tp;
		$class_name = $tp -> toDB($class_name, true);
		$class_description = $tp -> toDB($class_description, true);
		if ($action == 'add')
		{
			$i = 1;
			while ($sql->db_Select('userclass_classes', '*', "userclass_id={$i} ") && $i < e_UC_READONLY)
			{
				$i++;
			}
			if ($i < e_UC_READONLY)
			{
				return $sql->db_Insert('userclass_classes', "{$i},'".strip_tags(strtoupper($class_name))."', '{$class_description}' ,".e_UC_PUBLIC);
			}
			else
			{
				return FALSE;
			}
		}
		if ($action == 'remove')
		{
			if ($sql->db_Select('userclass_classes', 'userclass_id', "userclass_name = '{$class_name}'"))
			{
				$row = $sql->db_Fetch();
				$class_id = $row['userclass_id'];
				if ($sql->db_Delete('userclass_classes', "userclass_id = {$class_id}"))
				{
					if ($sql->db_Select('user', 'user_id, user_class', "user_class REGEXP('^{$class_id}\.') OR user_class REGEXP('\.{$class_id}\.') OR user_class REGEXP('\.{$class_id}$')"))
					{
						$sql2 = new db;
						while ($row = $sql->db_Fetch())
						{
							$classes = explode(".", $row['user_class']);
							unset($classes[$class_id]);
							foreach($classes as $k => $v)
							{
								if ($v = '')
								{
									unset($classes[$k]);
								}
							}
							$newclass = '.'.implode('.', $classes).'.';
							$sql2->db_Update('user', "user_class = '{$newclass}' WHERE user_id = {$row['user_id']}");
						}
					}
				}
			}
		}
	}



	function manage_link($action, $link_url, $link_name, $link_class=0)
	{
		global $sql, $tp;

		if(!ctype_digit($link_class))
		{
			$link_class = strtolower($link_class);
			$plug_perm['everyone'] = e_UC_PUBLIC;
			$plug_perm['guest'] = e_UC_GUEST;
			$plug_perm['member'] = e_UC_MEMBER;
			$plug_perm['mainadmin'] = e_UC_MAINADMIN;
			$plug_perm['admin'] = e_UC_ADMIN;
			$plug_perm['nobody'] = e_UC_NOBODY;
			$link_class = ($plug_perm[$link_class]) ? $plug_perm[$link_class] : e_UC_PUBLIC;
		}

		$link_url = $tp -> toDB($link_url, true);
		$link_name = $tp -> toDB($link_name, true);
		$path = str_replace("../", "", $link_url);			// This should clean up 'non-standard' links
		$path = $tp->createConstants($path);				// Add in standard {e_XXXX} directory constants if we can
		if ($action == 'add')
		{
			$link_t = $sql->db_Count('links');
			if (!$sql->db_Count('links', '(*)', "WHERE link_url = '{$path}' OR link_name = '{$link_name}'"))
			{
				return $sql->db_Insert('links', "0, '{$link_name}', '{$path}', '', '', '1', '".($link_t + 1)."', '0', '0', '{$link_class}' ");
			}
			else
			{
				return FALSE;
			}
		}
		if ($action == 'remove')
		{	// Look up by URL if we can - should be more reliable. Otherwise try looking up by name (as previously)
			if (($path && $sql->db_Select('links', 'link_id,link_order', "link_url = '{$path}'")) ||
			$sql->db_Select('links', 'link_id,link_order', "link_name = '{$link_name}'"))
			{
				$row = $sql->db_Fetch();
				$sql->db_Update('links', "link_order = link_order - 1 WHERE link_order > {$row['link_order']}");
				return $sql->db_Delete('links', "link_id = {$row['link_id']}");
			}
		}
	}




	// Update prefs array according to $action
	// $prefType specifies the storage type - may be 'pref', 'listPref' or 'arrayPref'
	function manage_prefs($action, $var, $prefType = 'pref', $path = '')
	{
	  global $pref;
	  if (!is_array($var)) return;
	  if (($prefType == 'arrayPref') && ($path == '')) return;
	  foreach($var as $k => $v)
	  {
		switch ($prefType)
		{
		  case 'pref' :
			switch ($action)
			{
			  case 'add' :
				$pref[$k] = $v;
				break;

			  case 'update' :
				  // Only update if $pref doesn't exist
				  if (!isset($pref[$k])) $pref[$k] = $v;
				break;

			  case 'remove' :
				if (is_numeric($k))
				{	// Sometimes arrays specified with value being the name of the key to delete
				  unset($pref[$var[$k]]);
				}
				else
				{	// This is how the array should be specified - key is the name of the pref
				  unset($pref[$k]);
				}
				break;
			}
		  break;
		  case 'listPref' :
			$tmp = array();
			if (isset($pref[$k])) $tmp = explode(',',$pref[$k]);
			switch ($action)
			{
			  case 'add' :
			  case 'update' :
				if (!in_array($v,$tmp)) $tmp[] = $v;
				break;
			  case 'remove' :
				if (($tkey = array_search($v,$tmp)) !== FALSE) unset($tmp[$tkey]);
				break;
			}
			$pref[$k] = implode(',',$tmp);		// Leaves an empty element if no values - probably acceptable or even preferable
			break;
		  case 'arrayPref' :
			switch($action)
			{
			  case 'add' :
				$pref[$k][$path] = $v;
				break;
			  case 'update' :
				if (!isset($pref[$k][$path])) $pref[$k][$path] = $v;
				break;
			  case 'remove' :
				if (isset($pref[$k][$path])) unset($pref[$k][$path]);		// Leaves an empty element if no values - probably acceptable or even preferable
				break;
			}
			break;
		}
	  }
	  save_prefs();
	}




	function manage_comments($action, $comment_id)
	{
		global $sql, $tp;
		$tmp = array();
		if($action == 'remove')
		{
			foreach($comment_id as $com)
			{
				$tmp[] = "comment_type='".$tp -> toDB($com, true)."'";
			}
			$qry = implode(" OR ",$tmp);
			echo $qry."<br />";
			return $sql->db_Delete('comments', $qry);
		}
	}

	function manage_tables($action, $var)
	{
	  global $sql;
	  switch ($action)
	  {
		case 'add' :
		  if (is_array($var))
		  {
			foreach($var as $tab)
			{
			  if (!$sql->db_Query($tab))
			  {
				return FALSE;
			  }
			}
			return TRUE;
		  }
		  return TRUE;
		  break;
		case 'upgrade' :
			if (is_array($var))
			{
				foreach($var as $tab)
				{
					if (!$sql->db_Query_all($tab))
					{
						return FALSE;
					}
				}
				return TRUE;
			}
			return TRUE;
		  break;
		case 'remove' :
			if (is_array($var))
			{
				foreach($var as $tab)
				{
					$qry = 'DROP TABLE '.MPREFIX.$tab;
					if (!$sql->db_Query_all($qry))
					{
						return $tab;
					}
				}
				return TRUE;
			}
			return TRUE;
		  break;
	  }
	}



	// Handle prefs from arrays (mostly 0.7 stuff, possibly apart from the special cases)
	function manage_plugin_prefs($action, $prefname, $plugin_folder, $varArray = '')
	{  // These prefs are 'cumulative' - several plugins may contribute an array element
		global $pref;
		if ($prefname == 'plug_sc' || $prefname == 'plug_bb')
		{  // Special cases - shortcodes and bbcodes - each plugin may contribute several elements
			foreach($varArray as $code)
			{
				$prefvals[] = "$code:$plugin_folder";
			}
		}
		else
		{
			$prefvals[] = $varArray;
			//			$prefvals[] = $plugin_folder;
		}
		$curvals = explode(',', $pref[$prefname]);

		if ($action == 'add')
		{
			$newvals = array_merge($curvals, $prefvals);
		}
		if ($action == 'remove')
		{
			foreach($prefvals as $v)
			{
				if (($i = array_search($v, $curvals)) !== FALSE)
				{
					unset($curvals[$i]);
				}
			}
			$newvals = $curvals;
		}
		$newvals = array_unique($newvals);
		$pref[$prefname] = implode(',', $newvals);

		if(substr($pref[$prefname], 0, 1) == ",")
		{
			$pref[$prefname] = substr($pref[$prefname], 1);
		}
		save_prefs();
	}




	function manage_search($action, $eplug_folder)
	{
		global $sql, $sysprefs;
		$search_prefs = $sysprefs -> getArray('search_prefs');
		$default = file_exists(e_PLUGIN.$eplug_folder.'/e_search.php') ? TRUE : FALSE;
		$comments = file_exists(e_PLUGIN.$eplug_folder.'/search/search_comments.php') ? TRUE : FALSE;
		if ($action == 'add')
		{
			$install_default = $default ? TRUE : FALSE;
			$install_comments = $comments ? TRUE : FALSE;
		}
		else if ($action == 'remove')
		{
			$uninstall_default = isset($search_prefs['plug_handlers'][$eplug_folder]) ? TRUE : FALSE;
			$uninstall_comments = isset($search_prefs['comments_handlers'][$eplug_folder]) ? TRUE : FALSE;
		}
		else if ($action == 'upgrade')
		{
			if (isset($search_prefs['plug_handlers'][$eplug_folder]))
			{
				$uninstall_default = $default ? FALSE : TRUE;
			}
			else
			{
				$install_default = $default ? TRUE : FALSE;
			}
			if (isset($search_prefs['comments_handlers'][$eplug_folder]))
			{
				$uninstall_comments = $comments ? FALSE : TRUE;
			}
			else
			{
				$install_comments = $comments ? TRUE : FALSE;
			}
		}
		if ($install_default)
		{
			$search_prefs['plug_handlers'][$eplug_folder] = array('class' => 0, 'pre_title' => 1, 'pre_title_alt' => '', 'chars' => 150, 'results' => 10);
		}
		else if ($uninstall_default)
		{
			unset($search_prefs['plug_handlers'][$eplug_folder]);
		}
		if ($install_comments)
		{
			require_once(e_PLUGIN.$eplug_folder.'/search/search_comments.php');
			$search_prefs['comments_handlers'][$eplug_folder] = array('id' => $comments_type_id, 'class' => 0, 'dir' => $eplug_folder);
		}
		else if ($uninstall_comments)
		{
			unset($search_prefs['comments_handlers'][$eplug_folder]);
		}
		$tmp = addslashes(serialize($search_prefs));
		$sql->db_Update("core", "e107_value = '{$tmp}' WHERE e107_name = 'search_prefs' ");
	}

	function manage_notify($action, $eplug_folder)
	{
		global $sql, $sysprefs, $eArrayStorage, $tp;
		$notify_prefs = $sysprefs -> get('notify_prefs');
		$notify_prefs = $eArrayStorage -> ReadArray($notify_prefs);
		$e_notify = file_exists(e_PLUGIN.$eplug_folder.'/e_notify.php') ? TRUE : FALSE;
		if ($action == 'add')
		{
			$install_notify = $e_notify ? TRUE : FALSE;
		}
		else if ($action == 'remove')
		{
			$uninstall_notify = isset($notify_prefs['plugins'][$eplug_folder]) ? TRUE : FALSE;
		}
		else if ($action == 'upgrade')
		{
			if (isset($notify_prefs['plugins'][$eplug_folder]))
			{
				$uninstall_notify = $e_notify ? FALSE : TRUE;
			}
			else
			{
				$install_notify = $e_notify ? TRUE : FALSE;
			}
		}
		if ($install_notify)
		{
			$notify_prefs['plugins'][$eplug_folder] = TRUE;
			require_once(e_PLUGIN.$eplug_folder.'/e_notify.php');
			foreach ($config_events as $event_id => $event_text)
			{
				$notify_prefs['event'][$event_id] = array('type' => 'off', 'class' => '254', 'email' => '');
			}
		}
		else if ($uninstall_notify)
		{
			unset($notify_prefs['plugins'][$eplug_folder]);
			require_once(e_PLUGIN.$eplug_folder.'/e_notify.php');
			foreach ($config_events as $event_id => $event_text)
			{
				unset($notify_prefs['event'][$event_id]);
			}
		}
		$s_prefs = $tp -> toDB($notify_prefs);
		$s_prefs = $eArrayStorage->WriteArray($s_prefs);
		$sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'");
	}



	//----------------------------------------------------------
	//		Install routine for XML file
	//----------------------------------------------------------
	function manage_plugin_xml($id, $function='')
	{
		global $sql, $pref;

		$error = array();			// Array of error messages
		$canContinue = TRUE;		// Clear flag if must abort part way through

		$id = (int)$id;
		$plug = $this->getinfo($id);			// Get plugin info from DB
		$this->current_plug = $plug;
		$txt = '';
		$path = e_PLUGIN.$plug['plugin_path'].'/';


		$addons = explode(',', $plug['plugin_addons']);
		$sql_list = array();
		foreach($addons as $addon)
		{
			if(substr($addon, -4) == '_sql')
			{
				$sql_list[] = $addon.'.php';
			}
		}

		if(!file_exists($path.'plugin.xml') || $function == '')
		{
		  $error[] = EPL_ADLAN_77;
		  $canContinue = FALSE;
		}


		if($canContinue && $this->parse_plugin_xml($path))
		{
		  $plug_vars = $this->plug_vars;
		}
		else
		{
		  $error[] = EPL_ADLAN_76;
		  $canContinue = FALSE;
		}

		// First of all, if installing or upgrading, check that any dependencies are met
		if ($canContinue && ($function != 'uninstall') && isset($plug_vars['depends']))
		{
		  foreach ($plug_vars['depends'] as $dt => $dv)
		  {
			if (isset($dv['@attributes']) && isset($dv['@attributes']['name']))
			{
			  switch ($dt)
			  {
				case 'plugin' :
//				  echo "Check plugin dependency: {$dv['@attributes']['name']} version {$dv['@attributes']['min_version']}<br />";
				  if (!isset($pref['plug_installed'][$dv['@attributes']['name']])) 
				  { // Plugin not installed
					$canContinue = FALSE;
					$error[] = EPL_ADLAN_70.$dv['@attributes']['name'];
				  }
				  elseif (isset($dv['@attributes']['min_version']) && (version_compare($dv['@attributes']['min_version'],$pref['plug_installed'][$dv['@attributes']['name']],'<=') === FALSE))
				  {
					$error[] = EPL_ADLAN_71.$dv['@attributes']['name'].EPL_ADLAN_72.$dv['@attributes']['min_version'];
					$canContinue = FALSE;
				  }
				  break;
				case 'extension' :
				  if (!extension_loaded($dv['@attributes']['name']))
				  {
					$canContinue = FALSE;
					$error[] = EPL_ADLAN_73.$dv['@attributes']['name'];
				  }
				  elseif (isset($dv['@attributes']['min_version']) && (version_compare($dv['@attributes']['min_version'],phpversion($dv['@attributes']['name']),'<=') === FALSE))
				  {
					$error[] = EPL_ADLAN_71.$dv['@attributes']['name'].EPL_ADLAN_72.$dv['@attributes']['min_version'];
					$canContinue = FALSE;
				  }
				  break;
				case 'PHP' :
				  if (isset($dv['@attributes']['min_version']) && (version_compare($dv['@attributes']['min_version'],phpversion(),'<=') === FALSE))
				  {
					$error[] = EPL_ADLAN_74.$dv['@attributes']['min_version'];
					$canContinue = FALSE;
				  }
				  break;
				case 'MySQL' :
				  if (isset($dv['@attributes']['min_version']) && (version_compare($dv['@attributes']['min_version'],mysql_get_server_info(),'<=') === FALSE))
				  {
					$error[] = EPL_ADLAN_75.$dv['@attributes']['min_version'];
					$canContinue = FALSE;
				  }
				  break;
				default :
				  echo "Unknown dependency: {$dt}<br />";
			  }
			}
		  }
		}

		// Temporary error handling - need to do something with this
		if (!$canContinue)
		{
		  echo implode('<br />',$error);
		  return false;
		}


		// All the dependencies are OK - can start the install now

		// Let's call any custom pre functions defined in <management> section
		$txt .= $this->execute_function($path, $function, 'pre');

		// tables
		// This will load each _sql.php file found in the plugin directory and parse it.
		if(($function == 'install' || $function == 'uninstall') && count($sql_list))
		{
			foreach($sql_list as $sql_file)
			{
				if($sql_data = file_get_contents($path.$sql_file))
				{
					preg_match_all("/create(.*?)myisam.*?;/si", $sql_data, $result );
					foreach ($result[0] as $sql_table)
					{
						preg_match("/CREATE TABLE(.*?)\(/si", $sql_table, $match);
						$tablename = trim($match[1]);

						if($function == 'uninstall' && isset($_POST['delete_tables']) && $_POST['delete_tables'])
						{
							$txt .= "Removing table $tablename <br />";
							$this->manage_tables('remove', array($tablename));
						}
						if($function == 'install')
						{
							$sql_table = preg_replace("/create table\s+/si", "CREATE TABLE ".MPREFIX, $sql_table);
							$txt .= "Adding table: {$tablename} ... ";
							$result = $this->manage_tables('add', array($sql_table));
							$txt .= ($result ? "Success" : "Failed!")."<br />";
						}
					}
				}
			}
		}

		//main menu items
		if(isset($plug_vars['menuLink']))
		{
			//Ensure it is an array for use with foreach()
			if(!is_array($plug_vars['menuLink']))
			{
				$plug_vars['menuLink'] = array($plug_vars['menuLink']);
			}
			foreach($plug_vars['menuLink'] as $link)
			{
				$attrib = $link['@attributes'];
				switch($function)
				{
				  case 'upgrade':
				  case 'install':
					// Add any active link
					if(!isset($attrib['active']) || $attrib['active'] == 'true')
					{
						$perm = (isset($attrib['perm']) ? $attrib['perm'] : 0);
						$txt .= "Adding link {$attrib['name']} with url [{$attrib['url']}] and perm {$perm} <br />";
						$this->manage_link('add', $attrib['url'], $attrib['name'], $perm);
					}
					//remove inactive links on upgrade
					if($function == 'upgrade' && isset($attrib['active']) && $attrib['active'] == 'false')
					{
						$txt .= "Removing link {$attrib['name']} with url [{$attrib['url']}] <br />";
						$this->manage_link('remove', $attrib['url'], $attrib['name']);
					}
					break;

				  case 'uninstall':
					//remove all links
					$txt .= "Removing link {$attrib['name']} with url [{$attrib['url']}] <br />";
					$this->manage_link('remove', $attrib['url'], $attrib['name']);
					break;
				}
			}
		}

		//main pref items
		if(isset($plug_vars['mainPrefs']))
		{
		  foreach (array('pref','listPref','arrayPref') as $prefType)
		  {
			if(isset($plug_vars['mainPrefs'][$prefType]))
			{
			  $list = $this->parse_prefs($plug_vars['mainPrefs'][$prefType]);
			  switch($function)
			  {
				case 'install':
					if(is_array($list['active']))
					{
						$txt .= "Adding {$prefType} prefs ".print_a($list['active'], true)."<br />";
						$this->manage_prefs('add', $list['active'], $prefType, $plug['plugin_path']);
					}
					break;
				case 'upgrade':
					if(is_array($list['active']))
					{
						$txt .= "Updating {$prefType} prefs ".print_a($list['active'], true)."<br />";
						$this->manage_prefs('update', $list['active'], $prefType, $plug['plugin_path']);		// This only adds prefs which aren't already defined
					}

					//If upgrading, removing any inactive pref
					if(is_array($list['inactive']))
					{
						$txt .= "Removing {$prefType} prefs ".print_a($list['inactive'], true)."<br />";
						$this->manage_prefs('remove', $list['inactive'], $prefType, $plug['plugin_path']);
					}
					break;

					//If uninstalling, remove all prefs (active or inactive)
				case 'uninstall':
					if(is_array($list['all']))
					{
						$txt .= "Removing {$prefType} prefs ".print_a($list['all'], true)."<br />";
						$this->manage_prefs('remove', $list['all'], $prefType, $plug['plugin_path']);
					}
					break;
			  }
			}
		  }
		}

		//Userclasses
		//$this->manage_userclass('add', $eplug_userclass, $eplug_userclass_description);
		if(isset($plug_vars['userclass']))
		{
			$uclass_list = (isset($plug_vars['userclass'][0]) ? $plug_vars['userclass'] : array($plug_vars['userclass']));
			foreach($uclass_list as $uclass)
			{
				$attrib = $uclass['@attributes'];
				switch($function)
				{
					case 'install':
					case 'upgrade':
					// Add all active userclasses						
					if(!isset($attrib['active']) || $attrib['active'] == 'true')
					{
						$txt .= "Adding userclass ".$attrib['name']."<br />";
						$this->manage_userclass('add', $attrib['name'], $attrib['description']);
					}

					//If upgrading, removing any inactive userclass
					if($function == 'upgrade' && isset($attrib['active']) && $attrib['active'] == 'false')
					{
						$txt .= "Removing userclass ".$attrib['name']."<br />";
						$this->manage_userclass('remove', $attrib['name'], $attrib['description']);
					}
					break;

					//If uninstalling, remove all userclasses (active or inactive)
					case 'uninstall':
					$txt .= "Removing userclass ".$attrib['name']."<br />";
					$this->manage_userclass('remove', $attrib['name'], $attrib['description']);
					break;
				}
			}
		}
		
		//If any commentIDs are configured, we need to remove all comments on uninstall
		if($function == 'uninstall' && isset($plug_vars['commentID']))
		{
			$commentArray = (is_array($plug_vars['commentID']) ? $plug_vars['commentID'] : array($plug_vars['commentID']));
			$txt .= "Removing all plugin comments: (".implode(', ', $commentArray).")<br />";
			$this->manage_comments('remove', $commentArray);
		}

		$this->manage_search($function, $plug_vars['folder']);
		$this->manage_notify($function, $plug_vars['folder']);


		// Now any 'custom' values for update, language file definitions etc
		if (isset($plug_vars['management']['upgradeCheck']))
		{
		  $tmp = $plug_vars['management']['upgradeCheck'];
		  switch ($function)
		  {
		    case 'install' :
			case 'upgrade' :
			  $pref['upgradeCheck'][$plug['plugin_path']]['url'] = $tmp['@attributes']['url'];
			  $pref['upgradeCheck'][$plug['plugin_path']]['method'] = varset($tmp['@attributes']['method'],'sf_news');
			  break;
			case 'uninstall' :
			  unset($pref['upgradeCheck'][$plug['plugin_path']]);
			  break;
		  }
		  unset($tmp);
		}

		if (isset($plug_vars['logLanguageFile']))
		{
		  switch ($function)
		  {
		    case 'install' :
			case 'upgrade' :
			  $pref['logLanguageFile'][$plug['plugin_path']] = $plug_vars['logLanguageFile']['@attributes']['filename'];
			  break;
			case 'uninstall' :
			  unset($pref['logLanguageFile'][$plug['plugin_path']]);
			  break;
		  }
		}
		save_prefs();


		// Let's call any custom post functions defined in <management> section
		$txt .= $this->execute_function($path, $function, 'post');


		$eplug_addons = $this->getAddons($plug['plugin_path']);

		if($function == 'install' || $function == 'upgrade')
		{
			$sql->db_Update('plugin', "plugin_installflag = 1, plugin_addons = '{$eplug_addons}', plugin_version = '{$plug_vars['version']}' WHERE plugin_id = ".$id);
			$pref['plug_installed'][$plug['plugin_path']] = $plug_vars['version'];
			save_prefs();
		}

		if($function == 'uninstall')
		{
			$sql->db_Update('plugin', "plugin_installflag = 0, plugin_addons = '{$eplug_addons}', plugin_version = '{$plug_vars['version']}' WHERE plugin_id = ".$id);
			unset($pref['plug_installed'][$plug['plugin_path']]);
			save_prefs();
		}

		if($function == 'install')
		{
			$txt .= LAN_INSTALL_SUCCESSFUL."<br />";
			if(isset($plug_vars['installDone']))
			{
				$txt .= $plug_vars['installDone'];
			}
		}
		return $txt;
	}

	function execute_function($path = '', $what='', $when='')
	{
		if($what == '' || $when == '') { return true; }
		if(!isset($this->plug_vars['management'][$what])) { return true; }
		$vars = $this->plug_vars['management'][$what];
		if(!is_array($vars)) { $vars = array($vars); }
		foreach($vars as $var)
		{
			$attrib = $var['@attributes'];
			if(isset($attrib['when']) && $attrib['when'] == $when)
			{
				if(is_readable($path.$attrib['file']))
				{
					include_once($path.$attrib['file']);
					if($attrib['type'] == 'fileFunction')
					{
						$result = call_user_func($attrib['function'], $this);
						return $result;
					}	
					elseif($attrib['type'] == 'classFunction')
					{
						$_tmp = new $attrib['class'];
						$result = call_user_func(array($_tmp, $attrib['function']), $this);
						return $result;
					}
				}
			}
		}
	}

	function parse_prefs($pref_array)
	{
		$ret = array();
		if(!isset($pref_array[0]))
		{
			$pref_array = array($pref_array);
		}
		foreach($pref_array as $k => $p)
		{
			$attrib = $p['@attributes'];
			if(isset($attrib['type']) && $attrib['type'] == 'array')
			{
				$name = $attrib['name'];
				$tmp = $this->parse_prefs($pref_array[$k]['key']);
				$ret['all'][$name] = $tmp['all'];
				$ret['active'][$name] = $tmp['active'];
				$ret['inactive'][$name] = $tmp['inactive'];
			}
			else
			{
				$ret['all'][$attrib['name']] = $attrib['value'];
				if(!isset($attrib['active']) || $attrib['active'] == 'true')
				{
					$ret['active'][$attrib['name']] = $attrib['value'];
				}
				else
				{
					$ret['inactive'][$attrib['name']] = $attrib['value'];
				}
			}
		}
		return $ret;
	}
	
	function install_plugin_php($id)
	{
		global $sql;

		$plug = $this->getinfo($id);
		$_path = e_PLUGIN.$plug['plugin_path'].'/';

		$plug['plug_action'] = 'install';

		//	$plug_vars = $this->parse_plugin_php($path);
		include_once($_path.'plugin.php');

		$func = $eplug_folder.'_install';
		if (function_exists($func))
		{
			$text .= call_user_func($func);
		}

		if (is_array($eplug_tables))
		{
			$result = $this->manage_tables('add', $eplug_tables);
			if ($result === TRUE)
			{
				$text .= EPL_ADLAN_19.'<br />';
				//success
			}
			else
			{
				$text .= EPL_ADLAN_18.'<br />';
				//fail
			}
		}

		if (is_array($eplug_prefs))
		{
			$this->manage_prefs('add', $eplug_prefs);
			$text .= EPL_ADLAN_8.'<br />';
		}

		if (is_array($eplug_array_pref))
		{
			foreach($eplug_array_pref as $key => $val)
			{
				$this->manage_plugin_prefs('add', $key, $eplug_folder, $val);
			}
		}

		if (is_array($eplug_sc))
		{
			$this->manage_plugin_prefs('add', 'plug_sc', $eplug_folder, $eplug_sc);
		}

		if (is_array($eplug_bb))
		{
			$this->manage_plugin_prefs('add', 'plug_bb', $eplug_folder, $eplug_bb);
		}

		if ($eplug_link === TRUE && $eplug_link_url != '' && $eplug_link_name != '')
		{
			$linkperm = (isset($eplug_link_perms) ? $eplug_link_perms : e_UC_PUBLIC);
			$this->manage_link('add', $eplug_link_url, $eplug_link_name, $linkperm);
		}

		if ($eplug_userclass)
		{
			$this->manage_userclass('add', $eplug_userclass, $eplug_userclass_description);
		}

		$this -> manage_search('add', $eplug_folder);

		$this -> manage_notify('add', $eplug_folder);

		$eplug_addons = $this->getAddons($eplug_folder);

		$sql->db_Update('plugin', "plugin_installflag = 1, plugin_addons = '{$eplug_addons}' WHERE plugin_id = ".(int)$id);
		$pref['plug_installed'][$plugin_path] = $plug['plugin_version'];
		save_prefs();

		if($rssmess)
		{
			$text .= $rssmess;
		}
		$text .= (isset($eplug_done) ? "<br />{$eplug_done}" : "<br />".LAN_INSTALL_SUCCESSFUL);

		return $text;
	}


	/**
	* Installs a plugin by ID
	*
	* @param int $id
	*/
	function install_plugin($id)
	{
		global $sql, $ns, $sysprefs, $mySQLprefix, $tp;

		// install plugin ...
		$id = (int)$id;
		$plug = $this->getinfo($id);
		$plug['plug_action'] = 'install';
	
		if ($plug['plugin_installflag'] == FALSE)
		{
			$_path = e_PLUGIN.$plug['plugin_path'].'/';
			if(file_exists($_path.'plugin.xml'))
			{
				$text = $this->manage_plugin_xml($id, 'install');
			}
			elseif(file_exists($_path.'plugin.php'))
			{
				$text = $this->install_plugin_php($id);
			}
		}
		else
		{
			$text = EPL_ADLAN_21;
		}
		return $text;
	}

	function save_addon_prefs()
	{  // scan the plugin table and create path-array-prefs for each addon.
		global $sql,$pref;
		//        $query = "SELECT * FROM #plugin WHERE plugin_installflag = 1 AND plugin_addons !='' ORDER BY plugin_path ASC";
		$query = "SELECT * FROM #plugin WHERE plugin_addons !='' ORDER BY plugin_path ASC";

		// clear all addon prefs before re-creation.
		unset($pref['shortcode_list'],$pref['bbcode_list'],$pref['e_sql_list']);
		foreach($this->plugin_addons as $plg)
		{
			unset($pref[$plg."_list"]);
		}

		if ($sql -> db_Select_gen($query))
		{
			while($row = $sql-> db_Fetch())
			{
				$is_installed = ($row['plugin_installflag'] == 1 );
				$tmp = explode(",",$row['plugin_addons']);
				$path = $row['plugin_path'];

				if ($is_installed)
				{
					foreach($tmp as $val)
					{
						if(strpos($val, 'e_') === 0)
						{
							$pref[$val."_list"][$path] = $path;
						}
					}
				}
				// search for .bb and .sc files.
				$sc_array = array();
				$bb_array = array();
				$sql_array = array();

				foreach($tmp as $adds)
				{
					if(substr($adds,-3) == ".sc")
					{
						$sc_name = substr($adds, 0,-3);  // remove the .sc
						if ($is_installed)
						{
							$sc_array[$sc_name] = "0"; // default userclass = e_UC_PUBLIC
						}
						else
						{
							$sc_array[$sc_name] = e_UC_NOBODY; // register shortcode, but disable it
						}
					}

					if($is_installed && (substr($adds,-3) == ".bb"))
					{
						$bb_name = substr($adds, 0,-3); // remove the .bb
						$bb_array[$bb_name] = "0"; // default userclass.
					}

					if($is_installed && (substr($adds,-4) == "_sql"))
					{
						$pref['e_sql_list'][$path] = $adds;
					}
				}

				// Build Bbcode list (will be empty if plugin not installed)
				if(count($bb_array) > 0)
				{
					ksort($bb_array);
					$pref['bbcode_list'][$path] = $bb_array;

				}
				else
				{
					if (isset($pref['bbcode_list'][$path])) unset($pref['bbcode_list'][$path]);
				}

				// Build shortcode list - do if uninstalled as well
				if(count($sc_array) > 0)
				{
					ksort($sc_array);
					$pref['shortcode_list'][$path] = $sc_array;
				}
				else
				{
					if(isset($pref['shortcode_list'][$path])) unset($pref['shortcode_list'][$path]);
				}

			}
		}

		save_prefs();
		return;
	}

	// return a list of available plugin addons for the specified plugin. e_xxx etc.
	// $debug = TRUE - prints diagnostics
	// $debug = 'check' - checks each file found for php tags - prints 'pass' or 'fail'
	function getAddons($plugin_path, $debug=FALSE)
	{
		global $fl;
		if(!is_object($fl)){
			require_once(e_HANDLER.'file_class.php');
			$fl = new e_file;
		}
		$p_addons = array();
		$addonlist = $fl->get_files(e_PLUGIN.$plugin_path, "^e_.*\.php$", "standard", 1);
		//		print_a($addonlist);
		foreach($addonlist as $f)
		{
			if(preg_match("#^(e_.*)\.php$#", $f['fname'], $matches))
			{
				$addon = $matches[1];
				if(is_readable(e_PLUGIN.$plugin_path."/".$f['fname']))
				{
					if ($debug === 'check')
					{
						$passfail = '';
						$file_text = file_get_contents(e_PLUGIN.$plugin_path."/".$f['fname']);
						if ((substr($file_text,0,5) != '<'.'?php') || (substr($file_text,-2,2) !='?>'))
						{
							$passfail = '<b>fail</b>';
						}
						else
						{
							$passfail = 'pass';
						}
						echo $plugin_path."/".$addon.".php - ".$passfail."<br />";
					}
					$p_addons[] = $addon;
				}
			}
		}

		// Grab List of Shortcodes & BBcodes
		$shortcodeList	= $fl->get_files(e_PLUGIN.$plugin_path, ".sc$", "standard", 1);
		$bbcodeList		= $fl->get_files(e_PLUGIN.$plugin_path, ".bb$", "standard", 1);
		$sqlList		= $fl->get_files(e_PLUGIN.$plugin_path, "_sql.php$", "standard", 1);

		// Search Shortcodes
		foreach($shortcodeList as $sc)
		{
			if(is_readable(e_PLUGIN.$plugin_path."/".$sc['fname']))
			{
				$p_addons[] = $sc['fname'];
			}
		}

		// Search Bbcodes.
		foreach($bbcodeList as $bb)
		{
			if(is_readable(e_PLUGIN.$plugin_path."/".$bb['fname']))
			{
				$p_addons[] = $bb['fname'];
			}
		}

		// Search _sql files.
		foreach($sqlList as $esql)
		{
			if(is_readable(e_PLUGIN.$plugin_path."/".$esql['fname']))
			{
			  $fname = str_replace(".php","",$esql['fname']);
			  if (!in_array($fname, $p_addons)) $p_addons[] = $fname;		// Probably already found - avoid duplication
			}
		}

		if($debug == true)
		{
			echo $plugin_path." = ".implode(",",$p_addons)."<br />";
		}

		return implode(",",$p_addons);
	}

	function checkAddon($plugin_path,$e_xxx)
	{ // Return 0 = OK, 1 = Fail, 2 = inaccessible
		if(is_readable(e_PLUGIN.$plugin_path."/".$e_xxx.".php"))
		{
			$file_text = file_get_contents(e_PLUGIN.$plugin_path."/".$e_xxx.".php");
			if ((substr($file_text,0,5) != '<'.'?php') || (substr($file_text,-2,2) !='?>')) return 1;
			return 0;
		}
		return 2;
	}


	// Entry point to read plugin configuration data
	function parse_plugin($path, $force=false)
	{
		if(isset($this->parsed_plugin[$path]) && $force != true)
		{
			$this->plug_vars = $this->parsed_plugin[$path];
			return true;
		}
		if(file_exists($path.'plugin.xml'))
		{
			$ret = $this->parse_plugin_xml($path);
		}
		elseif(file_exists($path.'plugin.php'))
		{
			$ret = $this->parse_plugin_php($path);
		}
		if($ret == true)
		{
			$this->parsed_plugin[$path] = $this->plug_vars;
		}
		return $ret;
	}


	// Called to parse the (deprecated) plugin.php file
	function parse_plugin_php($path)
	{
		include($path.'plugin.php');
		$ret = array();

		$ret['installRequired'] = ($eplug_conffile || is_array($eplug_table_names) || is_array($eplug_prefs) || is_array($eplug_sc) || is_array($eplug_bb) || $eplug_module || $eplug_userclass || $eplug_status || $eplug_latest);

		$ret['version'] = varset($eplug_version);
		$ret['name'] = varset($eplug_name);
		$ret['folder'] = varset($eplug_folder);
		$ret['description'] = varset($eplug_description);
		$ret['author'] = varset($eplug_author);
		$ret['authorUrl'] = varset($eplug_url);
		$ret['authorEmail'] = varset($eplug_email);
		$ret['compatibility'] = varset($eplug_compatible);
		$ret['readme'] = varset($eplug_readme);
		$ret['compliant'] = varset($eplug_compliant);
		$ret['menuName'] = varset($eplug_menu_name);

		$ret['administration']['icon'] = varset($eplug_icon);
		$ret['administration']['caption'] = varset($eplug_caption);
		$ret['administration']['iconSmall'] = varset($eplug_icon_small);
		$ret['administration']['configFile'] = varset($eplug_conffile);

		// Set this key so we know the vars came from a plugin.php file
		$ret['plugin_php'] = true;
		$this->plug_vars = $ret;
		return true;
	}



	// Called to parse the plugin.xml file if it exists
	function parse_plugin_xml($path)
	{
		global $tp;
		include_lan($path.'languages/'.e_LANGUAGE.'/lan_config.php');
		include_lan($path.'languages/admin/'.e_LANGUAGE.'.php');
		require_once(e_HANDLER.'xml_class.php');
		$xml = new xmlClass;
		$this->plug_vars = $xml->loadXMLfile($path.'plugin.xml', true, true);
		if ($this->plug_vars === FALSE) return FALSE;
//		print_a($this->plug_vars);
//		$xml->loadXMLfile($path.'plugin.xml', true, true);
//		$xml->xmlFileContents = $tp->replaceConstants($xml->xmlFileContents, '', true);
//		$this->plug_vars = $xml->parseXml();
		return true;
	}

}

?>