<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/links_page/link_defines.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$imagedir = e_IMAGE."admin_images/";
if (!defined("IMAGE_NEW")){ define("IMAGE_NEW", (file_exists(THEME."images/new.png") ? THEME."images/new.png" : e_IMAGE."generic/new.png")); }
if (!defined('LINK_ICON_EDIT')) { define("LINK_ICON_EDIT", "<img src='".$imagedir."edit_16.png' alt='' class='icon S16' />"); }
if (!defined('LINK_ICON_DELETE')) { define("LINK_ICON_DELETE", "<img src='".$imagedir."delete_16.png' alt='' class='icon S16' />"); }
if (!defined('LINK_ICON_DELETE_BASE')) { define("LINK_ICON_DELETE_BASE", $imagedir."delete_16.png"); }
if (!defined('LINK_ICON_LINK')) { define("LINK_ICON_LINK", "<img src='".$imagedir."leave_16.png' alt='' class='icon S16' />"); }
if (!defined('LINK_ICON_ORDER_UP_BASE')) { define("LINK_ICON_ORDER_UP_BASE", $imagedir."up.png"); }
if (!defined('LINK_ICON_ORDER_DOWN_BASE')) { define("LINK_ICON_ORDER_DOWN_BASE", $imagedir."down.png"); }
if (!defined('LINK_ICON_ORDER_UP')) { define("LINK_ICON_ORDER_UP", "<img src='".$imagedir."up.png' alt='' class='icon S16' />"); }
if (!defined('LINK_ICON_ORDER_DOWN')) { define("LINK_ICON_ORDER_DOWN", "<img src='".$imagedir."down.png' alt='' class='icon S16' />"); }

?>