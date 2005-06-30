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
|     $Source: /cvs_backup/e107_0.7/e107_admin/modcomment.php,v $
|     $Revision: 1.11 $
|     $Date: 2005-06-30 14:18:21 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("B")) {
	header("location:".e_BASE."index.php");
	exit;
}
require_once("auth.php");
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;
global $tp;

$tmp	= explode(".", e_QUERY);
$table	= $tmp[0];
$id		= $tmp[1];
$type	= $cobj -> getCommentType($table);

if (isset($_POST['moderate'])) {
	if (isset($_POST['comment_lock']) && $_POST['comment_lock'] == "1" && $_POST['comment_lock'] != $_POST['current_lock']) {
		$sql->db_Update("comments", "comment_lock='1' WHERE comment_item_id='$id' ");
	}
	if ((!isset($_POST['comment_lock']) || $_POST['comment_lock'] == "0") && $_POST['comment_lock'] != $_POST['current_lock']) {
		$sql->db_Update("comments", "comment_lock='0' WHERE comment_item_id='$id' ");
	}
	if (is_array($_POST['comment_blocked'])) {
		while (list ($key, $cid) = each ($_POST['comment_blocked'])) {
			$sql->db_Update("comments", "comment_blocked='1' WHERE comment_id='$cid' ");
		}
	}
	if (is_array($_POST['comment_unblocked'])) {
		while (list ($key, $cid) = each ($_POST['comment_unblocked'])) {
			$sql->db_Update("comments", "comment_blocked='0' WHERE comment_id='$cid' ");
		}
	}
	if (is_array($_POST['comment_delete'])) {
		while (list ($key, $cid) = each ($_POST['comment_delete'])) {
			if ($sql->db_Select("comments", "*", "comment_id='$cid' ")) {
				$row = $sql->db_Fetch();
				delete_children($row, $cid);
			}
		}
	}
	$e107cache->clear("comment");
	$e107cache->clear("news");
	$e107cache->clear($table);
	$message = MDCLAN_1;
}

if (isset($message)) {
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "
<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."'>
<table class='fborder' style='".ADMIN_WIDTH."'>";

if (!$sql->db_Select("comments", "*", "(comment_type='".$type."' OR comment_type='".$table."') AND comment_item_id=$id")) {
	$text .= "<tr><td class='forumheader3' style='text-align:center'>".MDCLAN_2.".</td></tr></table></form></div>";
} else {
	$con = new convert;

	$commentArray = $sql -> db_getList();
	$total_comments = count($commentArray);

	$comments = "";
	foreach($commentArray as $row)
	{
		$comment_lock = $row['comment_lock'];
		$total_blocked += $row['comment_blocked'];
		$datestamp = $con->convert_date($row['comment_datestamp'], "short");
		$comment_author_id = substr($row['comment_author'], 0, strpos($row['comment_author'], "."));
		if ($comment_author_id) {
			$sql->db_Select("user", "*", "user_id='$comment_author_id' ");
			$rowu = $sql->db_Fetch();
			$comment_nick = "<a href='".e_BASE."user.php?id.".$rowu['user_id']."'>".$rowu['user_name']."</a>";
			$comment_str = MDCLAN_3." #".$rowu['user_id'];
		} else {
			$comment_str = MDCLAN_4;
			$comment_nick = eregi_replace("[0-9]+\.", "", $row['comment_author']);
		}
		$row['comment_comment'] = $tp->toHTML($row['comment_comment'], TRUE, "");

		$comments .= "
		<tr>
			<td class='forumheader3' style='width:5%; text-align:center;'>".($row['comment_blocked'] ? "<img src='".e_IMAGE."admin_images/blocked.png' />" : "&nbsp;")."</td>
			<td class='forumheader3' style='width:15%;'>".$datestamp."</td>
			<td class='forumheader3' style='width:15%;'><b>".$comment_nick."</b><br />".$comment_str."</td>
			<td class='forumheader3' style='width:40%;'>".$row['comment_comment']."</td>
			<td class='forumheader3' style='width:25%;'>".($row['comment_blocked'] ? "<input type='checkbox' name='comment_unblocked[]' value='".$row['comment_id']."' /> ".MDCLAN_5."" : "<input type='checkbox' name='comment_blocked[]' value='".$row['comment_id']."' /> ".MDCLAN_6."")."&nbsp;<input type='checkbox' name='comment_delete[]' value='".$row['comment_id']."' /> ".LAN_DELETE."</td>
		</tr>";
	}
	$text .= "
	<tr><td colspan='5' class='fcaption'>".MDCLAN_10."</td></tr>
	<tr>
	<td class='forumheader3' style='text-align:right' colspan='4'>".MDCLAN_14.":</td>
	<td style='width:25%;' class='forumheader3'>
	<input type='radio' name='comment_lock' value='0' ".(!$comment_lock ? " checked='checked'" : "")." /> ".MDCLAN_15." 
	<input type='radio' name='comment_lock' value='1' ".($comment_lock ? " checked='checked'" : "")." /> ".MDCLAN_16."
	<input type='hidden' name='current_lock' value='".$comment_lock."' />
	</td>
	</tr>
	<tr><td colspan='5' class='fcaption'>comments (".$total_comments." ".($total_comments == "1" ? MDCLAN_11 : MDCLAN_12).", ".$total_blocked." ".MDCLAN_13.")</td></tr>
	".$comments."
	<tr><td colspan='5' class='forumheader' style='text-align:center'>".MDCLAN_9."</td></tr>
	<tr><td colspan='5' class='forumheader' style='text-align:center'><input class='button' type='submit' name='moderate' value='".MDCLAN_8."' /></td></tr>
	";
}
$text .= "</table></form></div>";

$ns->tablerender(MDCLAN_8, $text);

require_once("footer.php");

function delete_children($row, $cid) {
	global $sql, $sql2, $table;

	$tmp = explode(".", $row['comment_author']);
	$u_id = $tmp[0];
	if ($u_id >= 1) {
		$sql->db_Update("user", "user_comments=user_comments-1 WHERE user_id='$u_id'");
	}
	if($table == "news"){
		$sql->db_Update("news", "news_comment_total=news_comment_total-1 WHERE news_id='".$row['comment_item_id']."'");
	}
	if ($sql2->db_Select("comments", "*", "comment_pid='".$row['comment_id']."'")) {
		while ($row2 = $sql2->db_Fetch()) {
			delete_children($row2, $row2['comment_id']);
		}
	}
	$c_del[] = $cid;
	while (list ($key, $cid) = each ($c_del)) {
		$sql->db_Delete("comments", "comment_id='$cid'");
	}
}

?>