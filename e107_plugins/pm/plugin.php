<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN."pm/languages/admin/".e_LANGUAGE.".php");

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "ADLAN_PM";
$eplug_version = "3.0";
$eplug_author = "McFly";
$eplug_url = "";
$eplug_email = "mcfly@e107.org";
$eplug_description = ADLAN_PM_57;
$eplug_compatible = "e107v.7+";
// leave blank if no readme file

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "pm";

// Mane of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "pm";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "pm_conf.php";

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
"CREATE TABLE ".MPREFIX."private_msg_block (
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

if(!function_exists("pm_uninstall"))
{
	//Remove prefs and menu entry during uninstall
	function pm_uninstall()
	{
		global $sql;
		$sql->db_Delete("core", "e107_name = 'pm_prefs'");
		$sql->db_Delete("menus", "menu_name = 'private_msg_menu'");
	}
}

?>
