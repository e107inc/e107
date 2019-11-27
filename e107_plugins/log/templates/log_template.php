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
</tr>";
 
$LOG_TEMPLATE['todaysvisits']['item']   = 
"<tr>
  <td class='forumheader3' style='width: 20%;text-align:left'><img src='{ITEM_IMAGE}' alt='' style='vertical-align: middle;' /><a href='{ITEM_URL}'>{ITEM_KEY}</a></td>
  <td class='forumheader3' style='width: 70%;'>{ITEM_BAR}</td>
  <td class='forumheader3' style='width: 10%; text-align: center;'>{ITEM_PERC}%</td>
</tr>";

$LOG_TEMPLATE['todaysvisits']['end']      =  
"<tr>
  <td class='forumheader' colspan='2'>".ADSTAT_L21." [".ADSTAT_L22."]</td>
  <td class='forumheader' style='text-align: center;'>{TOTALV} [{TOTALU}]</td>
  <td class='forumheader'></td>
  </tr>
</table>
</div>";
 
 
$LOG_TEMPLATE['alltimevisits_total']['start'] = "
<div class='table-responsive' id='alltimevisits_total'>
<table class='table table-striped fborder' style='width: 100%;'>\n
  <colgroup>
    <col style='width: 20%;' />
    <col style='width: 60%;' />
    <col style='width: 10%;' />
    <col style='width: 10%;' />
  </colgroup>
  <tr>
  	<th class='fcaption' >".ADSTAT_L19."</th>\n
  	<th class='fcaption' colspan='2'>".ADSTAT_L23."</th>
  	<th class='fcaption' style='text-align: center;'>%</th>
  </tr>\n";  
$LOG_TEMPLATE['alltimevisits_total']['item'] = "
<tr>
	<td class='forumheader3' >{ITEM_DELETE}{ITEM_IMAGE}
  <a href='{ITEM_URL}' title='{ITEM_TITLE}' >{ITEM_KEY}</a></td>
	<td class='forumheader3' >{ITEM_BAR}</td>
	<td class='forumheader3' style='text-align: center;'>{ITEM_PERC}%</td>
</tr>
";
$LOG_TEMPLATE['alltimevisits_total']['end']  = "
  <tr>
    <td class='forumheader' colspan='2'>".ADSTAT_L21."</td>
    <td class='forumheader' style='text-align: center;'>{TOTAL}</td><td class='forumheader'></td>
  </tr>
  </table>
</div>";   

$LOG_TEMPLATE['alltimevisits_unique']['start'] = "<br />
<div class='table-responsive' id='alltimevisits_unique'>
		<table class='table table-striped fborder' style='width: 100%;'>
		<tr>
			<th class='fcaption' style='width: 20%;'>".ADSTAT_L19."</th>
			<th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L24."</th>
			<th class='fcaption' style='width: 10%; text-align: center;'>%</th>
		</tr>\n";  
$LOG_TEMPLATE['alltimevisits_unique']['item'] = "<tr>
				<td class='forumheader3' style='width: 20%;'><img src='".e_PLUGIN_ABS."log/images/html.png' alt='' style='vertical-align: middle;' /> 
        <a href='{ITEM_URL}'>{ITEM_KEY}</a></td>
				<td class='forumheader3' style='width: 70%;'>{ITEM_BAR}</td>
				<td class='forumheader3' style='width: 10%; text-align: center;'>{ITEM_PERC}%</td>
				</tr>\n";
$LOG_TEMPLATE['alltimevisits_unique']['end']  = "
<tr><td class='forumheader' colspan='2'>".ADSTAT_L21."</td>
<td class='forumheader' style='text-align: center;'>{TOTAL}</td>
<td class='forumheader'></td></tr>\n</table></div>";  

 
$LOG_TEMPLATE['browsers']['start'] = "	
<div class='table-responsive' id='browsers'>			
<table class='table table-striped fborder' style='width: 100%;'>\n
   <tr>
   	<th class='fcaption' colspan='4' style='text-align:center'>{START_CAPTION}</th>
   </tr>\n
   <tr>
   <th class='fcaption' style='width: 20%;'>
   	<a title='{START_TITLE}' href='{START_URL}'>".ADSTAT_L26."</a>
   </th>
  <th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L21."</th>\n
   <th class='fcaption' style='width: 10%; text-align: center;'>%</th>
  </tr>\n"; 
$LOG_TEMPLATE['browsers']['item'] = "
<tr>
<td class='forumheader3' style='width: 20%;'>{ITEM_IMAGE}{ITEM_KEY}</td>
<td class='forumheader3' style='width: 70%;'>{ITEM_BAR}</td> 
<td class='forumheader3' style='width: 10%; text-align: center;'>{ITEM_PERC}%</td>
</tr>\n";
$LOG_TEMPLATE['browsers']['end']  = "
<tr><td class='forumheader' colspan='2'>".ADSTAT_L21."</td><td class='forumheader' style='text-align: center;'>{TOTAL}</td>
<td class='forumheader'></td></tr>
</table><br /></div>";

$LOG_TEMPLATE['browsers']['nostatistic'] = 
"<tr><td class='fcaption' colspan='4' style='text-align:center'>".ADSTAT_L25."</td></tr></table><br /></div>";

$LOG_TEMPLATE['oses']['start'] = "
<div class='table-responsive' id='oses'>
<table class='table table-striped fborder' style='width: 100%;'>\n
	<tr>
		<th class='fcaption' colspan='4' style='text-align:center'>{START_CAPTION}</th>
	</tr>\n
  <tr>
	<th class='fcaption' style='width: 20%;'>
  	<a title='{START_TITLE}' href='{START_URL}'>".ADSTAT_L27."</a></th>\n
  	<th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L21."</th>
	<th class='fcaption' style='width: 10%; text-align: center;'>%</th>
</tr>"; 
$LOG_TEMPLATE['oses']['item'] = $LOG_TEMPLATE['browsers']['item'];
$LOG_TEMPLATE['oses']['end']  = "
<tr>
  <td class='forumheader' colspan='2'>".ADSTAT_L21."</td>
  <td class='forumheader' style='text-align: center;'>{TOTAL}</td>
  <td class='forumheader'>&nbsp;</td>
</tr>
</table><br /></div>";
$LOG_TEMPLATE['oses']['nostatistic'] = $LOG_TEMPLATE['browsers']['nostatistic'];


$LOG_TEMPLATE['domains']['start'] = "
<div class='table-responsive' id='domains'>
<table class='table table-striped fborder' style='width: 100%;'>
				<tr><td class='fcaption' colspan='4' style='text-align:center'>{START_CAPTION}</td></tr>
				<tr><td class='fcaption' style='width: 20%;'>
				<a title='{START_TITLE}' href='{START_URL}'>".ADSTAT_L28."</a></td>\n
				<td class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L21."</td>
        <td class='fcaption' style='width: 10%; text-align: center;'>%</td>\n</tr>"; 
        
$LOG_TEMPLATE['domains']['item'] = "
<tr>
<td class='forumheader3' style='width: 20%;'>{ITEM_KEY}</td>
<td class='forumheader3' style='width: 70%;'>{ITEM_BAR}</td> 
<td class='forumheader3' style='width: 10%; text-align: center;'>{ITEM_PERC}%</td>
</tr>";
$LOG_TEMPLATE['domains']['end']  = "
<tr>
  <td class='forumheader' colspan='2'>".ADSTAT_L21."</td>
  <td class='forumheader' style='text-align: center;'>{TOTAL}</td>
  <td class='forumheader'>&nbsp;</td>
</tr>
</table><br /></div>";
$LOG_TEMPLATE['domains']['nostatistic'] = $LOG_TEMPLATE['browsers']['nostatistic'];


$LOG_TEMPLATE['screens']['start']       = 
"<div class='table-responsive' id='screens'>			
<table class='table table-striped fborder' style='width: 100%;'>\n
   <tr>
   	<th class='fcaption' colspan='4' style='text-align:center'>{START_CAPTION}</th>
   </tr>\n
   <tr>
   <th class='fcaption' style='width: 20%;'>
   	<a title='{START_TITLE}' href='{START_URL}'>".ADSTAT_L26."</a>
   </th>
  <th class='fcaption' style='width: 70%;' colspan='2'>".ADSTAT_L21."</th>\n
   <th class='fcaption' style='width: 10%; text-align: center;'>%</th>
  </tr>\n"; 
  
$LOG_TEMPLATE['screens']['item']        = $LOG_TEMPLATE['browsers']['item'];
$LOG_TEMPLATE['screens']['end']         = $LOG_TEMPLATE['browsers']['end'];
$LOG_TEMPLATE['screens']['nostatistic'] = $LOG_TEMPLATE['browsers']['nostatistic'];

$LOG_TEMPLATE['refers']['start'] =  $LOG_TEMPLATE['browsers']['start']; 
$LOG_TEMPLATE['refers']['item'] = "
<tr>
	<td class='forumheader3'><img src='{ITEM_IMAGE}' alt='' style='vertical-align: middle;' /> 
  <a href='{ITEM_URL}' rel='external'>{ITEM_KEY}</a></td>
	<td class='forumheader3'>{ITEM_BAR}</td>
	<td class='forumheader3' style='text-align: center;'>{ITEM_PERC}%</td>
</tr>";
$LOG_TEMPLATE['refers']['end']  = "
<tr>
  <td class='forumheader' colspan='2'>".ADSTAT_L21."</td>
  <td class='forumheader' style='text-align: center;'>{TOTAL}</td>
  <td class='forumheader'>&nbsp;</td>
</tr>
</table><br /></div>";
$LOG_TEMPLATE['refers']['nostatistic'] = $LOG_TEMPLATE['browsers']['nostatistic'];


$LOG_TEMPLATE['queries']['start'] = ""; 
$LOG_TEMPLATE['queries']['item'] = "";
$LOG_TEMPLATE['queries']['end']  = "";

$LOG_TEMPLATE['visitors']['start'] = ""; 
$LOG_TEMPLATE['visitors']['item'] = "";
$LOG_TEMPLATE['visitors']['end']  = "";

$LOG_TEMPLATE['daily']['start'] = ""; 
$LOG_TEMPLATE['daily']['item'] = "";
$LOG_TEMPLATE['daily']['end']  = "";

$LOG_TEMPLATE['monthly']['start'] = ""; 
$LOG_TEMPLATE['monthly']['item'] = "";
$LOG_TEMPLATE['monthly']['end']  = "";