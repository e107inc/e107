<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");

if (isset($pref['signup_options'])) // CONVERT 0.6 STYLE SIGNUP OPTIONS
{
    $tmp = explode(".", $pref['signup_options']);
	$pref['signup_option_realname']  = $tmp[0];
	$pref['signup_option_signature'] = $tmp[7];
	$pref['signup_option_image']     = $tmp[8];
	$pref['signup_option_timezone']  = $tmp[9];
	$pref['signup_option_class']     = $tmp[10];
	unset($pref['signup_options']);
	save_prefs();
}

if (!$pref['displayname_maxlength'])
{
  $pref['displayname_maxlength'] = 15;
  save_prefs();
}

if (!defined("LAN_UPDATE_8")) { define("LAN_UPDATE_8", ""); }
if (!defined("LAN_UPDATE_9")) { define("LAN_UPDATE_9", ""); }



$dbupdate = array();

global $e107cache;
	if (is_readable(e_ADMIN."ver.php"))
	{
		include(e_ADMIN."ver.php");
	}


// If $dont_check_update is both defined and TRUE on entry, a check for update is done only once per 24 hours.
$dont_check_update = varset($dont_check_update, FALSE);


if ($dont_check_update === TRUE)
{
  $dont_check_update = FALSE;
  if ($tempData = $e107cache->retrieve("nq_admin_updatecheck",3600, TRUE))
  {	// See when we last checked for an admin update
    list($last_time, $dont_check_update,$last_ver) = explode(',',$tempData);
	if ($last_ver != $e107info['e107_version']) 
	{
	  $dont_check_update = FALSE;	// Do proper check on version change
	}
  }
}

if (!$dont_check_update)
{ 
if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'forum' AND plugin_installflag='1' ")) {
	if(file_exists(e_PLUGIN.'forum/forum_update_check.php'))
	{
		include_once(e_PLUGIN.'forum/forum_update_check.php');
	}
}
if (mysql_table_exists("stat_info") && $sql -> db_Select("plugin", "*", "plugin_path = 'log' AND plugin_installflag='1'")) {
	if(file_exists(e_PLUGIN.'log/log_update_check.php'))
	{
		include_once(e_PLUGIN.'log/log_update_check.php');
	}
}

//content
if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'content' AND plugin_installflag='1' "))
{
	if(file_exists(e_PLUGIN.'content/content_update_check.php'))
	{
		include_once(e_PLUGIN.'content/content_update_check.php');
	}
}

if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'pm' AND plugin_installflag='1' "))
{
	if(file_exists(e_PLUGIN.'pm/pm_update_check.php'))
	{
		include_once(e_PLUGIN.'pm/pm_update_check.php');
	}
}

// $dbupdate["701_to_702"] = LAN_UPDATE_8." .7.1 ".LAN_UPDATE_9." .7.2";
$dbupdate["70x_to_706"] = LAN_UPDATE_8." .70x ".LAN_UPDATE_9." .706";
$dbupdate["617_to_700"] = LAN_UPDATE_8." .617 ".LAN_UPDATE_9." .7";
$dbupdate["616_to_617"] = LAN_UPDATE_8." .616 ".LAN_UPDATE_9." .617";
$dbupdate["615_to_616"] = LAN_UPDATE_8." .615 ".LAN_UPDATE_9." .616";
$dbupdate["614_to_615"] = LAN_UPDATE_8." .614 ".LAN_UPDATE_9." .615";
$dbupdate["611_to_612"] = LAN_UPDATE_8." .611 ".LAN_UPDATE_9." .612";
$dbupdate["603_to_604"] = LAN_UPDATE_8." .603 ".LAN_UPDATE_9." .604";

}

function update_check() 
{
  global $ns, $dont_check_update, $e107info;
  
  $update_needed = FALSE;
  
  if ($dont_check_update === FALSE)
  {
	global $dbupdate, $dbupdatep, $e107cache;
	foreach($dbupdate as $func => $rmks) {
		if (function_exists("update_".$func)) {
			if (!call_user_func("update_".$func, FALSE)) {
				$update_needed = TRUE;
				continue;
			}
		}
	}

	foreach($dbupdatep as $func => $rmks) {
		if (function_exists("update_".$func)) {
			if (!call_user_func("update_".$func, FALSE)) {
				$update_needed = TRUE;
				continue;
			}
		}
	}
  	$e107cache->set("nq_admin_updatecheck", time().','.($update_needed ? '2,' : '1,').$e107info['e107_version'], TRUE);
  }
  else  
  {
    $update_needed = ($dont_check_update == '2');
  }


	if ($update_needed === TRUE) {
		$txt = "<div style='text-align:center;'>".ADLAN_120;
		$txt .= "<br /><form method='post' action='".e_ADMIN."e107_update.php'>
		<input class='button' type='submit' value='".LAN_UPDATE."' />
		</form></div>";
		$ns->tablerender(LAN_UPDATE, $txt);
	}
}		// Function update_check




/*
// ------------------------------- .7.1 to .7.2 etc ----------------------------------
function update_701_to_702($type='') {

//  $sql->db_Query_all() must be used for all mysql queries to avoid serious multi-language issues.


}
*/

function update_70x_to_706($type='') {
	global $sql,$ns;

	if ($type == "do")
	{
		//rename plugin_rss field
		if($sql->db_Field("plugin",5) == "plugin_rss")
		{
			mysql_query("ALTER TABLE `".MPREFIX."plugin` CHANGE `plugin_rss` `plugin_addons` TEXT NOT NULL;");
			catch_error();
		}

		if(!$sql->db_Field("plugin",5))  // not plugin_rss so just add the new one.
		{
        	mysql_query("ALTER TABLE `".MPREFIX."plugin` ADD `plugin_addons` TEXT NOT NULL ;");
			catch_error();
		}

		if($sql->db_Field("dblog",5) == "dblog_query")
		{
			mysql_query("ALTER TABLE `".MPREFIX."dblog` CHANGE `dblog_query` `dblog_title` VARCHAR( 255 ) NOT NULL DEFAULT '';");
			catch_error();
			mysql_query("ALTER TABLE `".MPREFIX."dblog` CHANGE `dblog_remarks` `dblog_remarks` TEXT NOT NULL;");
			catch_error();
		}

		if(!$sql->db_Field("plugin","plugin_path","UNIQUE"))
		{
            if(!mysql_query("ALTER TABLE `".MPREFIX."plugin` ADD UNIQUE (`plugin_path`);"))
			{
				$mes = "<div style='text-align:center'>".LAN_UPDATE_12." : <a href='".e_ADMIN."db.php?plugin'>".ADLAN_145."</a>.</div>";
                $ns -> tablerender(LAN_ERROR,$mes);
            	catch_error();
			}

		}

		if ($sql -> db_Query("SHOW INDEX FROM ".MPREFIX."tmp")) 
		{
			$row = $sql -> db_Fetch();
			if (!in_array('tmp_ip', $row)) {
				mysql_query("ALTER TABLE `".MPREFIX."tmp` ADD INDEX `tmp_ip` (`tmp_ip`);");
				mysql_query("ALTER TABLE `".MPREFIX."upload` ADD INDEX `upload_active` (`upload_active`);");
				mysql_query("ALTER TABLE `".MPREFIX."generic` ADD INDEX `gen_type` (`gen_type`);");
			}
		}


		// update new fields
        require_once(e_HANDLER."plugin_class.php");
		$ep = new e107plugin;
		$ep->update_plugins_table();
		$ep->save_addon_prefs();

		if(!$sql->db_Field("online",6)) // online_active field
		{
			mysql_query("ALTER TABLE ".MPREFIX."online ADD online_active INT(10) UNSIGNED NOT NULL DEFAULT '0'");
			catch_error();
		}

		return '';

	}
	else
	{

		if($sql->db_Field("plugin",5) == "plugin_rss")
		{
			return update_needed();
		}

		if($sql->db_Field("dblog",5) == "dblog_query")
		{
        	return update_needed();
		}

		if(!$sql->db_Field("plugin",5))
		{
			return update_needed();
		}

		if(!$sql->db_Field("plugin","plugin_path","UNIQUE"))
		{
            return update_needed();
		}

		if(!$sql->db_Field("online",6)) // online_active field
		{
			return update_needed();
		}
		
		if ($sql -> db_Query("SHOW INDEX FROM ".MPREFIX."tmp")) 
		{
			$row = $sql -> db_Fetch();
			if (!in_array('tmp_ip', $row)) {
				return update_needed();
			}
		}

		// No updates needed
	 	return TRUE;
	}
}

// ------------------------------- .6 to .7 ----------------------------------
function update_617_to_700($type='') {
	global $sql, $ns, $mySQLdefaultdb, $pref, $tp, $sysprefs, $eArrayStorage;
	if ($type == "do") {

		set_time_limit(400);
		$s_prefs = FALSE;

		// Lets build an array with all the table names.
		$result = mysql_query("SHOW tables");
		while($row = mysql_fetch_row($result))
		{
			$tablenames[]=$row[0];
		}

		// Switch 0.6xx upgraders back to standard English pack ========================
		if ($pref['sitelanguage'] == 'English-iso') {
			$pref['sitelanguage'] = 'English';
			$s_prefs = TRUE;
		}

		if ($sql -> db_Select("link_category", "link_category_id")){
			if (is_dir(e_LANGUAGEDIR.e_LANGUAGE."-iso")) {
				$pref['sitelanguage'] = $pref['sitelanguage']."-iso";
				$s_prefs = TRUE;
			}
		}

		// ==============================================================

		// add an index on user_ban - speeds up page render time massively on large user tables.
		mysql_query("ALTER TABLE `".MPREFIX."user` ADD INDEX `user_ban_index`(`user_ban`);");
		catch_error();

		if(!$sql -> db_Select("userclass_classes", "*", "userclass_editclass='254' ")){
			$sql->db_Update("userclass_classes", "userclass_editclass='254' WHERE userclass_editclass ='0' ");
			catch_error();
		}

		/*
		changes by jalist 19/01/05:
		altered structure of news table
		*/
			mysql_query("ALTER TABLE ".MPREFIX."news ADD news_comment_total int(10) unsigned NOT NULL default '0'");
			catch_error();
			$sql->db_Select_gen("SELECT comment_item_id AS id, COUNT(*) AS amount FROM #comments GROUP BY comment_item_id");
			$commentArray = $sql->db_getList();
			foreach($commentArray as $comments) {
				extract($comments);
				$sql->db_Update("news", "news_comment_total=$amount WHERE news_id=$id");
				catch_error();
			}
			mysql_query("ALTER TABLE `".MPREFIX."content` CHANGE `content_content` `content_content` LONGTEXT NOT NULL");
			catch_error();
		/* end */

		/* start poll update */
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
			catch_error();
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
					$sql->db_Insert("polls", "$poll_id, $poll_datestamp, 0, $poll_end_datestamp, $poll_admin_id, '$poll_title', '$poll_options', '$poll_votes', '$poll_ip', $poll_type, $poll_comment, 0, 0, 255, 1");
					catch_error();
				}
				$sql -> db_Select("polls", "poll_id", "poll_type=1 ORDER BY poll_datestamp DESC LIMIT 0,1");
				$row = $sql -> db_Fetch();
				$sql -> db_Update("polls", "poll_vote_userclass=0 WHERE poll_id=".$row['poll_id']);
				$sql->db_Select_gen("DROP TABLE ".MPREFIX."poll");
				catch_error();
			}
		/* end poll update */

		/* general table structure changes */
			mysql_query("ALTER TABLE `".MPREFIX."user` CHANGE `user_sess` `user_sess` varchar(100) NOT NULL default ''");
			catch_error();
		/*	end	*/


		/* start newsfeed update */
		if (!mysql_table_exists('newsfeed')) {
			$sql->db_Select_gen("CREATE TABLE ".MPREFIX."newsfeed (
			newsfeed_id int(10) unsigned NOT NULL auto_increment,
			newsfeed_name varchar(150) NOT NULL default '',
			newsfeed_url varchar(150) NOT NULL default '',
			newsfeed_data longtext NOT NULL,
			newsfeed_timestamp int(10) unsigned NOT NULL default '0',
			newsfeed_description text NOT NULL,
			newsfeed_image varchar(100) NOT NULL default '',
			newsfeed_active tinyint(1) unsigned NOT NULL default '0',
			newsfeed_updateint int(10) unsigned NOT NULL default '0',
			PRIMARY KEY  (newsfeed_id)
			) TYPE=MyISAM;");
			catch_error();
		}

		if (mysql_table_exists('newsfeed')) {
			mysql_query("ALTER TABLE `".MPREFIX."newsfeed` CHANGE `newsfeed_data` `newsfeed_data` LONGTEXT NOT NULL");
			catch_error();
			$sql -> db_Update("newsfeed", "newsfeed_timestamp='0' ");
			catch_error();
		}
		/*	end 	*/

		/* start emote update */
			$tmp =
			'a:28:{s:9:"alien!png";s:6:"!alien";s:10:"amazed!png";s:7:"!amazed";s:9:"angry!png";s:11:"!grr !angry";s:12:"biglaugh!png";s:4:"!lol";s:11:"cheesey!png";s:10:":D :oD :-D";s:12:"confused!png";s:10:":? :o? :-?";s:7:"cry!png";s:19:"&| &-| &o| :(( !cry";s:8:"dead!png";s:21:"x) xo) x-) x( xo( x-(";s:9:"dodge!png";s:6:"!dodge";s:9:"frown!png";s:10:":( :o( :-(";s:7:"gah!png";s:10:":@ :o@ :o@";s:8:"grin!png";s:10:":D :oD :-D";s:9:"heart!png";s:6:"!heart";s:8:"idea!png";s:10:":! :o! :-!";s:7:"ill!png";s:4:"!ill";s:7:"mad!png";s:13:"~:( ~:o( ~:-(";s:12:"mistrust!png";s:9:"!mistrust";s:11:"neutral!png";s:10:":| :o| :-|";s:12:"question!png";s:2:"?!";s:12:"rolleyes!png";s:10:"B) Bo) B-)";s:7:"sad!png";s:4:"!sad";s:10:"shades!png";s:10:"8) 8o) 8-)";s:7:"shy!png";s:4:"!shy";s:9:"smile!png";s:10:":) :o) :-)";s:11:"special!png";s:3:"%-6";s:12:"suprised!png";s:10:":O :oO :-O";s:10:"tongue!png";s:21:":p :op :-p :P :oP :-P";s:8:"wink!png";s:10:";) ;o) ;-)";}';
			$sql->db_Insert("core", "'emote_default', '$tmp' ");
			catch_error();

			if(!$pref['emotepack']){
        			$pref['emotepack'] = "default";
			}
			mysql_query("ALTER TABLE ".MPREFIX."core CHANGE e107_name e107_name varchar(100) NOT NULL default ''");
			catch_error();
		/*	end 	*/

		/* start download updates */
		if (!mysql_table_exists("download_mirror")) {
			$query = "CREATE TABLE ".MPREFIX."download_mirror (
			mirror_id int(10) unsigned NOT NULL auto_increment,
			mirror_name varchar(200) NOT NULL default '',
			mirror_url varchar(200) NOT NULL default '',
			mirror_image varchar(200) NOT NULL default '',
			mirror_location varchar(100) NOT NULL default '',
			mirror_description text NOT NULL,
			mirror_count int(10) unsigned NOT NULL default '0',
			PRIMARY KEY  (mirror_id)
			) TYPE=MyISAM;";
			$sql->db_Select_gen($query);
			catch_error();
			mysql_query("ALTER TABLE ".MPREFIX."download ADD download_class TINYINT ( 3 ) UNSIGNED NOT NULL");
			catch_error();
			mysql_query("ALTER TABLE ".MPREFIX."download_category ADD download_category_order int(10) unsigned NOT NULL default '0'");
			catch_error();
			mysql_query("ALTER TABLE `".MPREFIX."download` ADD `download_mirror` TEXT NOT NULL , ADD `download_mirror_type` tinyint(1) unsigned NOT NULL default '0' ");
			catch_error();
		}
		/*	end	*/


		/* start user update */
			mysql_query("ALTER TABLE ".MPREFIX."user ADD user_loginname varchar(100) NOT NULL default '' AFTER user_name");
			catch_error();
			mysql_query("ALTER TABLE ".MPREFIX."user ADD user_xup varchar(100) NOT NULL default ''");
			catch_error();
			$sql->db_Update("user", "user_loginname=user_name WHERE user_loginname=''");
			catch_error();
		/* end */

		/* start page update */
		if (!mysql_table_exists("page")) {
			$sql->db_Select_gen("CREATE TABLE ".MPREFIX."page (
		  	page_id int(10) unsigned NOT NULL auto_increment,
		  	page_title varchar(250) NOT NULL default '',
		  	page_text mediumtext NOT NULL,
		  	page_author int(10) unsigned NOT NULL default '0',
		  	page_datestamp int(10) unsigned NOT NULL default '0',
		  	page_rating_flag tinyint(1) unsigned NOT NULL default '0',
		  	page_comment_flag tinyint(1) unsigned NOT NULL default '0',
		  	page_password varchar(50) NOT NULL default '',
		  	page_class varchar(250) NOT NULL default '',
		  	page_ip_restrict text NOT NULL,
		  	page_theme varchar(50) NOT NULL default '',
		  	PRIMARY KEY  (page_id)
			) TYPE=MyISAM;");
			catch_error();
			mysql_query("ALTER TABLE ".MPREFIX."page CHANGE page_class page_class varchar(250) NOT NULL default ''");
			catch_error();
		}
		/*	end 	*/


		// start links update -------------------------------------------------------------------------------------------
			if (mysql_table_exists("link_category")) {
				global $IMAGES_DIRECTORY, $PLUGINS_DIRECTORY;

				$sql->db_Select_gen("CREATE TABLE ".MPREFIX."links_page_cat (
				link_category_id int(10) unsigned NOT NULL auto_increment,
				link_category_name varchar(100) NOT NULL default '',
				link_category_description varchar(250) NOT NULL default '',
				link_category_icon varchar(100) NOT NULL default '',
				PRIMARY KEY  (link_category_id)
				) TYPE=MyISAM;");
				catch_error();
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
				catch_error();
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

				$s_prefs = TRUE;
			}
		// end links update -------------------------------------------------------------------------------------------

		//  #########  McFly's 0.7 Updates ############

		// parse table obsolete
		if(mysql_table_exists("parser")){
			mysql_query('DROP TABLE `'.MPREFIX.'parser`');
			catch_error();
        }
			mysql_query("ALTER TABLE ".MPREFIX."menus ADD menu_path varchar(100) NOT NULL default ''");
			catch_error();

			$sql -> db_Update("menus", "menu_path='poll/' WHERE menu_name='poll_menu' ");
			catch_error();

			mysql_query("UPDATE ".MPREFIX."menus SET menu_path = 'custom', menu_name = substring(menu_name,8) WHERE substring(menu_name,1,6) = 'custom'");
			catch_error();

			mysql_query("UPDATE ".MPREFIX."menus SET menu_path = menu_name  WHERE menu_path = ''");
			catch_error();

		// New dblog table for logging db calls (admin log)
		if (!mysql_table_exists("dblog")) {
			$sql->db_Select_gen("CREATE TABLE ".MPREFIX."dblog (
			dblog_id int(10) unsigned NOT NULL auto_increment,
			dblog_type varchar(60) NOT NULL default '',
			dblog_datestamp int(10) unsigned NOT NULL default '0',
			dblog_user_id int(10) unsigned NOT NULL default '0',
			dblog_ip varchar(80) NOT NULL default '',
			dblog_query text NOT NULL,
			dblog_remarks varchar(255) NOT NULL default '',
			PRIMARY KEY  (dblog_id)
			) TYPE=MyISAM;");
			catch_error();
		}

		// New generic table for storing any miscellaneous data
		if (!mysql_table_exists("generic")) {
			$sql->db_Select_gen("CREATE TABLE ".MPREFIX."generic (
			gen_id int(10) unsigned NOT NULL auto_increment,
			gen_type varchar(80) NOT NULL default '',
			gen_datestamp int(10) unsigned NOT NULL default '0',
			gen_user_id int(10) unsigned NOT NULL default '0',
			gen_ip varchar(80) NOT NULL default '',
			gen_intdata int(10) unsigned NOT NULL default '0',
			gen_chardata text NOT NULL,
			PRIMARY KEY  (gen_id)
			) TYPE=MyISAM;");
			catch_error();
		}

		if (!mysql_table_exists("user_extended")) {
			$sql->db_Select_gen("CREATE TABLE ".MPREFIX."user_extended (
			user_extended_id int(10) unsigned NOT NULL default '0',
			PRIMARY KEY  (user_extended_id)
			) TYPE=MyISAM;");
			catch_error();

			$sql->db_Select_gen("CREATE TABLE ".MPREFIX."user_extended_struct (
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
			) TYPE=MyISAM;");
			catch_error();

			$sql->db_Select_gen("ALTER TABLE #user_extended_struct ADD user_extended_struct_applicable tinyint(3) unsigned NOT NULL default '0'");
			catch_error();

			$sql->db_Select_gen("ALTER TABLE #user_extended_struct ADD user_extended_struct_order int(10) unsigned NOT NULL default '0'");
			catch_error();

			$sql->db_Select_gen("ALTER TABLE #user_extended_struct ADD user_extended_struct_icon VARCHAR( 255 ) NOT NULL");
			catch_error();


			//Begin Extended user field conversion
			require_once(e_HANDLER."user_extended_class.php");
			$ue = new e107_user_extended;

			if($sql->db_Select("core", " e107_value", " e107_name='user_entended'", 'default'))
			{
				$row = $sql->db_Fetch();

				$user_extended = unserialize($row['e107_value']);
				$new_types = array('text' => 1, 'radio' => 2, 'dropdown' => 3, 'table' => 4);

				foreach($user_extended as $key => $val)
				{
					unset($new_field);
					$parms = explode("|", $val);
					$ext_name['ue_'.$key] = 'user_'.preg_replace("#\W#","",$parms[0]);
					$new_field['name'] = preg_replace("#\W#","",$parms[0]);
					$new_field['text'] = str_replace('_',' ',$parms[0]); // Spaces are ok now
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
				$s_prefs = TRUE;
				if($sql->db_Select('user','user_id, user_prefs',"1 ORDER BY user_id"))
				{
					$sql2 = new db;
					while($row = $sql->db_Fetch())
					{
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
										$new_values .= ", ";
									}
									$new_values .= "`".$ext_name[$key]."`='".$val."'";
								}
							}
						}
						foreach ($user_pref as $key => $prefvalue)
						{
							$user_pref[$key] = $tp->toDB($prefvalue);
						}
						$tmp=addslashes(serialize($user_pref));
						$sql2->db_Update("user", "user_prefs='$tmp' WHERE user_id='{$row['user_id']}'");
						if($new_values)
						{
//							echo $new_values."<br />";
							$sql2->db_Select_gen("INSERT INTO #user_extended (user_extended_id) values ('{$row['user_id']}')");
							$sql2->db_Update('user_extended', $new_values." WHERE user_extended_id = '{$row['user_id']}'");
						}
					}
				}
			}
			$sql->db_Select_gen("DELETE FROM #core WHERE e107_name='user_entended'");
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
					catch_error();
				}
			}

			mysql_query("ALTER TABLE ".MPREFIX."generic CHANGE gen_chardata gen_chardata TEXT NOT NULL");
			catch_error();

			mysql_query("ALTER TABLE ".MPREFIX."banner CHANGE banner_active banner_active TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'");
			catch_error();

		if ($sql->db_Field("cache",0) == "cache_url") {

			mysql_query('DROP TABLE `'.MPREFIX.'cache`'); // db cache is no longer an available option..
			catch_error();
		}
		$sql->db_Update("banner", "banner_active='255' WHERE banner_active = '0' ");
		$sql->db_Update("banner", "banner_active='0' WHERE banner_active = '1' ");
		$pref['wm_enclose'] = 1;
		$s_prefs = TRUE;
		/*
		Changes by McFly 2/12/2005
		Moving forum rules from wmessage table to generic table
		*/
			if($sql -> db_Select("wmessage"))
			{
				while($row = $sql->db_Fetch())
				{
					$wmList[] = $row;
				}
				foreach($wmList as $wm)
				{
					$fieldlist = "";
					$gen_type='wmessage';

					if($wm['wm_id'] == '1') { $wm_class = $wm['wm_active'] ? e_UC_GUEST : '255'; }
					if($wm['wm_id'] == '2') { $wm_class = $wm['wm_active'] ? e_UC_MEMBER : '255'; }
					if($wm['wm_id'] == '3') { $wm_class = $wm['wm_active'] ? e_UC_ADMIN : '255'; }
					if($wm['wm_id'] == '4') { $gen_type = 'forum_rules_guest'; $wm_class = $wm['wm_active'] ? e_UC_GUEST : '255'; }
					if($wm['wm_id'] == '5') { $gen_type = 'forum_rules_member'; $wm_class = $wm['wm_active'] ? e_UC_MEMBER : '255'; }
					if($wm['wm_id'] == '6') { $gen_type = 'forum_rules_admin'; $wm_class = $wm['wm_active'] ? e_UC_ADMIN : '255'; }

					if($gen_type != "wmessage")
					{
						$exists = $sql->db_Count('generic','(*)',"WHERE gen_type = '{$gen_type}'");
						if(!$exists)
						{
							$fieldlist = "0,'$gen_type','".time()."','".USERID."','',{$wm_class},'{$wm['wm_text']}'";
						}
					}
					else
					{
						$exists = $sql->db_Count('generic','(*)',"WHERE gen_type = 'wmessage' AND gen_user_id = '".$wm['wm_id']."'");
						if(!$exists)
						{
							$fieldlist = "0,'wmessage','".time()."','".$wm['wm_id']."','',{$wm_class},'{$wm['wm_text']}'";
						}
					}
					if($fieldlist)
					{
						$sql->db_Insert('generic',$fieldlist);
					}
				}
				$sql -> db_Select_gen("DROP TABLE ".MPREFIX."wmessage");
				catch_error();
			}

		// ############# END McFly's Updates  ##############

		// start chatbox update -------------------------------------------------------------------------------------------
			if (!$sql->db_Select("plugin", "plugin_path", "plugin_path='chatbox_menu'")) {
				$sql->db_Insert("plugin", "0, 'Chatbox', '1.0', 'chatbox_menu', 1");
				catch_error();
			}
		// end chatbox update -------------------------------------------------------------------------------------------

		// Cam's new PRESET Table. -------------------------------------------------------------------------------------------
		if (!mysql_table_exists("preset")) {
			$sql->db_Select_gen("CREATE TABLE ".MPREFIX."preset (
			preset_id int(10) unsigned NOT NULL auto_increment,
			preset_name varchar(80) NOT NULL default '',
			preset_field varchar(80) NOT NULL default '',
			preset_value varchar(255) NOT NULL default '',
			PRIMARY KEY  (preset_id)
			) TYPE=MyISAM;");
			catch_error();
		}

		// News Updates -----------------

			$field1 = $sql->db_Field("news",13);
			$field2 = $sql->db_Field("news",14);
			$field3 = $sql->db_Field("news",15);

			if($field1 != "news_summary" && $field1 != "news_thumbnail" && $field3 != "news_sticky"){
				mysql_query("ALTER TABLE `".MPREFIX."news` ADD `news_summary` text NOT NULL");
				catch_error();
				mysql_query("ALTER TABLE `".MPREFIX."news` ADD `news_thumbnail` text NOT NULL");
				catch_error();
				mysql_query("ALTER TABLE ".MPREFIX."news ADD news_sticky tinyint(3) unsigned NOT NULL default '0'");
				catch_error();
			}

		// Downloads updates - Added March 1, 2005 by McFly

		if (!mysql_table_exists("download_requests")) {
			$sql->db_Select_gen("CREATE TABLE ".MPREFIX."download_requests (
			download_request_id int(10) unsigned NOT NULL auto_increment,
			download_request_userid int(10) unsigned NOT NULL default '0',
			download_request_ip varchar(30) NOT NULL default '',
			download_request_download_id int(10) unsigned NOT NULL default '0',
			download_request_datestamp int(10) unsigned NOT NULL default '0',
			PRIMARY KEY  (download_request_id)
			) TYPE=MyISAM;");
			catch_error();
		}



         // fix for the the moving of the stats.php file in 0.7.
			if($sql -> db_Select("links", "*", "link_url = 'stats.php'")){
				$sql -> db_Update("links", "link_url='{"."e_PLUGIN"."}log/stats.php' WHERE link_url='stats.php' ");
				catch_error();
			}

		// Missing Forum upgrade stuff by Cam.

			global $PLUGINS_DIRECTORY;
			if($sql -> db_Select("links", "*", "link_url = 'forum.php'")){
				$sql -> db_Insert("plugin", "0, 'Forum', '1.1', 'forum', '1' ");
				catch_error();
				$sql -> db_Update("links", "link_url='{"."e_PLUGIN"."}forum/forum.php' WHERE link_url='forum.php' ");
				catch_error();
			}

			if($sql -> db_Select("menus", "*", "menu_name = 'newforumposts_menu' and menu_path='newforumposts_menu' ")){
				$sql -> db_Update("menus", "menu_path='forum' WHERE menu_name = 'newforumposts_menu' ");
				catch_error();
			}

		if($pref['cb_linkreplace'] && !$pref['link_replace']){
			$pref['link_text'] = "[link]";
			$pref['link_replace'] = 1;
			$pref['make_clickable'] = 1;
			$pref['cb_linkreplace'] = "";
			$s_prefs = TRUE;
		}

		// db verify fixes
			// Are these needed? To facilitate for users that upgraded to the cvs during development, or?
			mysql_query("ALTER TABLE `".MPREFIX."user_extended_struct` DROP `user_extended_struct_signup_show` , DROP `user_extended_struct_signup_required`;");
			catch_error();
			mysql_query("ALTER TABLE `".MPREFIX."user_extended_struct` ADD `user_extended_struct_signup` TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `user_extended_struct_required`;");
			catch_error();
			mysql_query("ALTER TABLE `".MPREFIX."user_extended_struct` DROP `user_extended_struct_icon`;");
			catch_error();
	        mysql_query("ALTER TABLE `".MPREFIX."user_extended_struct` ADD `user_extended_struct_parent` int(10) unsigned NOT NULL default '0'");
			catch_error();
        	mysql_query("ALTER TABLE `".MPREFIX."user_extended` ADD `user_hidden_fields` TEXT NOT NULL AFTER `user_extended_id`");
			catch_error();


			mysql_query("ALTER TABLE `".MPREFIX."download_category` CHANGE `download_category_class` `download_category_class` TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL");
			catch_error();


			mysql_query("ALTER TABLE `".MPREFIX."generic` CHANGE `gen_chardata` `gen_chardata` TEXT NOT NULL");
			catch_error();

			mysql_query("ALTER TABLE `".MPREFIX."news` CHANGE `news_class` `news_class` VARCHAR( 255 ) DEFAULT '0' NOT NULL");
			catch_error();
			// news_attach removal / field structure changes / 'thumb:' prefix removal
			mysql_query("ALTER TABLE `".MPREFIX."news` CHANGE `news_attach` `news_thumbnail` TEXT NOT NULL;");
			catch_error();
			mysql_query("ALTER TABLE `".MPREFIX."news` CHANGE `news_summary` `news_summary` TEXT NOT NULL;");
			catch_error();
			if ($sql -> db_Select("news", "news_id, news_thumbnail", "news_thumbnail LIKE '%thumb:%'")) {
				while ($row = $sql -> db_Fetch()) {
					$thumbnail = trim(str_replace('thumb:', '', $row['news_thumbnail']));
					$sql2 -> db_Update("news", "news_thumbnail='".$thumbnail."' WHERE news_id='".$row['news_id']."'");
					catch_error();
				}
			}

			if (!$sql->db_Select("plugin", "plugin_path", "plugin_path='log'") && !mysql_table_exists("logstats")) {
				$sql->db_Select_gen("CREATE TABLE ".MPREFIX."logstats (
				log_uniqueid int(11) NOT NULL auto_increment,
				log_id varchar(50) NOT NULL default '',
				log_data longtext NOT NULL,
				PRIMARY KEY  (log_uniqueid),
				UNIQUE KEY log_id (log_id)
				) TYPE=MyISAM;");
				catch_error();
			}

		if (isset($pref['log_activate'])) {
			if ($pref['log_activate']) {
				$pref['statActivate'] = 1;
				$pref['statCountAdmin'] = 0;
				$pref['statBrowser'] = 1;
				$pref['statOs'] = 1;
				$pref['statScreen'] = 1;
				$pref['statDomain'] = 1;
				$pref['statRefer'] = 1;
				$pref['statQuery'] = 1;
				$pref['statRecent'] = 1;
			} else {
				$pref['statActivate'] = 0;
			}
			unset($pref['log_activate']);
			$s_prefs = TRUE;
		}


			// start poll update -------------------------------------------------------------------------------------------
			if (!$sql->db_Select("plugin", "plugin_path", "plugin_path='poll'")) {
				$sql->db_Insert("plugin", "0, 'Poll', '2.0', 'poll', 1");
				$s_prefs = TRUE;
			}
			// end poll update -------------------------------------------------------------------------------------------

			// start newsfeed update -------------------------------------------------------------------------------------------
			if (!$sql->db_Select("plugin", "plugin_path", "plugin_path='newsfeed'")) {
				$sql->db_Insert("plugin", "0, 'Newsfeeds', '2.0', 'newsfeed', 1");
				$s_prefs = TRUE;
			}
			// end newsfeed update -------------------------------------------------------------------------------------------

			// start stats update -------------------------------------------------------------------------------------------
			if (!$sql->db_Select("plugin", "plugin_path", "plugin_path='log'")) {
				$sql->db_Insert("plugin", "0, 'Statistic Logging', '2.0', 'log', 1");
				$s_prefs = TRUE;
			}
			// end stats update -------------------------------------------------------------------------------------------

			// start content update -------------------------------------------------------------------------------------------
			if (!$sql->db_Select("plugin", "plugin_path", "plugin_path='content'")) {
				$sql->db_Insert("plugin", "0, 'Content Management', '1.0', 'content', 1");
				$s_prefs = TRUE;
			}
			// end content update -------------------------------------------------------------------------------------------

			// start list_new update -------------------------------------------------------------------------------------------
			if (!$sql->db_Select("plugin", "plugin_path", "plugin_path='list_new'")) {
				$sql->db_Insert("plugin", "0, 'List', '1.0', 'list_new', 1");
				$s_prefs = TRUE;
			}
			// end list_new update -------------------------------------------------------------------------------------------


		// Truncate logstats table if log_id = pageTotal not found
		/* log update - previous log entries are not compatible with later versions, sorry but we have to clear the table :\ */
		if (mysql_table_exists("logstats")) {
				if(!$sql->db_Select("logstats","log_id","log_id = 'pageTotal'")){
					mysql_query("TRUNCATE TABLE `".MPREFIX."logstats");
					catch_error();
				}
		}
		// -----------------------------------------------------

		// Fix corrupted Plugin Table.
		$sql -> db_Delete("plugin", " plugin_installflag='0' ");

		// Notify
		if (!$sql -> db_Select("core", "e107_name", "e107_name = 'notify_prefs'")) {
			$serial_prefs = "a:1:{s:5:\"event\";a:9:{s:7:\"usersup\";a:3:{s:4:\"type\";s:3:\"off\";s:5:\"class\";s:3:\"254\";s:5:\"email\";s:0:\"\";}s:8:\"userveri\";a:3:{s:4:\"type\";s:3:\"off\";s:5:\"class\";s:3:\"254\";s:5:\"email\";s:0:\"\";}s:5:\"flood\";a:3:{s:4:\"type\";s:3:\"off\";s:5:\"class\";s:3:\"254\";s:5:\"email\";s:0:\"\";}s:7:\"subnews\";a:3:{s:4:\"type\";s:3:\"off\";s:5:\"class\";s:3:\"254\";s:5:\"email\";s:0:\"\";}s:5:\"login\";a:3:{s:4:\"type\";s:3:\"off\";s:5:\"class\";s:3:\"254\";s:5:\"email\";s:0:\"\";}s:6:\"logout\";a:3:{s:4:\"type\";s:3:\"off\";s:5:\"class\";s:3:\"254\";s:5:\"email\";s:0:\"\";}s:8:\"newspost\";a:3:{s:4:\"type\";s:3:\"off\";s:5:\"class\";s:3:\"254\";s:5:\"email\";s:0:\"\";}s:7:\"newsupd\";a:3:{s:4:\"type\";s:3:\"off\";s:5:\"class\";s:3:\"254\";s:5:\"email\";s:0:\"\";}s:7:\"newsdel\";a:3:{s:4:\"type\";s:3:\"off\";s:5:\"class\";s:3:\"254\";s:5:\"email\";s:0:\"\";}}}";
			$notify_prefs = unserialize(stripslashes($serial_prefs));
			$handle = opendir(e_PLUGIN);
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) {
					$plugin_handle = opendir(e_PLUGIN.$file."/");
					while (false !== ($file2 = readdir($plugin_handle))) {
						if ($file2 == "e_notify.php") {
							if ($sql -> db_Select("plugin", "plugin_path", "plugin_path='".$file."' AND plugin_installflag='1'")) {
								$notify_prefs['plugins'][$file] = TRUE;
								require_once(e_PLUGIN.$file.'/e_notify.php');
								foreach ($config_events as $event_id => $event_text) {
									$notify_prefs['event'][$event_id] = array('type' => 'off', 'class' => '254', 'email' => '');
								}
							}
						}
					}
				}
			}
			$n_prefs = $tp -> toDB($notify_prefs);
			$n_prefs = $eArrayStorage -> WriteArray($n_prefs);
			$sql -> db_Insert("core", "'notify_prefs', '".$n_prefs."'");
			$pref['notify'] = FALSE;
			$s_prefs = TRUE;
		}

		// Admin Password Change Menu Display

		if (!isset($pref['adminpwordchange'])) {
			$pref['adminpwordchange'] = TRUE;
			$s_prefs = TRUE;
		}

		// Front Page Upgrade

		if (!is_array($pref['frontpage'])) {
			if (!$pref['frontpage']) {
				$up_pref = 'news.php';
			} else if ($pref['frontpage'] == 'links') {
				$up_pref = $PLUGINS_DIRECTORY.'links_page/links.php';
			} else if ($pref['frontpage'] == 'forum') {
				$up_pref = $PLUGINS_DIRECTORY.'forum/forum.php';
			} else if (is_numeric($pref['frontpage'])) {
				$up_pref = $PLUGINS_DIRECTORY.'content/content.php?content.'.$pref['frontpage'];
			} else if (substr($pref['frontpage'], -1) != '/' && strpos($pref['frontpage'], '.') === FALSE) {
				$up_pref = $pref['frontpage'].'.php';
			} else {
				$up_pref = $pref['frontpage'];
			}
			unset($pref['frontpage']);
			$pref['frontpage']['all'] = $up_pref;
			$s_prefs = TRUE;
		}



		// convert notify prefs from serialised to eArrayStorage
		$notify_prefs = $sysprefs -> getArray('notify_prefs');
		if (is_array($notify_prefs)) {
			$s_prefs = $tp -> toDB($notify_prefs);
			$s_prefs = $eArrayStorage -> WriteArray($s_prefs);
			$sql -> db_Update("core", "e107_value='".$s_prefs."' WHERE e107_name='notify_prefs' ");
		}

		// New Downloads visibility field.
			if($sql->db_Field("download",18) != "download_visible"){
				mysql_query("ALTER TABLE `".MPREFIX."download` ADD `download_visible` varchar(255) NOT NULL default '0';");
				catch_error();
				mysql_query("UPDATE `".MPREFIX."download` SET download_visible = download_class");
				catch_error();
				mysql_query("ALTER TABLE `".MPREFIX."download` CHANGE `download_class` `download_class` varchar(255) NOT NULL default '0'");
				catch_error();
			}
			mysql_query("ALTER TABLE `".MPREFIX."download_category` CHANGE `download_category_class` `download_category_class` varchar(255) NOT NULL default '0'");
			catch_error();

		// Links Update for using Link_Parent. .
			if($sql->db_Field("links",7) != "link_parent"){
				mysql_query("ALTER TABLE `".MPREFIX."links` CHANGE `link_refer` `link_parent` INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL");
				catch_error();
				$sql -> db_Select("links", "link_id,link_name", "link_name NOT LIKE 'submenu.%' ORDER BY link_name");
				while($row = $sql-> db_Fetch()){
					$name = $row['link_name'];
					$parent[$name] = $row['link_id']; // Possible top level parents
				}
				$sql -> db_Select("links", "link_id,link_name", "link_name LIKE 'submenu.%' ORDER BY link_name");
				while($row = $sql-> db_Fetch()){
					$tmp = explode(".",$row['link_name']);
					if (count($tmp) == 3) {
						$name = $tmp[2]; // submenu.topname.midname
						$parent[$name] = $row['link_id']; // Possible mid-level parents
					}
				}
        if(!is_object($sql2)){
        	$sql2 = new db;
				}
				$sql -> db_Select("links", "link_id,link_name", "link_name LIKE 'submenu.%' ORDER BY link_name");
				while($row = $sql-> db_Fetch()){
					$tmp = explode(".",$row['link_name']);
					$nm = $tmp[1];
					$id = $row['link_id'];
					$sql2 -> db_Update("links", "link_parent='".$parent[$nm]."' WHERE link_id ='$id' ");
					catch_error();
				}
				$sql -> db_Select("links", "link_id,link_name", "link_name LIKE '%.child.%' ORDER BY link_name");
				while($row = $sql-> db_Fetch()){
					$tmp = explode(".",$row['link_name']);
					$nm = $tmp[2]; // submenu.topname.midname.child.finalname
					$id = $row['link_id'];
					$sql2 -> db_Update("links", "link_parent='".$parent[$nm]."' WHERE link_id ='$id' ");
					catch_error();
				}
      }

		//20050626 : update links_page_cat and links_page
			$field1 = $sql->db_Field("links_page_cat",4);
			$field2 = $sql->db_Field("links_page_cat",5);
			$field3 = $sql->db_Field("links_page_cat",6);

			if($field1 != "link_category_order" && $field2 != "link_category_class" && $field3 != "link_category_datestamp"){
				mysql_query("ALTER TABLE ".MPREFIX."links_page_cat ADD link_category_order VARCHAR ( 100 ) NOT NULL DEFAULT '0';");
				catch_error();
				mysql_query("ALTER TABLE ".MPREFIX."links_page_cat ADD link_category_class VARCHAR ( 100 ) NOT NULL DEFAULT '0';");
				catch_error();
				mysql_query("ALTER TABLE ".MPREFIX."links_page_cat ADD link_category_datestamp INT ( 10 ) UNSIGNED NOT NULL DEFAULT '0';");
				catch_error();
			}
			if($sql->db_Field("links_page",10) != "link_datestamp"){
				mysql_query("ALTER TABLE ".MPREFIX."links_page ADD link_datestamp INT ( 10 ) UNSIGNED NOT NULL DEFAULT '0';");
				catch_error();
			}

		// Search Update
			$search_prefs = $sysprefs -> getArray('search_prefs');
			if ((!$sql -> db_Select("core", "e107_name", "e107_name='search_prefs'")) || !isset($pref['search_highlight'])) {
				$serial_prefs = "a:11:{s:11:\"user_select\";s:1:\"1\";s:9:\"time_secs\";s:2:\"60\";s:13:\"time_restrict\";s:1:\"0\";s:8:\"selector\";i:2;s:9:\"relevance\";i:0;s:13:\"plug_handlers\";N;s:10:\"mysql_sort\";i:0;s:11:\"multisearch\";s:1:\"1\";s:6:\"google\";s:1:\"0\";s:13:\"core_handlers\";a:4:{s:4:\"news\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}s:8:\"comments\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}s:5:\"users\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}s:9:\"downloads\";a:5:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";}}s:17:\"comments_handlers\";a:2:{s:4:\"news\";a:3:{s:2:\"id\";i:0;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}s:8:\"download\";a:3:{s:2:\"id\";i:2;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}}}";
				$search_prefs = unserialize(stripslashes($serial_prefs));
				$handle = opendir(e_PLUGIN);
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) {
						if ($sql -> db_Select("plugin", "plugin_path", "plugin_path='".$file."' AND plugin_installflag='1'") || $file == 'content' || $file == 'forum' || $file == 'links_page' || $file == 'chatbox_menu') {
							$plugin_handle = opendir(e_PLUGIN.$file."/");
							while (false !== ($file2 = readdir($plugin_handle))) {
								if ($file2 == "e_search.php") {
									$search_prefs['plug_handlers'][$file] = array('class' => 0, 'pre_title' => 1, 'pre_title_alt' => '', 'chars' => 150, 'results' => 10);
								}
								if ($file2 == "search" && is_readable(e_PLUGIN.$file.'/search/search_comments.php')) {
									require_once(e_PLUGIN.$file.'/search/search_comments.php');
									$search_prefs['comments_handlers'][$file] = array('id' => $comments_type_id, 'class' => '0', 'dir' => $file);
									unset($comments_type_id);
								}
							}
						}
					}
				}
				preg_match("/^(.*?)($|-)/", mysql_get_server_info(), $mysql_version);
				if (version_compare($mysql_version[1], '4.0.1', '<')) {
					$search_prefs['mysql_sort'] = FALSE;
				} else {
					$search_prefs['mysql_sort'] = TRUE;
				}
				$serial_prefs = addslashes(serialize($search_prefs));
				if (!$sql -> db_Select("core", "e107_name", "e107_name='search_prefs'")) {
					$sql -> db_Insert("core", "'search_prefs', '".$serial_prefs."'");
				} else {
					$sql -> db_Update("core", "e107_value='".$serial_prefs."' WHERE e107_name='search_prefs' ");
				}
				if ($pref['search_restrict']) {
					$pref['search_restrict'] = 253;
					} else {
					$pref['search_restrict'] = 0;
				}
				$pref['search_highlight'] = TRUE;
				$s_prefs = TRUE;
			}

			// search sort method and search selector updates
			if (!isset($search_prefs['selector'])) {
				preg_match("/^(.*?)($|-)/", mysql_get_server_info(), $mysql_version);
				if (version_compare($mysql_version[1], '4.0.1', '<')) {
					$search_prefs['mysql_sort'] = FALSE;
				} else {
					$search_prefs['mysql_sort'] = TRUE;
				}
				$search_prefs['selector'] = 2;
				$search_prefs['multisearch'] = 1;
				unset($search_prefs['search_sort']);
			}

			// search content plugin comments id change
			if ($search_prefs['comments_handlers']['content']['id'] == '1') {
				$search_prefs['comments_handlers']['content']['id'] = 'pcontent';
			}

			// custom pages search added
			if (!isset($search_prefs['core_handlers']['pages'])) {
				$search_prefs['core_handlers']['pages'] = array('class' => 0, 'chars' => 150, 'results' => 10, 'pre_title' => 1, 'pre_title_alt' => '', 'order' => 13);
			}

        if(!is_array($pref['meta_tag'])){
        	$pref['meta_tag'] = array($pref['sitelanguage']=>$pref['meta_tag']);
		}

			$serial_prefs = addslashes(serialize($search_prefs));
			$sql -> db_Update("core", "e107_value='".$serial_prefs."' WHERE e107_name='search_prefs'");

		// end search updates

			$result = mysql_query('SET SQL_QUOTE_SHOW_CREATE = 1');
			catch_error();

			$qry = "SHOW CREATE TABLE `".MPREFIX."links`";
			$res = mysql_query($qry);
			catch_error();

			if ($res) {
				$row = mysql_fetch_row($res);
				$lines = explode("\n", $row[1]);
				if(strpos($lines[10],"tinyint")){
					mysql_query("ALTER TABLE `".MPREFIX."links` CHANGE `link_class` `link_class` VARCHAR( 255 ) DEFAULT '0' NOT NULL ");
					catch_error();
					mysql_query("ALTER TABLE `".MPREFIX."menus` CHANGE `menu_class` `menu_class` VARCHAR( 255 ) DEFAULT '0' NOT NULL ");
					catch_error();
				}
			}


			if (!function_exists("update_70x_to_706")) {
				if($sql->db_Field("plugin",5) != "plugin_rss"){
					mysql_query("ALTER TABLE `".MPREFIX."plugin` ADD `plugin_rss` varchar(255) NOT NULL default ''");
					catch_error();
				}
			}

		//20050630: added comment_lock to comments
			if($sql->db_Field("comments",11) != "comment_lock"){
				mysql_query("ALTER TABLE `".MPREFIX."comments` ADD `comment_lock` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0';");
				catch_error();
			}

			if($sql->db_Field("links_page",11) != "link_author"){
				mysql_query("ALTER TABLE `".MPREFIX."links_page` ADD `link_author` VARCHAR( 255 ) NOT NULL DEFAULT '';");
				catch_error();
			}

			if($sql->db_Field("user", 8) == "user_icq")
			{
				require_once(e_HANDLER."user_extended_class.php");
				$ue = new e107_user_extended;
				$ue->convert_old_fields();
			}

			if (mysql_table_exists("links_page_cat") && $sql -> db_Query("SHOW COLUMNS FROM ".MPREFIX."links_page_cat")) {
				while ($row = $sql -> db_Fetch()) {
					if ($row['Field'] == 'link_category_order' && strpos($row['Type'], 'int') === FALSE) {
						mysql_query("ALTER TABLE `".MPREFIX."links_page_cat` CHANGE `link_category_order` `link_category_order` INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL;");
						catch_error();
					}
				}
			}

		if (!isset($pref['track_online'])) {
			$pref['track_online'] = 1;
			$s_prefs = TRUE;
		}

		// custom menus / pages update

			unset($type);
			global $tp, $ns, $sql;
			require_once(e_HANDLER."file_class.php");
			$file = new e_file;
			$reject = array('$.','$..','/','CVS','thumbs.db','*._$', 'index', 'null*', 'Readme.txt');
			$cpages = $file -> get_files(e_PLUGIN."custompages", "", $reject);
			$cmenus = $file -> get_files(e_PLUGIN."custom", "", $reject);

			$customs = array_merge($cpages, $cmenus);

			$count = 0;
			foreach($customs as $p)
			{
				$type = (strstr($p['path'], "custompages") ? "" : str_replace(".php", "", $p['fname']));
				$filename = $p['path'].$p['fname'];
				$handle = fopen ($filename, "r");
				$contents = fread ($handle, filesize ($filename));
				fclose ($handle);
				$contents = str_replace("'", "&#039;", $contents);
				if(!preg_match('#\$caption = "(.*?)";#si', $contents, $match))
				{
					preg_match('#<CAPTION(.*?)CAPTION#si', $contents, $match);
				}
				$page_title = $tp -> toDB(trim($match[1]));

				if(!preg_match('#\$text = "(.*?)";#si', $contents, $match))
				{
					preg_match('#TEXT(.*?)TEXT#si', $contents, $match);
				}

				$page_text = $tp -> toDB(trim($match[1]));
				$filetime = filemtime($filename);

				if(!$sql -> db_Select("page", "*", "page_title='$page_title' "))
				{
					$sql -> db_Insert("page", "0, '$page_title', '$page_text', '".USERID."', '".$filetime."', '0', '0', '', '', '', '$type' ");
					$text .= "<b>Inserting: </b> '".$page_title."' <br />";
					$count ++;
				}

				$iid = mysql_insert_id();

				if($type)
				{
					if(!$sql -> db_Select("menus", "*", "menu_path='$iid' "))
					{
						mysql_query("UPDATE ".MPREFIX."menus SET menu_pages = '', menu_path='".$iid."' WHERE menu_name = '".$type."'");
					}
				}
				if (strstr($p['path'], "custompages")) {
					if ($sql -> db_Select("links", "*", "link_url LIKE '%custompages/".$p['fname']."%'")) {
						$sql -> db_Update("links", "link_url='page.php?".$iid."' WHERE link_url LIKE '%custompages/".$p['fname']."%'");
					}
				}
			}
			catch_error();

			if($sql -> db_Select("menus", "*", "menu_pages='dbcustom'")) {
				mysql_query("UPDATE ".MPREFIX."menus SET menu_pages = '' WHERE menu_pages='dbcustom'");
			}

			mysql_query("ALTER TABLE `".MPREFIX."news` CHANGE `news_thumbnail` `news_thumbnail` TEXT NOT NULL;");

			// Add forum indexes, remove any extras
			if (mysql_table_exists('forum_t') && $sql -> db_Query("SHOW INDEX FROM ".MPREFIX."forum_t"))
			{
				$a = array("PRIMARY", "thread_id", "thread_parent", "thread_datestamp", "thread_forum_id");
				while ($row = $sql -> db_Fetch())
				{
					if(!in_array($row['Key_name'], $a))
					{
						mysql_query("ALTER TABLE `".MPREFIX."forum_t` DROP INDEX `".$row['Key_name']."`");
						catch_error();
					}
					$index_list[] = $row['Key_name'];
				}
				$a = array("thread_parent", "thread_datestamp", "thread_forum_id");
				foreach($a as $f)
				{
					if(!in_array($f, $index_list))
					{
						mysql_query("ALTER TABLE `".MPREFIX."forum_t` ADD INDEX ( `{$f}` );");
						catch_error();
					}
				}
			}

			if (!isset($pref['download_email'])) {
				$pref['download_email'] = $pref['reported_post_email'];
				$s_prefs = TRUE;
			}

		if (!isset($pref['mailer'])) {
			$pref['mailer'] = $pref['smtp_enable'] ? 'smtp' : 'php';
			$s_prefs = TRUE;
		}

		//calendar_menu
			if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'calendar_menu' AND plugin_installflag='1' ")) {
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_class int(10) unsigned NOT NULL default '0';");
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_subs tinyint(3) unsigned NOT NULL default '0';");
				// mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_force tinyint(3) unsigned NOT NULL default '0';");
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_ahead tinyint(3) unsigned NOT NULL default '0';");
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_msg1 text;");
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_msg2 text;");
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_notify  tinyint(3) unsigned NOT NULL default '0';");
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_last int(10) unsigned NOT NULL default '0';");
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_today int(10) unsigned NOT NULL default '0';");
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_lastupdate int(10) unsigned NOT NULL default '0';");
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_addclass int(10) unsigned NOT NULL default '0';");
// 2 lines added for V3.6 event calendar
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_description text");
				mysql_query("ALTER TABLE ".MPREFIX."event_cat ADD event_cat_force_class int(10) unsigned NOT NULL default '0';");

				mysql_query("CREATE TABLE ".MPREFIX."event_subs (
					event_subid int(10) unsigned NOT NULL auto_increment,
					event_userid int(10) unsigned NOT NULL default '0',
					event_cat int(10) unsigned NOT NULL default '0',
					PRIMARY KEY (event_subid)
					) TYPE=MyISAM;");

				$row = $sql->db_Fetch();
				if($row['plugin_version'] != '3.5'){
					$sql -> db_Update("plugin", "plugin_version='3.5' WHERE plugin_path = 'calendar_menu' ");
				}
			}
		// end calendar_menu

		//update plugin_version : links_page
		if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'links_page' AND plugin_installflag='1' ")) {
			$row = $sql->db_Fetch();
			if($row['plugin_version'] != '1.12'){
				$sql -> db_Update("plugin", "plugin_version='1.12' WHERE plugin_path = 'links_page' ");
			}
		}

		// install new private message plugin if old plugin is installed
		if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'pm_menu' AND plugin_installflag='1' "))
		{
			$sql->db_Insert("plugin", "0, 'Private Messenger', '3.0', 'pm', 1, ''");
			catch_error();
		}

		if(!array_key_exists('ue_upgrade', $pref)){
			$pref['ue_upgrade'] = 1;
			$s_prefs = TRUE;
		}

		// Add default pref for Max IP signups.
		if(!isset($pref['signup_maxip'])){
			$pref['signup_maxip'] = 3;
			$s_prefs = TRUE;
		}

		// -----------------------------------------------------

		// Save all prefs that were set in above update routines
		if ($s_prefs == TRUE) {
			save_prefs();
		}

		return '';

	} else {

		// Check if update is needed to 0.7. -----------------------------------------------
		global $pref;
		if (!mysql_table_exists("user_extended")) {
			return update_needed();
		}

		if ($pref['sitelanguage'] == 'English-iso') {
			return update_needed();
		}

		if (!isset($pref['download_email'])) {
			return update_needed();
		}

		if($sql -> db_Select("menus", "*", "menu_pages='dbcustom'")) {
			return update_needed();
		}

		if (mysql_table_exists('forum_t') && $sql->db_Query("SHOW INDEX FROM ".MPREFIX."forum_t"))
		{
			$a = array("PRIMARY", "thread_parent", "thread_datestamp", "thread_forum_id");
			while ($row = $sql->db_Fetch())
			{
				if(!in_array($row['Key_name'], $a))
				{
					return update_needed();
				}
				$index_list[] = $row['Key_name'];
			}
			if(!in_array("thread_parent", $index_list) || !in_array("thread_datestamp", $index_list) || !in_array("thread_forum_id", $index_list))
			{
				return update_needed();
			}
		}

		if (mysql_table_exists('news') && $sql -> db_Query("SHOW COLUMNS FROM ".MPREFIX."news")) {
			while ($row = $sql -> db_Fetch()) {
				if ($row['Field'] == 'news_thumbnail' && strpos($row['Null'], 'YES') !== FALSE) {
					return update_needed();
				}
			}
		}

		if (mysql_table_exists('links_page_cat') && $sql -> db_Query("SHOW COLUMNS FROM ".MPREFIX."links_page_cat")) {
			while ($row = $sql -> db_Fetch()) {
				if ($row['Field'] == 'link_category_order' && strpos($row['Type'], 'int') === FALSE) {
					return update_needed();
				}
			}
		}

      $result = mysql_query('SET SQL_QUOTE_SHOW_CREATE = 1');
		$qry = "SHOW CREATE TABLE `".MPREFIX."links`";
		$res = mysql_query($qry);
		if ($res) {
			$row = mysql_fetch_row($res);
			$lines = explode("\n", $row[1]);
			if(strpos($lines[10],"tinyint")){
		  		return update_needed();
			}
		}

		if($pref['meta_tag']!="" && !is_array($pref['meta_tag'])){
			return update_needed();
		}

		if(!array_key_exists('ue_upgrade', $pref)){
			return update_needed();
		}

		if (!function_exists("update_70x_to_706")) {
			if($sql->db_Field("plugin",5) != "plugin_rss"){
				return update_needed();
			}
		}

		if($sql->db_Field("links",7) != "link_parent"){
			return update_needed();
		}

		if($sql->db_Field("user", 8) == "user_icq")
		{
			return update_needed();
		}

		if($sql->db_Field("user",36) != "user_xup" && $sql->db_Field("user", 30) != "user_xup"){
		 	return update_needed();
		}

		if($sql->db_Field("download",18) != "download_visible"){
		 	return update_needed();
		}

		if (!$sql -> db_Select("core", "e107_name", "e107_name = 'notify_prefs'")) {
		 	return update_needed();
		}

		// search content plugin comments id change
		$search_prefs = $sysprefs -> getArray('search_prefs');
		if ($search_prefs['comments_handlers']['content']['id'] == '1') {
		 	return update_needed();
		}

		// custom pages search added
		if (is_array($search_prefs) && !isset($search_prefs['core_handlers']['pages'])) {
		 	return update_needed();
		}

		$result = mysql_query('SET SQL_QUOTE_SHOW_CREATE = 1');
		$qry = "SHOW CREATE TABLE `".MPREFIX."user`";
		$res = mysql_query($qry);
		if ($res) {
			$row = mysql_fetch_row($res);
			if(!strstr($row[1], "KEY `user_ban_index` (`user_ban`)")) {
			 	return update_needed();
			}
		}

		global $pref;
		if (!is_array($pref['frontpage'])) {
		 	return update_needed();
		}

		if ((!$sql -> db_Select("core", "e107_name", "e107_name='search_prefs'")) || !isset($pref['search_highlight'])) {
		 	return update_needed();
		}

		if($sql->db_Field("comments",11) != "comment_lock"){
		 	return update_needed();
		}

		if(mysql_table_exists("links_page") && $sql->db_Field("links_page",11) != "link_author"){
		  	return update_needed();
		}

		if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'calendar_menu' AND plugin_installflag='1' ")) {
			if($sql->db_Field("event_cat",3) != "event_cat_class"){
			 	return update_needed();
			}
		}

		// No updates needed
	 	return TRUE;
	}
}


function update_616_to_617($type='') {
	global $sql;

	if ($type == "do") {
		mysql_query("ALTER TABLE  ".MPREFIX."poll ADD poll_comment TINYINT( 3 ) UNSIGNED DEFAULT '1' NOT NULL ");
		mysql_query("ALTER TABLE  ".MPREFIX."menus ADD menu_pages TEXT NOT NULL ");
		$sql2 = new db;
		$sql2->db_Update("poll", "poll_comment='1' WHERE poll_id!='0'");
		} else {
			if($sql->db_Field("menus",5) == "menu_pages"){
				return TRUE;
			}

	 	return update_needed();
	}
}

function update_615_to_616($type='') {
	global $sql;
	if ($type == "do") {
		mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES (4, 'This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on.', '0')");
		mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES (5, 'Member rules ----- This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on - only logged in members will see this.', '0')");
		mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES (6, 'Administrator rules ----- This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on - only logged in administrators will see this.', '0')");
		mysql_query("ALTER TABLE ".MPREFIX."download ADD download_comment TINYINT( 3 ) UNSIGNED NOT NULL ");
		mysql_query("ALTER TABLE ".MPREFIX."chatbox CHANGE cb_nick cb_nick VARCHAR( 30 ) NOT NULL default ''");
		mysql_query("ALTER TABLE ".MPREFIX."comments CHANGE comment_type comment_type VARCHAR( 10 ) DEFAULT '0' NOT NULL ");
		mysql_query("ALTER TABLE ".MPREFIX."comments ADD comment_pid INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL AFTER comment_id ");
		mysql_query("ALTER TABLE ".MPREFIX."comments ADD comment_subject VARCHAR( 100 ) NOT NULL AFTER comment_item_id ");
		mysql_query("ALTER TABLE ".MPREFIX."user ADD user_customtitle VARCHAR( 100 ) NOT NULL AFTER user_name ");
		mysql_query("ALTER TABLE ".MPREFIX."parser ADD UNIQUE (parser_regexp)");
		mysql_query("ALTER TABLE ".MPREFIX."userclass_classes ADD userclass_editclass TINYINT( 3 ) UNSIGNED NOT NULL ");
		update_extended_616();
		} else {

		if($sql->db_Field("userclass_classes",3) == "userclass_editclass"){
			return TRUE;
		}

	 	return update_needed();
	}
}

function update_614_to_615($type='') {
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

		if($sql->db_Field("submitnews",9) == "submitnews_file"){
        	return TRUE;
		}

		 	return update_needed();
	}
}

function update_611_to_612($type='') {
	global $sql;
	if ($type == "do") {
		mysql_query("ALTER TABLE ".MPREFIX."news ADD news_render_type TINYINT UNSIGNED NOT NULL ");
		mysql_query("ALTER TABLE ".MPREFIX."content CHANGE content_parent content_parent INT UNSIGNED DEFAULT '0' NOT NULL ");
		} else {
		global $mySQLdefaultdb;

		if($sql->db_Field("news",11) == "news_render_type"){
        	return TRUE;
		}

		return FALSE;
	}
}

function update_603_to_604($type='') {
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
		if (mysql_table_exists("link_category")) {
			$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."link_category");		// This won't work with PHP5.3. But noone should be on version 0.603 now!!!!
			$columns = mysql_num_fields($fields);
			for ($i = 0; $i < $columns; $i++) {
				if ("link_category_icon" == mysql_field_name($fields, $i)) {
					return TRUE;
				}
			}
		 	return update_needed();
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

function update_needed()
{
	global $ns;
	if(E107_DEBUG_LEVEL)
	{
		$tmp = debug_backtrace();
		$ns->tablerender("", "<div style='text-align:center'>Update required in ".basename(__FILE__)." on line ".$tmp[0]['line']."</div>");
	}
	return FALSE;
}

function mysql_table_exists($table){
     $exists = mysql_query("SELECT 1 FROM ".MPREFIX."$table LIMIT 0");
     if ($exists) return TRUE;
     return FALSE;
}


function catch_error(){
	if (mysql_error()!='' && E107_DEBUG_LEVEL != 0) {
		$tmp2 = debug_backtrace();
		$tmp = mysql_error();
		echo $tmp." [ ".basename(__FILE__)." on line ".$tmp2[0]['line']."] <br />";
	}
	return;
}


?>
