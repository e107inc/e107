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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/newsfeed/admin_config.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-02-28 18:41:35 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if (!getperms("E")) {
	header("location:".e_BASE."index.php");
	 exit;
}

require_once(e_ADMIN."auth.php");

if (e_QUERY) {
	list($action, $id) = explode(".", e_QUERY);
}
else
{
	$action = FALSE;
	$id = FALSE;
}

if(isset($_POST['createFeed']))
{
	if ($_POST['newsfeed_url'] && $_POST['newsfeed_name']) {
		$name = $tp -> toDB($_POST['newsfeed_name']);
		$description = $tp -> toDB($_POST['newsfeed_description']);
		$sql->db_Insert("newsfeed", "0, '$name', '".$_POST['newsfeed_url']."', '', '0', '$description', '".$_POST['newsfeed_image']."', ".$_POST['newsfeed_active'].", ".$_POST['newsfeed_updateint']." ");
		$message = NFLAN_23;
	} else {
		$message = NFLAN_24;
	}
}

if(isset($_POST['updateFeed']))
{
	$name = $tp -> toDB($_POST['newsfeed_name']);
	$description = $tp -> toDB($_POST['newsfeed_description']);
	$sql->db_Update("newsfeed", "newsfeed_name='$name', newsfeed_url='".$_POST['newsfeed_url']."', newsfeed_timestamp='0', newsfeed_image='".$_POST['newsfeed_image']."', newsfeed_description='$description', newsfeed_active=".$_POST['newsfeed_active'].", newsfeed_updateint=".$_POST['newsfeed_updateint']." WHERE newsfeed_id=".$_POST['newsfeed_id']);
	$message = NFLAN_25;
}

if($action == "delete") {
	$sql->db_Delete("newsfeed", "newsfeed_id=$id");
	$message = NFLAN_40;
}

if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}


$headline_total = $sql->db_Select("newsfeed");
$nfArray = $sql -> db_getList();

$text = "<div style='text-align:center'>
<table class='fborder' style='".ADMIN_WIDTH.";'>
<tr>
<td class='forumheader' style='width: 5%; text-align: center;'>ID</td>
<td class='forumheader' style='width: 50%;'>".NFLAN_30."</td>
<td class='forumheader' style='width: 10%; text-align: center;'>".NFLAN_26."</td>
<td class='forumheader' style='width: 25%; text-align: center;'>".NFLAN_12."</td>
<td class='forumheader' style='width: 10%; text-align: center;'>".NFLAN_27."</td>
</tr>\n";

foreach($nfArray as $newsfeed)
{
	extract($newsfeed);
	switch ($newsfeed_active)
	{
		case 0:
			$active = NFLAN_13;
		break;
		case 1:
			$active = NFLAN_14;
		break;
		case 2:
			$active = NFLAN_20;
		break;
		case 3:
			$active = NFLAN_21;
		break;
	}

	$text .= "<td class='forumheader3' style='width: 5%; text-align: center;'>$newsfeed_id</td>
	<td class='forumheader3' style='width: 50%;'><a href='$newsfeed_url' rel='external'>$newsfeed_name</a></td>
	<td class='forumheader3' style='width: 10%; text-align: center;'>".($newsfeed_updateint ? $newsfeed_updateint : "3600")."</td>
	<td class='forumheader3' style='width: 25%; text-align: center;'>$active</td>
	<td class='forumheader3' style='width: 10%; text-align: center;'><a href='".e_SELF."?edit.".$newsfeed_id."'>".NFLAN_05."</a> - <a href='".e_SELF."?delete.".$newsfeed_id."'>".NFLAN_06."</a></td>
	</tr>\n";
}

$text .= "</table>\n</div>";
$ns->tablerender(NFLAN_07, $text);

if($action == "edit")
{
	if($sql->db_Select("newsfeed", "*", "newsfeed_id=$id"))
	{
		$row = $sql->db_Fetch();
		extract($row);
	}
}
else
{
	unset($newsfeed_name, $newsfeed_url, $newsfeed_image, $newsfeed_description, $newsfeed_updateint, $newsfeed_active);
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='".ADMIN_WIDTH."' class='fborder'>

<tr>
<td style='width:50%' class='forumheader3'>".NFLAN_30."</td>
<td style='width:50%; text-align: right;' class='forumheader3'>
<input class='tbox' type='text' name='newsfeed_name' size='80' value='$newsfeed_name' maxlength='200' />
</td>
</tr>



<tr>
<td style='width:50%' class='forumheader3'>".NFLAN_10."</td>
<td style='width:50%; text-align: right;' class='forumheader3'>
<input class='tbox' type='text' name='newsfeed_url' size='80' value='$newsfeed_url' maxlength='200' />
</td>
</tr>
	 
<tr>
<td style='width:50%' class='forumheader3'>".NFLAN_11."<br /><span class='smalltext'>".NFLAN_17."</span></td>
<td style='width:50%; text-align: right;' class='forumheader3'>
<input class='tbox' type='text' name='newsfeed_image' size='80' value='$newsfeed_image' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".NFLAN_36."<br /><span class='smalltext'>".NFLAN_37."</span></td>
<td style='width:50%; text-align: right;' class='forumheader3'>
<input class='tbox' type='text' name='newsfeed_description' size='80' value='$newsfeed_description' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".NFLAN_18."<br /><span class='smalltext'>".NFLAN_19."</span></td>
<td style='width:50%; text-align: right;' class='forumheader3'>
<input class='tbox' type='text' name='newsfeed_updateint' size='5' value='".($newsfeed_updateint ? $newsfeed_updateint : "3600")."' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:50%' class='forumheader3'>".NFLAN_12."<br /><span class='smalltext'>".NFLAN_22."</span></td>
<td style='width:50%; text-align: right;' class='forumheader3'>

<input type='radio' name='newsfeed_active' value='0'".(!$newsfeed_active ? " checked='checked'" : "")." /> ".NFLAN_13."&nbsp;<br />
<input type='radio' name='newsfeed_active' value='1'".($newsfeed_active == 1 ? " checked='checked'" : "")." /> ".NFLAN_14."&nbsp;<br />
<input type='radio' name='newsfeed_active' value='2'".($newsfeed_active == 2 ? " checked='checked'" : "")." /> ".NFLAN_20."&nbsp;<br />
<input type='radio' name='newsfeed_active' value='3'".($newsfeed_active == 3 ? " checked='checked'" : "")." /> ".NFLAN_21."&nbsp;
</td>
</tr>

<tr style='vertical-align:top'>
<td colspan='2' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='".($action == "edit" ? "updateFeed" : "createFeed")."' value='".($action == "edit" ? NFLAN_16 : NFLAN_15)."' />
</td>
</tr>
	 
</table>
".($action == "edit" ? "<input type='hidden' name='newsfeed_id' value='$newsfeed_id' />" : "")."
</form>
</div>";
	
$ns->tablerender(NFLAN_09, $text);






















if (isset($_POST['add_headline'])) {
	 
	$datestamp = time();
	if ($_POST['headline_url']) {
		$sql->db_Insert("headlines", "0, '".$_POST['headline_url']."', '', '0', '', '".$_POST['headline_image']."', '".$_POST['activate']."' ");
		$message = NWFLAN_1;
		unset($headline_url, $headline_image);
	} else {
		$message = NWFLAN_20;
	}
}
	
if (isset($_POST['update_headline'])) {
	$sql->db_Update("headlines", "headline_url='".$_POST['headline_url']."', headline_timestamp='0', headline_image='".$_POST['headline_image']."', headline_active='".$_POST['activate']."' WHERE headline_id='".$_POST['headline_id']."'");
	$message = NWFLAN_2;
	unset($headline_url, $headline_image);
}
	
if (isset($_POST['delete'])) {
	if ($_POST['confirm']) {
		$sql->db_Delete("headlines", "headline_url='".$_POST['existing']."' ");
		$message = NWFLAN_3;
	} else {
		$message = NWFLAN_4;
	}
}
	
if (isset($_POST['edit'])) {
	$sql->db_Select("headlines", "*", "headline_url='".$_POST['existing']."' ");
	$row = $sql->db_Fetch();
	 extract($row);
}
	
if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}
	
	

	
require_once("footer.php");
?>