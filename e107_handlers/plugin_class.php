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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/plugin_class.php,v $
|     $Revision: 1.9 $
|     $Date: 2005-03-12 15:54:00 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

class e107plugin {

	/**
	 * Returns an array containing details of all plugins in the plugin table - should noramlly use e107plugin::update_plugins_table() first to make sure the table is up to date.
	 *
	 * @return array plugin details
	 */
	function getall() {
		global $sql;
		if ($sql->db_Select('plugin')) {
			while ($row = $sql->db_Fetch()) {
				$ret[ucfirst($row['plugin_name'])] = $row;
			}

			ksort($ret, SORT_STRING);

			return $ret;
		}
		return FALSE;
	}

	/**
	 * Check for new plugins, create entry in plugin table and remove deleted plugins
	 *
	 */
	function update_plugins_table() {
		global $sql;

		require_once(e_HANDLER.'file_class.php');

		$fl = new e_file;
		$pluginList = $fl->get_files(e_PLUGIN, "^plugin\.php$", "standard", 1);

		foreach($pluginList as $p)
		{
			foreach($defined_vars as $varname) {
				if (substr($varname, 0, 6) == 'eplug_' || substr($varname, 0, 8) == 'upgrade_') {
					unset($$varname);
				}
			}
			include($p['path']."/".$p['fname']);
			$plugin_path = substr($p['path'], strrpos($p['path'], "/")+1);

			if ((!$sql->db_Select("plugin", "plugin_id", "plugin_path='$plugin_path'")) && $eplug_name)
			{
				if (!$eplug_prefs && !$eplug_table_names && !$eplug_user_prefs && !$eplug_sc && !$eplug_userclass && !$eplug_module && !$eplug_bb && !$eplug_latest && !$eplug_status)
				{
					// new plugin, assign entry in plugin table, install is not necessary so mark it as intalled
					$sql->db_Insert("plugin", "0, '$eplug_name', '$eplug_version', '$eplug_folder', 1");
				}
				else
				{
					// new plugin, assign entry in plugin table, install is necessary
					$sql->db_Insert("plugin", "0, '$eplug_name', '$eplug_version', '$eplug_folder', 0");
				}
			}
		}

		$sql->db_Select("plugin");
		while ($row = $sql->db_fetch()) {
			if (!is_dir(e_PLUGIN.$row['plugin_path'])) {
				$sql->db_Delete('plugin', "plugin_path='{$row['plugin_path']}'");
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
		global $sql;
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
			if ($sql->db_Select('userclass_classes', 'userclass_id', "userclass_name='{$class_name}'")) {
				$row = $sql->db_Fetch();
				$class_id = $row['userclass_id'];
				if ($sql->db_Delete('userclass_classes', "userclass_id='{$class_id}'")) {
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
							$sql2->db_Update('user', "user_class='$newclass' WHERE user_id='{$row['user_id']}");
						}
					}
				}
			}
		}
	}

	function manage_link($action, $link_url, $link_name) {
		global $sql;
		if ($action == 'add') {
			$path = str_replace("../", "", $link_url);
			$link_t = $sql->db_Count('links');
			if (!$sql->db_Count('links', '(*)', "link_name='$link_name' ")) {
				return $sql->db_Insert('links', "0, '$link_name', '$path', '', '', '1', '".($link_t+1)."', '0', '0', '' ");
			} else {
				return FALSE;
			}
		}
		if ($action == 'remove') {
			if ($sql->db_Select('links', 'link_order', "link_name='{$link_name}'")) {
				$row = $sql->db_Fetch();
				$sql->db_Update('links', "link_order=link_order-1 WHERE link_order > {$row['link_order']}");
				return $sql->db_Delete('links', "link_name='{$link_name}'");
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
		if ($action == 'remove') {
			if (is_array($var)) {
				foreach($var as $tab) {
					$qry = 'DROP TABLE '.MPREFIX.$tab;
					if (!$sql->db_Query($qry)) {
						return $tab;
					}
				}
				return TRUE;
			}
			return TRUE;
		}
	}

	function manage_plugin_prefs($action, $prefname, $plugin_folder, $varArray = '') {
		global $pref;
		if ($prefname == 'plug_sc' || $prefname == 'plug_bb') {
			foreach($varArray as $code) {
				$prefvals[] = "$code:$plugin_folder";
			}
		} else {
			$prefvals[] = $plugin_folder;
		}
		$curvals = explode(',', $pref[$prefname]);

		if ($action == 'add') {
			$newvals = array_merge($curvals, $prefvals);
		}
		if ($action == 'remove') {
			foreach($prefvals as $v) {
				if ($i = array_search($v, $curvals)) {
					unset($curvals[$i]);
				}
			}
			// $newvals = explode(',', $curvals);
			$newvals = $curvals;
		}
		$newvals = array_unique($newvals);
		if(count($newvals) < 2)
		{
			$pref[$prefname] = $newvals[0];
		}
		else
		{
			$pref[$prefname] = implode(',', $newvals);
		}
		save_prefs();
	}
}
?>

