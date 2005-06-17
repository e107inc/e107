<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        code adapted from original by Lolo Irie (lolo_irie@e107coders.org)
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

global $PLUGINS_DIRECTORY;
// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "List";
$eplug_version = "1.0";
$eplug_author = "Lisa";
$eplug_logo = "";
$eplug_url = "http://eindhovenseschool.net";
$eplug_email = "lisa@eindhovenseschool.net";
$eplug_description = "This plugin allows you to view a list of recent additions in all e107 categories. You can either view the list with data since your last visit, or view a general latest additions list. Besides the page a menu is also present. Every section is configurable in the admin area.";
$eplug_compatible = "e107v7";
$eplug_readme = "";        // leave blank if no readme file


// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "list_new";

// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = array("list_new_menu.php", "list_recent_menu.php");

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_list_config.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/icon/list_32.png";
$eplug_icon_small = $eplug_folder."/icon/list_16.png";
$eplug_caption =  "Configure Main Menu";

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array();

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = "";

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = "";


// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = TRUE;
$eplug_link_name = "list";
$eplug_link_url = e_PLUGIN."list_new/list.php";


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = "This plugin is now ready to be used.";


// upgrading ... //

$upgrade_add_prefs = "";
$upgrade_remove_prefs = "";
$upgrade_alter_tables = "";
$eplug_upgrade_done = "";


?>