global $sql;
global $ns;
$ret = "";
$campaign = $parm;
mt_srand ((double) microtime() * 1000000);
$seed = mt_rand(1,2000000000);
if($campaign != "") {
	$query = "banner_active=1 AND (banner_startdate=0 OR banner_startdate<=".time().") AND (banner_enddate=0 OR banner_enddate>".time().") AND (banner_impurchased = 0 OR banner_impressions <= banner_impurchased) AND banner_campaign='$campaign' ORDER BY RAND($seed)";
} else {
	$query = "banner_active=1 AND (banner_startdate=0 OR banner_startdate<=".time().") AND (banner_enddate=0 OR banner_enddate>".time().") AND (banner_impurchased = 0 OR banner_impressions <= banner_impurchased) ORDER BY RAND($seed)";
}
if($sql -> db_Select("banner", "*", $query)) {
	$row = $sql -> db_Fetch(); extract($row);
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
}
$sql -> db_Update("banner", "banner_impressions=banner_impressions+1 WHERE banner_id='$banner_id' ");
