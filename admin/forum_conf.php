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
if(!getperms("A")){ header("location:".e_HTTP."index.php"); exit; }
require_once("auth.php");

$qs = explode(".", e_QUERY);
$action = $qs[0];
$forum_id = $qs[1];
$thread_id = $qs[2];
$thread_parent = $qs[3];

if($action == "close"){
	$sql -> db_Update("forum_t", "thread_active='0' WHERE thread_id='$thread_id' ");
	$message = "Thread closed.";
	$url = "../forum_viewforum.php?".$forum_id;
}

if($action == "open"){
	$sql -> db_Update("forum_t", "thread_active='1' WHERE thread_id='$thread_id' ");
	$message = "Thread reopened.";
	$url = "../forum_viewforum.php?".$forum_id;
}

if($action == "stick"){
	$sql -> db_Update("forum_t", "thread_s='1' WHERE thread_id='$thread_id' ");
	$message = "Thread made sticky.";
	$url = "../forum_viewforum.php?".$forum_id;
}

if($action == "unstick"){
	$sql -> db_Update("forum_t", "thread_s='0' WHERE thread_id='$thread_id' ");
	$message = "Thread unstuck.";
	$url = "../forum_viewforum.php?".$forum_id;
}

if(IsSet($_POST['deletepollconfirm'])){
	$sql -> db_Delete("poll", "poll_id='$thread_parent' ");

	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	$row = $sql -> db_Fetch(); extract($row);
	$thread_name = str_replace("[poll] ", "", $thread_name);
	$sql -> db_Update("forum_t", "thread_name='$thread_name' WHERE thread_id='$thread_id' ");
	$message = "Poll deleted.";
	$url = "../forum_viewtopic.php?".$forum_id.".".$thread_id;
}

if(IsSet($_POST['deleteconfirm'])){
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	$row = $sql -> db_Fetch(); extract($row);
	$url = (!$thread_parent ? "../forum_viewforum.php?".$forum_id : "../forum_viewtopic.php?".$forum_id.".".$thread_parent);
	$sql -> db_Delete("forum_t", "thread_parent='$thread_id' ");
	$sql -> db_Delete("poll", "poll_datestamp='$thread_id' ");
	$sql -> db_Delete("forum_t", "thread_id='$thread_id' ");
	$message = "Thread deleted.";
}

if(IsSet($_POST['deletecancel'])){
	$message = "Delete cancelled.";
	if($thread_parent != 0){
		$url =  "../forum_viewtopic.php?".$forum_id.".".$thread_parent;
	}else{
		$url = "../forum_viewtopic.php?".$forum_id.".".$thread_id;
	}
}

if(IsSet($_POST['move'])){
	$sql -> db_Select("forum", "forum_id", "forum_name ='".$_POST['forum_move']."' ");
	list($forum_id_n) = $sql -> db_Fetch();
	$sql -> db_Select("forum_t", "thread_name", "thread_id ='".$thread_id."' ");
	list($thread_name_m) = $sql -> db_Fetch();
	$thread_name_m = "[moved] ".$thread_name_m;
	$sql -> db_Update("forum_t", "thread_forum_id='$forum_id_n', thread_name='".$thread_name_m."' WHERE thread_id='$thread_id' ");
	$message = "Thread moved.";
	$url = "../forum_viewforum.php?".$forum_id_n;
}

if(IsSet($_POST['movecancel'])){
	$message = "Move cancelled.";
	$url = "../forum_viewforum.php?".$forum_id.".".$thread_id;
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

if($action == "delete_poll"){
	$text = "<div style='text-align:center'>
	Are you absolutely certain you want to delete this poll?<br />Once deleted it <b><u>cannot</u></b> be retreived.
	<br /><br />
	<form method=\"post\" action=\"".e_SELF."?".e_QUERY."\">
	<input class=\"button\" type=\"submit\" name=\"deletecancel\" value=\"Cancel\" /> 
	<input class=\"button\" type=\"submit\" name=\"deletepollconfirm\" value=\"Confirm Delete\" /> 
	</form>
	</div>";
	$ns -> tablerender("Confirm Delete Poll", $text);
	require_once("footer.php");
	exit;
}

if($action == "delete"){
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	$row = $sql -> db_Fetch(); extract($row);
	if(!$thread_parent){
		$sql -> db_Select("forum_t", "*", "thread_parent='".$thread_id."' ");
	}

	$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));

	$text = "<div style=\"text-align:center\">\n<b>'".$thread_thread."' <br />posted by ".$post_author_name."</b><br /><br />\nAre you absolutely certain you want to delete this forum ";

	if(!$thread_parent){
		$text .= "thread and it's related posts?";
		if(eregi("\[poll\]", $thread_name)){
			$text .= " (the poll will also be deleted).";
		}
		$text .= "<br />Once deleted they";
	}else{
		$text .= "post?<br />Once deleted it";
	}

	$text .= " <b><u>cannot</u></b> be retreived.
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





// forum tidy, added by Edwin (evdwal@xs4all.nl) -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-

$sql2 = new db;
$sql -> db_Select("forum", "*", "forum_parent = 0");
$forums = $sql -> db_Select("forum", "*", "forum_parent != 0");
while($row = $sql-> db_Fetch()){
	extract($row);
	$no_topics = $sql2 -> db_Count("forum_t", "(*)", "WHERE thread_forum_id=$forum_id and thread_parent=0");
	$no_replies = $sql2 -> db_Count("forum_t", "(*)", "WHERE thread_forum_id=$forum_id and thread_parent!=0");
	$sql2 -> db_Select("forum_t", "thread_user,thread_datestamp", "thread_forum_id = $forum_id order by thread_datestamp DESC LIMIT 0,1");
	list($thread_user,$thread_lastpost) = $sql2 -> db_Fetch();
	$new_lastpost = $thread_user.".".$thread_lastpost;
	if (($forum_threads != $no_topics) || ($forum_replies != $no_replies) ||( $forum_lastpost != $new_lastpost)) {
		$sql2 -> db_Update("forum", "forum_threads = $no_topics, forum_replies = $no_replies, forum_lastpost = '$new_lastpost' where forum_id = $forum_id");
	} 
}

// end forum tidy  -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-























require_once("footer.php");
?>	