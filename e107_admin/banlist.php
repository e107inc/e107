<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/banlist.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("4")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;

if(e_QUERY){
	$tmp = explode("-", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}

if(IsSet($_POST['add_ban'])){
	$aj = new textparse;
	$bd = ($_POST['ban_ip'] ? $_POST['ban_ip'] : $_POST['ban_email']);
	$_POST['ban_reason'] = $aj -> formtpa($_POST['ban_reason'], "admin");
	$sql -> db_Insert("banlist", "'$bd', '".ADMINID."', '".$_POST['ban_reason']."' ");
	unset($ban_ip);
}

if($action == "remove"){
	$sql -> db_Delete("banlist", "banlist_ip='$sub_action'");
	$message = BANLAN_1;
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'><div style='border : solid 1px #000; padding : 4px; width : auto; height : 100px; overflow : auto; '>\n";
if(!$ban_total = $sql -> db_Select("banlist")){
	$text .= "<div style='text-align:center'>".BANLAN_2."</div>";
}else{
	$text .= "<table class='fborder' style='width:100%'>
	<tr>
	<td style='width:70%' class='forumheader2'>".BANLAN_10."</td>
<td style='width:30%' class='forumheader2'>".BANLAN_11."</td>
</tr>";

	while($row = $sql -> db_Fetch()){
		extract($row);
		$text .= "<td style='width:70%' class='forumheader3'>$banlist_ip</td>
		<td style='width:30%; text-align:center' class='forumheader3'>".$rs -> form_button("submit", "main_edit", BANLAN_4, "onClick=\"document.location='".e_SELF."?remove-$banlist_ip'\"")."</td>\n</tr>";
	}
	$text .= "</table>\n";
}
$text .= "</div>";
$ns -> tablerender(BANLAN_3, $text);

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>

<tr>
<td style='width:30%' class='forumheader3'>".BANLAN_5.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='ban_ip' size='40' value='$action' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".BANLAN_6.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='ban_email' size='40' value='' maxlength='200' />
</td>
</tr>

<tr> 
<td style='width:20%' class='forumheader3'>".BANLAN_7.": </td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='ban_reason' cols='50' rows='4'></textarea>
</td>
</tr>

<tr style='vertical-align:top'> 
<td colspan='2' style='text-align:center' class='forumheader'>

<input class='button' type='submit' name='add_ban' value='".BANLAN_8."' />

</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".BANLAN_9."</div>", $text);

require_once("footer.php");
?>	