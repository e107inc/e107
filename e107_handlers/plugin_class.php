<?php
class e107plugin {
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
