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

if(e_QUERY){ $ban_ip = e_QUERY; }

if(IsSet($_POST['add_ban'])){
	$aj = new textparse;
	$bd = ($_POST['ban_ip'] ? $_POST['ban_ip'] : $_POST['ban_email']);
	$_POST['ban_reason'] = $aj -> formtpa($_POST['ban_reason'], "admin");
	$sql -> db_Insert("banlist", "'$bd', '".ADMINID."', '".$_POST['ban_reason']."' ");
	unset($ban_ip);
}

if(IsSet($_POST['delete'])){
	$sql -> db_Delete("banlist", "banlist_ip='".$_POST['existing']."' ");
	$message = BANLAN_1;
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$ban_total = $sql -> db_Select("banlist");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td colspan='2' style='text-align:center' class='forumheader'>";

if(!$ban_total){
	$text .= "<span style='defaulttext'>".BANLAN_2."</span>";
}else{
	$text .= "<span style='defaulttext'>".BANLAN_3.":</span> 
	<select name='existing' class='tbox'>";
	while(list($ban_ip_) = $sql-> db_Fetch()){
		$text .= "<option>".$ban_ip_."</option>";
	}
	$text .= "</select> 
	<input class='button' type='submit' name='delete' value='".BANLAN_4."' />";
}

$text .= "
</td>
</tr>
<tr>
<td style='width:30%' class='forumheader3'>".BANLAN_5.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='ban_ip' size='40' value='' maxlength='200' />
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