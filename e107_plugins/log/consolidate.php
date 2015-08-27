<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/log/consolidate.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/* first thing to do is check if the log file is out of date ... */

// $pathtologs = e_PLUGIN."log/logs/";

if (!defined('e107_INIT')){ exit; } 

$pathtologs 	= e_LOG;
$date 			= date("z.Y", time());
$yesterday 		= date("z.Y",(time() - 86400));		// This makes sure year wraps round OK
$date2 			= date("Y-m-j", (time() -86400));		// Yesterday's date for the database summary	
$date3 			= date("Y-m", (time() -86400));			// Current month's date for monthly summary (we're working with yesterday's data)

$pfileprev 		= "logp_".$yesterday.".php";		// Yesterday's log file
$pfile 			= "logp_".$date.".php";				// Today's log file
$ifileprev 		= "logi_".$yesterday.".php";
$ifile 			= "logi_".$date.".php";

if(file_exists($pathtologs.$pfile))  /* log file is up to date, no consolidation required */
{
	return;
}
else if(!file_exists($pathtologs.$pfileprev))  // See if any older log files
{  
  if (($retvalue = check_for_old_files($pathtologs)) === FALSE) /* no logfile found at all - create - this will only ever happen once ... */
  { 	
	createLog($pathtologs);
	return FALSE;
  }
 
  list($pfileprev,$ifileprev,$date2,$tstamp) = explode('|',$retvalue);  // ... if we've got files
}



// List of the non-page-based info which is gathered - historically only 'all-time' stats, now we support monthly as well
$stats_list = array('statBrowser','statOs','statScreen','statDomain','statReferer','statQuery');

$qry = "`log_id` IN ('statTotal','statUnique'";
foreach ($stats_list as $s)
{
  $qry .= ",'{$s}'";									// Always read the all-time stats
  if ($pref[$s] == 2) $qry .= ",'{$s}:{$date3}'";		// Look for monthlys as well as cumulative
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
		elseif (($pos = strpos($row['log_id'],':')) === FALSE)
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


require_once($pathtologs.$pfileprev);		// Yesterday's page accesses - $pageInfo array
require_once($pathtologs.$ifileprev);		// Yesterdays browser accesses etc

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
	
	if (!$sql->update("logstats", "log_data='".$$destvar."' WHERE log_id='".$s.":".$date3."'"))
	{
	  $sql->insert("logstats", "0, '".$s.":".$date3."', '".$$destvar."'");
	}
  }
}



/* get page access monthly info from db */
if($sql->select("logstats", "*", "log_id='{$date3}' ")) 
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
	$sql->update("logstats", "log_data='{$monthlyinfo}' WHERE log_id='{$date3}'");
} 
else 
{
	$sql->insert("logstats", "0, '{$date3}', '{$monthlyinfo}'");
}


/* collate page total information */
if($sql->select("logstats", "*", "log_id='pageTotal' "))
{
	$tmp = $sql->fetch();
	$pageTotal = unserialize($tmp['log_data']);
	unset($tmp);
}
else
{
	$pageTotal = array();
}

foreach($pageInfo as $key => $info)
{
	$pageTotal[$key]['url'] = $info['url'];
	$pageTotal[$key]['ttlv'] += $info['ttl'];
	$pageTotal[$key]['unqv'] += $info['unq'];
}

$pagetotal = serialize($pageTotal);

$insertPageTotal = array('log_data'=> $pageTotal, 'WHERE' => "log_id='pageTotal'");
$sql->replace('logstats', $insertPageTotal);

/*
if(!$sql->update("logstats", "log_data='{$pagetotal}' WHERE log_id='pageTotal' "))
{
	$sql->insert("logstats", "0, 'pageTotal', '{$pagetotal}' ");
}*/


/* now we need to collate the individual page information into an array ... */

$data = "";
$dailytotal = 0;
$uniquetotal = 0;
foreach($pageInfo as $key => $value)
{
	$data .= $value['url']."|".$value['ttl']."|".$value['unq'].chr(1);
	$dailytotal += $value['ttl'];
	$uniquetotal += $value['unq'];
}

$data = $dailytotal.chr(1).$uniquetotal.chr(1) . $data;
$sql->insert("logstats", "0, '$date2', '".$tp -> toDB($data, true)."'");

	
/* ok, we're finished with the log file now, we can empty it ... */
if(!unlink($pathtologs.$pfileprev))
{
	$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n\n\n".chr(47)."* THE INFORMATION IN THIS LOG FILE HAS BEEN CONSOLIDATED INTO THE DATABASE - YOU CAN SAFELY DELETE IT. *". chr(47)."\n\n\n?".  chr(62);
	if ($handle = fopen($pathtologs.$pfileprev, 'w')) { 
		fwrite($handle, $data);
	}
	fclose($handle);
}
if(!unlink($pathtologs.$ifileprev))
{
	$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n\n\n".chr(47)."* THE INFORMATION IN THIS LOG INFO FILE HAS BEEN CONSOLIDATED INTO THE DATABASE - YOU CAN SAFELY DELETE IT. *". chr(47)."\n\n\n?".  chr(62);
	if ($handle = fopen($pathtologs.$ifileprev, 'w')) { 
		fwrite($handle, $data);
	}
	fclose($handle);
}

/* and finally, we need to create new logfiles for today ... */
createLog($pathtologs);
/* done! */


function createLog($pathtologs) 
{
	global $statTotal, $statUnique, $pfile, $ifile;
	if(!is_writable($pathtologs)) 
	{
		echo "Log directory is not writable - please CHMOD ".e_LOG." to 777";
		echo '<br />Path to logs: '.$pathtologs;
		return FALSE;
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

	if(!touch($pathtologs.$pfile)) {
		return FALSE;
	}

	if(!touch($pathtologs.$ifile)) {
		return FALSE;
	}

	if(!is_writable($pathtologs.$pfile)) {
		$old = umask(0);
		chmod($pathtologs.$pfile, 0777);
		umask($old);
	//	return FALSE;
	}

	if(!is_writable($pathtologs.$ifile)) {
		$old = umask(0);
		chmod($pathtologs.$ifile, 0777);
		umask($old);
	//	return FALSE;
	}

	if ($handle = fopen($pathtologs.$pfile, 'w')) 
	{ 
		fwrite($handle, $data);
	}
	fclose($handle);


$data = "<?php

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

	if ($handle = fopen($pathtologs.$ifile, 'w')) 
	{ 
		fwrite($handle, $data);
	}
	fclose($handle);
	return;
}

// Called if both today's and yesterday's log files missing, to see
// if there are any older files we could process. Return FALSE if nothing
// Otherwise return a string of relevant information
function check_for_old_files($pathtologs)
{
  $no_files = TRUE;
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
  return FALSE;
}

?>