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
	$url = e_BASE."forum_viewforum.php?".$forum_id;
}

if($action == "open"){
	$sql -> db_Update("forum_t", "thread_active='1' WHERE thread_id='$thread_id' ");
	$message = "Thread reopened.";
	$url = e_BASE."forum_viewforum.php?".$forum_id;
}

if($action == "stick"){
	$sql -> db_Update("forum_t", "thread_s='1' WHERE thread_id='$thread_id' ");
	$message = "Thread made sticky.";
	$url = e_BASE."forum_viewforum.php?".$forum_id;
}

if($action == "unstick"){
	$sql -> db_Update("forum_t", "thread_s='0' WHERE thread_id='$thread_id' ");
	$message = "Thread unstuck.";
	$url = e_BASE."forum_viewforum.php?".$forum_id;
}

if(IsSet($_POST['deletepollconfirm'])){
	$sql -> db_Delete("poll", "poll_id='$thread_parent' ");
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	$row = $sql -> db_Fetch(); extract($row);
	$thread_name = str_replace("[poll] ", "", $thread_name);
	$sql -> db_Update("forum_t", "thread_name='$thread_name' WHERE thread_id='$thread_id' ");
	$message = "Poll deleted.";
	$url = e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_id;
}


// delete thread/replies ------------------------------------------------------------------------------------------------------------------------------------
if(IsSet($_POST['deleteconfirm'])){
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	$row = $sql -> db_Fetch(); extract($row);
	if($thread_parent){ // is post a reply?
		$sql -> db_Delete("forum_t", "thread_id='$thread_id' ");	// delete reply only
		$sql -> db_Update("forum", "forum_replies=forum_replies-1 WHERE forum_id='$thread_forum_id' ");	// dec reply count by 1
		$url = e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_parent;	// set return url
	}else{	// post is thread
		$sql -> db_Delete("poll", "poll_datestamp='$thread_id' ");	 // delete poll if there is one
		$count = $sql -> db_Delete("forum_t", "thread_parent='$thread_id' ");	// delete replies and grab how many there were
		$sql -> db_Delete("forum_t", "thread_id='$thread_id' ");	// delete the post itself
		$sql -> db_Update("forum", "forum_threads=forum_threads-1, forum_replies=forum_replies-$count WHERE forum_id='$thread_forum_id' ");	// update thread/reply counts
		$url = e_BASE."forum_viewforum.php?".$forum_id;	// set return url
	}
	$message = "Thread deleted".($count ? ", ".$count." replies deleted." : ".");
}
// end delete ----------------------------------------------------------------------------------------------------------------------------------------------


if(IsSet($_POST['deletecancel'])){
	$message = "Delete cancelled.";
	if($thread_parent != 0){
		$url =  e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_parent;
	}else{
		$url = e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_id;
	}
}

if(IsSet($_POST['move'])){

	$new_forum = $_POST['forum_move'];
	$replies = $sql -> db_Select("forum_t", "*", "thread_parent='$thread_id' ");
	
	$sql -> db_Select("forum_t", "thread_name", "thread_id ='".$thread_id."' ");
	$row = $sql -> db_Fetch(); extract($row);
	$sql -> db_Update("forum_t", "thread_forum_id='$new_forum', thread_name='[moved] ".$thread_name."' WHERE thread_id='$thread_id' ");
	$sql -> db_Update("forum_t", "thread_forum_id='$new_forum' WHERE thread_parent='$thread_id' ");
	$sql -> db_Update("forum", "forum_threads=forum_threads-1, forum_replies=forum_replies-$replies WHERE forum_id='$forum_id' ");
	$sql -> db_Update("forum", "forum_threads=forum_threads+1, forum_replies=forum_replies+$replies WHERE forum_id='$new_forum' ");

	// update lastposts

	if($sql -> db_Select("forum_t", "*", "thread_forum_id='$new_forum' ORDER BY thread_datestamp DESC LIMIT 0,1")){
		$row = $sql -> db_Fetch(); extract($row);
		$new_forum_lastpost = $thread_user.".".$thread_datestamp;
	}else{
		$new_forum_lastpost = "";
	}

	if($sql -> db_Select("forum_t", "*", "thread_forum_id='$forum_id' ORDER BY thread_datestamp DESC LIMIT 0,1")){
		$row = $sql -> db_Fetch(); extract($row);
		$new_forum_lastpost = $thread_user.".".$thread_datestamp;
	}else{
		$new_forum_lastpost = "";
	}


	$message = "Thread moved.";
	$url = e_BASE."forum_viewforum.php?".$new_forum;
}

if(IsSet($_POST['movecancel'])){
	$message = "Move cancelled.";
	$url = e_BASE."forum_viewforum.php?".$forum_id.".".$thread_id;
}

if($message){
	$text = "<div style='text-align:center'>".$message."
	<br />
	<a href='$url'>Back To Forums</a>
	</div>";
	$ns -> tablerender("Forum Configuration", $text);
	require_once("footer.php");
	exit;
}

if($action == "delete_poll"){
	$text = "<div style='text-align:center'>
	Are you absolutely certain you want to delete this poll?<br />Once deleted it <b><u>cannot</u></b> be retreived.
	<br /><br />
	<form method='post' action='".e_SELF."?".e_QUERY."'>
	<input class='button' type='submit' name='deletecancel' value='Cancel' /> 
	<input class='button' type='submit' name='deletepollconfirm' value='Confirm Delete' /> 
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
	$text = "<div style='text-align:center'>\n<b>'".$thread_thread."' <br />posted by ".$post_author_name."</b><br /><br />\nAre you absolutely certain you want to delete this forum ";
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
	<form method='post' action='".e_SELF."?".e_QUERY.".".$thread_parent."'>
	<input class='button' type='submit' name='deletecancel' value='Cancel' /> 
	<input class='button' type='submit' name='deleteconfirm' value='Confirm Delete' /> 
	</form>
	</div>";
	$ns -> tablerender("Confirm Delete Forum Post", $text);
	require_once("footer.php");
	exit;
}

if($action == "move"){
$forum_total = $sql -> db_Select("forum", "*", "forum_parent!='0' ");
$text = "
<form method='post' action='".e_SELF."?".e_QUERY.".".$thread_parent."'>
<div style='text-align:center'>
<table style='width:50%'>
<tr> 
<td style='width:40%'>Move thread  to forum: </td>
<td style='width:60%'>
<select name='forum_move' class='tbox'>";
while(list($forum_id_, $forum_name_) = $sql-> db_Fetch()){
	if($forum_id_ != $forum_id){
		$text .= "<option value='$forum_id_'>".$forum_name_."</option>";
	}
}
$text .= "</select>
</td>
</tr>
<tr style='vertical-align: top;'>
<td colspan='2'  style='text-align=center'>
<input class='button' type='submit' name='move' value='Move Thread' /> 
<input class='button' type='submit' name='movecancel' value='Cancel' />
</table>
</div>
</form>";
$ns -> tablerender("Move Thread", $text);
}

require_once("footer.php");
?>	