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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/plugin.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-22 16:13:12 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
// Plugin info -------------------------------------------------------------------------------------------------------
$lan_file = e_PLUGIN."links_page/languages/".e_LANGUAGE.".php";
@require_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."links_page/languages/English.php");
$eplug_name = "Links Page";
$eplug_version = "1.0";
$eplug_author = "e107";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = "Links Page For Displaying External Web Links";
$eplug_compatible = "e107v6";
$eplug_readme = "";
$eplug_latest = TRUE;

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "links_page";

// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_config.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/linkspage_32.png";
$eplug_icon_small = $eplug_folder."/images/linkspage_16.png";
$eplug_caption =  LCLAN_101;

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
	"link_submit" => 1,
	"link_submit_class" => 0,
	"linkpage_categories" => 0
);

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
	"links_page_cat",
	"links_page"
);

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
	"CREATE TABLE ".MPREFIX."links_page_cat (
	link_category_id int(10) unsigned NOT NULL auto_increment,
	link_category_name varchar(100) NOT NULL default '',
	link_category_description varchar(250) NOT NULL default '',
	link_category_icon varchar(100) NOT NULL default '',
	PRIMARY KEY  (link_category_id)
	) TYPE=MyISAM;",
	"CREATE TABLE ".MPREFIX."links_page (
	link_id int(10) unsigned NOT NULL auto_increment,
	link_name varchar(100) NOT NULL default '',
	link_url varchar(200) NOT NULL default '',
	link_description text NOT NULL,
	link_button varchar(100) NOT NULL default '',
	link_category tinyint(3) unsigned NOT NULL default '0',
	link_order int(10) unsigned NOT NULL default '0',
	link_refer int(10) unsigned NOT NULL default '0',
	link_open tinyint(1) unsigned NOT NULL default '0',
	link_class tinyint(3) unsigned NOT NULL default '0',
	PRIMARY KEY  (link_id)
	) TYPE=MyISAM;"
);

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = TRUE;
$eplug_link_name = LCLAN_103;
$eplug_link_url = e_PLUGIN."links_page/links.php";


// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = LCLAN_102;


// upgrading ... //

$upgrade_add_prefs = "";

$upgrade_remove_prefs = "";

$upgrade_alter_tables = "";

$eplug_upgrade_done = "";





?>