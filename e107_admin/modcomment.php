<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * Exists only for BC. 
 */

require_once("../class2.php");

if (!getperms("B")) 
{
	e107::redirect('admin');
	exit;
}


$tmp	= explode(".", e_QUERY);
$table	= $tmp[0];
$id		= intval($tmp[1]);
$editid	= intval($tmp[2]);

$url = e_ADMIN_ABS."comment.php?searchquery=".$id."&filter_options=comment_type__".e107::getComment()->getCommentType($table);

e107::getRedirect()->go($url);
exit;


/*

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

require_once("auth.php");
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;
$tp = e107::getParser();

$tmp	= explode(".", e_QUERY);
$table	= $tmp[0];
$id		= intval($tmp[1]);
$editid	= intval($tmp[2]);
$type	= $cobj -> getCommentType($table);

if (isset($_POST['moderate'])) 
{
	if (isset($_POST['comment_comment'])) 
	{
		$sql->db_Update('comments', "comment_comment='".$tp -> todb($_POST['comment_comment'])."' WHERE comment_id=".$editid);
		header("location: ".e_ADMIN."modcomment.php?{$table}.{$id}"); 
		exit;
	}

	if (isset($_POST['comment_lock']) && $_POST['comment_lock'] == "1" && $_POST['comment_lock'] != $_POST['current_lock']) 
	{
		$sql->db_Update('comments', "comment_lock='1' WHERE `comment_item_id`=".$id." AND `comment_type`='".$tp -> toDB($type, true)."' ");
	}

	if ((!isset($_POST['comment_lock']) || $_POST['comment_lock'] == "0") && $_POST['comment_lock'] != $_POST['current_lock']) 
	{
		$sql->db_Update('comments', "comment_lock='0' WHERE `comment_item_id`=".$id." AND `comment_type`='".$tp -> toDB($type, true)."' ");
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
	if (is_array($_POST['comment_delete'])) 
	{
		while (list ($key, $cid) = each ($_POST['comment_delete'])) 
		{
			if ($sql->db_Select('comments', "*", "comment_id='$cid' ")) 
			{
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
<form method='post' action='".e_SELF."?".e_QUERY."'>
<table class='table adminform'>";

if ($editid)
{
	if (!$sql->db_Select("comments", "*", "comment_id=$editid")) {
		$text .= "<tr><td class='forumheader3' style='text-align:center'>".MDCLAN_2.".</td></tr>";
	}
	else
	{
		$row = $sql->db_Fetch();
		$text .= "<tr><td><textarea class='tbox' name='comment_comment' cols='1' rows='15' style='width:100%;'>".$row['comment_comment']."</textarea></td></tr>";
		$text .= "<tr><td colspan='5' class='forumheader' style='text-align:center'><input class='btn button' type='submit' name='moderate' value='".MDCLAN_8."' /></td></tr>";
		$text .= "<div><input type='hidden' name='e-token' value='".e_TOKEN."' /></div>";		
	}

	$text .= "</table></form></div>";
	$ns->tablerender(MDCLAN_8, $text);
	require_once("footer.php"); exit;
}

if (!$sql->db_Select("comments", "*", "(comment_type='".$type."' OR comment_type='".$table."') AND comment_item_id={$id} ORDER BY `comment_datestamp` ")) 
{
	$text .= "<tr><td class='forumheader3' style='text-align:center'>".MDCLAN_2.".</td></tr>";
} 
else 
{
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
			$comment_nick = preg_replace("#[0-9]+\.#", "", $row['comment_author']);
		}
		$row['comment_comment'] = $tp->toHTML($row['comment_comment'], TRUE, "");

		$comments .= "
		<tr>
			<td class='forumheader3' style='width:5%; text-align:center;'>".($row['comment_blocked'] ? "<img src='".e_IMAGE."admin_images/blocked.png' />" : "&nbsp;")."</td>
			<td class='forumheader3' style='width:15%;'>".$datestamp."</td>
			<td class='forumheader3' style='width:15%;'><b>".$comment_nick."</b><br />".$comment_str."</td>
			<td class='forumheader3' style='width:40%;'>".$row['comment_comment']."</td>
			<td class='forumheader3' style='width:25%;'>
				<a href='".e_ADMIN."modcomment.php?{$table}.{$id}.".$row['comment_id']."'><img src='".e_IMAGE."admin_images/edit_16.png' alt='".LAN_EDIT."' title='".LAN_EDIT."' class='icon S16' /></a>"
				."&nbsp;".($row['comment_blocked'] ? "<input type='checkbox' name='comment_unblocked[]' value='".$row['comment_id']."' /> ".MDCLAN_5."" : "<input type='checkbox' name='comment_blocked[]' value='".$row['comment_id']."' /> ".MDCLAN_6."")
				."&nbsp;<input type='checkbox' name='comment_delete[]' value='".$row['comment_id']."' /> ".LAN_DELETE."
			</td>
		</tr>";
	}
	$text .= "
	<tr><td colspan='5' class='fcaption'>".LAN_OPTIONS."</td></tr>
	<tr>
	<td class='forumheader3' style='text-align:right' colspan='4'>".MDCLAN_14.":</td>
	<td style='width:25%;' class='forumheader3'>
	<input type='radio' name='comment_lock' value='0' ".(!$comment_lock ? " checked='checked'" : "")." /> ".MDCLAN_15." 
	<input type='radio' name='comment_lock' value='1' ".($comment_lock ? " checked='checked'" : "")." /> ".MDCLAN_16."
	<input type='hidden' name='current_lock' value='".$comment_lock."' />
	</td>
	</tr>
	<tr><td colspan='5' class='fcaption'>".MDCLAN_12." (".$total_comments." ".($total_comments == "1" ? MDCLAN_11 : MDCLAN_12).", ".$total_blocked." ".MDCLAN_13.")</td></tr>
	".$comments."
	<tr><td colspan='5' class='forumheader' style='text-align:center'>".MDCLAN_9."</td></tr>
	<tr><td colspan='5' class='forumheader' style='text-align:center'>
	<input class='btn button' type='submit' name='moderate' value='".MDCLAN_8."' />
	</td></tr>
	";
}
$text .= "</table></form>";

$ns->tablerender(MDCLAN_8, $text);

require_once("footer.php");



function delete_children($row, $cid) 
{
	global $sql, $sql2, $table;

	$tmp = explode(".", $row['comment_author']);
	$u_id = intval($tmp[0]);
	if ($u_id >= 1) 
	{
		$sql->db_Update("user", "user_comments=user_comments-1 WHERE user_id=".$u_id);
	}
	if (($table == "news") || ($table == '0'))
	{
		$sql->db_Update("news", "news_comment_total=news_comment_total-1 WHERE news_id='".$row['comment_item_id']."'");
	}
	if ($sql2->db_Select("comments", "*", "comment_pid='".$row['comment_id']."'")) 
	{
		while ($row2 = $sql2->db_Fetch()) 
		{
			delete_children($row2, $row2['comment_id']);
		}
	}
	$c_del[] = $cid;
	while (list ($key, $cid) = each ($c_del)) 
	{
		$sql->db_Delete("comments", "comment_id='$cid'");
	}
}
*/


?>