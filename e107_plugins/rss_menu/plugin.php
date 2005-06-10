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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/rss_menu/plugin.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-10 00:40:48 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "RSS";
$eplug_version = "1.0";
$eplug_author = "e107";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = "RSS Feeds";
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";
$eplug_status = TRUE;

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "rss_menu";

// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "rss_menu";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_prefs.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/rss_32.png";
$eplug_icon_small = $eplug_folder."/images/rss_16.png";
$eplug_caption = "Configure RSS Feeds";

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
	'rss_feeds' => '1'
);



// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = "";

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = "";

$eplug_rss = "";

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = '';
$eplug_link_url = '';


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = "RSS has successfully installed. ";


// upgrading ... //

$upgrade_add_prefs = "";

$upgrade_remove_prefs = "";

$upgrade_alter_tables = "";

$eplug_upgrade_done = "";

?>