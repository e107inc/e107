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
require_once("classes/rate_class.php");

$gen = new convert;
$rater = new rater;

if(e_QUERY){
	$qs = explode(".", e_QUERY);
	if(IsSet($_POST['records'])){
		$dtype = $qs[0];
		$from = $qs[1];
		$records = $_POST['records'];
		$order = $_POST['order'];
		$sort = $_POST['sort'];
	}else if($qs[0] == ""){
		$records = 20;
		$from = 0;
		$order="DESC";
	}else if(ereg("!", $_SERVER['QUERY_STRING'])){
		$from = $qs[0];
		$dtype = $qs[1];
		$records = $qs[2];
		$order = $qs[3];
		$sort = $qs[4];

	}else{
		$qs = explode(".", $_SERVER['QUERY_STRING']);
		$dtype = $qs[0];
		$from = $qs[1];
		$records = $qs[2];
		$order = $qs[3];
		$sort = $qs[4];
	}
	if($order == ""){
		$order = "DESC";
	}
	if($from == ""){
		$from = 0;
	}
	if($records == ""){
		$records = 5;
	}
	if($sort == ""){
		$sort = "download_datestamp";
	}

	$sql -> db_Select("download_category", "*", "download_category_id='$dtype'");
	$row = $sql -> db_Fetch(); extract($row);
	$core_total = $sql -> db_Count("download WHERE download_category='$dtype' AND download_active=1");
	if($records > $core_total){ $records = $core_total; }
	$sql -> db_Select("download", "*", "download_category='$dtype' AND download_active=1 ORDER BY $sort $order LIMIT $from,$records");
	$type = $download_category_name." <span class=\"smalltext\">[ ".$download_category_description." ]";
	

	if($core_total == 0){
		$text = "<div class=\"mediumtext\" style=\"text-align:center\">There are no downloads in this category yet.<br /><br /><a href=\"download.php\">Back to downloads page</a></div>";
		$ns -> tablerender("No downloads", $text);
		require_once(FOOTERF);
		exit;
	}else{

	$text .= "
	<div style=\"text-align:center\" class=\"spacer\">
	<form method=\"post\" action=\"download.php?".$dtype."\">
	<p>
		Show ";
	 if($records == 5){
	$text .= "<select name=\"records\" class=\"tbox\">
	<option selected>5</option>
	<option>10</option>
	<option>20</option>
	<option>30</option>
	</select>  ";

	}else if($records == 10){
		$text .= "<select name=\"records\" class=\"tbox\">
	<option>5</option>
	<option selected>10</option>
	<option>20</option>
	<option>30</option>
	</select>  ";
	}else if($records == 20){
		$text .= "<select name=\"records\" class=\"tbox\">
	<option>5</option>
	<option>10</option>
	<option selected>20</option>
	<option>30</option>
	</select>  ";
	}else{
		$text .= "<select name=\"records\" class=\"tbox\">
	<option>5</option>
	<option>10</option>
	<option>20</option>
	<option selected>30</option>
	</select>  ";
	}
	$text .= "&nbsp;&nbsp;Sort by 
	<select name=\"sort\" class=\"tbox\">";

	if($sort == "download_datestamp"){
		$text .= "
	<option selected value=\"download_datestamp\">Date</option>
	<option value=\"download_requested\">Downloads</option>
	</select>  ";
	}else if($sort == "download_requested"){
		$text .= "
	<option value=\"download_datestamp\">Date</option>
	<option selected value=\"download_requested\">Downloads</option>
	</select>  ";
	}

	$text .= "&nbsp;&nbsp;Order ";

	if($order == "ASC"){
		$text .= "<select name=\"order\" class=\"tbox\">
	<option>DESC</option>
	<option selected>ASC</option>
	</select>";
	}else{
		$text .= "<select name=\"order\" class=\"tbox\">
	<option selected>DESC</option>
	<option>ASC</option>
	</select>";
	}

	$text .= " <input class=\"button\" type=\"submit\" name=\"submit\" value=\"Go\" />
	<input type=\"hidden\" name=\"from\" value=\"$from\" />
	</form>
	</div>";

		$text .= "<table style=\"width:96%\" class=\"fborder\">";
		while(list($download_id, $download_name, $download_url, $download_author, $download_author_email, $download_author_website, $download_description, $download_filesize, $download_requested, $download_type, $download_active, $download_datestamp, $download_thumb, $download_image) = $sql-> db_Fetch()){
		$datestamp = $gen->convert_date($download_datestamp, "long");

		$text .= "<tr>
		<td colspan=\"3\" style=\"text-align:left\" class=\"forumheader\"><span class=\"defaulttext\"><b>$download_name</b></span></td>
		</tr>
		<tr><td class=\"forumheader3\" style=\"width:20%\">Author:</td><td colspan=\"2\" class=\"forumheader3\" style=\"width:80%\">$download_author</td></tr>";
		if($download_author_email != ""){
			$text .= "<tr><td class=\"forumheader3\" style=\"width:20%\">Author email:</td><td colspan=\"2\" class=\"forumheader3\" style=\"width:80%\"><a href=\"mailto:".$download_author_email."\">".$download_author_email."</a></td></tr>";
		}
		if($download_author_website != ""){
			$text .= "<tr><td class=\"forumheader3\" style=\"width:20%\">Author website:</td><td colspan=\"2\" class=\"forumheader3\" style=\"width:80%\"><a href=\"".$download_author_website."\">$download_author_website</a></td></tr>";
		}
		if($download_description != ""){
			$text .= "<tr><td class=\"forumheader3\" style=\"width:20%; vertical-align:top\">Description:</td><td colspan=\"2\" class=\"forumheader3\" style=\"width:80%\">".$download_description."</td></tr>";
		}

		if($download_thumb != ""){
			$text .= "<tr><td class=\"forumheader3\" style=\"width:20%; vertical-align:top\">Image:</td><td colspan=\"2\" class=\"forumheader3\" style=\"width:80%; text-align:center\">";
			if($download_image != ""){
				$text .= " <a href=\"".e_BASE."files/downloadimages/".$download_image."\"><img src=\"".e_BASE."files/downloadthumbs/".$download_thumb."\" alt=\"\" style=\"border:1px solid black\" /></a>";
			}else{
				$text .= "<img src=\"".e_BASE."files/downloadthumbs/".$download_thumb."\" alt=\"\" style=\"border:1px solid black\" />";
			}
			$text .= "</td></tr>";
		}
		$tdownloads += $download_requested;
		$text .= "
		<tr><td class=\"forumheader3\" style=\"width:20%\">Filesize</td><td colspan=\"2\" class=\"forumheader3\" style=\"width:80%\">".parsesize($download_filesize)."</td></tr>
		<tr><td class=\"forumheader3\" style=\"width:20%\">Downloads</td><td colspan=\"2\" class=\"forumheader3\" style=\"width:80%\">".$download_requested."</td></tr>
		<tr><td class=\"forumheader3\" style=\"width:20%; vertical-align:top\">Download</td><td colspan=\"2\" class=\"forumheader3\" style=\"width:80%\"><a href=\"request.php?".$download_id."\"><img src=\"themes/shared/generic/download.png\" alt=\"\" style=\"border:0\" /></a></td></tr>";

		$text .= "<tr><td class=\"forumheader3\" style=\"width:20%; vertical-align:top\">Rating:</td><td class=\"forumheader3\" style=\"width:40%;vertical-align:middle\">";
		if($ratearray = $rater -> getrating("download", $download_id)){
			for($c=1; $c<= $ratearray[1]; $c++){
				$text .= "<img src=\"themes/shared/rate/star.png\" alt=\"\">";
			}
			if($ratearray[2]){
				$text .= "<img src=\"themes/shared/rate/".$ratearray[2].".png\"  alt=\"\">";
			}
			if($ratearray[2] == ""){ $ratearray[2] = 0; }
				$text .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
				$text .= ($ratearray[0] == 1 ? "vote" : "votes");
			}else{
				$text .= "Not rated yet";
			}

			$text .= "</td><td class=\"forumheader3\" style=\"vertical-align:middle; text-align:right\">";


			if(!$rater -> checkrated("download", $download_id) && USER){
				$text .= $rater -> rateselect("&nbsp;&nbsp;&nbsp;&nbsp; <b>Rate this download", "download", $download_id)."</b>";
			}else{
				$text .= "<span class=\"smalltext\">Thanks for rating</span>";
			}
		}
		$text .= "
		</td></tr>";

	}

	$text .= "</table>
	<div style=\"text-align:center\">$tdownloads downloads from $records files</div>";

	$ns -> tablerender("Downloads: ".$type, $text);

	$nx = new nextprev("download.php", $from, $records, $core_total, "Downloads", $dtype.".".$records.".".$order.".".$sort.".!");
	require_once(FOOTERF);
	exit;
}




























// ----- no qs, render category list ----- //

$text = "<div style=\"text-align:center\">
<table style=\"width:95%\" class=\"fborder\">";
if(!$sql -> db_Select("download_category", "*", "download_category_parent='0' ")){
	$text .= "<tr><td style=\"text-align:center\">No downloads yet, please check back soon</td></tr>";
}else{
	$sql2 = new db; $sql3 = new db;

	while($row = $sql-> db_Fetch()){
		extract($row);
		if($download_category_class){
			if(check_class($download_category_class)){
				$text .= "<tr>
				<td style=\"width:5%; text-align:center\" class=\"forumheader3\"><img src=\"".THEME."images/".$download_category_icon."\" alt=\"\" /></td>
				</td>
				<td style=\"width:95%\" colspan=\"2\" class=\"forumheader3\">
				<span class=\"captiontext\">".$download_category_name." (Restricted)</span></td></tr>";
				$parent_status == "open";
			}else{
				break;
			}
		}else{
			$text .= "<tr>
			<td style=\"width:5%; text-align:center\" class=\"forumheader3\"><img src=\"".THEME."images/".$download_category_icon."\" alt=\"\" /></td>
			</td>
			<td style=\"width:95%\" colspan=\"2\" class=\"forumheader3\">
			<span class=\"captiontext\">".$download_category_name."</span></td></tr>";
			$parent_status == "open";
		}
	

		$categories = $sql2 -> db_Select("download_category", "*", "download_category_parent='".$download_category_id."' ");
		if($categories == 0){
			$text .= "<td colspan=\"5\" align=\"center\">No downloads in this category yet, please check back soon.<br /><br /></td>";
		}else{
			while($row = $sql2-> db_Fetch()){
				extract($row);

				if($filecount = $sql3 -> db_Select("download", "*", "download_category='$download_category_id'")){
					while($row = $sql3 -> db_Fetch()){
						extract($row);
						$total_filesize += $download_filesize;
					}
					$total_filesize = parsesize($total_filesize);
					$total_downloadcount += $download_requested;
				}else{
					$total_filesize = "0";
				}
				
				if(!$download_category_class || ($download_category_class && check_class($download_category_class))){
					$text .= "
					<tr>
					<td style=\"width:5%; text-align:center\" class=\"forumheader3\"><img src=\"".THEME."images/".$download_category_icon."\" alt=\"\" /></td>
					<td style=\"width:20%\" class=\"forumheader3\"><a href=\"".e_SELF."?".$download_category_id."\">".$download_category_name."</a></td>
					<td style=\"width:60%\" class=\"forumheader3\">".$download_category_description ."</td>
					</tr>
					<tr>
					<td colspan=\"3\" class=\"forumheader2\"><span class=\"defaulttext\">[ Files available: $filecount ] [ Total size of files: $total_filesize ] [ Files downloaded: $download_requested ]</span></td>
					</tr>";
					unset($total_filesize);
				}
			}
		}
	}
}

$text .= "</table>
</div>";
$ns -> tablerender("Downloads".$type, $text);
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