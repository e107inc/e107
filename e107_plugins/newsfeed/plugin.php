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

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN."newsfeed/languages/".e_LANGUAGE.".php");

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "NFLAN_01";
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
  newsfeed_data longtext NOT NULL,
  newsfeed_timestamp int(10) unsigned NOT NULL default '0',
  newsfeed_description text NOT NULL,
  newsfeed_image varchar(100) NOT NULL default '',
  newsfeed_active tinyint(1) unsigned NOT NULL default '0',
  newsfeed_updateint int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (newsfeed_id)
) ENGINE=MyISAM;");

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = TRUE;
$eplug_link_name = NFLAN_01;
$eplug_link_url = e_PLUGIN."newsfeed/newsfeed.php";
$eplug_link_perms = "Everyone"; // Guest, Member, Admin, Everyone

// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = NFLAN_04; // "To activate please go to your menus screen and select the pm_menu into one of your menu areas.";

?>