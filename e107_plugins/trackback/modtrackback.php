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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/trackback/modtrackback.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-22 18:20:53 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");



if (!getperms("P") || !$pref['trackbackEnabled'])
{
	header("location:".e_BASE."index.php");
	exit;
}
require_once(e_ADMIN."auth.php");
$id = intval(e_QUERY);

if (IsSet($_POST['moderate'])) {
	extract($_POST);

	if (is_array($trackback_delete)) {
		while (list ($key, $cid) = each ($trackback_delete)) {
			$sql->db_Delete("trackback", "trackback_id='$cid' ");
		}
	}
	$ns->tablerender("", "<div style='text-align:center'><b>".TRACKBACK_L15."</b></div>");
}
	
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."'>
<table style='".ADMIN_WIDTH."' class='fborder'>";


if (!$sql->db_Select("trackback", "*", "trackback_pid=$id"))
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
	$text .= "<tr><td colspan='5' class='forumheader' style='text-align:center'><input class='button' type='submit' name='moderate' value='".TRACKBACK_L13."' /></td></tr></table></form></div>";
	
		 
}
	
$ns->tablerender(TRACKBACK_L13, $text);
	
require_once(e_ADMIN."footer.php");

?>