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
 * $Source: /cvs_backup/e107_0.8/e107_admin/includes/classis.php,v $
 * $Revision: 1.6 $
 * $Date: 2009-11-18 01:04:42 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER."message_handler.php");
$emessage = &eMessage::getInstance();

$buts = "";

while (list($key, $funcinfo) = each($newarray))
{
	$buts .= render_links($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[6], "classis");
}
if($buts != "")
{
    $text = "<div style='text-align:center'>
			<table style='".ADMIN_WIDTH."'>";
	$text .= $buts;
 	$text .= render_clean();
 	$text .= "</table></div>";
	$ns->tablerender(ADLAN_47." ".ADMINNAME, $emessage->render().$text);
}
$text = "<div style='text-align:center'>
	<table style='".ADMIN_WIDTH."'>";

$text .= getPluginLinks(E_32_PLUGMANAGER, "classis");

$text .= render_clean();

$text .= "</table></div>";

$ns->tablerender(ADLAN_CL_7, $text);

?>
