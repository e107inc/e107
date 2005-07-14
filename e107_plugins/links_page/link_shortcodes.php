<?php
include_once(e_HANDLER.'shortcode_handler.php');
$link_shortcodes = e_shortcode::parse_scbatch(__FILE__);
/*

//SC_BEGIN LINK_NAVIGATOR
//global $LINK_NAVIGATOR, $linkspage_pref, $qs;
//$main = "";
//if(isset($linkspage_pref['link_navigator_frontpage']) && $linkspage_pref['link_navigator_frontpage']){
//	$main .= "<a href='".e_PLUGIN."links_page/links.php'>".LAN_LINKS_14."</a> >><br />";
//}
//if(isset($linkspage_pref['link_navigator_submit']) && $linkspage_pref['link_navigator_submit'] && isset($linkspage_pref['link_submit']) && $linkspage_pref['link_submit'] && check_class($linkspage_pref['link_submit_class'])){
//	$main .= "<a href='".e_PLUGIN."links_page/links.php?submit'>".LAN_LINKS_27."</a> >><br />";
//}
//if(isset($linkspage_pref['link_navigator_manager']) && $linkspage_pref['link_navigator_manager'] && isset($linkspage_pref['link_manager']) && $linkspage_pref['link_manager'] && check_class($linkspage_pref['link_manager_class'])){
//	$main .= "<a href='".e_PLUGIN."links_page/links.php?manage'>".LCLAN_ITEM_35."</a> >><br />";
//}
//if(isset($linkspage_pref['link_navigator_refer']) && $linkspage_pref['link_navigator_refer']){
//	$main .= "<a href='".e_PLUGIN."links_page/links.php?top'>".LAN_LINKS_12."</a> >><br />";
//}
//if(isset($linkspage_pref['link_navigator_rated']) && $linkspage_pref['link_navigator_rated']){
//	$main .= "<a href='".e_PLUGIN."links_page/links.php?rated'>".LAN_LINKS_13."</a> >><br />";
//}
//if(isset($linkspage_pref['link_navigator_category']) && $linkspage_pref['link_navigator_category']){
//	$main .= "<a href='".e_PLUGIN."links_page/links.php?cat'>".LAN_LINKS_43."</a> >><br />";
//}
//return $main;
//SC_END

SC_BEGIN LINK_NAVIGATOR
global $LINK_NAVIGATOR, $rs, $linkspage_pref, $qs;
$mains = "";
$baseurl = e_PLUGIN."links_page/links.php";
if(isset($linkspage_pref['link_navigator_frontpage']) && $linkspage_pref['link_navigator_frontpage']){
	$mains .= $rs -> form_option(LAN_LINKS_14, "0", $baseurl, "");
}
if(isset($linkspage_pref['link_navigator_refer']) && $linkspage_pref['link_navigator_refer']){
	$mains .= $rs -> form_option(LAN_LINKS_12, "0", $baseurl."?top", "");
}
if(isset($linkspage_pref['link_navigator_rated']) && $linkspage_pref['link_navigator_rated']){
	$mains .= $rs -> form_option(LAN_LINKS_13, "0", $baseurl."?rated", "");
}
if(isset($linkspage_pref['link_navigator_category']) && $linkspage_pref['link_navigator_category']){
	$mains .= $rs -> form_option(LAN_LINKS_43, "0", $baseurl."?cat", "");
}
if(isset($linkspage_pref['link_navigator_links']) && $linkspage_pref['link_navigator_links']){
	$mains .= $rs -> form_option(LCLAN_OPT_68, "0", $baseurl."?all", "");
}
if(isset($linkspage_pref['link_navigator_submit']) && $linkspage_pref['link_navigator_submit'] && isset($linkspage_pref['link_submit']) && $linkspage_pref['link_submit'] && check_class($linkspage_pref['link_submit_class'])){
	$mains .= $rs -> form_option(LAN_LINKS_27, "0", $baseurl."?submit", "");
}
if(isset($linkspage_pref['link_navigator_manager']) && $linkspage_pref['link_navigator_manager'] && isset($linkspage_pref['link_manager']) && $linkspage_pref['link_manager'] && check_class($linkspage_pref['link_manager_class'])){
	$mains .= $rs -> form_option(LCLAN_ITEM_35, "0", $baseurl."?manage", "");
}
if(isset($linkspage_pref['link_navigator_allcat']) && $linkspage_pref['link_navigator_allcat']){
	$sqlc = new db;
	if ($sqlc->db_Select("links_page_cat", "link_category_id, link_category_name", "link_category_class REGEXP '".e_CLASS_REGEXP."' ORDER BY link_category_name")){
		$mains .= $rs -> form_option("&nbsp;", "0", "", "");
		$mains .= $rs -> form_option("-- view category --", "0", "", "");
		while ($rowc = $sqlc->db_Fetch()){
			$mains .= $rs -> form_option($rowc['link_category_name'], "0", $baseurl."?cat.".$rowc['link_category_id'], "");
		}
	}
}

if($mains){
	$main = "";
	$selectjs = " onchange=\"if(this.options[this.selectedIndex].value != ''){ return document.location=this.options[this.selectedIndex].value; }\" ";
	$main .= $rs -> form_select_open("navigator", $selectjs);
	$main .= $rs -> form_option("link navigator...", "0", "", "");
	$main .= $mains;
	$main .= $rs -> form_select_close();
	return $main;
}
SC_END

SC_BEGIN LINK_SORTORDER
global $LINK_SORTORDER;
return $LINK_SORTORDER;
SC_END



SC_BEGIN LINK_NAVIGATOR_TABLE_PRE
global $LINK_NAVIGATOR_TABLE_PRE;
if($LINK_NAVIGATOR_TABLE_PRE === TRUE){
$LINK_NAVIGATOR_TABLE_PRE = " ";
return $LINK_NAVIGATOR_TABLE_PRE;
}
SC_END
SC_BEGIN LINK_NAVIGATOR_TABLE_POST
global $LINK_NAVIGATOR_TABLE_POST;
if($LINK_NAVIGATOR_TABLE_POST === TRUE){
$LINK_NAVIGATOR_TABLE_POST = " ";
return $LINK_NAVIGATOR_TABLE_POST;
}
SC_END
















// LINK_TABLE_MANAGER ------------------------------------------------
SC_BEGIN LINK_MANAGE_ICON
global $LINK_MANAGE_ICON, $row;
$LINK_MANAGE_ICON = "";
return $LINK_MANAGE_ICON;
SC_END

SC_BEGIN LINK_MANAGE_NAME
global $LINK_MANAGE_NAME, $row, $tp;
return $tp->toHTML($row['link_name'], TRUE);
SC_END

SC_BEGIN LINK_MANAGE_OPTIONS
global $LINK_MANAGE_OPTIONS, $row, $tp, $linkspage_pref;
$linkid = $row['link_id'];
$LINK_MANAGE_OPTIONS = "<a href='".e_SELF."?manage.edit.".$linkid."' title='".LCLAN_ITEM_31."'>".LINK_ICON_EDIT."</a>";
if (isset($linkspage_pref['link_directdelete']) && $linkspage_pref['link_directdelete']){
	$LINK_MANAGE_OPTIONS .= " <input type='image' title='delete' name='delete[main_{$linkid}]' alt='".LCLAN_ITEM_32."' src='".LINK_ICON_DELETE_BASE."' onclick=\"return jsconfirm('".$tp->toJS(LCLAN_ITEM_33." [ ".$row['link_name']." ]")."')\" style='vertical-align:top;' />";
}
return $LINK_MANAGE_OPTIONS;
SC_END

SC_BEGIN LINK_MANAGE_CAT
global $LINK_MANAGE_CAT, $tp, $row;
return $tp->toHTML($row['link_category_name'], TRUE);
SC_END

SC_BEGIN LINK_MANAGE_NEWLINK
global $LINK_MANAGE_NEWLINK;
return "<a href='".e_SELF."?manage'>".LAN_LINKS_MANAGER_3."</a>";
SC_END







// LINK_MAIN_TABLE ------------------------------------------------
SC_BEGIN LINK_MAIN_HEADING
global $LINK_MAIN_HEADING, $rowl, $tp;
return (!$rowl['total_links'] ? $rowl['link_category_name'] : "<a href='links.php?cat.".$rowl['link_category_id']."'>".$tp->toHTML($rowl['link_category_name'], TRUE)."</a>");
SC_END

SC_BEGIN LINK_MAIN_DESC
global $LINK_MAIN_DESC, $rowl, $linkspage_pref, $tp;
return (isset($linkspage_pref['link_cat_desc']) && $linkspage_pref['link_cat_desc'] ? $tp->toHTML($rowl['link_category_description'], TRUE) : "");
SC_END

SC_BEGIN LINK_MAIN_NUMBER
global $LINK_MAIN_NUMBER, $rowl, $linkspage_pref;
if(isset($linkspage_pref['link_cat_amount']) && $linkspage_pref['link_cat_amount']){
$LINK_MAIN_NUMBER = $rowl['total_links']." ".($rowl['total_links'] == 1 ? LAN_LINKS_17 : LAN_LINKS_18)." ".LAN_LINKS_16;
}else{
$LINK_MAIN_NUMBER = "";
}
return $LINK_MAIN_NUMBER;
SC_END

SC_BEGIN LINK_MAIN_ICON
global $LINK_MAIN_ICON, $rowl, $linkspage_pref;
$LINK_MAIN_ICON = "";
if(isset($linkspage_pref['link_cat_icon']) && $linkspage_pref['link_cat_icon']){
	if (isset($rowl['link_category_icon']) && $rowl['link_category_icon']) {
		if(strstr($rowl['link_category_icon'], "/")){
			if(file_exists(e_BASE.$rowl['link_category_icon'])){
			$LINK_MAIN_ICON = "<img src='".e_BASE.$rowl['link_category_icon']."' alt='' style='border:0; vertical-align:middle' />";
			} else {
				if(isset($linkspage_pref['link_cat_icon_empty']) && $linkspage_pref['link_cat_icon_empty']){
				$LINK_MAIN_ICON = "<img src='".THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' style='border:0; vertical-align:middle;' />";
				}
			}
		}else{
			if(file_exists(e_PLUGIN."links_page/cat_images/".$rowl['link_category_icon'])){
			$LINK_MAIN_ICON = "<img src='".e_PLUGIN."links_page/cat_images/".$rowl['link_category_icon']."' alt='' style='border:0; vertical-align:middle' />";
			} else {
				if(isset($linkspage_pref['link_cat_icon_empty']) && $linkspage_pref['link_cat_icon_empty']){
				$LINK_MAIN_ICON = "<img src='".THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' style='border:0; vertical-align:middle;' />";
				}
			}
		}
	} else {
		if(isset($linkspage_pref['link_cat_icon_empty']) && $linkspage_pref['link_cat_icon_empty']){
		$LINK_MAIN_ICON = "<img src='".THEME."images/".(defined("BULLET") ? BULLET : "bullet2.gif")."' alt='' style='border:0; vertical-align:middle;' />";
		}
	}
	if($rowl['total_links'] && $LINK_MAIN_ICON){
	$LINK_MAIN_ICON = "<a href='links.php?cat.".$rowl['link_category_id']."'>".$LINK_MAIN_ICON."</a>";
	}
}
return $LINK_MAIN_ICON;
SC_END

SC_BEGIN LINK_MAIN_TOTAL
global $LINK_MAIN_TOTAL, $sql, $category_total, $linkspage_pref, $alllinks;
if(isset($linkspage_pref['link_cat_total']) && $linkspage_pref['link_cat_total']){
$LINK_MAIN_TOTAL = LAN_LINKS_21." ".($alllinks == 1 ? LAN_LINKS_22 : LAN_LINKS_23)." ".$alllinks." ".($alllinks == 1 ? LAN_LINKS_17 : LAN_LINKS_18)." ".LAN_LINKS_24." ".$category_total." ".($category_total == 1 ? LAN_LINKS_20 : LAN_LINKS_19);
}else{
$LINK_MAIN_TOTAL = "";
}
return $LINK_MAIN_TOTAL;
SC_END

SC_BEGIN LINK_MAIN_SHOWALL
global $LINK_MAIN_SHOWALL, $linkspage_pref;
return (isset($linkspage_pref['link_cat_total']) && $linkspage_pref['link_cat_total'] ? "<a href='".e_PLUGIN."links_page/links.php?cat.all'>".LAN_LINKS_25."</a>" : "");
SC_END




// LINK_TABLE ------------------------------------------------
SC_BEGIN LINK_BUTTON
global $LINK_BUTTON, $linkspage_pref, $rowl, $LINK_NAME, $LINK_APPEND;
$LINK_BUTTON = "";
if(isset($linkspage_pref['link_icon']) && $linkspage_pref['link_icon']){
	if ($rowl['link_button']) {
		if (strpos($rowl['link_button'], "http://") !== FALSE) {
			$LINK_BUTTON = $LINK_APPEND."\n<img style='border:1px solid #000;' src='".$rowl['link_button']."' alt='' /></a>";
		} else {
			if(strstr($rowl['link_button'], "/")){
				if(file_exists(e_BASE.$rowl['link_button'])){
					$LINK_BUTTON = $LINK_APPEND."\n<img style='border:1px solid #000;' src='".e_BASE.$rowl['link_button']."' alt='' /></a>";
				} else {
					if(isset($linkspage_pref['link_icon_empty']) && $linkspage_pref['link_icon_empty']){
						$LINK_BUTTON = $LINK_APPEND."\n<img style='border:1px solid #000; width: 88px; height: 31px;' src='".e_PLUGIN."links_page/images/generic.png' alt='' /></a>";
					}
				}
			}else{
				$LINK_BUTTON = $LINK_APPEND."\n<img style='border:1px solid #000' src='".e_PLUGIN."links_page/link_images/".$rowl['link_button']."' alt='' /></a>";
			}
		}
	} else {
		if(isset($linkspage_pref['link_icon_empty']) && $linkspage_pref['link_icon_empty']){
			$LINK_BUTTON = $LINK_APPEND."\n<img style='border:1px solid #000; width: 88px; height: 31px;' src='".e_PLUGIN."links_page/images/generic.png' alt='' /></a>";
		}
	}
}else{
	if(isset($linkspage_pref['link_icon_empty']) && $linkspage_pref['link_icon_empty']){
		$LINK_BUTTON = $LINK_APPEND."\n<img style='border:1px solid #000; width: 88px; height: 31px;' src='".e_PLUGIN."links_page/images/generic.png' alt='' /></a>";
	}
}
return $LINK_BUTTON;
SC_END

SC_BEGIN LINK_APPEND
global $LINK_APPEND;
return $LINK_APPEND;
SC_END

SC_BEGIN LINK_NAME
global $LINK_NAME, $rowl;
return $rowl['link_name'];
SC_END

SC_BEGIN LINK_URL
global $LINK_URL, $linkspage_pref, $rowl;
return (isset($linkspage_pref['link_url']) && $linkspage_pref['link_url'] ? $rowl['link_url'] : "");
SC_END

SC_BEGIN LINK_REFER
global $LINK_REFER, $linkspage_pref, $rowl;
return (isset($linkspage_pref['link_referal']) && $linkspage_pref['link_referal'] ? LAN_LINKS_26." ".$rowl['link_refer'] : "");
SC_END

SC_BEGIN LINK_COMMENT
global $LINK_COMMENT, $linkspage_pref, $rowl;
return (isset($linkspage_pref['link_comment']) && $linkspage_pref['link_comment'] ? "<a href='".e_SELF."?comment.".$rowl['link_id']."'>".LAN_LINKS_37." ".($rowl['link_comment'] ? $rowl['link_comment'] : "0")."</a>" : "");
SC_END

SC_BEGIN LINK_DESC
global $LINK_DESC, $linkspage_pref, $tp, $rowl;
return (isset($linkspage_pref['link_desc']) && $linkspage_pref['link_desc'] ? $tp->toHTML($rowl['link_description'], TRUE) : "");
SC_END

SC_BEGIN LINK_RATING
global $LINK_RATING, $LINK_RATED_RATING, $linkspage_pref, $rater, $rowl, $qs;
$LINK_RATING = "";
if(isset($linkspage_pref['link_rating']) && $linkspage_pref['link_rating']){
$LINK_RATING = $rater->composerating("links_page", $rowl['link_id'], $enter=TRUE, $userid=FALSE);
}
return $LINK_RATING;
SC_END

SC_BEGIN LINK_NEW
global $LINK_NEW, $linkspage_pref, $qs, $rowl;
$LINK_NEW = "";
if(USER && $rowl['link_datestamp'] > USERLV){
$LINK_NEW = "<img src='".IMAGE_NEW."' alt='' style='vertical-align:middle' />";
}
return $LINK_NEW;
SC_END


// LINK_TOP_TABLE ------------------------------------------------



// LINK_RATED_TABLE ------------------------------------------------
SC_BEGIN LINK_RATED_RATING
global $LINK_RATED_RATING, $rowl;
$tmp = explode(".", $rowl['rate_avg']);
$one = $tmp[0];
$two = round($tmp[1],1);
$rating = $one.".".$two." ";
for($c=1; $c<= $one; $c++){
	$rating .= "<img src='".e_IMAGE."rate/box.png' alt='' style='height:8px; vertical-align:middle' />";
}
if($one < 10){
	for($c=9; $c>=$one; $c--){
		$rating .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='height:8px; vertical-align:middle' />";
	}
}
$rating .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='height:8px; vertical-align:middle' />";
return $rating;
SC_END

SC_BEGIN LINK_RATED_BUTTON
global $LINK_RATED_BUTTON, $linkspage_pref, $rowl, $LINK_RATED_NAME, $LINK_RATED_APPEND;
if(isset($linkspage_pref['link_icon']) && $linkspage_pref['link_icon']){
	if ($rowl['link_button']) {
		if (strpos($rowl['link_button'], "http://") !== FALSE) {
			$LINK_RATED_BUTTON = $LINK_RATED_APPEND."\n<img style='border:0;' src='".$rowl['link_button']."' alt='".$LINK_RATED_NAME."' /></a>";
		} else {
			if(strstr($rowl['link_button'], "/")){
				$LINK_RATED_BUTTON = $LINK_RATED_APPEND."\n<img style='border:0;' src='".e_BASE.$rowl['link_button']."' alt='".$LINK_RATED_NAME."' /></a>";
			}else{
				$LINK_RATED_BUTTON = $LINK_RATED_APPEND."\n<img style='border:0' src='".e_PLUGIN."links_page/link_images/".$rowl['link_button']."' alt='".$LINK_RATED_NAME."' /></a>";
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

SC_BEGIN LINK_RATED_CATEGORY
global $LINK_RATED_CATEGORY, $rowl, $qs, $tp;
if(!isset($qs[1])){
$LINK_RATED_CATEGORY = "<a href='".e_SELF."?cat.".$rowl['link_category_id']."'>".$tp->toHTML($rowl['link_category_name'], TRUE)."</a>";
}
return $LINK_RATED_CATEGORY;
SC_END

SC_BEGIN LINK_RATED_NAME
global $LINK_RATED_NAME, $rowl;
return $rowl['link_name'];
SC_END

SC_BEGIN LINK_RATED_URL
global $LINK_RATED_URL, $linkspage_pref, $rowl;
return (isset($linkspage_pref['link_url']) && $linkspage_pref['link_url'] ? $rowl['link_url'] : "");
SC_END

SC_BEGIN LINK_RATED_REFER
global $LINK_RATED_REFER, $linkspage_pref, $rowl;
return (isset($linkspage_pref['link_referal']) && $linkspage_pref['link_referal'] ? LAN_LINKS_26." ".$rowl['link_refer'] : "");
SC_END

SC_BEGIN LINK_RATED_DESC
global $LINK_RATED_DESC, $linkspage_pref, $tp, $rowl;
return (isset($linkspage_pref['link_desc']) && $linkspage_pref['link_desc'] ? $tp->toHTML($rowl['link_description'], TRUE) : "");
SC_END





// LINK_SUBMIT_TABLE ------------------------------------------------
SC_BEGIN LINK_SUBMIT_CAT
global $LINK_SUBMIT_CAT;
return $LINK_SUBMIT_CAT;
SC_END

*/
?>
