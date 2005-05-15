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
global $CONTENT_AUTHOR_TABLE_DATE;
return $CONTENT_AUTHOR_TABLE_DATE;
SC_END





// CONTENT_CAT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_CAT_TABLE_ICON
global $CONTENT_CAT_TABLE_ICON;
return $CONTENT_CAT_TABLE_ICON;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_HEADING
global $CONTENT_CAT_TABLE_HEADING;
return $CONTENT_CAT_TABLE_HEADING;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_AMOUNT
global $CONTENT_CAT_TABLE_AMOUNT;
return $CONTENT_CAT_TABLE_AMOUNT;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_SUBHEADING
global $CONTENT_CAT_TABLE_SUBHEADING;
return $CONTENT_CAT_TABLE_SUBHEADING;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_DATE
global $CONTENT_CAT_TABLE_DATE;
return $CONTENT_CAT_TABLE_DATE;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_AUTHORDETAILS
global $CONTENT_CAT_TABLE_AUTHORDETAILS;
return $CONTENT_CAT_TABLE_AUTHORDETAILS;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_EPICONS
global $CONTENT_CAT_TABLE_EPICONS;
return $CONTENT_CAT_TABLE_EPICONS;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_COMMENT
global $CONTENT_CAT_TABLE_COMMENT;
return $CONTENT_CAT_TABLE_COMMENT;
SC_END

SC_BEGIN CONTENT_CAT_TABLE_TEXT
global $CONTENT_CAT_TABLE_TEXT;
return $CONTENT_CAT_TABLE_TEXT;
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
global $CONTENT_SEARCHRESULT_TABLE_HEADING, $row, $type, $type_id;
return ($row['content_heading'] ? "<a href='".e_SELF."?".$type.".".$type_id.".content.".$row['content_id']."'>".$row['content_heading']."</a>" : "");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_SUBHEADING
global $CONTENT_SEARCHRESULT_TABLE_SUBHEADING, $row;
return ($row['content_subheading'] ? $row['content_subheading'] : "");
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
global $CONTENT_SEARCHRESULT_TABLE_DATE, $datestamp;
return $datestamp;
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_TEXT
global $CONTENT_SEARCHRESULT_TABLE_TEXT, $contenttext;
return $contenttext;
SC_END




// CONTENT_RECENT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_RECENT_TABLE_ICON
global $CONTENT_RECENT_TABLE_ICON;
return $CONTENT_RECENT_TABLE_ICON;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_HEADING
global $CONTENT_RECENT_TABLE_HEADING;
return $CONTENT_RECENT_TABLE_HEADING;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_SUBHEADING
global $CONTENT_RECENT_TABLE_SUBHEADING;
return $CONTENT_RECENT_TABLE_SUBHEADING;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_SUMMARY
global $CONTENT_RECENT_TABLE_SUMMARY;
return $CONTENT_RECENT_TABLE_SUMMARY;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_DATE
global $CONTENT_RECENT_TABLE_DATE;
return $CONTENT_RECENT_TABLE_DATE;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_EPICONS
global $CONTENT_RECENT_TABLE_EPICONS;
return $CONTENT_RECENT_TABLE_EPICONS;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_AUTHORDETAILS
global $CONTENT_RECENT_TABLE_AUTHORDETAILS;
return $CONTENT_RECENT_TABLE_AUTHORDETAILS;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_EDITICON
global $CONTENT_RECENT_TABLE_EDITICON;
return $CONTENT_RECENT_TABLE_EDITICON;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_REFER
global $CONTENT_RECENT_TABLE_REFER;
return $CONTENT_RECENT_TABLE_REFER;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_RATING
global $CONTENT_RECENT_TABLE_RATING;
return $CONTENT_RECENT_TABLE_RATING;
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_PARENT
global $CONTENT_RECENT_TABLE_PARENT;
return $CONTENT_RECENT_TABLE_PARENT;
SC_END




*/
?>