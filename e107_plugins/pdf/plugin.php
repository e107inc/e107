<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$lan_file = e_PLUGIN."pdf/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."pdf/languages/English.php");

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "PDF_PLUGIN_LAN_1";
$eplug_version = "1.0";
$eplug_author = "Eric Vanderfeesten (lisa)";
$eplug_logo = "";
$eplug_url = "http://eindhovenseschool.net";
$eplug_email = "lisa@eindhovenseschool.net";
$eplug_description = PDF_PLUGIN_LAN_2;
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";        // leave blank if no readme file


// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "pdf";

// Mane of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_pdf_config.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/pdf_32.png";
$eplug_icon_small = $eplug_folder."/images/pdf_16.png";
$eplug_caption = PDF_PLUGIN_LAN_3;

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = "";

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = "";

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = "";

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$eplug_link_url = "";

// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = PDF_PLUGIN_LAN_4;

// upgrading ... //
$upgrade_add_prefs = "";
$upgrade_remove_prefs = "";
$upgrade_alter_tables = "";
$eplug_upgrade_done = "";

?>