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
$date2 = date("j.m.y", time());
$date3 = date("m-y");
$day = date("z", time());
$year = date("Y", time());

$yfile = "log_".($day-1).".".$year.".php";
$tfile = "log_".$date.".php";


if(file_exists($pathtologs.$tfile)) {
	/* log file is up to date, no consolidation required */
	return;
}else if(!file_exists($pathtologs.$yfile)) {
	/* no logfile found at all - create - this will only ever happen once ... */
	createLog("blank");
	return FALSE;
}



/* log file is out of date - consolidation required */

/* get existing stats ... */
if($sql -> db_Select("logstats", "*", "log_id='statBrowser' OR log_id='statOs' OR log_id='statScreen' OR log_id='statDomain' OR log_id='statTotal' OR log_id='statUnique' OR log_id='statReferer' OR log_id='statQuery'")) {
	$infoArray = array();
	while($row = $sql -> db_Fetch()) {
		$$row[1] = unserialize($row[2]);
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
	
require_once($pathtologs.$yfile);

$ipArray = explode("\n", $ipAddresses);


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
if($sql -> db_Select("logstats", "*", "log_id REGEXP('[[:digit:]]+-[[:digit:]]+')")) {
	$tmp = $sql -> db_Fetch();
	$monthlyInfo = unserialize($tmp['log_data']);
	unset($tmp);
	$MonthlyExistsFlag = TRUE;
}

foreach($pageInfo as $key => $info) {
	$key = preg_replace("/\?.*/", "", $key);
	if(array_key_exists($key, $monthlyInfo)) {
		$monthlyInfo[$key]['ttlv'] += $info['ttlv'];
		$monthlyInfo[$key]['unqv'] += $info['unqv'];
	} else {
		$monthlyInfo[$key]['ttlv'] = $info['ttlv'];
		$monthlyInfo[$key]['unqv'] = $info['unqv'];
	}
}

$monthlyinfo = serialize($monthlyInfo);

if($MonthlyExistsFlag) {
	$sql -> db_Update("logstats", "log_data='$monthlyinfo' WHERE log_id='$date3'");
} else {
	$sql->db_Insert("logstats", "0, '$date3', '$monthlyinfo'");
}

/* now we need to collate the individual page information into an array ... */
if($sql -> db_Select("logstats", "*", "log_id REGEXP('[[:digit:]]+.')")) {
	$tmp = $sql -> db_Fetch();
	$pageArray = unserialize($tmp['log_data']);
	unset($tmp);
}



foreach($pageInfo as $key => $info) {
	$key = preg_replace("/\?.*/", "", $key);
	if(array_key_exists($key, $pageArray)) {
		$pageArray[$key]['ttl'] += $info['ttl'];
		$pageArray[$key]['unq'] += $info['unq'];
		$pageArray[$key]['ttlv'] += $info['ttlv'];
		$pageArray[$key]['unqv'] += $info['unqv'];
	} else {
		$pageArray[$key]['url'] = $info['url'];
		$pageArray[$key]['ttl'] = $info['ttl'];
		$pageArray[$key]['unq'] = $info['unq'];
		$pageArray[$key]['ttlv'] = $info['ttlv'];
		$pageArray[$key]['unqv'] = $info['unqv'];
	}
}

$pagearray = serialize($pageArray);

$sql->db_Insert("logstats", "0, '$date2', '$pagearray'");
	
/* ok, we're finished with the log file now, we can empty it ... */
if(!unlink($pathtologs.$yfile))
{
	$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n\n\n".chr(47)."* THE IMFORMATION IN THIS LOG FILE HAS BEEN CONSOLIDATED INTO THE DATABASE - YOU CAN SAFELY DELETE IT. *". chr(47)."\n\n\n?".  chr(62);
	if ($handle = fopen($pathtologs.$yfile, 'w')) { 
		fwrite($handle, $data);
	}
	fclose($handle);
}

/* and finally, we need to create a new logfile for today ... */
createLog();
/* done! */


function createLog($mode="default") {
	global $pathtologs, $statTotal, $statUnique, $pageArray, $tfile;
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
	$varStart."siteTotal = ".$quote.$statTotal.$quote.";\n".
	$varStart."siteUnique = ".$quote.$siteUnique.$quote.";\n".
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

	if(!touch($pathtologs.$tfile)) {
		return FALSE;
	}

	if(!is_writable($pathtologs.$tfile)) {
		$old = umask(0);
		chmod($pathtologs.$tfile, 0777);
		umask($old);
		return FALSE;
	}

	if ($handle = fopen($pathtologs.$tfile, 'w')) { 
		fwrite($handle, $data);
	}
	fclose($handle);
	return;
}

?>