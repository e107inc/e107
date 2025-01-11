<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * _blank menu file.
 *
 */


if (!defined('e107_INIT')) { exit; }

// $sql = e107::getDB(); 				// mysql class object
// $tp = e107::getParser(); 			// parser for converting to HTML and parsing templates etc.
// $frm = e107::getForm(); 				// Form element class.
// $ns = e107::getRender();				// render in theme box.

$text = "Empty Menu";

if(!empty($parm))
{
	$text .= print_a($parm,true); // e_menu.php form data.
}

e107::getRender()->tablerender("_blank", $text);






