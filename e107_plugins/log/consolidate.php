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

$pathtologs = e_PLUGIN."log/logs/";
$date = date("z.Y", time());
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
if($sql -> db_Select("stat_info", "*", "log_id='statBrowser' OR log_id='statOs' OR log_id='statScreen' OR log_id='statDomain' OR log_id='statTotal' OR log_id='statUnique' OR log_id='statReferer' OR log_id='statQuery'")) {
	$infoArray = array();
	while($row = $sql -> db_Fetch()) {
		$$row[0] = unserialize($row[1]);
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
	if($country = getcountry($name)) {
		$statDomain[$country] += $amount;
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

/* now we need to collate the individual page information into an array ... */
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

$sql->db_Insert("logstats", "0, '$date', '$pagearray'");
	
/* ok, we're finished with the log file now, we can empty it ... */

$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n\n\n".chr(47)."* THE IMFORMATION IN THIS LOG FILE HAS BEEN CONSOLIDATED INTO THE DATABASE - YOU CAN SAFELY DELETE IT. *". chr(47)."\n\n\n?".  chr(62);
if ($handle = fopen($pathtologs.$yfile, 'w')) { 
	fwrite($handle, $data);
}
fclose($handle);


/* and finally, we need to create a new logfile for today ... */
createLog("blank");

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

	if($mode != "default") {
		reset($pageArray);
		$loop = FALSE;
		foreach($pageArray as $key => $info) {
			if($loop) {
				$data .= ",\n";
			}
			$data .= $quote.$key.$quote." => array('url' => '".$info['url']."', 'ttl' => ".$info['ttl'].", 'unq' => ".$info['unq'].", 'ttlv' => ".$info['ttlv'].", 'unqv' => ".$info['unqv'].")";
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



function getcountry($dom) {
		$country["arpa"] = "ARPANet";
		$country["com"] = "Commercial Users";
		$country["edu"] = "Education";
		$country["gov"] = "Government";
		$country["int"] = "Oganization established by an International Treaty";
		$country["mil"] = "Military";
		$country["net"] = "Network";
		$country["org"] = "Organization";
		$country["ad"] = "Andorra";
		$country["ae"] = "United Arab Emirates";
		$country["af"] = "Afghanistan";
		$country["ag"] = "Antigua & Barbuda";
		$country["ai"] = "Anguilla";
		$country["al"] = "Albania";
		$country["am"] = "Armenia";
		$country["an"] = "Netherland Antilles";
		$country["ao"] = "Angola";
		$country["aq"] = "Antarctica";
		$country["ar"] = "Argentina";
		$country["as"] = "American Samoa";
		$country["at"] = "Austria";
		$country["au"] = "Australia";
		$country["aw"] = "Aruba";
		$country["az"] = "Azerbaijan";
		$country["ba"] = "Bosnia-Herzegovina";
		$country["bb"] = "Barbados";
		$country["bd"] = "Bangladesh";
		$country["be"] = "Belgium";
		$country["bf"] = "Burkina Faso";
		$country["bg"] = "Bulgaria";
		$country["bh"] = "Bahrain";
		$country["bi"] = "Burundi";
		$country["bj"] = "Benin";
		$country["bm"] = "Bermuda";
		$country["bn"] = "Brunei Darussalam";
		$country["bo"] = "Bolivia";
		$country["br"] = "Brasil";
		$country["bs"] = "Bahamas";
		$country["bt"] = "Bhutan";
		$country["bv"] = "Bouvet Island";
		$country["bw"] = "Botswana";
		$country["by"] = "Belarus";
		$country["bz"] = "Belize";
		$country["ca"] = "Canada";
		$country["cc"] = "Cocos (Keeling) Islands";
		$country["cf"] = "Central African Republic";
		$country["cg"] = "Congo";
		$country["ch"] = "Switzerland";
		$country["ci"] = "Ivory Coast";
		$country["ck"] = "Cook Islands";
		$country["cl"] = "Chile";
		$country["cm"] = "Cameroon";
		$country["cn"] = "China";
		$country["co"] = "Colombia";
		$country["cr"] = "Costa Rica";
		$country["cs"] = "Czechoslovakia";
		$country["cu"] = "Cuba";
		$country["cv"] = "Cape Verde";
		$country["cx"] = "Christmas Island";
		$country["cy"] = "Cyprus";
		$country["cz"] = "Czech Republic";
		$country["de"] = "Germany";
		$country["dj"] = "Djibouti";
		$country["dk"] = "Denmark";
		$country["dm"] = "Dominica";
		$country["do"] = "Dominican Republic";
		$country["dz"] = "Algeria";
		$country["ec"] = "Ecuador";
		$country["ee"] = "Estonia";
		$country["eg"] = "Egypt";
		$country["eh"] = "Western Sahara";
		$country["er"] = "Eritrea";
		$country["es"] = "Spain";
		$country["et"] = "Ethiopia";
		$country["fi"] = "Finland";
		$country["fj"] = "Fiji";
		$country["fk"] = "Falkland Islands (Malvibas)";
		$country["fm"] = "Micronesia";
		$country["fo"] = "Faroe Islands";
		$country["fr"] = "France";
		$country["fx"] = "France (European Territory)";
		$country["ga"] = "Gabon";
		$country["gb"] = "Great Britain";
		$country["gd"] = "Grenada";
		$country["ge"] = "Georgia";
		$country["gf"] = "Guyana (French)";
		$country["gh"] = "Ghana";
		$country["gi"] = "Gibralta";
		$country["gl"] = "Greenland";
		$country["gm"] = "Gambia";
		$country["gn"] = "Guinea";
		$country["gp"] = "Guadeloupe (French)";
		$country["gq"] = "Equatorial Guinea";
		$country["gr"] = "Greece";
		$country["gs"] = "South Georgia & South Sandwich Islands";
		$country["gt"] = "Guatemala";
		$country["gu"] = "Guam (US)";
		$country["gw"] = "Guinea Bissau";
		$country["gy"] = "Guyana";
		$country["hk"] = "Hong Kong";
		$country["hm"] = "Heard & McDonald Islands";
		$country["hn"] = "Honduras";
		$country["hr"] = "Croatia";
		$country["ht"] = "Haiti";
		$country["hu"] = "Hungary";
		$country["id"] = "Indonesia";
		$country["ie"] = "Ireland";
		$country["il"] = "Israel";
		$country["in"] = "India";
		$country["io"] = "British Indian Ocean Territories";
		$country["iq"] = "Iraq";
		$country["ir"] = "Iran";
		$country["is"] = "Iceland";
		$country["it"] = "Italy";
		$country["jm"] = "Jamaica";
		$country["jo"] = "Jordan";
		$country["jp"] = "Japan";
		$country["ke"] = "Kenya";
		$country["kg"] = "Kyrgyz Republic";
		$country["kh"] = "Cambodia";
		$country["ki"] = "Kiribati";
		$country["km"] = "Comoros";
		$country["kn"] = "Saint Kitts Nevis Anguilla";
		$country["kp"] = "Korea (North)";
		$country["kr"] = "Korea (South)";
		$country["kw"] = "Kuwait";
		$country["ky"] = "Cayman Islands";
		$country["kz"] = "Kazachstan";
		$country["la"] = "Laos";
		$country["lb"] = "Lebanon";
		$country["lc"] = "Saint Lucia";
		$country["li"] = "Liechtenstein";
		$country["lk"] = "Sri Lanka";
		$country["lr"] = "Liberia";
		$country["ls"] = "Lesotho";
		$country["lt"] = "Lithuania";
		$country["lu"] = "Luxembourg";
		$country["lv"] = "Latvia";
		$country["ly"] = "Libya";
		$country["ma"] = "Morocco";
		$country["mc"] = "Monaco";
		$country["md"] = "Moldova";
		$country["mg"] = "Madagascar";
		$country["mh"] = "Marshall Islands";
		$country["mk"] = "Macedonia";
		$country["ml"] = "Mali";
		$country["mm"] = "Myanmar";
		$country["mn"] = "Mongolia";
		$country["mo"] = "Macau";
		$country["mp"] = "Northern Mariana Islands";
		$country["mq"] = "Martinique (French)";
		$country["mr"] = "Mauretania";
		$country["ms"] = "Montserrat";
		$country["mt"] = "Malta";
		$country["mu"] = "Mauritius";
		$country["mv"] = "Maldives";
		$country["mw"] = "Malawi";
		$country["mx"] = "Mexico";
		$country["my"] = "Malaysia";
		$country["mz"] = "Mozambique";
		$country["na"] = "Namibia";
		$country["nc"] = "New Caledonia (French)";
		$country["ne"] = "Niger";
		$country["nf"] = "Norfolk Island";
		$country["ng"] = "Nigeria";
		$country["ni"] = "Nicaragua";
		$country["nl"] = "Netherlands";
		$country["no"] = "Norway";
		$country["np"] = "Nepal";
		$country["nr"] = "Nauru";
		$country["nt"] = "Saudiarab. Irak)";
		$country["nu"] = "Niue";
		$country["nz"] = "New Zealand";
		$country["om"] = "Oman";
		$country["pa"] = "Panama";
		$country["pe"] = "Peru";
		$country["pf"] = "Polynesia (French)";
		$country["pg"] = "Papua New Guinea";
		$country["ph"] = "Philippines";
		$country["pk"] = "Pakistan";
		$country["pl"] = "Poland";
		$country["pm"] = "Saint Pierre & Miquelon";
		$country["pn"] = "Pitcairn";
		$country["pr"] = "Puerto Rico (US)";
		$country["pt"] = "Portugal";
		$country["pw"] = "Palau";
		$country["py"] = "Paraguay";
		$country["qa"] = "Qatar";
		$country["re"] = "Reunion (French)";
		$country["ro"] = "Romania";
		$country["ru"] = "Russian Federation";
		$country["rw"] = "Rwanda";
		$country["sa"] = "Saudi Arabia";
		$country["sb"] = "Salomon Islands";
		$country["sc"] = "Seychelles";
		$country["sd"] = "Sudan";
		$country["se"] = "Sweden";
		$country["sg"] = "Singapore";
		$country["sh"] = "Saint Helena";
		$country["si"] = "Slovenia";
		$country["sj"] = "Svalbard & Jan Mayen";
		$country["sk"] = "Slovakia";
		$country["sl"] = "Sierra Leone";
		$country["sm"] = "San Marino";
		$country["sn"] = "Senegal";
		$country["so"] = "Somalia";
		$country["sr"] = "Suriname";
		$country["st"] = "Sao Tome & Principe";
		$country["su"] = "Soviet Union";
		$country["sv"] = "El Salvador";
		$country["sy"] = "Syria";
		$country["sz"] = "Swaziland";
		$country["tc"] = "Turks & Caicos Islands";
		$country["td"] = "Chad";
		$country["tf"] = "French Southern Territories";
		$country["tg"] = "Togo";
		$country["th"] = "Thailand";
		$country["tj"] = "Tadjikistan";
		$country["tk"] = "Tokelau";
		$country["tm"] = "Turkmenistan";
		$country["tn"] = "Tunisia";
		$country["to"] = "Tonga";
		$country["tp"] = "East Timor";
		$country["tr"] = "Turkey";
		$country["tt"] = "Trinidad & Tobago";
		$country["tv"] = "Tuvalu";
		$country["tw"] = "Taiwan";
		$country["tz"] = "Tanzania";
		$country["ua"] = "Ukraine";
		$country["ug"] = "Uganda";
		$country["uk"] = "United Kingdom";
		$country["um"] = "US Minor outlying Islands";
		$country["us"] = "United States";
		$country["uy"] = "Uruguay";
		$country["uz"] = "Uzbekistan";
		$country["va"] = "Vatican City State";
		$country["vc"] = "St Vincent & Grenadines";
		$country["ve"] = "Venezuela";
		$country["vg"] = "Virgin Islands (British)";
		$country["vi"] = "Virgin Islands (US)";
		$country["vn"] = "Vietnam";
		$country["vu"] = "Vanuatu";
		$country["wf"] = "Wallis & Futuna Islands";
		$country["ws"] = "Samoa";
		$country["ye"] = "Yemen";
		$country["yt"] = "Mayotte";
		$country["yu"] = "Yugoslavia";
		$country["za"] = "South Africa";
		$country["zm"] = "Zambia";
		$country["zr"] = "Zaire";
		$country["zw"] = "Zimbabwe";
		$scountry = $country[$dom];
	return $scountry;
}

?>