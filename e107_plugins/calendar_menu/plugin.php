<?php
/*
 * e107 website system
 *
 * Copyright (C) 2002-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Event calendar - install definitions etc
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT')) { exit; }

// Plugin info
// -----------
include_lan(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php");
$eplug_name = 'EC_ADLAN_1';
$eplug_version = "3.70";
$eplug_author = "jalist / cameron / McFly / Barry / Lisa_ / steved";
$eplug_url = "http://e107.org";
$eplug_email = "e107@e107.org";
$eplug_description = EC_LAN_107;
$eplug_compatible = "e107v7";
//$eplug_readme = "readme.pdf";
$eplug_compliant = TRUE;
$eplug_folder = "calendar_menu";		// Name of the plugin's folder

$eplug_menu_name = "calendar_menu";		// Name of menu item for plugin
$eplug_conffile = "admin_config.php";	// Name of the admin configuration file

$eplug_icon = $eplug_folder."/images/calendar_32.png";			// 32x32 icon
$eplug_icon_small = $eplug_folder."/images/calendar_16.png";	// 16x16 icon
$eplug_caption = EC_LAN_81; 									// Admin caption


//---------------------------------------
//	Get version of installed plugin
//---------------------------------------
// If there's the potential for install/uninstall/upgrade, $plug is always set.
// Otherwise we're just being scanned for some info - don't execute anything
  $ec_mode = 'scan';
  $ecal_ver = $eplug_version;
  if (isset($plug))
  {
	if (isset($plug['plug_action']))
	{
	  $ec_mode = $plug['plug_action'];		// Definitive answer
	}
	elseif ($plug['plugin_installflag'])
	{  // Potential upgrade - we're installed
	  $ecal_ver = $plug['plugin_version'];
	  $ec_mode = 'upgrade';
	}
	else
	{  // Potential install
	  $ec_mode = 'install';
	}
  }
  elseif (isset($pref['plug_installed'][$eplug_folder])) 
  {		// Potential upgrade - we're installed. But more likely just a scan
    $ecal_ver = $pref['plug_installed'][$eplug_folder];
	$ec_mode = 'upgrade';
  }
if (($ec_mode == 'upgrade') && ($ecal_ver == $eplug_version)) $ec_mode = 'scan';		// Nothing to do if version up to date

list($$ecal_ver_num,$ecal_ver_alpha) = explode(" ", $ecal_ver);
$ecal_ver_num = intval((100*$ecal_ver_num) + 0.1);						// Pull out numeric version as integer
$ecal_ver_alpha = trim($ecal_ver_alpha);								// Release candidate etc

//echo "Calendar Version: ".$ecal_ver."  Numeric: ".$ecal_ver_num."  Alpha: ".$ecal_ver_alpha."  Mode: ".$ec_mode."<br />";


// Work out query to insert default category here, so we only have to modify one place.
require_once('ecal_class.php');	// Gets the define for the 'Default' category
$ec_insert_entries = "INSERT INTO ".MPREFIX."event_cat (event_cat_name, event_cat_description, event_cat_ahead, event_cat_msg1, event_cat_msg2, event_cat_lastupdate)
 VALUES ('".EC_DEFAULT_CATEGORY."', '".EC_ADLAN_A190."', 5,
'Forthcoming event:\n\n{EC_MAIL_CATEGORY}\n\n{EC_MAIL_TITLE} on {EC_MAIL_HEADING_DATE}{EC_MAIL_TIME_START}\n\n".
"{EC_MAIL_DETAILS}\n\nFor further details: {EC_EVENT_LINK=Click Here}\n\nor {EC_MAIL_CONTACT} for further information.', ". 
"'Calendar event imminent:\n\n{EC_MAIL_CATEGORY}\n\n{EC_MAIL_TITLE} on {EC_MAIL_HEADING_DATE}{EC_MAIL_TIME_START}\n\n{EC_MAIL_DETAILS}\n\n".
"For further details see the calendar entry on the web site:\n{EC_MAIL_LINK=Click Here}\n\n {EC_MAIL_CONTACT} for further details', 
'".intval(time())."') ";

// List of preferences
// -------------------
$eplug_prefs = array(
"eventpost_admin" => 254,			// Admin
"eventpost_adminlog" => 0,
"eventpost_showeventcount" => 1,
"eventpost_showmouseover" => 0,
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
"eventpost_mailsubject" => EC_ADLAN_A12." - {EC_MAIL_TITLE}",
"eventpost_mailfrom" => EC_ADLAN_A151,
"eventpost_mailaddress" => EC_ADLAN_A152,
"eventpost_asubs" => 1,
"eventpost_emaillog" => 1,
"eventpost_menuheading" => EC_LAN_140,
"eventpost_daysforward" => 30,
"eventpost_numevents" => 3,
"eventpost_checkrecur" => 1,
"eventpost_linkheader" => 0,
"eventpost_fe_set" => '',
"eventpost_fe_hideifnone" => '0',
"eventpost_fe_showrecent" => 0,
"eventpost_showcaticon" => 0,
"eventpost_printlists" => 1,
"eventpost_namelink" => 1 );


// List of table names 
//--------------------
$eplug_table_names = array("event","event_cat","event_subs" );


// List of sql requests to create tables 
//--------------------------------------
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
	) ENGINE=MyISAM;",
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
	) ENGINE=MyISAM;"
	,
	"CREATE TABLE ".MPREFIX."event_subs (
	event_subid int(10) unsigned NOT NULL auto_increment,
	event_userid  int(10) unsigned NOT NULL default '0',
	event_cat  int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (event_subid)
	) ENGINE=MyISAM;", $ec_insert_entries);


// Create a link in main menu (yes=TRUE, no=FALSE) 
//---------------------------
$ec_dir = e_PLUGIN."calendar_menu/";
$eplug_link = TRUE;
$eplug_link_name = EC_LAN_83; 		// "Calendar";
$eplug_link_url = "".$ec_dir."calendar.php";
$eplug_link_perms = "everyone"; 	// Everyone, Guest, Member, Admin 


// Text to display after plugin successfully installed 
//----------------------------------------------------
$eplug_done = EC_LAN_82; 		// "To activate please go to your menus screen and select the calendar_menu into one of your menu areas.";



// upgrading ... //
$upgrade_add_prefs = array();
$upgrade_remove_prefs = array();
$upgrade_alter_tables = array();
$version_notes = "";


//----------------------------------------------------
//		Solely for upgrades after here
//----------------------------------------------------

if (!function_exists('create_ec_log_dir'))
{
  function create_ec_log_dir($eplug_folder)
  {
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

// Note: Decimal points removed from version number, two decimal places implied 
if ($ecal_ver_num < 350)
{   // To version 3.50
//  echo "Add for V3.5<br />";
$upgrade_alter_tables = array_merge($upgrade_alter_tables,array(
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
	) ENGINE=MyISAM;"
));
$version_notes .= "<u>3.5</u><br />".EC_ADLAN_A156."<br />";
}

// To version 3.60 - fair number of tweaks overall
if ($ecal_ver_num < 360)
{
//  echo "Add for V3.6<br />";
$upgrade_alter_tables = array_merge($upgrade_alter_tables,array(
"ALTER TABLE ".MPREFIX."event_cat DROP event_cat_force",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_description text",
"ALTER TABLE ".MPREFIX."event_cat ADD event_cat_force_class int(10) unsigned NOT NULL default '0'"
));
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
	"eventpost_eventdatecustom" => "%A %d %B %Y",
	"eventpost_nextdatecustom"   => "%d %b",
    "eventpost_menulink" => 0 );
	$upgrade_add_prefs = array_merge($upgrade_add_prefs,$verprefs);
	$version_notes .= "<u>3.6</u><br />".EC_ADLAN_A156."<br />".create_ec_log_dir($eplug_folder)."<br />
	                   <a href='".e_PLUGIN_ABS.$eplug_folder."/".$eplug_conffile."'>Configure</a><br />";
					   
  $upgrade_remove_prefs = array(
    "eventpost_addcat",
	"eventpost_evtoday",
	"eventpost_headercss",
	"eventpost_daycss",
	"eventpost_todaycss"
	);
}


// Mods for 3.70 - not much
if ($ecal_ver_num < 370)
{
//  echo "Add for V3.7<br />";
  $upgrade_alter_tables = array_merge($upgrade_alter_tables,array($ec_insert_entries));		// Add the 'default' category

  $verprefs = array(
    "eventpost_showmouseover" => 0,
	"eventpost_fe_hideifnone" => '0',
	"eventpost_fe_showrecent" => 0,
	"eventpost_printlists" => 1
	);
	$upgrade_add_prefs = array_merge($upgrade_add_prefs,$verprefs);
	$version_notes .= "<u>3.7</u><br />".EC_ADLAN_A164;
}

$eplug_upgrade_done = EC_LAN_108."<br />".$version_notes;


?>