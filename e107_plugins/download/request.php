<?php
if (!defined('e107_INIT'))
{
	require_once("../../class2.php");
}


e107::lan('download','download');


$log = e107::getAdminLog(); 
$id = FALSE;



if (!is_numeric(e_QUERY) && empty($_GET['id'])) 
{
	if ($sql->select('download', 'download_id', "download_url='".$tp->toDB(e_QUERY)."'")) 
	{
		$row = $sql->fetch();
		$type = 'file';
		$id = $row['download_id'];
	} 
	elseif((strpos(e_QUERY, "http://") === 0) || (strpos(e_QUERY, "ftp://") === 0) || (strpos(e_QUERY, "https://") === 0)) 
	{
		header("location: ".e_QUERY);
		exit();
	} 
	elseif(file_exists(e_DOWNLOAD.e_QUERY)) 		// 1 - should we allow this?
	{
		e107::getFile()->send(e_DOWNLOAD.e_QUERY);
		exit();
	}
}




if(strstr(e_QUERY, "mirror")) 
{	// Download from mirror
	list($action, $download_id, $mirror_id) = explode(".", e_QUERY);
	$download_id = intval($download_id);
	$mirror_id = intval($mirror_id);
	$qry = "SELECT d.*, dc.download_category_class FROM #download as d LEFT JOIN #download_category AS dc ON dc.download_category_id = d.download_category WHERE d.download_id = {$download_id}";
	if ($sql->gen($qry)) 
	{
		$row = $sql->fetch();
		extract($row);
		if (check_class($download_category_class) && check_class($download_class)) 
		{
			if($pref['download_limits'] && $download_active == 1) 
			{
				check_download_limits();
			}
			$mirrorList = explode(chr(1), $download_mirror);
			$mstr = "";
			foreach($mirrorList as $mirror) 
			{
				if($mirror) 
				{
					$tmp = explode(",", $mirror);
					$mid = intval($tmp[0]);
					$address = $tmp[1];
					$requests = $tmp[2];
					if($tmp[0] == $mirror_id) 
					{
						$gaddress = trim($address);
						$requests ++;
					}
					$mstr .= $mid.",".$address.",".$requests.chr(1);
				}
			}
			$sql->update("download", "download_requested = download_requested + 1, download_mirror = '{$mstr}' WHERE download_id = '".intval($download_id)."'");
			$sql->update("download_mirror", "mirror_count = mirror_count + 1 WHERE mirror_id = '".intval($mirror_id)."'");
			header("Location: ".decorate_download_location($gaddress));
			exit();
		}

		$goUrl = e107::url('download', 'index', null, array('query'=>array('action'=>'error','id'=>1))); // ."?action=error&id=1";
		e107::redirect($goUrl);
		//header("Location: ".e_BASE."download.php?error.{$download_id}.1");
		exit;
	}
}

$tmp = explode(".", e_QUERY);
if (!$tmp[1] || strstr(e_QUERY, "pub_")) 
{
	$id = intval($tmp[0]);
	$type = "file";
} 
else 
{
	$table = preg_replace("#\W#", "", $tp->toDB($tmp[0], true));
	$id = intval($tmp[1]);
	$type = "image";
}

if(vartrue($_GET['id'])) // SEF URL 
{
	$id = intval($_GET['id']);	
	$type = 'file';
}



if (preg_match("#.*\.[a-z,A-Z]{3,4}#", e_QUERY)) 
{
	if(strstr(e_QUERY, "pub_"))
	{
		$bid = str_replace("pub_", "", e_QUERY);
		if (file_exists(e_UPLOAD.$bid))
		{
			e107::getFile()->send(e_UPLOAD.$bid);
			exit();
		}
		$log->addError("Line".__LINE__.": Couldn't find ".e_UPLOAD.$bid."");
	}
	if (file_exists(e_DOWNLOAD.e_QUERY)) 
	{
		e107::getFile()->send(e_DOWNLOAD.e_QUERY);
		exit();
	}
	$log->addError("Line".__LINE__.": Couldn't find ".e_DOWNLOAD.e_QUERY);
	$log->toFile('download_requests','Download Requests', true); // Create a log file and add the log messages
	require_once(HEADERF);
	$ns->tablerender(LAN_ERROR, "<div style='text-align:center'>".LAN_FILE_NOT_FOUND."\n<br /><br />\n<a href='javascript:history.back(1)'>".LAN_BACK."</a></div>");
	require_once(FOOTERF);
	exit();
}

if ($type == "file")
{
	$qry = "SELECT d.*, dc.download_category_class FROM #download as d LEFT JOIN #download_category AS dc ON dc.download_category_id = d.download_category WHERE d.download_id = {$id}";
	if ($sql->gen($qry)) 
	{
		$row = $sql->fetch();
		
		$row['download_url'] = $tp->replaceConstants($row['download_url']); // must be relative file-path.

		if (check_class($row['download_category_class']) && check_class($row['download_class'])) 
		{
			if ($row['download_active'] == 0) // Inactive download - don't allow
			{
				require_once(HEADERF);
				$search = array("[","]");
				$replace = array("<a href='".e_HTTP."download.php'>", "</a>");

				$ns->tablerender(LAN_ERROR, "<div class='alert alert-warning' style='text-align:center'>".str_replace($search, $replace, LAN_dl_78).'</div>');
				require_once(FOOTERF);
				exit();
			}

			if($pref['download_limits'] && $row['download_active'] == 1) 
			{
				check_download_limits();
			}
			extract($row);
			if($download_mirror) 
			{
				$array = explode(chr(1), $download_mirror);
				$c = (count($array)-1);
				for ($i=1; $i < $c; $i++) 
				{
					$d = mt_rand(0, $i);
					$tmp = $array[$i];
					$array[$i] = $array[$d];
					$array[$d] = $tmp;
				}
				$tmp = explode(",", $array[0]);
				$mirror_id = $tmp[0];
				$mstr = "";
				foreach($array as $mirror) 
				{
					if($mirror) 
					{
						$tmp = explode(",", $mirror);
						$mid = $tmp[0];
						$address = $tmp[1];
						$requests = $tmp[2];
						if($tmp[0] == $mirror_id) 
						{
							$gaddress = trim($address);
							$requests ++;
						}
					  $mstr .= $mid.",".$address.",".$requests.chr(1);
					}
				}
				$sql->update("download", "download_requested = download_requested + 1, download_mirror = '{$mstr}' WHERE download_id = '".intval($download_id)."'");
				$sql->update("download_mirror", "mirror_count = mirror_count + 1 WHERE mirror_id = '".intval($mirror_id)."'");

				header("Location: ".decorate_download_location($gaddress));
				exit();
			}

			// increment download count
			$sql->update("download", "download_requested = download_requested + 1 WHERE download_id = '{$id}'");
			$user_id = USER ? USERID : 0;
			$ip = e107::getIPHandler()->getIP(FALSE);
			$request_data = "'0', '{$user_id}', '{$ip}', '{$id}', '".time()."'";
			//add request info to db
			$sql->db_Insert("download_requests", $request_data, FALSE);
			if (preg_match("/Binary\s(.*?)\/.*/", $download_url, $result)) 
			{
				$bid = $result[1];
				$result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id = '{$bid}'");
				$binary_data = @mysql_result($result, 0, "binary_data");
				$binary_filetype = @mysql_result($result, 0, "binary_filetype");
				$binary_name = @mysql_result($result, 0, "binary_name");
				header("Content-type: {$binary_filetype}");
				header("Content-length: {$download_filesize}");
				header("Content-Disposition: attachment; filename={$binary_name}");
				header("Content-Description: PHP Generated Data");
				echo $binary_data;
				exit();
			}
			if (strstr($download_url, "http://") || strstr($download_url, "ftp://") || strstr($download_url, "https://"))
			{
				$download_url = e107::getParser()->parseTemplate($download_url,true); // support for shortcode-driven dynamic URLS.
				e107::redirect(decorate_download_location($download_url));
				// header("Location: {$download_url}");
				exit();
			} 
			else 
			{
				if (file_exists(e_DOWNLOAD.$download_url)) 
				{
					e107::getFile()->send(e_DOWNLOAD.$download_url);
					exit();
				} 
				elseif(file_exists($download_url)) 
				{
					e107::getFile()->send($download_url);
					exit();
				}
				elseif(file_exists(e_UPLOAD.$download_url)) 
				{
					e107::getFile()->send(e_UPLOAD.$download_url);
					exit();
				}
				$log->addError("Couldn't find ".e_DOWNLOAD.$download_url." or ".$download_url." or ".e_UPLOAD.$download_ur);
				$log->toFile('download_requests','Download Requests', true); // Create a log file and add the log messages
			}
		} 
		else 
		{	// Download Access Denied.
			if((!strpos($pref['download_denied'],".php") &&
				!strpos($pref['download_denied'],".htm") &&
				!strpos($pref['download_denied'],".html") &&
				!strpos($pref['download_denied'],".shtml") ||
				(strpos($pref['download_denied'],"signup.php") && USER == TRUE)
				))
			{
			//	$goUrl = e107::getUrl()->create('download/index')."?action=error&id=1";
				$goUrl = e107::url('download', 'index', null, array('query'=>array('action'=>'error','id'=>1)));
				e107::redirect($goUrl);
				exit();
			}
			else
			{
				e107::redirect(trim($pref['download_denied']));
				exit();
			}
		}
	}
	else if(strstr(e_QUERY, "pub_"))
	{
		/* check to see if public upload and not in download table ... */
		$bid = str_replace("pub_", "", e_QUERY);
		if($result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id = '$bid' "))
		{
			$binary_data = @mysql_result($result, 0, "binary_data");
			$binary_filetype = @mysql_result($result, 0, "binary_filetype");
			$binary_name = @mysql_result($result, 0, "binary_name");
			header("Content-type: {$binary_filetype}");
			header("Content-length: {$download_filesize}");
			header("Content-Disposition: attachment; filename={$binary_name}");
			header("Content-Description: PHP Generated Data");
			echo $binary_data;
			exit();
		}
	}
	
	
	$log->addError("Line".__LINE__.": Couldn't find ".e_DOWNLOAD.e_QUERY);
	$log->toFile('download_requests','Download Requests', true); // Create a log file and add the log messages
	require_once(HEADERF);
	$ns -> tablerender(LAN_ERROR, "<div style='text-align:center'>".LAN_FILE_NOT_FOUND."<br /><br /><a href='javascript:history.back(1)'>".LAN_BACK."</a></div>");
	require_once(FOOTERF);
	exit();
}

$sql->select($table, "*", "{$table}_id = '{$id}'");
$row = $sql->fetch();
extract($row);
$image = ($table == "upload" ? $upload_ss : $download_image);
if (preg_match("/Binary\s(.*?)\/.*/", $image, $result)) 
{
	$bid = $result[1];
	$result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id = '{$bid}'");
	$binary_data = @mysql_result($result, 0, "binary_data");
	$binary_filetype = @mysql_result($result, 0, "binary_filetype");
	$binary_name = @mysql_result($result, 0, "binary_name");
	header("Content-type: {$binary_filetype}");
	header("Content-Disposition: inline; filename={$binary_name}");
	echo $binary_data;
	exit();
}


$image = ($table == "upload" ? $upload_ss : $download_image);

if (strpos($image, "http") !== FALSE) 
{
	e107::redirect($image);
	exit();
} 
else 
{
	if ($table == "download") 
	{
		require_once(HEADERF);
         $imagecaption = ''; // TODO ?name or text Screenshot

		if (file_exists(e_FILE."download/{$image}")) 
		{
			$disp = "<div style='text-align:center'><img class='img-responsive img-fluid' src='".e_FILE_ABS."download/{$image}' alt='' /></div>";
		}
		else if(file_exists(e_FILE."downloadimages/{$image}")) 
		{
			$disp = "<div style='text-align:center'><img class='img-responsive img-fluid' src='".e_FILE_ABS."downloadimages/{$image}' alt='' /></div>";
		} 
		else 
		{
             $image = $tp->replaceConstants($image,'abs');
			$disp = "<div style='text-align:center'><img class='img-responsive img-fluid' src='".$image."' alt='' /></div>";
		}

		$disp .= "<br /><div style='text-align:center'><a href='javascript:history.back(1)'>".LAN_BACK."</a></div>";

		$ns->tablerender($imagecaption, $disp);

		require_once(FOOTERF);
	} else 
	{
		if (is_file(e_UPLOAD.$image)) 
		{
			echo "<img src='".e_UPLOAD.$image."' alt='' />";
		} 
		elseif(is_file(e_FILE."downloadimages/{$image}")) 
		{
			echo "<img src='".e_FILE_ABS."downloadimages/{$image}' alt='' />";
		} 
		else 
		{
			require_once(HEADERF);
			$ns->tablerender(LAN_ERROR, "<div style='text-align:center'>".LAN_FILE_NOT_FOUND."<br /><br /><a href='javascript:history.back(1)'>".LAN_BACK."</a></div>");
			require_once(FOOTERF);
			exit;
		}
		exit();
	}
}



function check_download_limits() 
{
	global $pref, $sql, $ns, $HEADER, $e107, $tp;
	// Check download count limits
	$qry = "SELECT gen_intdata, gen_chardata, (gen_intdata/gen_chardata) as count_perday FROM #generic WHERE gen_type = 'download_limit' AND gen_datestamp IN (".USERCLASS_LIST.") AND (gen_chardata >= 0 AND gen_intdata >= 0) ORDER BY count_perday DESC";
	if($sql->gen($qry)) 
	{
		$limits = $sql->fetch();
		$cutoff = time() - (86400 * $limits['gen_chardata']);
		if(USER) 
		{
			$where = "dr.download_request_datestamp > {$cutoff} AND dr.download_request_userid = ".USERID;
		} 
		else 
		{
			$ip = e107::getIPHandler()->getIP(FALSE);
			$where = "dr.download_request_datestamp > {$cutoff} AND dr.download_request_ip = '{$ip}'";
		}
		$qry = "SELECT COUNT(d.download_id) as count FROM #download_requests as dr LEFT JOIN #download as d ON dr.download_request_download_id = d.download_id AND d.download_active = 1 WHERE {$where} GROUP by dr.download_request_userid";
		if($sql->gen($qry)) 
		{
			$row = $sql->fetch();
			if($row['count'] >= $limits['gen_intdata']) 
			{
				// Exceeded download count limit
			//	$goUrl = e107::getUrl()->create('download/index')."?action=error&id=2";
				$goUrl = e107::url('download', 'index', null, array('query'=>array('action'=>'error','id'=>2)));
				e107::redirect($goUrl);
			 // 	e107::redirect(e_BASE."download.php?error.{$cutoff}.2");
				/* require_once(HEADERF);
				$ns->tablerender(LAN_ERROR, LAN_dl_62);
				require(FOOTERF);  */
				exit();
			}
		}
	}
	// Check download bandwidth limits
	$qry = "SELECT gen_user_id, gen_ip, (gen_user_id/gen_ip) as bw_perday FROM #generic WHERE gen_type='download_limit' AND gen_datestamp IN (".USERCLASS_LIST.") AND (gen_user_id >= 0 AND gen_ip >= 0) ORDER BY bw_perday DESC";
	if($sql->gen($qry)) 
	{
		$limit = $sql->fetch();
		$cutoff = time() - (86400*$limit['gen_ip']);
		if(USER) 
		{
			$where = "dr.download_request_datestamp > {$cutoff} AND dr.download_request_userid = ".USERID;
		} 
		else 
		{
			$ip = e107::getIPHandler()->getIP(FALSE);
			$where = "dr.download_request_datestamp > {$cutoff} AND dr.download_request_ip = '{$ip}'";
		}
		$qry = "SELECT SUM(d.download_filesize) as total_bw FROM #download_requests as dr LEFT JOIN #download as d ON dr.download_request_download_id = d.download_id AND d.download_active = 1 WHERE {$where} GROUP by dr.download_request_userid";
		if($sql->gen($qry)) 
		{
			$row = $sql->fetch();
			
			if($row['total_bw'] / 1024 > $limit['gen_user_id']) 
			{	//Exceed bandwith limit
			//	$goUrl = e107::getUrl()->create('download/index')."?action=error&id=2";
				$goUrl = e107::url('download', 'index', null, array('query'=>array('action'=>'error','id'=>2)));
				 e107::redirect($goUrl);
			 // e107::redirect(e_BASE."download.php?error.{$cutoff}.2");
				/* require(HEADERF);
				$ns->tablerender(LAN_ERROR, LAN_dl_62);
				require(FOOTERF); */
				exit();
			}
		}
	}
}

function decorate_download_location($url)
{
	$pref = e107::getPref();
	if ($pref['download_security_mode'] !== 'nginx-secure_link_md5')
		return $url;
	require_once(__DIR__."/handlers/NginxSecureLinkMd5Decorator.php");
	$decorator = new NginxSecureLinkMd5Decorator($url, $pref);
	return $decorator->decorate();
}