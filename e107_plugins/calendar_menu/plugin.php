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
// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "Event Calendar";
$eplug_version = "3.2";
$eplug_author = "jalist / cameron";
$eplug_logo = "button.png";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = "This plugin is a fully featured event calendar with calendar menu.";
$eplug_compatible = "e107v6";
$eplug_readme = "";        // leave blank if no readme file

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "calendar_menu";

// Mane of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "calendar_menu";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "config.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/icon_ec.png";
$eplug_caption =  "Configure Event Calendar";

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
        "eventpost_admin" => 0
);

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
        "event",
        "event_cat"
);

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
        "CREATE TABLE ".MPREFIX."event (
  event_id int(11) unsigned NOT NULL auto_increment,
  event_start int(10) NOT NULL default '0',
  event_end int(10) NOT NULL default '0',
  event_allday tinyint(1) unsigned NOT NULL default '0',
  event_recurring tinyint(1) unsigned NOT NULL default '0',
  event_datestamp int(10) unsigned NOT NULL default '0',
  event_title varchar(200) NOT NULL default '',
  event_location text NOT NULL,
  event_details text NOT NULL,
  event_author varchar(100) NOT NULL default '',
  event_contact varchar(200) NOT NULL default '',
  event_category smallint(5) unsigned NOT NULL default '0',
  event_thread varchar(100) NOT NULL default '',
  event_rec_m tinyint(2) unsigned NOT NULL default '0',
  event_rec_y tinyint(2) unsigned NOT NULL default '0',
  PRIMARY KEY  (event_id)
) TYPE=MyISAM;",
        "CREATE TABLE ".MPREFIX."event_cat (
  event_cat_id smallint(5) unsigned NOT NULL auto_increment,
  event_cat_name varchar(100) NOT NULL default '',
  event_cat_icon varchar(100) NOT NULL default '',
  PRIMARY KEY  (event_cat_id)
) TYPE=MyISAM;");


// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = TRUE;
$eplug_link_name = "Calendar";
$ec_dir = e_PLUGIN."calendar_menu/";
$eplug_link_url = "".$ec_dir."calendar.php";


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = "To activate please go to your menus screen and select the calendar_menu into one of your menu areas.";


// upgrading ... //

$upgrade_add_prefs = "";

$upgrade_remove_prefs = "";

$upgrade_alter_tables = "";

$eplug_upgrade_done = "";





?>