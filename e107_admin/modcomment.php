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
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("B")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");

$tmp = explode(".", e_QUERY);
$table = $tmp[0];
$id = $tmp[1];

switch($table){
	case "news": $type=0; break;
	case "content" : $type=1; break;
	case "download" : $type=2; break;
	case "faq" : $type=3; break;
	case "poll" : $type=4; break;
	case "docs" : $type=5; break;
	case "bugtrack" : $type=6; break;
	/****************************************
	Add your comment type here in same format as above, ie ...
	case "your_comment_type"; $type = your_type_id; break;
	****************************************/
}

if(IsSet($_POST['moderate'])){
	extract($_POST);
	if(is_array($comment_blocked)){
		while (list ($key, $cid) = each ($comment_blocked)){
			$sql -> db_Update("comments", "comment_blocked='1' WHERE comment_id='$cid' ");
		}
	}
	if(is_array($comment_unblocked)){
		while (list ($key, $cid) = each ($comment_unblocked)){
			$sql -> db_Update("comments", "comment_blocked='0' WHERE comment_id='$cid' ");
		}
	}
	if(is_array($comment_delete)){
		while (list ($key, $cid) = each ($comment_delete)){ 
			$sql -> db_Delete("comments", "comment_id='$cid' ");
		}
	}
	if($table == "news" || $table == "poll"){
		$sql -> db_Delete("cache", "cache_url='comment.php?".e_QUERY."' ");
	}else if($table == "content"){
		$eq = str_replace("content.", "", e_QUERY);
		$sql -> db_Delete("cache", "cache_url='article.php?".$eq."' ");
		$sql -> db_Delete("cache", "cache_url='article.php?".$eq.".0' ");
	}
	$message = MDCLAN_1;
}


if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."'>
<table style='width:95%' class='fborder'>";

if(!$sql -> db_Select("comments", "*", "comment_type=$type AND comment_item_id=$id")){
	$text .= "<tr><td class='forumheader3' style='text-align:center'>".MDCLAN_2.".</td></tr></table></form></div>";
}else{
	$con = new convert;
	$aj = new textparse();
	$sql2 = new db;
	while($row = $sql -> db_Fetch()){
		extract($row);
		$datestamp = $con->convert_date($comment_datestamp, "short");
		$comment_author_id = substr($comment_author, 0, strpos($comment_author, "."));
		if($comment_author_id){
			$sql2 -> db_Select("user", "*", "user_id='$comment_author_id' ");
			$row = $sql2 -> db_Fetch(); extract($row);
			$comment_nick = "<a href='".e_BASE."user.php?id.".$user_id."'>".$user_name."</a>";
			$comment_str = MDCLAN_3." #".$user_id;
		}else{
			$comment_str = MDCLAN_4;
			$comment_nick = eregi_replace("[0-9]+\.", "", $comment_author);
		}
		$comment_comment = $aj -> tpa($comment_comment);
		$text .= "<tr><td class='forumheader3' style='width:5%; text-align: center;'>".($comment_blocked ? "<img src='".e_IMAGE."generic/blocked.png' />" : "&nbsp;")."</td><td class='forumheader3' style='width:15%'>$datestamp</td><td class='forumheader3' style='width:20%'><b>".$comment_nick."</b><br />".$comment_str."</td><td class='forumheader3' style='width:40%'>".$comment_comment."</td><td class='forumheader3' style='width:20%' style='text-align:center'>".($comment_blocked ?  "<input type='checkbox' name='comment_unblocked[]' value='$comment_id'> ".MDCLAN_5."" : "<input type='checkbox' name='comment_blocked[]' value='$comment_id'> ".MDCLAN_6."")."&nbsp;<input type='checkbox' name='comment_delete[]' value='$comment_id'> ".MDCLAN_7."</td></tr>";

	}

	$text .= "<tr><td colspan='5' class='forumheader' style='text-align:center'><input class='button' type='submit' name='moderate' value='".MDCLAN_8."' /></td></tr></table></form></div>";

}

$ns -> tablerender(MDCLAN_8, $text);

require_once("footer.php");
?>	