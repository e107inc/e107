<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/newsletter/plugin.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-11-17 13:48:45 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN."newsletter/languages/".e_LANGUAGE.".php");

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "NLLAN_01";
$eplug_version = "1.0";
$eplug_author = "e107 Inc.";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = NLLAN_02;
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";
$eplug_category = "content";

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "newsletter";

// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "newsletter_menu";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_config.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/nl_32.png";
$eplug_icon_small = $eplug_folder."/images/nl_16.png";
$eplug_caption = NLLAN_03;

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array();

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array("newsletter");

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(

"CREATE TABLE ".MPREFIX."newsletter (
  newsletter_id int(10) unsigned NOT NULL auto_increment,
  newsletter_datestamp int(10) unsigned NOT NULL,
  newsletter_title varchar(200) NOT NULL,
  newsletter_text text NOT NULL,
  newsletter_header text NOT NULL,
  newsletter_footer text NOT NULL,
  newsletter_subscribers text NOT NULL,
  newsletter_parent int(11) NOT NULL,
  newsletter_flag tinyint(4) NOT NULL,
  newsletter_issue varchar(100) NOT NULL,
  PRIMARY KEY  (newsletter_id)
  ) TYPE=MyISAM;");


// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = "";
$eplug_link_url = "";

// Text to display after plugin successfully installed ------------------------------------------------------------------
$eplug_done = NLLAN_04;

?>