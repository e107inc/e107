<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/e107.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

if(!e_QUERY){
	// no qs - render categories ...

	if(!$sql -> db_Select("download_category", "*", "download_category_parent='0' ")){
		$text .= "<div style='text-align:center'>No downloads yet, please check back soon</div>>";
	}else{
		$sql2 = new db; $sql3 = new db;
		while($row = $sql-> db_Fetch()){
		extract($row);

		if($download_category_class){
			if(check_class($download_category_class)){
				$text .= "
				<img src='".THEME."images/".$download_category_icon."' alt='' style='float-left' />

				<b>Category: <u>".$download_category_name." ".LAN_354."</u></b><br />";
				$parent_status == "open";
			}else{
				break;
			}
		}else{
			$text .= "<img src='".THEME."images/".$download_category_icon."' alt='' style='float-left' />
			<b>Category: <u>".$download_category_name."</u></b><br />";
			$parent_status == "open";
		}
	

		$categories = $sql2 -> db_Select("download_category", "*", "download_category_parent='".$download_category_id."' ");
		if($categories == 0){
			$text .= LAN_355."<br /><br />";
		}else{
			while($row = $sql2-> db_Fetch()){
				extract($row);
				$total_filesize=0; $total_downloadcount=0;
				if($filecount = $sql3 -> db_Select("download", "*", "download_category='$download_category_id'")){
					while($row = $sql3 -> db_Fetch()){
						extract($row);
						$total_filesize += $download_filesize;
						$total_downloadcount += $download_requested;
					}
					$total_filesize = parsesize($total_filesize);	
				}
				
				if(!$download_category_class || ($download_category_class && check_class($download_category_class))){
					$text .= "
					<img src='".THEME."images/".$download_category_icon."' alt='' style='float-left'  /> ".
					($filecount ? "<a href='".e_SELF."?".$download_category_id."'>".$download_category_name."</a>" : $download_category_name)."
					 [ ".$download_category_description ." ] <br />
					<span class='smalltext'>[ ".LAN_358."$filecount ] [ ".LAN_356."$total_filesize ] [ ".LAN_357."$total_downloadcount ]</span><br /><br />
					";
					
				}
			}
		}
	}
}

$text .= "
</div>";
$ns -> tablerender("Downloads".$type, $text);
require_once(FOOTERF);
exit;


}
require_once("classes/rate_class.php");
$gen = new convert;
$rater = new rater;
unset($text);
$dtype = e_QUERY;
$sql -> db_Select("download_category", "*", "download_category_id='$dtype'");
$row = $sql -> db_Fetch(); extract($row);
$core_total = $sql -> db_Count("download WHERE download_category='$dtype' AND download_active=1");
$sql -> db_Select("download", "*", "download_category='$dtype' AND download_active=1 ORDER BY download_datestamp ASC");
$type = $download_category_name." <span class='smalltext'>[ ".$download_category_description." ]";

while($row = $sql -> db_Fetch()){
	extract($row);
	$datestamp = $gen->convert_date($download_datestamp, "long");

	$text .= "<div style='text-align:center'>
	<table style='width:95%'>
	<tr>
	<td style='width:50%'>
	<a href='request.php?".$download_id."'><img src='themes/shared/generic/download.png' alt='' style='border:0' /></a> 
	<b><u><a href='request.php?".$download_id."'>".$download_name."</a></u></b>
	</td>

	<td style='width:50%; text-align:right'>
	<a href='request.php?".$download_id."'><img src='themes/shared/generic/download.png' alt='' style='border:0' /></a>
	</td>
	</tr>

	<tr>
	<td class='smalltext' colspan='2'>".$datestamp."</td>
	</tr>

	<tr>
	<td class='defaulttext' colspan='2'>by: <b>".$download_author."</b><br />";
	
	if($download_author_email != ""){
		$text .= " [ <a href='mailto:".$download_author_email."'>".$download_author_email."</a> ]";
	}
	if($download_author_website != ""){
		$text .= " [ <a href='".$download_author_website."'>$download_author_website</a> ]";
	}
	
	$text .= "</td></tr>";


	if($download_description != ""){
		$text .= "<tr><td colspan='2' class='defaulttext'>".$download_description."</td></tr>";
	}

	if($download_thumb != ""){
		$text .= "<tr><td colspan='2'>Image: ";
		if($download_image != ""){
			$text .= " <a href='".e_BASE."files/downloadimages/".$download_image."'><img src='".e_BASE."files/downloadthumbs/".$download_thumb."' alt='' style='border:1px solid black' /></a>";
		}else{
			$text .= "<img src='".e_BASE."files/downloadthumbs/".$download_thumb."' alt='' style='border:1px solid black' />";
		}
		$text .= "</td></tr>";
	}

	$tdownloads += $download_requested;
	$text .= "<tr>
	<td colspan='2'>Filesize: ".parsesize($download_filesize)."</td>
	</tr>

	<tr>
	<td colspan='2'>Downloads: ".$download_requested."
	</td>
	
	<tr>
	<td style='width:50%'>".LAN_370;
	if($ratearray = $rater -> getrating("download", $download_id)){
		for($c=1; $c<= $ratearray[1]; $c++){
			$text .= "<img src='themes/shared/rate/star.png' alt=''>";
		}
		if($ratearray[2]){
			$text .= "<img src='themes/shared/rate/".$ratearray[2].".png'  alt=''>";
		}
		if($ratearray[2] == ""){ $ratearray[2] = 0; }
			$text .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
			$text .= ($ratearray[0] == 1 ? "vote" : "votes");
		}else{
			$text .= LAN_369;
		}

		$text .= "</td><td style='width:50%; text-align:right'>";

		if(!$rater -> checkrated("download", $download_id) && USER){
			$text .= $rater -> rateselect("&nbsp;&nbsp;&nbsp;&nbsp; <b>".LAN_359, "download", $download_id)."</b>";
		}else if(!USER){
			$text .= "&nbsp;";
		}else{
			$text .= LAN_360;
		}
	
	$text .= "</td></tr></table></div><br />";
}
	

$text .= "
<div style='text-align:center'>$tdownloads ".LAN_361." $core_total ".LAN_362."</div>";
$ns -> tablerender("<a href='".e_SELF."'>".LAN_363."</a>: ".$type, $text);
require_once(FOOTERF);





function parsesize($size){
	$kb = 1024;
	$mb = 1024 * $kb;
	$gb = 1024 * $mb;
	$tb = 1024 * $gb;
	if($size < $kb) {
		return $size." b";
	}else if($size < $mb) {
		return round($size/$kb,2)." kb";
	}else if($size < $gb) {
		return round($size/$mb,2)." mb";
	}else if($size < $tb) {
		return round($size/$gb,2)." gb";
	}else {
		return round($size/$tb,2)." tb";
	}
}
?>