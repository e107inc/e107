global $sql;
global $ns;
global $menu_pref;
$ret = "";
//$campaign = $parm;

require_once(e_HANDLER."banner_class.php");
$bn = new banner;

if($menu_pref['banner_campaign']){	
	//if campaign(s) are present, set query
	$campaignlist = explode("|", $menu_pref['banner_campaign']);
	unset($campaignlist_query);
	for($i = 0 ;$i < count($campaignlist) ; $i++){
		$campaignlist_query .= " SUBSTRING_INDEX(banner_campaign, '^', 1) = '".$campaignlist[$i]."' OR ";
	}
	$campaignlist_query = " AND (".substr($campaignlist_query, 0, -3).")";
	$bannerarray = $bn -> getBanner($campaignlist_query);
}else{
	$bannerarray = $bn -> getBanner();
}


// ##### set banner_id query for banner retrieval
unset($banneridquery);
for($i = 0 ;$i < count($bannerarray) ; $i++){
	$banneridquery .= " banner_id = '".$bannerarray[$i]."' OR ";
}
$banneridquery = substr($banneridquery, 0, -3);

// ##### retrieve banners from db
unset($text);
mt_srand ((double) microtime() * 1000000);
$seed = mt_rand(1,2000000000);

$sql2 = new db;
if($sql -> db_Select("banner", "*", $banneridquery." ORDER BY RAND($seed) ")){
	while($row = $sql -> db_Fetch()){
	extract($row);

		$fileext1 = substr(strrchr($banner_image, "."), 1);
		$fileext2 = substr(strrchr($banner_image, "."), 0);
		if ($fileext1 == 'swf') {
			return "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0\" width=\"468\" height=\"60\">\n<param name=\"movie\" value=\"".e_IMAGE."banners/".$banner_image."\">\n<param name=\"quality\" value=\"high\"><param name=\"SCALE\" value=\"noborder\">\n<embed src=\"".e_IMAGE."banners/".$banner_image."\" width=\"468\" height=\"60\" scale=\"noborder\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed></object>";
		} elseif($fileext1 == 'php' || $fileext1 == 'html' || $fileext1 == 'js') {
			include(e_IMAGE."banners/".$banner_image);
			return "";
		} else {
			return "<a href='".e_BASE."banner.php?".$banner_id."' rel='external'><img src='".e_IMAGE."banners/".$banner_image."' alt='".$banner_clickurl."' style='border:0' /></a>";
		}
	
		$sql2 -> db_Update("banner", "banner_impressions=banner_impressions+1 WHERE banner_id='$banner_id' ");

	}
}

