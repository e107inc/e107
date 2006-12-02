<?php

if (!defined('e107_INIT')) { exit; }
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;

$LIST_CAPTION = $arr[0];
$LIST_DISPLAYSTYLE = ($arr[2] ? "" : "none");

$bullet = $this -> getBullet($arr[6], $mode);

$qry = '';
if($mode == "new_page" || $mode == "new_menu" ){
	$lvisit = $this -> getlvisit();
	$qry = "comment_datestamp>".$lvisit;
}

$data = $cobj->getCommentData(intval($arr[7]), '0', $qry);

foreach($data as $row){
	$rowheading	= $this -> parse_heading($row['comment_title'], $mode);
	if($row['comment_url']){
		$HEADING	= "<a href='".$row['comment_url']."' title='".$row['comment_title']."'>".$tp -> toHTML($rowheading, TRUE)."</a>";
	}else{
		$HEADING	= $tp -> toHTML($rowheading, TRUE);
	}
	$CATEGORY = '';
	if($arr[4]){
		if($row['comment_category_url']){
			$CATEGORY = "<a href='".$row['comment_category_url']."'>".$row['comment_category_heading']."</a>";
		}else{
			$CATEGORY = $row['comment_category_heading'];
		}
	}
	$AUTHOR		= ($arr[3] ? $row['comment_author'] : '');
	$DATE		= ($arr[5] ? $this -> getListDate($row['comment_datestamp'], $mode) : "");
	$ICON		= $bullet;
	$INFO		= '';
	
	$LIST_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
}

?>