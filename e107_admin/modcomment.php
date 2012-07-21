<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

// Experimental e-token
if(!empty($_POST) && !isset($_POST['e-token']))
{
	// set e-token so it can be processed by class2
	$_POST['e-token'] = '';
}

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
$id		= intval($tmp[1]);
$editid	= intval($tmp[2]);
$type	= $cobj -> getCommentType($table);
$amount = 15;
$from 	= intval(varset($_GET['frm']));

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
	
	if (is_array($_POST['comment_approve'])) 
	{
		while (list ($key, $cid) = each ($_POST['comment_approve'])) 
		{
			$sql->db_Update("comments", "comment_blocked='0' WHERE comment_id='$cid' ");
		}
	}	
	
	
	if (is_array($_POST['comment_blocked'])) 
	{
		while (list ($key, $cid) = each ($_POST['comment_blocked'])) 
		{
			$sql->db_Update("comments", "comment_blocked='1' WHERE comment_id='$cid' ");
		}
	}
	
	
	if (is_array($_POST['comment_unblocked'])) 
	{
		while (list ($key, $cid) = each ($_POST['comment_unblocked'])) 
		{
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
<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."'>
<table class='fborder' style='".ADMIN_WIDTH."'>";

if ($editid)
{
	if (!$sql->db_Select("comments", "*", "comment_id=$editid")) {
		$text .= "<tr><td class='forumheader3' style='text-align:center'>".MDCLAN_2.".</td></tr>";
	}
	else
	{
		$row = $sql->db_Fetch();
		$text .= "<tr><td><textarea class='tbox' name='comment_comment' cols='1' rows='15' style='width:100%;'>".$row['comment_comment']."</textarea></td></tr>";
		$text .= "<tr><td colspan='6' class='forumheader' style='text-align:center'><input class='button' type='submit' name='moderate' value='".MDCLAN_8."' />
		</td></tr>";
	}

	$text .= "</table></form></div>";
	$ns->tablerender(MDCLAN_8, $text);
	require_once("footer.php"); exit;
}

if($type && $table && $id)
{
	$query = "SELECT * FROM #comments WHERE (comment_type='".$type."' OR comment_type='".$table."') AND comment_item_id={$id} ORDER BY `comment_datestamp` ";
	$noresult = MDCLAN_2;
}
elseif(varsettrue($_GET['mode'])=='all')
{
	$totalCom = $sql->db_Select_gen("SELECT comment_id FROM #comments ORDER BY comment_datestamp");	
	$query = "SELECT * FROM #comments ORDER BY comment_datestamp DESC LIMIT {$from},{$amount}";	
	$noresult = MDCLAN_17;
}
else {
	$totalCom = $sql->db_Select_gen("SELECT comment_id FROM #comments WHERE comment_blocked = 2 ORDER BY comment_datestamp");
	$query = "SELECT * FROM #comments WHERE comment_blocked = 2 ORDER BY comment_datestamp DESC ";	
	$noresult = MDCLAN_17;
}

if (!$sql->db_Select_gen($query)) 
{
	$text .= "<tr><td class='forumheader3' style='text-align:center'>".$noresult.".</td></tr>";
} 
else 
{

	$con = new convert;

	$commentArray = $sql -> db_getList();
	$total_comments = count($commentArray);

	$comments = "";
	foreach($commentArray as $row)
	{
		$comment_lock 			= $row['comment_lock'];
		$total_blocked 			+= $row['comment_blocked'];
		$datestamp 				= $con->convert_date($row['comment_datestamp'], "short");
		$comment_author_id 		= substr($row['comment_author'], 0, strpos($row['comment_author'], "."));
		
		if ($comment_author_id) 
		{
			$sql->db_Select("user", "*", "user_id='$comment_author_id' ");
			
			$rowu 				= $sql->db_Fetch();
			$comment_nick 		= "<a href='".e_BASE."user.php?id.".$rowu['user_id']."'>".$rowu['user_name']."</a>";
			$comment_str 		= MDCLAN_3." #".$rowu['user_id'];
		}
		else 
		{
			$comment_str = MDCLAN_4;
			$comment_nick = preg_replace("#[0-9]+\.#", "", $row['comment_author']);
		}
		
		$row['comment_comment'] = $tp->toHTML($row['comment_comment'], TRUE, "");
		
		$row['comment_type'] = $cobj->getTable($row['comment_type']);

		$comments .= "
		<tr>
			<td class='forumheader3' style='width:5%; text-align:center;'>".statusIcon($row['comment_blocked']) ."</td>
			<td class='forumheader3' style='width:auto'>".$row['comment_type']."</td>
			<td class='forumheader3' style='width:auto'>".$datestamp."</td>
			<td class='forumheader3' style='width:auto'><b>".$comment_nick."</b><br />".$comment_str."</td>
			<td class='forumheader3' style='width:40%;'>".$row['comment_comment']."</td>
			<td class='forumheader3' style='width:25%;'>
				<a href='".e_ADMIN."modcomment.php?{$table}.{$id}.".$row['comment_id']."'><img src='".e_IMAGE."admin_images/edit_16.png' alt='".LAN_EDIT."' title='".LAN_EDIT."' style='border:none' /></a>"
				.commentOptions($row)."
			</td>
		</tr>";
	}
	
	
	$lockoptions = "<tr><td colspan='6' class='fcaption'>".MDCLAN_10."</td></tr>
	<tr>
	<td class='forumheader3' style='text-align:right' colspan='5'>".MDCLAN_14.":</td>
	<td style='width:25%;' class='forumheader3'>
	<input type='radio' name='comment_lock' value='0' ".(!$comment_lock ? " checked='checked'" : "")." /> ".MDCLAN_15." 
	<input type='radio' name='comment_lock' value='1' ".($comment_lock ? " checked='checked'" : "")." /> ".MDCLAN_16."
	<input type='hidden' name='current_lock' value='".$comment_lock."' />
	</td>
	</tr>";
	
	if($type && $table && $id)
	{
		$text .= $lockoptions;
	}
	
	$caption = MDCLAN_12;
	
	$caption .=" (".$total_comments." ".($total_comments == "1" ? MDCLAN_11 : MDCLAN_12);
	
	if(varset($_GET['mode']) !='pending')
	{
		$caption .= ", ".$total_blocked." ".MDCLAN_13;	
	}

	$caption .= ")";
	
	$text .= "<tr><td colspan='6' class='fcaption'>".$caption."</td></tr>
	".$comments."
	<tr><td colspan='6' class='forumheader' style='text-align:center'>".MDCLAN_9."</td></tr>
	<tr><td colspan='6' class='forumheader' style='text-align:center'><input class='button' type='submit' name='moderate' value='".MDCLAN_8."' /></td></tr>
	";
}
$text .= "</table></form>
<input type='hidden' name='e-token' value='".e_TOKEN."' />
</div>";

if($totalCom > $amount)
{
	$parms = $totalCom.",".$amount.",".$from.",".e_SELF.'?mode='.$_GET['mode'].'&amp;frm=[FROM]';
	$text .= "<div style='text-align:center;margin-top:10px'>".$tp->parseTemplate("{NEXTPREV=$parms}",TRUE)."</div>";
}

$ns->tablerender(MDCLAN_8, $text);

require_once("footer.php");

//v1.0.2
function commentOptions($row)
{
	$text = "<div style='margin-left:8px;display:inline-block;width:110px'>";
	
	switch ($row['comment_blocked']) 
	{		
		case 2:
			$text .= "<input type='checkbox' name='comment_approve[]' value='".$row['comment_id']."' /> ".MDCLAN_7;		
		break;			
		
		case 1:
			$text .= "<input type='checkbox' name='comment_unblocked[]' value='".$row['comment_id']."' /> ".MDCLAN_5;
		break;
		
		default:
			$text .= "<input type='checkbox' name='comment_blocked[]' value='".$row['comment_id']."' /> ".MDCLAN_6;
		break;
	}
	
	$text .= "</div><div style='display:inline-block;width:100px'>
	<input type='checkbox' name='comment_delete[]' value='".$row['comment_id']."' /> ".LAN_DELETE;
	$text .= "</div>";
	
	return $text;			
}


function statusIcon($val)
{
	switch ($val) 
	{		
		case 2:
			return "<img src='".e_IMAGE."admin_images/nopreview.png' style='width:22px;height:22px' alt='' />";	
		break;			
		
		case 1:
			return "<img src='".e_IMAGE."admin_images/blocked.png' alt='' />";	
		break;
		
		default:
			return "&nbsp;";	
		break;
	}
	
}

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

function modcomment_adminmenu() 
{
	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_comment.php");
	
	$act = varset($_GET['mode'],'pending');
	
	$var['pending']['text'] = COMLAN_331; // "Pending Approval";
	$var['pending']['link'] = e_SELF."?mode=pending";

	$var['all']['text'] = ADLAN_117; // "All entries";
	$var['all']['link'] = e_SELF."?mode=all";

	show_admin_menu(MDCLAN_8, $act, $var);
}

?>