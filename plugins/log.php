<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/plugins/log.php															|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
IF(ADMIN == FALSE){
$self = $_SERVER['PHP_SELF'];

$ip = getip();

$date = date("Y-m-d");

if(!$sql -> db_Select("stat_counter", "*", "counter_url='".$self."' ")){
	// page not parsed before - create new entry ...
	$ip .= ".";
	$sql -> db_Insert("stat_counter", "CURRENT_DATE, '$self', '0', '0', '$ip', '0', '0' ");
}else if(!$sql -> db_Select("stat_counter", "*", "counter_url='".$self."' AND counter_date='$date' ")){
	// First visit of the day - update tables ...
	$sql -> db_Update("stat_counter", "counter_ip='', counter_date='$date', counter_unique=counter_unique+1, counter_total=counter_total+1, counter_today_total='1', counter_today_unique='1' WHERE counter_url='".$self."' ");
}
	// Not first visit - update tables
	list($counter_date, $counter_url, $counter_unique, $counter_total, $counter_ip, $counter_today_total, $counter_today_unique) = $sql-> db_Fetch();
	if(!ereg($ip, $counter_ip) && (!eregi("admin", $self))){
		// ip is not stored and not an admin page so unique visit - update counters
		
		$iplist = $counter_ip."-".$ip;
		$sql -> db_Update("stat_counter", "counter_ip='$iplist', counter_unique=counter_unique+1, counter_total=counter_total+1, counter_today_total=counter_today_total+1, counter_today_unique=counter_today_unique+1 WHERE counter_url='".$_SERVER['PHP_SELF']."' ");

		// log visitor stats

		$agent =$HTTP_USER_AGENT;

		if(eregi("MSIE",$agent)){
			$browser = "Internet Explorer";
		}else if(eregi("Opera",$agent)){
			$browser = "Opera";
		}else if(eregi("Konqueror",$agent)){
			$browser = "Konqueror";
		}else if(eregi("Lynx",$agent)){
			$browser = "Lynx";
		}else if(eregi("Mozilla/5",$agent)){
			$browser = "Netscape";
		}else if(eregi("(netscape6)/(6.[0-9]{1,3})",$agent,$ver)){
			$browser = "Netscape $ver[2]";
		}else if(eregi("(Mozilla)/([0-9]{1,2}.[0-9]{1,3})",$agent,$ver)){
			$browser = "Netscape $ver[2]";
		}else{
			$browser = "Unknown";
		}

		if(eregi("win32",$agent)){
			$os = "Windows";
		}else if(eregi("linux",$agent)){
			$os = "Linux";
		}else if(eregi("Win 9x 4.90",$agent)){
			$os = "Windows Me";
		}else if(eregi("Windows 2000",$agent) || eregi("(Windows NT)( ){0,1}(5.0)",$browser)){
			$os = "Windows 2000";
		}else if(eregi("(Windows NT)( ){0,1}(5.1)",$agent)){
			$os = "Windows XP";
		}else if( (eregi("(Win)([0-9]{2})",$agent,$ver)) || (eregi("(Windows) ([0-9]{2})",$agent,$ver)) ){
			$os = "Windows $ver[2]";
		}else if(eregi("(WinNT)([0-9]{1,2}.[0-9]{1,2}){0,1}",$agent,$ver)){
			$os = "Windows NT $ver[2]";
		}else if(eregi("(Windows NT)( ){0,1}([0-9]{1,2}.[0-9]{1,2}){0,1}",$agent,$ver)){
			$os = "Windows NT $ver[3]";
		}else if(eregi("OS2",$agent)){
			$os = "OS2";
		}else if(eregi("Mac",$agent)){
			$os = "Apple Macintosh";
		}else if(eregi("BeOS",$agent)){
			$os = "BeOS";
		}else if(eregi("Unix", $agent) || eregi("HP-ux", $agent) || eregi("X11", $agent)){
			$os = "Unix";
		}else if(eregi("OpenBSD",$agent)){
			$os = "OpenBSD";
		}else if(eregi("FreeBSD",$agent)){
			$os = "FreeBSD";
		}else{
			$os = "Unknown";
		}

		if($host = gethostbyaddr(getenv(REMOTE_ADDR))){
			$dom = substr($host, strrpos($host, ".")+1);

			$country["arpa"]="ARPANet"; 
			$country["com"]="Commercial Users"; 
			$country["edu"]="Education"; 
			$country["gov"]="Government"; 
			$country["int"]="Oganization established by an Iinternational Teaty"; 
			$country["mil"]="Military"; 
			$country["net"]="Network"; 
			$country["org"]="Organization"; 
			$country["ad"]="Andorra"; 
			$country["ae"]="United Arab Emirates"; 
			$country["af"]="Afghanistan"; 
			$country["ag"]="Antigua & Barbuda"; 
			$country["ai"]="Anguilla"; 
			$country["al"]="Albania"; 
			$country["am"]="Armenia"; 
			$country["an"]="Neterland Antilles"; 
			$country["ao"]="Angola"; 
			$country["aq"]="Antarctica"; 
			$country["ar"]="Argentina"; 
			$country["as"]="American Samoa"; 
			$country["at"]="Austria"; 
			$country["au"]="Australia"; 
			$country["aw"]="Aruba"; 
			$country["az"]="Azerbaijan"; 
			$country["ba"]="Bosnia-Herzegovina"; 
			$country["bb"]="Barbados"; 
			$country["bd"]="Bangladesh"; 
			$country["be"]="Belgium"; 
			$country["bf"]="Burkina Faso"; 
			$country["bg"]="Bulgaria"; 
			$country["bh"]="Bahrain"; 
			$country["bi"]="Burundi"; 
			$country["bj"]="Benin"; 
			$country["bm"]="Bermuda"; 
			$country["bn"]="Brunei Darussalam"; 
			$country["bo"]="Bolivia"; 
			$country["br"]="Brasil"; 
			$country["bs"]="Bahamas"; 
			$country["bt"]="Bhutan"; 
			$country["bv"]="Bouvet Island"; 
			$country["bw"]="Botswana"; 
			$country["by"]="Belarus"; 
			$country["bz"]="Belize"; 
			$country["ca"]="Canada"; 
			$country["cc"]="Cocos (Keeling) Islands"; 
			$country["cf"]="Central African Republic"; 
			$country["cg"]="Congo"; 
			$country["ch"]="Switzerland"; 
			$country["ci"]="Ivory Coast"; 
			$country["ck"]="Cook Islands"; 
			$country["cl"]="Chile"; 
			$country["cm"]="Cameroon"; 
			$country["cn"]="China"; 
			$country["co"]="Colombia"; 
			$country["cr"]="Costa Rica"; 
			$country["cs"]="Czechoslovakia"; 
			$country["cu"]="Cuba"; 
			$country["cv"]="Cape Verde"; 
			$country["cx"]="Christmas Island"; 
			$country["cy"]="Cyprus"; 
			$country["cz"]="Czech Republic"; 
			$country["de"]="Germany"; 
			$country["dj"]="Djibouti"; 
			$country["dk"]="Denmark"; 
			$country["dm"]="Dominica"; 
			$country["do"]="Dominican Republic"; 
			$country["dz"]="Algeria"; 
			$country["ec"]="Ecuador"; 
			$country["ee"]="Estonia"; 
			$country["eg"]="Egypt"; 
			$country["eh"]="Western Sahara"; 
			$country["er"]="Eritrea"; 
			$country["es"]="Spain"; 
			$country["et"]="Ethiopia"; 
			$country["fi"]="Finland"; 
			$country["fj"]="Fiji"; 
			$country["fk"]="Falkland Islands (Malvibas)"; 
			$country["fm"]="Micronesia"; 
			$country["fo"]="Faroe Islands"; 
			$country["fr"]="France"; 
			$country["fx"]="France (European Territory)"; 
			$country["ga"]="Gabon"; 
			$country["gb"]="Great Britain"; 
			$country["gd"]="Grenada"; 
			$country["ge"]="Georgia"; 
			$country["gf"]="Guyana (French)"; 
			$country["gh"]="Ghana"; 
			$country["gi"]="Gibralta"; 
			$country["gl"]="Greenland"; 
			$country["gm"]="Gambia"; 
			$country["gn"]="Guinea"; 
			$country["gp"]="Guadeloupe (French)"; 
			$country["gq"]="Equatorial Guinea"; 
			$country["gr"]="Greece"; 
			$country["gs"]="South Georgia & South Sandwich Islands"; 
			$country["gt"]="Guatemala"; 
			$country["gu"]="Guam (US)"; 
			$country["gw"]="Guinea Bissau"; 
			$country["gy"]="Guyana"; 
			$country["hk"]="Hong Kong"; 
			$country["hm"]="Heard & McDonald Islands"; 
			$country["hn"]="Honduras"; 
			$country["hr"]="Croatia"; 
			$country["ht"]="Haiti"; 
			$country["hu"]="Hungary"; 
			$country["id"]="Indonesia"; 
			$country["ie"]="Ireland"; 
			$country["il"]="Israel"; 
			$country["in"]="India"; 
			$country["io"]="British Indian Ocean Territories"; 
			$country["iq"]="Iraq"; 
			$country["ir"]="Iran"; 
			$country["is"]="Iceland"; 
			$country["it"]="Italy"; 
			$country["jm"]="Jamaica"; 
			$country["jo"]="Jordan"; 
			$country["jp"]="Japan"; 
			$country["ke"]="Kenya"; 
			$country["kg"]="Kyrgyz Republic"; 
			$country["kh"]="Cambodia"; 
			$country["ki"]="Kiribati"; 
			$country["km"]="Comoros"; 
			$country["kn"]="Saint Kitts Nevis Anguilla"; 
			$country["kp"]="Korea (North)"; 
			$country["kr"]="Korea (South)"; 
			$country["kw"]="Kuwait"; 
			$country["ky"]="Cayman Islands"; 
			$country["kz"]="Kazachstan"; 
			$country["la"]="Laos"; 
			$country["lb"]="Lebanon"; 
			$country["lc"]="Saint Lucia"; 
			$country["li"]="Liechtenstein"; 
			$country["lk"]="Sri Lanka"; 
			$country["lr"]="Liberia"; 
			$country["ls"]="Lesotho"; 
			$country["lt"]="Lithuania"; 
			$country["lu"]="Luxembourg"; 
			$country["lv"]="Latvia"; 
			$country["ly"]="Libya"; 
			$country["ma"]="Morocco"; 
			$country["mc"]="Monaco"; 
			$country["md"]="Moldova"; 
			$country["mg"]="Madagascar"; 
			$country["mh"]="Marshall Islands"; 
			$country["mk"]="Macedonia"; 
			$country["ml"]="Mali"; 
			$country["mm"]="Myanmar"; 
			$country["mn"]="Mongolia"; 
			$country["mo"]="Macau"; 
			$country["mp"]="Northern Mariana Islands"; 
			$country["mq"]="Martinique (French)"; 
			$country["mr"]="Mauretania"; 
			$country["ms"]="Montserrat"; 
			$country["mt"]="Malta"; 
			$country["mu"]="Mauritius"; 
			$country["mv"]="Maldives"; 
			$country["mw"]="Malawi"; 
			$country["mx"]="Mexico"; 
			$country["my"]="Malaysia"; 
			$country["mz"]="Mozambique"; 
			$country["na"]="Namibia"; 
			$country["nc"]="New Caledonia (French)"; 
			$country["ne"]="Niger"; 
			$country["nf"]="Norfolk Island"; 
			$country["ng"]="Nigeria"; 
			$country["ni"]="Nicaragua"; 
			$country["nl"]="Netherlands"; 
			$country["no"]="Norway"; 
			$country["np"]="Nepal"; 
			$country["nr"]="Nauru"; 
			$country["nt"]="Saudiarab. Irak)"; 
			$country["nu"]="Niue"; 
			$country["nz"]="New Zealand"; 
			$country["om"]="Oman"; 
			$country["pa"]="Panama"; 
			$country["pe"]="Peru"; 
			$country["pf"]="Polynesia (French)"; 
			$country["pg"]="Papua New Guinea"; 
			$country["ph"]="Philippines"; 
			$country["pk"]="Pakistan"; 
			$country["pl"]="Poland"; 
			$country["pm"]="Saint Pierre & Miquelon"; 
			$country["pn"]="Pitcairn"; 
			$country["pr"]="Puerto Rico (US)"; 
			$country["pt"]="Portugal"; 
			$country["pw"]="Palau"; 
			$country["py"]="Paraguay"; 
			$country["qa"]="Qatar"; 
			$country["re"]="Reunion (French)"; 
			$country["ro"]="Romania"; 
			$country["ru"]="Russian Federation"; 
			$country["rw"]="Rwanda"; 
			$country["sa"]="Saudi Arabia"; 
			$country["sb"]="Salomon Islands"; 
			$country["sc"]="Seychelles"; 
			$country["sd"]="Sudan"; 
			$country["se"]="Sweden"; 
			$country["sg"]="Singapore"; 
			$country["sh"]="Saint Helena"; 
			$country["si"]="Slovenia"; 
			$country["sj"]="Svalbard & Jan Mayen"; 
			$country["sk"]="Slovakia"; 
			$country["sl"]="Sierra Leone"; 
			$country["sm"]="San Marino"; 
			$country["sn"]="Senegal"; 
			$country["so"]="Somalia"; 
			$country["sr"]="Suriname"; 
			$country["st"]="Sao Tome & Principe"; 
			$country["su"]="Soviet Union"; 
			$country["sv"]="El Salvador"; 
			$country["sy"]="Syria"; 
			$country["sz"]="Swaziland"; 
			$country["tc"]="Turks & Caicos Islands"; 
			$country["td"]="Chad"; 
			$country["tf"]="French Southern Territories"; 
			$country["tg"]="Togo"; 
			$country["th"]="Thailand"; 
			$country["tj"]="Tadjikistan"; 
			$country["tk"]="Tokelau"; 
			$country["tm"]="Turkmenistan"; 
			$country["tn"]="Tunisia"; 
			$country["to"]="Tonga"; 
			$country["tp"]="East Timor"; 
			$country["tr"]="Turkey"; 
			$country["tt"]="Trinidad & Tobago"; 
			$country["tv"]="Tuvalu"; 
			$country["tw"]="Taiwan"; 
			$country["tz"]="Tanzania"; 
			$country["ua"]="Ukraine"; 
			$country["ug"]="Uganda"; 
			$country["uk"]="United Kingdom"; 
			$country["um"]="US Minor outlying Islands"; 
			$country["us"]="United States"; 
			$country["uy"]="Uruguay"; 
			$country["uz"]="Uzbekistan"; 
			$country["va"]="Vatican City State"; 
			$country["vc"]="St Vincent & Grenadines"; 
			$country["ve"]="Venezuela"; 
			$country["vg"]="Virgin Islands (British)"; 
			$country["vi"]="Virgin Islands (US)"; 
			$country["vn"]="Vietnam"; 
			$country["vu"]="Vanuatu"; 
			$country["wf"]="Wallis & Futuna Islands"; 
			$country["ws"]="Samoa"; 
			$country["ye"]="Yemen"; 
			$country["yt"]="Mayotte"; 
			$country["yu"]="Yugoslavia"; 
			$country["za"]="South Africa"; 
			$country["zm"]="Zambia"; 
			$country["zr"]="Zaire"; 
			$country["zw"]="Zimbabwe";
			$scountry = $country[$dom];
		}else{
			$scountry = "";
		}

		$referer = $_SERVER['HTTP_REFERER'];
		
		if($browser != ""){
			if($sql -> db_Count("stat_info", "(*)", " WHERE info_name='$browser' ")){
				$sql -> db_Update("stat_info", "info_count=info_count+1 WHERE info_name='$browser' ");
			}else{
				$sql -> db_Insert("stat_info", " '$browser', '1', '1' ");
			}
		}

		if($os != ""){
			if($sql -> db_Count("stat_info", "(*)", " WHERE info_name='$os' ")){
				$sql -> db_Update("stat_info", "info_count=info_count+1 WHERE info_name='$os' ");
			}else{
				$sql -> db_Insert("stat_info", " '$os', '1', '2' ");
			}
		}

		if($scountry != ""){
			if($sql -> db_Count("stat_info", "(*)", " WHERE info_name='$scountry' ")){
				$sql -> db_Update("stat_info", "info_count=info_count+1 WHERE info_name='$scountry' ");
			}else{
				$sql -> db_Insert("stat_info", " '$scountry', '1', '4' ");
			}
		}

		$siteurl = parse_url(SITEURL);
		if(!eregi($siteurl['host'], $referer)){
			if($referer != ""){
				if($pref['log_refertype'][1] == 0){
					// log domain only
					$rl = parse_url($referer);
					$ref =  eregi_replace("www.", "", $rl['host']);
					if($sql -> db_Select("stat_info", "*", "info_name='$ref' ")){
						$sql -> db_Update("stat_info", "info_count=info_count+1 WHERE info_name='$ref' ");
					}else{
						$sql -> db_Insert("stat_info", " '$ref', '1', '6' ");
					}
				}else{
					// Log whole URL
					if($sql -> db_Select("stat_info", "*", "info_name='$referer' ")){
						$sql -> db_Update("stat_info", "info_count=info_count+1 WHERE info_name='$referer' ");
					}else{
						$sql -> db_Insert("stat_info", " '$referer', '1', '6' ");
					}
				}
			}
		}

	}else{
		$sql -> db_Update("stat_counter", "counter_total=counter_total+1, counter_today_total=counter_today_total+1 WHERE counter_url='".$_SERVER['PHP_SELF']."' ");
	}
}
?>