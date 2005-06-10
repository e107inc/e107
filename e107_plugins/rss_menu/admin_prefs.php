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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/rss_menu/admin_prefs.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-06-10 00:40:48 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/


require_once("../../class2.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
$lan_file = e_PLUGIN."rss_menu/languages/".e_LANGUAGE.".php";
require_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."rss_menu/languages/English.php");

require_once(e_ADMIN."auth.php");

if(IsSet($_POST['updatesettings'])){
	$pref['rss_feeds'] = implode(",",$_POST['feeds']);
	$pref['rss_newscats'] = $_POST['rss_newscats'];
	save_prefs();
	$message = LAN_SAVED;
}

if(isset($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table class='fborder' style='width:94%' class='fborder'>
<tr><td class='fcaption'>".BACKEND_MENU_L2."</td><td class='fcaption'>Enable</td></tr>";

	$feedlist[1] = "News";
	$feedlist[9] = "Chatbox Posts";
	$feedlist[12] = "Downloads";
	$feedlist[6] = "Forum Threads";
	$feedlist[7] = "Forum Posts";

	$preset = explode(",",$pref['rss_feeds']);

	foreach($feedlist as $key=>$name){
		$text .= "<tr><td class='forumheader3'>$name</td>";
		$text .= "<td>";
		$selected = in_array($key,$preset) ? "checked='checked'" : "";
		$text .= "<input type='checkbox'  name='feeds[]' value='$key' $selected />";

	$text .="</td></tr>";
	}

	$text .= "<tr><td class='forumheader3'>Enable seperate feeds for each news category?</td>";
	$text .= "<td>";
	$sel = ($pref['rss_newscats'] == 1) ? " checked='checked' " : "";
	$text .= "<input type='checkbox' $sel class='tbox' name='rss_newscats' value='1' />
				</td></tr>";

	$text .="<tr style='vertical-align:top'>
	<td colspan='2'  style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='updatesettings' value='".LAN_SAVE."' />
	</td>
	</tr>
	</table>
	</form>
	</div>";

$ns -> tablerender(BACKEND_MENU_L2, $text);

require_once(e_ADMIN."footer.php");

?>