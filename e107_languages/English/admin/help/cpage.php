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
 * $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/cpage.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$text = "From this area you can create custom menus and pages with your own content.<br />Menus and Pages are linked so that you may have a menu easily link back to page if you so wish. ";
// $text .= "Please see <a href='http://docs.e107.org/Using Custom Pages and Custom Menus'>http://docs.e107.org/Using Custom Pages and Custom Menus</a> for an explanation of all the features.";

$ns -> tablerender('Custom Menus/Pages Help', $text);
