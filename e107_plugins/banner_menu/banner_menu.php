<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/banner_menu/banner_menu.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-02-13 08:55:45 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
	
require_once(e_HANDLER."banner_class.php");
$bn = new banner;
	
if (isset($menu_pref['banner_campaign']) && $menu_pref['banner_campaign']) {
	//if campaign(s) are present, set query
	$campaignlist = explode("|", $menu_pref['banner_campaign']);
	unset($campaignlist_query);
	for($i = 0 ; $i < count($campaignlist) ; $i++) {
		$campaignlist_query .= " SUBSTRING_INDEX(banner_campaign, '^', 1) = '".$campaignlist[$i]."' OR banner_campaign = '".$campaignlist[$i]."' OR ";
	}
	$campaignlist_query = " AND (".substr($campaignlist_query, 0, -3).")";
	$bannerarray = $bn->getBanner($campaignlist_query);
} else {
	$bannerarray = $bn->getBanner();
}
	
// ##### set limit for amount of banners that need to be shown
if (!isset($menu_pref['banner_rendertype']) || !$menu_pref['banner_rendertype'] || $menu_pref['banner_rendertype'] == "1") {
	//show one banner from one campaign in single menu
	$limitbanner = "1";
} elseif($menu_pref['banner_rendertype'] == "2") {
	//show one banner from all campaigns in single menu
	$limitbanner = ($menu_pref['banner_amount'] ? $menu_pref['banner_amount'] : "1");
} elseif($menu_pref['banner_rendertype'] == "3") {
	//show one banner from all campaigns in multiple menus
	$limitbanner = ($menu_pref['banner_amount'] ? $menu_pref['banner_amount'] : "1");
}
	
// ##### set banner_id query for banner retrieval
$banneridquery = '';
for($i = 0 ; $i < count($bannerarray) ; $i++) {
	$banneridquery .= " banner_id = '".$bannerarray[$i]."' OR ";
}
$banneridquery = substr($banneridquery, 0, -3);
	
	
// ##### retrieve banners from db
unset($text);
mt_srand ((double) microtime() * 1000000);
$seed = mt_rand(1, 2000000000);
	
$sql2 = new db;
$sql->db_Select("banner", "*", $banneridquery." ORDER BY RAND($seed) LIMIT ".$limitbanner." ");
while ($row = $sql->db_Fetch()) {
	extract($row);
	 
	$banner[] = "<a href='".e_BASE."banner.php?".$banner_id."'  rel='external'><img src='".e_IMAGE."banners/".$banner_image."' alt='".$banner_clickurl."' style='border:0' /></a>";
	 
	$sql2->db_Update("banner", "banner_impressions=banner_impressions+1 WHERE banner_id='$banner_id' ");
}
	
	
$textstart = "<div style='text-align:center'>";
$textend = "</div>";
$textsep = "<br /><br />";
	
	
//show the banner(s) based on banner_rendertype setting
if (isset($menu_pref['banner_rendertype']) && $menu_pref['banner_rendertype'] == "1") {
	//show one banner from one campaign in single menu
	$text = $textstart.$banner[0].$textend;
	$ns->tablerender($menu_pref['banner_caption'], $text);
	unset($text);
	 
} elseif(isset($menu_pref['banner_rendertype']) && $menu_pref['banner_rendertype'] == "2") {
	//show one banner from all campaigns in single menu
	for($i = 0 ; $i < count($banner) ; $i++) {
		$text .= $banner[$i].$textsep;
	}
	$text = substr($text, 0, -strlen($textsep));
	$text = $textstart.$text.$textend;
	$ns->tablerender($menu_pref['banner_caption'], $text);
	unset($text);
	 
} elseif(isset($menu_pref['banner_rendertype']) && $menu_pref['banner_rendertype'] == "3") {
	//show one banner from all campaigns in multiple menus
	for($a = 0 ; $a < count($banner) ; $a++) {
		$text = $textstart.$banner[$a].$textend;
		$ns->tablerender($menu_pref['banner_caption'], $text);
		unset($text);
	}
}
	
?>