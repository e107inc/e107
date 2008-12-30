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
|     $Source: /cvs_backup/e107_0.8/e107_admin/includes/compact.php,v $
|     $Revision: 1.4 $
|     $Date: 2008-12-30 15:56:12 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER."message_handler.php");
$emessage = &eMessage::getInstance();

$buts = "";
$text = "<div style='text-align:center'>
	<table style='".ADMIN_WIDTH."'>";

while (list($key, $funcinfo) = each($newarray)) {
	$buts .= render_links($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5], 'default');
}
$text .= $buts;

$text_cat = '';
while ($td <= 5) {
	$text_cat .= "<td class='td' style='width:20%;' ></td>";
	$td++;
}
$td = 1;

$text .= "</tr></table></div>";

if($buts !=""){
	$ns->tablerender(ADLAN_47." ".ADMINNAME, $emessage->render().$text);
}

$text = "<div style='text-align:center'>
	<table style='".ADMIN_WIDTH."'>";


$text .= getPluginLinks( E_16_PLUGMANAGER, 'default');


$text .= "</tr>
	</table></div>";

$ns->tablerender(ADLAN_CL_7, $text);

echo admin_info();

?>