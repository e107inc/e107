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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/plugin.php,v $
|     $Revision: 1.12 $
|     $Date: 2005-09-06 08:52:31 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
// Plugin info -------------------------------------------------------------------------------------------------------
$lan_file = e_PLUGIN."links_page/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."links_page/languages/English.php");

$eplug_name = LCLAN_PLUGIN_LAN_1;
$eplug_version = "1.12";
$eplug_author = "Eric Vanderfeesten (lisa)";
$eplug_url = "http://e107.org";
$eplug_email = "lisa@eindhovenseschool.net";
$eplug_description = LCLAN_PLUGIN_LAN_2;
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";
$eplug_latest = TRUE; //Show reported threads in admin (use e_latest.php)
$eplug_status = TRUE; //Show post count in admin (use e_status.php)
	
// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "links_page";
	
// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "";
	
// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_linkspage_config.php";
	
// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/linkspage_32.png";
$eplug_icon_small = $eplug_folder."/images/linkspage_16.png";
$eplug_caption = LCLAN_PLUGIN_LAN_3;
	
// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array();

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
"links_page_cat",
"links_page" );
	
// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
"CREATE TABLE ".MPREFIX."links_page_cat (
	link_category_id int(10) unsigned NOT NULL auto_increment,
	link_category_name varchar(100) NOT NULL default '',
	link_category_description varchar(250) NOT NULL default '',
	link_category_icon varchar(100) NOT NULL default '',
	link_category_order varchar(100) NOT NULL default '0',
	link_category_class varchar(100) NOT NULL default '0',
	link_category_datestamp int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (link_category_id)
	) TYPE=MyISAM;",

	"CREATE TABLE ".MPREFIX."links_page (
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
	link_datestamp int(10) unsigned NOT NULL default '0',
	link_author varchar(255) NOT NULL default '',
	PRIMARY KEY  (link_id)
	) TYPE=MyISAM;" );
	
// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = TRUE;
$eplug_link_name = LCLAN_PAGETITLE_1;
$eplug_link_url = e_PLUGIN."links_page/links.php";
	
	
// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = LCLAN_PLUGIN_LAN_5;
$upgrade_add_prefs = "";
$upgrade_remove_prefs = "";

// upgrading ... //
$upgrade_alter_tables = array(
"ALTER TABLE ".MPREFIX."links_page ADD link_datestamp int(10) unsigned NOT NULL default '0'", 
"ALTER TABLE ".MPREFIX."links_page ADD link_author varchar(255) NOT NULL default ''", 
"ALTER TABLE ".MPREFIX."links_page_cat ADD link_category_order varchar(100) NOT NULL default '0'", 
"ALTER TABLE ".MPREFIX."links_page_cat ADD link_category_class varchar(100) NOT NULL default '0'", 
"ALTER TABLE ".MPREFIX."links_page_cat ADD link_category_datestamp int(10) unsigned NOT NULL default '0'"
);

$eplug_upgrade_done = LCLAN_PLUGIN_LAN_6.': '.$eplug_version;

?>