<?php
/*
+ ----------------------------------------------------------------------------+
|    e107 website system
|
|    ©Steve Dunstan 2001-2002
|    http://e107.org
|    jalist@e107.org
|
|    Released   under the   terms and   conditions of the
|    GNU    General Public  License (http://gnu.org).
|
|    $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/link_defines.php,v $
|    $Revision: 1.1 $
|    $Date: 2005-07-01 08:22:10 $
|    $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

$imagedir = e_IMAGE."/admin_images/";
if (!defined("IMAGE_NEW")){ define("IMAGE_NEW", (file_exists(THEME."generic/new.png") ? THEME."generic/new.png" : e_IMAGE."generic/".IMODE."/new.png")); }
if (!defined('LINK_ICON_EDIT')) { define("LINK_ICON_EDIT", "<img src='".$imagedir."maintain_16.png' alt='' style='border:0; cursor:pointer;' />"); }
if (!defined('LINK_ICON_DELETE')) { define("LINK_ICON_DELETE", "<img src='".$imagedir."delete_16.png' alt='' style='border:0; cursor:pointer;' />"); }
if (!defined('LINK_ICON_DELETE_BASE')) { define("LINK_ICON_DELETE_BASE", $imagedir."delete_16.png"); }
if (!defined('LINK_ICON_LINK')) { define("LINK_ICON_LINK", "<img src='".$imagedir."leave_16.png' alt='' style='border:0; cursor:pointer;' />"); }
if (!defined('LINK_ICON_ORDER_UP_BASE')) { define("LINK_ICON_ORDER_UP_BASE", $imagedir."up.png"); }
if (!defined('LINK_ICON_ORDER_DOWN_BASE')) { define("LINK_ICON_ORDER_DOWN_BASE", $imagedir."down.png"); }
if (!defined('LINK_ICON_ORDER_UP')) { define("LINK_ICON_ORDER_UP", "<img src='".$imagedir."up.png' alt='' style='border:0; cursor:pointer;' />"); }
if (!defined('LINK_ICON_ORDER_DOWN')) { define("LINK_ICON_ORDER_DOWN", "<img src='".$imagedir."down.png' alt='' style='border:0; cursor:pointer;' />"); }

?>