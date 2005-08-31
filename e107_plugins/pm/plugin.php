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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm/plugin.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-08-31 18:57:59 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
	
@include_once(e_PLUGIN."pm/languages/admin/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."pm/languages/admin/English.php");
	
// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = ADLAN_PM;
$eplug_version = "3.0";
$eplug_author = "McFly";
$eplug_url = "";
$eplug_email = "mcfly@e107.org";
$eplug_description = "This plugin is a fully featured Private Messaging system.";
$eplug_compatible = "e107v.7+";
// leave blank if no readme file
	
// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "pm";
	
// Mane of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "pm";
	
// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "pm_conf.php";
	
$eplug_sc = array('SENDPM');

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/pvt_message_32.png";
$eplug_icon_small = $eplug_folder."/images/pvt_message_16.png";
$eplug_caption = ADLAN_PM_2; //"Configure Private Messager";
	
// List of preferences -----------------------------------------------------------------------------------------------

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
"private_msg",
"private_msg_block" 
);
	
// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
"CREATE TABLE ".MPREFIX."private_msg (
  pm_id int(10) unsigned NOT NULL auto_increment,
  pm_from int(10) unsigned NOT NULL default '0',
  pm_to varchar(250) NOT NULL default '',
  pm_sent int(10) unsigned NOT NULL default '0',
  pm_read int(10) unsigned NOT NULL default '0',
  pm_subject text NOT NULL,
  pm_text text NOT NULL,
  pm_sent_del tinyint(1) unsigned NOT NULL default '0',
  pm_read_del tinyint(1) unsigned NOT NULL default '0',
  pm_attachments text NOT NULL,
  pm_option varchar(250) NOT NULL default '',
  pm_size int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pm_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;",
"CREATE TABLE e107_private_msg_block (
  pm_block_id int(10) unsigned NOT NULL auto_increment,
  pm_block_from int(10) unsigned NOT NULL default '0',
  pm_block_to int(10) unsigned NOT NULL default '0',
  pm_block_datestamp int(10) unsigned NOT NULL default '0',
  pm_block_count int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pm_block_id)
) TYPE=MyISAM AUTO_INCREMENT=1 ;"
);
	
// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$eplug_link_url = "";
	
// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = ADLAN_PM_1;

?>