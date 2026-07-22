<?php

use e107\Database\SqlFragment;

if (!defined('e107_INIT'))
{
	require_once(__DIR__.'/../../class2.php');
}

e107::lan('download','download');

class download_request
{

	static function request()
	{

		$log = e107::getLog();
		$id = false;

		$sql = e107::getDb();
		$tp = e107::getParser();
		$pref = e107::pref();

		if(!is_numeric(e_QUERY) && empty($_GET['id']))
		{
			$row = $sql->createQueryBuilder()
				->select('download_id')->from('download')
				->where('download_url', $tp->toDB(e_QUERY))
				->fetchRow();
			if($row)
			{
				$type = 'file';
				$id = $row['download_id'];
			}
			elseif(file_exists(e_DOWNLOAD . e_QUERY) && !is_dir(e_DOWNLOAD . e_QUERY))        // 1 - should we allow this?
			{
				e107::getFile()->send(e_DOWNLOAD . e_QUERY);
				exit();
			}
		}


		if(strpos(e_QUERY, "mirror") !== false)
		{    // Download from mirror
			list($action, $download_id, $mirror_id) = explode(".", e_QUERY);
			$download_id = intval($download_id);
			$mirror_id = intval($mirror_id);
			$qb = $sql->createQueryBuilder();
			$row = $qb
				->select('d.*', 'dc.download_category_class')
				->from('download', 'd')
				->leftJoin('download_category', 'dc', $qb->expr()->compareColumns('dc.download_category_id', 'd.download_category'))
				->where('d.download_id', (int) $download_id)
				->fetchRow();
			if($row)
			{
				extract($row);
				if(check_class($row['download_category_class']) && check_class($row['download_class']))
				{
					if(!empty($pref['download_limits']) && $row['download_active'] == 1)
					{
						self::check_download_limits();
					}
					$mirrorList = explode(chr(1), $row['download_mirror']);
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
								$requests++;
							}
							$mstr .= $mid . "," . $address . "," . $requests . chr(1);
						}
					}
					$sql->createQueryBuilder()->update('download')
						->increment('download_requested')
						->set('download_mirror', $mstr)
						->where('download_id', (int) $download_id)->execute();
					$sql->createQueryBuilder()->update('download_mirror')
						->increment('mirror_count')
						->where('mirror_id', (int) $mirror_id)->execute();

					if(!empty($gaddress))
					{
						header("Location: " . self::decorate_download_location($gaddress));
					}
					exit();
				}

				$goUrl = e107::url('download', 'index', null, array('query' => array('action' => 'error', 'id' => 1))); // ."?action=error&id=1";
				e107::redirect($goUrl);
				//header("Location: ".e_BASE."download.php?error.{$download_id}.1");
				exit;
			}
		}

		$tmp = explode(".", e_QUERY);
		if(empty($tmp[1]) || strpos(e_QUERY, "pub_") !== false)
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


		if(preg_match("#.*\.[a-z,A-Z]{3,4}#", e_QUERY))
		{
			if(strpos(e_QUERY, "pub_") !== false)
			{
				$bid = str_replace("pub_", "", e_QUERY);
				if(file_exists(e_UPLOAD . $bid))
				{
					e107::getFile()->send(e_UPLOAD . $bid);
					exit();
				}
				$log->addError("Line" . __LINE__ . ": Couldn't find " . e_UPLOAD . $bid . "");
			}
			if(file_exists(e_DOWNLOAD . e_QUERY))
			{
				e107::getFile()->send(e_DOWNLOAD . e_QUERY);
				exit();
			}
			$log->addError("Line" . __LINE__ . ": Couldn't find " . e_DOWNLOAD . e_QUERY);
			$log->toFile('download_requests', 'Download Requests', true); // Create a log file and add the log messages
			require_once(HEADERF);
			e107::getRender()->tablerender(LAN_ERROR, "<div style='text-align:center'>" . LAN_FILE_NOT_FOUND . "\n<br /><br />\n<a href='javascript:history.back(1)'>" . LAN_BACK . "</a></div>");
			require_once(FOOTERF);
			exit();
		}

		if($type == "file")
		{
			$qb = $sql->createQueryBuilder();
			$row = $qb
				->select('d.*', 'dc.download_category_class')
				->from('download', 'd')
				->leftJoin('download_category', 'dc', $qb->expr()->compareColumns('dc.download_category_id', 'd.download_category'))
				->where('d.download_id', (int) $id)
				->fetchRow();
			if($row)
			{
				$row['download_url'] = $tp->replaceConstants($row['download_url']); // must be relative file-path.

				if(check_class($row['download_category_class']) && check_class($row['download_class']))
				{
					if($row['download_active'] == 0) // Inactive download - don't allow
					{
						require_once(HEADERF);
						$search = array("[", "]");
						$replace = array("<a href='" . e_HTTP . "download.php'>", "</a>");

						e107::getRender()->tablerender(LAN_ERROR, "<div class='alert alert-warning' style='text-align:center'>" . str_replace($search, $replace, LAN_dl_78) . '</div>');
						require_once(FOOTERF);
						exit();
					}

					if($pref['download_limits'] && $row['download_active'] == 1)
					{
						self::check_download_limits();
					}
					extract($row);
					if($row['download_mirror'])
					{
						$array = explode(chr(1), $row['download_mirror']);
						$c = (count($array) - 1);
						for($i = 1; $i < $c; $i++)
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
									$requests++;
								}
								$mstr .= $mid . "," . $address . "," . $requests . chr(1);
							}
						}
						$sql->createQueryBuilder()->update('download')
							->increment('download_requested')
							->set('download_mirror', $mstr)
							->where('download_id', intval($download_id))->execute();
						$sql->createQueryBuilder()->update('download_mirror')
							->increment('mirror_count')
							->where('mirror_id', intval($mirror_id))->execute();
						if(!empty($gaddress))
						{
							header("Location: " . self::decorate_download_location($gaddress));
						}
						exit();
					}

					// increment download count
					$sql->createQueryBuilder()->update('download')
						->increment('download_requested')
						->where('download_id', (int) $id)->execute();
					$user_id = USER ? USERID : 0;
					$ip = e107::getIPHandler()->getIP(false);
					//add request info to db
					$sql->createQueryBuilder()->insert('download_requests')
						->values(array(
							'download_request_userid'      => $user_id,
							'download_request_ip'          => $ip,
							'download_request_download_id' => $id,
							'download_request_datestamp'   => time(),
						))->execute();
					//	if (preg_match("/Binary\s(.*?)\/.*/", $download_url, $result))
					//	{
					//		$bid = $result[1];
					///		$result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id = '{$bid}'");
					//		$binary_data = @mysql_result($result, 0, "binary_data");
					//		$binary_filetype = @mysql_result($result, 0, "binary_filetype");
					//		$binary_name = @mysql_result($result, 0, "binary_name");
					//		header("Content-type: {$binary_filetype}");
					//		header("Content-length: {$download_filesize}");
					//		header("Content-Disposition: attachment; filename={$binary_name}");
					//		header("Content-Description: PHP Generated Data");
					//		echo $binary_data;
					//		exit();
					//	}
					if(strpos($row['download_url'], "http://") !== false || strpos($row['download_url'], "ftp://") !== false || strpos($row['download_url'], "https://") !== false)
					{
						$download_url = e107::getParser()->parseTemplate($row['download_url']); // support for shortcode-driven dynamic URLS.
						e107::redirect(self::decorate_download_location($download_url));
						// header("Location: {$download_url}");
						exit();
					}
					else
					{
						if(file_exists(e_DOWNLOAD . $row['download_url']))
						{
							e107::getFile()->send(e_DOWNLOAD . $row['download_url']);
							exit();
						}
						elseif(file_exists($row['download_url']))
						{
							e107::getFile()->send($row['download_url']);
							exit();
						}
						elseif(file_exists(e_UPLOAD . $row['download_url']))
						{
							e107::getFile()->send(e_UPLOAD . $row['download_url']);
							exit();
						}
						$log->addError("Couldn't find " . e_DOWNLOAD . $row['download_url'] . " or " . $row['download_url'] . " or " . e_UPLOAD . $row['download_url']);
						$log->toFile('download_requests', 'Download Requests', true); // Create a log file and add the log messages
					}
				}
				else
				{    // Download Access Denied.
					if((!strpos($pref['download_denied'], ".php") &&
						!strpos($pref['download_denied'], ".htm") &&
						!strpos($pref['download_denied'], ".html") &&
						!strpos($pref['download_denied'], ".shtml") ||
						(strpos($pref['download_denied'], "signup.php") && USER == true)
					))
					{
						//	$goUrl = e107::getUrl()->create('download/index')."?action=error&id=1";
						$goUrl = e107::url('download', 'index', null, array('query' => array('action' => 'error', 'id' => 1)));
						e107::redirect($goUrl);
						return;
					}
					else
					{
						e107::redirect(trim($pref['download_denied']));
						return;
					}
				}
			}
			//else if(strstr(e_QUERY, "pub_"))
	//	{
			/* check to see if public upload and not in download table ... */
			/*$bid = str_replace("pub_", "", e_QUERY);
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
			}*/
	//	}


			$log->addError("Line" . __LINE__ . ": Couldn't find " . e_DOWNLOAD . e_QUERY);
			$log->toFile('download_requests', 'Download Requests', true); // Create a log file and add the log messages
			require_once(HEADERF);
			e107::getRender()->tablerender(LAN_ERROR, "<div style='text-align:center'>" . LAN_FILE_NOT_FOUND . "<br /><br /><a href='javascript:history.back(1)'>" . LAN_BACK . "</a></div>");
			require_once(FOOTERF);
			return;
		}

		if(!empty($table) && in_array($table, array('download', 'upload'), true)) // validate dynamic table name fail-closed
		{
			$row = $sql->createQueryBuilder()
				->select('*')->from($table)
				->where($table . '_id', $id)
				->fetchRow();
			if($row)
			{
				extract($row);
				$image = ($table == "upload" ? $row['upload_ss'] : $row['download_image']);
			}
		}
	//if (preg_match("/Binary\s(.*?)\/.*/", $image, $result))
	//{
		/*	$bid = $result[1];
			$result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id = '{$bid}'");
			$binary_data = @mysql_result($result, 0, "binary_data");
			$binary_filetype = @mysql_result($result, 0, "binary_filetype");
			$binary_name = @mysql_result($result, 0, "binary_name");
			header("Content-type: {$binary_filetype}");
			header("Content-Disposition: inline; filename={$binary_name}");
			echo $binary_data;
			exit();*/

	//}


	// $image = ($table == "upload" ? $upload_ss : $download_image);

		if(strpos($image, "http") !== false)
		{
			e107::redirect($image);
			exit();
		}
		else
		{
			if($table == "download")
			{
				require_once(HEADERF);
				$imagecaption = ''; // TODO ?name or text Screenshot

				if(file_exists(e_FILE . "download/{$image}"))
				{
					$disp = "<div style='text-align:center'><img class='img-responsive img-fluid' src='" . e_FILE_ABS . "download/{$image}' alt='' /></div>";
				}
				elseif(file_exists(e_FILE . "downloadimages/{$image}"))
				{
					$disp = "<div style='text-align:center'><img class='img-responsive img-fluid' src='" . e_FILE_ABS . "downloadimages/{$image}' alt='' /></div>";
				}
				else
				{
					$image = $tp->replaceConstants($image, 'abs');
					$disp = "<div style='text-align:center'><img class='img-responsive img-fluid' src='" . $image . "' alt='' /></div>";
				}

				$disp .= "<br /><div style='text-align:center'><a href='javascript:history.back(1)'>" . LAN_BACK . "</a></div>";

				e107::getRender()->tablerender($imagecaption, $disp);

				require_once(FOOTERF);
			}
			else
			{
				if(is_file(e_UPLOAD . $image))
				{
					echo "<img src='" . e_UPLOAD . $image . "' alt='' />";
				}
				elseif(is_file(e_FILE . "downloadimages/{$image}"))
				{
					echo "<img src='" . e_FILE_ABS . "downloadimages/{$image}' alt='' />";
				}
				else
				{
					require_once(HEADERF);
					e107::getRender()->tablerender(LAN_ERROR, "<div style='text-align:center'>" . LAN_FILE_NOT_FOUND . "<br /><br /><a href='javascript:history.back(1)'>" . LAN_BACK . "</a></div>");
					require_once(FOOTERF);
				}

				return;
			}
		}
	}


	private static function check_download_limits()
	{
		global $HEADER;
		$sql = e107::getDb();
		$pref = e107::getPref();

		$classList = explode(',', USERCLASS_LIST);

		// Check download count limits
		$limits = $sql->createQueryBuilder()
			->select('gen_intdata', 'gen_chardata')->addSelect(SqlFragment::raw('(gen_intdata/gen_chardata) AS count_perday'))
			->from('generic')
			->where('gen_type', 'download_limit')
			->whereIn('gen_datestamp', $classList)
			->where('gen_chardata', '>=', 0)
			->where('gen_intdata', '>=', 0)
			->orderBy('count_perday', 'DESC')
			->fetchRow();
		if($limits)
		{
			$cutoff = time() - (86400 * $limits['gen_chardata']);
			$row = self::aggregate_download_requests('COUNT', 'd.download_id', 'count', $cutoff);
			if($row && $row['count'] >= $limits['gen_intdata'])
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
		// Check download bandwidth limits
		$limit = $sql->createQueryBuilder()
			->select('gen_user_id', 'gen_ip')->addSelect(SqlFragment::raw('(gen_user_id/gen_ip) AS bw_perday'))
			->from('generic')
			->where('gen_type', 'download_limit')
			->whereIn('gen_datestamp', $classList)
			->where('gen_user_id', '>=', 0)
			->where('gen_ip', '>=', 0)
			->orderBy('bw_perday', 'DESC')
			->fetchRow();
		if($limit)
		{
			$cutoff = time() - (86400*$limit['gen_ip']);
			$row = self::aggregate_download_requests('SUM', 'd.download_filesize', 'total_bw', $cutoff);
			if($row && $row['total_bw'] / 1024 > $limit['gen_user_id'])
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

	/**
	 * Aggregate a user's recent download requests, scoped to the current user
	 * (or their IP when anonymous) since a cutoff timestamp.
	 *
	 * @param string $function aggregate function, e.g. 'COUNT' or 'SUM';
	 *                         see {@see \e107\Database\QueryBuilder::selectAggregate()}.
	 * @param string $column aggregated column, e.g. 'd.download_id'.
	 * @param string $alias result column alias.
	 * @param int $cutoff lower-bound request timestamp.
	 * @return array|false fetched row, or false when none.
	 */
	private static function aggregate_download_requests($function, $column, $alias, $cutoff)
	{
		$qb = e107::getDb()->createQueryBuilder();
		$qb->selectAggregate($function, $column, $alias)
			->from('download_requests', 'dr')
			->leftJoin('download', 'd', $qb->expr()->allOf($qb->expr()->compareColumns('dr.download_request_download_id', 'd.download_id'), $qb->expr()->eq('d.download_active', 1)))
			->where('dr.download_request_datestamp', '>', $cutoff)
			->groupBy('dr.download_request_userid');

		if(USER)
		{
			$qb->where('dr.download_request_userid', USERID);
		}
		else
		{
			$qb->where('dr.download_request_ip', e107::getIPHandler()->getIP());
		}

		return $qb->fetchRow();
	}

	private static function decorate_download_location($url)
	{
		$pref = e107::getPref();

		if (varset($pref['download_security_mode']) !== 'nginx-secure_link_md5')
		{
			return $url;
		}

		require_once(__DIR__."/handlers/NginxSecureLinkMd5Decorator.php");
		$decorator = new NginxSecureLinkMd5Decorator($url, $pref);
		return $decorator->decorate();
	}

}


download_request::request();