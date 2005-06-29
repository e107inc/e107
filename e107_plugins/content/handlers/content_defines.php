<?php
global $plugindir;
$plugindir = e_PLUGIN."content/";
$imagedir = e_IMAGE."admin_images/";
$lan_file = e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content.php';
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN.'content/languages/English/lan_content.php');

if (!defined('CONTENT_ICON_EDIT')) { define("CONTENT_ICON_EDIT", "<img src='".$imagedir."maintain_16.png' alt='".CONTENT_ICON_LAN_0."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_LINK')) { define("CONTENT_ICON_LINK", "<img src='".$imagedir."leave_16.png' alt='".CONTENT_ICON_LAN_15."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_DELETE')) { define("CONTENT_ICON_DELETE", "<img src='".$imagedir."delete_16.png' alt='".CONTENT_ICON_LAN_1."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_DELETE_BASE')) { define("CONTENT_ICON_DELETE_BASE", $imagedir."delete_16.png"); }
if (!defined('CONTENT_ICON_OPTIONS')) { define("CONTENT_ICON_OPTIONS", "<img src='".$imagedir."cat_settings_16.png' alt='".CONTENT_ICON_LAN_2."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_USER')) { define("CONTENT_ICON_USER", "<img src='".$imagedir."users_16.png' alt='".CONTENT_ICON_LAN_3."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_FILE')) { define("CONTENT_ICON_FILE", "<img src='".$plugindir."images/file_16.png' alt='".CONTENT_ICON_LAN_4."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_NEW')) { define("CONTENT_ICON_NEW", "<img src='".$imagedir."articles_16.png' alt='".CONTENT_ICON_LAN_5."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_SUBMIT')) { define("CONTENT_ICON_SUBMIT", "<img src='".$plugindir."images/redo.png' alt='".CONTENT_ICON_LAN_6."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_AUTHORLIST')) { define("CONTENT_ICON_AUTHORLIST", "<img src='".$plugindir."images/personal.png' alt='".CONTENT_ICON_LAN_7."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_WARNING')) { define("CONTENT_ICON_WARNING", "<img src='".$plugindir."images/warning_16.png' alt='".CONTENT_ICON_LAN_8."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_OK')) { define("CONTENT_ICON_OK", "<img src='".$plugindir."images/ok_16.png' alt='".CONTENT_ICON_LAN_9."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_ERROR')) { define("CONTENT_ICON_ERROR", "<img src='".$plugindir."images/error_16.png' alt='".CONTENT_ICON_LAN_10."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_ORDERCAT')) { define("CONTENT_ICON_ORDERCAT", "<img src='".$plugindir."images/view_remove.png' alt='".CONTENT_ICON_LAN_11."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_ORDERALL')) { define("CONTENT_ICON_ORDERALL", "<img src='".$plugindir."images/window_new.png' alt='".CONTENT_ICON_LAN_12."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_CONTENTMANAGER')) { define("CONTENT_ICON_CONTENTMANAGER", "<img src='".$plugindir."images/manager_48.png' alt='".CONTENT_ICON_LAN_14."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_CONTENTMANAGER_SMALL')) { define("CONTENT_ICON_CONTENTMANAGER_SMALL", "<img src='".$plugindir."images/manager_16.png' alt='".CONTENT_ICON_LAN_13."' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_ORDER_UP')) { define("CONTENT_ICON_ORDER_UP", "<img src='".$imagedir."up.png' alt='".CONTENT_ADMIN_ITEM_LAN_63."' />"); }
if (!defined('CONTENT_ICON_ORDER_DOWN')) { define("CONTENT_ICON_ORDER_DOWN", "<img src='".$imagedir."down.png' alt='".CONTENT_ADMIN_ITEM_LAN_64."' />"); }

?>