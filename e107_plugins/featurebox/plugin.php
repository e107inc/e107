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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/featurebox/plugin.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-04-01 11:03:58 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
	
@include_once(e_PLUGIN."featurebox/languages/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."featurebox/languages/English.php");
	
// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = FBLAN_01;
$eplug_version = "1.0";
$eplug_author = "Steve Dunstan (jalist)";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = FBLAN_02;
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";
	
// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "featurebox";
	
// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "";
	
// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_config.php";
	
$eplug_sc = array("");

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/featurebox_32.png";
$eplug_icon_small = $eplug_folder."/images/featurebox_16.png";
$eplug_caption = FBLAN_03;
	
// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array("fb_active" => 1);
	
// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array("featurebox");
	
// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
"CREATE TABLE ".MPREFIX."featurebox (
  fb_id int(10) unsigned NOT NULL auto_increment,
  fb_title varchar(200) NOT NULL default '',
  fb_text text NOT NULL,
  fb_mode tinyint(3) unsigned NOT NULL default '0',
  fb_class tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (fb_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;");
	
// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$eplug_link_url = "";
	
// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = FBLAN_04; // "To activate please go to your menus screen and select the pm_menu into one of your menu areas.";
	
?>