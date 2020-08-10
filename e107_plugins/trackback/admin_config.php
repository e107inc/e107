<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin administration - Trackback
 *
 * $URL$
 * $Id$
 *
*/
require_once("../../class2.php");
if (!getperms("P") || !e107::isInstalled('trackback')) 
{
	e107::redirect('admin');
	exit() ;
}

e107::includeLan(e_PLUGIN."trackback/languages/".e_LANGUAGE."_admin_trackback.php");
	
require_once(e_ADMIN."auth.php");
$frm = e107::getForm();
$mes = e107::getMessage();
	
if (isset($_POST['updatesettings'])) 
{
	$temp = array();
	if ($pref['trackbackEnabled'] != $_POST['trackbackEnabled'])
	{
		$temp['trackbackEnabled'] = $_POST['trackbackEnabled'];
		$e107cache->clear('news.php');
	}
	$temp['trackbackString'] = $tp->toDB($_POST['trackbackString']);
	
	e107::getConfig('core')->setPref($temp)->save(false);
	
}
	
$ns->tablerender($caption, $mes->render() . $text);

$text = "
<form method='post' action='".e_SELF."'>
<table class='table adminform'>
<tr>
	<td>".TRACKBACK_L7."</td>
	<td>".$frm->radio_switch('trackbackEnabled', $pref['trackbackEnabled'])."</td>
</tr>

<tr>
	<td>".TRACKBACK_L8."</td>
	<td><input  size='50' class='tbox' type='text' name='trackbackString' value='".$pref['trackbackString']."' />	</td>
</table>
<div class='buttons-bar center'>
	".$frm->admin_button('updatesettings', LAN_UPDATE, 'update')."
</div>
</form>
</div>
";

$ns->tablerender(TRACKBACK_L10, $text);
	
require_once(e_ADMIN."footer.php");
