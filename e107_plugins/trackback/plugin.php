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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/trackback/plugin.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-15 22:46:22 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

@include_once(e_PLUGIN."trackback/languages/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."trackback/languages/English.php");

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "Trackback";
$eplug_version = "1.0";
$eplug_author = "jalist";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = TRACKBACK_L2;
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";
// leave blank if no readme file
	
// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "trackback";
	
// Mane of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "";
	
// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_config.php";
	
// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/trackback_32.png";
$eplug_icon_small = $eplug_folder."/images/trackback_16.png";
$eplug_caption = TRACKBACK_L1;
	
// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
"trackbackEnabled" => 0, 
"trackbackString" => "<span class='smalltext'><b>".TRACKBACK_L11."</b></span>"
);
	
// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
"trackback");
	
// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
"CREATE TABLE ".MPREFIX."trackback (
  trackback_id int(10) unsigned NOT NULL auto_increment,
  trackback_pid int(10) unsigned NOT NULL default '0',
  trackback_title varchar(200) NOT NULL default '',
  trackback_excerpt varchar(250) NOT NULL default '',
  trackback_url varchar(150) NOT NULL default '',
  PRIMARY KEY  (trackback_id)
) TYPE=MyISAM;");
	
// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$ec_dir = "";
$eplug_link_url = "";
	
	
// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = TRACKBACK_L3;
	
?>