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
|     $Source: /cvs_backup/e107_0.7/request.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-03-01 19:08:18 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
if (!e_QUERY) {
	header("location: ".e_BASE."index.php");
	exit;
}

if (!is_numeric(e_QUERY)) {
	if ($sql->db_Select("download", "download_id", "download_url='".e_QUERY."'", TRUE)) {
		$row = $sql->db_Fetch();
		$type = "file";
		$id = $row['download_id'];
	}
	else if(file_exists($DOWNLOADS_DIRECTORY.e_QUERY)) {
		send_file($DOWNLOADS_DIRECTORY.e_QUERY);
		exit;
	}
	else if(strstr(e_QUERY, "http:") || strstr(e_QUERY, "ftp:")) {
		header("location:".e_QUERY);
		exit;
	}
}
	
$tmp = explode(".", e_QUERY);
if (!$tmp[1]) {
	$id = $tmp[0];
	$type = "file";
} else {
	$table = $tmp[0];
	$id = $tmp[1];
	$type = "image";
}
if (preg_match("#.*\.[a-z,A-Z]{3,4}#", e_QUERY)) {
	if (file_exists($DOWNLOADS_DIRECTORY.e_QUERY)) {
		send_file($DOWNLOADS_DIRECTORY.e_QUERY);
		exit;
	}
	return;
}
	
if ($type == "file") {
	$qry = "
	SELECT d.*, dc.download_category_class FROM #download as d
	LEFT JOIN #download_category AS dc ON dc.download_category_id = d.download_id
	WHERE d.download_id = $id;
	";
	
	if ($sql->db_Select_gen($qry))
	{
		$row = $sql->db_Fetch();
		if (check_class($row['download_category_class']) && check_class($row['download_class']))
		{
			if($pref['download_limits'] && $row['download_active'] == 1)
			{
				check_download_limits();
			}
			extract($row);
			//increment download count
			$sql->db_Update("download", "download_requested=download_requested+1 WHERE download_id='$id' ");
			$user_id = USER ? USERID : 0;
			$ip = getip();
			$request_data = "'0', '{$user_id}', '{$ip}', '$id', '".time()."'";
			//add request info to db
			$sql->db_Insert("download_requests", $request_data, TRUE);
			if (preg_match("/Binary\s(.*?)\/.*/", $download_url, $result)) {
				$bid = $result[1];
				$result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id='$bid' ");
				$binary_data = @mysql_result($result, 0, "binary_data");
				$binary_filetype = @mysql_result($result, 0, "binary_filetype");
				$binary_name = @mysql_result($result, 0, "binary_name");
				header("Content-type: ".$binary_filetype);
				header("Content-length: ".$download_filesize);
				header("Content-Disposition: attachment; filename=".$binary_name);
				header("Content-Description: PHP Generated Data");
				echo $binary_data;
				exit;
			}
			if (strstr($download_url, "http:") || strstr($download_url, "ftp:")) {
				header("location:".$download_url);
				exit;
			} else {
				if (file_exists($DOWNLOADS_DIRECTORY.$download_url)) {
					send_file($DOWNLOADS_DIRECTORY.$download_url);
					exit;
				}
				else if(file_exists(e_FILE."public/".$download_url)) {
					send_file(e_FILE."public/".$download_url);
					exit;
				}
			}
		} else {
			echo "<br /><br /><div style='text-align:center; font: 12px Verdana, Tahoma'>You do not have the correct permissions to download this file.</div>";
			exit;
		}
	}
	exit;
}
	
$sql->db_Select($table, "*", $table."_id= '$id' ");
$row = $sql->db_Fetch();
extract($row);
	
$image = ($table == "upload" ? $upload_ss : $download_image);
	
if (preg_match("/Binary\s(.*?)\/.*/", $image, $result)) {
	$bid = $result[1];
	$result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id='$bid' ");
	$binary_data = @mysql_result($result, 0, "binary_data");
	$binary_filetype = @mysql_result($result, 0, "binary_filetype");
	$binary_name = @mysql_result($result, 0, "binary_name");
	header("Content-type: ".$binary_filetype);
	header("Content-Disposition: inline; filename=\"$binary_name\"");
	echo $binary_data;
	exit;
}
	
$image = ($table == "upload" ? $upload_ss : $download_image);
	
if (eregi("http", $image)) {
	 
	header("location:".$image);
	exit;
} else {
	if ($table == "download") {
		require_once(HEADERF);
		if (file_exists(e_FILE."download/".$image)) {
			$disp = "<div style='text-align:center'><img src='".e_FILE."download/".$image."' alt='' /></div>";
		}
		else if(file_exists(e_FILE."downloadimages/".$image)) {
			$disp = "<div style='text-align:center'><img src='".e_FILE."downloadimages/".$image."' alt='' /></div>";
		} else {
			$disp = "<div style='text-align:center'><img src='".e_FILE."public/".$image."' alt='' /></div>";
		}
		$disp .= "<br /><div style='text-align:center'><a href='javascript:history.back(1)'>Back</a></div>";
		$ns->tablerender($image, $disp);
		 
		require_once(FOOTERF);
	} else {
		if (is_file(e_FILE."public/".$image)) {
			echo "<img src='".e_FILE."public/".$image."' alt='' />";
		} elseif(is_file(e_FILE."downloadimages/".$image)) {
			echo "<img src='".e_FILE."downloadimages/".$image."' alt='' />";
		} else {
			echo "Not Found";
		}
		exit;
	}
}
	
	
	
// File retrieval function. by Cam.
	
function send_file($file) {
	global $pref;
	 
	if (!$pref['download_php']) {
		header("location:".SITEURL.$file);
		exit;
	}
	 
	@set_time_limit(10 * 60);
	@ini_set("max_execution_time", 10 * 60);
	 
	$fullpath = $file;
	$file = basename($file);
	if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
		$file = preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1);
	}

	if (is_file($fullpath) && connection_status() == 0) {
		header("Cache-control: private");
		header('Pragma: no-cache');
		header("Content-Type: application/force-download");
		header("Content-Disposition:attachment; filename=\"".trim(htmlentities($file))."\"");
		header("Content-Description: ".trim(htmlentities($file)));
		header("Content-length:".(string)(filesize($fullpath)));
		header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		 
		 
		if ($file = fopen($fullpath, 'rb')) {
			while (!feof($file) and (connection_status() == 0)) {
				print(fread($file, 1024 * 8));
				flush();
			}
			fclose($file);
		}
		 
	} else {
		header("location: ".e_BASE."index.php");
		exit;
	}
}

function check_download_limits()
{
	global $pref, $sql, $ns;

	// Check download count limits
	$qry = "
	SELECT gen_intdata, gen_chardata, (gen_intdata/gen_chardata) as count_perday 
	FROM #generic 
	WHERE gen_type = 'download_limit' AND gen_datestamp IN (".USERCLASS_LIST.") AND (gen_chardata > 0 AND gen_intdata > 0)
	ORDER BY count_perday DESC
	";
	if($sql->db_Select_gen($qry))
	{
		$limits = $sql->db_Fetch();
//		echo "<br />Allowed {$limits['gen_intdata']} downloads every {$limits['gen_chardata']} days <br />";
		$cutoff = time() - (86400*$limits['gen_chardata']);
		if(USER)
		{
			$where = "dr.download_request_datestamp > $cutoff AND dr.download_request_userid = ".USERID;
		}
		else
		{
			$ip = getip();
			$where = "dr.download_request_datestamp > $cutoff AND dr.download_request_ip = '$ip'";
		}

		$qry = "
		SELECT COUNT(d.download_id) as count
		FROM #download_requests as dr
		LEFT JOIN #download as d ON dr.download_request_download_id = d.download_id AND d.download_active = 1
		WHERE {$where}
		GROUP by dr.download_request_userid
		";
		if($sql->db_Select_gen($qry))
		{
			$row=$sql->db_Fetch();
			if($row['count'] >= $limits['gen_intdata'])
			{
				// Exceeded download count limit
				@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_download.php");
				@include_once(e_LANGUAGEDIR."English/lan_download.php");
				require(HEADERF);
				$ns->tablerender(LAN_dl_61, LAN_dl_62);
				require(FOOTERF);
				exit;
			}
		}
	}
		

	// Check download bandwidth limits

	$qry = "
	SELECT gen_user_id, gen_ip, (gen_user_id/gen_ip) as bw_perday 
	FROM #generic 
	WHERE gen_type='download_limit' AND gen_datestamp IN (".USERCLASS_LIST.") AND (gen_user_id > 0 AND gen_ip > 0)
	ORDER BY bw_perday DESC
	";
	if($sql->db_Select_gen($qry))
	{
		$limit = $sql->db_Fetch();
		$cutoff = time() - (86400*$limit['gen_ip']);
		if(USER)
		{
			$where = "dr.download_request_datestamp > $cutoff AND dr.download_request_userid = ".USERID;
		}
		else
		{
			$ip = getip();
			$where = "dr.download_request_datestamp > $cutoff AND dr.download_request_ip = '$ip'";
		}
		$qry = "
		SELECT SUM(d.download_filesize) as total_bw
		FROM #download_requests as dr
		LEFT JOIN #download as d ON dr.download_request_download_id = d.download_id AND d.download_active = 1
		WHERE {$where}
		GROUP by dr.download_request_userid
		";
		if($sql->db_Select_gen($qry))
		{
			$row=$sql->db_Fetch();
			if($row['total_bw']/1024 > $limit['gen_user_id'])
			{
				//Exceed bandwith limit
				@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_download.php");
				@include_once(e_LANGUAGEDIR."English/lan_download.php");
				require(HEADERF);
				$ns->tablerender(LAN_dl_61, LAN_dl_62);
				require(FOOTERF);
				exit;
			}
		}
	}
}

?>