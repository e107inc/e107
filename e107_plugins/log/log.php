<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/plugins/log2.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
$colour = $_REQUEST['color'];
$res = $_REQUEST['res'];
$self = $_REQUEST['eself'];
$ref = $_REQUEST['referer'];

$self = substr(strrchr(eregi_replace("\?.*", "", $self), "/"), 1);
if($self == "/"){ $self = "index.php"; }
$screenstats = $res." @ ".$colour;
@include("../../e107_config.php");
define("MPREFIX", $mySQLprefix);
require_once(e_HANDLER."mysql_class.php");
$sql = new db;
$sql -> db_SetErrorReporting(FALSE);
$sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
$sql -> db_Select("core", "*", "e107_name='pref' ");
$row = $sql -> db_Fetch();
$tmp = stripslashes($row['e107_value']);
$pref=unserialize($tmp);

$siteurl = $pref['siteurl'];

if($pref['log_activate']){

	$agent = $_SERVER['HTTP_USER_AGENT'];
	$browser = getbrowser();
	$os = getos();
	$country = getcountry();
	$ip = getip();
	$gip = $ip;

	$date = date("Y-m-d");

	if(!$sql -> db_Select("stat_counter", "*", "counter_url='".$self."' AND counter_date='$date' ") && $self){
		// page not parsed before - create new entry ...
		$ip .= ".";
		$sql -> db_Insert("stat_counter", "CURRENT_DATE, '$self', '0', '0', '$ip' ");
		$yesterday = date("Y-m-d", (time()-86400));
		$sql -> db_Update("stat_counter", "counter_ip='' WHERE counter_date='$yesterday' ");	// clear ip stats
	}else{
		$row = $sql-> db_Fetch(); extract($row);
		$sql -> db_Update("stat_counter", "counter_total=counter_total+1 WHERE counter_url='".$self."' AND counter_date='$date' ");
	}
	// update tables

		
	if(!ereg($ip, $counter_ip) && (!eregi("admin", $self))){
			// ip is not stored and not an admin page so unique visit - update counters
			
		$iplist = $counter_ip."-".$ip;
		$sql -> db_Update("stat_counter", "counter_ip='$iplist', counter_unique=counter_unique+1, counter_total=counter_total+1 WHERE counter_url='".$self."' ");
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
		if($country != ""){
			if($sql -> db_Count("stat_info", "(*)", " WHERE info_name='$country' ")){
				$sql -> db_Update("stat_info", "info_count=info_count+1 WHERE info_name='$country' ");
			}else{
				$sql -> db_Insert("stat_info", " '$country', '1', '4' ");
			}
		}
		if($screenstats != ""){
			if(trim(chop($screenstats)) != "@" && trim(chop($screenstats)) != "\" res \" @ \" colord \""){
				if($sql -> db_Count("stat_info", "(*)", " WHERE info_name='$screenstats' ")){
					$sql -> db_Update("stat_info", "info_count=info_count+1 WHERE info_name='$screenstats' ");
				}else{
					$sql -> db_Insert("stat_info", " '$screenstats', '1', '5' ");
				}
			}
		}
		$referer = $ref;
		if($referer != "" && $ref != "\" ref \"" && !eregi("blocked", $referer)){
			$siteurl = parse_url($pref['siteurl']);
			if(!strstr($referer, $siteurl['host']) && !strstr($referer, "localhost")){
				if($pref['log_refertype'] == 0){
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
// last unique visitors -------------------------------------------------------------------------------------------------------------------------------------------------------
		if($sql -> db_Count("stat_last") >= $pref['log_lvcount']){
			$sql -> db_Select("stat_last", "*",  "ORDER BY stat_last_date ASC", "no_where");
			$row = $sql -> db_Fetch();
			$sql -> db_Delete("stat_last", "stat_last_date='".$row['stat_last_date']."' ");
		}

		$sip = substr($ip, 0, (strlen($ip)-2))."x";
		if(!$country){
			$country = "(unknown)";
		}

		$con = new convert;
		$datestamp = $con -> convert_date(time(), "long");
		$laststr = "IP: ".$sip." using ".$browser." under ".$os ." at ".$res." x ".$colour." from ".$country.".";

		if(!$sql -> db_Select("stat_last", "*", "stat_last_info='$laststr' ")){
			$sql -> db_Insert("stat_last", " '".time()."', '$laststr' ");
		}
	}
}

// end last visitors -----------------------------------------------------------------------------------------------------------------------------------------------------------
header("Content-type: text/css");
echo ".DUMMY {color: green;}";
// functions -------------------------------------------------------------------------------------------------------------------------------------------------------------------
function getbrowser(){
	$agent = $_SERVER['HTTP_USER_AGENT'];
	if(eregi("Netcaptor", $agent)){$browser = "Netcaptor";
	}else if(eregi("(opera) ([0-9]{1,2}.[0-9]{1,3}){0,1}", $agent, $ver) || eregi("(opera/)([0-9]{1,2}.[0-9]{1,3}){0,1}", $agent, $ver)){ $browser = "Opera $ver[2]";
	}else if(eregi("(konqueror)/([0-9]{1,2}.[0-9]{1,3})", $agent, $ver)){ $browser = "Konqueror $ver[2]";
	}else if(eregi("(lynx)/([0-9]{1,2}.[0-9]{1,2}.[0-9]{1,2})", $agent, $ver)){ $browser = "Lynx $ver[2]";
	}else if(eregi("(msie) ([0-9]{1,2}.[0-9]{1,3})", $agent, $ver)){ $browser = "Internet Explorer $ver[2]";
	}else if(eregi("Links", $agent)){ $browser = "Lynx";
	}else if(eregi("(Firebird/)([0-9]{1,2}.[0-9]{1,3}){0,1}", $agent, $ver)){ $browser = "Firebird $ver[2]";
	}else if(eregi("Mozilla/5",$agent)){$browser = "Netscape 5";
	}else if(eregi("Gecko", $agent)){ $browser = "Mozilla";
	}else if(eregi("Safari",$agent)){ $browser = "OS-X Safari";
	}else if(eregi("(netscape6)/(6.[0-9]{1,3})", $agent, $ver)){ $browser = "Netscape $ver[2]";
	}else if(eregi("(Mozilla)/([0-9]{1,2}.[0-9]{1,3})", $agent, $ver)){ $browser = "Netscape $ver[2]";
	}else if(eregi("Galeon", $agent)){ $browser = "Galeon";
	}else if(eregi("(lynx)/([0-9]{1,2}.[0-9]{1,2}.[0-9]{1,2})", $agent, $ver) ){$browser = "Lynx $ver[2]";
	}else if(eregi("Avant Browser", $agent)){ $browser = "Avant";
	}else if(eregi("(omniweb/)([0-9]{1,2}.[0-9]{1,3})", $agent, $ver) ){$browser = "OmniWeb $ver[2]";
	}else if(eregi("ZyBorg|WebCrawler|Slurp|Googlebot|MuscatFerret|ia_archiver", $agent)){ $browser = "Web indexing robot";
	}else if(eregi("(webtv/)([0-9]{1,2}.[0-9]{1,3})", $agent, $ver) ){$browser = "WebTV $ver[2]";
	}else{$browser = "Unknown";}
	return $browser;
}

function getos(){
	$agent = $_SERVER['HTTP_USER_AGENT'];
	if(strstr($agent,'Win')){
		if(strstr($agent,'NT 5.0') || strstr($agent,'NT5.0') || strstr($agent,'Windows 2000')){ $os = "Windows 2000";
		}else if(strstr($agent,'NT 5.1') || strstr($agent,'NT5.1') || strstr($agent,'Windows XP')){$os = "Windows XP";
		}else if(strstr($agent,'Win98') || strstr($agent,'Windows 98')){$os="Windows 98";
		}else if(strstr($agent,'NT')) {$os='Windows NT';
		}else if(strstr($agent,'Win95') || strstr($agent,'Windows 95')){$os='Windows 95';
		}else if(strstr($agent,'Win 9x')) {$os='Windows 95';
		}else if(strstr($agent,'WinME') || strstr($agent,'Windows ME')){$os='Windows ME';
		}else{$os='Windows (version unspecified)';}
	
	}else if (strstr($agent,'Mac')){$os='Apple Macintosh';
	}else if (strstr($agent,'Linux')){$os='Linux';
	}else if (strstr($agent,'BeOS')){$os='BeOS';
	}else if (strstr($agent,'Unix') || strstr($agent, "HP-ux") || strstr($agent, "X11")){$os='Unix';
	}else if (strstr($agent,'SunOS')){$os='SunOS';
	}else if (strstr($agent,'FreeBSD')){$os='FreeBSD';
	}else if (strstr($agent,'OpenBSD')){$os='OpenBSD';
	}else if (strstr($agent,'IRIX')){$os='IRIX';
	}else if (strstr($agent,'spider') || strstr($agent,'bot') || strstr($agent,'http') || strstr($agent,'Scooter') || strstr($agent,'WebCopier')){$os='Spiders';
	}else{$os='Unspecified';
	}
	return $os;
}

function getcountry(){
	if($host = gethostbyaddr(getenv(REMOTE_ADDR))){
			$dom = strtolower(substr($host, strrpos($host, ".")+1));

			$country["arpa"]="ARPANet"; 
			$country["com"]="Commercial Users"; 
			$country["edu"]="Education"; 
			$country["gov"]="Government"; 
			$country["int"]="Oganization established by an International Treaty"; 
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
			$country["an"]="Netherland Antilles"; 
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
		return $scountry;
}

function getip(){
	if(getenv('HTTP_X_FORWARDED_FOR')){
		$ip = $_SERVER['REMOTE_ADDR'];
		if(preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", getenv('HTTP_X_FORWARDED_FOR'), $ip3)){
			$ip2 = array('/^0\./', '/^127\.0\.0\.1/', '/^192\.168\..*/', '/^172\.16\..*/', '/^10..*/', '/^224..*/', '/^240..*/');
			$ip = preg_replace($ip2, $ip, $ip3[1]);
		}
	}else{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	if($ip == ""){ $ip = "x.x.x.x"; }
	return $ip;
}
class convert{
	function convert_date($datestamp, $mode="long"){
		global $pref;
		$datestamp += (TIMEOFFSET*3600);
		if($mode == "long"){
			return strftime($pref['longdate'], $datestamp);
		}else if($mode == "short"){
			return strftime($pref['shortdate'], $datestamp);
		}else{
			return strftime($pref['forumdate'], $datestamp);
		}
	}
}
?>