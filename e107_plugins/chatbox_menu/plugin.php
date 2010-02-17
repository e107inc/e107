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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/chatbox_menu/plugin.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

// Plugin info -------------------------------------------------------------------------------------------------------
$eplug_name = "Chatbox";
$eplug_version = "1.0";
$eplug_author = "e107";
$eplug_url = "http://e107.org";
$eplug_email = "jalist@e107.org";
$eplug_description = "Chatbox Menu";
$eplug_compatible = "e107v0.7+";
$eplug_readme = "";
$eplug_status = TRUE;

// Name of the plugin's folder -------------------------------------------------------------------------------------
$eplug_folder = "chatbox_menu";

// Name of menu item for plugin ----------------------------------------------------------------------------------
$eplug_menu_name = "chatbox_menu";

// Name of the admin configuration file --------------------------------------------------------------------------
$eplug_conffile = "admin_chatbox.php";

// Icon image and caption text ------------------------------------------------------------------------------------
$eplug_icon = $eplug_folder."/images/chatbox_32.png";
$eplug_icon_small = $eplug_folder."/images/chatbox_16.png";

$eplug_caption = LAN_CONFIGURE; // e107 generic term.

// List of preferences -----------------------------------------------------------------------------------------------
$eplug_prefs = array(
	'chatbox_posts' => '10',
	'cb_wordwrap' => '20',
	'cb_layer' => '0',
	'cb_layer_height' => '200',
	'cb_emote' => '0',
	'cb_mod' => e_UC_ADMIN
);

// List of table names -----------------------------------------------------------------------------------------------
$eplug_table_names = array(
	"chatbox"
);

// List of sql requests to create tables -----------------------------------------------------------------------------
$eplug_tables = array(
	"CREATE TABLE ".MPREFIX."chatbox (
	cb_id int(10) unsigned NOT NULL auto_increment,
	cb_nick varchar(30) NOT NULL default '',
	cb_message text NOT NULL,
	cb_datestamp int(10) unsigned NOT NULL default '0',
	cb_blocked tinyint(3) unsigned NOT NULL default '0',
	cb_ip varchar(15) NOT NULL default '',
	PRIMARY KEY  (cb_id)
	) TYPE=MyISAM;"
);

// Create a link in main menu (yes=TRUE, no=FALSE) -------------------------------------------------------------
$eplug_link = FALSE;
$eplug_link_name = '';
$eplug_link_url = '';


// upgrading ... //
$upgrade_add_prefs = "";
$upgrade_remove_prefs = "";
$upgrade_alter_tables = "";


if (!function_exists('chatbox_menu_uninstall')) {
	function chatbox_menu_uninstall() {
		global $sql;
		$sql -> db_Update("user", "user_chats='0'");
	}
}

if (!function_exists('chatbox_menu_install')) {
	function chatbox_menu_install() {
		global $sql;
		$sql -> db_Update("user", "user_chats='0'");
	}
}

?>
