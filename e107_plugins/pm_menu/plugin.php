<?php
/*
+---------------------------------------------------------------+
|	e107 website system
	http://e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

@include_once(e_PLUGIN."pm_menu/languages/admin/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."pm_menu/languages/admin/English.php");

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = PMLAN_PM;
$eplug_version = "2.06";
$eplug_author = "McFly";
$eplug_logo = "/images/icon_pm.png";
$eplug_url = "http://mcfly.gotdns.org";
$eplug_email = "mcfly@e107.org";
$eplug_description = "This plugin is a fully featured Private Messaging system.";
$eplug_compatible = "e107v6+";
$eplug_readme = "pm_readme.txt";	// leave blank if no readme file
$eplug_parse= array("/{{(SENDPM)=([0-9]+)}}/");

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "pm_menu";

// Mane of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "pm_menu";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "pm_conf.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/icon_pm.png";
$eplug_caption =  "Configure Private Messenger";

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
	"pm_title" => "PMLAN_PM",
	"pm_show_animated" => "1",
	"pm_user_dropdown" => "1",
	"pm_read_timeout" => "30",
	"pm_unread_timeout" => "60",
	"pm_delete_read" => "0",
	"pm_popup" => "0",
	"pm_popdelay" => "30",
	"pm_userclass" => e_UC_MEMBER,
	"pm_sendemail" => "0"
);

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
	"pm_messages", 
	"pm_blocks"
);

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
"CREATE TABLE ".MPREFIX."pm_messages (
  pm_id int(10) unsigned NOT NULL auto_increment,
  pm_from_user varchar(100) NOT NULL default '',
  pm_to_user varchar(100) NOT NULL default '',
  pm_sent_datestamp int(10) unsigned NOT NULL default '0',
  pm_rcv_datestamp int(10) unsigned NOT NULL default '0',
  pm_subject text NOT NULL,
  pm_message text NOT NULL,
  PRIMARY KEY  (pm_id)
) TYPE=MyISAM AUTO_INCREMENT=1",
"CREATE TABLE ".MPREFIX."pm_blocks (
  block_id int(10) unsigned NOT NULL auto_increment,
  block_from varchar(100) NOT NULL default '',
  block_to varchar(100) NOT NULL default '',
  block_datestamp int(10) unsigned NOT NULL default '0',
  block_count int(10) NOT NULL default '0',
  PRIMARY KEY  (block_id)
) TYPE=MyISAM AUTO_INCREMENT=1;");

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$eplug_link_url = "";

// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = "To activate please go to your menus screen and select the pm_menu into one of your menu areas.";

$eplug_upgrade_done = "PM has been successfully upgraded to version ".$eplug_version;

?>	