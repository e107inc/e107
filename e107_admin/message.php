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
|     $Source: /cvs_backup/e107_0.7/e107_admin/message.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-02-11 22:04:47 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");

$messageTypes = array("Reported Forum Post", "Broken Download", "Dev Team Message");
$queryString = "";
foreach($messageTypes as $types) {
	$queryString .= " gen_type='$types' OR";
}
$queryString = substr($queryString, 0, -3);

$e_sub_cat = 'message';
require_once("auth.php");
$gen = new convert;

if(isset($_POST['delete_message']))
{
	$id = substr($_POST['delete_message'], -1);
	$sql->db_Delete("generic", "gen_id=$id");
	$message = MESSLAN_3;
}

if(isset($_POST['delete_all']) && isset($_POST['deleteconfirm']))
{
	$sql->db_Delete("generic", $queryString);
	$message = MESSLAN_6;
}


if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


if($amount = $sql -> db_Select("generic", "*", $queryString))
{


	$text = "<table style='width: 98%;' class='fborder'>\n<form method='post' action='".e_SELF."'>\n";
	$messages = $sql -> db_getList();

	foreach($messages as $message)
	{
		extract($message);

		$sql -> db_Select("user", "user_name", "user_id=$gen_user_id");
		$user = $sql -> db_Fetch();
		$user = "<a href='".e_BASE."user.php?id.$gen_user_id'>".$user['user_name']."</a>";

		switch($gen_type)
		{
			case "Broken Download":
				$link = "<a href='".e_BASE."download.php?view.$gen_intdata' rel='external' title='".MESSLAN_11."'>$gen_ip</a>";
			break;
			case "Reported Forum Post":
				$sql -> db_Select("forum_t", "thread_parent", "thread_id=$gen_intdata");
				$thread = $sql -> db_Fetch();
				$link = "<a href='".e_PLUGIN."forum/forum_viewtopic.php?".$thread['thread_parent']."#$gen_intdata'>$gen_ip</a>";
			break;
			case "Dev Team Message":
				$link = "";
			break;
		}


		$text .= "<tr>
<td style='width: 100%;' class='forumheader3'><b>".MESSLAN_8."</b>: $gen_type<br />
<b>".MESSLAN_9."</b>: ".$gen->convert_date($gen_datestamp, 'long')."<br />
<b>".MESSLAN_10."</b>: $user<br />
<b>".MESSLAN_13."</b>: $link".
($gen_chardata ? "<br /><b>".MESSLAN_12."</b>: $gen_chardata" : "")."<br /><input class='button' type='submit' name='delete_message' value='".MESSLAN_2." $gen_id' />
</td>\n</tr>\n";
	}

$text .= "
<tr>
<td><br /><input class='button' type='submit' name='delete_all' value='".MESSLAN_4."' />
<input type='checkbox' name='deleteconfirm' value='1'> ".MESSLAN_5."
</td>
</tr>


</form></table>";
}
else
{
	$text = MESSLAN_7;
}
$ns->tablerender(MESSLAN_1, $text);
	
require_once("footer.php");


















/*
if ($action != "edit") {
	$text = $rs->form_open("post", e_SELF, "ban_form")."<div style='text-align:center'>".$rs->form_hidden("ban_secure", "1")."<div style='padding : 1px; ".ADMIN_WIDTH."; height : 170px; overflow : auto; margin-left: auto; margin-right: auto;'>\n";
	if (!$ban_total = $sql->db_Select("banlist")) {
		$text .= "<div style='text-align:center'>".BANLAN_2."</div>";
	} else {
		$text .= "<table class='fborder' style='width:99%;'>
			<tr>
			<td style='width:70%' class='fcaption'>".BANLAN_10."</td>
			<td style='width:30%' class='fcaption'>".BANLAN_11."</td>
			</tr>";
		$count = 0;
		while ($row = $sql->db_Fetch()) {
			extract($row);
			$text .= "<tr><td style='width:70%' class='forumheader3'>$banlist_ip<br />".BANLAN_7.": $banlist_reason</td>
				<td style='width:30%; text-align:center' class='forumheader3'>".$rs->form_button("submit", "main_edit_$count", BANLAN_12, "onclick=\"document.getElementById('ban_form').action='".e_SELF."?edit-$banlist_ip'\"").$rs->form_button("submit", "main_delete_$count", BANLAN_4, "onclick=\"document.getElementById('ban_form').action='".e_SELF."?remove-$banlist_ip'\"")."</td>\n</tr>";
			$count++;
		}
		$text .= "</table>\n";
	}
	$text .= "</div></div>".$rs->form_close();
	$ns->tablerender(BANLAN_3, $text);
}
	
if ($action == "edit") {
	$sql2->db_Select("banlist", "*", "banlist_ip='$sub_action'");
	$row = $sql2->db_Fetch();
	extract($row);
} else {
	unset($banlist_ip, $banlist_reason);
	if (e_QUERY && strpos($_SERVER["HTTP_REFERER"], "userinfo")) {
		$banlist_ip = $action;
	}
}
$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	 
	<tr>
	<td style='width:30%' class='forumheader3'>".BANLAN_5.": </td>
	<td style='width:70%' class='forumheader3'>
	<input class='tbox' type='text' name='ban_ip' size='40' value='".$banlist_ip."' maxlength='200' />
	</td>
	</tr>
	 
	<tr>
	<td style='width:20%' class='forumheader3'>".BANLAN_7.": </td>
	<td style='width:80%' class='forumheader3'>
	<textarea class='tbox' name='ban_reason' cols='50' rows='4'>$banlist_reason</textarea>
	</td>
	</tr>
	 
	<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'>".  
($action == "edit" ? "<input type='hidden' name='old_ip' value='$banlist_ip' /><input class='button' type='submit' name='update_ban' value='".BANLAN_13."' />" : "<input class='button' type='submit' name='add_ban' value='".BANLAN_8."' />")."
	 
	</td>
	</tr>
	</table>
	</form>
	</div>";
	
$ns->tablerender(BANLAN_9, $text);
	
require_once("footer.php");
*/
?>