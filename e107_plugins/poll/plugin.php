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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/poll/plugin.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-03-04 08:57:03 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
	
@include_once(e_PLUGIN."poll/languages/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."poll/languages/English.php");
	
// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = POLL_ADLAN01;
$eplug_version = "2.0";
$eplug_author = "Steve Dunstan (jalist)";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = POLL_ADLAN02;
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";
	
// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "poll";
	
// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "poll_menu";
	
// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_config.php";
	
$eplug_sc = array("");

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/polls_32.png";
$eplug_icon_small = $eplug_folder."/images/polls_16.png";
$eplug_caption = POLL_ADLAN03;
	
// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array();
	
// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array("poll");
	
// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
"CREATE TABLE ".MPREFIX."poll (
  poll_id int(10) unsigned NOT NULL auto_increment,
  poll_datestamp int(10) unsigned NOT NULL default '0',
  poll_start_datestamp int(10) unsigned NOT NULL default '0',
  poll_end_datestamp int(10) unsigned NOT NULL default '0',
  poll_admin_id int(10) unsigned NOT NULL default '0',
  poll_title varchar(250) NOT NULL default '',
  poll_options text NOT NULL,
  poll_votes text NOT NULL,
  poll_ip text NOT NULL,
  poll_type tinyint(1) unsigned NOT NULL default '0',
  poll_comment tinyint(1) unsigned NOT NULL default '1',
  poll_allow_multiple tinyint(1) unsigned NOT NULL default '0',
  poll_result_type tinyint(2) unsigned NOT NULL default '0',
  poll_vote_userclass tinyint(3) unsigned NOT NULL default '0',
  poll_storage_method tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (poll_id)
) TYPE=MyISAM;");
	
// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$eplug_link_url = "";
	
// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = POLL_ADLAN04;
	
?>