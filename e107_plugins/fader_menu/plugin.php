<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        code adapted from original by Lolo Irie (lolo@touchatou.com)
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
$lan_file = e_PLUGIN."fader_menu/languages/".e_LANGUAGE.".php";
require_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."fader_menu/languages/English.php");


// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "Fader";
$eplug_version = "1";
$eplug_author = "jalist";
$eplug_logo = "button.png";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = FADER_L1; //"Fading text inside a menu, uses code from DynamicDrive.com";
$eplug_compatible = "e107v6";
$eplug_readme = "";        // leave blank if no readme file

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "fader_menu";

// Mane of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "fader_menu";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "config.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/logo.png";
$eplug_caption =  FADER_L17; //"Configure Fader";

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
        "fader_caption" => "Fader",
                "fader_message_1" => FADER_L2,
                "fader_message_2" => FADER_L3,
                "fader_message_3" => FADER_L4,
                "fader_message_4" => "",
                "fader_message_5" => "",
                "fader_message_6" => "",
                "fader_message_7" => "",
                "fader_message_8" => "",
                "fader_message_9" => "",
                "fader_message_10" => ""
);

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = "";

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = "";


// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$ec_dir = e_PLUGIN."";
$eplug_link_url = "";


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = FADER_L5; // "To configure please click on the link in the plugins section of the admin front page, then go to the menus screen and activate the menu";


// upgrading ... //

$upgrade_add_prefs = "";

$upgrade_remove_prefs = "";

$upgrade_alter_tables = "";

$eplug_upgrade_done = "";





?>