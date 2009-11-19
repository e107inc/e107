<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     (C)Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/plugin.php,v $
|     $Revision: 1.23 $ - with mods to hopefully trigger upgrade to new version
|     $Date: 2009-11-19 11:45:49 $
|     $Author: marj_nl_fr $
|
| 22.07.06 - Mods for V3.6 upgrade, including log directory
| 02.08.06 - Support for category icon display added
| 29.09.06 - prefs, db field added for next batch of mods
| 03.10.06 - forced subs fields changed
| 04.10.06 - db field order changed to avoid confusing update routines
| 29.10.06 - Language mods to reflect CVS update to V1.14
| 10.11.06 - Mods for next release to CVS
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

// Plugin info -------------------------------------------------------------------------------------------------------
include_lan(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php");

$eplug_name = 'EC_ADLAN_1';
$eplug_version = "3.6";
$eplug_author = "jalist / cameron / McFly / Barry / Lisa_ / steved";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = EC_LAN_107;
$eplug_compatible = "e107v7";
$eplug_readme = "readme.pdf";
// leave blank if no readme file
$eplug_compliant = TRUE;

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "calendar_menu";

// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "calendar_menu";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_config.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/calendar_32.png";
$eplug_icon_small = $eplug_folder."/images/calendar_16.png";
$eplug_caption = EC_LAN_81; // "Configure Event Calendar";

$ecalSQL = new db;
$ecalSQL->db_Select("plugin", "plugin_version", "plugin_path='calendar_menu' AND plugin_installflag > 0");
list($ecalVer) = $ecalSQL->db_Fetch();
$ecalVer = preg_replace("/[a-zA-z\s]/", '', $ecalVer);

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
"eventpost_admin" => 254,			// Admin
"eventpost_adminlog" => 0,
"eventpost_showeventcount" => 1,
"eventpost_forum" => 1,
"eventpost_recentshow" => 0,
"eventpost_super" => 254,			// Admin
"eventpost_menulink" => 0,
"eventpost_dateformat" => 1,
"eventpost_fivemins" => 0,
"eventpost_weekstart" => "sun",
"eventpost_lenday" => 1,
"eventpost_caltime" => 0,
"eventpost_datedisplay" => 1,
"eventpost_timedisplay" => 0,
"eventpost_timecustom" => "%H%M",
"eventpost_dateevent"  => 1,
"eventpost_datenext"   => 1,
"eventpost_eventdatecustom" => "%A %d %B %Y",
"eventpost_nextdatecustom"   => "%d %b",
"eventpost_mailsubject" => EC_ADLAN_A12,
"eventpost_mailfrom" => EC_ADLAN_A151,
"eventpost_mailaddress" => EC_ADLAN_A152,
"eventpost_asubs" => 1,
"eventpost_emaillog" => 1,
"eventpost_menuheading" => EC_LAN_140,
"eventpost_daysforward" => 30,
"eventpost_numevents" => 3,
"eventpost_checkrecur" => 1,
"eventpost_linkheader" => 0,
"eventpost_fe_set" => "",
"eventpost_showcaticon" => 0,
"eventpost_namelink" => 1 );

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array("event","event_cat","event_subs" );

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
	event_cat_class int(10) unsigned NOT NULL default '0',
	event_cat_subs tinyint(3) unsigned NOT NULL default '0',
	event_cat_ahead tinyint(3) unsigned NOT NULL default '0',
	event_cat_msg1 text,
	event_cat_msg2 text,
	event_cat_notify  tinyint(3) unsigned NOT NULL default '0',
	event_cat_last int(10) unsigned NOT NULL default '0',
	event_cat_today int(10) unsigned NOT NULL default '0',
	event_cat_lastupdate int(10) unsigned NOT NULL default '0',
	event_cat_addclass int(10) unsigned NOT NULL default '0',
	event_cat_description text,
	event_cat_force_class int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (event_cat_id)
	) TYPE=MyISAM;"
	,
	"CREATE TABLE ".MPREFIX."event_subs (
	event_subid int(10) unsigned NOT NULL auto_increment,
	event_userid  int(10) unsigned NOT NULL default '0',
	event_cat  int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (event_subid)
	) TYPE=MyISAM;");


// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$ec_dir = e_PLUGIN."calendar_menu/";
$eplug_link = TRUE;
$eplug_link_name = EC_LAN_83; // "Calendar";
$eplug_link_url = "".$ec_dir."calendar.php";
$eplug_link_perms = "Everyone"; // Everyone, Guest, Member, Admin 


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = EC_LAN_82; // "To activate please go to your menus screen and select the calendar_menu into one of your menu areas.";



// upgrading ... //
$upgrade_add_prefs = "";
$upgrade_remove_prefs = "";
$upgrade_alter_tables = array();
$version_notes = "";


if (!function_exists('create_ec_log_dir'))
{
function create_ec_log_dir()
{
global $eplug_folder;

$response = "";
$cal_log_dir = e_PLUGIN.$eplug_folder.'/log';
  if (!is_dir($cal_log_dir))
  {  // Need to create log directory
    if (!mkdir($cal_log_dir,0666))
	{
	  $response = EC_ADLAN_A158."<br />";
	}
  }
  if (!is_dir($cal_log_dir))
  {
    $response .= EC_ADLAN_A153;
	return $response;
  }
  
// Now check directory permissions
  if (!is_writable($cal_log_dir."/"))
  {
    if (!chmod($cal_log_dir,0666))
	{
	  $response = EC_ADLAN_A154."<br />";
	}
    if (!is_writable($cal_log_dir."/"))
    {
      $response .= EC_ADLAN_A155;
    }
  }
  return $response;
}
}


if ($ecalVer < 3.5)
{
// To version 3.5

$upgrade_alter_tables = array(
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_class int(10) unsigned NOT NULL default '0'",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_subs tinyint(3) unsigned NOT NULL default '0'",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_force tinyint(3) unsigned NOT NULL default '0'",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_ahead tinyint(3) unsigned NOT NULL default '0'",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_msg1 text",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_msg2 text",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_notify  tinyint(3) unsigned NOT NULL default '0'",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_last int(10) unsigned NOT NULL default '0'",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_today int(10) unsigned NOT NULL default '0'",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_lastupdate int(10) unsigned NOT NULL default '0'",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_addclass int(10) unsigned NOT NULL default '0'",
"CREATE TABLE ".MPREFIX."event_subs (
	event_subid int(10) unsigned NOT NULL auto_increment,
	event_userid  int(10) unsigned NOT NULL default '0',
	event_cat  int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (event_subid)
	) TYPE=MyISAM;"
);
$version_notes .= "<u>3.5</u><br />".EC_ADLAN_A156."<br />";
}
// To version 3.6 - fair number of tweaks overall
if ($ecalVer < 3.6)
{
$upgrade_alter_tables = array(
"ALTER TABLE ".MPREFIX."event_cat DROP event_cat_force",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_description text",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_force_class int(10) unsigned NOT NULL default '0'"
);
  $verprefs = array(
	"eventpost_adminlog" => 0,
	"eventpost_showeventcount" => 1,
	"eventpost_menuheading" => EC_LAN_140,
	"eventpost_daysforward" => 30,
	"eventpost_numevents" => 3,
	"eventpost_checkrecur" => 1,
	"eventpost_linkheader" => 0,
	"eventpost_showcaticon" => 0,
	"eventpost_dateformat" => 1,
	"eventpost_fivemins" => 0,
	"eventpost_emaillog" => 1,
	"eventpost_caltime" => 0,
	"eventpost_datedisplay" => 1,
	"eventpost_timedisplay" => 0,
	"eventpost_timecustom" => "%H%M",
	"eventpost_fe_set" => "",
	"eventpost_namelink" => 1,
	"eventpost_recentshow" => 0,
	"eventpost_dateevent"  => 1,
	"eventpost_datenext"   => 1,
	"eventpost_eventdatecustom" => "&A %d %B %Y",
	"eventpost_nextdatecustom"   => "%d %b",
    "eventpost_menulink" => 0 );
	$upgrade_add_prefs .= $verprefs;
	$version_notes .= "<u>3.6</u><br />".EC_ADLAN_A156."<br />".create_ec_log_dir()."<br />
	                   <a href='".e_PLUGIN_ABS.$eplug_folder."/".$eplug_conffile."'>Configure</a><br />";
					   
  $upgrade_remove_prefs = array(
    "eventpost_addcat",
	"eventpost_evtoday",
	"eventpost_headercss",
	"eventpost_daycss",
	"eventpost_todaycss"
	);
}


$eplug_upgrade_done = EC_LAN_108."<br />".$version_notes;


?>