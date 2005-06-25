<?php
include_once(e_HANDLER.'shortcode_handler.php');
$content_shortcodes = e_shortcode::parse_scbatch(__FILE__);
/*

// CONTENT_TYPE_TABLE ------------------------------------------------
SC_BEGIN CONTENT_TYPE_TABLE_TOTAL
global $contenttotal;
return $contenttotal." ".($contenttotal == 1 ? CONTENT_LAN_53 : CONTENT_LAN_54);
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_HEADING
global $CONTENT_TYPE_TABLE_HEADING, $contenttotal, $row, $tp;
$row['content_heading'] = $tp -> toHTML($row['content_heading'], TRUE, "");
return ($contenttotal != "0" ? "<a href='".e_SELF."?recent.".$row['content_id']."'>".$row['content_heading']."</a>" : $row['content_heading'] );
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_SUBHEADING
global $CONTENT_TYPE_TABLE_SUBHEADING, $contenttotal, $row, $tp;
$row['content_subheading'] = $tp -> toHTML($row['content_subheading'], TRUE, "");
return ($row['content_subheading'] ? $row['content_subheading'] : "");
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_ICON
global $CONTENT_TYPE_TABLE_ICON, $contenttotal, $row, $aa, $content_cat_icon_path_large, $content_pref;
if($contenttotal != "0"){
	$CONTENT_TYPE_TABLE_ICON = $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "recent.".$row['content_id'], "", $content_pref["content_blank_caticon_{$row['content_id']}"]);
}else{
	$CONTENT_TYPE_TABLE_ICON = $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "", "", $content_pref["content_blank_caticon_{$row['content_id']}"]);
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
if($content_pref["content_top_icon_{$mainparent}"]){

$width = (isset($content_pref["content_upload_icon_size_{$mainparent}"]) && $content_pref["content_upload_icon_size_{$mainparent}"] ? $content_pref["content_upload_icon_size_{$mainparent}"] : "100");

return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "content.".$row['content_id'], $width, $content_pref["content_blank_icon_{$mainparent}"]);
}
SC_END

SC_BEGIN CONTENT_TOP_TABLE_AUTHOR
global $CONTENT_TOP_TABLE_AUTHOR, $content_pref, $qs, $row, $aa, $mainparent;
if($content_pref["content_top_authorname_{$mainparent}"] || $content_pref["content_top_authoremail_{$mainparent}"] || $content_pref["content_top_authoricon_{$mainparent}"] || $content_pref["content_top_authorprofile_{$mainparent}"]){
	$authordetails = $aa -> getAuthor($row['content_author']);
	if($content_pref["content_top_authorname_{$mainparent}"]){
		if(isset($content_pref["content_top_authoremail_{$mainparent}"]) && $authordetails[2]){
			if($authordetails[0] == "0"){
				if($content_pref["content_top_authoremail_nonmember_{$mainparent}"] && strpos($authordetails[2], "@") ){
					$CONTENT_TOP_TABLE_AUTHOR = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
				}else{
					$CONTENT_TOP_TABLE_AUTHOR = $authordetails[1];
				}
			}else{
				$CONTENT_TOP_TABLE_AUTHOR = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
			}
		}else{
			$CONTENT_TOP_TABLE_AUTHOR = $authordetails[1];
		}
		if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0" && isset($content_pref["content_top_authorprofile_{$mainparent}"]) && $content_pref["content_top_authorprofile_{$mainparent}"]){
			$CONTENT_TOP_TABLE_AUTHOR .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
		}
	}
	if(isset($content_pref["content_top_authoricon_{$mainparent}"]) && $content_pref["content_top_authoricon_{$mainparent}"]){
		$CONTENT_TOP_TABLE_AUTHOR .= " <a href='".e_SELF."?author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
	}
return $CONTENT_TOP_TABLE_AUTHOR;
}
SC_END

SC_BEGIN CONTENT_TOP_TABLE_RATING
global $CONTENT_TOP_TABLE_RATING, $thisratearray;
$rating = "";
$rating .= $thisratearray[3]." ";
for($c=1; $c<= $thisratearray[4]; $c++){
	$rating .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
}
if($thisratearray[4] < 10){
	for($c=9; $c>=$thisratearray[4]; $c--){
		$rating .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
	}
}
$rating .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
return $rating;
SC_END


// CONTENT_SCORE_TABLE ------------------------------------------------
SC_BEGIN CONTENT_SCORE_TABLE_HEADING
global $CONTENT_SCORE_TABLE_HEADING, $row, $qs;
return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_SCORE_TABLE_ICON
global $CONTENT_SCORE_TABLE_ICON, $aa, $row, $content_pref, $content_icon_path, $qs, $mainparent;
if(isset($content_pref["content_score_icon_{$mainparent}"]) && $content_pref["content_score_icon_{$mainparent}"]){

$width = (isset($content_pref["content_upload_icon_size_{$mainparent}"]) && $content_pref["content_upload_icon_size_{$mainparent}"] ? $content_pref["content_upload_icon_size_{$mainparent}"] : "100");

return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "content.".$row['content_id'], $width, $content_pref["content_blank_icon_{$mainparent}"]);
}
SC_END

SC_BEGIN CONTENT_SCORE_TABLE_AUTHOR
global $CONTENT_SCORE_TABLE_AUTHOR, $content_pref, $qs, $row, $aa, $mainparent;
if( (isset($content_pref["content_score_authorname_{$mainparent}"]) && $content_pref["content_score_authorname_{$mainparent}"]) || (isset($content_pref["content_score_authoremail_{$mainparent}"]) && $content_pref["content_score_authoremail_{$mainparent}"]) || (isset($content_pref["content_score_authoricon_{$mainparent}"]) && $content_pref["content_score_authoricon_{$mainparent}"]) || (isset($content_pref["content_score_authorprofile_{$mainparent}"]) && $content_pref["content_score_authorprofile_{$mainparent}"]) ){
	$authordetails = $aa -> getAuthor($row['content_author']);
	if($content_pref["content_score_authorname_{$mainparent}"]){
		if(isset($content_pref["content_score_authoremail_{$mainparent}"]) && $authordetails[2]){
			if($authordetails[0] == "0"){
				if($content_pref["content_score_authoremail_nonmember_{$mainparent}"] && strpos($authordetails[2], "@") ){
					$CONTENT_SCORE_TABLE_AUTHOR = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
				}else{
					$CONTENT_SCORE_TABLE_AUTHOR = $authordetails[1];
				}
			}else{
				$CONTENT_SCORE_TABLE_AUTHOR = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
			}
		}else{
			$CONTENT_SCORE_TABLE_AUTHOR = $authordetails[1];
		}
		if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0" && isset($content_pref["content_score_authorprofile_{$mainparent}"]) && $content_pref["content_score_authorprofile_{$mainparent}"]){
			$CONTENT_SCORE_TABLE_AUTHOR .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
		}
	}
	if(isset($content_pref["content_score_authoricon_{$mainparent}"]) && $content_pref["content_score_authoricon_{$mainparent}"]){
		$CONTENT_SCORE_TABLE_AUTHOR .= " <a href='".e_SELF."?author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
	}
return $CONTENT_SCORE_TABLE_AUTHOR;
}
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
return $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "content.submit.".$row['content_id'], "", $content_pref["content_blank_caticon_{$row['content_id']}"]);
SC_END



// CONTENT_CONTENT_TABLEMANAGER ------------------------------------------------
SC_BEGIN CONTENT_CONTENTMANAGER_ICONNEW
global $CONTENT_CONTENTMANAGER_ICONNEW, $row;
return "<a href='".e_SELF."?content.create.".$row['content_id']."'>".CONTENT_ICON_NEW."</a>";
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_CATEGORY
global $CONTENT_CONTENTMANAGER_CATEGORY, $row;
return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_ICONEDIT
global $CONTENT_CONTENTMANAGER_ICONEDIT, $row;
return "<a href='".e_SELF."?content.".$row['content_id']."'>".CONTENT_ICON_EDIT."</a>";
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
if($content_pref["content_author_amount_{$mainparent}"]){
$CONTENT_AUTHOR_TABLE_TOTAL = $totalcontent." ".($totalcontent==1 ? CONTENT_LAN_53 : CONTENT_LAN_54);
return $CONTENT_AUTHOR_TABLE_TOTAL;
}
SC_END


SC_BEGIN CONTENT_AUTHOR_TABLE_LASTITEM
global $CONTENT_AUTHOR_TABLE_LASTITEM, $gen, $row, $mainparent, $content_pref;
if($content_pref["content_author_lastitem_{$mainparent}"]){
if(!is_object($gen)){ $gen = new convert; }
$CONTENT_AUTHOR_TABLE_LASTITEM = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "short"));
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
global $CONTENT_CAT_TABLE_ICON, $aa, $row, $content_pref, $qs, $content_cat_icon_path_large, $mainparent;
if(isset($content_pref["content_catall_icon_{$mainparent}"]) && $content_pref["content_catall_icon_{$mainparent}"]){
return $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "cat.".$row['content_id'], "", $content_pref["content_blank_caticon_{$mainparent}"]);
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_HEADING
global $CONTENT_CAT_TABLE_HEADING, $row;
return "<a href='".e_SELF."?cat.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_CAT_TABLE_AMOUNT
global $CONTENT_CAT_TABLE_AMOUNT, $aa, $row, $keytocount, $mainparent, $content_pref;
if(isset($content_pref["content_catall_amount_{$mainparent}"]) && $content_pref["content_catall_amount_{$mainparent}"]){
$n = $aa -> countCatItems($keytocount);
$CONTENT_CAT_TABLE_AMOUNT = $n." ".($n == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54);
return $CONTENT_CAT_TABLE_AMOUNT;
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_SUBHEADING
global $CONTENT_CAT_TABLE_SUBHEADING, $row, $tp, $mainparent, $content_pref;
if(isset($content_pref["content_catall_subheading_{$mainparent}"]) && $content_pref["content_catall_subheading_{$mainparent}"]){
return ($row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_DATE
global $CONTENT_CAT_TABLE_DATE, $gen, $row, $mainparent, $content_pref, $gen;
if(isset($content_pref["content_catall_date_{$mainparent}"]) && $content_pref["content_catall_date_{$mainparent}"]){
if(!is_object($gen)){ $gen = new convert; }
$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "long"));
$DATE = ($datestamp != "" ? $datestamp : "");
return $DATE;
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_AUTHORDETAILS
global $CONTENT_CAT_TABLE_AUTHORDETAILS, $content_pref, $qs, $row, $aa, $mainparent;
if($content_pref["content_catall_authorname_{$mainparent}"] || $content_pref["content_catall_authoremail_{$mainparent}"] || (isset($content_pref["content_catall_authoricon_{$mainparent}"]) && $content_pref["content_catall_authoricon_{$mainparent}"]) || $content_pref["content_catall_authorprofile_{$mainparent}"]){
	$authordetails = $aa -> getAuthor($row['content_author']);
	if($content_pref["content_catall_authorname_{$mainparent}"]){
		if(isset($content_pref["content_catall_authoremail_{$mainparent}"]) && $authordetails[2]){
			if($authordetails[0] == "0"){
				if($content_pref["content_catall_authoremail_nonmember_{$mainparent}"] && strpos($authordetails[2], "@") ){
					$AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
				}else{
					$AUTHORDETAILS = $authordetails[1];
				}
			}else{
				$AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
			}
		}else{
			$AUTHORDETAILS = $authordetails[1];
		}
		if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0" && isset($content_pref["content_catall_authorprofile_{$mainparent}"]) && $content_pref["content_catall_authorprofile_{$mainparent}"]){
			$AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
		}
	}
	if(isset($content_pref["content_catall_authoricon_{$mainparent}"]) && $content_pref["content_catall_authoricon_{$mainparent}"]){
		$AUTHORDETAILS .= " <a href='".e_SELF."?author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
	}
return $AUTHORDETAILS;
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_EPICONS
global $CONTENT_CAT_TABLE_EPICONS, $row, $tp, $mainparent, $content_pref;
$EPICONS = "";
if($row['content_pe'] && isset($content_pref["content_catall_peicon_{$mainparent}"]) && $content_pref["content_catall_peicon_{$mainparent}"]){
	$EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_72."^plugin:content.".$row['content_id']."}");
	$EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_72."^plugin:content.".$row['content_id']."}");
	$EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
return $EPICONS;
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_COMMENT
global $CONTENT_CAT_TABLE_COMMENT, $row, $qs, $comment_total, $mainparent, $content_pref, $plugintable;
if($row['content_comment'] && isset($content_pref["content_catall_comment_{$mainparent}"]) && $content_pref["content_catall_comment_{$mainparent}"]){
$sqlc = new db;
$comment_total = $sqlc -> db_Select("comments", "*",  "comment_item_id='".$row['content_id']."' AND comment_type='".$plugintable."' AND comment_pid='0' ");
return "<a style='text-decoration:none;' href='".e_SELF."?cat.".$row['content_id'].".comment'>".CONTENT_LAN_57." ".$comment_total."</a>";
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_TEXT
global $CONTENT_CAT_TABLE_TEXT, $row, $tp, $mainparent, $content_pref;
if($row['content_text'] && isset($content_pref["content_catall_text_{$mainparent}"]) && $content_pref["content_catall_text_{$mainparent}"] && $content_pref["content_catall_text_char_{$mainparent}"] > 0){
	$rowtext = preg_replace("/\[newpage.*?]/si", " ", $row['content_text']);
	$rowtext = $tp->toHTML($rowtext, TRUE, "nobreak");
	
	$rowtext = strip_tags($rowtext);
	$words = explode(" ", $rowtext);
	$CONTENT_CAT_TABLE_TEXT = implode(" ", array_slice($words, 0, $content_pref["content_catall_text_char_{$mainparent}"]));
	if($content_pref["content_catall_text_link_{$mainparent}"]){
		$CONTENT_CAT_TABLE_TEXT .= " <a href='".e_SELF."?cat.".$row['content_id']."'>".$content_pref["content_catall_text_post_{$mainparent}"]."</a>";
	}else{
		$CONTENT_CAT_TABLE_TEXT .= " ".$content_pref["content_catall_text_post_{$mainparent}"];
	}
return $CONTENT_CAT_TABLE_TEXT;
}
SC_END

SC_BEGIN CONTENT_CAT_TABLE_RATING
global $CONTENT_CAT_TABLE_RATING, $row, $rater, $mainparent, $content_pref, $plugintable;
$RATING = "";
if($row['content_rate'] && isset($content_pref["content_catall_rating_{$mainparent}"]) && $content_pref["content_catall_rating_{$mainparent}"]){
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
if(isset($content_pref["content_cat_icon_{$mainparent}"]) && $content_pref["content_cat_icon_{$mainparent}"]){
return $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "", "", $content_pref["content_blank_caticon_{$mainparent}"]);;
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_HEADING
global $CONTENT_CAT_LIST_TABLE_HEADING, $tp, $row;
return ($row['content_heading'] ? $tp -> toHTML($row['content_heading'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_SUMMARY
global $CONTENT_CAT_LIST_TABLE_SUMMARY, $tp, $row, $mainparent;
return ($row['content_summary'] ? $tp -> toHTML($row['content_summary'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_TEXT
global $CONTENT_CAT_LIST_TABLE_TEXT, $tp, $row, $mainparent, $content_pref;
if($row['content_text'] && isset($content_pref["content_cat_text_{$mainparent}"]) && $content_pref["content_cat_text_{$mainparent}"] && $content_pref["content_cat_text_char_{$mainparent}"] > 0){
	$rowtext = preg_replace("/\[newpage.*?]/si", " ", $row['content_text']);
	$rowtext = $tp->toHTML($rowtext, TRUE, "nobreak");
	
	$rowtext = strip_tags($rowtext);
	$words = explode(" ", $rowtext);
	$CONTENT_CAT_LIST_TABLE_TEXT = implode(" ", array_slice($words, 0, $content_pref["content_cat_text_char_{$mainparent}"]));
	if($content_pref["content_cat_text_link_{$mainparent}"]){
		$CONTENT_CAT_LIST_TABLE_TEXT .= " <a href='".e_SELF."?cat.".$row['content_id']."'>".$content_pref["content_cat_text_post_{$mainparent}"]."</a>";
	}else{
		$CONTENT_CAT_LIST_TABLE_TEXT .= " ".$content_pref["content_cat_text_post_{$mainparent}"];
	}
return $CONTENT_CAT_LIST_TABLE_TEXT;
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_AMOUNT
global $CONTENT_CAT_LIST_TABLE_AMOUNT, $aa, $row, $mainparent, $content_pref;
if(isset($content_pref["content_cat_amount_{$mainparent}"]) && $content_pref["content_cat_amount_{$mainparent}"]){
$n = $aa -> countCatItems($row['content_id']);
$n = $n." ".($n == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54);
return $n;
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_SUBHEADING
global $CONTENT_CAT_LIST_TABLE_SUBHEADING, $tp, $row, $mainparent, $content_pref;
if(isset($content_pref["content_cat_subheading_{$mainparent}"]) && $content_pref["content_cat_subheading_{$mainparent}"]){
return ($row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_DATE
global $CONTENT_CAT_LIST_TABLE_DATE, $row, $gen, $mainparent, $content_pref, $gen;
if(isset($content_pref["content_cat_date_{$mainparent}"]) && $content_pref["content_cat_date_{$mainparent}"]){
if(!is_object($gen)){ $gen = new convert; }
$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "long"));
return ($datestamp != "" ? $datestamp : "");
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_AUTHORDETAILS
global $CONTENT_CAT_LIST_TABLE_AUTHORDETAILS, $content_pref, $qs, $row, $aa, $mainparent;
if($content_pref["content_cat_authorname_{$mainparent}"] || $content_pref["content_cat_authoremail_{$mainparent}"] || (isset($content_pref["content_cat_authoricon_{$mainparent}"]) && $content_pref["content_cat_authoricon_{$mainparent}"]) || $content_pref["content_cat_authorprofile_{$mainparent}"]){
	$authordetails = $aa -> getAuthor($row['content_author']);
	if($content_pref["content_cat_authorname_{$mainparent}"]){
		if(isset($content_pref["content_cat_authoremail_{$mainparent}"]) && $authordetails[2]){
			if($authordetails[0] == "0"){
				if($content_pref["content_cat_authoremail_nonmember_{$mainparent}"] && strpos($authordetails[2], "@") ){
					$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
				}else{
					$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS = $authordetails[1];
				}
			}else{
				$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
			}
		}else{
			$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS = $authordetails[1];
		}
		if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0" && isset($content_pref["content_cat_authorprofile_{$mainparent}"]) && $content_pref["content_cat_authorprofile_{$mainparent}"]){
			$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
		}
	}
	if(isset($content_pref["content_cat_authoricon_{$mainparent}"]) && $content_pref["content_cat_authoricon_{$mainparent}"]){
		$CONTENT_CAT_LIST_TABLE_AUTHORDETAILS .= " <a href='".e_SELF."?author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
	}
return $CONTENT_CAT_LIST_TABLE_AUTHORDETAILS;
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_EPICONS
global $CONTENT_CAT_LIST_TABLE_EPICONS, $row, $tp, $qs, $mainparent, $content_pref;
$EPICONS = "";
if( (isset($content_pref["content_cat_peicon_{$mainparent}"]) && $content_pref["content_cat_peicon_{$mainparent}"] && $row['content_pe']) || (isset($content_pref["content_cat_peicon_all_{$mainparent}"]) && $content_pref["content_cat_peicon_all_{$mainparent}"])){
	$EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_72."^plugin:content.$qs[1]}");
	$EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_72."^plugin:content.$qs[1]}");
	$EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.$qs[1]}");
return $EPICONS;
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_COMMENT
global $CONTENT_CAT_LIST_TABLE_COMMENT, $qs, $row, $comment_total, $mainparent, $content_pref, $sql, $plugintable;
if($row['content_comment'] && isset($content_pref["content_cat_comment_{$mainparent}"]) && $content_pref["content_cat_comment_{$mainparent}"]){
	$comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='".$qs[1]."' AND comment_type='".$plugintable."' AND comment_pid='0' ");
	return "<a style='text-decoration:none;' href='".e_SELF."?cat.".$qs[1].".comment'>".CONTENT_LAN_57." ".$comment_total."</a>";
}
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_RATING
global $CONTENT_CAT_LIST_TABLE_RATING, $row, $rater, $content_pref, $mainparent, $plugintable;
$RATING = "";
if( (isset($content_pref["content_cat_rating_all_{$mainparent}"]) && $content_pref["content_cat_rating_all_{$mainparent}"]) || (isset($content_pref["content_cat_rating_{$mainparent}"]) && $content_pref["content_cat_rating_{$mainparent}"] && $row['content_rate'])){
	return $rater->composerating($plugintable, $row['content_id'], $enter=TRUE, $userid=FALSE);
}
return $RATING;
SC_END






// CONTENT_CAT_LISTSUB ------------------------------------------------
SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_ICON
global $CONTENT_CAT_LISTSUB_TABLE_ICON, $aa, $row, $content_pref, $qs, $mainparent, $content_cat_icon_path_small;
if(isset($content_pref["content_catsub_icon_{$mainparent}"]) && $content_pref["content_catsub_icon_{$mainparent}"]){
return $aa -> getIcon("catsmall", $row['content_icon'], $content_cat_icon_path_small, "cat.".$row['content_id'], "", $content_pref["content_blank_caticon_{$mainparent}"]);
}
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_HEADING
global $CONTENT_CAT_LISTSUB_TABLE_HEADING, $qs, $row, $tp;
return "<a href='".e_SELF."?cat.".$row['content_id']."'>".$tp -> toHTML($row['content_heading'], TRUE, "")."</a>";
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_AMOUNT
global $CONTENT_CAT_LISTSUB_TABLE_AMOUNT, $aa, $row, $content_pref, $mainparent;
if(isset($content_pref["content_catsub_amount_{$mainparent}"]) && $content_pref["content_catsub_amount_{$mainparent}"]){
$n = $aa -> countCatItems($row['content_id']);
$n = $n." ".($n == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54);
return $n;
}
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_SUBHEADING
global $CONTENT_CAT_LISTSUB_TABLE_SUBHEADING, $row, $tp, $content_pref, $mainparent;
if(isset($content_pref["content_catsub_subheading_{$mainparent}"]) && $content_pref["content_catsub_subheading_{$mainparent}"]){
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
$width = (isset($content_pref["content_upload_icon_size_{$mainparent}"]) && $content_pref["content_upload_icon_size_{$mainparent}"] ? $content_pref["content_upload_icon_size_{$mainparent}"] : "100");
return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "content.".$row['content_id'], $width, $content_pref["content_blank_icon_{$mainparent}"]);
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
$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "short"));
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
if(isset($content_pref["content_list_icon_{$mainparent}"]) && $content_pref["content_list_icon_{$mainparent}"]){

$width = (isset($content_pref["content_upload_icon_size_{$mainparent}"]) && $content_pref["content_upload_icon_size_{$mainparent}"] ? $content_pref["content_upload_icon_size_{$mainparent}"] : "100");

return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "content.".$row['content_id'], $width, $content_pref["content_blank_icon_{$mainparent}"]);
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_HEADING
global $CONTENT_RECENT_TABLE_HEADING, $row;
return ($row['content_heading'] ? "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>" : "");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_SUBHEADING
global $CONTENT_RECENT_TABLE_SUBHEADING, $content_pref, $qs, $row, $mainparent;
if (isset($content_pref["content_list_subheading_{$mainparent}"]) && $content_pref["content_list_subheading_{$mainparent}"] && $row['content_subheading'] && $content_pref["content_list_subheading_char_{$mainparent}"] && $content_pref["content_list_subheading_char_{$mainparent}"] != "" && $content_pref["content_list_subheading_char_{$mainparent}"] != "0"){
	if(strlen($row['content_subheading']) > $content_pref["content_list_subheading_char_{$mainparent}"]) {
		$row['content_subheading'] = substr($row['content_subheading'], 0, $content_pref["content_list_subheading_char_{$mainparent}"]).$content_pref["content_list_subheading_post_{$mainparent}"];
	}
	$CONTENT_RECENT_TABLE_SUBHEADING = ($row['content_subheading'] != "" && $row['content_subheading'] != " " ? $row['content_subheading'] : "");
}else{
	$CONTENT_RECENT_TABLE_SUBHEADING = ($row['content_subheading'] ? $row['content_subheading'] : "");
}
return $CONTENT_RECENT_TABLE_SUBHEADING;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_SUMMARY
global $CONTENT_RECENT_TABLE_SUMMARY, $content_pref, $qs, $row, $mainparent;
if (isset($content_pref["content_list_summary_{$mainparent}"]) && $content_pref["content_list_summary_{$mainparent}"]){
	if($row['content_summary'] && $content_pref["content_list_summary_char_{$mainparent}"] && $content_pref["content_list_summary_char_{$mainparent}"] != "" && $content_pref["content_list_summary_char_{$mainparent}"] != "0"){
		if(strlen($row['content_summary']) > $content_pref["content_list_summary_char_{$mainparent}"]) {
			$row['content_summary'] = substr($row['content_summary'], 0, $content_pref["content_list_summary_char_{$mainparent}"]).$content_pref["content_list_summary_post_{$mainparent}"];
		}
		$CONTENT_RECENT_TABLE_SUMMARY = ($row['content_summary'] != "" && $row['content_summary'] != " " ? $row['content_summary'] : "");
	}else{
		$CONTENT_RECENT_TABLE_SUMMARY = ($row['content_summary'] ? $row['content_summary'] : "");
	}
return $CONTENT_RECENT_TABLE_SUMMARY;
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_TEXT
global $CONTENT_RECENT_TABLE_TEXT, $content_pref, $qs, $row, $mainparent, $tp;
if(isset($content_pref["content_list_text_{$mainparent}"]) && $content_pref["content_list_text_{$mainparent}"] && $content_pref["content_list_text_char_{$mainparent}"] > 0){
	$rowtext = preg_replace("/\[newpage.*?]/si", " ", $row['content_text']);
	//$rowtext = str_replace ("<br />", " ", $rowtext);
	$rowtext = $tp->toHTML($rowtext, TRUE, "nobreak");
	$rowtext = strip_tags($rowtext);
	$words = explode(" ", $rowtext);
	$CONTENT_RECENT_TABLE_TEXT = implode(" ", array_slice($words, 0, $content_pref["content_list_text_char_{$mainparent}"]));
	if($CONTENT_RECENT_TABLE_TEXT){
		if($content_pref["content_list_text_link_{$mainparent}"]){
			$CONTENT_RECENT_TABLE_TEXT .= " <a href='".e_SELF."?content.".$row['content_id']."'>".$content_pref["content_list_text_post_{$mainparent}"]."</a>";
		}else{
			$CONTENT_RECENT_TABLE_TEXT .= " ".$content_pref["content_list_text_post_{$mainparent}"];
		}
	}
}
return $CONTENT_RECENT_TABLE_TEXT;
SC_END


SC_BEGIN CONTENT_RECENT_TABLE_DATE
global $CONTENT_RECENT_TABLE_DATE, $content_pref, $qs, $row, $mainparent;
if(isset($content_pref["content_list_date_{$mainparent}"]) && $content_pref["content_list_date_{$mainparent}"]){
$datestyle = ($content_pref["content_list_datestyle_{$mainparent}"] ? $content_pref["content_list_datestyle_{$mainparent}"] : "%d %b %Y");
return strftime($datestyle, $row['content_datestamp']);
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_EPICONS
global $CONTENT_RECENT_TABLE_EPICONS, $tp, $content_pref, $qs, $row, $mainparent;
$CONTENT_RECENT_TABLE_EPICONS = "";
if(isset($content_pref["content_list_peicon_{$mainparent}"]) && $content_pref["content_list_peicon_{$mainparent}"]){
	if($row['content_pe'] || isset($content_pref["content_list_peicon_all_{$mainparent}"]) && $content_pref["content_list_peicon_all_{$mainparent}"]){
		$CONTENT_RECENT_TABLE_EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
		$CONTENT_RECENT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
		$CONTENT_RECENT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
	}
}
return $CONTENT_RECENT_TABLE_EPICONS;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_AUTHORDETAILS
global $CONTENT_RECENT_TABLE_AUTHORDETAILS, $content_pref, $qs, $row, $aa, $mainparent;
if($content_pref["content_list_authorname_{$mainparent}"] || $content_pref["content_list_authoremail_{$mainparent}"] || (isset($content_pref["content_list_authoricon_{$mainparent}"]) && $content_pref["content_list_authoricon_{$mainparent}"]) || $content_pref["content_list_authorprofile_{$mainparent}"]){
	$authordetails = $aa -> getAuthor($row['content_author']);
	if($content_pref["content_list_authorname_{$mainparent}"]){
		if(isset($content_pref["content_list_authoremail_{$mainparent}"]) && $authordetails[2]){
			if($authordetails[0] == "0"){
				if($content_pref["content_list_authoremail_nonmember_{$mainparent}"] && strpos($authordetails[2], "@") ){
					$CONTENT_RECENT_TABLE_AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
				}else{
					$CONTENT_RECENT_TABLE_AUTHORDETAILS = $authordetails[1];
				}
			}else{
				$CONTENT_RECENT_TABLE_AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
			}
		}else{
			$CONTENT_RECENT_TABLE_AUTHORDETAILS = $authordetails[1];
		}
		if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0" && isset($content_pref["content_list_authorprofile_{$mainparent}"]) && $content_pref["content_list_authorprofile_{$mainparent}"]){
			$CONTENT_RECENT_TABLE_AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
		}
	}
	if(isset($content_pref["content_list_authoricon_{$mainparent}"]) && $content_pref["content_list_authoricon_{$mainparent}"]){
		$CONTENT_RECENT_TABLE_AUTHORDETAILS .= " <a href='".e_SELF."?author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
	}
return $CONTENT_RECENT_TABLE_AUTHORDETAILS;
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_EDITICON
global $CONTENT_RECENT_TABLE_EDITICON, $content_pref, $qs, $row, $mainparent, $plugindir;
if(ADMIN && getperms("P") && isset($content_pref["content_list_editicon_{$mainparent}"]) && $content_pref["content_list_editicon_{$mainparent}"]){
return $CONTENT_RECENT_TABLE_EDITICON = "<a href='".$plugindir."admin_content_config.php?content.edit.".$row['content_id']."'>".CONTENT_ICON_EDIT."</a>";
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_REFER
global $CONTENT_RECENT_TABLE_REFER, $content_pref, $qs, $row, $mainparent;
if($content_pref["content_log_{$mainparent}"] && $content_pref["content_list_refer_{$mainparent}"]){
	$refercounttmp = explode("^", $row['content_refer']);
	$CONTENT_RECENT_TABLE_REFER = ($refercounttmp[0] ? $refercounttmp[0] : "0");
return $CONTENT_RECENT_TABLE_REFER;
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_RATING
global $CONTENT_RECENT_TABLE_RATING, $rater, $row, $qs, $content_pref, $plugintable, $mainparent;
if($content_pref["content_list_rating_{$mainparent}"]){
	if($content_pref["content_list_rating_all_{$mainparent}"] || $row['content_rate']){
		return $rater->composerating($plugintable, $row['content_id'], $enter=TRUE, $userid=FALSE);
	}
}
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_PARENT
global $CONTENT_RECENT_TABLE_PARENT, $content_pref, $mainparent, $row, $array, $aa;
if(isset($content_pref["content_list_parent_{$mainparent}"]) && $content_pref["content_list_parent_{$mainparent}"]){
return $aa -> getCrumbItem($row['content_parent'], $array);
}
SC_END



// CONTENT_ARCHIVE_TABLE ------------------------------------------------
SC_BEGIN CONTENT_ARCHIVE_TABLE_LETTERS
global $CONTENT_ARCHIVE_TABLE_LETTERS, $content_pref, $mainparent;
if($content_pref["content_archive_letterindex_{$mainparent}"]){
return $CONTENT_ARCHIVE_TABLE_LETTERS;
}
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_HEADING
global $CONTENT_ARCHIVE_TABLE_HEADING, $row, $qs;
return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_DATE
global $CONTENT_ARCHIVE_TABLE_DATE, $row, $content_pref, $qs, $mainparent;
if(isset($content_pref["content_archive_date_{$mainparent}"]) && $content_pref["content_archive_date_{$mainparent}"]){
$datestyle = ($content_pref["content_archive_datestyle_{$mainparent}"] ? $content_pref["content_archive_datestyle_{$mainparent}"] : "%d %b %Y");
return strftime($datestyle, $row['content_datestamp']);
}
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_AUTHOR
global $CONTENT_ARCHIVE_TABLE_AUTHOR, $row, $qs, $aa, $content_pref, $mainparent;
if( (isset($content_pref["content_archive_authorname_{$mainparent}"]) && $content_pref["content_archive_authorname_{$mainparent}"]) || (isset($content_pref["content_archive_authoremail_{$mainparent}"]) && $content_pref["content_archive_authoremail_{$mainparent}"]) || (isset($content_pref["content_archive_authoricon_{$mainparent}"]) && $content_pref["content_archive_authoricon_{$mainparent}"]) || (isset($content_pref["content_archive_authorprofile_{$mainparent}"]) && $content_pref["content_archive_authorprofile_{$mainparent}"]) ){
	$authordetails = $aa -> getAuthor($row['content_author']);
	if($content_pref["content_archive_authorname_{$mainparent}"]){
		if(isset($content_pref["content_archive_authoremail_{$mainparent}"]) && $authordetails[2]){
			if($authordetails[0] == "0"){
				if($content_pref["content_archive_authoremail_nonmember_{$mainparent}"] && strpos($authordetails[2], "@") ){
					$CONTENT_ARCHIVE_TABLE_AUTHOR = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
				}else{
					$CONTENT_ARCHIVE_TABLE_AUTHOR = $authordetails[1];
				}
			}else{
				$CONTENT_ARCHIVE_TABLE_AUTHOR = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
			}
		}else{
			$CONTENT_ARCHIVE_TABLE_AUTHOR = $authordetails[1];
		}
		if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0" && isset($content_pref["content_archive_authorprofile_{$mainparent}"]) && $content_pref["content_archive_authorprofile_{$mainparent}"]){
			$CONTENT_ARCHIVE_TABLE_AUTHOR .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
		}
	}
	if(isset($content_pref["content_archive_authoricon_{$mainparent}"]) && $content_pref["content_archive_authoricon_{$mainparent}"]){
		$CONTENT_ARCHIVE_TABLE_AUTHOR .= " <a href='".e_SELF."?author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
	}
return $CONTENT_ARCHIVE_TABLE_AUTHOR;
}
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

SC_BEGIN CONTENT_CONTENT_TABLE_PARENT
global $CONTENT_CONTENT_TABLE_PARENT, $aa, $array, $row, $content_pref, $mainparent;
if(isset($content_pref["content_content_parent_{$mainparent}"]) && $content_pref["content_content_parent_{$mainparent}"]){
return $aa -> getCrumbItem($row['content_parent'], $array);
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_ICON
global $CONTENT_CONTENT_TABLE_ICON, $qs, $row, $aa, $content_pref, $content_icon_path, $mainparent;
if(isset($content_pref["content_content_icon_{$mainparent}"]) && $content_pref["content_content_icon_{$mainparent}"]){

$width = (isset($content_pref["content_upload_icon_size_{$mainparent}"]) && $content_pref["content_upload_icon_size_{$mainparent}"] ? $content_pref["content_upload_icon_size_{$mainparent}"] : "100");

return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "", $width, $content_pref["content_blank_icon_{$mainparent}"]);
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_HEADING
global $CONTENT_CONTENT_TABLE_HEADING, $row, $tp;
$CONTENT_CONTENT_TABLE_HEADING = ($row['content_heading'] ? $tp -> toHTML($row['content_heading'], TRUE, "") : "");
return $CONTENT_CONTENT_TABLE_HEADING;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_REFER
global $CONTENT_CONTENT_TABLE_REFER, $sql, $qs, $content_pref, $plugintable, $mainparent;
if(isset($content_pref["content_content_refer_{$mainparent}"]) && $content_pref["content_content_refer_{$mainparent}"]){
	$sql -> db_Select($plugintable, "content_refer", "content_id='".$qs[1]."' ");
	list($content_refer) = $sql -> db_Fetch();
	$refercounttmp = explode("^", $content_refer);
	$CONTENT_CONTENT_TABLE_REFER = ($refercounttmp[0] ? $refercounttmp[0] : "");
return $CONTENT_CONTENT_TABLE_REFER;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_SUBHEADING
global $CONTENT_CONTENT_TABLE_SUBHEADING, $row, $tp, $content_pref, $qs, $mainparent;
$CONTENT_CONTENT_TABLE_SUBHEADING = ($content_pref["content_content_subheading_{$mainparent}"] && $row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
return $CONTENT_CONTENT_TABLE_SUBHEADING;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_COMMENT
global $CONTENT_CONTENT_TABLE_COMMENT, $row, $plugintable, $content_pref, $qs, $sql, $mainparent;
if((isset($content_pref["content_content_comment_{$mainparent}"]) && $content_pref["content_content_comment_{$mainparent}"] && $row['content_comment']) || $content_pref["content_content_comment_all_{$mainparent}"] ){
	$CONTENT_CONTENT_TABLE_COMMENT = $sql -> db_Select("comments", "*",  "comment_item_id='".$qs[1]."' AND comment_type='".$plugintable."' AND comment_pid='0' ");
	if(!$CONTENT_CONTENT_TABLE_COMMENT){ $CONTENT_CONTENT_TABLE_COMMENT = "0"; }
return $CONTENT_CONTENT_TABLE_COMMENT;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_DATE
global $CONTENT_CONTENT_TABLE_DATE, $gen, $row, $qs, $content_pref, $mainparent;
if(isset($content_pref["content_content_date_{$mainparent}"]) && $content_pref["content_content_date_{$mainparent}"]){
	$gen = new convert;
	$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "long"));
	$CONTENT_CONTENT_TABLE_DATE = ($datestamp != "" ? $datestamp : "");
return $CONTENT_CONTENT_TABLE_DATE;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_AUTHORDETAILS
global $CONTENT_CONTENT_TABLE_AUTHORDETAILS, $content_pref, $qs, $row, $aa, $mainparent;
if($content_pref["content_content_authorname_{$mainparent}"] || $content_pref["content_content_authoremail_{$mainparent}"] || (isset($content_pref["content_content_authoricon_{$mainparent}"]) && $content_pref["content_content_authoricon_{$mainparent}"]) || $content_pref["content_content_authorprofile_{$mainparent}"]){
	$authordetails = $aa -> getAuthor($row['content_author']);
	if($content_pref["content_content_authorname_{$mainparent}"]){
		if(isset($content_pref["content_content_authoremail_{$mainparent}"]) && $authordetails[2]){
			if($authordetails[0] == "0"){
				if($content_pref["content_content_authoremail_nonmember_{$mainparent}"] && strpos($authordetails[2], "@") ){
					$CONTENT_CONTENT_TABLE_AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
				}else{
					$CONTENT_CONTENT_TABLE_AUTHORDETAILS = $authordetails[1];
				}
			}else{
				$CONTENT_CONTENT_TABLE_AUTHORDETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
			}
		}else{
			$CONTENT_CONTENT_TABLE_AUTHORDETAILS = $authordetails[1];
		}
		if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0" && isset($content_pref["content_content_authorprofile_{$mainparent}"]) && $content_pref["content_content_authorprofile_{$mainparent}"]){
			$CONTENT_CONTENT_TABLE_AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
		}
	}
	if(isset($content_pref["content_content_authoricon_{$mainparent}"]) && $content_pref["content_content_authoricon_{$mainparent}"]){
		$CONTENT_CONTENT_TABLE_AUTHORDETAILS .= " <a href='".e_SELF."?author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
	}
return $CONTENT_CONTENT_TABLE_AUTHORDETAILS;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_EPICONS
global $CONTENT_CONTENT_TABLE_EPICONS, $content_pref, $qs, $row, $tp, $mainparent;
$CONTENT_CONTENT_TABLE_EPICONS = "";
if(($content_pref["content_content_peicon_{$mainparent}"] && $row['content_pe']) || $content_pref["content_content_peicon_all_{$mainparent}"]){
	$CONTENT_CONTENT_TABLE_EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
	$CONTENT_CONTENT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
	$CONTENT_CONTENT_TABLE_EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
return $CONTENT_CONTENT_TABLE_EPICONS;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_EDITICON
global $CONTENT_CONTENT_TABLE_EDITICON, $content_pref, $qs, $row, $plugindir, $mainparent;
if(ADMIN && getperms("P") && isset($content_pref["content_content_editicon_{$mainparent}"])){
	$CONTENT_CONTENT_TABLE_EDITICON = "<a href='".$plugindir."admin_content_config.php?content.edit.".$row['content_id']."'>".CONTENT_ICON_EDIT."</a>";
return $CONTENT_CONTENT_TABLE_EDITICON;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_RATING
global $CONTENT_CONTENT_TABLE_RATING, $content_pref, $qs, $row, $rater, $plugintable, $mainparent;
if(($content_pref["content_content_rating_{$mainparent}"] && $row['content_rate']) || $content_pref["content_content_rating_all_{$mainparent}"] ){
return $rater->composerating($plugintable, $row['content_id'], $enter=TRUE, $userid=FALSE);
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_FILE
global $CONTENT_CONTENT_TABLE_FILE, $row, $content_file_path, $content_pref, $mainparent;
if($content_pref["content_content_attach_{$mainparent}"]){
$filestmp = explode("[file]", $row['content_file']);
foreach($filestmp as $key => $value) { 
	if($value == "") { 
		unset($filestmp[$key]); 
	} 
} 
$files = array_values($filestmp);
$content_files_popup_name = ereg_replace("'", "", $row['content_heading']);
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
if($content_pref["content_content_images_{$mainparent}"]){
$authordetails = $aa -> getAuthor($row['content_author']);
$imagestmp = explode("[img]", $row['content_image']);
foreach($imagestmp as $key => $value) { 
	if($value == "") { 
		unset($imagestmp[$key]); 
	} 
} 
$images = array_values($imagestmp);
$content_image_popup_name = ereg_replace("'", "", $row['content_heading']);
$CONTENT_CONTENT_TABLE_IMAGES = "";
$gen = new convert;
$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "long"));
for($i=0;$i<count($images);$i++){		
	$oSrc = $content_image_path.$images[$i];
	$oSrcThumb = $content_image_path."thumb_".$images[$i];

	$oIconWidth = (isset($content_pref["content_upload_image_size_thumb_{$mainparent}"]) && $content_pref["content_upload_image_size_thumb_{$mainparent}"] ? $content_pref["content_upload_image_size_thumb_{$mainparent}"] : "100");
	
	$oMaxWidth = (isset($content_pref["content_upload_image_size_{$mainparent}"]) && $content_pref["content_upload_image_size_{$mainparent}"] ? $content_pref["content_upload_image_size_{$mainparent}"] : "500");
	
	$oTitle = $content_image_popup_name." ".($i+1);
	$oText = $content_image_popup_name." ".($i+1)."<br />".$tp -> toHTML($row['content_subheading'], TRUE, "")."<br />".$authordetails[1]." (".$datestamp.")";
	$CONTENT_CONTENT_TABLE_IMAGES .= $aa -> popup($oSrc, $oSrcThumb, $oIconWidth, $oMaxWidth, $oTitle, $oText);
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


*/
?>
