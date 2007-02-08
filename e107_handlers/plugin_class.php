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
|     $Revision: 1.3 $
|     $Date: 2007-02-08 20:09:07 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class e107plugin
{
    var	$plugin_addons = array("e_rss", "e_notify", "e_linkgen", "e_list", "e_bb", "e_meta", "e_emailprint", "e_frontpage", "e_latest", "e_status", "e_search", "e_sc", "e_module", "e_comment", "e_sql");

	// List of all plugin variables which need to be checked - install required if one or more set and non-empty
	var $all_eplug_install_variables = array(
		'eplug_link_url',
		'eplug_link',
		'eplug_prefs',
		'eplug_array_pref',		// missing previously
		'eplug_table_names',
		'eplug_user_prefs',
		'eplug_sc',
		'eplug_userclass',
		'eplug_module',
		'eplug_bb',
		'eplug_latest',
		'eplug_status'		//,
//		'eplug_comment_ids',	// Not sure about this one
//		'eplug_menu_name',		// ...or this one
//		'eplug_conffile'		// ...or this one
	);

	// List of all plugin variables involved in an update (not used ATM, but worth 'documenting')
	var $all_eplug_update_variables = array (
		'upgrade_alter_tables',
		'upgrade_add_eplug_sc',
		'upgrade_remove_eplug_sc',
		'upgrade_add_eplug_bb',
		'upgrade_remove_eplug_bb',
		'upgrade_add_prefs',
		'upgrade_remove_prefs',
		'upgrade_add_user_prefs',
		'upgrade_remove_user_prefs',
		'upgrade_add_array_pref',		// missing
		'upgrade_remove_array_pref'		// missing
	);
	
	/**
	 * Returns an array containing details of all plugins in the plugin table - should noramlly use e107plugin::update_plugins_table() first to make sure the table is up to date.
	 * @return array plugin details
	 */
	function getall($flag)
	{
		global $sql;
		if ($sql->db_Select("plugin","*","plugin_installflag = '".intval($flag)."' ORDER BY plugin_path ASC"))
		{
			$ret = $sql->db_getList();
 		}
		return ($ret) ? $ret : FALSE;
	}


	/**
	 * Check for new plugins, create entry in plugin table and remove deleted plugins
	 */
	function update_plugins_table() 
	{
		global $sql, $sql2, $mySQLprefix, $menu_pref, $tp;

		require_once(e_HANDLER.'file_class.php');

		$fl = new e_file;
		$pluginList = $fl->get_files(e_PLUGIN, "^plugin\.php$", "standard", 1);

		// Get rid of any variables previously defined which may occur in plugin.php
		foreach($pluginList as $p)
		{
		  $defined_vars = array_keys(get_defined_vars());
		  foreach($defined_vars as $varname) 
		  {
			if ((substr($varname, 0, 6) == 'eplug_') || (substr($varname, 0, 8) == 'upgrade_')) 
			{
			  unset($$varname);
			}
		  }

		  // We have to include here to set the variables, otherwise we only get uninstalled plugins
		  // Would be nice to eval() the file contents to pick up errors better, but too many path issues
		  include("{$p['path']}{$p['fname']}");
		  $plugin_path = substr(str_replace(e_PLUGIN,"",$p['path']),0,-1);

		  // scan for addons.
		  $eplug_addons = $this->getAddons($plugin_path);

		  // See whether the plugin needs installation - it does if one or more variables defined
		  $no_install_needed = 1;
		  foreach ($this->all_eplug_install_variables as $check_var)
		  {
		    if (isset($$check_var) && ($$check_var)) { $no_install_needed = 0; }
		  }
				  
		  if ($plugin_path == $eplug_folder)
		  {
			if($sql->db_Select("plugin", "plugin_id, plugin_version, plugin_installflag", "plugin_path = '$plugin_path'"))
			{  // Update the addons needed by the plugin
			  $ep_row = $sql->db_Fetch();
			  $ep_update = '';
			  // If plugin not installed, and version number of files changed, update version as well
			  if (($ep_row['plugin_installflag'] == 0) && ($ep_row['plugin_version'] != $eplug_version)) $ep_update = ", plugin_version = '{$eplug_version}' ";
			  $sql->db_Update("plugin", "plugin_addons = '{$eplug_addons}'{$ep_update} WHERE plugin_path = '$plugin_path'");
			  unset($ep_row, $ep_update);
			}

			if ((!$sql->db_Select("plugin", "plugin_id", "plugin_path = '".$tp -> toDB($eplug_folder, true)."'")) && $eplug_name)
			{  // New plugin - not in table yet, so add it. If no install needed, mark it as 'installed'
			  $sql->db_Insert("plugin", "0, '".$tp -> toDB($eplug_name, true)."', '".$tp -> toDB($eplug_version, true)."', '".$tp -> toDB($eplug_folder, true)."', {$no_install_needed}, '{$eplug_addons}' ");
			}
		  }
		  else
		  {  // May be useful that we ignore what will usually be copies/backups of plugins - but don't normally say anything
//		    echo "Plugin copied to wrong directory. Is in: {$plugin_path} Should be: {$eplug_folder}<br /><br />";
		  }
		}

		$sql->db_Select("plugin");
		while ($row = $sql->db_fetch()) 
		{	// Check for the actual plugin.php file - that's really the criterion for a 'proper' plugin
		  if (!file_exists(e_PLUGIN.$row['plugin_path'].'/plugin.php')) 
		  {
//			  echo "Deleting: ".e_PLUGIN.$row['plugin_path'].'/plugin.php'."<br />";
			$sql2->db_Delete('plugin', "plugin_path = '{$row['plugin_path']}'");
		  }
		}
	}

	/**
	 * Returns deatils of a plugin from the plugin table from it's ID
	 *
	 * @param int $id
	 * @return array plugin info
	 */
	function getinfo($id) {
		global $sql;
		$id = intval($id);
		if ($sql->db_Select('plugin', '*', "plugin_id = {$id}")) {
			return $sql->db_Fetch();
		}
	}

	function manage_userclass($action, $class_name, $class_description) {
		global $sql, $tp;
		$class_name = $tp -> toDB($class_name, true);
		$class_description = $tp -> toDB($class_description, true);
		if ($action == 'add') {
			$i = 1;
			while ($sql->db_Select('userclass_classes', '*', "userclass_id='{$i}' ") && $i < e_UC_READONLY) {
				$i++;
			}
			if ($i < e_UC_READONLY) {
				return $sql->db_Insert('userclass_classes', "{$i},'".strip_tags(strtoupper($class_name))."', '{$class_description}' ,".e_UC_PUBLIC);
			} else {
				return FALSE;
			}
		}
		if ($action == 'remove') {
			if ($sql->db_Select('userclass_classes', 'userclass_id', "userclass_name = '{$class_name}'")) {
				$row = $sql->db_Fetch();
				$class_id = $row['userclass_id'];
				if ($sql->db_Delete('userclass_classes', "userclass_id = '{$class_id}'")) {
					if ($sql->db_Select('user', 'user_id, user_class', "user_class REGEXP('^{$class_id}\.') OR user_class REGEXP('\.{$class_id}\.') OR user_class REGEXP('\.{$class_id}$')")) {
						$sql2 = new db;
						while ($row = $sql->db_Fetch()) {
							$classes = explode(".", $row['user_class']);
							unset($classes[$class_id]);
							foreach($classes as $k => $v) {
								if ($v = '') {
									unset($classes[$k]);
								}
							}
							$newclass = '.'.implode('.', $classes).'.';
							$sql2->db_Update('user', "user_class = '{$newclass}' WHERE user_id = '{$row['user_id']}");
						}
					}
				}
			}
		}
	}

	function manage_link($action, $link_url, $link_name,$link_class=0) {
		global $sql, $tp;
		$link_url = $tp -> toDB($link_url, true);
		$link_name = $tp -> toDB($link_name, true);
		if ($action == 'add') {
			$path = str_replace("../", "", $link_url);
			$link_t = $sql->db_Count('links');
			if (!$sql->db_Count('links', '(*)', "link_name = '{$link_name}'")) {
				return $sql->db_Insert('links', "0, '{$link_name}', '{$path}', '', '', '1', '".($link_t + 1)."', '0', '0', '{$link_class}' ");
			} else {
				return FALSE;
			}
		}
		if ($action == 'remove') {
			if ($sql->db_Select('links', 'link_order', "link_name = '{$link_name}'")) {
				$row = $sql->db_Fetch();
				$sql->db_Update('links', "link_order = link_order - 1 WHERE link_order > {$row['link_order']}");
				return $sql->db_Delete('links', "link_name = '{$link_name}'");
			}
		}
	}

	function manage_prefs($action, $var) {
		global $pref;
		if (is_array($var)) {
			if ($action == 'add') {
				foreach($var as $k => $v) {
					$pref[$k] = $v;
				}
			}
			if ($action == 'remove') {
				foreach($var as $k => $v) {
					unset($pref[$k]);
				}
			}
			save_prefs();
		}
	}


	function manage_comments($action,$comment_id){
		global $sql, $tp;
    	if($action == 'remove'){
			foreach($comment_id as $com){
            	$tmp[] = "comment_type='".$tp -> toDB($com, true)."'";
			}
			$qry = implode(" OR ",$tmp);
   			return $sql->db_Delete('comments',$qry);
   		}
	}


	function manage_tables($action, $var) {
		global $sql;
		if ($action == 'add') {
			if (is_array($var)) {
				foreach($var as $tab) {
					if (!$sql->db_Query($tab)) {
						return FALSE;
					}
				}
				return TRUE;
			}
			return TRUE;
		}
        if ($action == 'upgrade') {
			if (is_array($var)) {
				foreach($var as $tab) {
					if (!$sql->db_Query_all($tab)) {
						return FALSE;
					}
				}
				return TRUE;
			}
			return TRUE;
		}
		if ($action == 'remove') {
			if (is_array($var)) {
				foreach($var as $tab) {
					$qry = 'DROP TABLE '.MPREFIX.$tab;
					if (!$sql->db_Query_all($qry)) {
						return $tab;
					}
				}
				return TRUE;
			}
			return TRUE;
		}
	}

	function manage_plugin_prefs($action, $prefname, $plugin_folder, $varArray = '') 
	{  // 
		global $pref;
		if ($prefname == 'plug_sc' || $prefname == 'plug_bb') {
			foreach($varArray as $code) {
				$prefvals[] = "$code:$plugin_folder";
			}
		} else {
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






	function manage_search($action, $eplug_folder) {
		global $sql, $sysprefs;
		$search_prefs = $sysprefs -> getArray('search_prefs');
		$default = file_exists(e_PLUGIN.$eplug_folder.'/e_search.php') ? TRUE : FALSE;
		$comments = file_exists(e_PLUGIN.$eplug_folder.'/search/search_comments.php') ? TRUE : FALSE;
		if ($action == 'add'){
			$install_default = $default ? TRUE : FALSE;
			$install_comments = $comments ? TRUE : FALSE;
		} else if ($action == 'remove'){
			$uninstall_default = isset($search_prefs['plug_handlers'][$eplug_folder]) ? TRUE : FALSE;
			$uninstall_comments = isset($search_prefs['comments_handlers'][$eplug_folder]) ? TRUE : FALSE;
		} else if ($action == 'upgrade'){
			if (isset($search_prefs['plug_handlers'][$eplug_folder])) {
				$uninstall_default = $default ? FALSE : TRUE;
			} else {
				$install_default = $default ? TRUE : FALSE;
			}
			if (isset($search_prefs['comments_handlers'][$eplug_folder])) {
				$uninstall_comments = $comments ? FALSE : TRUE;
			} else {
				$install_comments = $comments ? TRUE : FALSE;
			}
		}
		if ($install_default) {
			$search_prefs['plug_handlers'][$eplug_folder] = array('class' => 0, 'pre_title' => 1, 'pre_title_alt' => '', 'chars' => 150, 'results' => 10);
		} else if ($uninstall_default) {
			unset($search_prefs['plug_handlers'][$eplug_folder]);
		}
		if ($install_comments) {
			require_once(e_PLUGIN.$eplug_folder.'/search/search_comments.php');
			$search_prefs['comments_handlers'][$eplug_folder] = array('id' => $comments_type_id, 'class' => 0, 'dir' => $eplug_folder);
		} else if ($uninstall_comments) {
			unset($search_prefs['comments_handlers'][$eplug_folder]);
		}
		$tmp = addslashes(serialize($search_prefs));
		$sql->db_Update("core", "e107_value = '{$tmp}' WHERE e107_name = 'search_prefs' ");
	}

	function manage_notify($action, $eplug_folder) {
		global $sql, $sysprefs, $eArrayStorage, $tp;
		$notify_prefs = $sysprefs -> get('notify_prefs');
		$notify_prefs = $eArrayStorage -> ReadArray($notify_prefs);
		$e_notify = file_exists(e_PLUGIN.$eplug_folder.'/e_notify.php') ? TRUE : FALSE;
		if ($action == 'add'){
			$install_notify = $e_notify ? TRUE : FALSE;
		} else if ($action == 'remove'){
			$uninstall_notify = isset($notify_prefs['plugins'][$eplug_folder]) ? TRUE : FALSE;
		} else if ($action == 'upgrade'){
			if (isset($notify_prefs['plugins'][$eplug_folder])) {
				$uninstall_notify = $e_notify ? FALSE : TRUE;
			} else {
				$install_notify = $e_notify ? TRUE : FALSE;
			}
		}
		if ($install_notify) {
			$notify_prefs['plugins'][$eplug_folder] = TRUE;
			require_once(e_PLUGIN.$eplug_folder.'/e_notify.php');
			foreach ($config_events as $event_id => $event_text) {
				$notify_prefs['event'][$event_id] = array('type' => 'off', 'class' => '254', 'email' => '');
			}
		} else if ($uninstall_notify) {
			unset($notify_prefs['plugins'][$eplug_folder]);
			require_once(e_PLUGIN.$eplug_folder.'/e_notify.php');
			foreach ($config_events as $event_id => $event_text) {
				unset($notify_prefs['event'][$event_id]);
			}
		}
		$s_prefs = $tp -> toDB($notify_prefs);
		$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
		$sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs'");
	}

	/**
	 * Installs a plugin by ID
	 *
	 * @param int $id
	 */
	function install_plugin($id) {
		global $sql, $ns, $sysprefs,$mySQLprefix, $tp;

		// install plugin ...
		$plug = $this->getinfo($id);

		if ($plug['plugin_installflag'] == FALSE) {
			include_once(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

			$func = $eplug_folder.'_install';
			if (function_exists($func)) {
				$text .= call_user_func($func);
			}

			if (is_array($eplug_tables)) {
				$result = $this->manage_tables('add', $eplug_tables);
				if ($result === TRUE) {
					$text .= EPL_ADLAN_19.'<br />';
					//success
				} else {
					$text .= EPL_ADLAN_18.'<br />';
					//fail
				}
			}



			if (is_array($eplug_prefs)) {
				$this->manage_prefs('add', $eplug_prefs);
				$text .= EPL_ADLAN_8.'<br />';
			}



			if (is_array($eplug_array_pref)){
				foreach($eplug_array_pref as $key => $val){
					$this->manage_plugin_prefs('add', $key, $eplug_folder, $val);
				}
			}

			if (is_array($eplug_sc)) {
				$this->manage_plugin_prefs('add', 'plug_sc', $eplug_folder, $eplug_sc);
			}

			if (is_array($eplug_bb)) {
				$this->manage_plugin_prefs('add', 'plug_bb', $eplug_folder, $eplug_bb);
			}

			if (is_array($eplug_user_prefs)) {
				$sql->db_Select("core", " e107_value", " e107_name = 'user_entended'");
				$row = $sql->db_Fetch();
				$user_entended = unserialize($row[0]);
				while (list($e_user_pref, $default_value) = each($eplug_user_prefs)) {
					$user_entended[] = $e_user_pref;
					$user_pref['$e_user_pref'] = $default_value;
				}
				save_prefs("user");
				$tmp = addslashes(serialize($user_entended));
				if ($sql->db_Select("core", "e107_value", " e107_name = 'user_entended'")) {
					$sql->db_Update("core", "e107_value = '{$tmp}' WHERE e107_name = 'user_entended' ");
				} else {
					$sql->db_Insert("core", "'user_entended', '{$tmp}' ");
				}
				$text .= EPL_ADLAN_8."<br />";
			}

			if ($eplug_link === TRUE && $eplug_link_url != '' && $eplug_link_name != '') {
                $plug_perm['everyone'] = e_UC_PUBLIC;
				$plug_perm['guest'] = e_UC_GUEST;
				$plug_perm['member'] = e_UC_MEMBER;
				$plug_perm['admin'] = e_UC_ADMIN;
				$plug_perm['nobody'] = e_UC_NOBODY;
				$eplug_link_perms = strtolower($eplug_link_perms);
                $linkperm = ($plug_perm[$eplug_link_perms]) ? $plug_perm[$eplug_link_perms] : e_UC_PUBLIC;
				$this->manage_link('add', $eplug_link_url, $eplug_link_name,$linkperm);
			}

			if ($eplug_userclass) {
				$this->manage_userclass('add', $eplug_userclass, $eplug_userclass_description);
			}

			$this -> manage_search('add', $eplug_folder);

			$this -> manage_notify('add', $eplug_folder);

			$eplug_addons = $this->getAddons($eplug_folder);

			$sql->db_Update('plugin', "plugin_installflag = 1, plugin_addons = '{$eplug_addons}' WHERE plugin_id = '".intval($id)."'");
            if($rssmess){ $text .= $rssmess; }
			$text .= (isset($eplug_done) ? "<br />{$eplug_done}" : "<br />".LAN_INSTALL_SUCCESSFUL);
		} else {
			$text = EPL_ADLAN_21;
		}
		if($eplug_conffile){ $text .= "&nbsp;<a href='".e_PLUGIN."$eplug_folder/$eplug_conffile'>[".LAN_CONFIGURE."]</a>"; }
		$ns->tablerender(EPL_ADLAN_33, $text);
	}



	function save_addon_prefs(){  // scan the plugin table and create path-array-prefs for each addon.
		global $sql,$pref;
        $query = "SELECT * FROM #plugin WHERE plugin_installflag = 1 AND plugin_addons !='' ORDER BY plugin_path ASC";

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
                $tmp = explode(",",$row['plugin_addons']);
				$path = $row['plugin_path'];

        		foreach($this->plugin_addons as $val)
				{
                	if(in_array($val,$tmp))
					{
 						$pref[$val."_list"][$path] = $path;
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
                    	$sc_array[$sc_name] = "0"; // default userclass.
					}

					if(substr($adds,-3) == ".bb")
					{
						$bb_name = substr($adds, 0,-3); // remove the .bb
                    	$bb_array[$bb_name] = "0"; // default userclass.
					}

					if(substr($adds,-4) == "_sql")
					{
						$pref['e_sql_list'][$path] = $adds;
					}
				}

                // Build Bbcode list
                if(count($bb_array) > 0)
				{
					ksort($bb_array);
                	$pref['bbcode_list'][$path] = $bb_array;

				}
				else
				{
                    unset($pref['bbcode_list'][$path]);
				}

                // Build shortcode list
				if(count($sc_array) > 0){
					ksort($sc_array);
					$pref['shortcode_list'][$path] = $sc_array;
                }
				else
				{
                    unset($pref['shortcode_list'][$path]);
				}

			}
		}

	  	save_prefs();
		return;

	}

    // return a list of available plugin addons for the specified plugin. e_xxx etc.
	function getAddons($plugin_path,$debug=FALSE){
        global $fl;

		$p_addons = array();
		foreach($this->plugin_addons as $e_xxx)
		{
			if(is_readable(e_PLUGIN.$plugin_path."/".$e_xxx.".php"))
			{
				$p_addons[] = $e_xxx;
			}
		}

		if(!is_object($fl)){
			require_once(e_HANDLER.'file_class.php');
 			$fl = new e_file;
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
				$p_addons[] = str_replace(".php","",$esql['fname']);
			}
		}


		if($debug)
		{
			echo $plugin_path." = ".implode(",",$p_addons)."<br />";
		}

		return implode(",",$p_addons);
	}


}

?>
