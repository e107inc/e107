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
|     $Source: /cvs_backup/e107_0.8/e107_admin/includes/cascade.php,v $
|     $Revision: 1.4 $
|     $Date: 2008-12-30 15:56:12 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER."message_handler.php");
$emessage = &eMessage::getInstance();

$text = "<div style='text-align:center'>
	<table class='fborder' style='".ADMIN_WIDTH."'>";

while (list($key, $funcinfo) = each($newarray))
{
	$text .= render_links($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5], 'adminb');
}

$text .= "<tr>
	<td class='fcaption' colspan='5'>
	".ADLAN_CL_7."
	</td>
	</tr>";



$text .= getPluginLinks( E_16_PLUGMANAGER, 'adminb');

$text .= "</table></div>";

$ns->tablerender(ADLAN_47." ".ADMINNAME, $emessage->render().$text);

?>
