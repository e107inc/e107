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

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "PDF";
$eplug_version = "1.0";
$eplug_author = "Lisa";
$eplug_logo = "";
$eplug_url = "http://eindhovenseschool.net";
$eplug_email = "lisa@eindhovenseschool.net";
$eplug_description = "PDF creation support";
$eplug_compatible = "e107v7";
$eplug_readme = "";        // leave blank if no readme file

$eplug_sc = array('PDF');

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "pdf";

// Mane of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/pdf_32.png";
$eplug_icon_small = $eplug_folder."/images/pdf_16.png";
$eplug_caption =  'PDF';

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = "";

// List of bbcode -----------------------------------------------------------------------------------------------
//$eplug_bb = array('pcontent');

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = "";

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = "";

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$eplug_link_url = "";

// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = "This plugin is now ready to be used.";

// upgrading ... //
$upgrade_add_prefs = "";
$upgrade_remove_prefs = "";
$upgrade_alter_tables = "";
$eplug_upgrade_done = "";


?>