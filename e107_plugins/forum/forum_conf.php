<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
|
| $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum_conf.php,v $
| $Revision: 1.1 $
| $Date: 2005-01-25 15:14:57 $
| $Author: stevedunstan $
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("A")) { 
	header("location:".e_BASE."index.php"); 
	exit; 
}
$e_sub_cat = 'forum';
require_once(e_ADMIN.'auth.php');

$qs = explode(".", e_QUERY);
$action = $qs[0];
$forum_id = $qs[1];
$thread_id = $qs[2];
$thread_parent = $qs[3];

if(isset($_POST['deletepollconfirm'])) {
	$sql -> db_Delete("poll", "poll_id='$thread_parent' ");
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	$row = $sql -> db_Fetch(); extract($row);
	$thread_name = str_replace("[poll] ", "", $thread_name);
	$sql -> db_Update("forum_t", "thread_name='$thread_name' WHERE thread_id='$thread_id' ");
	$message = FORLAN_5;
	$url = e_PLUGIN."forum/forum_viewtopic.php?".$forum_id.".".$thread_id;
}

if(isset($_POST['move'])) {
	$new_forum = $_POST['forum_move'];
	$replies = $sql -> db_Select("forum_t", "*", "thread_parent='$thread_id' ");
	$sql -> db_Select("forum_t", "thread_name", "thread_id ='".$thread_id."' ");
	$row = $sql -> db_Fetch(); extract($row);
	$sql -> db_Update("forum_t", "thread_forum_id='$new_forum', thread_name='[".FORLAN_27."] ".$thread_name."' WHERE thread_id='$thread_id' ");
	$sql -> db_Update("forum_t", "thread_forum_id='$new_forum' WHERE thread_parent='$thread_id' ");
	$sql -> db_Update("forum", "forum_threads=forum_threads-1, forum_replies=forum_replies-$replies WHERE forum_id='$forum_id' ");
	$sql -> db_Update("forum", "forum_threads=forum_threads+1, forum_replies=forum_replies+$replies WHERE forum_id='$new_forum' ");

	// update lastposts

	if($sql -> db_Select("forum_t", "*", "thread_forum_id='$new_forum' ORDER BY thread_datestamp DESC LIMIT 0,1")) {
		$row = $sql -> db_Fetch(); extract($row);
		$new_forum_lastpost = $thread_user.".".$thread_datestamp;
	} else {
		$new_forum_lastpost = "";
	}
	$sql -> db_Update("forum", "forum_lastpost='{$new_forum_lastpost}' WHERE forum_id='$new_forum' ");

	if($sql -> db_Select("forum_t", "*", "thread_forum_id='$forum_id' ORDER BY thread_datestamp DESC LIMIT 0,1")) {
		$row = $sql -> db_Fetch();
		extract($row);
		$new_forum_lastpost = $thread_user.".".$thread_datestamp;
	} else {
		$new_forum_lastpost = "";
	}
	$sql -> db_Update("forum", "forum_lastpost='{$new_forum_lastpost}' WHERE forum_id='$forum_id' ");

	$message = FORLAN_9;
	$url = e_PLUGIN."forum/forum_viewforum.php?".$new_forum;
}

if(IsSet($_POST['movecancel'])) {
	$message = FORLAN_10;
	$url = e_PLUGIN."forum/forum_viewforum.php?".$thread_id;
}

if($message) {
	$text = "<div style='text-align:center'>".$message."
	<br />
	<a href='$url'>".FORLAN_11."</a>
	</div>";
	$ns -> tablerender(FORLAN_12, $text);
	require_once("footer.php");
	exit;
}

if($action == "delete_poll") {
	$text = "<div style='text-align:center'>
	".FORLAN_13."
	<br /><br />
	<form method='post' action='".e_SELF."?".e_QUERY."'>
	<input class='button' type='submit' name='deletecancel' value='".FORLAN_14."' />
	<input class='button' type='submit' name='deletepollconfirm' value='".FORLAN_15."' />
	</form>
	</div>";
	$ns -> tablerender(FORLAN_16, $text);
	require_once("footer.php");
	exit;
}

if($action == "move") {
	$forum_total = $sql -> db_Select("forum", "forum_id,forum_name", "forum_parent!='0' ");
	$text = "
	<form method='post' action='".e_SELF."?".e_QUERY.".".$thread_parent."'>
	<div style='text-align:center'>
	<table style='".ADMIN_WIDTH."'>
	<tr>
	<td style='text-align:right'>".FORLAN_24.": </td>
	<td style='text-align:left'>
	<select name='forum_move' class='tbox'>";
	while(list($forum_id_, $forum_name_) = $sql-> db_Fetch()) {
		if($forum_id_ != $forum_id) {
			$text .= "<option value='$forum_id_'>".$forum_name_."</option>";
		}
	}
	$text .= "</select>
	</td>
	</tr>
	<tr style='vertical-align: top;'>
	<td colspan='2'  style='text-align:center'><br />
	<input class='button' type='submit' name='move' value='".FORLAN_25."' />
	<input class='button' type='submit' name='movecancel' value='".FORLAN_14."' />
	</td>
	</tr>
	</table>
	</div>
	</form>";
	$ns -> tablerender(FORLAN_25, $text);
}
require_once(e_ADMIN.'footer.php');
?>