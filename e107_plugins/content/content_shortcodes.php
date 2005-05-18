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
return ($contenttotal != "0" ? "<a href='".e_SELF."?type.".$row['content_id']."'>".$row['content_heading']."</a>" : $row['content_heading'] );
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_SUBHEADING
global $CONTENT_TYPE_TABLE_SUBHEADING, $contenttotal, $row, $tp;
$row['content_subheading'] = $tp -> toHTML($row['content_subheading'], TRUE, "");
return ($row['content_subheading'] ? $row['content_subheading'] : "");
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_ICON
global $CONTENT_TYPE_TABLE_ICON, $contenttotal, $row, $aa, $content_cat_icon_path_large, $content_pref;
if($contenttotal != "0"){
	$CONTENT_TYPE_TABLE_ICON = $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "type.".$row['content_id'], "", $content_pref["content_blank_caticon_{$row['content_id']}"]);
}else{
	$CONTENT_TYPE_TABLE_ICON = $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "", "", $content_pref["content_blank_caticon_{$row['content_id']}"]);
}
return $CONTENT_TYPE_TABLE_ICON;
SC_END


// CONTENT_TYPE_TABLE_SUBMIT ------------------------------------------------
SC_BEGIN CONTENT_TYPE_TABLE_SUBMIT_ICON
global $CONTENT_TYPE_TABLE_SUBMIT_ICON;
return "<a href='".e_PLUGIN."content/content_submit.php'>".CONTENT_ICON_SUBMIT."</a>";
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_SUBMIT_HEADING
global $CONTENT_TYPE_TABLE_SUBMIT_HEADING;
return "<a href='".e_PLUGIN."content/content_submit.php'>".CONTENT_LAN_65."</a>";
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_SUBMIT_SUBHEADING
global $CONTENT_TYPE_TABLE_SUBMIT_SUBHEADING;
return CONTENT_LAN_66;
SC_END


// CONTENT_TYPE_TABLE_MANAGER ------------------------------------------------
SC_BEGIN CONTENT_TYPE_TABLE_MANAGER_ICON
global $CONTENT_TYPE_TABLE_MANAGER_ICON;
return "<a href='".e_PLUGIN."content/content_manager.php'>".CONTENT_ICON_CONTENTMANAGER."</a>";
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_MANAGER_HEADING
global $CONTENT_TYPE_TABLE_MANAGER_HEADING;
return "<a href='".e_PLUGIN."content/content_manager.php'>".CONTENT_LAN_67."</a>";
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_MANAGER_SUBHEADING
global $CONTENT_TYPE_TABLE_MANAGER_SUBHEADING;
return CONTENT_LAN_68;
SC_END



// CONTENT_ARCHIVE_TABLE ------------------------------------------------
SC_BEGIN CONTENT_ARCHIVE_TABLE_HEADING
global $CONTENT_ARCHIVE_TABLE_HEADING, $row, $type_id;
return "<a href='".e_SELF."?type.".$type_id.".content.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_DATE
global $CONTENT_ARCHIVE_TABLE_DATE, $row, $content_pref, $type_id;
$datestyle = ($content_pref["content_archive_datestyle_{$type_id}"] ? $content_pref["content_archive_datestyle_{$type_id}"] : "%d %b %Y");
return strftime($datestyle, $row['content_datestamp']);
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_AUTHOR
global $CONTENT_ARCHIVE_TABLE_AUTHOR, $row, $type, $type_id, $aa;
$authordetails = $aa -> getAuthor($row['content_author']);
$author = " <a href='".e_SELF."?type.".$type_id.".author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0"){
	$author .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
}
$author .= ($authordetails[1] == "" ? "... ".CONTENT_LAN_29." ..." : $authordetails[1])." ";
return $author;
SC_END



// CONTENT_TOP_TABLE ------------------------------------------------
SC_BEGIN CONTENT_TOP_TABLE_HEADING
global $CONTENT_TOP_TABLE_HEADING, $row, $type, $type_id;
return "<a href='".e_PLUGIN."content/content.php?".$type.".".$type_id.".content.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_TOP_TABLE_ICON
global $CONTENT_TOP_TABLE_ICON, $aa, $row, $content_pref, $content_icon_path, $type, $type_id;
return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, $type.".".$type_id.".content.".$row['content_id'], "50", $content_pref["content_blank_icon_{$type_id}"]);
SC_END

SC_BEGIN CONTENT_TOP_TABLE_AUTHOR
global $CONTENT_TOP_TABLE_AUTHOR, $authordetails, $type, $type_id, $row;
	$CONTENT_TOP_TABLE_AUTHOR = $authordetails[1]." ";
	if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0"){
		$CONTENT_TOP_TABLE_AUTHOR .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
	}else{
		//$CONTENT_TOP_TABLE_AUTHOR .= " ".CONTENT_ICON_USER;
	}
	$CONTENT_TOP_TABLE_AUTHOR .= " <a href='".e_SELF."?".$type.".".$type_id.".author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";

return $CONTENT_TOP_TABLE_AUTHOR;
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





// CONTENT_SUBMIT_TYPE_TABLE ------------------------------------------------
SC_BEGIN CONTENT_SUBMIT_TYPE_TABLE_HEADING
global $CONTENT_SUBMIT_TYPE_TABLE_HEADING, $row;
return "<a href='".e_SELF."?type.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING
global $CONTENT_SUBMIT_TYPE_TABLE_SUBHEADING, $row;
return ($row['content_subheading'] ? $row['content_subheading'] : "");
SC_END

SC_BEGIN CONTENT_SUBMIT_TYPE_TABLE_ICON
global $CONTENT_SUBMIT_TYPE_TABLE_ICON, $aa, $row, $content_cat_icon_path_large, $content_pref;
return $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "type.".$row['content_id'], "", $content_pref["content_blank_caticon_{$row['content_id']}"]);
SC_END



// CONTENT_CONTENTMANAGER ------------------------------------------------
SC_BEGIN CONTENT_CONTENTMANAGER_ICONNEW
global $CONTENT_CONTENTMANAGER_ICONNEW, $type, $type_id, $catidstring;
return "<a href='".e_SELF."?".$type.".".$type_id.".create.".$catidstring."'>".CONTENT_ICON_NEW."</a>";
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_CATEGORY
global $CONTENT_CONTENTMANAGER_CATEGORY, $parentheading;
return $parentheading;
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_ICONEDIT
global $CONTENT_CONTENTMANAGER_ICONEDIT, $type, $type_id, $catidstring;
return "<a href='".e_SELF."?".$type.".".$type_id.".c.".$catidstring."'>".CONTENT_ICON_EDIT."</a>";
SC_END





// CONTENT_AUTHOR_TABLE ------------------------------------------------
SC_BEGIN CONTENT_AUTHOR_TABLE_NAME
global $CONTENT_AUTHOR_TABLE_NAME, $authordetails, $i, $type, $type_id, $row;
$name = ($authordetails[$i][1] == "" ? "... ".CONTENT_LAN_29." ..." : $authordetails[$i][1]);
$authorlink = "<a href='".e_SELF."?".$type.".".$type_id.".author.".$row['content_id']."'>".$name."</a>";
return $authorlink;
SC_END

SC_BEGIN CONTENT_AUTHOR_TABLE_ICON
global $CONTENT_AUTHOR_TABLE_ICON, $type, $type_id, $row;
return "<a href='".e_SELF."?".$type.".".$type_id.".author.".$row['content_id']."'>".CONTENT_ICON_AUTHORLIST."</a>";
SC_END

SC_BEGIN CONTENT_AUTHOR_TABLE_TOTAL
global $CONTENT_AUTHOR_TABLE_TOTAL, $totalcontent;
return $totalcontent." ".($totalcontent==1 ? CONTENT_LAN_53 : CONTENT_LAN_54);
SC_END

SC_BEGIN CONTENT_AUTHOR_TABLE_HEADING
global $CONTENT_AUTHOR_TABLE_HEADING, $type, $type_id, $row;
return "<a href='".e_SELF."?".$type.".".$type_id.".content.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CONTENT_AUTHOR_TABLE_DATE
global $CONTENT_AUTHOR_TABLE_DATE, $gen, $row;
$DATE = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "short"));
return $DATE;
SC_END





// CONTENT_CAT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_CAT_TABLE_ICON
global $CONTENT_CAT_TABLE_ICON, $aa, $row, $content_pref, $type, $type_id, $content_cat_icon_path_large;
return $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, $type.".".$type_id.".cat.".$row['content_id'], "", $content_pref["content_blank_caticon_{$type_id}"]);
SC_END

SC_BEGIN CONTENT_CAT_TABLE_HEADING
global $CONTENT_CAT_TABLE_HEADING, $aa, $prefetchbreadcrumb, $row;
return $aa -> drawBreadcrumb($prefetchbreadcrumb, $row['content_id'], "nobase", "");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_AMOUNT
global $CONTENT_CAT_TABLE_AMOUNT, $aa, $row;
return $aa -> countItemsInCat($row['content_id'], $row['content_parent']);
SC_END

SC_BEGIN CONTENT_CAT_TABLE_SUBHEADING
global $CONTENT_CAT_TABLE_SUBHEADING, $row, $tp;
return ($row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_DATE
global $CONTENT_CAT_TABLE_DATE, $gen, $row;
$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "long"));
$DATE = ($datestamp != "" ? $datestamp : "");
return $DATE;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_AUTHORDETAILS
global $CONTENT_CAT_TABLE_AUTHORDETAILS, $authordetails, $type, $type_id;
if(USER){
	$AUTHORDETAILS = $authordetails[1]." ";
	if(is_numeric($authordetails[3])){
		$AUTHORDETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
	}else{
		$AUTHORDETAILS .= " ".CONTENT_ICON_USER;
	}
	$AUTHORDETAILS .= "<a href='".e_SELF."?".$type.".".$type_id.".author' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
}else{
	$AUTHORDETAILS = $authordetails[1]." ".CONTENT_ICON_USER." <a href='".e_SELF."?".$type.".".$type_id.".author' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
}
return $AUTHORDETAILS;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_EPICONS
global $CONTENT_CAT_TABLE_EPICONS, $row, $tp;
$EPICONS = "";
if($row['content_pe']){
	$EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_72."^plugin:content.".$row['content_id']."}");
	$EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_72."^plugin:content.".$row['content_id']."}");
	$EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
}
return $EPICONS;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_COMMENT
global $CONTENT_CAT_TABLE_COMMENT, $row, $type, $type_id, $comment_total;
if($row['content_comment']){
	$COMMENT = "<a style='text-decoration:none;' href='".e_SELF."?".$type.".".$type_id.".cat.".$row['content_id'].".comment'>".CONTENT_LAN_57." ".$comment_total."</a>";
}
return $COMMENT;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_TEXT
global $CONTENT_CAT_TABLE_TEXT, $row, $tp;
if(strlen($row['content_text']) > 500) {
	$row['content_text'] = substr($row['content_text'], 0, 500)." [more...]";
}
return ($row['content_text'] != "" && $row['content_text'] != " " ? $tp -> toHTML($row['content_text'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_RATING
global $CONTENT_CAT_TABLE_RATING, $row, $rater;
	$RATING = "";
	if($row['content_rate']){
		if($ratearray = $rater -> getrating("content_cat", $row['content_id'])){
			for($c=1; $c<= $ratearray[1]; $c++){
				$RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
			}
			if($ratearray[1] < 10){
				for($c=9; $c>=$ratearray[1]; $c--){
					$RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
				}
			}
			$RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
			if($ratearray[2] == ""){ $ratearray[2] = 0; }
			$RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
			$RATING .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
		}else{
			$RATING .= LAN_65;
		}
		if(!$rater -> checkrated("content_cat", $row['content_id']) && USER){
			$RATING .= " - ".$rater -> rateselect(LAN_40, "content_cat", $row['content_id']);
		}else if(USER){
			$RATING .= " - ".LAN_41;
		}
	}
return $RATING;
SC_END



// CONTENT_CAT_LIST_TABLE ------------------------------------------------
SC_BEGIN CONTENT_CAT_LIST_TABLE_ICON
global $CONTENT_CAT_LIST_TABLE_ICON, $aa, $row, $type_id, $content_pref, $content_cat_icon_path_large;
return $aa -> getIcon("catlarge", $row['content_icon'], $content_cat_icon_path_large, "", "", $content_pref["content_blank_caticon_{$type_id}"]);;
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_HEADING
global $CONTENT_CAT_LIST_TABLE_HEADING, $tp, $row;
return ($row['content_heading'] ? $tp -> toHTML($row['content_heading'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_SUMMARY
global $CONTENT_CAT_LIST_TABLE_SUMMARY, $tp, $row;
return ($row['content_summary'] ? $tp -> toHTML($row['content_summary'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_TEXT
global $CONTENT_CAT_LIST_TABLE_TEXT, $tp, $row;
return ($row['content_text'] ? $tp -> toHTML($row['content_text'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_AMOUNT
global $CONTENT_CAT_LIST_TABLE_AMOUNT, $aa, $row;
return $aa -> countItemsInCat($row['content_id'], $row['content_parent']);
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_SUBHEADING
global $CONTENT_CAT_LIST_TABLE_SUBHEADING, $tp, $row;
return ($row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_DATE
global $CONTENT_CAT_LIST_TABLE_DATE, $row, $gen;
$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "long"));
return ($datestamp != "" ? $datestamp : "");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_AUTHOREMAIL
global $CONTENT_CAT_LIST_TABLE_AUTHOREMAIL, $authordetails;
return ($authordetails[2] ? $authordetails[2] : "");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_AUTHORDETAILS
global $CONTENT_CAT_LIST_TABLE_AUTHORDETAILS, $authordetails, $type, $type_id, $row;
	if(USER){
		$DETAILS = $authordetails[1]." ";
		if(is_numeric($authordetails[3])){
			$DETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
		}else{
			$DETAILS .= " ".CONTENT_ICON_USER;
		}
		$DETAILS .= "<a href='".e_SELF."?".$type.".".$type_id.".author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
	}else{
		$DETAILS = $authordetails[1]." ".CONTENT_ICON_USER." <a href='".e_SELF."?".$type.".".$type_id.".author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
	}
return $DETAILS;
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_EPICONS
global $CONTENT_CAT_LIST_TABLE_EPICONS, $row, $tp, $sub_action;
$EPICONS = "";
if($row['content_pe']){
	$EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_72."^plugin:content.$sub_action}");
	$EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_72."^plugin:content.$sub_action}");
	$EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.$sub_action}");
}
return $EPICONS;
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_COMMENT
global $CONTENT_CAT_LIST_TABLE_COMMENT, $type, $type_id, $sub_action, $row, $comment_total;
if($row['content_comment']){
	$comments = "<a style='text-decoration:none;' href='".e_SELF."?".$type.".".$type_id.".cat.".$sub_action.".comment'>".CONTENT_LAN_57." ".$comment_total."</a>";
}
return $comments;
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_RATING
global $CONTENT_CAT_LIST_TABLE_RATING, $row, $rater;
	$RATING = "";
	if($row['content_rate']){
		if($ratearray = $rater -> getrating("content_cat", $row['content_id'])){
			for($c=1; $c<= $ratearray[1]; $c++){
				$RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
			}
			if($ratearray[1] < 10){
				for($c=9; $c>=$ratearray[1]; $c--){
					$RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
				}
			}
			$RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
			if($ratearray[2] == ""){ $ratearray[2] = 0; }
			$RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
			$RATING .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
		}else{
			$RATING .= LAN_65;
		}
		if(!$rater -> checkrated("content_cat", $row['content_id']) && USER){
			$RATING .= " - ".$rater -> rateselect(LAN_40, "content_cat", $row['content_id']);
		}else if(USER){
			$RATING .= " - ".LAN_41;
		}
	}
return $RATING;
SC_END






// CONTENT_CAT_LISTSUB ------------------------------------------------
SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_ICON
global $CONTENT_CAT_LISTSUB_TABLE_ICON, $aa, $row, $content_pref, $type_id;
return $aa -> getIcon("catsmall", $row['content_icon'], "", "", "", $content_pref["content_blank_caticon_{$type_id}"]);
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_HEADING
global $CONTENT_CAT_LISTSUB_TABLE_HEADING, $type, $type_id, $row, $tp;
return "<a href='".e_SELF."?".$type.".".$type_id.".cat.".$row['content_id']."'>".$tp -> toHTML($row['content_heading'], TRUE, "")."</a>";
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_AMOUNT
global $CONTENT_CAT_LISTSUB_TABLE_AMOUNT, $aa, $row;
return $aa -> countItemsInCat($row['content_id'], $row['content_parent']);
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_SUBHEADING
global $CONTENT_CAT_LISTSUB_TABLE_SUBHEADING, $row, $tp;
return ($row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
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
global $CONTENT_SEARCHRESULT_TABLE_ICON, $aa, $row, $content_icon_path, $type, $type_id, $content_pref;
return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, $type.".".$type_id.".content.".$row['content_id'], "50", $content_pref["content_blank_icon_{$type_id}"]."<br />");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_HEADING
global $CONTENT_SEARCHRESULT_TABLE_HEADING, $row, $type, $type_id, $tp;
return ($row['content_heading'] ? "<a href='".e_SELF."?".$type.".".$type_id.".content.".$row['content_id']."'>".$tp -> toHTML($row['content_heading'], TRUE, "")."</a>" : "");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_SUBHEADING
global $CONTENT_SEARCHRESULT_TABLE_SUBHEADING, $row, $tp;
return ($row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS
global $CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS, $authordetails, $type, $type_id, $row;
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
$CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS .= " <a href='".e_SELF."?".$type.".".$type_id.".author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
return $CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS;
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_DATE
global $CONTENT_SEARCHRESULT_TABLE_DATE, $gen, $row;
$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "short"));
return $datestamp;
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_TEXT
global $CONTENT_SEARCHRESULT_TABLE_TEXT, $contenttext;
return $contenttext;
SC_END




// CONTENT_RECENT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_RECENT_TABLE_ICON
global $CONTENT_RECENT_TABLE_ICON, $aa, $row, $tp, $type, $type_id, $content_icon_path, $content_pref;
return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, $type.".".$type_id.".content.".$row['content_id'], "100", $content_pref["content_blank_icon_{$type_id}"]);
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_HEADING
global $CONTENT_RECENT_TABLE_HEADING, $row, $tp, $type, $type_id;
return ($row['content_heading'] ? "<a href='".e_SELF."?".$type.".".$type_id.".content.".$row['content_id']."'>".$row['content_heading']."</a>" : "");

SC_END

SC_BEGIN CONTENT_RECENT_TABLE_SUBHEADING
global $CONTENT_RECENT_TABLE_SUBHEADING, $content_pref, $type_id, $row;
if ($content_pref["content_list_subheading_{$type_id}"] && $row['content_subheading'] && $content_pref["content_list_subheading_char_{$type_id}"] && $content_pref["content_list_subheading_char_{$type_id}"] != "" && $content_pref["content_list_subheading_char_{$type_id}"] != "0"){
	if(strlen($row['content_subheading']) > $content_pref["content_list_subheading_char_{$type_id}"]) {
		$row['content_subheading'] = substr($row['content_subheading'], 0, $content_pref["content_list_subheading_char_{$type_id}"]).$content_pref["content_list_subheading_post_{$type_id}"];
	}
	$SUBHEADING = ($row['content_subheading'] != "" && $row['content_subheading'] != " " ? $row['content_subheading'] : "");
}else{
	$SUBHEADING = ($row['content_subheading'] ? $row['content_subheading'] : "");
}
return $SUBHEADING;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_SUMMARY
global $CONTENT_RECENT_TABLE_SUMMARY, $content_pref, $type_id, $row;
if ($content_pref["content_list_summary_{$type_id}"] && $row['content_summary'] && $content_pref["content_list_summary_char_{$type_id}"] && $content_pref["content_list_summary_char_{$type_id}"] != "" && $content_pref["content_list_summary_char_{$type_id}"] != "0"){
	if(strlen($row['content_summary']) > $content_pref["content_list_summary_char_{$type_id}"]) {
		$row['content_summary'] = substr($row['content_summary'], 0, $content_pref["content_list_summary_char_{$type_id}"]).$content_pref["content_list_summary_post_{$type_id}"];
	}
	$SUMMARY = ($row['content_summary'] != "" && $row['content_summary'] != " " ? $row['content_summary'] : "");
}else{
	$SUMMARY = ($row['content_summary'] ? $row['content_summary'] : "");
}
return $SUMMARY;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_DATE
global $CONTENT_RECENT_TABLE_DATE, $gen, $content_pref, $type_id, $row;
if($content_pref["content_list_date_{$type_id}"]){
	$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "long"));
	$DATE = ($datestamp != "" ? $datestamp : "");
}
return $DATE;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_EPICONS
global $CONTENT_RECENT_TABLE_EPICONS, $tp, $content_pref, $type, $type_id, $row;
$EPICONS  = "";
if(($content_pref["content_list_peicon_{$type_id}"] && $row['content_pe']) || $content_pref["content_list_peicon_all_{$type_id}"]){
	$EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
	$EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
	$EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
}
return $EPICONS;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_AUTHORDETAILS
global $CONTENT_RECENT_TABLE_AUTHORDETAILS, $content_pref, $type, $type_id, $row, $aa;
if($content_pref["content_list_authorname_{$type_id}"] || $content_pref["content_list_authoremail_{$type_id}"]){
	$authordetails = $aa -> getAuthor($row['content_author']);
	//$authordetails[1] = ($authordetails[1] ? $authordetails[1] : "unknown");
	if($content_pref["content_list_authorname_{$type_id}"]){
		if(isset($content_pref["content_list_authoremail_{$type_id}"]) && $authordetails[2]){
			if($authordetails[0] == "0"){
				if($content_pref["content_list_authoremail_nonmember_{$type_id}"]){
					$DETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
				}else{
					$DETAILS = $authordetails[1];
				}
			}else{
				$DETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
			}
		}else{
			$DETAILS = $authordetails[1];
		}
		if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0"){
			$DETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
		}else{
			//$DETAILS .= " ".CONTENT_ICON_USER;
		}
	}
	$DETAILS .= " <a href='".e_SELF."?".$type.".".$type_id.".author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
}
return $DETAILS;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_EDITICON
global $CONTENT_RECENT_TABLE_EDITICON, $content_pref, $type_id, $row;
if(getperms("P") && $content_pref["content_list_editicon_{$type_id}"]){
	$EDITICON = "<a href='".e_PLUGIN."content/admin_content_config.php?type.".$type_id.".create.edit.".$row['content_id']."'>".CONTENT_ICON_EDIT."</a>";
}
return $EDITICON;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_REFER
global $CONTENT_RECENT_TABLE_REFER, $content_pref, $type_id, $row;
if($content_pref["content_log_{$type_id}"] && $content_pref["content_list_refer_{$type_id}"]){
	$refercounttmp = explode("^", $row['content_refer']);
	$REFER = ($refercounttmp[0] ? $refercounttmp[0] : "0");
}
return $REFER;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_RATING
global $CONTENT_RECENT_TABLE_RATING, $rater, $row, $type, $type_id, $content_pref, $plugintable;
$RATING = "";
if($content_pref["content_list_rating_all_{$type_id}"] || ($content_pref["content_list_rating_{$type_id}"] && $row['content_rate'])){
	if($ratearray = $rater -> getrating($plugintable, $row['content_id'])){
		for($c=1; $c<= $ratearray[1]; $c++){
			$RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
		}
		if($ratearray[1] < 10){
			for($c=9; $c>=$ratearray[1]; $c--){
				$RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
			}
		}
		$RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
		if($ratearray[2] == ""){ $ratearray[2] = 0; }
		$RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
		$RATING .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
	}else{
		$RATING .= LAN_65;
	}
	if(!$rater -> checkrated($plugintable, $row['content_id']) && USER){
		$RATING .= " - ".$rater -> rateselect(LAN_40, $plugintable, $row['content_id']);
	}else if(USER){
		$RATING .= " - ".LAN_41;
	}
}
return $RATING;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_PARENT
global $CONTENT_RECENT_TABLE_PARENT, $content_pref, $type_id, $aa, $prefetchbreadcrumb, $row;
if($content_pref["content_list_parent_{$type_id}"]){
	$PARENT = $aa -> drawBreadcrumb($prefetchbreadcrumb, $row['content_parent'], "nobase", "");
}
return $PARENT;
SC_END





// CONTENT_CONTENT_TABLE ------------------------------------------------

SC_BEGIN CONTENT_CONTENT_TABLE_ICON
global $CONTENT_CONTENT_TABLE_ICON, $aa, $row, $content_pref, $content_icon_path, $type_id;
return $aa -> getIcon("item", $row['content_icon'], $content_icon_path, "", "100", $content_pref["content_blank_icon_{$type_id}"]);
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_HEADING
global $CONTENT_CONTENT_TABLE_HEADING, $row, $tp;
return ($row['content_heading'] ? $tp -> toHTML($row['content_heading'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_REFER
global $CONTENT_CONTENT_TABLE_REFER, $sql, $type_id, $content_pref, $sub_action, $plugintable;
if($content_pref["content_content_refer_{$type_id}"]){
	$sql -> db_Select($plugintable, "content_refer", "content_id='".$sub_action."' ");
	list($content_refer) = $sql -> db_Fetch();
	$refercounttmp = explode("^", $content_refer);
	$REFER = ($refercounttmp[0] ? $refercounttmp[0] : "");
}
return $REFER;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_SUBHEADING
global $CONTENT_CONTENT_TABLE_SUBHEADING, $row, $tp, $content_pref, $type_id;
return ($content_pref["content_content_subheading_{$type_id}"] && $row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_COMMENT
global $CONTENT_CONTENT_TABLE_COMMENT, $row, $type_id, $plugintable, $content_pref, $sub_action, $sql;
if($row['content_comment'] || $content_pref["content_content_comment_all_{$type_id}"]){
	$COMMENT = $sql -> db_Select("comments", "*",  "comment_item_id='".$sub_action."' AND comment_type='".$plugintable."' AND comment_pid='0' ");
	if(!$COMMENT){ $COMMENT = "0"; }
}
return $COMMENT;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_DATE
global $CONTENT_CONTENT_TABLE_DATE, $gen, $row, $type_id, $content_pref;
if($content_pref["content_content_date_{$type_id}"]){
	$gen = new convert;
	$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "long"));
	$DATE = ($datestamp != "" ? $datestamp : "");
}
return $DATE;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_AUTHORDETAILS
global $CONTENT_CONTENT_TABLE_AUTHORDETAILS, $content_pref, $type, $type_id, $row, $aa;
$authordetails = $aa -> getAuthor($row['content_author']);
if($content_pref["content_content_authorname_{$type_id}"] || $content_pref["content_content_authoremail_{$type_id}"]){
	if(isset($content_pref["content_content_authoremail_{$type_id}"]) && $authordetails[2]){
		if($authordetails[0] == "0"){
			if($content_pref["content_content_authoremail_nonmember_{$type_id}"]){
				$DETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
			}else{
				$DETAILS = $authordetails[1];
			}
		}else{
			$DETAILS = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
		}
	}else{
		$DETAILS = $authordetails[1];
	}
	if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0"){
		$DETAILS .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
	}else{
		//$DETAILS .= " ".CONTENT_ICON_USER;
	}
}
$DETAILS .= " <a href='".e_SELF."?".$type.".".$type_id.".author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
return $DETAILS;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_EPICONS
global $CONTENT_CONTENT_TABLE_EPICONS, $content_pref, $type_id, $row, $tp;
$EPICONS = "";
if(($content_pref["content_content_peicon_{$type_id}"] && $row['content_pe']) || $content_pref["content_content_peicon_all_{$type_id}"]){
	$EPICONS = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
	$EPICONS .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
	$EPICONS .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
}
return $EPICONS;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_EDITICON
global $CONTENT_CONTENT_TABLE_EDITICON, $content_pref, $type_id, $row;
if(getperms("P") && $content_pref["content_content_editicon_{$type_id}"]){
	$EDITICON = "<a href='".e_PLUGIN."content/admin_content_config.php?type.".$type_id.".create.edit.".$row['content_id']."'>".CONTENT_ICON_EDIT."</a>";
}
return $EDITICON;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_RATING
global $CONTENT_CONTENT_TABLE_RATING, $content_pref, $type_id, $row, $rater, $plugintable;
$RATING = "";
if(($content_pref["content_content_rating_{$type_id}"] && $row['content_rate']) || $content_pref["content_content_rating_all_{$type_id}"]){	
	if($ratearray = $rater -> getrating($plugintable, $row['content_id'])){
		for($c=1; $c<= $ratearray[1]; $c++){
			$RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
		}
		if($ratearray[1] < 10){
			for($c=9; $c>=$ratearray[1]; $c--){
				$RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
			}
		}
		$RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
		if($ratearray[2] == ""){ $ratearray[2] = 0; }
		$RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
		$RATING .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
	}else{
		$RATING .= LAN_65;
	}
	if(!$rater -> checkrated($plugintable, $row['content_id']) && USER){
		$RATING .= " - ".$rater -> rateselect(LAN_40, $plugintable, $row['content_id']);
	}else if(USER){
		$RATING .= " - ".LAN_41;
	}
}
return $RATING;

SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_FILE
global $CONTENT_CONTENT_TABLE_FILE, $row, $content_file_path;
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
return ($filesexisting == "0" ? "" : CONTENT_LAN_41." ".($filesexisting == 1 ? CONTENT_LAN_42 : CONTENT_LAN_43)." ".$file." ");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_SCORE
global $CONTENT_CONTENT_TABLE_SCORE, $custom;
if($custom['content_custom_score']){
	if(strlen($custom['content_custom_score']) == "2"){
		$SCORE = substr($custom['content_custom_score'],0,1).".".substr($custom['content_custom_score'],1,2);
	}else{
		$SCORE = "0.".$custom['content_custom_score'];
	}
}
return $SCORE;
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
global $CONTENT_CONTENT_TABLE_IMAGES, $row, $content_image_path, $aa, $tp, $authordetails;
$imagestmp = explode("[img]", $row['content_image']);
foreach($imagestmp as $key => $value) { 
	if($value == "") { 
		unset($imagestmp[$key]); 
	} 
} 
$images = array_values($imagestmp);
$content_image_popup_name = ereg_replace("'", "", $row['content_heading']);
$IMAGES = "";
$gen = new convert;
$datestamp = ereg_replace(" -.*", "", $gen -> convert_date($row['content_datestamp'], "long"));
for($i=0;$i<count($images);$i++){		
	$oSrc = $content_image_path.$images[$i];
	$oSrcThumb = $content_image_path."thumb_".$images[$i];
	$oMaxWidth = 500;
	$oTitle = $content_image_popup_name." ".($i+1);
	$oText = $content_image_popup_name." ".($i+1)."<br />".$tp -> toHTML($row['content_subheading'], TRUE, "")."<br />".$authordetails[1]." (".$datestamp.")";
	$IMAGES .= $aa -> popup($oSrc, $oSrcThumb, $oMaxWidth, $oTitle, $oText);
	//$myimagelink .= $aa -> popup($oSrc, $oSrcThumb, $oMaxWidth, $oTitle, $oText);
}
return $IMAGES;
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