<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT')) { exit; }


$mes = e107::getMessage();


$newarray = e107::getNav()->adminLinks('core');

$buts = "";
$text = "<div style='text-align:center'>
	<table class='table'>";
foreach($newarray as $key=>$funcinfo)
{
	$buts .= e107::getNav()->renderAdminButton($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5], 'default');
}

$text .= $buts;

$text_cat = '';
$td = 0;
while ($td <= 5) 
{
	$text_cat .= "<td class='td' style='width:20%;' ></td>";
	$td++;
}

$td = 1;

$text .= "</tr></table></div>";

if(!empty($buts))
{
	e107::getRender()->tablerender(ADLAN_47." ".ADMINNAME, $mes->render().$text);
}

$text = "<div style='text-align:center'>
	<table class='table'>";


$text .= e107::getNav()->pluginLinks( E_16_PLUGMANAGER, 'default');


$text .= "</tr>
	</table></div>";

e107::getRender()->tablerender(ADLAN_CL_7, $text);

echo admin_info();

