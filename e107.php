<?php
$qs = $_SERVER['QUERY_STRING'];
if($qs == ""){ header("location:index.php"); }
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
$gen = new convert;

if($qs == 1){
	$core_total = $sql -> db_Select("download", "*", "download_type='core' AND download_active=1");
	$type = "Core";
}else if($qs == 2){
	$core_total = $sql -> db_Select("download", "*", "download_type='plugin' AND download_active=1 ORDER BY download_datestamp DESC");
	$type = "Plugin";
}else if($qs == 3){
	$core_total = $sql -> db_Select("download", "*", "download_type='theme' AND download_active=1 ORDER BY download_datestamp DESC ");
	$type = "Theme";
}else{
	header("location:index.php");
}

if($core_total == 0){
	$text = "<div class=\"mediumtext\" style=\" text-align:center\">There are no downloads in the $type category yet.</div>";
	$null = TRUE;
}else{
	$ns -> tablerender($type." Downloads", "");
	$text .= "<table style=\"width:100%\" class=\"fborder\" cellspacing=\"6\">";
	while(list($download_id, $download_name, $download_url, $download_author, $download_author_email, $download_author_website, $download_description, $download_filesize, $download_requested, $download_type, $download_active, $download_datestamp, $download_thumb, $download_image) = $sql-> db_Fetch()){
		$datestamp = $gen->convert_date($download_datestamp, "long");
		
		$text .= "<tr>
		<td style=\"text-align:center\" class=\"fcaption\">$download_name</td>
		</tr>
		<tr>
		<td>
		<b>By:</b> $download_author</br>";
		if($download_author_email != ""){
			$text .= "<b>Email:</b> <a href=\"mailto:".$download_author_email."\">".$download_author_email."</a><br />";
		}
		if($download_author_website != ""){
			$text .= "<b>Website:</b> <a href=\"".$download_author_website."\">$download_author_website</a><br />";
		}
		if($download_description != ""){
			$text .= "<b>Description:</b> ".$download_description."<br />";
		}
		
		if($download_thumb != ""){
			$text .= "<b>Image:</b>";
			if($download_image != ""){
				$text .= " (Click for full view)<br /><a href=\"".$download_image."\"><img src=\"".$download_thumb."\" alt=\"\" style=\"border:1px solid black\" /></a>";
			}else{
				$text .= "<br /><img src=\"".$download_thumb."\" alt=\"\" style=\"border:1px solid black\" />";
			}
		}
		
		$text .= "<br /><br />
		<a href=\"download.php?".$download_id."\"><img src=\"themes/shared/generic/file.png\" alt=\"\" style=\"border:0\" /> Download</a> 
		[ <b>Filesize:</b> $download_filesize ] [ <b>Downloaded:</b> $download_requested ]
		</td></tr>";



/*
$text .= "<td colspan=\"2\" class=\"caption\" style=\"width:50%\"><span class=\"mediumtext\"><b>".$download_name."</b></span>
<br />
by <a href=\"mailto:".$download_author_email."\">".$download_author."</a>";
	if($download_author_website != ""){
		$text .= " [<a href=\"".$download_author_website."\">website</a>]";
	}
	$text .= "<br />$datestamp</td>
</tr>";


if($download_thumb != ""){
	$text .= "<tr>
<td style=\"width:50%\">";
if($download_image != ""){
	$text .= "<a href=\"".$download_image."\"><img src=\"".$download_thumb."\" alt=\"\" style=\"border:1px solid black\" /></a>";
}else{
	$text .= "<img src=\"".$download_thumb."\" alt=\"\" style=\"border:1px dotted black\" />";
}

$text .= "</td>
<td style=\"text-align:left; vertical-align:top; width:50%\">".$download_description."</td>
</tr>";
}else{

	$text .= "<tr> 
<td colspan=\"2\">".$download_description."</td>
</tr>";
}

$text .= "<tr> 
<td><a href=\"download.php?".$download_id."\"><img src=\"themes/shared/generic/file.png\" alt=\"\" style=\"border:0\" /> Download</a> [ ".$download_filesize." ]</td>
<td style=\"text-align:right\">[ Downloads: ".$download_requested." ] </td>
</tr>
</table>
<br />";

*/

$downloads += $download_requested;
	}
}

$text .= "</table>";

if($null != TRUE){
	$text .= "<div style=\"text-align:center\">$downloads downloads from $core_total files</div>";
}
echo $text;

if($qs == 1){
	$text = "<img src=\"files/images/admin.png\" alt=\"\" /><br />Admin area<br />
<img src=\"files/images/newspost.png\" alt=\"\" /><br />News post page<br />
<img src=\"files/images/prefs.png\" alt=\"\" /><br />Preferences page";
//$ns -> tablerender("Screenshots", $text);
}






require_once(FOOTERF);
?>