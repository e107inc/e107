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
|     $Revision: 1.16 $
|     $Date: 2005-01-27 19:52:24 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
	
$dbupdate = array(
"61x_to_700" => LAN_UPDATE_8." .61x ".LAN_UPDATE_9." .7",
	"616_to_617" => LAN_UPDATE_8." .616 ".LAN_UPDATE_9." .617",
	"615_to_616" => LAN_UPDATE_8." .615 ".LAN_UPDATE_9." .616",
	"614_to_615" => LAN_UPDATE_8." .614 ".LAN_UPDATE_9." .615",
	"611_to_612" => LAN_UPDATE_8." .611 ".LAN_UPDATE_9." .612",
	"603_to_604" => LAN_UPDATE_8." .603 ".LAN_UPDATE_9." .604",
	);
	
	
function update_check() {
	global $ns, $dbupdate;
	foreach($dbupdate as $func => $rmks) {
		if (function_exists("update_".$func)) {
			if (!call_user_func("update_".$func)) {
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
	global $sql, $ns;
	if ($type == "do") {
		$sql->db_Update("userclass_classes", "userclass_editclass='254' WHERE userclass_editclass ='0' ");
		 
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
		changes by jalist 19/01/05:
		altered structure of news table
		*/
		mysql_query("ALTER TABLE ".MPREFIX."e107_news ADD 'news_comment_total' INT UNSIGNED NOT NULL");
		$sql->db_Select_gen("SELECT comment_item_id AS id, COUNT(*) AS amount FROM #comments GROUP BY comment_item_id");
		$commentArray = $sql->db_getList();
		foreach($commentArray as $comments) {
			extract($comments);
			$sql->db_Update("news", "news_comment_total=$amount WHERE news_id=$id");
		}
		/*
		changes by jalist 26/01/2005:
		altered structure of forum_t table
		*/
		 
		$sql->db_Select_gen("SELECT thread_parent AS id, COUNT(*) AS amount FROM #forum_t WHERE thread_parent!=0 GROUP BY thread_parent");
		$threadArray = $sql->db_getList();
		foreach($threadArray as $threads) {
			extract($threads);
			$sql->db_Update("forum_t", "thread_total_replies=$amount WHERE thread_id=$id");
		}
		 
		 
		/* end */
		 
		// start links update -------------------------------------------------------------------------------------------
		if (!$sql->db_Query("SHOW COLUMNS FROM ".MPREFIX."links_page")) {
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
					$link_category_icon = strstr($row['link_category_icon'], "/") ? $row['link_category_icon'] :
					 $IMAGES_DIRECTORY."link_icons/".$row['link_category_icon'];
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
					$link_button = strstr($row['link_button'], "/") ? $row['link_button'] :
					 $IMAGES_DIRECTORY."link_icons/".$row['link_button'];
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
		// ############# END McFly's Updates  ##############
	} else {
		// check if update is needed.
		// FALSE = needed, TRUE = not needed.
		//   $fields = mysql_list_fields($mySQLdefaultdb,MPREFIX."wmessage");
		//    $columns = mysql_num_fields($fields);
		return $sql->db_Query("SHOW COLUMNS FROM ".MPREFIX."dblog");
		 
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