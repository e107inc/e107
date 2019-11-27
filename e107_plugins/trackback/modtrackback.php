<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin administration - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/trackback/modtrackback.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
$eplug_admin = true;
require_once("../../class2.php");

if (!getperms("P") || !e107::isInstalled('trackback') || !$pref['trackbackEnabled'])
{
	e107::redirect();
	exit;
}

require_once(e_ADMIN."auth.php");
if (isset($_POST['moderate'])) 
{
	$temp = array();
	if (is_array($_POST['trackback_delete'])) 
	{
		while (list ($key, $cid) = each ($_POST['trackback_delete'])) 
		{
			$cid = intval($cid);
			if ($cid > 0)
			{
				$sql->db_Delete("trackback", "trackback_id=".$cid);
				$temp[] = $cid;
			}
		}
		if (count($temp))
		{
			e107::getLog()->add('TRACK_02',implode(', ',$temp), E_LOG_INFORMATIVE,'');
		}
	}
	$ns->tablerender("", "<div style='text-align:center'><b>".TRACKBACK_L15."</b></div>");
	$e107cache->clear("news.php");
}
	
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."'>
<table style='".ADMIN_WIDTH."' class='fborder'>";

if (e_QUERY=='all') 
{
	$res=$sql->db_Select("trackback", "*");
} 
else 
{
	$res=$sql->db_Select("trackback", "*", "trackback_pid=".intval(e_QUERY));
}

if (!$res)
{
	$text .= "<tr><td class='forumheader3' style='text-align:center'>".TRACKBACK_L12.".</td></tr></table></form></div>";
} 
else
{
	$tbArray = $sql -> db_getList();
	foreach($tbArray as $trackback)
	{
		extract($trackback);
		$text .= "<tr>
		<td class='forumheader3' style='width: 30%;'><a href='$trackback_url' rel='external'>$trackback_title</a></td>
		<td class='forumheader3' style='width: 40%;'>$trackback_excerpt</td>
		<td class='forumheader3' style='width: 20%;'>$trackback_blogname</td>
		<td class='forumheader3' style='width: 10%;'><input type='checkbox' name='trackback_delete[]' value='$trackback_id' /> ".TRACKBACK_L14."</td>
		</tr>\n";
	}
	$text .= "<tr><td colspan='5' class='forumheader' style='text-align:center'><input class='btn btn-default btn-secondary button' type='submit' name='moderate' value='".TRACKBACK_L13."' /></td></tr></table></form></div>";
}
	
$ns->tablerender(TRACKBACK_L13, $text);
	
require_once(e_ADMIN."footer.php");

?>