<?php
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$content_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*

SC_BEGIN CONTENT_NEXTPREV
global $CONTENT_NEXTPREV;
return $CONTENT_NEXTPREV;
SC_END

// CONTENT_TYPE_TABLE ------------------------------------------------
SC_BEGIN CONTENT_TYPE_TABLE_TOTAL
global $contenttotal;
return $contenttotal." ".($contenttotal == 1 ? CONTENT_LAN_53 : CONTENT_LAN_54);
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_HEADING
global $CONTENT_TYPE_TABLE_HEADING, $contenttotal, $row, $tp;
$row['content_heading'] = $tp -> toHTML($row['content_heading'], TRUE, "emotes_off, no_make_clickable");
return ($contenttotal != "0" ? "<a href='".e_SELF."?cat.".$row['content_id']."'>".$row['content_heading']."</a>" : $row['content_heading'] );
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_LINK
global $row, $tp;
$text = "
[<a href='".e_PLUGIN."content/content.php?cat.list.".$row['content_id']."'>".CONTENT_TYPE_LAN_0."</a>] 
[<a href='".e_PLUGIN."content/content.php?author.list.".$row['content_id']."'>".CONTENT_TYPE_LAN_1."</a>] 
[<a href='".e_PLUGIN."content/content.php?list.".$row['content_id']."'>".CONTENT_TYPE_LAN_2."</a>] 
[<a href='".e_PLUGIN."content/content.php?top.".$row['content_id']."'>".CONTENT_TYPE_LAN_3."</a>] 
[<a href='".e_PLUGIN."content/content.php?score.".$row['content_id']."'>".CONTENT_TYPE_LAN_4."</a>] 
[<a href='".e_PLUGIN."content/content.php?recent.".$row['content_id']."'>".CONTENT_TYPE_LAN_5."</a>]";
return $text;
SC_END


SC_BEGIN CONTENT_TYPE_TABLE_SUBHEADING
global $CONTENT_TYPE_TABLE_SUBHEADING, $contenttotal, $row, $tp;
$row['content_subheading'] = $tp -> toHTML($row['content_subheading'], TRUE, "emotes_off, no_make_clickable");
return ($row['content_subheading'] ? $row['content_subheading'] : "");
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_ICON
global $CONTENT_TYPE_TABLE_ICON, $contenttotal, $row, $aa, $content_cat_icon_path_large, $content_pref;
if($contenttotal != "0"){
	$CONTENT_TYPE_TABLE_ICON = $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "cat.".$row['content_id'], "", $content_pref["content_blank_caticon"]);
}else{
	$CONTENT_TYPE_TABLE_ICON = $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "", "", $content_pref["content_blank_caticon"]);
}
return $CONTENT_TYPE_TABLE_ICON;
SC_END

// CONTENT_TYPE_TABLE_SUBMIT ------------------------------------------------
SC_BEGIN CONTENT_TYPE_TABLE_SUBMIT_ICON
global $CONTENT_TYPE_TABLE_SUBMIT_ICON, $plugindir;
return "<a href='".$plugindir."content_submit.php'>".CONTENT_ICON_SUBMIT."</a>";
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_SUBMIT_HEADING
global $CONTENT_TYPE_TABLE_SUBMIT_HEADING, $plugindir;
return "<a href='".$plugindir."content_submit.php'>".CONTENT_LAN_65."</a>";
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_SUBMIT_SUBHEADING
global $CONTENT_TYPE_TABLE_SUBMIT_SUBHEADING;
return CONTENT_LAN_66;
SC_END

// CONTENT_TYPE_TABLE_MANAGER ------------------------------------------------
SC_BEGIN CONTENT_TYPE_TABLE_MANAGER_ICON
global $CONTENT_TYPE_TABLE_MANAGER_ICON, $plugindir;
return "<a href='".$plugindir."content_manager.php'>".CONTENT_ICON_CONTENTMANAGER."</a>";
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_MANAGER_HEADING
global $CONTENT_TYPE_TABLE_MANAGER_HEADING, $plugindir;
return "<a href='".$plugindir."content_manager.php'>".CONTENT_LAN_67."</a>";
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_MANAGER_SUBHEADING
global $CONTENT_TYPE_TABLE_MANAGER_SUBHEADING;
return CONTENT_LAN_68;
SC_END

// CONTENT_TOP_TABLE ------------------------------------------------
SC_BEGIN CONTENT_TOP_TABLE_HEADING
global $CONTENT_TOP_TABLE_HEADING, $row, $qs;
return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_TOP_TABLE_ICON
global $CONTENT_TOP_TABLE_ICON, $aa, $row, $content_pref, $content_icon_path, $qs, $mainparent;
if($content_pref["content_top_icon"]){
$width = (isset($content_pref["content_upload_icon_size"]) && $content_pref["content_upload_icon_size"] ? $content_pref["content_upload_icon_size"] : "100");
$width = (isset($content_pref["content_top_icon_width"]) && $content_pref["content_top_icon_width"] ? $content_pref["content_top_icon_width"] : $width);
return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "content.".$row['content_id'], $width, $content_pref["content_blank_icon"]);
}
SC_END

SC_BEGIN CONTENT_TOP_TABLE_AUTHOR
global $CONTENT_TOP_TABLE_AUTHOR;
return $CONTENT_TOP_TABLE_AUTHOR;
SC_END

SC_BEGIN CONTENT_TOP_TABLE_RATING
global $CONTENT_TOP_TABLE_RATING, $row;
$row['rate_avg'] = round($row['rate_avg'], 1);
$row['rate_avg'] = (strlen($row['rate_avg'])>1 ? $row['rate_avg'] : $row['rate_avg'].".0");
$tmp = explode(".", $row['rate_avg']);
$rating = "";
$rating .= $row['rate_avg']." ";
for($c=1; $c<= $tmp[0]; $c++){
	$rating .= "<img src='".e_IMAGE."rate/box.png' alt='' style='border:0; height:8px; vertical-align:middle' />";
}
if($tmp[0] < 10){
	for($c=9; $c>=$tmp[0]; $c--){
		$rating .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='border:0; height:8px; vertical-align:middle' />";
	}
}
$rating .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='border:0; height:8px; vertical-align:middle' />";
return $rating;
SC_END

// CONTENT_SCORE_TABLE ------------------------------------------------
SC_BEGIN CONTENT_SCORE_TABLE_HEADING
global $CONTENT_SCORE_TABLE_HEADING, $row, $qs;
return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_SCORE_TABLE_ICON
global $CONTENT_SCORE_TABLE_ICON, $aa, $row, $content_pref, $content_icon_path, $qs, $mainparent;
if(isset($content_pref["content_score_icon"]) && $content_pref["content_score_icon"]){
$width = (isset($content_pref["content_upload_icon_size"]) && $content_pref["content_upload_icon_size"] ? $content_pref["content_upload_icon_size"] : "100");
$width = (isset($content_pref["content_score_icon_width"]) && $content_pref["content_score_icon_width"] ? $content_pref["content_score_icon_width"] : $width);
return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "content.".$row['content_id'], $width, $content_pref["content_blank_icon"]);
}
SC_END

SC_BEGIN CONTENT_SCORE_TABLE_AUTHOR
global $CONTENT_SCORE_TABLE_AUTHOR;
return $CONTENT_SCORE_TABLE_AUTHOR;
SC_END

SC_BEGIN CONTENT_SCORE_TABLE_SCORE
global $CONTENT_SCORE_TABLE_SCORE, $row;
$score = $row['content_score'];
$height = "height:8px;";
$img = "";
$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
$img .= "<img src='".e_PLUGIN."content/images/score.png' alt='' style='$height width:".$score."px; border:0;' />";
$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
if($score < 100){
	$empty = 100-$score;
	$img .= "<img src='".e_PLUGIN."content/images/score_empty.png' alt='' style='$height width:".$empty."px; border:0;' />";
}
$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
return $score."/100 ".$img;
SC_END

// CONTENT_SUBMIT_TYPE_TABLE ------------------------------------------------
SC_BEGIN CONTENT_SUBMIT_TYPE_TABLE_HEADING
global $CONTENT_SUBMIT_TYPE_TABLE_HEADING, $row;
return "<a href='".e_SELF."?content.submit.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING
global $CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING, $row;
return ($row['content_subheading'] ? $row['content_subheading'] : "");
SC_END

SC_BEGIN CONTENT_SUBMIT_TYPE_TABLE_ICON
global $CONTENT_SUBMIT_TYPE_TABLE_ICON, $aa, $row, $content_cat_icon_path_large, $content_pref;
return $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "content.submit.".$row['content_id'], "", $content_pref["content_blank_caticon"]);
SC_END

// CONTENT_CONTENT_TABLEMANAGER ------------------------------------------------
SC_BEGIN CONTENT_CONTENTMANAGER_CATEGORY
global $CONTENT_CONTENTMANAGER_CATEGORY, $row, $content_pref;
if( (isset($content_pref["content_manager_personal"]) && check_class($content_pref["content_manager_personal"])) || (isset($content_pref["content_manager_category"]) && check_class($content_pref["content_manager_category"])) || (isset($content_pref["content_manager_approve"]) && check_class($content_pref["content_manager_approve"])) ){
return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
}
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_ICONNEW
global $CONTENT_CONTENTMANAGER_ICONNEW, $row, $content_pref;
if( (isset($content_pref["content_manager_personal"]) && check_class($content_pref["content_manager_personal"])) || (isset($content_pref["content_manager_category"]) && check_class($content_pref["content_manager_category"])) ){
return "<a href='".e_SELF."?content.create.".$row['content_id']."'>".CONTENT_ICON_NEW."</a>";
//return "<input type='button' onclick=\"document.location='".e_SELF."?content.create.".$row['content_id']."'\" value='new' title='new' />";
}
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_ICONEDIT
global $CONTENT_CONTENTMANAGER_ICONEDIT, $row, $content_pref;
if( (isset($content_pref["content_manager_personal"]) && check_class($content_pref["content_manager_personal"])) || (isset($content_pref["content_manager_category"]) && check_class($content_pref["content_manager_category"])) ){
return "<a href='".e_SELF."?content.".$row['content_id']."'>".CONTENT_ICON_EDIT."</a>";
//return "<input type='button' onclick=\"document.location='".e_SELF."?content.".$row['content_id']."'\" value='edit' title='edit' />";
}
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_ICONSUBM
global $CONTENT_CONTENTMANAGER_ICONSUBM, $row, $content_pref, $plugintable;
if(isset($content_pref["content_manager_approve"]) && check_class($content_pref["content_manager_approve"])){
	if(!is_object($sqls)){ $sqls = new db; }
	$num = $sqls -> db_Count($plugintable, "(*)", "WHERE content_refer = 'sa' AND content_parent='".intval($row['content_id'])."' ");
	if($num>0){
		return "<a href='".e_SELF."?content.submitted.".$row['content_id']."'>".CONTENT_ICON_SUBMIT_SMALL."</a>";
	}
}
SC_END


// CONTENT_AUTHOR_TABLE ------------------------------------------------
SC_BEGIN CONTENT_AUTHOR_TABLE_NAME
global $CONTENT_AUTHOR_TABLE_NAME, $authordetails, $i, $qs, $row;
$name = ($authordetails[$i][1] == "" ? "... ".CONTENT_LAN_29." ..." : $authordetails[$i][1]);
$authorlink = "<a href='".e_SELF."?author.".$row['content_id']."'>".$name."</a>";
return $authorlink;
SC_END

SC_BEGIN CONTENT_AUTHOR_TABLE_ICON
global $CONTENT_AUTHOR_TABLE_ICON, $qs, $row;
return "<a href='".e_SELF."?author.".$row['content_id']."'>".CONTENT_ICON_AUTHORLIST."</a>";
SC_END

SC_BEGIN CONTENT_AUTHOR_TABLE_TOTAL
global $CONTENT_AUTHOR_TABLE_TOTAL, $totalcontent, $mainparent, $content_pref;
if($content_pref["content_author_amount"]){
$CONTENT_AUTHOR_TABLE_TOTAL = $totalcontent." ".($totalcontent==1 ? CONTENT_LAN_53 : CONTENT_LAN_54);
return $CONTENT_AUTHOR_TABLE_TOTAL;
}
SC_END

SC_BEGIN CONTENT_AUTHOR_TABLE_LASTITEM
global $CONTENT_AUTHOR_TABLE_LASTITEM, $gen, $row, $mainparent, $content_pref;
if($content_pref["content_author_lastitem"]){
if(!is_object($gen)){ $gen = new convert; }
$CONTENT_AUTHOR_TABLE_LASTITEM = preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "short"));
$CONTENT_AUTHOR_TABLE_LASTITEM .= " : <a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
return $CONTENT_AUTHOR_TABLE_LASTITEM;
}
SC_END

// CONTENT_CAT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_CAT_TABLE_INFO_PRE
global $CONTENT_CAT_TABLE_INFO_PRE;
if($CONTENT_CAT_TABLE_INFO_PRE === TRUE){
$CONTENT_CAT_TABLE_INFO_PRE = " ";
return $CONTENT_CAT_TABLE_INFO_PRE;
}
SC_END
SC_BEGIN CONTENT_CAT_TABLE_INFO_POST
global $CONTENT_CAT_TABLE_INFO_POST;
if($CONTENT_CAT_TABLE_INFO_POST === TRUE){
$CONTENT_CAT_TABLE_INFO_POST = " ";
return $CONTENT_CAT_TABLE_INFO_POST;
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_ICON
global $CONTENT_CAT_TABLE_ICON, $aa, $totalitems, $row, $content_pref, $qs, $content_cat_icon_path_large, $mainparent;
if(isset($content_pref["content_catall_icon"]) && $content_pref["content_catall_icon"]){
	//$qry = ($totalitems > 0 ? "cat.".$row['content_id'] : "");
	$qry = "cat.".$row['content_id'];
	return $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, $qry, "", $content_pref["content_blank_caticon"]);
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_HEADING
global $CONTENT_CAT_TABLE_HEADING, $row, $totalitems, $tp;
//return ($totalitems > 0 ? "<a href='".e_SELF."?cat.".$row['content_id']."'>".$tp -> toHTML($row['content_heading'], TRUE, "")."</a>" : $tp -> toHTML($row['content_heading'], TRUE, "") );
return "<a href='".e_SELF."?cat.".$row['content_id']."'>".$tp -> toHTML($row['content_heading'], TRUE, "")."</a>";
SC_END

SC_BEGIN CONTENT_CAT_TABLE_AMOUNT
global $CONTENT_CAT_TABLE_AMOUNT, $aa, $row, $totalitems, $mainparent, $content_pref;
if(isset($content_pref["content_catall_amount"]) && $content_pref["content_catall_amount"]){
$n = $totalitems;
$CONTENT_CAT_TABLE_AMOUNT = $n." ".($n == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54);
return $CONTENT_CAT_TABLE_AMOUNT;
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_SUBHEADING
global $CONTENT_CAT_TABLE_SUBHEADING, $row, $tp, $mainparent, $content_pref;
if(isset($content_pref["content_catall_subheading"]) && $content_pref["content_catall_subheading"]){
return ($row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_DATE
global $CONTENT_CAT_TABLE_DATE, $gen, $row, $mainparent, $content_pref, $gen;
if(isset($content_pref["content_catall_date"]) && $content_pref["content_catall_date"]){
if(!is_object($gen)){ $gen = new convert; }
$datestamp = preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "long"));
$DATE = ($datestamp != "" ? $datestamp : "");
return $DATE;
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_AUTHORDETAILS
global $CONTENT_CAT_TABLE_AUTHORDETAILS;
return $CONTENT_CAT_TABLE_AUTHORDETAILS;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_EPICONS
global $CONTENT_CAT_TABLE_EPICONS, $row, $tp, $mainparent, $content_pref;
$EPICONS = "";
if($row['content_pe'] && isset($content_pref["content_catall_peicon"]) && $content_pref["content_catall_peicon"]){
	$EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_72."^plugin:content.".$row['content_id']."}");
	$EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_72."^plugin:content.".$row['content_id']."}");
	$EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_72."^plugin:content.".$row['content_id']."}");
return $EPICONS;
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_COMMENT
global $CONTENT_CAT_TABLE_COMMENT, $row, $qs, $comment_total, $mainparent, $content_pref, $plugintable;
if($row['content_comment'] && isset($content_pref["content_catall_comment"]) && $content_pref["content_catall_comment"]){
$sqlc = new db;
$comment_total = $sqlc -> db_Select("comments", "*",  "comment_item_id='".$row['content_id']."' AND comment_type='".$plugintable."' AND comment_pid='0' ");
return "<a style='text-decoration:none;' href='".e_SELF."?cat.".$row['content_id'].".comment'>".CONTENT_LAN_57." ".$comment_total."</a>";
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_TEXT
global $CONTENT_CAT_TABLE_TEXT, $row, $tp, $mainparent, $content_pref;
if($row['content_text'] && isset($content_pref["content_catall_text"]) && $content_pref["content_catall_text"] && ($content_pref["content_catall_text_char"] > 0 || $content_pref["content_catall_text_char"] == 'all')){
	if($content_pref["content_catall_text_char"] == 'all'){
		$CONTENT_CAT_TABLE_TEXT = $row['content_text'];
	}else{
		$rowtext = preg_replace("/\[newpage.*?]/si", " ", $row['content_text']);
		$rowtext = $tp->toHTML($rowtext, TRUE, "nobreak");
		
		$rowtext = strip_tags($rowtext);
		$words = explode(" ", $rowtext);
		$CONTENT_CAT_TABLE_TEXT = implode(" ", array_slice($words, 0, $content_pref["content_catall_text_char"]));
		if($content_pref["content_catall_text_link"]){
			$CONTENT_CAT_TABLE_TEXT .= " <a href='".e_SELF."?cat.".$row['content_id']."'>".$content_pref["content_catall_text_post"]."</a>";
		}else{
			$CONTENT_CAT_TABLE_TEXT .= " ".$content_pref["content_catall_text_post"];
		}
	}
return $CONTENT_CAT_TABLE_TEXT;
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_RATING
global $CONTENT_CAT_TABLE_RATING, $row, $rater, $mainparent, $content_pref, $plugintable;
$RATING = "";
if($row['content_rate'] && isset($content_pref["content_catall_rating"]) && $content_pref["content_catall_rating"]){
return $rater->composerating($plugintable, $row['content_id'], $enter=TRUE, $userid=FALSE);
}
return $RATING;
SC_END

// CONTENT_CAT_LIST_TABLE ------------------------------------------------
SC_BEGIN CONTENT_CAT_LIST_TABLE_INFO_PRE
global $CONTENT_CAT_LIST_TABLE_INFO_PRE;
if($CONTENT_CAT_LIST_TABLE_INFO_PRE === TRUE){
$CONTENT_CAT_LIST_TABLE_INFO_PRE = " ";
return $CONTENT_CAT_LIST_TABLE_INFO_PRE;
}
SC_END
SC_BEGIN CONTENT_CAT_LIST_TABLE_INFO_POST
global $CONTENT_CAT_LIST_TABLE_INFO_POST;
if($CONTENT_CAT_LIST_TABLE_INFO_POST === TRUE){
$CONTENT_CAT_LIST_TABLE_INFO_POST = " ";
return $CONTENT_CAT_LIST_TABLE_INFO_POST;
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_ICON
global $CONTENT_CAT_LIST_TABLE_ICON, $aa, $row, $qs, $content_pref, $content_cat_icon_path_large, $mainparent;
if(isset($content_pref["content_cat_icon"]) && $content_pref["content_cat_icon"]){
return $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "", "", $content_pref["content_blank_caticon"]);;
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_HEADING
global $CONTENT_CAT_LIST_TABLE_HEADING, $tp, $row, $totalparent, $tp;
//return ($totalparent > 0 ? "<a href='".e_SELF."?cat.".$row['content_id'].".view'>".$tp -> toHTML($row['content_heading'], TRUE, "")."</a>" : $tp -> toHTML($row['content_heading'], TRUE, "") );
return "<a href='".e_SELF."?cat.".$row['content_id'].".view'>".$tp -> toHTML($row['content_heading'], TRUE, "")."</a>";
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_SUMMARY
global $CONTENT_CAT_LIST_TABLE_SUMMARY, $tp, $row, $mainparent;
return ($row['content_summary'] ? $tp -> toHTML($row['content_summary'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_TEXT
global $CONTENT_CAT_LIST_TABLE_TEXT, $tp, $row, $mainparent, $content_pref;
if($row['content_text'] && isset($content_pref["content_cat_text"]) && $content_pref["content_cat_text"] && ($content_pref["content_cat_text_char"] > 0 || $content_pref["content_cat_text_char"] == 'all')){
	if($content_pref["content_cat_text_char"] == 'all'){
		//$CONTENT_CAT_LIST_TABLE_TEXT = $row['content_text'];
		$CONTENT_CAT_LIST_TABLE_TEXT = $tp->toHTML($row['content_text'], TRUE, "constants");
	}else{
		$rowtext = preg_replace("/\[newpage.*?]/si", " ", $row['content_text']);
		$rowtext = $tp->toHTML($rowtext, TRUE, "nobreak, constants");
		
		$rowtext = strip_tags($rowtext);
		$words = explode(" ", $rowtext);
		$CONTENT_CAT_LIST_TABLE_TEXT = implode(" ", array_slice($words, 0, $content_pref["content_cat_text_char"]));
		if($content_pref["content_cat_text_link"]){
			$CONTENT_CAT_LIST_TABLE_TEXT .= " <a href='".e_SELF."?cat.".$row['content_id'].".view'>".$content_pref["content_cat_text_post"]."</a>";
		}else{
			$CONTENT_CAT_LIST_TABLE_TEXT .= " ".$content_pref["content_cat_text_post"];
		}
	}
return $CONTENT_CAT_LIST_TABLE_TEXT;
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_AMOUNT
global $CONTENT_CAT_LIST_TABLE_AMOUNT, $aa, $row, $mainparent, $content_pref, $totalparent;
if(isset($content_pref["content_cat_amount"]) && $content_pref["content_cat_amount"]){
$n = $totalparent;
$n = $n." ".($n == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54);
return $n;
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_SUBHEADING
global $CONTENT_CAT_LIST_TABLE_SUBHEADING, $tp, $row, $mainparent, $content_pref;
if(isset($content_pref["content_cat_subheading"]) && $content_pref["content_cat_subheading"]){
return ($row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_DATE
global $CONTENT_CAT_LIST_TABLE_DATE, $row, $gen, $mainparent, $content_pref, $gen;
if(isset($content_pref["content_cat_date"]) && $content_pref["content_cat_date"]){
if(!is_object($gen)){ $gen = new convert; }
$datestamp = preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "long"));
return ($datestamp != "" ? $datestamp : "");
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_AUTHORDETAILS
global $CONTENT_CAT_LIST_TABLE_AUTHORDETAILS;
return $CONTENT_CAT_LIST_TABLE_AUTHORDETAILS;
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_EPICONS
global $CONTENT_CAT_LIST_TABLE_EPICONS, $row, $tp, $qs, $mainparent, $content_pref;
$EPICONS = "";
if( (isset($content_pref["content_cat_peicon"]) && $content_pref["content_cat_peicon"] && $row['content_pe']) || (isset($content_pref["content_cat_peicon_all"]) && $content_pref["content_cat_peicon_all"])){
	$EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_72."^plugin:content.$qs[1]}");
	$EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_72."^plugin:content.$qs[1]}");
	$EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_72."^plugin:content.$qs[1]}");
return $EPICONS;
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_COMMENT
global $CONTENT_CAT_LIST_TABLE_COMMENT, $qs, $row, $comment_total, $mainparent, $content_pref, $sql, $plugintable;
if($row['content_comment'] && isset($content_pref["content_cat_comment"]) && $content_pref["content_cat_comment"]){
	$comment_total = $sql -> db_Count("comments", "(*)",  "WHERE comment_item_id='".intval($qs[1])."' AND comment_type='".$plugintable."' AND comment_pid='0' ");
	return "<a style='text-decoration:none;' href='".e_SELF."?cat.".$qs[1].".comment'>".CONTENT_LAN_57." ".$comment_total."</a>";
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_RATING
global $CONTENT_CAT_LIST_TABLE_RATING, $row, $rater, $content_pref, $mainparent, $plugintable;
$RATING = "";
if( (isset($content_pref["content_cat_rating_all"]) && $content_pref["content_cat_rating_all"]) || (isset($content_pref["content_cat_rating"]) && $content_pref["content_cat_rating"] && $row['content_rate'])){
	return $rater->composerating($plugintable, $row['content_id'], $enter=TRUE, $userid=FALSE);
}
return $RATING;
SC_END

// CONTENT_CAT_LISTSUB ------------------------------------------------
SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_ICON
global $CONTENT_CAT_LISTSUB_TABLE_ICON, $aa, $row, $content_pref, $qs, $mainparent, $content_cat_icon_path_small;
if(isset($content_pref["content_catsub_icon"]) && $content_pref["content_catsub_icon"]){
return $aa -> getIcon("catsmall", $row['content_icon'], $content_cat_icon_path_small, "cat.".$row['content_id'], "", $content_pref["content_blank_caticon"]);
}
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_HEADING
global $CONTENT_CAT_LISTSUB_TABLE_HEADING, $tp, $row, $totalsubcat, $tp;
//return ($totalsubcat > 0 ? "<a href='".e_SELF."?cat.".$row['content_id']."'>".$tp -> toHTML($row['content_heading'], TRUE, "")."</a>" : $tp -> toHTML($row['content_heading'], TRUE, "") );
return "<a href='".e_SELF."?cat.".$row['content_id']."'>".$tp -> toHTML($row['content_heading'], TRUE, "")."</a>";
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_AMOUNT
global $CONTENT_CAT_LISTSUB_TABLE_AMOUNT, $aa, $row, $content_pref, $mainparent, $totalsubcat;
if(isset($content_pref["content_catsub_amount"]) && $content_pref["content_catsub_amount"]){
$n = $totalsubcat;
$n = $n." ".($n == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54);
return $n;
}
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_SUBHEADING
global $CONTENT_CAT_LISTSUB_TABLE_SUBHEADING, $row, $tp, $content_pref, $mainparent;
if(isset($content_pref["content_catsub_subheading"]) && $content_pref["content_catsub_subheading"]){
return ($row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
}
SC_END

// CONTENT_SEARCH_TABLE ------------------------------------------------
SC_BEGIN CONTENT_SEARCH_TABLE_SELECT
global $CONTENT_SEARCH_TABLE_SELECT;
return $CONTENT_SEARCH_TABLE_SELECT;
SC_END

SC_BEGIN CONTENT_SEARCH_TABLE_ORDER
global $CONTENT_SEARCH_TABLE_ORDER;
return $CONTENT_SEARCH_TABLE_ORDER;
SC_END

SC_BEGIN CONTENT_SEARCH_TABLE_KEYWORD
global $CONTENT_SEARCH_TABLE_KEYWORD;
return $CONTENT_SEARCH_TABLE_KEYWORD;
SC_END

// CONTENT_SEARCHRESULT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_SEARCHRESULT_TABLE_ICON
global $CONTENT_SEARCHRESULT_TABLE_ICON, $aa, $row, $content_icon_path, $qs, $content_pref, $mainparent;
$width = (isset($content_pref["content_upload_icon_size"]) && $content_pref["content_upload_icon_size"] ? $content_pref["content_upload_icon_size"] : "100");
return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "content.".$row['content_id'], $width, $content_pref["content_blank_icon"]);
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_HEADING
global $CONTENT_SEARCHRESULT_TABLE_HEADING, $row, $qs, $tp;
return ($row['content_heading'] ? "<a href='".e_SELF."?content.".$row['content_id']."'>".$tp -> toHTML($row['content_heading'], TRUE, "")."</a>" : "");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_SUBHEADING
global $CONTENT_SEARCHRESULT_TABLE_SUBHEADING, $row, $tp;
return ($row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS
global $CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS, $qs, $aa, $row;
$authordetails = $aa -> getAuthor($row['content_author']);
$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS = $authordetails[1];
if(USER){
	if(is_numeric($authordetails[3])){
		$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
	}else{
		$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS .= " ".CONTENT_ICON_USER;
	}
}else{
	$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS .= " ".CONTENT_ICON_USER;
}
$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS .= " <a href='".e_SELF."?author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
return $CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS;
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_DATE
global $CONTENT_SEARCHRESULT_TABLE_DATE, $gen, $row;
$datestamp = preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "short"));
return $datestamp;
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_TEXT
global $CONTENT_SEARCHRESULT_TABLE_TEXT, $row, $tp;
return ($row['content_text'] ? $tp -> toHTML($row['content_text'], TRUE, "") : "");
SC_END

// CONTENT_RECENT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_RECENT_TABLE_INFOPRE
global $CONTENT_RECENT_TABLE_INFOPRE;
if($CONTENT_RECENT_TABLE_INFOPRE === TRUE){
$CONTENT_RECENT_TABLE_INFOPRE = " ";
return $CONTENT_RECENT_TABLE_INFOPRE;
}
SC_END
SC_BEGIN CONTENT_RECENT_TABLE_INFOPOST
global $CONTENT_RECENT_TABLE_INFOPOST;
if($CONTENT_RECENT_TABLE_INFOPOST === TRUE){
$CONTENT_RECENT_TABLE_INFOPOST = " ";
return $CONTENT_RECENT_TABLE_INFOPOST;
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_ICON
global $CONTENT_RECENT_TABLE_ICON, $aa, $row, $content_icon_path, $content_pref, $mainparent;
if(isset($content_pref["content_list_icon"]) && $content_pref["content_list_icon"]){
$width = (isset($content_pref["content_upload_icon_size"]) && $content_pref["content_upload_icon_size"] ? $content_pref["content_upload_icon_size"] : "100");
return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "content.".$row['content_id'], $width, $content_pref["content_blank_icon"]);
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_HEADING
global $CONTENT_RECENT_TABLE_HEADING, $row, $tp;
return ($row['content_heading'] ? "<a href='".e_SELF."?content.".$row['content_id']."'>".$tp->toHTML($row['content_heading'], TRUE, "")."</a>" : "");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_SUBHEADING
global $CONTENT_RECENT_TABLE_SUBHEADING, $tp, $content_pref, $qs, $row, $mainparent;
if (isset($content_pref["content_list_subheading"]) && $content_pref["content_list_subheading"] && $row['content_subheading'] && $content_pref["content_list_subheading_char"] && $content_pref["content_list_subheading_char"] != "" && $content_pref["content_list_subheading_char"] != "0"){
	if(strlen($row['content_subheading']) > $content_pref["content_list_subheading_char"]) {
		$row['content_subheading'] = substr($row['content_subheading'], 0, $content_pref["content_list_subheading_char"]).$content_pref["content_list_subheading_post"];
	}
	$CONTENT_RECENT_TABLE_SUBHEADING = ($row['content_subheading'] != "" && $row['content_subheading'] != " " ? $row['content_subheading'] : "");
}else{
	$CONTENT_RECENT_TABLE_SUBHEADING = ($row['content_subheading'] ? $row['content_subheading'] : "");
}
return $tp->toHTML($CONTENT_RECENT_TABLE_SUBHEADING, TRUE, "");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_SUMMARY
global $CONTENT_RECENT_TABLE_SUMMARY, $content_pref, $tp, $qs, $row, $mainparent;
if (isset($content_pref["content_list_summary"]) && $content_pref["content_list_summary"]){
	if($row['content_summary'] && $content_pref["content_list_summary_char"] && $content_pref["content_list_summary_char"] != "" && $content_pref["content_list_summary_char"] != "0"){
		if(strlen($row['content_summary']) > $content_pref["content_list_summary_char"]) {
			$row['content_summary'] = substr($row['content_summary'], 0, $content_pref["content_list_summary_char"]).$content_pref["content_list_summary_post"];
		}
		$CONTENT_RECENT_TABLE_SUMMARY = ($row['content_summary'] != "" && $row['content_summary'] != " " ? $row['content_summary'] : "");
	}else{
		$CONTENT_RECENT_TABLE_SUMMARY = ($row['content_summary'] ? $row['content_summary'] : "");
	}
return $tp->toHTML($CONTENT_RECENT_TABLE_SUMMARY, TRUE, "");
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_TEXT
global $CONTENT_RECENT_TABLE_TEXT, $content_pref, $qs, $row, $mainparent, $tp;
if(isset($content_pref["content_list_text"]) && $content_pref["content_list_text"] && $content_pref["content_list_text_char"] > 0){
	$rowtext = preg_replace("/\[newpage.*?]/si", " ", $row['content_text']);
	//$rowtext = str_replace ("<br />", " ", $rowtext);
	$rowtext = $tp->toHTML($rowtext, TRUE, "nobreak");
	$rowtext = strip_tags($rowtext);
	$words = explode(" ", $rowtext);
	$CONTENT_RECENT_TABLE_TEXT = implode(" ", array_slice($words, 0, $content_pref["content_list_text_char"]));
	if($CONTENT_RECENT_TABLE_TEXT){
		if($content_pref["content_list_text_link"]){
			$CONTENT_RECENT_TABLE_TEXT .= " <a href='".e_SELF."?content.".$row['content_id']."'>".$content_pref["content_list_text_post"]."</a>";
		}else{
			$CONTENT_RECENT_TABLE_TEXT .= " ".$content_pref["content_list_text_post"];
		}
	}
}
return $CONTENT_RECENT_TABLE_TEXT;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_DATE
global $CONTENT_RECENT_TABLE_DATE, $content_pref, $qs, $row, $mainparent;
if(isset($content_pref["content_list_date"]) && $content_pref["content_list_date"]){
$datestyle = ($content_pref["content_list_datestyle"] ? $content_pref["content_list_datestyle"] : "%d %b %Y");
return strftime($datestyle, $row['content_datestamp']);
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_EPICONS
global $CONTENT_RECENT_TABLE_EPICONS, $tp, $content_pref, $qs, $row, $mainparent;
$CONTENT_RECENT_TABLE_EPICONS = "";
if(isset($content_pref["content_list_peicon"]) && $content_pref["content_list_peicon"]){
	if($row['content_pe'] || isset($content_pref["content_list_peicon_all"]) && $content_pref["content_list_peicon_all"]){
		$CONTENT_RECENT_TABLE_EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
		$CONTENT_RECENT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
		$CONTENT_RECENT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
	}
}
return $CONTENT_RECENT_TABLE_EPICONS;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_AUTHORDETAILS
global $CONTENT_RECENT_TABLE_AUTHORDETAILS;
return $CONTENT_RECENT_TABLE_AUTHORDETAILS;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_EDITICON
global $CONTENT_RECENT_TABLE_EDITICON, $content_pref, $qs, $row, $mainparent, $plugindir;
if(ADMIN && getperms("P") && isset($content_pref["content_list_editicon"]) && $content_pref["content_list_editicon"]){
return $CONTENT_RECENT_TABLE_EDITICON = "<a href='".$plugindir."admin_content_config.php?content.edit.".$row['content_id']."'>".CONTENT_ICON_EDIT."</a>";
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_REFER
global $CONTENT_RECENT_TABLE_REFER, $content_pref, $qs, $row, $mainparent;
if($content_pref["content_log"] && $content_pref["content_list_refer"]){
	$refercounttmp = explode("^", $row['content_refer']);
	$CONTENT_RECENT_TABLE_REFER = ($refercounttmp[0] ? $refercounttmp[0] : "0");
	if($CONTENT_RECENT_TABLE_REFER > 0){
		return $CONTENT_RECENT_TABLE_REFER;
	}
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_RATING
global $CONTENT_RECENT_TABLE_RATING, $rater, $row, $qs, $content_pref, $plugintable, $mainparent;
if($content_pref["content_list_rating"]){
	if($content_pref["content_list_rating_all"] || $row['content_rate']){
		return $rater->composerating($plugintable, $row['content_id'], $enter=FALSE, $userid=FALSE);
	}
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_PARENT
global $CONTENT_RECENT_TABLE_PARENT, $content_pref, $mainparent, $row, $array, $aa;
if(isset($content_pref["content_list_parent"]) && $content_pref["content_list_parent"]){
return $aa -> getCrumbItem($row['content_parent'], $array);
}
SC_END

// CONTENT_ARCHIVE_TABLE ------------------------------------------------
SC_BEGIN CONTENT_ARCHIVE_TABLE_LETTERS
global $CONTENT_ARCHIVE_TABLE_LETTERS, $content_pref, $mainparent;
if($content_pref["content_archive_letterindex"]){
return $CONTENT_ARCHIVE_TABLE_LETTERS;
}
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_HEADING
global $CONTENT_ARCHIVE_TABLE_HEADING, $row, $qs;
return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_DATE
global $CONTENT_ARCHIVE_TABLE_DATE, $row, $content_pref, $qs, $mainparent;
if(isset($content_pref["content_archive_date"]) && $content_pref["content_archive_date"]){
$datestyle = ($content_pref["content_archive_datestyle"] ? $content_pref["content_archive_datestyle"] : "%d %b %Y");
return strftime($datestyle, $row['content_datestamp']);
}
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_AUTHOR
global $CONTENT_ARCHIVE_TABLE_AUTHOR;
return $CONTENT_ARCHIVE_TABLE_AUTHOR;
SC_END

// CONTENT_CONTENT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_CONTENT_TABLE_INFO_PRE
global $CONTENT_CONTENT_TABLE_INFO_PRE;
if($CONTENT_CONTENT_TABLE_INFO_PRE === TRUE){
$CONTENT_CONTENT_TABLE_INFO_PRE = " ";
return $CONTENT_CONTENT_TABLE_INFO_PRE;
}
SC_END
SC_BEGIN CONTENT_CONTENT_TABLE_INFO_POST
global $CONTENT_CONTENT_TABLE_INFO_POST;
if($CONTENT_CONTENT_TABLE_INFO_POST === TRUE){
$CONTENT_CONTENT_TABLE_INFO_POST = " ";
return $CONTENT_CONTENT_TABLE_INFO_POST;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA
global $CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA;
if($CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA === TRUE){
$CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA = " ";
return $CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA;
}
SC_END
SC_BEGIN CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA
global $CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA;
if($CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA === TRUE){
$CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA = " ";
return $CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_PARENT
global $CONTENT_CONTENT_TABLE_PARENT, $aa, $array, $row, $content_pref, $mainparent;
if(isset($content_pref["content_content_parent"]) && $content_pref["content_content_parent"]){
return $aa -> getCrumbItem($row['content_parent'], $array);
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_ICON
global $CONTENT_CONTENT_TABLE_ICON, $qs, $row, $aa, $content_pref, $content_icon_path, $mainparent;
if(isset($content_pref["content_content_icon"]) && $content_pref["content_content_icon"]){
$width = (isset($content_pref["content_upload_icon_size"]) && $content_pref["content_upload_icon_size"] ? $content_pref["content_upload_icon_size"] : "100");
return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "", $width, $content_pref["content_blank_icon"]);
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_HEADING
global $CONTENT_CONTENT_TABLE_HEADING, $row, $tp;
$CONTENT_CONTENT_TABLE_HEADING = ($row['content_heading'] ? $tp -> toHTML($row['content_heading'], TRUE, "") : "");
return $CONTENT_CONTENT_TABLE_HEADING;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_REFER
global $CONTENT_CONTENT_TABLE_REFER, $sql, $qs, $content_pref, $plugintable, $mainparent;
if(isset($content_pref["content_content_refer"]) && $content_pref["content_content_refer"]){
	$sql -> db_Select($plugintable, "content_refer", "content_id='".intval($qs[1])."' ");
	list($content_refer) = $sql -> db_Fetch();
	$refercounttmp = explode("^", $content_refer);
	$CONTENT_CONTENT_TABLE_REFER = ($refercounttmp[0] ? $refercounttmp[0] : "");
return $CONTENT_CONTENT_TABLE_REFER;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_SUBHEADING
global $CONTENT_CONTENT_TABLE_SUBHEADING, $row, $tp, $content_pref, $qs, $mainparent;
$CONTENT_CONTENT_TABLE_SUBHEADING = ($content_pref["content_content_subheading"] && $row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
return $CONTENT_CONTENT_TABLE_SUBHEADING;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_COMMENT
global $CONTENT_CONTENT_TABLE_COMMENT, $cobj, $qs, $content_pref, $mainparent, $row, $plugintable;
if((isset($content_pref["content_content_comment"]) && $content_pref["content_content_comment"] && $row['content_comment']) || $content_pref["content_content_comment_all"] ){
return $cobj -> count_comments($plugintable, $qs[1]);
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_DATE
global $CONTENT_CONTENT_TABLE_DATE, $gen, $row, $qs, $content_pref, $mainparent;
if(isset($content_pref["content_content_date"]) && $content_pref["content_content_date"]){
	$gen = new convert;
	$datestamp = preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "long"));
	$CONTENT_CONTENT_TABLE_DATE = ($datestamp != "" ? $datestamp : "");
return $CONTENT_CONTENT_TABLE_DATE;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_AUTHORDETAILS
global $CONTENT_CONTENT_TABLE_AUTHORDETAILS;
return $CONTENT_CONTENT_TABLE_AUTHORDETAILS;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_EPICONS
global $CONTENT_CONTENT_TABLE_EPICONS, $content_pref, $qs, $row, $tp, $mainparent;
$CONTENT_CONTENT_TABLE_EPICONS = "";
if(($content_pref["content_content_peicon"] && $row['content_pe']) || $content_pref["content_content_peicon_all"]){
	$CONTENT_CONTENT_TABLE_EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
	$CONTENT_CONTENT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
	$CONTENT_CONTENT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
return $CONTENT_CONTENT_TABLE_EPICONS;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_EDITICON
global $CONTENT_CONTENT_TABLE_EDITICON, $content_pref, $qs, $row, $plugindir, $mainparent;
if(ADMIN && getperms("P") && isset($content_pref["content_content_editicon"])){
	$CONTENT_CONTENT_TABLE_EDITICON = "<a href='".$plugindir."admin_content_config.php?content.edit.".$row['content_id']."'>".CONTENT_ICON_EDIT."</a>";
return $CONTENT_CONTENT_TABLE_EDITICON;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_RATING
global $CONTENT_CONTENT_TABLE_RATING, $content_pref, $qs, $row, $rater, $plugintable, $mainparent;
if(($content_pref["content_content_rating"] && $row['content_rate']) || $content_pref["content_content_rating_all"] ){
	//return $rater->composerating($plugintable, $row['content_id'], $enter=TRUE, $userid=FALSE);
	$tmp = $_SERVER['QUERY_STRING'];
	$_SERVER['QUERY_STRING'] .= ".rated";
	$text = $rater->composerating($plugintable, $row['content_id'], $enter=TRUE, $userid=FALSE);
	$_SERVER['QUERY_STRING'] = $tmp;
	return $text;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_FILE
global $CONTENT_CONTENT_TABLE_FILE, $row, $content_file_path, $content_pref, $mainparent;
if($content_pref["content_content_attach"]){
$filestmp = explode("[file]", $row['content_file']);
foreach($filestmp as $key => $value) { 
	if($value == "") { 
		unset($filestmp[$key]); 
	} 
} 
$files = array_values($filestmp);
$content_files_popup_name = str_replace("'", "", $row['content_heading']);
$file = "";
$filesexisting = "0";
for($i=0;$i<count($files);$i++){
	if(file_exists($content_file_path.$files[$i])){
		$filesexisting = $filesexisting+1;
		$file .= "<a href='".$content_file_path.$files[$i]."' rel='external'>".CONTENT_ICON_FILE."</a> ";						
	}else{
		$file .= "&nbsp;";
	}
}
$CONTENT_CONTENT_TABLE_FILE = ($filesexisting == "0" ? "" : CONTENT_LAN_41." ".($filesexisting == 1 ? CONTENT_LAN_42 : CONTENT_LAN_43)." ".$file." ");
return $CONTENT_CONTENT_TABLE_FILE;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_SCORE
global $CONTENT_CONTENT_TABLE_SCORE, $row;
$score = $row['content_score'];
if($score){
	$height = "height:8px;";
	$img = "";
	$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
	$img .= "<img src='".e_PLUGIN."content/images/score.png' alt='' style='$height width:".$score."px; border:0;' />";
	$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
	if($score < 100){
		$empty = 100-$score;
		$img .= "<img src='".e_PLUGIN."content/images/score_empty.png' alt='' style='$height width:".$empty."px; border:0;' />";
	}
	$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
	return $img." ".$score;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_SUMMARY
global $CONTENT_CONTENT_TABLE_SUMMARY;
return $CONTENT_CONTENT_TABLE_SUMMARY;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_TEXT
global $CONTENT_CONTENT_TABLE_TEXT;
return $CONTENT_CONTENT_TABLE_TEXT;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_IMAGES
global $CONTENT_CONTENT_TABLE_IMAGES, $row, $content_image_path, $aa, $tp, $authordetails, $content_pref, $mainparent;
if($content_pref["content_content_images"]){
$authordetails = $aa -> getAuthor($row['content_author']);
$imagestmp = explode("[img]", $row['content_image']);
foreach($imagestmp as $key => $value) { 
	if($value == "") { 
		unset($imagestmp[$key]); 
	} 
} 
$images = array_values($imagestmp);
$content_image_popup_name = $row['content_heading'];
$CONTENT_CONTENT_TABLE_IMAGES = "";
require_once(e_HANDLER."popup_handler.php");
$pp = new popup;
$gen = new convert;
$datestamp = preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "long"));
for($i=0;$i<count($images);$i++){		
	$oSrc = $content_image_path.$images[$i];
	$oSrcThumb = $content_image_path."thumb_".$images[$i];

	$oIconWidth = (isset($content_pref["content_upload_image_size_thumb"]) && $content_pref["content_upload_image_size_thumb"] ? $content_pref["content_upload_image_size_thumb"] : "100");
	
	$oMaxWidth = (isset($content_pref["content_upload_image_size"]) && $content_pref["content_upload_image_size"] ? $content_pref["content_upload_image_size"] : "500");
	
	$subheading	= $tp -> toHTML($row['content_subheading'], TRUE);
	$popupname	= $tp -> toHTML($content_image_popup_name, TRUE);
	$author		= $tp -> toHTML($authordetails[1], TRUE);
	$oTitle		= $popupname." ".($i+1);
	$oText		= $popupname." ".($i+1)."<br />".$subheading."<br />".$author." (".$datestamp.")";
	$CONTENT_CONTENT_TABLE_IMAGES .= $pp -> popup($oSrc, $oSrcThumb, $oIconWidth, $oMaxWidth, $oTitle, $oText);
}
return $CONTENT_CONTENT_TABLE_IMAGES;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_CUSTOM_TAGS
global $CONTENT_CONTENT_TABLE_CUSTOM_TAGS;
return $CONTENT_CONTENT_TABLE_CUSTOM_TAGS;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_PAGENAMES
global $CONTENT_CONTENT_TABLE_PAGENAMES;
return $CONTENT_CONTENT_TABLE_PAGENAMES;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_NEXT_PAGE
global $CONTENT_CONTENT_TABLE_NEXT_PAGE;
return $CONTENT_CONTENT_TABLE_NEXT_PAGE;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_PREV_PAGE
global $CONTENT_CONTENT_TABLE_PREV_PAGE;
return $CONTENT_CONTENT_TABLE_PREV_PAGE;
SC_END




// PRINT PAGE ------------------------------------------------

//content images (from uploaded area) used in the print page
SC_BEGIN CONTENT_PRINT_IMAGES
global $CONTENT_PRINT_IMAGES, $row, $content_image_path, $tp, $content_pref, $mainparent;
if($content_pref["content_content_images"]){
$imagestmp = explode("[img]", $row['content_image']);
foreach($imagestmp as $key => $value) { 
	if($value == "") { 
		unset($imagestmp[$key]); 
	} 
} 
$images = array_values($imagestmp);
$CONTENT_PRINT_IMAGES = "";
for($i=0;$i<count($images);$i++){		
	$oSrc = $content_image_path.$images[$i];
	$oSrcThumb = $content_image_path."thumb_".$images[$i];

	$iconwidth = (isset($content_pref["content_upload_image_size_thumb"]) && $content_pref["content_upload_image_size_thumb"] ? $content_pref["content_upload_image_size_thumb"] : "100");
	if($iconwidth){
		$style = "style='width:".$iconwidth."px;'";
	}
	
	//use $image if $thumb doesn't exist
	if(file_exists($oSrc)){
		if(!file_exists($oSrcThumb)){
			$thumb = $oSrc;
		}else{
			$thumb = $oSrcThumb;
		}
		$CONTENT_PRINT_IMAGES .= "<img src='".$thumb."' ".$style." alt='' /><br /><br />";
	}
}
return $CONTENT_PRINT_IMAGES;
}
SC_END


// PDF PAGE ------------------------------------------------

//content images (from uploaded area) used in the pdf creation
SC_BEGIN CONTENT_PDF_IMAGES
global $CONTENT_PDF_IMAGES, $row, $content_image_path, $tp, $content_pref, $mainparent;
if($content_pref["content_content_images"]){
$imagestmp = explode("[img]", $row['content_image']);
foreach($imagestmp as $key => $value) { 
	if($value == "") { 
		unset($imagestmp[$key]); 
	} 
} 
$images = array_values($imagestmp);
$CONTENT_PDF_IMAGES = "";
for($i=0;$i<count($images);$i++){		
	$oSrc = $content_image_path.$images[$i];
	$oSrcThumb = $content_image_path."thumb_".$images[$i];

	$iconwidth = (isset($content_pref["content_upload_image_size_thumb"]) && $content_pref["content_upload_image_size_thumb"] ? $content_pref["content_upload_image_size_thumb"] : "100");
	if($iconwidth){
		$style = "style='width:".$iconwidth."px;'";
	}
	
	//use $image if $thumb doesn't exist
	if(file_exists($oSrc)){
		if(!file_exists($oSrcThumb)){
			$thumb = $oSrc;
		}else{
			$thumb = $oSrcThumb;
		}
		$thumb = $oSrc;
		$CONTENT_PDF_IMAGES .= "<img src='".$thumb."' ".$style." alt='' />";
	}
}
return $CONTENT_PDF_IMAGES;
}
SC_END

*/
?>
