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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newsfeed/plugin.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-28 18:23:56 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
	
@include_once(e_PLUGIN."newsfeed/languages/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."newsfeed/languages/English.php");
	
// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = NFLAN_01;
$eplug_version = "2.0";
$eplug_author = "Steve Dunstan (jalist)";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = NFLAN_02;
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";
	
// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "newsfeed";
	
// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "newsfeed_menu";
	
// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_config.php";
	
$eplug_sc = array("");

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/newsfeed_32.png";
$eplug_icon_small = $eplug_folder."/images/newsfeed_16.png";
$eplug_caption = NFLAN_03;
	
// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array();
	
// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array("newsfeed");
	
// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
"CREATE TABLE ".MPREFIX."newsfeed (
  newsfeed_id int(10) unsigned NOT NULL auto_increment,
  newsfeed_name varchar(150) NOT NULL default '',
  newsfeed_url varchar(150) NOT NULL default '',
  newsfeed_data text NOT NULL,
  newsfeed_timestamp int(10) unsigned NOT NULL default '0',
  newsfeed_description text NOT NULL,
  newsfeed_image varchar(100) NOT NULL default '',
  newsfeed_active tinyint(1) unsigned NOT NULL default '0',
  newsfeed_updateint int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (newsfeed_id)
) TYPE=MyISAM;");
	
// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = TRUE;
$eplug_link_name = NFLAN_01;
$eplug_link_url = e_PLUGIN."newsfeed/newsfeed.php";
	
// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = NFLAN_04; // "To activate please go to your menus screen and select the pm_menu into one of your menu areas.";
	
?>