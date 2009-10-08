<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/plugin_class.php,v $
|     $Revision: 1.100 $
|     $Date: 2009-10-08 14:47:54 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_plugin.php");

class e107plugin
{
	// Reserved Addon names. 
	var	$plugin_addons = array(
		'e_rss',
		'e_notify',
		'e_linkgen',
		'e_list',
		'e_bb',
		'e_meta',
		'e_emailprint',
		'e_frontpage',
		'e_latest',
		'e_status',
		'e_search',
		'e_shortcode',
		'e_module',
		'e_event',
		'e_comment',
		'e_sql',
		'e_userprofile',
		'e_header',
		'e_userinfo',
		'e_tagwords'
		);

	// List of all plugin variables which need to be checked - install required if one or more set and non-empty
	// Deprecated in 0.8 (used in plugin.php only). Probably delete in 0.9
	var $all_eplug_install_variables = array(
		'eplug_link_url',
		'eplug_link',
		'eplug_prefs',
		'eplug_array_pref',
		'eplug_table_names',
	//	'eplug_sc',				// Not used in 0.8 (or later 0.7)
		'eplug_userclass',
		'eplug_module',
	//	'eplug_bb',				// Not used in 0.8 (or later 0.7)
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
	//	'upgrade_add_eplug_sc',				// Not used in 0.8 (or later 0.7)
	//	'upgrade_remove_eplug_sc',			// Not used in 0.8 (or later 0.7)
	//	'upgrade_add_eplug_bb',				// Not used in 0.8 (or later 0.7)
	//	'upgrade_remove_eplug_bb',			// Not used in 0.8 (or later 0.7)
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
		'plugin_addons',				// List of any extras associated with plugin - bbcodes, e_XXX files...
		'plugin_category'				// Plugin Category: settings, users, content, management, tools, misc, about
	);

	var $accepted_categories = array('settings', 'users', 'content', 'tools', 'manage', 'misc', 'menu', 'about');

	var $plug_vars;
	var $current_plug;
	var $parsed_plugin;
	var $plugFolder;
	var $unInstallOpts;
	var $module = array();

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
		$sql = e107::getDb();
		
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
				
		$sql = e107::getDb();
		$sql2 = e107::getDb('sql2');
		$tp = e107::getParser();
		$fl = e107::getFile();
		
		global $mySQLprefix, $menu_pref, $pref;

		$sp = FALSE;
				
		$pluginDBList = array();
		if ($sql->db_Select('plugin',"*")) // Read all the plugin DB info into an array to save lots of accesses
		{
			while ($row = $sql->db_Fetch(MYSQL_ASSOC))
			{				
				$pluginDBList[$row['plugin_path']] = $row;
				$pluginDBList[$row['plugin_path']]['status'] = 'read';
				//	echo "Found plugin: ".$row['plugin_path']." in DB<br />";
			}
		}

		$plugList = $fl->get_files(e_PLUGIN, "^plugin\.(php|xml)$", "standard", 1);
		foreach($plugList as $num=>$val) // Remove Duplicates caused by having both plugin.php AND plugin.xml.  
		{
			$key = basename($val['path']);
			$pluginList[$key] =$val;
		}
		
		$p_installed = e107::getPref('plug_installed',array()); // load preference; 
		require_once(e_HANDLER."message_handler.php");
		$emessage = eMessage::getInstance();

		foreach($pluginList as $p)
		{
			$p['path'] = substr(str_replace(e_PLUGIN,"",$p['path']),0,-1);
			$plugin_path = $p['path'];	

			if(strpos($plugin_path,'e107_')!==FALSE)
			{
				$emessage->add("Folder error: <i>{$p['path']}</i>.  'e107_' is not permitted within plugin folder names.", E_MESSAGE_WARNING);
				continue;
			}
			$plug['plug_action'] = 'scan';			// Make sure plugin.php knows what we're up to
			

			
			if(!$this->parse_plugin($p['path']))
			{
				//parsing of plugin.php/plugin.xml failed.			  
				$emessage->add("Parsing failed - file format error: {$p['path']}", E_MESSAGE_ERROR);
			  	continue;		// Carry on and do any others that are OK
			}
						
			$plug_info = $this->plug_vars;
		//	$plugin_path = substr(str_replace(e_PLUGIN,"",$p['path']),0,-1);

			// scan for addons.
			$eplug_addons = $this->getAddons($plugin_path);			// Returns comma-separated list
			//		  $eplug_addons = $this->getAddons($plugin_path,'check');		// Checks opening/closing tags on addon files

			//Ensure the plugin path lives in the same folder as is configured in the plugin.php/plugin.xml
			if ($plugin_path == $plug_info['folder'])
			{
				if(array_key_exists($plugin_path, $pluginDBList))
				{  // Update the addons needed by the plugin
					$pluginDBList[$plugin_path]['status'] = 'exists';

                   // Check for missing plugin_category in plugin table.
                    if ($pluginDBList[$plugin_path]['plugin_category'] == '')
					{
						 // print_a($plug_info);
                     	$pluginDBList[$plugin_path]['status'] = 'update';
						$pluginDBList[$plugin_path]['plugin_category'] = (varsettrue($plug_info['category']) ) ? $plug_info['category'] : "misc";
					}


					// If plugin not installed, and version number of files changed, update version as well
					if (($pluginDBList[$plugin_path]['plugin_installflag'] == 0) && ($pluginDBList[$plugin_path]['plugin_version'] != $plug_info['@attributes']['version']))
					{  // Update stored version
						$pluginDBList[$plugin_path]['plugin_version'] = $plug_info['@attributes']['version'];
						$pluginDBList[$plugin_path]['status'] = 'update';
					}
					if ($pluginDBList[$plugin_path]['plugin_addons'] != $eplug_addons)
					{  // Update stored addons list
						$pluginDBList[$plugin_path]['plugin_addons'] = $eplug_addons;
						$pluginDBList[$plugin_path]['status'] = 'update';
					}

					if ($pluginDBList[$plugin_path]['plugin_installflag'] == 0 ) // Plugin not installed - make sure $pref not set
					{  
						if(isset($p_installed[$plugin_path]))
						{
							unset($p_installed[$plugin_path]);
							$sp = TRUE;
						}
					}
					else
					{	// Plugin installed - make sure $pref is set
						if (!isset($p_installed[$plugin_path]) || ($p_installed[$plugin_path] != $pluginDBList[$plugin_path]['plugin_version']))
						{	// Update prefs array of installed plugins
							$p_installed[$plugin_path] = $pluginDBList[$plugin_path]['plugin_version'];
							//				  echo "Add: ".$plugin_path."->".$ep_row['plugin_version']."<br />";
							$sp = TRUE;
						}
					}
				}
				else
				{  // New plugin - not in table yet, so add it. If no install needed, mark it as 'installed'
					//SecretR - update to latest XML version
					if ($plug_info['@attributes']['name'])
					{
//					  echo "New plugin to add: {$plug_info['name']}<br />";
						// Can just add to DB - shouldn't matter that its not in our current table
						//				echo "Trying to insert: ".$eplug_folder."<br />";
						$_installed = ($plug_info['@attributes']['installRequired'] == 'true' || $plug_info['@attributes']['installRequired'] == 1 ? 0 : 1 );
						e107::getDb()->db_Insert("plugin", "0, '".$tp -> toDB($plug_info['@attributes']['name'], true)."', '".$tp -> toDB($plug_info['@attributes']['version'], true)."', '".$tp -> toDB($plugin_path, true)."', {$_installed}, '{$eplug_addons}', '".$this->manage_category($plug_info['category'])."', '".varset($plug_info['@attributes']['releaseUrl'])."' ");
						echo "<br />Installing: ".$plug_info['@attributes']['name'];
					}
				}
			}
			else
			{  // May be useful that we ignore what will usually be copies/backups of plugins - but don't normally say anything
//						    echo "Plugin copied to wrong directory. Is in: {$plugin_path} Should be: {$plug_info['folder']}<br /><br />";
			}

		   	// print_a($plug_info);
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
		if ($sp && vartrue($p_installed))
		{
			e107::getConfig('core')->setPref('plug_installed',$p_installed);
			e107::getConfig('core')->save();
		}
	}
	
	
	function manage_category($cat)
	{
		if(vartrue($cat) && in_array($cat,$this->accepted_categories))
		{
			return $cat;
		}
		else
		{
			return 'misc';
		}
	}

    function manage_icons($plugin='',$function='')
	{
		global $iconpool,$pref;
		
		$emessage = eMessage::getInstance();
		$sql = e107::getDb();
		$tp = e107::getParser();
		$fl = e107::getFile();
			
		if($plugin && ($function == 'uninstall') )
		{
			if(vartrue($this->unInstallOpts['del_ipool'], FALSE))
			{
				$ipool_entry = 'plugin-'.$plugin;
				e107::getConfig('ipool')->remove($ipool_entry); 	// FIXME - ipool removal issue. 
	        	$status = (e107::getConfig('ipool')->save(FALSE)) ?  E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
				$emessage->add('Removing Icon-Pool entry: '.$ipool_entry, $status); 
			}
			return;
		}
	


         $query = "SELECT * FROM #plugin WHERE plugin_installflag =0 ORDER BY plugin_path ASC";
		$sql->db_Select_gen($query);
		$list = $sql->db_getList();


		$reject_core = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*');
		$reject_plugin = $reject_core;
		foreach($list as $val) // reject uninstalled plugin folders.
		{
        	$reject_plugin[] = $val['plugin_path']."/images";
 		}

        $filesrch = implode("|",array("_16.png","_16.PNG","_32.png","_32.PNG","_48.png","_48.PNG","_64.png","_64.PNG","_128.png","_128.png"));

        if($plugin_icons = $fl->get_files(e_PLUGIN,$filesrch,$reject_plugin,2))
        {
        	sort($plugin_icons);
        }

		if($core_icons = $fl->get_files(e_IMAGE."icons/",$filesrch,$reject_core,2))
        {
        	sort($core_icons);
        }

		if($theme_icons = $fl->get_files(e_THEME.$pref['sitetheme']."/images/",$filesrch,$reject_core,2))
        {
        	sort($theme_icons);
        }

		$srch = array(e_IMAGE,"/");
		$repl = array("","-");

		$iconpool = array();

		foreach($core_icons as $file)
		{
			$path = str_replace($srch,$repl,$file['path']);
			$key = substr("core-".$path,0,-1);
       		$iconpool[$key][] = $tp->createConstants($file['path'],1).$file['fname'];
		}

 		$srch = array(e_PLUGIN,"/images/","/icons/","/icon/");
		$repl = array("","","");

		foreach($plugin_icons as $file)
		{
			$path = str_replace($srch,$repl,$file['path']);
			$key = "plugin-".$path;
       		$iconpool[$key][] = $tp->createConstants($file['path'],1).$file['fname'];
		}

		$srch = array(e_THEME,"/images/","/icons/","/demo/");
		$repl = array("","","");

		foreach($theme_icons as $file)
		{
			$path = str_replace($srch,$repl,$file['path']);
			$key = "theme-".$path;
       		$iconpool[$key][] = $tp->createConstants($file['path'],1).$file['fname'];
		}

		e107::getConfig('ipool')->setPref($iconpool); 	
        return (e107::getConfig('ipool')->save(FALSE)) ?  TRUE : FALSE;

	}

	/**
	* Returns details of a plugin from the plugin table from it's ID
	*
	* @param int $id
	* @return array plugin info
	*/
	function getinfo($id, $force=false)
	{
		$sql = e107::getDb();
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

	function manage_extended_field($action, $field_name, $field_type, $field_default='', $field_source='')
	{
		if(!isset($this->module['ue']))
		{
			include_once(e_HANDLER.'user_extended_class.php');
			$this->module['ue'] = new e107_user_extended;
		}
		$type = defined($field_type) ? constant($field_type) : $field_type;

		if($action == 'add')
		{
			return $this->module['ue']->user_extended_add_system($field_name, $type, $field_default, $field_source);
		}

		if ($action == 'remove')
		{
			return $this->module['ue']->user_extended_remove($field_name, $field_name);
		}
	}

	
	function manage_userclass($action, $class_name, $class_description)
	{
		global $e107;
		$tp = e107::getParser();
		$sql  = e107::getDb();
		
		if (!$e107->user_class->isAdmin)
		{
			$e107->user_class = new user_class_admin;			// We need the extra methods of the admin extension
		}
		$class_name = strip_tags(strtoupper($class_name));
		if ($action == 'add')
		{
			if ($e107->user_class->ucGetClassIDFromName($class_name) !== FALSE)
			{	// Class already exists.
				return TRUE;			// That's probably OK
			}
			$i = $e107->user_class->findNewClassID();
			if ($i !== FALSE)
			{
				$tmp = array();
				$tmp['userclass_id'] = $i;
				$tmp['userclass_name'] = $class_name;
				$tmp['userclass_description'] = $class_description;
				$tmp['userclass_editclass'] = e_UC_ADMIN;
				$tmp['userclass_visibility'] = e_UC_ADMIN;
				$tmp['userclass_type'] = UC_TYPE_STD;
				$tmp['userclass_parent'] = e_UC_NOBODY;
				$tmp['_FIELD_TYPES']['userclass_editclass'] = 'int';
				$tmp['_FIELD_TYPES']['userclass_visibility'] = 'int';
				$tmp['_FIELD_TYPES']['userclass_id'] = 'int';
				$tmp['_FIELD_TYPES']['_DEFAULT'] = 'todb';
				return $e107->user_class->add_new_class($tmp);
			}
			else
			{
				return FALSE;
			}
		}
		if ($action == 'remove')
		{
			$classID = $e107->user_class->ucGetClassIDFromName($class_name);
			if (($classID !== FALSE)
				&& ($e107->user_class->deleteClassAndUsers($classID) === TRUE))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
	}



	function manage_link($action, $link_url, $link_name, $link_class = 0)
	{
		
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		if(!is_numeric($link_class))
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



	// DEPRECATED in 0.8 - See XmlPrefs(); Left for BC. 
	// Update prefs array according to $action
	// $prefType specifies the storage type - may be 'pref', 'listPref' or 'arrayPref'
	function manage_prefs($action, $var, $prefType = 'pref', $path = '', $unEscape = FALSE)
	{
	  global $pref;
	  if (!is_array($var)) return;
	  if (($prefType == 'arrayPref') && ($path == '')) return;
	  foreach($var as $k => $v)
	  {
		if ($unEscape)
		{
		  $v = str_replace(array('\{','\}'),array('{','}'),$v);
		}
		switch ($prefType)
		{
		  case 'pref' :
			switch ($action)
			{
			  case 'add' :
				$pref[$k] = $v;
				break;

			  case 'update' :
			  case 'refresh' :
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
			  case 'refresh' :
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
			  case 'refresh' :
				if (!isset($pref[$k][$path])) $pref[$k][$path] = $v;
				break;
			  case 'remove' :
				if (isset($pref[$k][$path])) unset($pref[$k][$path]);		// Leaves an empty element if no values - probably acceptable or even preferable
				break;
			}
			break;
		}
	  }
	  
  	 e107::getConfig('core')->setPref($pref)->save();
	 
//	 e107::getConfig()->loadData($pref, false)->save(false, true);
	}




	function manage_comments($action, $comment_id)
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		$tmp = array();
		if($action == 'remove')
		{
			foreach($comment_id as $com)
			{
				$tmp[] = "comment_type='".$tp -> toDB($com, true)."'";
			}
			$qry = implode(" OR ",$tmp);
//			echo $qry."<br />";
			return $sql->db_Delete('comments', $qry);
		}
	}



	// Handle table updates - passed an array of actions.
	// $var array:
	//   For 'add' - its a query to create the table
	//	 For 'upgrade' - its a query to modify the table (not called from the plugin.xml handler)
	//	 For 'remove' - its a table name
	//  'upgrade' and 'remove' operate on all language variants of the same table
	function manage_tables($action, $var)
	{
	  $sql = e107::getDB();
	  if (!is_array($var)) return FALSE;			// Return if nothing to do

	  switch ($action)
	  {
		case 'add' :
			foreach($var as $tab)
			{
			  if (!$sql->db_Query($tab))
			  {
				return FALSE;
			  }
			}
			return TRUE;
		  break;
		case 'upgrade' :
				foreach($var as $tab)
				{
					if (!$sql->db_Query_all($tab))
					{
						return FALSE;
					}
				}
			return TRUE;
		  break;
		case 'remove' :
				foreach($var as $tab)
				{
					$qry = 'DROP TABLE '.MPREFIX.$tab;
					if (!$sql->db_Query_all($qry))
					{
						return $tab;
					}
				}
				return TRUE;
		  break;
	  }
	  return FALSE;
	}


	// DEPRECATED for 0.8 xml files - See XmlPrefs();
	// Handle prefs from arrays (mostly 0.7 stuff, possibly apart from the special cases)
	function manage_plugin_prefs($action, $prefname, $plugin_folder, $varArray = '')
	{  // These prefs are 'cumulative' - several plugins may contribute an array element
	//	global $pref;
/*
		if ($prefname == 'plug_sc' || $prefname == 'plug_bb')
		{  // Special cases - shortcodes and bbcodes - each plugin may contribute several elements
			foreach($varArray as $code)
			{
				$prefvals[] = "$code:$plugin_folder";
			}
		}
		else
		{
*/
			$prefvals[] = $varArray;
			//			$prefvals[] = $plugin_folder;
//		}
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
		
		e107::getConfig('core')->setPref($pref);
		e107::getConfig('core')->save();

	}




	function manage_search($action, $eplug_folder)
	{
		global $sysprefs;
		$sql = e107::getDb();
		
		
		$search_prefs = e107::getConfig('search')->getPref();
		
		
	//	$search_prefs = $sysprefs -> getArray('search_prefs');
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
		if (vartrue($install_default))
		{
			$search_prefs['plug_handlers'][$eplug_folder] = array('class' => 0, 'pre_title' => 1, 'pre_title_alt' => '', 'chars' => 150, 'results' => 10);
		}
		else if (vartrue($uninstall_default))
		{
			unset($search_prefs['plug_handlers'][$eplug_folder]);
		}
		if (vartrue($install_comments))
		{
			require_once(e_PLUGIN.$eplug_folder.'/search/search_comments.php');
			$search_prefs['comments_handlers'][$eplug_folder] = array('id' => $comments_type_id, 'class' => 0, 'dir' => $eplug_folder);
		}
		else if (vartrue($uninstall_comments))
		{
			unset($search_prefs['comments_handlers'][$eplug_folder]);
		}
				
		e107::getConfig('search')->setPref($search_prefs)->save();
		
	}

	function manage_notify($action, $eplug_folder)
	{
		$tp = e107::getParser();
	//	$notify_prefs = $sysprefs -> get('notify_prefs');
	//	$notify_prefs = $eArrayStorage -> ReadArray($notify_prefs);
		
		$notify_prefs = e107::getConfig('notify');
		$e_notify = file_exists(e_PLUGIN.$eplug_folder.'/e_notify.php') ? TRUE : FALSE;
		if ($action == 'add')
		{
			$install_notify = $e_notify ? TRUE : FALSE;
		}
		else if ($action == 'remove')
		{
			$uninstall_notify = $notify_prefs->isData('plugins/'.$eplug_folder);//isset($notify_prefs['plugins'][$eplug_folder]) ? TRUE : FALSE;
		}
		else if ($action == 'upgrade')
		{
			if ($notify_prefs->isData('plugins/'.$eplug_folder))
			{
				$uninstall_notify = $e_notify ? FALSE : TRUE;
			}
			else
			{
				$install_notify = $e_notify ? TRUE : FALSE;
			}
		}
		if (vartrue($install_notify))
		{
			$notify_prefs->setPref('plugins/'.$eplug_folder, 1); //$notify_prefs['plugins'][$eplug_folder] = TRUE;
			require_once(e_PLUGIN.$eplug_folder.'/e_notify.php');
			foreach ($config_events as $event_id => $event_text)
			{
				$notify_prefs->setPref('event/'.$event_id.'/class', e_UC_NOBODY)
					->setPref('event/'.$event_id.'/email', '');
				//$notify_prefs['event'][$event_id] = array('class' => e_UC_NOBODY, 'email' => '');
			}
		}
		else if (vartrue($uninstall_notify))
		{
			$notify_prefs->removePref('plugins/'.$eplug_folder);
			//unset($notify_prefs['plugins'][$eplug_folder]);
			require_once(e_PLUGIN.$eplug_folder.'/e_notify.php');
			foreach ($config_events as $event_id => $event_text)
			{
				$notify_prefs->removePref('event/'.$event_id);
				//unset($notify_prefs['event'][$event_id]);
			}
		}
		//$s_prefs = $tp -> toDB($notify_prefs);
		//$s_prefs = e107::getArrayStorage()->WriteArray($s_prefs); 
		//e107::getDb() -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'");
		$notify_prefs->save(false);
	}

	function displayArray(&$array, $msg='')
	{
		$txt = ($msg ? $msg.'<br />' : '');
		foreach($array as $_k => $_v)
		{
			$txt .= "{$_k} -> {$_v}<br />";
		}
		$txt .='<br />';
		return $txt;
	}


	//----------------------------------------------------------
	//		Install routine for XML file
	//----------------------------------------------------------
	//	$id - the number of the plugin in the DB
	// Values for $function:
	//		'install'
	//		'upgrade'
	//		'uninstall'
	// 		'refresh' 	- adds things that are missing, but doesn't change any existing settings
	// $options is an array of possible options - ATM used only for uninstall:
	//		'del_userclasses' - to delete userclasses created
	//		'del_tables' - to delete DB tables
	//		'del_extended' - to delete extended fields
	function manage_plugin_xml($id, $function='', $options = FALSE)
	{
		global $pref;

		$sql = e107::getDb();
		$emessage = eMessage::getInstance();
			
		$error = array();			// Array of error messages
		$canContinue = TRUE;		// Clear flag if must abort part way through

		$id = (int)$id;
		$plug = $this->getinfo($id);			// Get plugin info from DB
		$this->current_plug = $plug;
		$txt = '';
		$path = e_PLUGIN.$plug['plugin_path'].'/';
		
		$this->plugFolder = $plug['plugin_path'];
		$this->unInstallOpts = $options;
	

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


		if($canContinue && $this->parse_plugin_xml($plug['plugin_path']))
		{
			$plug_vars = $this->plug_vars;
		}
		else
		{
			$error[] = EPL_ADLAN_76;
			$canContinue = FALSE;
		}

		// First of all, see if there's a language file specific to install
		if (isset($plug_vars['installLanguageFile']) && isset($plug_vars['installLanguageFile']['@attributes']['filename']))
		{
			include_lan($path.str_replace('--LAN--',e_LANGUAGE, $plug_vars['installLanguageFile']['@attributes']['filename']));
		}

		// Next most important, if installing or upgrading, check that any dependencies are met
		if ($canContinue && ($function != 'uninstall') && isset($plug_vars['dependencies']))
		{
			$canContinue = $this->XmlDependencies($plug_vars['dependencies']);
		}

		if (!$canContinue)
		{
			return FALSE;
		}

		// All the dependencies are OK - can start the install now

		if ($canContinue)
		{	
			$ret = $this->execute_function($path, $function, 'pre');
			if (!is_bool($ret)) $txt .= $ret;
		}

		if ($canContinue && count($sql_list)) // TODO - move to it's own function. 
		{	// Handle tables
		
		
			require_once(e_HANDLER.'db_table_admin_class.php');
			$dbHandler = new db_table_admin;
			foreach($sql_list as $sqlFile)
			{
				$tableList = $dbHandler->get_table_def('',$path.$sqlFile);
				if (!is_array($tableList))
				{
					$emessage->add("Can\'t read SQL definition: ".$path.$sqlFile,E_MESSAGE_ERROR);
					break;
				}
				// Got the required definition here
				foreach ($tableList as $ct)
				{ // Process one table at a time (but they could be multi-language)
					switch($function)
					{
						case 'install' :
							$sqlTable = str_replace("CREATE TABLE ".MPREFIX.'`', "CREATE TABLE `".MPREFIX, preg_replace("/create table\s+/si", "CREATE TABLE ".MPREFIX, $ct[0]));
							$txt = "Adding table: {$ct[1]} ... ";
							$status = $this->manage_tables('add', array($sqlTable)) ? E_MESSAGE_SUCCESS: E_MESSAGE_ERROR;		// Pass the statement to create the table
							$emessage->add($txt,$status);
							break;
						case 'upgrade' :
							$tmp = $dbHandler->update_table_structure($ct,FALSE,TRUE, $pref['multilanguage']);
							if ($tmp === FALSE)
							{
								$error[] = 'Error updating table: '.$ct[1];
							}
							elseif ($tmp !== TRUE)
							{
								$error[] = $tmp;
							}
							break;
						case 'refresh' :		// Leave things alone
							break;
						case 'uninstall' :
							if (varsettrue($options['del_tables'], FALSE))
							{
								$txt = "Removing table {$ct[1]} <br />";
								$status = $this->manage_tables('remove', array($ct[1])) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;				// Delete the table
								$emessage->add($txt,$status);
							}
							else
							{								
								$emessage->add("Table {$ct[1]} left in place.",E_MESSAGE_SUCCESS);
							}
							break;
					}
				}
			}
		}	
		
		if(varset($plug_vars['siteLinks']))
		{
			$this->XmlSiteLinks($function,$plug_vars['siteLinks']);	
		}
				
		if(varset($plug_vars['mainPrefs'])) //Core pref items <mainPrefs>
		{
			$this->XmlPrefs('core',$function,$plug_vars['mainPrefs']);
		}		
			
		if(varset($plug_vars['pluginPrefs'])) //Plugin pref items <pluginPrefs>
		{
			$this->XmlPrefs($plug['plugin_path'],$function,$plug_vars['pluginPrefs']);
		}
		
		if(varset($plug_vars['userClasses']))
		{
			$this->XmlUserClasses($function,$plug_vars['userClasses']);
		}
		
		if(varset($plug_vars['extendedFields']))
		{
			$this->XmlExtendedFields($function,$plug_vars['extendedFields']);
		}
		
		if(varset($plug_vars['logLanguageFile']))
		{
			$this->XmlLogLanguageFile($function,$plug_vars['logLanguageFile']);
		}
		
		$this->manage_icons($this->plugFolder,$function);
		
		
		//FIXME 
		//If any commentIDs are configured, we need to remove all comments on uninstall
		if($function == 'uninstall' && isset($plug_vars['commentID']))
		{
			$txt .= 'Removing all plugin comments: ('.implode(', ', $plug_vars['commentID']).')<br />';
			$this->manage_comments('remove', $commentArray);
		}

		$this->manage_search($function, $plug_vars['folder']);
		$this->manage_notify($function, $plug_vars['folder']);

		if ($canContinue)
		{	// Let's call any custom post functions defined in <management> section
			$ret = $this->execute_function($path, $function, 'post');
			if (!is_bool($ret)) $txt .= $ret;
		}


		$eplug_addons = $this->getAddons($plug['plugin_path']);

		$p_installed = e107::getPref('plug_installed',array()); // load preference; 

		if($function == 'install' || $function == 'upgrade')
		{			
			$sql->db_Update('plugin', "plugin_installflag = 1, plugin_addons = '{$eplug_addons}', plugin_version = '{$plug_vars['@attributes']['version']}', plugin_category ='".$this->manage_category($plug_vars['category'])."', plugin_releaseUrl= '".varset($plug_vars['@attributes']['releaseUrl'])."' WHERE plugin_id = ".$id);
			$p_installed[$plug['plugin_path']] = $plug_vars['@attributes']['version'];
	
			e107::getConfig('core')->setPref('plug_installed',$p_installed);
			e107::getConfig('core')->save();		
		}

		if($function == 'uninstall')
		{
			$sql->db_Update('plugin', "plugin_installflag = 0, plugin_addons = '{$eplug_addons}', plugin_version = '{$plug_vars['@attributes']['version']}', plugin_category ='".$this->manage_category($plug_vars['category'])."', plugin_releaseUrl= '".varset($plug_vars['@attributes']['releaseUrl'])."' WHERE plugin_id = ".$id);
			unset($p_installed[$plug['plugin_path']]);
			e107::getConfig('core')->setPref('plug_installed',$p_installed);
			
		}
		
		
		e107::getConfig('core')->save();	

		if($function == 'install')
		{
			$txt .= LAN_INSTALL_SUCCESSFUL."<br />";
			if(isset($plug_vars['management']['installDone'][0]))
			{
				$emessage->add($plug_vars['management']['installDone'][0], E_MESSAGE_SUCCESS);
			}
		}
				
		// return $txt;
	}
	
	/**
	 * Process XML Tag <dependencies> (deprecated 'depend' which is a brand of adult diapers)
	 * @param array $tag
	 * @return boolean
	 */
	function XmlDependencies($tag)
	{
		$canContinue = TRUE;
		$emessage = eMessage::getInstance();
		$error = array();
		
		foreach ($tag as $dt => $dv)
		{
			if (isset($dv['@attributes']) && isset($dv['@attributes']['name']))
			{
//			  echo "Check {$dt} dependency: {$dv['@attributes']['name']} version {$dv['@attributes']['min_version']}<br />";
			  switch ($dt)
			  {
				case 'plugin' :
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
				case 'php' : // all should be lowercase
				  if (isset($dv['@attributes']['min_version']) && (version_compare($dv['@attributes']['min_version'],phpversion(),'<=') === FALSE))
				  {
					$error[] = EPL_ADLAN_74.$dv['@attributes']['min_version'];
					$canContinue = FALSE;
				  }
				  break;
				case 'mysql' : // all should be lowercase
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
		
		if(count($error))
		{
			$text = '<b>'.LAN_INSTALL_FAIL.'</b><br />'.implode('<br />',$error);
			$emessage->add($text, E_MESSAGE_ERROR); 	
		}
		
		return $canContinue;
	}
	
	
	
	
	/**
	 * Process XML Tag <logLanguageFile>
	 * @param object $function
	 * @param object $tag
	 * @return 
	 */
	function XmlLogLanguageFile($function,$tag)
	{
		$core = e107::getConfig('core');
		
		switch ($function)
		{
		    case 'install' :
			case 'upgrade' :
			case 'refresh' :
				$core->setPref('logLanguageFile/'.$this->plugFolder,$tag['@attributes']['filename']);
			  break;
			case 'uninstall' :
				$core->removePref('logLanguageFile/'.$this->plugFolder);
			  break;
		}	
	}
	
	

	/**
	 * Process XML Tag <siteLinks>
	 * @param string $function install|upgrade|refresh|uninstall
	 * @param array $array
	 * @return 
	 */
	function XmlSiteLinks($function,$array)
	{
		$emessage = &eMessage::getInstance();
		
		foreach($array['link'] as $link)
		{
				$attrib = $link['@attributes'];
				$linkName = (defset($link['@value'])) ? constant($link['@value']) : $link['@value'];
				$remove = (varset($attrib['deprecate']) == 'true') ? TRUE : FALSE;
				$url = e_PLUGIN.$attrib['url'];;
				$perm = (isset($attrib['perm']) ? $attrib['perm'] : 0);

				switch($function)
				{
					case 'upgrade':
					case 'install':
						
						if(!$remove) // Add any non-deprecated link 
						{							
							$status = ($this->manage_link('add', $url, $linkName, $perm)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
							$emessage->add("Adding Link: {$linkName} with url [{$url}] and perm {$perm} ", $status); 
						}
						
						if($function == 'upgrade' && $remove) //remove inactive links on upgrade
						{
							$status = ($this->manage_link('remove', $url, $linkName)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
							$emessage->add("Removing Link: {$linkName} with url [{$url}]", $status);
						}
						break;

					case 'refresh' : // Probably best to leave well alone
						break;

					case 'uninstall': //remove all links
						
						$status = ($this->manage_link('remove', $url, $linkName)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
						$emessage->add("Removing Link: {$linkName} with url [{$url}]", $status); 
						break;
				}
			}		
	}

	/**
	 * Process XML Tag <adminLinks> 
	 * @return 
	 */
	function XmlAdminLinks()
	{
		//TODO
	}



	/**
	 * Process XML Tag <userClasses> 
	 * @param string $function install|upgrade|refresh|uninstall
	 * @param array $array
	 * @return 
	 */
	function XmlUserClasses($function,$array)
	{
		$emessage = &eMessage::getInstance();
		
		foreach($array['class'] as $uclass)
		{
				$attrib = $uclass['@attributes'];
				$name = $attrib['name'];
				$description = $attrib['description'];
				$remove = (varset($attrib['deprecate']) == 'true') ? TRUE : FALSE;
				
				switch($function)
				{
					case 'install' :
					case 'upgrade' :
					case 'refresh' :
					
						if(!$remove) // Add all active userclasses (code checks for already installed)
						{
							$status = $this->manage_userclass('add', $name, $description) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
							$emessage->add('Adding Userclass: '.$name, $status); 
						}
	
						
						if($function == 'upgrade' && $remove) //If upgrading, removing any inactive userclass
						{
							$status = $this->manage_userclass('remove', $name, $description) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;			
							$emessage->add('Removing Userclass: '.$name, $status); 
						}
						
					break;

					
					case 'uninstall': //If uninstalling, remove all userclasses (active or inactive)
						
						if (varsettrue($this->unInstallOpts['del_userclasses'], FALSE))
						{
							$status = $this->manage_userclass('remove', $name, $description) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
							$emessage->add('Removing Userclass: '.$name, $status); 
						}
						else
						{
							$emessage->add('Userclass: '.$name.' left in place'.$name, $status); 
						}
						
					break;
				}
		}
	}


	/**
	 * Process XML Tag <extendedFields> 
	 * @param string $function install|upgrade|refresh|uninstall
	 * @param array $array
	 * @return 
	 */
	function XmlExtendedFields($function,$array)
	{
		$emessage = &eMessage::getInstance();
		
		foreach($array['field'] as $efield)
		{
				$attrib = $efield['@attributes'];
				$attrib['default'] = varset($attrib['default']);
				$name = 'plugin_'.$this->plugFolder.'_'.$attrib['name'];
				$source = 'plugin_'.$this->plugFolder;
				$remove = (varset($attrib['deprecate']) == 'true') ? TRUE : FALSE;
				$type = $attrib['type'];

				switch($function)
				{
					case 'install': // Add all active extended fields
					case 'upgrade':
						
						if(!$remove)
						{
							$status = $this->manage_extended_field('add', $name, $type, $attrib['default'], $source) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;		
							$emessage->add('Adding Extended Field: '.$name.' ... ', $status);
						}
						
						if($function == 'upgrade' && $remove) //If upgrading, removing any inactive extended fields
						{		
							$status = $this->manage_extended_field('remove', $name, $source) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;		
							$emessage->add('Removing Extended Field: '.$name.' ... ', $status);
						}
						break;

					
					case 'uninstall': //If uninstalling, remove all extended fields (active or inactive)
					
						if (varsettrue($this->unInstallOpts['del_extended'], FALSE))
						{
							$status = ($this->manage_extended_field('remove', $name, $source)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;					
							$emessage->add('Removing Extended Field: '.$name.' ... ', $status);
						}
						else
						{
							$emessage->add('Extended Field: '.$name.' left in place'.$name, E_MESSAGE_SUCCESS); 
						}
						break;
				}
			}
	}


	/**
	 * Process XML tags <mainPrefs> and <pluginPrefs>
	 * @param object $mode 'core' or the folder name of the plugin.
	 * @param object $function install|uninstall|upgrade|refresh
	 * @param object $prefArray XML array of prefs. eg. mainPref() or pluginPref();
	 * @return 
	 */
	function XmlPrefs($mode='core',$function,$prefArray)
	{
		
		//XXX Could also be used for theme prefs.. perhaps this function should be moved elsewhere?
		//TODO array support for prefs. <key>? or array() as used in xml site export? 
		
		$emessage = &eMessage::getInstance();
		
		if(!varset($prefArray) || !varset($prefArray))
		{
			return;	
		}
		
		$config = ($mode == 'core') ? e107::getConfig('core') : e107::getPlugConfig($mode);
		
		foreach($prefArray['pref'] as $tag)
		{
			$key = varset($tag['@attributes']['name']);
			$value = vartrue($tag['@value']);
			$remove = (varset($tag['@attributes']['deprecate'])=='true') ? TRUE : FALSE; 
			
			if(varset($tag['@attributes']['value']))
			{
				$emessage->add("Deprecated plugin.xml spec. found. Use the following format: ".htmlentities("<pref name='name'>value</pref>"), E_MESSAGE_ERROR); 	
			}
			
			switch($function)
			{
				case 'install':
					$config->add($key,$value);		
					$emessage->add("Adding Pref: ".$key, E_MESSAGE_SUCCESS); 						
				break;
				
				case 'upgrade' :
				case 'refresh' :
					if($remove) // remove active='false' prefs. 
					{
						$config->remove($key,$value);				
						$emessage->add("Removing Pref: ".$key, E_MESSAGE_SUCCESS); 		
					}
					else
					{
						$config->update($key,$value);		
						$emessage->add("Updating Pref: ".$key, E_MESSAGE_SUCCESS);
					}
					 	
				break;
				
				case 'uninstall':
					$config->remove($key,$value);				
					$emessage->add("Removing Pref: ".$key, E_MESSAGE_SUCCESS); 	
				break;
			}	
			
		}
		
		if($mode != "core") // Do only one core pref save during install/uninstall etc. 
		{
			$config->save();	
		} 
		return;


	}





	function execute_function($path = '', $what='', $when='')
	{
		if($what == '' || $when == '') { return true; }
		if(!isset($this->plug_vars['management'][$what])) { return true; }
		$vars = $this->plug_vars['management'][$what];
		if(count($vars) <= 1) { $vars = array($vars); }
		foreach($vars as $var)
		{
			$attrib = varset($var['@attributes']);
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

	// DEPRECATED - See XMLPrefs();
	function parse_prefs($pref_array,$mode='simple')
	{
		$ret = array();
		if(!isset($pref_array[0]))
		{
			$pref_array = array($pref_array);
		}
		if(is_array($pref_array))
		{
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
		}
		return $ret;
	}

	function install_plugin_php($id)
	{
	
		$sql = e107::getDb();

		$plug = $this->getinfo($id);
		$_path = e_PLUGIN.$plug['plugin_path'].'/';

		$plug['plug_action'] = 'install';

		//	$plug_vars = $this->parse_plugin_php($plug['plugin_path']);
		include($_path.'plugin.php');

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
/*
		if (is_array($eplug_sc))
		{
			$this->manage_plugin_prefs('add', 'plug_sc', $eplug_folder, $eplug_sc);
		}

		if (is_array($eplug_bb))
		{
			$this->manage_plugin_prefs('add', 'plug_bb', $eplug_folder, $eplug_bb);
		}
*/
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
		
		$p_installed = e107::getPref('plug_installed',array()); // load preference; 
		$p_installed[$plug['plugin_path']] = $plug['plugin_version'];
		
		e107::getConfig('core')->setPref('plug_installed',$p_installed)->save();
	
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
		global $ns, $sysprefs, $mySQLprefix;
		
		$sql = e107::getDb();
		$tp = e107::getParser();

		// install plugin ...
		$id = (int)$id;
		$plug = $this->getinfo($id);


		$plug['plug_action'] = 'install';

		if (!vartrue($plug['plugin_installflag']))
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

	function save_addon_prefs() // scan the plugin table and create path-array-prefs for each addon.
	{  	
		$sql = e107::getDb();
		$core = e107::getConfig('core');
		
		
		foreach($this->plugin_addons as $var) // clear all existing prefs.  
		{
			$core->update($var.'_list',"");	
		}
		
        $query = "SELECT * FROM #plugin WHERE plugin_addons !='' ORDER BY plugin_path ASC"; 
		
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
							// $addpref[$val."_list"][$path] = $path;
							$core->setPref($val.'_list/'.$path,$path);
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
						$core->setPref('e_sql_list/'.$path,$adds);
					}
				}

				// Build Bbcode list (will be empty if plugin not installed)
				if(count($bb_array) > 0)
				{
					ksort($bb_array);
					$core->setPref('bbcode_list/'.$path, $bb_array);
				}

				// Build shortcode list - do if uninstalled as well
				if(count($sc_array) > 0)
				{
					ksort($sc_array);
					$core->setPref('shortcode_list/'.$path,$sc_array);
				}
			}
		}

		$core->save(FALSE); 

		if($this->manage_icons())
		{
           // 	echo 'IT WORKED';
		}
		else
		{
        	// echo "didn't work!";
		}
		return;
	}

	// return a list of available plugin addons for the specified plugin. e_xxx etc.
	// $debug = TRUE - prints diagnostics
	// $debug = 'check' - checks each file found for php tags - prints 'pass' or 'fail'
	function getAddons($plugin_path, $debug=FALSE)
	{
		$fl = e107::getFile();
		
		$p_addons = array();

		foreach($this->plugin_addons as $addon) //Find exact matches only. 
		{	
			//	if(preg_match("#^(e_.*)\.php$#", $f['fname'], $matches)) 

				$addonPHP = $addon.".php";
								
				if(is_readable(e_PLUGIN.$plugin_path."/".$addonPHP))
				{
					if ($debug === 'check')
					{
						$passfail = '';
						$file_text = file_get_contents(e_PLUGIN.$plugin_path."/".$addonPHP);
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
	function parse_plugin($plugName, $force=false)
	{
		$ret = "";
		
		if(isset($this->parsed_plugin[$plugName]) && $force != true)
		{
			$this->plug_vars = $this->parsed_plugin[$plugName];
			return true;
		}
		unset($this->parsed_plugin[$plugName]);		// In case forced parsing which fails
		if(file_exists(e_PLUGIN.$plugName.'/plugin.xml'))
		{
			$ret = $this->parse_plugin_xml($plugName);
		}
		elseif(file_exists(e_PLUGIN.$plugName.'/plugin.php'))
		{
			$ret = $this->parse_plugin_php($plugName);
		}
		if($ret == true)
		{
			$this->parsed_plugin[$plugName] = $this->plug_vars;
		}
		
		
		return $ret;
	}


	// Called to parse the (deprecated) plugin.php file
	function parse_plugin_php($plugName)
	{
		include(e_PLUGIN.$plugName.'/plugin.php');
		$ret = array();

//		$ret['installRequired'] = ($eplug_conffile || is_array($eplug_table_names) || is_array($eplug_prefs) || is_array($eplug_sc) || is_array($eplug_bb) || $eplug_module || $eplug_userclass || $eplug_status || $eplug_latest);
		$ret['@attributes']['installRequired'] = ($eplug_conffile || is_array($eplug_table_names) || is_array($eplug_prefs) || $eplug_module || $eplug_userclass || $eplug_status || $eplug_latest);

		$ret['@attributes']['version'] = varset($eplug_version);
		$ret['@attributes']['name'] = varset($eplug_name);
		$ret['@attributes']['compatibility'] = varset($eplug_compatible);
		$ret['folder'] = (varset($eplug_folder)) ? $eplug_folder : $plugName;
		$ret['category'] = varset($eplug_category) ? $this->manage_category($eplug_category) : "misc";
		$ret['description'] = varset($eplug_description);
		$ret['author']['@attributes']['name'] = varset($eplug_author);
		$ret['author']['@attributes']['url'] = varset($eplug_url);
		$ret['author']['@attributes']['email'] = varset($eplug_email);
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
	function parse_plugin_xml($plugName)
	{
	
		$tp = e107::getParser();
	//	loadLanFiles($plugName, 'admin');					// Look for LAN files on default paths
		require_once(e_HANDLER.'xml_class.php');
		$xml = new xmlClass;
	//	$xml->setOptArrayTags('extendedField,userclass,menuLink,commentID'); // always arrays for these tags.
	//	$xml->setOptStringTags('install,uninstall,upgrade'); 
		
	//	$plug_vars2 = $xml->loadXMLfile(e_PLUGIN.$plugName.'/plugin2.xml', 'advanced');
		$this->plug_vars = $xml->loadXMLfile(e_PLUGIN.$plugName.'/plugin.xml', 'advanced');
		if ($this->plug_vars === FALSE)
		{
            require_once(e_HANDLER."message_handler.php");
			$emessage = &eMessage::getInstance();
			$emessage->add("Error reading {$plugName}/plugin.xml", E_MESSAGE_ERROR);
 			return FALSE;
		}
		
		$this->plug_vars['category'] = (isset($this->plug_vars['category'])) ? $this->manage_category($this->plug_vars['category']) : "misc";	
		$this->plug_vars['folder'] = $plugName; // remove the need for <folder> tag in plugin.xml. 
		
/*		if($plugName == "forum")
		{
			echo "<table><tr><td>";
			//print_a($plug_vars2);
			echo "</td><td>";		
			print_a($this->plug_vars);
			echo "</table>";
		}*/
		
		// TODO search for $this->plug_vars['adminLinks']['link'][0]['@attributes']['primary']==true. 
		$this->plug_vars['administration']['icon'] = varset($this->plug_vars['adminLinks']['link'][0]['@attributes']['icon']);
		$this->plug_vars['administration']['caption'] = varset($this->plug_vars['adminLinks']['link'][0]['@attributes']['description']);
		$this->plug_vars['administration']['iconSmall'] = varset($this->plug_vars['adminLinks']['link'][0]['@attributes']['iconSmall']);
		$this->plug_vars['administration']['configFile'] = varset($this->plug_vars['adminLinks']['link'][0]['@attributes']['url']);
		
		return TRUE;
	}



}

?>