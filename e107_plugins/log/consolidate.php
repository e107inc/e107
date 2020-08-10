<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)

 */

/* first thing to do is check if the log file is out of date ... */



if (!defined('e107_INIT')){ exit; } 



// Begin v2.x cleanup.
class logConsolidate
{

		protected $pathtologs;
		protected $date;
		protected $yesterday;
		protected $date2;
		protected $date3;

		protected $pfileprev;
		protected $pfile;
		protected $ifileprev;
		protected $ifile;


		function __construct()
		{
			$this->pathtologs 	    = e_LOG;
			$this->date 			= date("z.Y", time());
			$this->yesterday 		= date("z.Y",(time() - 86400));		// This makes sure year wraps round OK
			$this->date2 			= date("Y-m-j", (time() -86400));	// Yesterday's date for the database summary
			$this->date3 			= date("Y-m", (time() -86400));		// Current month's date for monthly summary (we're working with yesterday's data)

			$this->pfileprev 		= "logp_".$this->yesterday.".php";	// Yesterday's log file
			$this->pfile 			= "logp_".$this->date.".php";		// Today's log file
			$this->ifileprev 		= "logi_".$this->yesterday.".php";
			$this->ifile 			= "logi_".$this->date.".php";
		}


		function run()
		{

			$sql = e107::getDb();
			$pref = e107::pref('core');

			$pageInfo       = array();
			$domainInfo     = array();
			$screenInfo     = array();
			$browserInfo    = array();
			$osInfo         = array();
			$refInfo        = array();
			$searchInfo     = array();

			$monthlyInfo    = array();
			$mon_statBrowser = array();
			$mon_statOs     = array();
			$mon_statScreen = array();
			$mon_statDomain = array();
			$mon_statQuery  = array();
			$statBrowser    = array();
			$statOs         = array();
			$statScreen     = array();
			$statDomain     = array();
			$statQuery      = array();
			$statReferer    = array();

			$statTotal      = 0;
			$statUnique     = 0;
			$siteTotal      = 0;
			$siteUnique     = 0;

			$MonthlyExistsFlag  = false;

			if(file_exists($this->pathtologs.$this->pfile))  /* log file is up to date, no consolidation required */
			{
				return false;
			}
			else if(!file_exists($this->pathtologs.$this->pfileprev))  // See if any older log files
			{
				if (($retvalue = $this->check_for_old_files($this->pathtologs)) === false) /* no logfile found at all - create - this will only ever happen once ... */
				{
					$this->createLog();
					return false;
				}

				list($this->pfileprev,$this->ifileprev,$this->date2,$tstamp) = explode('|',$retvalue);  // ... if we've got files
			}

			unset($tstamp);

			// List of the non-page-based info which is gathered - historically only 'all-time' stats, now we support monthly as well
			$stats_list = array('statBrowser','statOs','statScreen','statDomain','statReferer','statQuery');

			$qry = "`log_id` IN ('statTotal','statUnique'";
			foreach ($stats_list as $s)
			{
				$qry .= ",'{$s}'";									// Always read the all-time stats
				if ($pref[$s] == 2) $qry .= ",'{$s}:{$this->date3}'";		// Look for monthlys as well as cumulative
			}
			$qry .= ")";

			/* log file is out of date - consolidation required */

			/* get existing stats ... */
			//if($sql->select("logstats", "*", "log_id='statBrowser' OR log_id='statOs' OR log_id='statScreen' OR log_id='statDomain' OR log_id='statTotal' OR log_id='statUnique' OR log_id='statReferer' OR log_id='statQuery'"))
			if($sql->select("logstats", "*", $qry))
			{	// That's read in all the stats we need to modify
				while($row = $sql->fetch())
				{
					if($row['log_id'] == "statUnique")
					{
						$statUnique = $row['log_data'];
					}
					elseif ($row['log_id'] == "statTotal")
					{
						$statTotal = $row['log_data'];
					}
					elseif (($pos = strpos($row['log_id'],':')) === false)
					{  // Its all-time stats
						$$row['log_id'] = unserialize($row['log_data']);	// $row['log_id'] is the stats type - save in a variable
					}
					else
					{  // Its monthly stats
						$row['log_id'] = 'mon_'.substr($row['log_id'],0,$pos);	// Create a generic variable for each monthly stats
						$$row['log_id'] = unserialize($row['log_data']);	// $row['log_id'] is the stats type - save in a variable
					}
				}
			}
			else
			{
				// this must be the first time a consolidation has happened - this will only ever happen once ...
				$sql->insert("logstats", "0, 'statBrowser', ''");
				$sql->insert("logstats", "0, 'statOs', ''");
				$sql->insert("logstats", "0, 'statScreen', ''");
				$sql->insert("logstats", "0, 'statDomain', ''");
				$sql->insert("logstats", "0, 'statReferer', ''");
				$sql->insert("logstats", "0, 'statQuery', ''");
				$sql->insert("logstats", "0, 'statTotal', '0'");
				$sql->insert("logstats", "0, 'statUnique', '0'");

				$statBrowser 	=array();
				$statOs 		=array();
				$statScreen 	=array();
				$statDomain 	=array();
				$statReferer 	=array();
				$statQuery 		=array();
			}


			foreach ($stats_list as $s)
			{
				$varname = 'mon_'.$s;
				if (!isset($$varname)) $$varname = array();		// Create monthly arrays if they don't exist
			}

			if(file_exists($this->pathtologs.$this->pfileprev))
			{
				require($this->pathtologs.$this->pfileprev);		// Yesterday's page accesses - $pageInfo array
			}

			if(file_exists($this->pathtologs.$this->ifileprev))
			{
				require($this->pathtologs.$this->ifileprev);		// Yesterdays browser accesses etc
			}

			foreach($browserInfo as $name => $amount)
			{
				$statBrowser[$name] += $amount;
				$mon_statBrowser[$name] += $amount;
			}

			foreach($osInfo as $name => $amount)
			{
				$statOs[$name] += $amount;
				$mon_statOs[$name] += $amount;
			}

			foreach($screenInfo as $name => $amount)
			{
				$statScreen[$name] += $amount;
				$mon_statScreen[$name] += $amount;
			}


			foreach($domainInfo as $name => $amount)
			{
				if(!is_numeric($name))
				{
					$statDomain[$name] += $amount;
					$mon_statDomain[$name] += $amount;
				}
			}

			foreach($refInfo as $name => $info)
			{
				$statReferer[$name]['url'] = $info['url'];
				$statReferer[$name]['ttl'] += $info['ttl'];
				$mon_statReferer[$name]['url'] = $info['url'];
				$mon_statReferer[$name]['ttl'] += $info['ttl'];
			}


			foreach($searchInfo as $name => $amount)
			{
				$statQuery[$name] += $amount;
				$mon_statQuery[$name] += $amount;
			}

			$browser 	= serialize($statBrowser);
			$os 		= serialize($statOs);
			$screen 	= serialize($statScreen);
			$domain 	= serialize($statDomain);
			$refer 		= serialize($statReferer);
			$squery 	= serialize($statQuery);

			$statTotal += $siteTotal;
			$statUnique += $siteUnique;

			// Save cumulative results - always keep track of these, even if the $pref doesn't display them
			$sql->update("logstats", "log_data='{$browser}' WHERE log_id='statBrowser'");
			$sql->update("logstats", "log_data='{$os}' WHERE log_id='statOs'");
			$sql->update("logstats", "log_data='{$screen}' WHERE log_id='statScreen'");
			$sql->update("logstats", "log_data='{$domain}' WHERE log_id='statDomain'");
			$sql->update("logstats", "log_data='{$refer}' WHERE log_id='statReferer'");
			$sql->update("logstats", "log_data='{$squery}' WHERE log_id='statQuery'");
			$sql->update("logstats", "log_data='".intval($statTotal)."' WHERE log_id='statTotal'");
			$sql->update("logstats", "log_data='".intval($statUnique)."' WHERE log_id='statUnique'");


			// Now save the relevant monthly results - only where enabled
			foreach ($stats_list as $s)
			{
				if (isset($pref[$s]) && ($pref[$s] > 1))
				{ // Value 2 requires saving of monthly stats
					$srcvar = 'mon_'.$s;
					$destvar = 'smon_'.$s;
					$$destvar = serialize($$srcvar);

					if (!$sql->update("logstats", "log_data='".$$destvar."' WHERE log_id='".$s.":".$this->date3."'"))
					{
						$sql->insert("logstats", "0, '".$s.":".$this->date3."', '".$$destvar."'");
					}
				}
			}


			/* get page access monthly info from db */
			if($sql->select("logstats", "*", "log_id='{$this->date3}' "))
			{
				$tmp = $sql->fetch();
				$monthlyInfo = unserialize($tmp['log_data']);
				unset($tmp);
				$MonthlyExistsFlag = TRUE;
			}

			foreach($pageInfo as $key => $info)
			{
				$monthlyInfo['TOTAL']['ttlv'] += $info['ttl'];
				$monthlyInfo['TOTAL']['unqv'] += $info['unq'];
				$monthlyInfo[$key]['ttlv'] += $info['ttl'];
				$monthlyInfo[$key]['unqv'] += $info['unq'];
			}

			$monthlyinfo = serialize($monthlyInfo);

			if($MonthlyExistsFlag)
			{
				$sql->update("logstats", "log_data='{$monthlyinfo}' WHERE log_id='{$this->date3}'");
			}
			else
			{
				$sql->insert("logstats", "0, '{$this->date3}', '{$monthlyinfo}'");
			}


			$this->collatePageTotal($pageInfo);
			$this->collatePageInfo($pageInfo, $this->date2);
			$this->resetLogFiles();

			/* and finally, we need to create new logfiles for today ... */
			$this->createLog();

			return true;
		}




		function createLog()
		{

			if(!is_writable($this->pathtologs))
			{
				echo "<div class='alert alert-error'>Log directory is not writable - please CHMOD ".e_LOG." to 777";
				echo '<br />Path to logs: '.$this->pathtologs;
				echo "</div>";

				return false;
			}

			$varStart = chr(36);
			$quote = chr(34);

			$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n".
				$varStart."refererData = ".$quote.$quote.";\n".
				$varStart."ipAddresses = ".$quote.$quote.";\n".
				$varStart."hosts = ".$quote.$quote.";\n".
				$varStart."siteTotal = ".$quote."0".$quote.";\n".
				$varStart."siteUnique = ".$quote."0".$quote.";\n".
				$varStart."screenInfo = array();\n".
				$varStart."browserInfo = array();\n".
				$varStart."osInfo = array();\n".
				$varStart."pageInfo = array(\n";

			$data .= "\n);\n\n?".  chr(62);

			if(!touch($this->pathtologs.$this->pfile))
			{
				return false;
			}

			if(!touch($this->pathtologs.$this->ifile))
			{
				return false;
			}

			if(!is_writable($this->pathtologs.$this->pfile))
			{
				$old = umask(0);
				chmod($this->pathtologs.$this->pfile, 0777);
				umask($old);
				//	return false;
			}

			if(!is_writable($this->pathtologs.$this->ifile))
			{
				$old = umask(0);
				chmod($this->pathtologs.$this->ifile, 0777);
				umask($old);
				//	return false;
			}

			if ($handle = fopen($this->pathtologs.$this->pfile, 'w'))
			{
				fwrite($handle, $data);
			}

			fclose($handle);


			$data = "<?php";
			$data .= "
/* e107 website system: Log info file: ".date("z:Y", time())." */

";
			$data .= '$domainInfo'." = array();\n\n";
			$data .= '$screenInfo'." = array();\n\n";
			$data .= '$browserInfo'." = array();\n\n";
			$data .= '$osInfo'." = array();\n\n";
			$data .= '$refInfo'." = array();\n\n";
			$data .= '$searchInfo'." = array();\n\n";
			$data .= '$visitInfo'." = array();\n\n";
			$data .= "?>";

			if ($handle = fopen($this->pathtologs.$this->ifile, 'w'))
			{
				fwrite($handle, $data);
			}
			fclose($handle);

			return true;
		}




		/**
		* Called if both today's and yesterday's log files missing, to see
		* if there are any older files we could process. Return false if nothing
		* Otherwise return a string of relevant information
	    * @param $pathtologs
		* @return bool|string
		*/
		function check_for_old_files($pathtologs)
		{
		//	$no_files = TRUE;
			if ($dir_handle = opendir($pathtologs))
			{
				while (false !== ($file = readdir($dir_handle)))
				{
					// Do match on #^logp_(\d{1,3})\.php$#i
					if (preg_match('#^logp_(\d{1,3}\.\d{4})\.php$#i',$file,$match) == 1)
					{  // got a matching file
						$yesterday = $match[1];						// Day of year - zero is 1st Jan
						$pfileprev = "logp_".$yesterday.".php";		// Yesterday's log file
						$ifileprev = "logi_".$yesterday.".php";
						list($day,$year) = explode('.',$yesterday);
						$tstamp = mktime(0,0,0,1,1,$year) + ($day*86400);
						$date2 = date("Y-m-j", $tstamp);		// Yesterday's date for the database summary
						$temp = array($pfileprev,$ifileprev,$date2,$tstamp);
						return implode('|',$temp);
					}
				}
			}
			return false;
		}





		// for future use.
		private function collate($pfile)
		{
			if(is_readable(e_LOG.$pfile))
			{
				require(e_LOG.$pfile); // contains $pageInfo;
			}
			else
			{
				return false;
			}

			return null;
		}




		/**
		 * @param $url
		 * @param bool $logQry
		 * @param string $err_code
		 * @return bool|mixed|string
		 */
		function getPageKey($url,$logQry=false,$err_code='', $lan=null)
		{
			$pageDisallow = "cache|file|eself|admin";
			$tagRemove = "(\\\)|(\s)|(\')|(\")|(eself)|(&nbsp;)|(\.php)|(\.html)";

		//	preg_match("#/(.*?)(\?|$)(.*)#si", $url, $match);
		//	$match[1] = isset($match[1]) ? $match[1] : '';
		//	$pageName = substr($match[1], (strrpos($match[1], "/")+1));

			if(deftrue('e_DOMAIN'))
			{
				list($discard,$pageName) = explode(e_DOMAIN.'/',$url); // everything after the domain.
			}
			else // eg. local setup.
			{
				$pageName = str_replace(SITEURL,'',$url);
			}

			$pageName = urldecode($pageName);

			$pageName = preg_replace("/".$tagRemove."/si", "", $pageName);

			if($logQry == false)
			{
				list($pageName,$tmp) = explode("?",$pageName);
			}

			if($pageName == "")
			{
				$pageName = "index";
			}

			if(preg_match("/".$pageDisallow."/i", $pageName))
			{
				return false;
			}

		//	if ($logQry)
		//	{
		//		$pageName .= '+'.$match[3];			// All queries match
		//	}

			$pageName = $err_code.$pageName;			// Add the error code at the beginning, so its treated uniquely

			// filter out any non-utf8 characters which could halt processing.

			$pageName = iconv('UTF-8', 'ASCII//IGNORE', $pageName);

			$pageName = trim($pageName,' /');


			if(!empty($lan))
			{
				$pageName .= "|".$lan;
			}

			return $pageName;
		}


		/**
		 * Process Raw Backup Log File. e. e_LOG."log/2015-09-24_SiteStats.log
		 * This method can be used in the case of a database corruption to restore stats to the database.
		 * @param string $file
		 * @param bool $savetoDB
		 * @return bool
		 * @example processRawBackupLog('2015-09-24_SiteStats.log', false);
		 */
		function processRawBackupLog($file, $savetoDB=false)
		{
			$path = e_LOG."log/".$file;

			$mes = e107::getMessage();

			if(!is_readable($path))
			{
				$mes->addError( "File Not Found: ".$path);
				return false;
			}

			$handle = fopen($path, "r");

			$pageTotal = array();
			$line = 0;


			if ($handle)
			{
				while (($buffer = fgets($handle, 4096)) !== false)
				{
					if($vars = $this->splitRawBackupLine($buffer))
					{

						if(substr($vars['eself'],0,7) == 'file://')
						{
							continue;
						}

						$lan = varset($vars['lan'],null);
						$key = $this->getPageKey($vars['eself'],false,'',$lan);

						if(empty($key))
						{
							continue;
						}

						if(!isset($pageTotal[$key]))
						{
							$pageTotal[$key] = array('url'=>'', 'ttl'=>0, 'unq'=>0, 'lan'=>'');
						}

						$pageTotal[$key]['url'] = $vars['eself'];
						$pageTotal[$key]['ttl'] += 1;

					//	echo "\n<br />line: ".$line."   ------- ".$key;

						if(isset($vars['unique']))
						{
							if($vars['unique'] == 1)
							{
								$pageTotal[$key]['unq'] += 1;
							}
						}
						else
						{
							$pageTotal[$key]['unq'] += 1;
						}

						$lan = varset($vars['lan'],'');
						$pageTotal[$key]['lan'] = $lan;
					}

					$line++;
				}

				if (!feof($handle))
				{
					$mes->addError( "Error: unexpected fgets() fail.");
				}

				fclose($handle);


				if(e_DEBUG)
				{
					$mes->addDebug("<h3>".$file."</h3>");
					$mes->addDebug(print_a($pageTotal,true));
				}
			}

			if($savetoDB === false)
			{
				$mes->addInfo( "Saving mode is off");
				return true;
			}


			if(!empty($pageTotal))
			{
				list($date,$name) = explode("_", $file, 2);

				unset($name);

				$unix = strtotime($date);

				$datestamp = date("Y-m-j", $unix);

				if(!empty($datestamp))
				{
					$sql = e107::getDb();

					if($sql->select('logstats','log_id',"log_id='".$datestamp."' ") && !$sql->select('logstats','log_id',"log_id='bak-".$datestamp."' "))
					{
						$sql->update('logstats', "log_id='bak-".$datestamp."' WHERE log_id='".$datestamp."' ");
					}

					if($this->collatePageInfo($pageTotal, $datestamp))
					{
						$message = e107::getParser()->lanVars(ADSTAT_LAN_90, array('x'=>$datestamp));
				        $mes->addSuccess($message);
					}
					else
					{
						$message = e107::getParser()->lanVars(ADSTAT_LAN_91, array('x'=>$datestamp));
				        $mes->addError($message);
					}
				}

			}

			return true;
		}




		private function splitRawBackupLine($line)
		{
			list($datestamp,$bla,$data) = explode("\t",$line, 3);

			if(!empty($data))
			{
				parse_str($data,$vars);
				return $vars;
			}

			unset($datestamp, $bla); // remove editor warnings.

			return false;
		}

		/**
		 * Fix corrupted page data.
		 * Re-calculate all page totals from all existing database information and save to DB as 'pageTotal'. .
		 */
		function collatePageTotalDB()
		{
			$sql = e107::getDb();

			$qry = "SELECT * FROM `#logstats` WHERE `log_id` REGEXP '^[0-9]' AND LENGTH(log_id) > 7 AND `log_data` LIKE '%http%'";
			$data = $sql->retrieve($qry,true);

			$pageTotal = array();

			foreach($data as $values)
			{
				$tmp = explode(chr(1),$values['log_data']);
				unset($tmp[0],$tmp[1]);
				$thisTotal = array();

				foreach($tmp as $val)
				{
					if(!empty($val))
					{
						list($url,$ttl,$unq,$lan) = explode("|",$val);
						$lan = vartrue($lan,e_LAN);
						$key = $this->getPageKey($url,'','', $lan);

						$thisTotal[$key]['url'] = $url;
						$thisTotal[$key]['lan'] = $lan;
						$thisTotal[$key]['ttlv'] += $ttl;
						$thisTotal[$key]['unqv'] += $unq;

						$pageTotal[$key]['url'] = $url;
						$pageTotal[$key]['lan'] = $lan;
						$pageTotal[$key]['ttlv'] += $ttl;
						$pageTotal[$key]['unqv'] += $unq;
					}

				}

			//	echo "<h3>".$values['log_id']."</h3>";
			//	print_a($thisTotal);

			}

			if(empty($pageTotal))
			{
				return false;
			}

			$id = $sql->retrieve('logstats','log_uniqueid', "log_id='pageTotal'");

			$insertData = array(
				'log_uniqueid' => intval($id),
				'log_id'=> 'pageTotal',
				'log_data'=> serialize($pageTotal)
			);

			//	echo "<h2>Total</h2>";
			//  print_a($pageTotal);

			return $sql->replace('logstats', $insertData);

		}


		/**
		 * collate page total information using today's data and totals stored in DB.
		 * @param $pageInfo - from today's file.
		 * @return bool
		 */
		function collatePageTotal($pageInfo=array())
		{
			$sql = e107::getDb();

			if($sql->select("logstats", "*", "log_id='pageTotal' "))
			{
				$tmp = $sql->fetch();
				$pageTotal = unserialize($tmp['log_data']);
				$uniqueID = $tmp['log_uniqueid'];
				unset($tmp);

			}
			else
			{
				$pageTotal = array();
				$uniqueID = 0;
			}

		//	echo "<h3>DB Totals</h3>";
		//	print_a($pageTotal);

		//	echo "<h3>From File</h3>";
		//	print_a($pageInfo);

			foreach($pageInfo as $key => $info)
			{
				$pageTotal[$key]['url'] = $info['url'];
				$pageTotal[$key]['ttlv'] += $info['ttl'];
				$pageTotal[$key]['unqv'] += $info['unq'];
			}

		//	echo "<h3>Consilidated</h3>";
		//	print_a($pageTotal);

			if(empty($pageTotal))
			{
				return false;
			}

			$insertData = array(
				'log_uniqueid'  => intval($uniqueID),
				'log_id'        => 'pageTotal',
				'log_data'      => serialize($pageTotal)
			);

			return $sql->replace('logstats', $insertData);

		}


		/**
		 * Collate individual page information into an array and save to database.
		 * @param array $pageInfo
		 * @param string $date  - the value saved to log_id ie. Y-m-j  , 2015-02-1, 2015-02-30
		 * @return bool
		 */
		function collatePageInfo($pageInfo, $date)
		{

			$sql = e107::getDb();
			$tp = e107::getParser();

			$data = "";
			$dailytotal = 0;
			$uniquetotal = 0;

			foreach($pageInfo as $key => $value)
			{
				$data .= $value['url']."|".$value['ttl']."|".$value['unq'].'|'.varset($value['lan'],e_LAN).chr(1);
				$dailytotal += $value['ttl'];
				$uniquetotal += $value['unq'];
			}

			$data = $dailytotal.chr(1).$uniquetotal.chr(1) . $data;
			return $sql->insert("logstats", "0, '$date', '".$tp -> toDB($data, true)."'");
		}


		/**
		 * Reset (empty) yesterday's log files.
		 * @return bool
		 */
		function resetLogFiles()
		{

			if(empty($this->pfileprev) || empty($this->ifileprev))
			{
				return false;
			}

			/* ok, we're finished with the log file now, we can empty it ... */
			if(!unlink($this->pathtologs.$this->pfileprev))
			{
				$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n\n\n".chr(47)."* THE INFORMATION IN THIS LOG FILE HAS BEEN CONSOLIDATED INTO THE DATABASE - YOU CAN SAFELY DELETE IT. *". chr(47)."\n\n\n?".  chr(62);

				if($handle = fopen($this->pathtologs.$this->pfileprev, 'w'))
				{
					fwrite($handle, $data);
				}

				fclose($handle);
			}


			if(!unlink($this->pathtologs.$this->ifileprev))
			{
				$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n\n\n".chr(47)."* THE INFORMATION IN THIS LOG INFO FILE HAS BEEN CONSOLIDATED INTO THE DATABASE - YOU CAN SAFELY DELETE IT. *". chr(47)."\n\n\n?".  chr(62);

				if($handle = fopen($this->pathtologs.$this->ifileprev, 'w'))
				{
					fwrite($handle, $data);
				}

				fclose($handle);
			}


			return true;
		}
}









