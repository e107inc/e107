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
+----------------------------------------------------------------------------+
*/

/* first thing to do is check if the log file is out of date ... */

//require_once("../../class2.php");

$pathtologs = e_PLUGIN."log/logs/";
$date = date("z.Y", time());
$date2 = date("Y-m-j", (time() -86400));
$date3 = date("Y-m");
$day = date("z", time());
$year = date("Y", time());

$pfileprev = "logp_".($day-1).".".$year.".php";
$pfile = "logp_".$date.".php";
$ifileprev = "logi_".($day-1).".".$year.".php";
$ifile = "logi_".$date.".php";

if(file_exists($pathtologs.$pfile)) {
	/* log file is up to date, no consolidation required */
	return;
}else if(!file_exists($pathtologs.$pfileprev)) {
	/* no logfile found at all - create - this will only ever happen once ... */
	createLog("blank");
	return FALSE;
}



/* log file is out of date - consolidation required */

/* get existing stats ... */
if($sql -> db_Select("logstats", "*", "log_id='statBrowser' OR log_id='statOs' OR log_id='statScreen' OR log_id='statDomain' OR log_id='statTotal' OR log_id='statUnique' OR log_id='statReferer' OR log_id='statQuery'")) {
	$infoArray = array();
	while($row = $sql -> db_Fetch())
	{
		$$row[1] = unserialize($row[2]);
		if($row[1] == "statUnique") $statUnique = $row[2];
		if($row[1] == "statTotal") $statTotal = $row[2];
	}
}else{
	/* this must be the first time a consolidation has happened - this will only ever happen once ... */
	$sql -> db_Insert("logstats", "0, 'statBrowser', ''");
	$sql -> db_Insert("logstats", "0, 'statOs', ''");
	$sql -> db_Insert("logstats", "0, 'statScreen', ''");
	$sql -> db_Insert("logstats", "0, 'statDomain', ''");
	$sql -> db_Insert("logstats", "0, 'statReferer', ''");
	$sql -> db_Insert("logstats", "0, 'statQuery', ''");
	$sql -> db_Insert("logstats", "0, 'statTotal', '0'");
	$sql -> db_Insert("logstats", "0, 'statUnique', '0'");
	$statBrowser =array();
	$statOs =array();
	$statScreen =array();
	$statDomain =array();
	$statReferer =array();
	$statQuery =array();
}

require_once($pathtologs.$pfileprev);
require_once($pathtologs.$ifileprev);

foreach($browserInfo as $name => $amount) {
	$statBrowser[$name] += $amount;
}

foreach($osInfo as $name => $amount) {
	$statOs[$name] += $amount;
}

foreach($screenInfo as $name => $amount) {
	$statScreen[$name] += $amount;
}


foreach($domainInfo as $name => $amount) {
	if(!is_numeric($name)) {
		$statDomain[$name] += $amount;
	}
}

foreach($refInfo as $name => $info) {
	$statReferer[$name]['url'] = $info['url'];
	$statReferer[$name]['ttl'] += $info['ttl'];
}


foreach($searchInfo as $name => $amount) {
	$statQuery[$name] += $amount;
}

$browser = serialize($statBrowser);
$os = serialize($statOs);
$screen = serialize($statScreen);
$domain = serialize($statDomain);
$refer = serialize($statReferer);
$squery = serialize($statQuery);

$statTotal += $siteTotal;
$statUnique += $siteUnique;

$sql -> db_Update("logstats", "log_data='$browser' WHERE log_id='statBrowser'");
$sql -> db_Update("logstats", "log_data='$os' WHERE log_id='statOs'");
$sql -> db_Update("logstats", "log_data='$screen' WHERE log_id='statScreen'");
$sql -> db_Update("logstats", "log_data='$domain' WHERE log_id='statDomain'");
$sql -> db_Update("logstats", "log_data='$refer' WHERE log_id='statReferer'");
$sql -> db_Update("logstats", "log_data='$squery' WHERE log_id='statQuery'");
$sql -> db_Update("logstats", "log_data='$statTotal' WHERE log_id='statTotal'");
$sql -> db_Update("logstats", "log_data='$statUnique' WHERE log_id='statUnique'");


/* get monthly info from db */
if($sql -> db_Select("logstats", "*", "log_id='$date3' ")) {
	$tmp = $sql -> db_Fetch();
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

if($MonthlyExistsFlag) {
	$sql -> db_Update("logstats", "log_data='$monthlyinfo' WHERE log_id='$date3'");
} else {
	$sql->db_Insert("logstats", "0, '$date3', '$monthlyinfo'");
}


/* collate page total information */
if($sql -> db_Select("logstats", "*", "log_id='pageTotal' "))
{
	$tmp = $sql -> db_Fetch();
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

if(!$sql -> db_Update("logstats", "log_data='$pagetotal' WHERE log_id='pageTotal' "))
{
	$sql -> db_Insert("logstats", "0, 'pageTotal', '$pagetotal' ");
}


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
$sql -> db_Insert("logstats", "0, '$date2', '$data'");

	
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

/* and finally, we need to create a new logfiles for today ... */
createLog();
/* done! */


function createLog($mode="default") {
	global $pathtologs, $statTotal, $statUnique, $pageArray, $pfile, $ifile;
	if(!is_writable($pathtologs)) {
		echo "Log directory is not writable - please CHMOD ".e_PLUGIN."log/logs to 777";
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

	if($mode == "default") {
		reset($pageArray);
		$loop = FALSE;
		foreach($pageArray as $key => $info) {
			if($loop) {
				$data .= ",\n";
			}
			$data .= $quote.$key.$quote." => array('url' => '".$info['url']."', 'ttl' => 0, 'unq' => 0, 'ttlv' => ".$info['ttlv'].", 'unqv' => ".$info['unqv'].")";
			$loop = TRUE;
		}
	}

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

	if ($handle = fopen($pathtologs.$pfile, 'w')) { 
		fwrite($handle, $data);
	}
	fclose($handle);

	if ($handle = fopen($pathtologs.$ifile, 'w')) { 
		fwrite($handle, "");
	}
	fclose($handle);
	return;
}

?>