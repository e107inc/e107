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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/linkwords/admin_config.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-03-23 15:03:16 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!getperms("P")) {
	header("location:".e_BASE."index.php");
	 exit ;
}
require_once(e_ADMIN."auth.php");
@include_once(e_PLUGIN."linkwords/languages/".e_LANGUAGE.".php");
@include_once(e_PLUGIN."linkwords/languages/English.php");

$deltest = array_flip($_POST);

if(isset($deltest[LWLAN_17]))
{
	$delete_id = str_replace("delete_", "", $deltest[LWLAN_17]);

	if ($sql->db_Count("linkwords", "(*)", "WHERE linkword_id = ".$delete_id))
	{
		$sql->db_Delete("linkwords", "linkword_id=".$delete_id);
		$message = LWLAN_19;
	}
}

if(e_QUERY)
{
	list($action, $id) = explode(".", e_QUERY);
}

if (isset($_POST['submit_linkword']))
{
	if(!$_POST['linkwords_word'] && $_POST['linkwords_url'])
	{
		$message = LWLAN_1;
	}
	else
	{
		$word = $tp -> toDB($_POST['linkword_word']);
		$link = $tp -> toDB($_POST['linkword_link']);
		$active = $_POST['linkword_active'];
		$sql -> db_Insert("linkwords", "0, $active, '$word', '$link' ");
		$message = LWLAN_2;
	}
}

if (isset($_POST['update_linkword']))
{
	if(!$_POST['linkwords_word'] && $_POST['linkwords_url'])
	{
		$message = LWLAN_1;
	}
	else
	{
		$id = $_POST['id'];
		$word = $tp -> toDB($_POST['linkword_word']);
		$link = $tp -> toDB($_POST['linkword_link']);
		$active = $_POST['linkword_active'];
		$sql -> db_Update("linkwords", "linkword_active=$active, linkword_word='$word', linkword_link='$link' WHERE linkword_id=".$id);
		$message = LWLAN_3;
	}
}


if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}



$text = "<div class='center'>\n";
if(!$sql -> db_Select("linkwords"))
{
	$text .= LWLAN_4;
}
else
{
	$text = "
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td class='forumheader' style='width: 20%;'>".LWLAN_5."</td>
	<td class='forumheader' style='width: 40%;'>".LWLAN_6."</td>
	<td class='forumheader' style='width: 10%; text-align: center;'>".LWLAN_7."</td>
	<td class='forumheader' style='width: 30%; text-align: center;'>".LWLAN_8."</td>
	</tr>\n";

	while($row = $sql -> db_Fetch())
	{
		extract($row);
		$text .= "
		
		<form action='".e_SELF."' method='post' id='myform_$linkword_id'  onsubmit=\"return jsconfirm('".LWLAN_18." [ID: $linkword_id ]')\">
		<tr>
		<td class='forumheader' style='width: 20%;'>$linkword_word</td>
		<td class='forumheader' style='width: 40%;'>$linkword_link</td>
		<td class='forumheader' style='width: 10%; text-align: center;'>".(!$linkword_active ? LWLAN_12 : LWLAN_13)."</td>
		<td class='forumheader' style='width: 30%; text-align: center;'>
		<input class='button' type='button' onclick=\"document.location='".e_SELF."?edit.$linkword_id'\" value='Edit' id='edit_$linkword_id' name='edit_linkword_id' />
		<input class='button' type='submit'  value='Delete' id='delete_$linkword_id' name='delete_$linkword_id' />
		</td>
		</tr>
		</form>\n";
	}
	$text .= "</table>";
}

$text .= "</div>";
$ns -> tablerender(LWLAN_11, $text);


if($action == "edit")
{

	if($sql -> db_Select("linkwords", "*", "linkword_id=".$id))
	{
		$row = $sql -> db_Fetch();
		extract($row);
		define("EDIT", TRUE);
	}
}
else
{
	unset($linkword_word, $linkword_link, $linkword_active);
}






$text = "
<div class='center'>
<form method='post' action='".e_SELF."'>
<table style='".ADMIN_WIDTH."' class='fborder'>

<tr>
<td style='width:50%' class='forumheader3'>Word to autolink</td>
<td style='width:50%' class='forumheader3'>
<input class='tbox' type='text' name='linkword_word' size='40' value='".$linkword_word."' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>Link</td>
<td style='width:50%' class='forumheader3'>
<input class='tbox' type='text' name='linkword_link' size='40' value='".$linkword_link."' maxlength='150' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>Activate?</td>
<td style='width:50%; text-align:right' class='forumheader3'>
<input type='radio' name='linkword_active' value='0'".(!$linkword_active ? " checked='checked'" : "")." /> ".LWLAN_9."&nbsp;&nbsp;
<input type='radio' name='linkword_active' value='1'".($linkword_active ? " checked='checked'" : "")." /> ".LWLAN_10."
</td>
</tr>

<tr>
<td colspan='2' style='text-align:center' class='forumheader'>".
(defined("EDIT") ? "<input class='button' type='submit' name='update_linkword' value='".LWLAN_15."' /><input type='hidden' name='id' value='$id' />" : "<input class='button' type='submit' name='submit_linkword' value='".LWLAN_14."' />")."
</td>
</tr>
</table>
</form>
</div>\n";

$ns -> tablerender($caption, $text);

	
require_once(e_ADMIN."footer.php");
?>