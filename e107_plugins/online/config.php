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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/online/config.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-05-01 19:50:56 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
$eplug_admin = TRUE;
require_once("../../class2.php");
include_lan(e_PLUGIN."online/languages/".e_LANGUAGE.".php");

if (!getperms("1")) {
	header("location:".e_BASE."index.php");
	exit ;
}
require_once(e_ADMIN."auth.php");

if (isset($_POST['update_menu'])) {
	while (list($key, $value) = each($_POST)) {
		if ($value != LAN_UPDATE) {
			$menu_pref[$key] = $value;
		}
	}
	$tmp = addslashes(serialize($menu_pref));
	$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='menu_pref' ");
	$ns->tablerender("", "<div style='text-align:center'><b>".LAN_UPDATED."</b></div>");
}


function defaultpref(){
	$menu_pref['online_ls_caption'] = 'LAN_LASTSEEN_1';		//caption for the lastseen_menu
	$menu_pref['online_ls_amount'] = 10;					//amount of records to show in the lastseen_menu
	$menu_pref['online_caption'] = 'LAN_ONLINE_10';			//caption for the online_menu
	$menu_pref['online_show_memberlist'] = true;			//toggle whether to show a simple member list of online members (shwoing user1, user2, user3)
	$menu_pref['online_show_memberlist_extended'] = false;	//toggle whether to show the extended member list of online members (showing 'user viewing page')
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' name='menu_form'>
<table style='".ADMIN_WIDTH."' class='fborder' >

<tr><td class='fcaption' colspan='2'>".LAN_ONLINE_ADMIN_1."</td></tr>

<tr>
<td style='width:40%' class='forumheader3'>".LAN_ONLINE_ADMIN_2."</td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='online_ls_caption' size='30' value='".$tp->toHTML($menu_pref['online_ls_caption'],"","defs")."' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>".LAN_ONLINE_ADMIN_3."</td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='online_ls_amount' size='3' value='".intval($menu_pref['online_ls_amount'])."' maxlength='3' />
</td>
</tr>

<tr><td class='fcaption' colspan='2'>".LAN_ONLINE_ADMIN_4."</td></tr>

<tr>
<td style='width:40%' class='forumheader3'>".LAN_ONLINE_ADMIN_5."</td>
<td style='width:60%' class='forumheader3'>
<input class='tbox' type='text' name='online_caption' size='30' value='".$tp->toHTML($menu_pref['online_caption'],"","defs")."' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>".LAN_ONLINE_ADMIN_6."</td>
<td style='width:60%' class='forumheader3'>
<input type='radio' value='1' id='online_show_memberlist1' name='online_show_memberlist' ".(varsettrue($menu_pref['online_show_memberlist']) ? "checked='checked'" : "")." /> ".LAN_ONLINE_ADMIN_ENABLED."
<input type='radio' value='0' id='online_show_memberlist0' name='online_show_memberlist' ".(varsettrue($menu_pref['online_show_memberlist']) ? "" : "checked='checked'")." /> ".LAN_ONLINE_ADMIN_DISABLED."
</td>
</tr>

<tr>
<td style='width:40%' class='forumheader3'>".LAN_ONLINE_ADMIN_7."</td>
<td style='width:60%' class='forumheader3'>
<input type='radio' value='1' id='online_show_memberlist_extended1' name='online_show_memberlist_extended' ".(varsettrue($menu_pref['online_show_memberlist_extended']) ? "checked='checked'" : "")." /> ".LAN_ONLINE_ADMIN_ENABLED."
<input type='radio' value='0' id='online_show_memberlist_extended0' name='online_show_memberlist_extended' ".(varsettrue($menu_pref['online_show_memberlist_extended']) ? "" : "checked='checked'")." /> ".LAN_ONLINE_ADMIN_DISABLED."
</td>
</tr>

<tr>
<td colspan='2' class='forumheader' style='text-align:center'><input class='button' type='submit' name='update_menu' value='".LAN_UPDATE."' /></td>
</tr>
</table>
</form>
</div>";

$ns->tablerender(LAN_ONLINE_ADMIN_0, $text);
require_once(e_ADMIN."footer.php");
?>