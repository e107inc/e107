<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/upgrade.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("config.php");

define("VERSION", "0.554");
define("BUILD", "beta");

define("MSERVER", $mySQLserver);
define("MUSER", $mySQLuser);
define("MPASS", $mySQLpassword);
define("MDB", $mySQLdefaultdb);
define("MPREFIX", $mySQLprefix);

$sql = new db;
$sql -> db_SetErrorReporting(TRUE);
$sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);

echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><e107 upgrade></title>
<link rel="stylesheet" href="themes/e107/style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
</head>
<body>
<div style="text-align:center">
<table style="width:100%" cellspacing="0" cellpadding="0">
<tr>
<td style="width:66%; background-color:#E2E2E2; text-align:left\">
<img src="themes/shared/logo.png" alt="Logo" />
</td>
<td style="background-color:#E2E2E2; text-align:right; vertical-align:bottom" class="smalltext">
©Steve Dunstan 2002. See gpl.txt for licence details.
</td>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<tr> 
<td colspan="2" style="background-color:#ccc; vertical-align: top;">
<div class="mediumtext">&nbsp;Upgrade</div>
</td>
</tr>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<td colspan="2" style="vertical-align: top; text-align:center"><br />
<table style="width:66%" class="fborder">
<tr>
<td class="installb">
<br /><img src="themes/e107/images/installlogo.png" alt="" /><br /><span class="smalltext">php/mySQL website system</span><br />
<br />
<span class="installe">e107 upgrade</span>
<br /><br />

<?php

if(IsSet($_POST['usubmit'])){
	if($sql -> db_Select("user", "*", "user_name='".$_POST['a_name']."' AND user_password='".md5($_POST['a_password'])."' AND user_perms='0'")){
		$row = $sql -> db_Fetch();
		extract($row);
		if($user_perms != 0){
			$error = "You do not have the correct permissions to execute this script.";
		}
	}else if($sql -> db_Select("admin", "*", "admin_name='".$_POST['a_name']."' AND admin_password='".md5($_POST['a_password'])."' AND admin_permissions='0'  ")){
		$row = $sql -> db_Fetch();
		extract($row);
		if($admin_permissions != 0){
			$error = "You do not have the correct permissions to execute this script.";
		}
	}else{
		$error = "Administrator not found in database - script halted.";
	}

	if($error){
		echo "<span class=\"installh\">".$error."</span><br /><br />";
		echo "</td></tr></table></body></html>";
		exit;
	}

	echo "<span class=\"installh\">Main site administrator found, click button to begin upgrade process.</span><br /><br />
	<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\"><br /><br />
	<input class=\"button\" type=\"submit\" name=\"csubmit\" value=\"Begin Upgrade\" />
	</form><br /><br />
	</td></tr></table></body></html>";
	exit;
	
}

if(IsSet($_POST['csubmit'])){
	$str = update_tables();
	echo $str."<br /><br />";
	if(eregi("ERROR", $str)){
		echo "<br /><span class=\"installe\">* Error *</span><br /><br />An error was encountered while running the upgrade process.";
	}else{
		echo "<span class=\"installe\">* Success! *</span><br /><br />Upgrade completed successfully, you are now running <b>version ".VERSION." ".BUILD."</b></span><br /><br /><b>Please note</b><br />To use the new public upload and avatar upload features you must CHMOD /files/public/ and /files/public/avatars/ to 777.<br /><br />	
		<a href=\"index.php\">Click here to go to your front page</a><br />";
	}
	echo "</td></tr></table></body></html>";
	exit;
}


echo "This script will upgrade your e107 core install to version ".VERSION.".<br /><br /><br />
<b>PLEASE NOTE</b><br />
While every measure has been taken to ensure none of your site content is altered during this process, it would still be wise to backup your database before continuing.<br />
Also, if you are upgrading from a version older than v5.4, please backup your config.php and set it's permissions to 777 (CHMOD 777) as it will be rewritten to the latest standard. After the upgrade has completed chmod it back to 644 (CHMOD 644).<br /><br /><br />

Please enter your main administrator username/password to continue<br /><br />

<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:70%\">
<tr>
<td style=\"width:30%\" class=\"mediumtext\">Main administrator name:</td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"a_name\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>
<tr>
<td style=\"width:30%\" class=\"mediumtext\">Main administrator Password:</td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"password\" name=\"a_password\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td colspan=\"2\" style=\"text-align:center\">
<br />
<input class=\"button\" type=\"submit\" name=\"usubmit\" value=\"Submit\" />
</td>
</tr>
</table>
</form>
</body>
</html>";


function update_tables(){
	global $pref;

	// v5.1 ...
	@mysql_query("ALTER TABLE ".MPREFIX."prefs  CHANGE pref_value pref_value TEXT NOT NULL");
	@mysql_query("ALTER TABLE ".MPREFIX."forum_t ADD thread_s TINYINT(1) UNSIGNED NOT NULL");
	@mysql_query("CREATE TABLE ".MPREFIX."flood ( flood_url text NOT NULL, flood_time int(10) unsigned NOT NULL default '0') TYPE=MyISAM;");
	@mysql_query("ALTER TABLE e107_admin CHANGE admin_permissions admin_permissions TEXT NOT NULL");
	
	// v5.2 ...
	@mysql_query("ALTER TABLE ".MPREFIX."e107_stat_info CHANGE info_name info_name TEXT NOT NULL");
	@mysql_query("CREATE TABLE ".MPREFIX."stat_last (stat_last_date int(11) unsigned NOT NULL default '0', stat_last_info text NOT NULL) TYPE=MyISAM;");

	// v5.3b1 ...
	@mysql_query("CREATE TABLE ".MPREFIX."userclass_classes (userclass_id int(10) unsigned NOT NULL auto_increment, userclass_name varchar(100) NOT NULL default '', userclass_description varchar(250) NOT NULL default '', PRIMARY KEY  (userclass_id)) TYPE=MyISAM;");

	// v5.3b2 ...
	@mysql_query("ALTER TABLE ".MPREFIX."banlist CHANGE banlist_ip banlist_ip VARCHAR( 150 ) NOT NULL");
	@mysql_query("ALTER TABLE ".MPREFIX."wmessage ADD wm_id TINYINT UNSIGNED NOT NULL FIRST");

	if(!mysql_query("SELECT * FROM ".MPREFIX."wmessage WHERE wm_id='2' ")){
		@mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES ('2', 'Member message ----- This text (if activated) will appear at the top of your front page all the time - only logged in members will see this.', '0')");
		@mysql_query("INSERT INTO ".MPREFIX."wmessage VALUES ('3', 'Administrator message ----- This text (if activated) will appear at the top of your front page all the time - only logged in administrators will see this.', '0')");
	}

	// v5.4b1 ...
	if(!defined("e_HTTP")){
		$e_HTTP = TRUE;
		$fpath = str_replace(strrchr($_SERVER['PHP_SELF'], "/"), "", $_SERVER['PHP_SELF'])."/";
 
		$data = chr(60)."?php\n".chr(47)."*\n+---------------------------------------------------------------+\n|	e107 website system\n|	/config.php\n|\n|	©Steve Dunstan 2001-2002\n|	http://e107.org\n|	jalist@e107.org\n|\n|	Released under the terms and conditions of the\n|	GNU General Public License (http://gnu.org).\n+---------------------------------------------------------------+\n\nThis file has been generated by the installation script.\n\n*".chr(47)."\n\n".chr(36)."mySQLserver = ".chr(34).MSERVER.chr(34).";\n".chr(36)."mySQLuser = ".chr(34).MUSER.chr(34).";\n".chr(36)."mySQLpassword = ".chr(34).MPASS.chr(34).";\n".chr(36)."mySQLdefaultdb = ".chr(34).MDB.chr(34).";\n".chr(36)."mySQLprefix = ".chr(34).MPREFIX.chr(34).";\n\n".chr(47).chr(47)."define(".chr(34)."MQ".chr(34).", TRUE);\ndefine(".chr(34)."e_HTTP".chr(34).", ".chr(34).$fpath.chr(34).");\n\n?".chr(62);

		$fp = @fopen("config.php","w");
		if(!@fwrite($fp, $data)){
			$text =  "<span class=\"installe\">* Error *</span><br /><br />Was unable to write config.php to server, the file probably doesn't have the correct permissions set. Try chmodding config.php to 666 or 777 and re-running script. Script halted.
			</td></tr></table></body></html>";
			exit;
		}else{
			fclose($fp);
		}
	}

	@mysql_query("ALTER TABLE `".MPREFIX."news` CHANGE `news_author` `news_author` INT UNSIGNED DEFAULT '0' NOT NULL");
	@mysql_query("ALTER TABLE `".MPREFIX."news` ADD `news_start` INT UNSIGNED NOT NULL ,ADD `news_end` INT UNSIGNED NOT NULL ,ADD `news_active` TINYINT( 1 ) UNSIGNED NOT NULL");
	@mysql_query("ALTER TABLE `".MPREFIX."user` ADD `user_login` VARCHAR( 100 ) NOT NULL ,ADD `user_class` TEXT NOT NULL , ADD `user_perms` TEXT NOT NULL ,ADD `user_realm` TEXT NOT NULL ,ADD `user_pwchange` TINYINT( 1 ) UNSIGNED NOT NULL");
	@mysql_query("ALTER TABLE `".MPREFIX."content` CHANGE `content_parent` `content_summary` TEXT NOT NULL");
	@mysql_query("CREATE TABLE `".MPREFIX."core` (`e107_name` VARCHAR( 20 ) NOT NULL ,`e107_value` TEXT NOT NULL ,PRIMARY KEY ( `e107_name` ));");
	@mysql_query("ALTER TABLE `".MPREFIX."forum` ADD `forum_class` VARCHAR( 100 ) NOT NULL");
	@mysql_query("CREATE TABLE ".MPREFIX."tmp (tmp_ip varchar(20) NOT NULL default '',tmp_time int(10) unsigned NOT NULL default '0',tmp_info text NOT NULL)");
	@mysql_query("ALTER TABLE `".MPREFIX."links` ADD `link_open` TINYINT( 1 ) UNSIGNED NOT NULL ");

	if($e_HTTP){
		$sql = new db; $sql2 = new db;
		$sql -> db_Select("admin");
		while($row = $sql -> db_Fetch()){
			@extract($row);
			$sql2 -> db_Update("user", "user_perms='$admin_permissions' WHERE user_name='$admin_name' ");
		}
		$str .= "Administrators updated ...<br />";

		if($sql -> db_Select("admin")){
			while($row = $sql -> db_Fetch()){
				@extract($row);
				$sql2 -> db_Select("user", "*", "user_name='$admin_name' ");
				$row2 = $sql2 -> db_Fetch();
				@extract($row2);
				$sql2 -> db_Update("news", "news_author='$user_id' WHERE news_author='$admin_id' ");
				$sql2 -> db_Update("content", "content_author='$user_id' WHERE content_author='$admin_id' ");
				$sql2 -> db_Update("poll", "poll_admin_id='$user_id' WHERE poll_admin_id='$admin_id' ");
			}
			$str .= "News authors updated ...<br />Content authors updated ...<br />Poll authors updated ...<br />";
		}

		if(!mysql_query("SELECT * FROM ".MPREFIX."core WHERE e107_name ='e107' ")){
			$e107['e107_author'] = "Steve Dunstan (jalist)";
			$e107['e107_url'] = "http://e107.org";
			$e107['e107_version'] = VERSION;
			$e107['e107_build'] = BUILD;
			$e107['e107_datestamp'] = time();
			$tmp = serialize($e107);
			mysql_query("INSERT INTO ".MPREFIX."core VALUES ('e107', '$tmp') ");
		}
	
		if(!mysql_query("SELECT * FROM ".MPREFIX."core WHERE e107_name ='pref' ")){
			$e_SELF = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
			$pref['sitename'][1] = "e107 powered website";
			$pref['siteurl'][1] = "http://yoursite.com";
			$pref['sitebutton'][1] = "button.png";
			$pref['sitetag'][1] = "Website System ".VERSION.BUILD;
			$pref['sitedescription'][1] = "";
			$pref['siteadmin'][1] = $mainsiteadmin;
			$pref['siteadminemail'][1] = $mainsiteadminemail;
			$pref['sitetheme'][1] = "e107";
			$pref['sitedisclaimer'][1] = "All trademarks are &copy; their respective owners, all other content is &copy; e107 powered website.<br />e107 is &copy; e107.org 2002/2003 and is released under the <a href=\"http://www.gnu.org/\">GNU GPL license</a>.";
			$pref['newsposts'][1] = "10";
			$pref['flood_protect'][1] = "";
			$pref['flood_timeout'][1] = "5";
			$pref['flood_time'][1] = "30";
			$pref['flood_hits'][1] = "100";
			$pref['anon_post'][1] = "1";
			$pref['user_reg'][1] = "1";
			$pref['use_coppa'][1] = "1";
			$pref['profanity_filter'][1] = "1";
			$pref['profanity_replace'][1] = "[censored]";
			$pref['chatbox_posts'][1] = "10";
			$pref['smiley_activate'][1] = "";
			$pref['log_activate'][1] = "";
			$pref['log_refertype'][1] = "1";
			$pref['longdate'][1] = "%A %d %B %Y - %H:%M:%S";
			$pref['shortdate'][1] = "%d %b : %H:%M";
			$pref['forumdate'][1] = "%a %b %d %Y, %I:%M%p";
			$pref['sitelanguage'][1] = "English";
			$pref['maintainance_flag'][1] = "0";
			$pref['time_offset'][1] = "0";
			$pref['cb_linkc'][1] = " -link- ";
			$pref['cb_wordwrap'][1] = "30";
			$pref['cb_linkreplace'][1] = "1";
			$pref['log_lvcount'][1] = "10";
			$pref['meta_tag'][1] = "";
			$pref['user_reg_veri'][1] = "1";
			$pref['email_notify'][1] = "0";
			$pref['forum_poll'][1] = "0";
			$pref['forum_popular'][1] = "10";
			$pref['forum_track'][1] = "0";
			$pref['forum_eprefix'][1] = "[forum]";
			$tmp = serialize($pref);
			$sql -> db_Insert("core", " 'pref', '$tmp' ");
		}

		$sql -> db_Select("userclass_classes", "*", "userclass_name='PRIVATEFORUM' ");
		if($row = $sql -> db_Fetch()){
			@extract($row);
			$sql -> db_Update("forum", "forum_class ='$userclass_id' WHERE forum_active='2' ");
		}

		if($sql -> db_Select("userclass_users")){
			while($row = $sql -> db_Fetch()){
				@extract($row);
				$sql2 -> db_Update("user", "user_class ='$userclass_class' WHERE user_id='$userclass_user' ");
			}
		}

		@mysql_query("DROP TABLE `".MPREFIX."admin`"); 
		@mysql_query("DROP TABLE `".MPREFIX."prefs`");
		@mysql_query("DROP TABLE `".MPREFIX."e107`");
		if(!@mysql_query("SELECT * FROM ".MPREFIX."links WHERE link_name ='Downloads' ")){
			@mysql_query("INSERT INTO ".MPREFIX."links VALUES (0, 'Downloads', 'download.php', '', '', 1, 0, 0, 0) ");
		}
	}

	// v5.4b3 ...
	@mysql_query("ALTER TABLE `".MPREFIX."content` CHANGE `content_parent` `content_summary` TEXT NOT NULL");
	@mysql_query("ALTER TABLE `".MPREFIX."menus` ADD `menu_class` TINYINT UNSIGNED NOT NULL");

	// v5.4b4 ...
	@mysql_query("ALTER TABLE `".MPREFIX."content` CHANGE `content_parent` `content_summary` TEXT NOT NULL");
	@mysql_query("ALTER TABLE `".MPREFIX."_menus` ADD `menu_class` TINYINT UNSIGNED NOT NULL");
	@mysql_query("CREATE TABLE ".MPREFIX."banner (banner_id int(10) unsigned NOT NULL auto_increment, banner_clientname varchar(100) NOT NULL default '',banner_clientlogin varchar(20) NOT NULL default '', banner_clientpassword varchar(50) NOT NULL default '', banner_image varchar(150) NOT NULL default '', banner_clickurl varchar(150) NOT NULL default '', banner_impurchased int(10) unsigned NOT NULL default '0', banner_startdate int(10) unsigned NOT NULL default '0', banner_enddate int(10) unsigned NOT NULL default '0', banner_active tinyint(1) unsigned NOT NULL default '0', banner_clicks int(10) unsigned NOT NULL default '0', banner_impressions int(10) unsigned NOT NULL default '0', banner_ip text NOT NULL, banner_campaign varchar(150) NOT NULL default '', PRIMARY KEY  (banner_id)) TYPE=MyISAM;");

	if(!@mysql_query("SELECT * FROM ".MPREFIX."banner WHERE banner_clientname ='e107' ")){
		@mysql_query("INSERT INTO ".MPREFIX."banner VALUES (1, 'e107', 'e107login', 'e107password', 'e107.jpg', 'http://e107.org', 0, 0, 0, 1, 0, 0, '', 'campaign_one')");
	}
	// v0.547/8/9

	$download_table = "CREATE TABLE ".MPREFIX."download (
	  download_id int(10) unsigned NOT NULL auto_increment,
	  download_name varchar(100) NOT NULL default '',
	  download_url varchar(150) NOT NULL default '',
	  download_author varchar(100) NOT NULL default '',
	  download_author_email varchar(200) NOT NULL default '',
	  download_author_website varchar(200) NOT NULL default '',
	  download_description text NOT NULL,
	  download_filesize varchar(20) NOT NULL default '',
	  download_requested int(10) unsigned NOT NULL default '0',
	  download_category int(10) unsigned NOT NULL default '0',
	  download_active tinyint(3) unsigned NOT NULL default '0',
	  download_datestamp int(10) unsigned NOT NULL default '0',
	  download_thumb varchar(150) NOT NULL default '',
	  download_image varchar(150) NOT NULL default '',
	  PRIMARY KEY  (download_id),
	  UNIQUE KEY download_name (download_name)
	) TYPE=MyISAM;";
	mysql_query($download_table);


	$download_category_table = "CREATE TABLE ".MPREFIX."download_category (
	  download_category_id int(10) unsigned NOT NULL auto_increment,
	  download_category_name varchar(100) NOT NULL default '',
	  download_category_description text NOT NULL,
	  download_category_icon varchar(100) NOT NULL default '',
	  download_category_parent int(10) unsigned NOT NULL default '0',
	  download_category_class varchar(100) NOT NULL default '',
	  PRIMARY KEY  (download_category_id)
	) TYPE=MyISAM;";
	mysql_query($download_category_table);


	$rate_table = "CREATE TABLE ".MPREFIX."rate (
	  rate_id int(10) unsigned NOT NULL auto_increment,
	  rate_table varchar(100) NOT NULL default '',
	  rate_itemid int(10) unsigned NOT NULL default '0',
	  rate_rating int(10) unsigned NOT NULL default '0',
	  rate_votes int(10) unsigned NOT NULL default '0',
	  rate_voters text NOT NULL,
	  PRIMARY KEY  (rate_id)
	) TYPE=MyISAM;";
	mysql_query($rate_table);

	$cache_table = "CREATE TABLE ".MPREFIX."cache (
	  cache_url varchar(200) NOT NULL default '',
	  cache_datestamp int(10) unsigned NOT NULL default '0',
	  cache_data longtext NOT NULL
	) TYPE=MyISAM;";
	mysql_query($cache_table);

	@mysql_query("ALTER TABLE ".MPREFIX."forum ADD forum_order INT UNSIGNED NOT NULL");

	$e107['e107_author'] = "Steve Dunstan (jalist)";
	$e107['e107_url'] = "http://e107.org";
	$e107['e107_version'] = VERSION;
	$e107['e107_build'] = BUILD;
	$e107['e107_datestamp'] = time();
	$tmp = serialize($e107);
	$sql = new db;
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='e107' ");

	if(!@mysql_query("SELECT * FROM ".MPREFIX."core WHERE e107_name ='emote' ")){
		$emote = 'a:60:{i:0;a:1:{s:2:"&|";s:7:"cry.png";}i:1;a:1:{s:3:"&-|";s:7:"cry.png";}i:2;a:1:{s:3:"&o|";s:7:"cry.png";}i:3;a:1:{s:3:":((";s:7:"cry.png";}i:4;a:1:{s:3:"~:(";s:7:"mad.png";}i:5;a:1:{s:4:"~:o(";s:7:"mad.png";}i:6;a:1:{s:4:"~:-(";s:7:"mad.png";}i:7;a:1:{s:2:":)";s:9:"smile.png";}i:8;a:1:{s:3:":o)";s:9:"smile.png";}i:9;a:1:{s:3:":-)";s:9:"smile.png";}i:10;a:1:{s:2:":(";s:9:"frown.png";}i:11;a:1:{s:3:":o(";s:9:"frown.png";}i:12;a:1:{s:3:":-(";s:9:"frown.png";}i:13;a:1:{s:2:":D";s:8:"grin.png";}i:14;a:1:{s:3:":oD";s:8:"grin.png";}i:15;a:1:{s:3:":-D";s:8:"grin.png";}i:16;a:1:{s:2:":?";s:12:"confused.png";}i:17;a:1:{s:3:":o?";s:12:"confused.png";}i:18;a:1:{s:3:":-?";s:12:"confused.png";}i:19;a:1:{s:3:"%-6";s:11:"special.png";}i:20;a:1:{s:2:"x)";s:8:"dead.png";}i:21;a:1:{s:3:"xo)";s:8:"dead.png";}i:22;a:1:{s:3:"x-)";s:8:"dead.png";}i:23;a:1:{s:2:"x(";s:8:"dead.png";}i:24;a:1:{s:3:"xo(";s:8:"dead.png";}i:25;a:1:{s:3:"x-(";s:8:"dead.png";}i:26;a:1:{s:2:":@";s:7:"gah.png";}i:27;a:1:{s:3:":o@";s:7:"gah.png";}i:28;a:1:{s:3:":-@";s:7:"gah.png";}i:29;a:1:{s:2:":!";s:8:"idea.png";}i:30;a:1:{s:3:":o!";s:8:"idea.png";}i:31;a:1:{s:3:":-!";s:8:"idea.png";}i:32;a:1:{s:2:":|";s:11:"neutral.png";}i:33;a:1:{s:3:":o|";s:11:"neutral.png";}i:34;a:1:{s:3:":-|";s:11:"neutral.png";}i:35;a:1:{s:2:"?!";s:12:"question.png";}i:36;a:1:{s:2:"B)";s:12:"rolleyes.png";}i:37;a:1:{s:3:"Bo)";s:12:"rolleyes.png";}i:38;a:1:{s:3:"B-)";s:12:"rolleyes.png";}i:39;a:1:{s:2:"8)";s:10:"shades.png";}i:40;a:1:{s:3:"8o)";s:10:"shades.png";}i:41;a:1:{s:3:"8-)";s:10:"shades.png";}i:42;a:1:{s:2:":O";s:12:"suprised.png";}i:43;a:1:{s:3:":oO";s:12:"suprised.png";}i:44;a:1:{s:3:":-O";s:12:"suprised.png";}i:45;a:1:{s:2:":p";s:10:"tongue.png";}i:46;a:1:{s:3:":op";s:10:"tongue.png";}i:47;a:1:{s:3:":-p";s:10:"tongue.png";}i:48;a:1:{s:2:":P";s:10:"tongue.png";}i:49;a:1:{s:3:":oP";s:10:"tongue.png";}i:50;a:1:{s:3:":-P";s:10:"tongue.png";}i:51;a:1:{s:2:";)";s:8:"wink.png";}i:52;a:1:{s:3:";o)";s:8:"wink.png";}i:53;a:1:{s:3:";-)";s:8:"wink.png";}i:54;a:1:{s:4:"!ill";s:7:"ill.png";}i:55;a:1:{s:7:"!amazed";s:10:"amazed.png";}i:56;a:1:{s:4:"!cry";s:7:"cry.png";}i:57;a:1:{s:6:"!dodge";s:9:"dodge.png";}i:58;a:1:{s:6:"!alien";s:9:"alien.png";}i:59;a:1:{s:6:"!heart";s:9:"heart.png";}}';
		$sql -> db_Insert("core", "'emote', '$emote' ");
	}

	if(!@mysql_query("SELECT * FROM ".MPREFIX."core WHERE e107_name ='menu_pref' ")){
		$menu_pref = 'a:18:{s:15:"comment_caption";s:15:"Latest Comments";s:15:"comment_display";s:2:"10";s:18:"comment_characters";s:2:"50";s:15:"comment_postfix";s:12:"[ more ... ]";s:13:"comment_title";i:0;s:15:"article_caption";s:8:"Articles";s:16:"articles_display";s:2:"10";s:17:"articles_mainlink";s:17:"List Articles ...";s:21:"newforumposts_caption";s:18:"Latest Forum Posts";s:21:"newforumposts_display";s:2:"10";s:19:"forum_no_characters";s:2:"20";s:13:"forum_postfix";s:10:"[more ...]";s:11:"update_menu";s:20:"Update menu Settings";s:17:"forum_show_topics";s:1:"1";s:24:"newforumposts_characters";s:2:"50";s:21:"newforumposts_postfix";s:10:"[more ...]";s:19:"newforumposts_title";i:0;s:13:"clock_caption";s:11:"Date / Time";}';
		$sql -> db_Insert("core", "'menu_pref', '$menu_pref' ");
	}


	// v0.554

	$binary_table = "CREATE TABLE ".MPREFIX."binary (
     binary_id int(10) unsigned NOT NULL auto_increment,
     binary_name varchar(200) NOT NULL default '',
     binary_filetype varchar(100) NOT NULL default '',
     binary_data longblob NOT NULL,
     PRIMARY KEY  (binary_id)
   ) TYPE=MyISAM;";
	@mysql_query($binary_table);


   $upload_table = "CREATE TABLE ".MPREFIX."upload (
     upload_id int(10) unsigned NOT NULL auto_increment,
     upload_poster varchar(100) NOT NULL default '',
     upload_email varchar(100) NOT NULL default '',
     upload_website varchar(100) NOT NULL default '',
     upload_datestamp int(10) unsigned NOT NULL default '0',
     upload_name varchar(100) NOT NULL default '',
     upload_version varchar(10) NOT NULL default '',
     upload_file varchar(100) NOT NULL default '',
     upload_ss varchar(100) NOT NULL default '',
     upload_description text NOT NULL,
     upload_demo varchar(100) NOT NULL default '',
     upload_filesize int(10) unsigned NOT NULL default '0',
     upload_active tinyint(3) unsigned NOT NULL default '0',
     PRIMARY KEY  (upload_id)
   ) TYPE=MyISAM;";
	@mysql_query($upload_table);



	$session_table = "CREATE TABLE ".MPREFIX."session (
     session_id varchar(32) NOT NULL default '',
     session_expire int(10) unsigned NOT NULL default '0',
     session_datestamp int(10) unsigned NOT NULL default '0',
     session_ip varchar(200) NOT NULL default '',
     session_data text NOT NULL
   ) TYPE=MyISAM;";
	@mysql_query($session_table);

	$sql -> db_Select("core", "*", "e107_name='pref' ");
	$row = $sql -> db_Fetch();
	$tmp = stripslashes($row['e107_value']);
	$pref=unserialize($tmp);
	if(!is_array($pref)){
		$pref=unserialize($row['e107_value']);
		if(!is_array($pref)){
			$str .= "ERROR! Unable to retrieve core settings from database. This is not a fatal error but you will have to update your settings manually by going to admin/upload.php.";
			return $str;
		}
	}

	$pref['user_tracking'][1] = "cookie";
	$pref['resize_method'][1] = "gd2";
	$pref['im_path'][1] = "/usr/local/bin/";
	$pref['im_quality'][1] = "80";
	$pref['im_width'][1] = "120";
	$pref['upload_enabled'][1] = "0";
	$pref['upload_allowedfiletype'][1] = ".zip\n.gz\n.jpg\n.png\n.gif\n.txt";
	$pref['upload_storagetype'][1] = "2";
	$pref['upload_maxfilesize'][1] = "";
	$pref['upload_class'][1] = "999";

	$tmp = addslashes(serialize($pref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='pref' ");

	$sql -> db_Insert("core", "'pref_backup', '$tmp' ");

	return $str;
	
}

class db{
	var $mySQLserver;
	var $mySQLuser;
	var $mySQLpassword;
	var $mySQLdefaultdb;
	var $mySQLaccess;
	var $mySQLresult;
	var $mySQLrows;
	var $mySQLerror;
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb){
		$this->mySQLserver = $mySQLserver;
		$this->mySQLuser = $mySQLuser;
		$this->mySQLpassword = $mySQLpassword;
		$this->mySQLdefaultdb = $mySQLdefaultdb;
		$temp = $this->mySQLerror;
		$this->mySQLerror = FALSE;
		$this->mySQL_access = @mysql_connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword);
		@mysql_select_db($this->mySQLdefaultdb);
		$this->dbError("dbConnect/SelectDB");
		return $this->mySQLerror = $temp;
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Select($table, $fields="*", $arg="", $mode="default"){
//		echo "SELECT ".$fields." FROM ".MPREFIX.$table." WHERE ".$arg."<br />";
		if($arg != "" && $mode=="default"){
			if($this->mySQLresult = @mysql_query("SELECT ".$fields." FROM ".MPREFIX.$table." WHERE ".$arg)){
				$this->dbError("dbQuery");
				return $this->db_Rows();
			}else{
				$this->dbError("dbQuery ($query)");
				return FALSE;
			}
		}else if($arg != "" && $mode != "default"){
			if($this->mySQLresult = @mysql_query("SELECT ".$fields." FROM ".MPREFIX.$table." ".$arg)){
				$this->dbError("dbQuery");
				return $this->db_Rows();
			}else{
				$this->dbError("dbQuery ($query)");
				return FALSE;
			}
		}else{
			if($this->mySQLresult = @mysql_query("SELECT ".$fields." FROM ".MPREFIX.$table)){
				$this->dbError("dbQuery");
				return $this->db_Rows();
			}else{
				$this->dbError("db_Query ($query)");
				return FALSE;
			}		
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Insert($table, $arg){
		if($result = $this->mySQLresult = @mysql_query("INSERT INTO ".MPREFIX.$table." VALUES (".$arg.")" )){
			return $result;
		}else{
			$this->dbError("db_Insert ($query)");
			return FALSE;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Update($table, $arg){
		if($result = $this->mySQLresult = @mysql_query("UPDATE ".MPREFIX.$table." SET ".$arg)){	
			return $result;
		}else{
			$this->dbError("db_Update ($query)");
			return FALSE;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Fetch(){
		if($row = @mysql_fetch_array($this->mySQLresult)){
			while (list($key,$val) = each($row)) {
				$row[$key] = stripslashes($val);
			}
			$this->dbError("db_Fetch");
			return $row;
		}else{
			$this->dbError("db_Fetch");
			return FALSE;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Close(){
		mysql_close();
		$this->dbError("dbClose");
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Delete($table, $arg){
		if($arg == ""){
			if($result = $this->mySQLresult = @mysql_query("DELETE FROM ".MPREFIX.$table)){
				return $result;
			}else{
				$this->dbError("db_Delete ($query)");
				return FALSE;
			}
		}else{
			if($result = $this->mySQLresult = @mysql_query("DELETE FROM ".MPREFIX.$table." WHERE ".$arg)){
				return $result;
			}else{
				$this->dbError("db_Delete ($query)");
				return FALSE;
			}
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Rows(){
		$rows = $this->mySQLrows = @mysql_num_rows($this->mySQLresult);
		return $rows;
		$this->dbError("db_Rows");
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function dbError($from){
		if($error_message = @mysql_error()){
			if($this->mySQLerror == TRUE){
				echo "<b>mySQL Error!</b> Function: $from. [".@mysql_errno()." - $error_message]<br />";
				return $error_message;
			}
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_SetErrorReporting($mode){
		$this->mySQLerror = $mode;
	}
}


?>