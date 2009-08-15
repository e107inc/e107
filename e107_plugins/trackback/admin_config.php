<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin administration - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/trackback/admin_config.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-08-15 11:55:30 $
 * $Author: marj_nl_fr $
 *
*/
require_once("../../class2.php");
if (!getperms("P") || !plugInstalled('trackback')) 
{
	header("location:".e_BASE."index.php");
	exit() ;
}

include_lan(e_PLUGIN."trackback/languages/".e_LANGUAGE."_admin_trackback.php");
	
require_once(e_ADMIN."auth.php");
	
if (isset($_POST['updatesettings'])) 
{
	$temp = array();
	if ($pref['trackbackEnabled'] != $_POST['trackbackEnabled'])
	{
		$temp['trackbackEnabled'] = $_POST['trackbackEnabled'];
		$e107cache->clear('news.php');
	}
	$temp['trackbackString'] = $tp->toDB($_POST['trackbackString']);
	if ($admin_log->logArrayDiffs($temp, $pref, 'TRACK_01'))
	{
		save_prefs();		// Only save if changes
		$message = TRACKBACK_L4;
	}
	else
	{
		$message = TRACKBACK_L17;
	}
}

	
if (isset($message)) 
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}
	

$text = "
<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='".ADMIN_WIDTH."' class='fborder'>
<tr>
<td style='width:50%' class='forumheader3'>".TRACKBACK_L7."</td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input type='radio' name='trackbackEnabled' value='1'".($pref['trackbackEnabled'] ? " checked='checked'" : "")." /> ".TRACKBACK_L5."&nbsp;&nbsp;
<input type='radio' name='trackbackEnabled' value='0'".(!$pref['trackbackEnabled'] ? " checked='checked'" : "")." /> ".TRACKBACK_L6."
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".TRACKBACK_L8."</td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input  size='50' class='tbox' type='text' name='trackbackString' value='".$pref['trackbackString']."' />
</td>
</tr>

<td colspan='2' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='updatesettings' value='".TRACKBACK_L9."' />
</td>
</tr>

</table>
</form>
</div>
";



$ns->tablerender(TRACKBACK_L10, $text);
	
require_once(e_ADMIN."footer.php");
?>