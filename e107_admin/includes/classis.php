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
|     $Source: /cvs_backup/e107_0.8/e107_admin/includes/classis.php,v $
|     $Revision: 1.3 $
|     $Date: 2008-08-25 10:46:32 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

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
	$ns->tablerender(ADLAN_47." ".ADMINNAME, $text);
}
$text = "<div style='text-align:center'>
	<table style='".ADMIN_WIDTH."'>";

$text .= getPluginLinks(E_32_PLUGMANAGER, "classis");

$text .= render_clean();

$text .= "</table></div>";

$ns->tablerender(ADLAN_CL_7, $text);

?>
