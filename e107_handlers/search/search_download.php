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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_download.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-03-13 16:16:33 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

//$return_fields = 'download_id, download_category, download_name, download_description, download_author, download_author_website';
$return_fields = 'd.download_id, d.download_category, d.download_name, d.download_description, d.download_author, d.download_author_website, c.download_category_name';
$search_fields = array('d.download_name', 'd.download_description', 'd.download_author', 'd.download_author_website');
$weights = array('1.2', '0.6', '0.6', '0.4');
$no_results = LAN_198;
$where = "";
$order = "";
$table = "download AS d LEFT JOIN #download_category AS c ON d.download_category = c.download_category_id";

$ps = $sch -> parsesearch($table, $return_fields, $search_fields, $weights, 'search_downloads', $no_results, $where, $order);
$text .= $ps['text'];
$results = $ps['results'];

function search_downloads($row) {
	$res['link'] = "download.php?view.".$row['download_id'];
	$res['title'] = $row['download_name'];
	$res['summary'] = $row['download_description'];
	$res['detail'] = LAN_SEARCH_14." ".$row['download_category_name']." | ".LAN_SEARCH_15." ".$row['download_author'];
	return $res;
}

/*
$c = 0;
if ($results = $sql->db_Select("download", "download_id, download_category, download_name, download_author, download_description, 
download_author_website", "(download_name REGEXP('".$query."') OR download_author REGEXP('".$query."') OR download_description  
REGEXP('".$query."') OR download_author_website REGEXP('".$query."')) AND download_active='1'  ")) {
	while (list($download_id, $download_category, $download_name, $download_author, $download_description, $download_author_website) = $sql->db_Fetch()) {
		 
		$download_name = parsesearch($download_name, $query);
		 
		$download_author = parsesearch($download_author, $query);
		 
		$download_description = parsesearch($download_description , $query);
		 
		$download_author_website = parsesearch($download_author_website, $query);
		 
		 
		$action = "download.php?view.".$download_id."";
		$text .= "<form method='post' action='$action' id='download_".$c."'>
			<input type='hidden' name='highlight_search' value='1' /><input type='hidden' name='search_query' value='$query' />
			<img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='javascript:document.getElementById(\"download_".$c."\").submit()'>
			$download_name</a></b></form><br />$download_description<br /><br />";
		$c ++;
		 
	}
} else {
	$text .= LAN_198;
}
*/
?>