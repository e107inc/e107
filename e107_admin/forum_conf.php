<?php
/*
+---------------------------------------------------------------+
| e107 website system
| /admin/forum_conf.php
|
| ©Steve Dunstan 2001-2002
| http://e107.org
| jalist@e107.org
|
| Released under the terms and conditions of the
| GNU General Public License (http://gnu.org).
|
| $Source: /cvs_backup/e107_0.7/e107_admin/forum_conf.php,v $
| $Revision: 1.5 $
| $Date: 2005-01-27 19:52:24 $
| $Author: streaky $
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("A")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'forum';
require_once("auth.php");
	
$qs = explode(".", e_QUERY);
$action = $qs[0];
$forum_id = $qs[1];
$thread_id = $qs[2];
$thread_parent = $qs[3];
	
if ($action == "close") {
	$sql->db_Update("forum_t", "thread_active='0' WHERE thread_id='$thread_id' ");
	$message = FORLAN_1;
	$url = e_BASE."forum_viewforum.php?".$forum_id;
}
	
if ($action == "open") {
	$sql->db_Update("forum_t", "thread_active='1' WHERE thread_id='$thread_id' ");
	$message = FORLAN_2;
	$url = e_BASE."forum_viewforum.php?".$forum_id;
}
	
if ($action == "stick") {
	$sql->db_Update("forum_t", "thread_s='1' WHERE thread_id='$thread_id' ");
	$message = FORLAN_3;
	$url = e_BASE."forum_viewforum.php?".$forum_id;
}
	
if ($action == "unstick") {
	$sql->db_Update("forum_t", "thread_s='0' WHERE thread_id='$thread_id' ");
	$message = FORLAN_4;
	$url = e_BASE."forum_viewforum.php?".$forum_id;
}
	
if (isset($_POST['deletepollconfirm'])) {
	$sql->db_Delete("poll", "poll_id='$thread_parent' ");
	$sql->db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	$row = $sql->db_Fetch();
	 extract($row);
	$thread_name = str_replace("[poll] ", "", $thread_name);
	$sql->db_Update("forum_t", "thread_name='$thread_name' WHERE thread_id='$thread_id' ");
	$message = FORLAN_5;
	$url = e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_id;
}
	
	
// delete thread/replies ------------------------------------------------------------------------------------------------------------------------------------
if ($action == "confirm") {
	$sql->db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	$row = $sql->db_Fetch();
	 extract($row);
	if ($thread_parent) {
		// is post a reply?
		$sql->db_Delete("forum_t", "thread_id='$thread_id' "); // delete reply only
		$sql->db_Update("forum", "forum_replies=forum_replies-1 WHERE forum_id='$thread_forum_id' "); // dec reply count by 1
		 
		$sql->db_Select("forum_t", "*", "thread_id=$thread_id");
		$row = $sql->db_Fetch();
		 extract($row);
		$replies = $sql->db_Count("forum_t", "(*)", "WHERE thread_parent='".$thread_parent."'");
		$pref['forum_postspage'] = ($pref['forum_postspage'] ? $pref['forum_postspage'] : 10);
		 
		$pages = 0;
		if ($replies) {
			$pages = ((ceil($replies/$pref['forum_postspage']) -1) * $pref['forum_postspage']);
		}
		$url = e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_parent.($pages ? ".$pages" : ""); // set return url
		$message = FORLAN_26;
	} else {
		// post is thread
		$sql->db_Delete("poll", "poll_datestamp='$thread_id' ");
		// delete poll if there is one
		$count = $sql->db_Delete("forum_t", "thread_parent='$thread_id' "); // delete replies and grab how many there were
		$sql->db_Delete("forum_t", "thread_id='$thread_id' "); // delete the post itself
		$sql->db_Update("forum", "forum_threads=forum_threads-1, forum_replies=forum_replies-$count WHERE forum_id='$thread_forum_id' "); // update thread/reply counts
		$url = e_BASE."forum_viewforum.php?".$forum_id; // set return url
		$message = FORLAN_6.($count ? ", ".$count." ".FORLAN_7."." : ".");
	}
	 
	if ($sql->db_Select("forum_t", "*", "thread_forum_id='$forum_id' ORDER BY thread_datestamp DESC LIMIT 0,1")) {
		$row = $sql->db_Fetch();
		 extract($row);
		$new_forum_lastpost = $thread_user.".".$thread_datestamp;
	} else {
		$new_forum_lastpost = "";
	}
	$sql->db_Update("forum", "forum_lastpost='{$new_forum_lastpost}' WHERE forum_id='$new_forum' ");
}
// end delete ----------------------------------------------------------------------------------------------------------------------------------------------
	
if (isset($_POST['move'])) {
	 
	$new_forum = $_POST['forum_move'];
	 
	$replies = $sql->db_Select("forum_t", "*", "thread_parent='$thread_id' ");
	 
	$sql->db_Select("forum_t", "thread_name", "thread_id ='".$thread_id."' ");
	$row = $sql->db_Fetch();
	 extract($row);
	$sql->db_Update("forum_t", "thread_forum_id='$new_forum', thread_name='[".FORLAN_27."] ".$thread_name."' WHERE thread_id='$thread_id' ");
	$sql->db_Update("forum_t", "thread_forum_id='$new_forum' WHERE thread_parent='$thread_id' ");
	$sql->db_Update("forum", "forum_threads=forum_threads-1, forum_replies=forum_replies-$replies WHERE forum_id='$forum_id' ");
	$sql->db_Update("forum", "forum_threads=forum_threads+1, forum_replies=forum_replies+$replies WHERE forum_id='$new_forum' ");
	 
	// update lastposts
	 
	if ($sql->db_Select("forum_t", "*", "thread_forum_id='$new_forum' ORDER BY thread_datestamp DESC LIMIT 0,1")) {
		$row = $sql->db_Fetch();
		 extract($row);
		$new_forum_lastpost = $thread_user.".".$thread_datestamp;
	} else {
		$new_forum_lastpost = "";
	}
	$sql->db_Update("forum", "forum_lastpost='{$new_forum_lastpost}' WHERE forum_id='$new_forum' ");
	 
	if ($sql->db_Select("forum_t", "*", "thread_forum_id='$forum_id' ORDER BY thread_datestamp DESC LIMIT 0,1")) {
		$row = $sql->db_Fetch();
		 extract($row);
		$new_forum_lastpost = $thread_user.".".$thread_datestamp;
	} else {
		$new_forum_lastpost = "";
	}
	$sql->db_Update("forum", "forum_lastpost='{$new_forum_lastpost}' WHERE forum_id='$forum_id' ");
	 
	$message = FORLAN_9;
	$url = e_BASE."forum_viewforum.php?".$new_forum;
}
	
if (isset($_POST['movecancel'])) {
	$message = FORLAN_10;
	$url = e_BASE."forum_viewforum.php?".$forum_id.".".$thread_id;
}
	
if ($message) {
	$text = "<div style='text-align:center'>".$message."
		<br />
		<a href='$url'>".FORLAN_11."</a>
		</div>";
	$ns->tablerender(FORLAN_12, $text);
	require_once("footer.php");
	exit;
}
	
if ($action == "delete_poll") {
	$text = "<div style='text-align:center'>
		".FORLAN_13."
		<br /><br />
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<input class='button' type='submit' name='deletecancel' value='".FORLAN_14."' />
		<input class='button' type='submit' name='deletepollconfirm' value='".FORLAN_15."' />
		</form>
		</div>";
	$ns->tablerender(FORLAN_16, $text);
	require_once("footer.php");
	exit;
}
	
if ($action == "delete") {
	$sql->db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	$row = $sql->db_Fetch();
	 extract($row);
	if (!$thread_parent) {
		$sql->db_Select("forum_t", "*", "thread_parent='".$thread_id."' ");
	}
	$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));
	$text = "<div style='text-align:center'>\n<b>'".$thread_thread."' <br />".FORLAN_17." ".$post_author_name."</b><br /><br />\n".FORLAN_18." ";
	if (!$thread_parent) {
		$text .= FORLAN_19;
		if (eregi("\[poll\]", $thread_name)) {
			$text .= " (".FORLAN_20.").";
		}
		$text .= "<br />".FORLAN_21;
	} else {
		$text .= FORLAN_22;
	}
	$text .= " <b><u>".FORLAN_23."
		<br /><br />
		<form method='post' action='".e_SELF."?".e_QUERY.".".$thread_parent."'>
		<input class='button' type='submit' name='deletecancel' value='".FORLAN_14."' />
		<input class='button' type='submit' name='deleteconfirm' value='".FORLAN_15."' />
		</form>
		</div>";
	$ns->tablerender(FORLAN_15, $text);
	require_once("footer.php");
	exit;
}
	
if ($action == "move") {
	$forum_total = $sql->db_Select("forum", "forum_id,forum_name", "forum_parent!='0' ");
	$text = "
		<form method='post' action='".e_SELF."?".e_QUERY.".".$thread_parent."'>
		<div style='text-align:center'>
		<table style='".ADMIN_WIDTH."'>
		<tr>
		<td style='text-align:right'>".FORLAN_24.": </td>
		<td style='text-align:left'>
		<select name='forum_move' class='tbox'>";
	while (list($forum_id_, $forum_name_) = $sql->db_Fetch()) {
		if ($forum_id_ != $forum_id) {
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
	$ns->tablerender(FORLAN_25, $text);
}
	
require_once("footer.php");
?>