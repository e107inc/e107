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

global $PLUGINS_DIRECTORY;
// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "Content Management";
$eplug_version = "1.0";
$eplug_author = "Lisa";
$eplug_logo = "";
$eplug_url = "http://eindhovenseschool.net";
$eplug_email = "lisa@eindhovenseschool.net";
$eplug_description = "A Complete Content Management Section.";
$eplug_compatible = "e107v7";
$eplug_readme = "";        // leave blank if no readme file
$eplug_latest = TRUE; //Show reported threads in admin (use latest.php)
$eplug_status = TRUE; //Show post count in admin (use status.php)

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "content";

// Mane of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "content_menu.php";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_content_config.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/articles_32.png";
$eplug_icon_small = $eplug_folder."/images/articles_16.png";
$eplug_caption =  'Configure Content Management';

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
);

// List of bbcode -----------------------------------------------------------------------------------------------
//$eplug_bb = array('pcontent');

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
	"pcontent"
);


// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
	"CREATE TABLE ".MPREFIX."pcontent (
	content_id int(10) unsigned NOT NULL auto_increment,
	content_heading varchar(250) NOT NULL default '',
	content_subheading varchar(250) NOT NULL default '',
	content_summary text NOT NULL,
	content_text longtext NOT NULL,
	content_author varchar(100) NOT NULL default '',
	content_icon varchar(250) NOT NULL default '',
	content_file text NOT NULL,
	content_image text NOT NULL,
	content_parent varchar(50) NOT NULL default '',
	content_comment tinyint(1) unsigned NOT NULL default '0',
	content_rate tinyint(1) unsigned NOT NULL default '0',
	content_pe tinyint(1) unsigned NOT NULL default '0',
	content_refer text NOT NULL,
	content_datestamp int(10) unsigned NOT NULL default '0',
	content_enddate int(10) unsigned NOT NULL default '0',
	content_class varchar(100) NOT NULL default '', 
	content_pref text NOT NULL, 
	content_order varchar(10) NOT NULL default '0',
	PRIMARY KEY  (content_id)
	) TYPE=MyISAM;"
);

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = TRUE;
$eplug_link_name = "Content";
$eplug_link_url = $PLUGINS_DIRECTORY.'content/content.php';
$eplug_link_icon = "";

// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = "This plugin is now ready to be used.";

// upgrading ... //
$upgrade_add_prefs = "";
$upgrade_remove_prefs = "";
$upgrade_alter_tables = "";
$eplug_upgrade_done = "";


?>