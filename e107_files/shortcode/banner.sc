global $sql;
global $ns;
global $menu_pref;
$ret = "";

unset($text);
mt_srand ((double) microtime() * 1000000);
$seed = mt_rand(1,2000000000);

$query = " (banner_startdate=0 OR banner_startdate<=".time().") AND (banner_enddate=0 OR banner_enddate>".time().") AND (banner_impurchased=0 OR banner_impressions<=banner_impurchased)".($parm ? " AND banner_campaign='$parm'" : "")." 
AND banner_active IN (".USERCLASS_LIST.")
ORDER BY RAND($seed)";

if($sql -> db_Select("banner", "*", $query))
{
	$row = $sql->db_Fetch();

	if(!$row['banner_image'])
	{
		return "<a href='".e_BASE."banner.php?".$row['banner_id']."' rel='external'>no image assigned to this banner</a>";
	}

	$fileext1 = substr(strrchr($row['banner_image'], "."), 1);
	$sql->db_Update("banner", "banner_impressions=banner_impressions+1 WHERE banner_id='{$row['banner_id']}' ");
	if ($fileext1 == 'swf')
	{
		return "
		<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0\" width=\"468\" height=\"60\">\n
			<param name=\"movie\" value=\"".e_IMAGE."banners/".$row['banner_image']."\">\n
			<param name=\"quality\" value=\"high\">\n
			<param name=\"SCALE\" value=\"noborder\">\n
			<embed src=\"".e_IMAGE."banners/".$row['banner_image']."\" width=\"468\" height=\"60\" scale=\"noborder\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed>
		</object>
		";
	}
	elseif($fileext1 == 'php' || $fileext1 == 'html' || $fileext1 == 'js')
	{
		include(e_IMAGE."banners/".$row['banner_image']);
		return "";
	}
	else
	{
		return "<a href='".e_BASE."banner.php?".$row['banner_id']."' rel='external'><img src='".e_IMAGE."banners/".$row['banner_image']."' alt='".$row['banner_clickurl']."' style='border:0' /></a>";
	}
}