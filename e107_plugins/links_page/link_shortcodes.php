<?php
include_once(e_HANDLER.'shortcode_handler.php');
$link_shortcodes = e_shortcode::parse_scbatch(__FILE__);
/*

// LINK_MAIN_TABLE ------------------------------------------------
SC_BEGIN LINK_MAIN_HEADING
global $LINK_MAIN_HEADING, $total_links_cat, $row, $tp;
return (!$total_links_cat ? $row['link_category_name'] : "<a href='links.php?cat.".$row['link_category_id']."'>".$tp->toHTML($row['link_category_name'], TRUE)."</a>");
SC_END


SC_BEGIN LINK_MAIN_DESC
global $LINK_MAIN_DESC, $row, $linkspage_pref, $tp;
return (isset($linkspage_pref['link_cat_desc']) && $linkspage_pref['link_cat_desc'] ? $tp->toHTML($row['link_category_description'], TRUE) : "");
SC_END

SC_BEGIN LINK_MAIN_NUMBER
global $LINK_MAIN_NUMBER, $total_links_cat, $linkspage_pref;
if(isset($linkspage_pref['link_cat_amount']) && $linkspage_pref['link_cat_amount']){
$LINK_MAIN_NUMBER = $total_links_cat." ".($total_links_cat == 1 ? LAN_65 : LAN_66)." ".LAN_64;
}else{
$LINK_MAIN_NUMBER = "";
}
return $LINK_MAIN_NUMBER;
SC_END

SC_BEGIN LINK_MAIN_ICON
global $LINK_MAIN_ICON, $row, $linkspage_pref;
$LINK_MAIN_ICON = "";
if(isset($linkspage_pref['link_cat_icon']) && $linkspage_pref['link_cat_icon']){
	if (isset($row['link_category_icon']) && $row['link_category_icon']) {
		if(strstr($row['link_cat_icon_empty'], "/")){
			$LINK_MAIN_ICON = "<img src='".e_BASE.$row['link_category_icon']."' alt='' style='vertical-align:middle' /></a>";
		}else{
			$LINK_MAIN_ICON = "<img src='".e_PLUGIN."links_page/cat_images/".$row['link_category_icon']."' alt='' style='vertical-align:middle' /></a>";
		}
	} else {
		if(isset($linkspage_pref['link_cat_icon_empty']) && $linkspage_pref['link_cat_icon_empty']){
			$LINK_MAIN_ICON = "<img src='".THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' style='vertical-align:middle;' />";
		}else{
			$LINK_MAIN_ICON = "";
		}
	}
}
return $LINK_MAIN_ICON;
SC_END

SC_BEGIN LINK_MAIN_TOTAL
global $LINK_MAIN_TOTAL, $sql, $category_total, $linkspage_pref;
if(isset($linkspage_pref['link_cat_total']) && $linkspage_pref['link_cat_total']){
$total_links = $sql->db_Count("links_page", "(*)");
$LINK_MAIN_TOTAL = LAN_102." ".($total_links == 1 ? LAN_103 : LAN_104)." ".$total_links." ".($total_links == 1 ? LAN_65 : LAN_66)." ".LAN_105." ".$category_total." ".($category_total == 1 ? LAN_63 : LAN_62);
}else{
$LINK_MAIN_TOTAL = "";
}
return $LINK_MAIN_TOTAL;
SC_END

SC_BEGIN LINK_MAIN_SHOWALL
global $LINK_MAIN_SHOWALL, $linkspage_pref;
return (isset($linkspage_pref['link_cat_total']) && $linkspage_pref['link_cat_total'] ? "<a href='".e_PLUGIN."links_page/links.php?cat.all'>".LAN_67."</a>" : "");
SC_END

SC_BEGIN LINK_MAIN_TOPREFER
global $LINK_MAIN_TOPREFER, $linkspage_pref;
return (isset($linkspage_pref['link_cat_toprefer']) && $linkspage_pref['link_cat_toprefer'] ? "<a href='".e_PLUGIN."links_page/links.php?top'>".LAN_LINKS_12."</a>" : "");
SC_END

SC_BEGIN LINK_MAIN_TOPRATED
global $LINK_MAIN_TOPRATED, $linkspage_pref;
return (isset($linkspage_pref['link_cat_toprated']) && $linkspage_pref['link_cat_toprated'] ? "<a href='".e_PLUGIN."links_page/links.php?rated'>".LAN_LINKS_13."</a>" : "");
SC_END


// LINK_CAT_TABLE ------------------------------------------------
SC_BEGIN LINK_CAT_SORTORDER
global $LINK_CAT_SORTORDER;
return $LINK_CAT_SORTORDER;
SC_END

SC_BEGIN LINK_CAT_BUTTON
global $LINK_CAT_BUTTON, $linkspage_pref, $row, $LINK_CAT_NAME, $LINK_CAT_APPEND;
if(isset($linkspage_pref['link_icon']) && $linkspage_pref['link_icon']){
	if ($row['link_button']) {
		if (strpos($row['link_button'], "http://") !== FALSE) {
			$LINK_CAT_BUTTON = $LINK_CAT_APPEND."\n<img style='border:0;' src='".$row['link_button']."' alt='".$LINK_CAT_NAME."' /></a>";
		} else {
			if(strstr($row['link_button'], "/")){
				$LINK_CAT_BUTTON = $LINK_CAT_APPEND."\n<img style='border:0;' src='".e_BASE.$row['link_button']."' alt='".$LINK_CAT_NAME."' /></a>";
			}else{
				$LINK_CAT_BUTTON = $LINK_CAT_APPEND."\n<img style='border:0' src='".e_PLUGIN."links_page/link_images/".$row['link_button']."' alt='".$LINK_CAT_NAME."' /></a>";
			}
		}
	} else {
		if(isset($linkspage_pref['link_icon_empty']) && $linkspage_pref['link_icon_empty']){
			$LINK_CAT_BUTTON = $LINK_CAT_APPEND."\n<img style='border:0; width: 88px; height: 31px;' src='".e_PLUGIN."links_page/images/generic.png' alt='".$LINK_CAT_NAME."' /></a>";
		}else{
			$LINK_CAT_BUTTON = "";
		}
	}
}else{
	if(isset($linkspage_pref['link_icon_empty']) && $linkspage_pref['link_icon_empty']){
		$LINK_CAT_BUTTON = $LINK_CAT_APPEND."\n<img style='border:0; width: 88px; height: 31px;' src='".e_PLUGIN."links_page/images/generic.png' alt='".$LINK_CAT_NAME."' /></a>";
	}else{
		$LINK_CAT_BUTTON = "";
	}	
}
return $LINK_CAT_BUTTON;
SC_END

SC_BEGIN LINK_CAT_APPEND
global $LINK_CAT_APPEND;
return $LINK_CAT_APPEND;
SC_END

SC_BEGIN LINK_CAT_NAME
global $LINK_CAT_NAME;
return $LINK_CAT_NAME;
SC_END

SC_BEGIN LINK_CAT_URL
global $LINK_CAT_URL, $linkspage_pref, $row;
return (isset($linkspage_pref['link_url']) && $linkspage_pref['link_url'] ? $row['link_url'] : "");
SC_END

SC_BEGIN LINK_CAT_REFER
global $LINK_CAT_REFER, $linkspage_pref, $row;
return (isset($linkspage_pref['link_referal']) && $linkspage_pref['link_referal'] ? LAN_88." ".$row['link_refer'] : "");
SC_END

SC_BEGIN LINK_CAT_DESC
global $LINK_CAT_DESC, $linkspage_pref, $tp, $row;
return (isset($linkspage_pref['link_desc']) && $linkspage_pref['link_desc'] ? $tp->toHTML($row['link_description'], TRUE) : "");
SC_END

SC_BEGIN LINK_CAT_RATING
global $LINK_CAT_RATING, $LINK_RATED_RATING, $linkspage_pref, $rater, $row, $qs;
$LINK_CAT_RATING = "";
if(isset($linkspage_pref['link_rating']) && $linkspage_pref['link_rating']){
$LINK_CAT_RATING = $rater->composerating("links_page", $row['link_id'], $enter=TRUE, $userid=FALSE);
}
return $LINK_CAT_RATING;
SC_END

SC_BEGIN LINK_CAT_SUBMIT
global $LINK_CAT_SUBMIT, $linkspage_pref, $qs;
if ($qs[0] != "top" && isset($linkspage_pref['link_submit']) && $linkspage_pref['link_submit'] && check_class($linkspage_pref['link_submit_class'])) {
$LINK_CAT_SUBMIT = "<a href='".e_SELF."?submit'>".LAN_101."</a>";
}
return $LINK_CAT_SUBMIT;
SC_END


SC_BEGIN LINK_CAT_NEW
global $LINK_CAT_NEW, $linkspage_pref, $qs, $row;
if(USER && $row['link_datestamp'] > USERLV){
$LINK_CAT_NEW = "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' />";
}else{
$LINK_CAT_NEW = "";
}
return $LINK_CAT_NEW;
SC_END


// LINK_TOP_TABLE ------------------------------------------------



// LINK_RATED_TABLE ------------------------------------------------
SC_BEGIN LINK_RATED_RATING
global $LINK_RATED_RATING, $thisratearray;
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

SC_BEGIN LINK_RATED_BUTTON
global $LINK_RATED_BUTTON, $linkspage_pref, $row, $LINK_RATED_NAME, $LINK_RATED_APPEND;
if(isset($linkspage_pref['link_icon']) && $linkspage_pref['link_icon']){
	if ($row['link_button']) {
		if (strpos($row['link_button'], "http://") !== FALSE) {
			$LINK_RATED_BUTTON = $LINK_RATED_APPEND."\n<img style='border:0;' src='".$row['link_button']."' alt='".$LINK_RATED_NAME."' /></a>";
		} else {
			if(strstr($row['link_button'], "/")){
				$LINK_RATED_BUTTON = $LINK_RATED_APPEND."\n<img style='border:0;' src='".e_BASE.$row['link_button']."' alt='".$LINK_RATED_NAME."' /></a>";
			}else{
				$LINK_RATED_BUTTON = $LINK_RATED_APPEND."\n<img style='border:0' src='".e_PLUGIN."links_page/link_images/".$row['link_button']."' alt='".$LINK_RATED_NAME."' /></a>";
			}
		}
	} else {
		if(isset($linkspage_pref['link_icon_empty']) && $linkspage_pref['link_icon_empty']){
			$LINK_RATED_BUTTON = $LINK_RATED_APPEND."\n<img style='border:0; width: 88px; height: 31px;' src='".e_PLUGIN."links_page/images/generic.png' alt='".$LINK_RATED_NAME."' /></a>";
		}else{
			$LINK_RATED_BUTTON = "";
		}
	}
}else{
	if(isset($linkspage_pref['link_icon_empty']) && $linkspage_pref['link_icon_empty']){
		$LINK_RATED_BUTTON = $LINK_RATED_APPEND."\n<img style='border:0; width: 88px; height: 31px;' src='".e_PLUGIN."links_page/images/generic.png' alt='".$LINK_RATED_NAME."' /></a>";
	}else{
		$LINK_RATED_BUTTON = "";
	}	
}
return $LINK_RATED_BUTTON;
SC_END

SC_BEGIN LINK_RATED_APPEND
global $LINK_RATED_APPEND;
return $LINK_RATED_APPEND;
SC_END

SC_BEGIN LINK_RATED_NAME
global $LINK_RATED_NAME;
return $LINK_RATED_NAME;
SC_END

SC_BEGIN LINK_RATED_URL
global $LINK_RATED_URL, $linkspage_pref, $row;
return (isset($linkspage_pref['link_url']) && $linkspage_pref['link_url'] ? $row['link_url'] : "");
SC_END

SC_BEGIN LINK_RATED_REFER
global $LINK_RATED_REFER, $linkspage_pref, $row;
return (isset($linkspage_pref['link_referal']) && $linkspage_pref['link_referal'] ? LAN_88." ".$row['link_refer'] : "");
SC_END

SC_BEGIN LINK_RATED_DESC
global $LINK_RATED_DESC, $linkspage_pref, $tp, $row;
return (isset($linkspage_pref['link_desc']) && $linkspage_pref['link_desc'] ? $tp->toHTML($row['link_description'], TRUE) : "");
SC_END



SC_BEGIN LINK_BACKLINK
global $LINK_BACKLINK;
return "<a href='".e_PLUGIN."links_page/links.php'>".LAN_LINKS_14."</a>";
SC_END

*/
?>
