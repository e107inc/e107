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
|     $Source: /cvs_backup/e107_0.7/e107_admin/update_routines.php,v $
|     $Revision: 1.53 $
|     $Date: 2005-03-18 01:47:07 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");

if (!defined("LAN_UPDATE_8")) { define("LAN_UPDATE_8", ""); }
if (!defined("LAN_UPDATE_9")) { define("LAN_UPDATE_9", ""); }
if(file_exists(e_PLUGIN.'forum/forum_update_check.php'))
{
	include_once(e_PLUGIN.'forum/forum_update_check.php');
}
$dbupdate["61x_to_700"] = LAN_UPDATE_8." .61x ".LAN_UPDATE_9." .7";
$dbupdate["616_to_617"] = LAN_UPDATE_8." .616 ".LAN_UPDATE_9." .617";
$dbupdate["615_to_616"] = LAN_UPDATE_8." .615 ".LAN_UPDATE_9." .616";
$dbupdate["614_to_615"] = LAN_UPDATE_8." .614 ".LAN_UPDATE_9." .615";
$dbupdate["611_to_612"] = LAN_UPDATE_8." .611 ".LAN_UPDATE_9." .612";
$dbupdate["603_to_604"] = LAN_UPDATE_8." .603 ".LAN_UPDATE_9." .604";


function update_check() {
	global $ns, $dbupdate;
	foreach($dbupdate as $func => $rmks) {
		if (function_exists("update_".$func)) {
			if (!call_user_func("update_".$func, FALSE)) {
				$update_needed = TRUE;
				continue;
			}
		}
	}
	if ($update_needed === TRUE) {
		$txt = "<div style='text-align:center;'>".ADLAN_120;
		$txt .= "<br /><form method='POST' action='".e_ADMIN."e107_update.php'>
			<input class='button' type='submit' value='".ADLAN_122."' />
			</form></div>";
		$ns->tablerender(ADLAN_122, $txt);
	}
}

function update_61x_to_700($type) {
	global $sql, $ns, $mySQLdefaultdb, $pref, $tp;
	if ($type == "do") {
		set_time_limit(180);

		$sql->db_Update("userclass_classes", "userclass_editclass='254' WHERE userclass_editclass ='0' ");

		/*
		changes by jalist 19/01/05:
		altered structure of news table
		*/
		mysql_query("ALTER TABLE ".MPREFIX."news ADD news_comment_total INT (10) UNSIGNED NOT NULL");
		$sql->db_Select_gen("SELECT comment_item_id AS id, COUNT(*) AS amount FROM #comments GROUP BY comment_item_id");
		$commentArray = $sql->db_getList();
		foreach($commentArray as $comments) {
			extract($comments);
			$sql->db_Update("news", "news_comment_total=$amount WHERE news_id=$id");
		}

		mysql_query("ALTER TABLE `".MPREFIX.".content` CHANGE `content_content` `content_content` LONGTEXT NOT NULL");
		/* end */

		/*
		changes by jalist 07/02/2005:
		description stat tables no longer required
		*/
		mysql_query("DROP TABLE ".MPREFIX."stat_counter");
		mysql_query("DROP TABLE ".MPREFIX."stat_info");
		mysql_query("DROP TABLE ".MPREFIX."stat_last");
		/* end */


		/* start poll update */
		$sql -> db_Update("menus", "menu_path='poll' WHERE menu_name='poll_menu' ");
		$query = "CREATE TABLE ".MPREFIX."polls (
		  poll_id int(10) unsigned NOT NULL auto_increment,
		  poll_datestamp int(10) unsigned NOT NULL default '0',
		  poll_start_datestamp int(10) unsigned NOT NULL default '0',
		  poll_end_datestamp int(10) unsigned NOT NULL default '0',
		  poll_admin_id int(10) unsigned NOT NULL default '0',
		  poll_title varchar(250) NOT NULL default '',
		  poll_options text NOT NULL,
		  poll_votes text NOT NULL,
		  poll_ip text NOT NULL,
		  poll_type tinyint(1) unsigned NOT NULL default '0',
		  poll_comment tinyint(1) unsigned NOT NULL default '1',
		  poll_allow_multiple tinyint(1) unsigned NOT NULL default '0',
		  poll_result_type tinyint(2) unsigned NOT NULL default '0',
		  poll_vote_userclass tinyint(3) unsigned NOT NULL default '0',
		  poll_storage_method tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (poll_id)
		) TYPE=MyISAM;";
		$sql->db_Select_gen($query);
		if($sql -> db_Select("poll"))
		{
			$polls = $sql -> db_getList();
			foreach($polls as $row)
			{
				extract($row);
				$poll_options = "";
				$poll_votes = "";
				for($count=1; $count <= 10; $count++)
				{
					$var = "poll_option_".$count;
					$var2 = "poll_votes_".$count;
					if($$var)
					{
						$poll_options .= $$var.chr(1);
						$poll_votes .= $$var2.chr(1);
					}
				}
				$poll_type = (strlen($poll_datestamp) > 9 ? 1 : 2);
				echo "Inserting field #".$poll_id." into new table ...(type: $poll_type)<br />";
				$sql->db_Insert("polls", "$poll_id, $poll_datestamp, 0, $poll_end_datestamp, $poll_admin_id, '$poll_title', '$poll_options', '$poll_votes', '$poll_ip', $poll_type, $poll_comment, 0, 0, 255, 1");
			}
			$sql -> db_Select("polls", "poll_id", "poll_type=1 ORDER BY poll_datestamp DESC LIMIT 0,1");
			$row = $sql -> db_Fetch();
			$sql -> db_Update("polls", "poll_vote_userclass=0 WHERE poll_id=".$row['poll_id']);
			$sql->db_Select_gen("DROP TABLE ".MPREFIX."poll");
		}
		/* end poll update */


		// start links update -------------------------------------------------------------------------------------------
		if ($sql->db_Query("SHOW COLUMNS FROM ".MPREFIX."link_category")) {
			global $IMAGES_DIRECTORY, $PLUGINS_DIRECTORY, $pref;
			$sql->db_Select_gen("CREATE TABLE ".MPREFIX."links_page_cat (
				link_category_id int(10) unsigned NOT NULL auto_increment,
				link_category_name varchar(100) NOT NULL default '',
				link_category_description varchar(250) NOT NULL default '',
				link_category_icon varchar(100) NOT NULL default '',
				PRIMARY KEY  (link_category_id)
				) TYPE=MyISAM;");
			$sql->db_Select_gen("CREATE TABLE ".MPREFIX."links_page (
				link_id int(10) unsigned NOT NULL auto_increment,
				link_name varchar(100) NOT NULL default '',
				link_url varchar(200) NOT NULL default '',
				link_description text NOT NULL,
				link_button varchar(100) NOT NULL default '',
				link_category tinyint(3) unsigned NOT NULL default '0',
				link_order int(10) unsigned NOT NULL default '0',
				link_refer int(10) unsigned NOT NULL default '0',
				link_open tinyint(1) unsigned NOT NULL default '0',
				link_class tinyint(3) unsigned NOT NULL default '0',
				PRIMARY KEY  (link_id)
				) TYPE=MyISAM;");

			$new_cat_id = 1;
			$sql->db_Select("link_category", "*", "link_category_id!=1 ORDER BY link_category_id");
			while ($row = $sql->db_Fetch()) {
				$link_cat_id[$row['link_category_id']] = $new_cat_id;
				if ($row['link_category_icon']) {
					$link_category_icon = strstr($row['link_category_icon'], "/") ? $row['link_category_icon'] : $IMAGES_DIRECTORY."link_icons/".$row['link_category_icon'];
				} else {
					$link_category_icon = "";
				}
				$link_cat_export[] = "'0', '".$row['link_category_name']."', '".$row['link_category_description']."', '".$link_category_icon."'";
				$link_cat_del[] = $row['link_category_id'];
				$new_cat_id++;
			}

			foreach ($link_cat_export as $link_cat_export_commit) {
				if (!$sql->db_Insert("links_page_cat", $link_cat_export_commit)) {
					$links_upd_failed = TRUE;
				}
			}

			$sql->db_Select("links", "*", "link_category!=1 ORDER BY link_category");
			while ($row = $sql->db_Fetch()) {
				if ($row['link_button']) {
					$link_button = strstr($row['link_button'], "/") ? $row['link_button'] : $IMAGES_DIRECTORY."link_icons/".$row['link_button'];
				} else {
					$link_button = "";
				}
				$link_export[] = "'0', '".$row['link_name']."', '".$row['link_url']."', '".$row['link_description']."', '".$link_button."', '".$link_cat_id[$row['link_category']]."', '".$row['link_order']."', '".$row['link_refer']."', '".$row['link_open']."', '".$row['link_class']."'";
				$link_del[] = $row['link_id'];
			}

			foreach ($link_export as $link_export_commit) {
				if (!$sql->db_Insert("links_page", $link_export_commit)) {
					$links_upd_failed = TRUE;
				}
			}

			if (!$links_upd_failed) {
				$sql->db_Select_gen("DROP TABLE ".MPREFIX."link_category");

				foreach ($link_del as $link_del_commit) {
					$sql->db_Delete("links", "link_id='".$link_del_commit."'");
				}
			}
			$sql->db_Insert("plugin", "0, 'Links Page', '1.0', 'links_page', 1");
			$sql->db_Update("links", "link_url = '".$PLUGINS_DIRECTORY."links_page/links.php' WHERE link_url = 'links.php'");

			$pref['plug_latest'] = $pref['plug_latest'].",links_page";
			save_prefs();
		}
		// end links update -------------------------------------------------------------------------------------------

		//  #########  McFly's 0.7 Updates ############

		// parse table obsolete
		mysql_query('DROP TABLE `'.MPREFIX.'parser`');
		mysql_query("ALTER TABLE ".MPREFIX."menus ADD menu_path VARCHAR( 100 ) NOT NULL");
		mysql_query("UPDATE ".MPREFIX."menus SET menu_path = 'custom', menu_name = substring(menu_name,8) WHERE substring(menu_name,1,6) = 'custom'");
		mysql_query("UPDATE ".MPREFIX."menus SET menu_path = menu_name  WHERE menu_path = ''");

		// New dblog table for logging db calls (admin log)
		$sql->db_Select_gen(
		"CREATE TABLE ".MPREFIX."dblog (
			dblog_id int(10) unsigned NOT NULL auto_increment,
			dblog_type varchar(60) NOT NULL default '',
			dblog_datestamp int(10) unsigned NOT NULL default '0',
			dblog_user_id int(10) unsigned NOT NULL default '0',
			dblog_ip varchar(80) NOT NULL default '',
			dblog_query text NOT NULL,
			dblog_remarks varchar(255) NOT NULL default '',
			PRIMARY KEY  (dblog_id)
			) TYPE=MyISAM;
			");

		// New generic table for storing any miscellaneous data
		$sql->db_Select_gen(
		"CREATE TABLE ".MPREFIX."generic (
			gen_id int(10) unsigned NOT NULL auto_increment,
  			gen_type varchar(80) NOT NULL default '',
  			gen_datestamp int(10) unsigned NOT NULL default '0',
			gen_user_id int(10) unsigned NOT NULL default '0',
			gen_ip varchar(80) NOT NULL default '',
			gen_intdata int(10) unsigned NOT NULL default '0',
			gen_chardata text NOT NULL,
			PRIMARY KEY  (gen_id)
			) TYPE=MyISAM;
		");

		$sql->db_Select_gen(
		"CREATE TABLE ".MPREFIX."generic (
			gen_id int(10) unsigned NOT NULL auto_increment,
  			gen_type varchar(80) NOT NULL default '',
  			gen_datestamp int(10) unsigned NOT NULL default '0',
			gen_user_id int(10) unsigned NOT NULL default '0',
			gen_ip varchar(80) NOT NULL default '',
			gen_intdata int(10) unsigned NOT NULL default '0',
			gen_chardata text NOT NULL,
			PRIMARY KEY  (gen_id)
			) TYPE=MyISAM;
		");

		$sql->db_Select_gen(
		"CREATE TABLE ".MPREFIX."user_extended (
  			user_extended_id int(10) unsigned NOT NULL default '0',
  			PRIMARY KEY  (user_extended_id)
			) TYPE=MyISAM;
    	");

		$sql->db_Select_gen(
		"CREATE TABLE ".MPREFIX."user_extended_struct (
  			user_extended_struct_id int(10) unsigned NOT NULL auto_increment,
  			user_extended_struct_name varchar(255) NOT NULL default '',
  			user_extended_struct_text varchar(255) NOT NULL default '',
  			user_extended_struct_type tinyint(3) unsigned NOT NULL default '0',
  			user_extended_struct_parms varchar(255) NOT NULL default '',
  			user_extended_struct_values text NOT NULL,
  			user_extended_struct_default varchar(255) NOT NULL default '',
  			user_extended_struct_read tinyint(3) unsigned NOT NULL default '0',
  			user_extended_struct_write tinyint(3) unsigned NOT NULL default '0',
  			user_extended_struct_required tinyint(3) unsigned NOT NULL default '0',
  			user_extended_struct_signup tinyint(3) unsigned NOT NULL default '0',
  			PRIMARY KEY  (user_extended_struct_id)
			) TYPE=MyISAM;
		");

		$sql->db_Select_gen("ALTER TABLE #user_extended_struct ADD user_extended_struct_applicable TINYINT( 3 ) UNSIGNED NOT NULL");

		//Begin Extended user field conversion
		require_once(e_HANDLER."user_extended_class.php");
		$ue = new e107_user_extended;
		
		$sql->db_Select("core", " e107_value", " e107_name='user_entended'", 'default');
		$row = $sql->db_Fetch();
		
		$user_extended = unserialize($row['e107_value']);
		$new_types = array('text' => 1, 'radio' => 2, 'dropdown' => 3, 'table' => 4);
		
		foreach($user_extended as $key => $val)
		{
			unset($new_field);
			$parms = explode("|", $val);
			$ext_name['ue_'.$key] = 'user_'.$parms[0];
			$new_field['name'] = preg_replace("#\W#","",$parms[0]);
			$new_field['text'] = $parms[0];
			$new_field['type'] = $new_types[$parms[1]];
			$new_field['values'] = $parms[2];
			$new_field['default'] = $parms[3];
			$new_field['applicable'] = $parms[4];
			$new_field['read'] = $parms[5];
			$new_field['write'] = e_UC_MEMBER;
			$new_field['signup'] = $pref['signup_ext'.$key];
			$new_field['parms'] = "";
			$new_field['required'] = 0;
			unset($pref['signup_ext'.$key]);
			unset($pref['signup_ext_req'.$key]);
			$ue->user_extended_add($new_field);
		}
		save_prefs('core');
		if($sql->db_Select('user','user_id, user_prefs',"1 ORDER BY user_id"))
		{
			$sql2 = new db;
			while($row = $sql->db_Fetch())
			{
				set_time_limit(30);
				$user_pref = unserialize($row['user_prefs']);
				$new_values = "";
				foreach($user_pref as $key => $val)
				{
					if(array_key_exists($key, $ext_name))
					{
						unset($user_pref[$key]);
						if($val)
						{
							if($new_values)
							{
								$new_values .= " ,";
							}
							$new_values .= $ext_name[$key]."='".$val."'";
						}
					}
				}
				foreach ($user_pref as $key => $prefvalue) {
					$user_pref[$key] = $tp->toDB($prefvalue);
				}
				$tmp=addslashes(serialize($user_pref));
				$sql2->db_Update("user", "user_prefs='$tmp' WHERE user_id='{$row['user_id']}'");
				if($new_values)
				{
					$sql2->db_Select_gen("INSERT INTO #user_extended (user_extended_id) values ('{$row['user_id']}')");
					$sql2->db_Update('user_extended', $new_values." WHERE user_extended_id = '{$row['user_id']}'");
				}
			}
		}
		$sql->db_Select_gen("DELETE FROM #core WHERE e107_name='user_entended'");

		if(!array_key_exists('ue_upgrade', $pref))
		{
			$pref['ue_upgrade'] = 1;
			save_prefs();
		}
		//End Extended user field conversion


		// Update user_class field to use #,#,# instead of #.#.#. notation
		if ($sql->db_Select('user', 'user_id, user_class')) {
			$sql2 = new db;
			while ($row = $sql->db_Fetch()) {
				$carray = explode('.', $row['user_class']);
				$carray = array_unique(array_diff($carray, array('')));
				if (count($carray) > 1) {
					$new_userclass = implode(',', $carray);
				} else {
					$new_userclass = $carray[0];
				}
				$sql2->db_Update('user', "user_class = '{$new_userclass}' WHERE user_id={$row['user_id']}");
			}
		}


		mysql_query("ALTER TABLE ".MPREFIX."generic` CHANGE gen_chardata gen_chardata TEXT NOT NULL");
		mysql_query("ALTER TABLE ".MPREFIX."banner CHANGE banner_active banner_active TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'");
		mysql_query('DROP TABLE `'.MPREFIX.'cache`'); // db cache is no longer an available option..
		$sql->db_Update("banner", "banner_active='255' WHERE banner_active = '0' ");
		$sql->db_Update("banner", "banner_active='0' WHERE banner_active = '1' ");
		$sql->db_Update("wmessage", "wm_active='255' WHERE wm_active = '0' ");
		$sql->db_Update("wmessage", "wm_active='252' WHERE wm_id = '1' AND wm_active='1' ");
		$sql->db_Update("wmessage", "wm_active='253' WHERE wm_id = '2' AND wm_active='1' ");
		$sql->db_Update("wmessage", "wm_active='254' WHERE wm_id = '3' AND wm_active='1' ");
		mysql_query("ALTER IGNORE TABLE `".MPREFIX."wmessage` ADD UNIQUE INDEX(wm_id)");
		mysql_query("ALTER TABLE `".MPREFIX."wmessage` CHANGE `wm_id` `wm_id` TINYINT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT");

		/*
		Changes by McFly 2/12/2005
		Moving forum rules from wmessage table to generic table
		*/

		if($sql->db_Select("wmessage"))
		{
			while($row = $sql->db_Fetch())
			{
				$wmList[] = $row;
			}
			foreach($wmList as $wm)
			{
				$gen_type='wmessage';
				if($wm['wm_id'] == '4') {$gen_type = 'forum_rules_guest';}
				if($wm['wm_id'] == '5') {$gen_type = 'forum_rules_member';}
				if($wm['wm_id'] == '6') {$gen_type = 'forum_rules_admin';}
				$fieldlist = "";
				if($gen_type != "wmessage")
				{
					$exists = $sql->db_Count('generic','(*)',"WHERE gen_type = '{$gen_type}'");
					if(!$exists)
					{
						$fieldlist = "0,'$gen_type','".time()."','".USERID."','',{$wm['wm_active']},'{$wm['wm_text']}'";
					}
				}
				else
				{
					if($wm['wm_id'] == '1') {$wm_class = e_UC_GUEST;}
					if($wm['wm_id'] == '2') {$wm_class = e_UC_MEMBER;}
					if($wm['wm_id'] == '3') {$wm_class = e_UC_ADMIN;}
					$fieldlist = "0,'wmessage','".time()."','".USERID."','',{$wm_class},'{$wm['wm_text']}'";
				}
				if($fieldlist)
				{
					$sql->db_Insert('generic',$fieldlist);
				}
				$sql->db_Delete('wmessage',"WHERE wm_id = '{$wm['wm_id']}'");
			}
		}
		mysql_query('DROP TABLE '.MPREFIX.'wmessage');  // table wmessage is no longer needed.

		// ############# END McFly's Updates  ##############

		// start chatbox update -------------------------------------------------------------------------------------------
		if (!$sql->db_Select("plugin", "plugin_path", "plugin_path='chatbox_menu'")) {
			global $pref;
			$sql->db_Insert("plugin", "0, 'Chatbox', '1.0', 'chatbox_menu', 1");
			$pref['plug_status'] = $pref['plug_status'].",chatbox_menu";
			save_prefs();
		}
		// end chatbox update -------------------------------------------------------------------------------------------

		// Cam's new PRESET Table. -------------------------------------------------------------------------------------------
		$sql->db_Select_gen(
		"CREATE TABLE ".MPREFIX."preset (
		preset_id int(10) unsigned NOT NULL auto_increment,
		preset_name varchar(80) NOT NULL default '',
		preset_field varchar(80) NOT NULL default '',
		preset_value varchar(255) NOT NULL default '',
		PRIMARY KEY  (preset_id)
		) TYPE=MyISAM;
		");
		// News Updates
		mysql_query("ALTER TABLE `".MPREFIX."news` CHANGE `news_image` `news_summary` TEXT DEFAULT NULL;");
		mysql_query("ALTER TABLE `".MPREFIX."news` CHANGE `news_thumb` `news_attach` TEXT DEFAULT NULL;");
		mysql_query("ALTER TABLE ".MPREFIX."news ADD news_sticky TINYINT ( 3 ) UNSIGNED NOT NULL");
		
		// Downloads updates - Added March 1, 2005 by McFly
		mysql_query("ALTER TABLE ".MPREFIX."download ADD download_class TINYINT ( 3 ) UNSIGNED NOT NULL");
		mysql_query("ALTER TABLE ".MPREFIX."download_category ADD download_category_order INT ( 10 ) UNSIGNED NOT NULL");
		$sql->db_Select_gen(
		"CREATE TABLE ".MPREFIX."download_requests (
  			download_request_id int(10) unsigned NOT NULL auto_increment,
  			download_request_userid int(10) unsigned NOT NULL default '0',
  			download_request_ip varchar(30) NOT NULL default '',
  			download_request_download_id int(10) unsigned NOT NULL default '0',
  			download_request_datestamp int(10) unsigned NOT NULL default '0',
  			PRIMARY KEY  (download_request_id)
			) TYPE=MyISAM;
		");
		
		// Search Update
		if (!$sql->db_Select("core", "e107_name", "e107_name='search_prefs'")) {
			$sql->db_Insert('core', "'search_prefs', 'a:4:{s:12:\"search_chars\";s:3:\"150\";s:11:\"search_sort\";s:3:\"php\";s:6:\"google\";s:2:\"on\";s:13:\"core_handlers\";a:4:{s:4:\"news\";s:2:\"on\";s:8:\"comments\";s:2:\"on\";s:9:\"downloads\";s:2:\"on\";s:5:\"users\";s:2:\"on\";}}'");
   		}

		// Search Update 2
        global $sysprefs;
        $search_prefs = $sysprefs -> getArray('search_prefs');
        if (!isset($search_prefs['plug_handlers'])) {
      	  $handle = opendir(e_PLUGIN);
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) {
					$plugin_handle = opendir(e_PLUGIN.$file."/");
					while (false !== ($file2 = readdir($plugin_handle))) {
						if ($file2 == "e_search.php") {
							if ($sql->db_Select("plugin", "plugin_path", "plugin_path='".$file."' AND plugin_installflag='1'")) {
								$plugin_handlers[$file] = TRUE;
							}
						}
					}
				}
			}
			$search_prefs['plug_handlers'] = $plugin_handlers;
			$tmp = addslashes(serialize($search_prefs));
			$sql->db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs' ");
		}
		
		// Search Update 3
		if (!isset($search_prefs['search_res'])) {
			$search_prefs['search_res'] = '10';
			$tmp = addslashes(serialize($search_prefs));
			$sql->db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs' ");
		}
		
		// Search Update 4
		if (!isset($search_prefs['relevance'])) {
			$search_prefs['relevance'] = 'on';
			$search_prefs['user_select'] = 'on';
			$tmp = addslashes(serialize($search_prefs));
			$sql->db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs' ");
		}
		
		// Search Update 5
		if (!isset($search_prefs['time_restrict'])) {
			$search_prefs['time_restrict'] = '';
			$search_prefs['time_secs'] = '60';
			$tmp = addslashes(serialize($search_prefs));
			$sql->db_Update("core", "e107_value='".$tmp."' WHERE e107_name='search_prefs' ");
		}
		
} else {
		// check if update is needed.
		// FALSE = needed, TRUE = not needed.
		
//		return $sql->db_Query("SHOW COLUMNS FROM ".MPREFIX."user_extended_struct");
/*
		$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."user_extended_struct");
		$fieldname = mysql_field_name($fields,12);
	 	return ($fieldname == "user_extended_struct_applicable") ? TRUE : FALSE;
*/
		//return !$sql->db_Select("core","*","e107_name = 'user_entended'");

//		$sql->db_Select_gen("DELETE FROM #core WHERE e107_name='user_entended'");

        global $sysprefs;
        $search_prefs = $sysprefs -> getArray('search_prefs');
		if (!isset($search_prefs['time_secs'])) {
			return FALSE;
		} else {
			return TRUE;
		}

	}
}


function update_616_to_617($type) {
	global $sql;
	if ($type == "do") {
		mysql_query("ALTER TABLE  ".MPREFIX."poll ADD poll_comment TINYINT( 3 ) UNSIGNED DEFAULT '1' NOT NULL ");
		mysql_query("ALTER TABLE  ".MPREFIX."menus ADD menu_pages TEXT NOT NULL ");
		$sql2 = new db;
		$sql2->db_Update("poll", "poll_comment='1' WHERE poll_id!='0'");
	} else {
		global $mySQLdefaultdb;
		$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."menus");
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
			if ("menu_pages" == mysql_field_name($fields, $i)) {
				return TRUE;
			}
		}
		return FALSE;
	}
}

function update_615_to_616($type) {
	global $sql;
	if ($type == "do") {
		mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES (4, 'This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on.', '0')");
		mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES (5, 'Member rules ----- This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on - only logged in members will see this.', '0')");
		mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES (6, 'Administrator rules ----- This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on - only logged in administrators will see this.', '0')");
		mysql_query("ALTER TABLE ".MPREFIX."download ADD download_comment TINYINT( 3 ) UNSIGNED NOT NULL ");
		mysql_query("ALTER TABLE ".MPREFIX."chatbox CHANGE cb_nick cb_nick VARCHAR( 30 ) NOT NULL ");
		mysql_query("ALTER TABLE ".MPREFIX."comments CHANGE comment_type comment_type VARCHAR( 10 ) DEFAULT '0' NOT NULL ");
		mysql_query("ALTER TABLE ".MPREFIX."comments ADD comment_pid INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL AFTER comment_id ");
		mysql_query("ALTER TABLE ".MPREFIX."comments ADD comment_subject VARCHAR( 100 ) NOT NULL AFTER comment_item_id ");
		mysql_query("ALTER TABLE ".MPREFIX."user ADD user_customtitle VARCHAR( 100 ) NOT NULL AFTER user_name ");
		mysql_query("ALTER TABLE ".MPREFIX."parser ADD UNIQUE (parser_regexp)");
		mysql_query("ALTER TABLE ".MPREFIX."userclass_classes ADD userclass_editclass TINYINT( 3 ) UNSIGNED NOT NULL ");
		update_extended_616();
	} else {
		global $mySQLdefaultdb;
		$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."userclass_classes");
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
			if ("userclass_editclass" == mysql_field_name($fields, $i)) {
				return TRUE;
			}
		}
		return FALSE;
	}
}

function update_614_to_615($type) {
	global $sql;
	if ($type == "do") {
		mysql_query("ALTER TABLE ".MPREFIX."submitnews ADD submitnews_category TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER submitnews_title");
		mysql_query("ALTER TABLE ".MPREFIX."upload ADD upload_category TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'");
		mysql_query("ALTER TABLE ".MPREFIX."online ADD online_pagecount tinyint(3) unsigned NOT NULL default '0'");
		mysql_query("ALTER TABLE ".MPREFIX."submitnews ADD submitnews_file VARCHAR(100) NOT NULL default '' ");

		global $DOWNLOADS_DIRECTORY;
		$sql2 = new db;
		$sql->db_Select("download", "download_id, download_url", "download_filesize=0");
		while ($row = $sql->db_Fetch()) {
			extract($row);
			$sql2->db_Update("download", "download_filesize='".filesize(e_BASE.$DOWNLOADS_DIRECTORY.$download_url)."' WHERE download_id='".$download_id."'");
		}
	} else {
		global $mySQLdefaultdb;
		$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."submitnews");
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
			if ("submitnews_file" == mysql_field_name($fields, $i)) {
				return TRUE;
			}
		}
		return FALSE;
	}
}

function update_611_to_612($type) {
	global $sql;
	if ($type == "do") {
		mysql_query("ALTER TABLE ".MPREFIX."news ADD news_render_type TINYINT UNSIGNED NOT NULL ");
		mysql_query("ALTER TABLE ".MPREFIX."content CHANGE content_parent content_parent INT UNSIGNED DEFAULT '0' NOT NULL ");
	} else {
		global $mySQLdefaultdb;
		$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."news");
		$columns = mysql_num_fields($fields);
		for ($i = 0; $i < $columns; $i++) {
			if ("news_render_type" == mysql_field_name($fields, $i)) {
				return TRUE;
			}
		}
		return FALSE;
	}
}

function update_603_to_604($type) {
	global $sql;
	if ($type == "do") {
		mysql_query("ALTER TABLE ".MPREFIX."link_category ADD link_category_icon VARCHAR( 100 ) NOT NULL");
		mysql_query("ALTER TABLE ".MPREFIX."headlines ADD headline_image VARCHAR( 100 ) NOT NULL AFTER headline_description");
		mysql_query("ALTER TABLE ".MPREFIX."content CHANGE content_page content_parent TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL");
		mysql_query("ALTER TABLE ".MPREFIX."content ADD content_review_score TINYINT UNSIGNED NOT NULL AFTER content_type");
		mysql_query("ALTER TABLE ".MPREFIX."content CHANGE content_author content_author VARCHAR( 200 ) NOT NULL");
		mysql_query("ALTER TABLE ".MPREFIX."content ADD content_pe_icon TINYINT( 1 ) UNSIGNED NOT NULL AFTER content_review_score");
	} else {
		global $mySQLdefaultdb;
		if ($sql->db_Query("SHOW COLUMNS FROM ".MPREFIX."link_category")) {
			$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."link_category");
			$columns = mysql_num_fields($fields);
			for ($i = 0; $i < $columns; $i++) {
				if ("link_category_icon" == mysql_field_name($fields, $i)) {
					return TRUE;
				}
			}
			return FALSE;
		} else {
			return TRUE;
		}
	}
}

function update_extended_616() {
	global $sql, $ns;
	$sql2 = new db;
	if ($sql2->db_Select("core", " e107_value", " e107_name='user_entended'")) {
		$row = $sql2->db_Fetch();
		$user_extended = unserialize($row[0]);
		if (count($user_extended)) {
			if ($sql->db_Select("user", "user_id,user_prefs")) {
				while ($row = $sql->db_Fetch()) {
					$uid = $row[0];
					$user_pref = unserialize($row[1]);
					foreach($user_extended as $key => $v) {
						list($fname, $null) = explode("|", $v, 2);
						$fname = $v;
						if (isset($user_pref[$fname])) {
							$user_pref["ue_{$key}"] = $user_pref[$fname];
							unset($user_pref[$fname]);
						}
					}
					$tmp = addslashes(serialize($user_pref));
					$sql2->db_Update("user", "user_prefs='$tmp' WHERE user_id=$uid");
				}
			}
		}
	}
	$ns->tablerender("Extended Users", "Updated extended user field data");
}

?>