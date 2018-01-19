<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * News menus templates
 */

if (!defined('e107_INIT'))  exit;



$LOG_TEMPLATE['todaysvisits']['start']        =  
"<div class='table-responsive' id='log-todaysvisits'>
<table class='table table-striped fborder' style='width: 100%;'>
<tr>
	<th class='fcaption' style='width: 20%;'>".ADSTAT_L19."</th>
	<th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L20."</th>
	<th class='fcaption' style='width: 10%; text-align: center;'>%</th>
</tr>\n";
 
$LOG_TEMPLATE['todaysvisits']['item']   = 
"<tr>\n<td class='forumheader3' style='width: 20%;text-align:left'>
<img src='".e_PLUGIN."log/images/html.png' alt='' style='vertical-align: middle;' /> 
<a href='{ITEM_URL}'>{ITEM_KEY}</a>
</td>\n<td class='forumheader3' style='width: 70%;'>{ITEM_BAR}</td>
<td class='forumheader3' style='width: 10%; text-align: center;'>{ITEM_PERC}%</td>\n</tr>\n";

$LOG_TEMPLATE['todaysvisits']['end']      =  
"<tr><td class='forumheader' colspan='2'>".ADSTAT_L21." [".ADSTAT_L22."]</td><td class='forumheader' style='text-align: center;'>{TOTALV} [{TOTALU}]</td>
    <td class='forumheader'></td></tr></table>
</div>";
 