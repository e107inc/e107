<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/forum_conf.php
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
if(!getperms("A")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");

$qs = explode(".", e_QUERY);
$action = $qs[0];
$forum_id = $qs[1];
$thread_id = $qs[2];
$thread_parent = $qs[3];

if($action == "close"){
	$sql -> db_Update("forum_t", "thread_active='0' WHERE thread_id='$thread_id' ");
	$message = "Thread closed.";
	$url = "../forum.php?forum.".$forum_id;
}

if($action == "open"){
	$sql -> db_Update("forum_t", "thread_active='1' WHERE thread_id='$thread_id' ");
	$message = "Thread reopened.";
	$url = "../forum.php?forum.".$forum_id;
}

if($action == "stick"){
	$sql -> db_Update("forum_t", "thread_s='1' WHERE thread_id='$thread_id' ");
	$message = "Thread made sticky.";
	$url = "../forum.php?forum.".$forum_id;
}

if($action == "unstick"){
	$sql -> db_Update("forum_t", "thread_s='0' WHERE thread_id='$thread_id' ");
	$message = "Thread unstuck.";
	$url = "../forum.php?forum.".$forum_id;
}

if(IsSet($_POST['deleteconfirm'])){
	$sql -> db_Delete("forum_t", "thread_id='$thread_id' ");
	$message = "Thread deleted.";
	$url = "../forum.php?view.".$forum_id.".".$thread_parent;
}

if(IsSet($_POST['deletecancel'])){
	$message = "Delete cancelled.";
	if($thread_parent != 0){
		$url =  "../forum.php?view.".$forum_id.".".$thread_parent;
	}else{
		$url = "../forum.php?view.".$forum_id.".".$thread_id;
	}
}

if(IsSet($_POST['move'])){
	$sql -> db_Select("forum", "*", "forum_name ='".$_POST['forum_move']."' ");
	list($forum_id_n) = $sql -> db_Fetch();
	$sql -> db_Update("forum_t", "thread_forum_id='$forum_id_n' WHERE thread_id='$thread_id' ");
	$message = "Thread moved.";
	$url = "../forum.php?forum.".$forum_id_n;
}

if(IsSet($_POST['movecancel'])){
	$message = "Move cancelled.";
	$url = "../forum.php?view.".$forum_id.".".$thread_id;
}

if($message){
	$text = "<div style=\"text-align:center\">".$message."
	<br />
	<a href=\"$url\">Back To Forums</a>
	</div>";
	$ns -> tablerender("Forum Configuration", $text);
	require_once("footer.php");
	exit;
}

if($action == "delete"){
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active) = $sql -> db_Fetch();

	$text = "<div style=\"text-align:center\">
<b>'".$thread_thread."' <br />posted by ".$thread_user."</b><br /><br />
Are you absolutely certain you want to delete this forum post? Once deleted it <b><u>cannot</u></b> be retreived.
<br /><br />
<form method=\"post\" action=\"".e_SELF."?".e_QUERY.".".$thread_parent."\">
<input class=\"button\" type=\"submit\" name=\"deletecancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"deleteconfirm\" value=\"Confirm Delete\" /> 
</form>
</div>";
$ns -> tablerender("Confirm Delete Forum Post", $text);
	
	require_once("footer.php");
	exit;
}

if($action == "move"){
$forum_total = $sql -> db_Select("forum", "*", "forum_parent!='0' ");
$text = "
<form method=\"post\" action=\"".e_SELF."?".e_QUERY.".".$thread_parent."\">
<div style=\"text-align:center\">
<table style=\"width:50%\">
<tr> 
<td style=\"width:40%\">Move thread  to forum: </td>
<td style=\"width:60%\">
<select name=\"forum_move\" class=\"tbox\">";

while(list($forum_id_, $forum_name_) = $sql-> db_Fetch()){
	if($forum_id_ != $forum_id){
		$text .= "<option>".$forum_name_."</option>";
	}
}
$text .= "</select>
</td>
</tr>

<tr style=\"vertical-align: top;\">
<td colspan=\"2\"  style=\"text-align=center\">
<input class=\"button\" type=\"submit\" name=\"move\" value=\"Move Thread\" /> 
<input class=\"button\" type=\"submit\" name=\"movecancel\" value=\"Cancel\" />

</table>
</div>
</form>";
$ns -> tablerender("Move Thread", $text);
}




























require_once("footer.php");
?>	