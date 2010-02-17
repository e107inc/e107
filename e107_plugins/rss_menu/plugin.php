<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     (C)Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/rss_menu/plugin.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit(); }

include_lan(e_PLUGIN."rss_menu/languages/".e_LANGUAGE.".php");

// Plugin info ----------------------------------------------------------------
$eplug_name				= "RSS_PLUGIN_LAN_1";
$eplug_version			= "1.1";
$eplug_author			= "e107dev";
$eplug_logo				= "";
$eplug_url				= "http://e107.org";
$eplug_email			= "";
$eplug_description		= RSS_MENU_L2;
$eplug_compatible		= "e107v0.7+";
$eplug_readme			= "";		//leave blank if no readme file
$eplug_latest			= FALSE;	//Show reported threads in admin (use latest.php)
$eplug_status			= FALSE;	//Show post count in admin (use status.php)

// Name of the plugin's folder ------------------------------------------------
$eplug_folder			= "rss_menu";

// Name of menu item for plugin -----------------------------------------------
$eplug_menu_name		= "rss_menu";

// Name of the admin configuration file ---------------------------------------
$eplug_conffile			= "admin_prefs.php";

// Icon image and caption text ------------------------------------------------
$eplug_icon				= $eplug_folder."/images/rss_32.png";
$eplug_icon_small		= $eplug_folder."/images/rss_16.png";
$eplug_caption			= LAN_CONFIGURE;

// List of preferences --------------------------------------------------------
$eplug_prefs			= '';

// List of table names --------------------------------------------------------
$eplug_table_names		= array("rss");

// List of sql requests to create tables --------------------------------------
$eplug_tables = array(
	"CREATE TABLE ".MPREFIX."rss (
	rss_id int(10) unsigned NOT NULL auto_increment,
	rss_name varchar(255) NOT NULL default '',
	rss_url text NOT NULL,
	rss_topicid varchar(255) NOT NULL default '',
	rss_path varchar(255) NOT NULL default '',
	rss_text longtext NOT NULL,
	rss_datestamp int(10) unsigned NOT NULL default '0',
	rss_class tinyint(1) unsigned NOT NULL default '0',
	rss_limit tinyint(3) unsigned NOT NULL default '0',
	PRIMARY KEY (rss_id)
	) TYPE=MyISAM;",

	"INSERT INTO ".MPREFIX."rss VALUES
	(0, '".RSS_NEWS."', 'news', '', 'news', '".RSS_PLUGIN_LAN_7."', '".time()."', 0, 9),
	(0, '".RSS_DL."', 'download', '', 'download', '".RSS_PLUGIN_LAN_8."', '".time()."', 0, 9),
	(0, '".RSS_COM."', 'comments', '', 'comments', '".RSS_PLUGIN_LAN_9."', '".time()."', 0, 9)
	"
);

// Create a link in main menu (yes=TRUE, no=FALSE) ----------------------------
$eplug_link				= FALSE;
$eplug_link_name		= '';
$eplug_link_url			= '';


// upgrading ------------------------------------------------------------------
$upgrade_add_prefs		= "";
$upgrade_remove_prefs	= "";

$upgrade_alter_tables	= array(
	"CREATE TABLE ".MPREFIX."rss (
	rss_id int(10) unsigned NOT NULL auto_increment,
	rss_name varchar(255) NOT NULL default '',
	rss_url text NOT NULL,
	rss_topicid varchar(255) NOT NULL default '',
	rss_path varchar(255) NOT NULL default '',
	rss_text longtext NOT NULL,
	rss_datestamp int(10) unsigned NOT NULL default '0',
	rss_class tinyint(1) unsigned NOT NULL default '0',
	rss_limit tinyint(3) unsigned NOT NULL default '0',
	PRIMARY KEY (rss_id)
	) TYPE=MyISAM;",

	"INSERT INTO ".MPREFIX."rss VALUES
	(0, '".RSS_NEWS."', 'news', '', 'news', '".RSS_PLUGIN_LAN_7."', '".time()."', 0, 9),
	(0, '".RSS_DL."', 'download', '', 'download', '".RSS_PLUGIN_LAN_8."', '".time()."', 0, 9),
	(0, '".RSS_COM."', 'comments', '', 'comments', '".RSS_PLUGIN_LAN_9."', '".time()."', 0, 9)
	"
);


?>
