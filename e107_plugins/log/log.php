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

$colour = strip_tags($_REQUEST['color']);
$res = strip_tags($_REQUEST['res']);
$self = strip_tags($_REQUEST['eself']);
$ref = strip_tags($_REQUEST['referer']);

if(strstr($ref, "admin")) {
	$ref = FALSE;
}
$screenstats = $res."@".$colour;
$agent = $_SERVER['HTTP_USER_AGENT'];

$ip = getip();
$browser = getBrowser($agent);
$os = getOs($agent);

$pageName = preg_replace("/(\?.*)|(\_.*)|(\.php)/", "", basename ($self));

$logfile = "logs/log_".date("z.Y", time()).".php";
require_once($logfile);


$flag = FALSE;
if(array_key_exists($pageName, $pageInfo)) {
	$pageInfo[$pageName]['ttl'] ++;
	$pageInfo[$pageName]['ttlv'] ++;
} else {
	$pageInfo[$pageName] = array('url' => $self, 'ttl' => 1, 'unq' => 1, 'ttlv' => 1, 'unqv' => 1);
	$flag = TRUE;
}

if(!strstr($ipAddresses, $ip)) {
	if(!$flag) {
		$pageInfo[$pageName]['unq'] ++;
		$pageInfo[$pageName]['unqv'] ++;
	}

	if($screenstats && $screenstats != "@") {
		if(array_key_exists($screenstats, $screenInfo)) {
			$screenInfo[$screenstats] ++;
		} else {
			$screenInfo[$screenstats] = 1;
		}
	}

	if(array_key_exists($browser, $browserInfo)) {
		$browserInfo[$browser] ++;
	} else {
		$browserInfo[$browser] = 1;
	}


	if(array_key_exists($os, $osInfo)) {
		$osInfo[$os] ++;
	} else {
		$osInfo[$os] =1;
	}

	/* referer data ... */
	if($ref && !strstr($ref, $_SERVER['HTTP_HOST'])) {
		$refererData .= $ref.$exp;
		if(preg_match("#http://(.*?)($|/)#is", $ref, $match)) {
			$refdom = $match[0];
			if(array_key_exists($refdom, $refInfo)) {
				$refInfo[$refdom]['ttl'] ++;
			} else {
				$refInfo[$refdom] = array('url' => $ref, 'ttl' => 1);
			}
		}
	}

	/* is the referal from Google? If so get search string ... */
	if(preg_match("#q=(.*?)($|&)#is", $ref, $match)) {
		$schstr = trim(chop($match[1]));
		if(array_key_exists($schstr, $searchInfo)) {
			$searchInfo[$schstr] ++;
		} else {
			$searchInfo[$schstr] = 1;
		}
	}


	$siteUnique ++;
	$ipAddresses .= $ip.chr(1);

	if ($tmp = gethostbyaddr(getenv(REMOTE_ADDR))) {
		$host = strtolower(substr($tmp, strrpos($tmp, ".")+1));
		if(array_key_exists($host, $domainInfo)) {
			$domainInfo[$host] ++;
		} else {
			$domainInfo[$host] =1;
		}
	}
}

$siteTotal ++;

$varStart = chr(36);
$quote = chr(34);

$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n".
$varStart."refererData = ".$quote.$refererData.$quote.";\n".
$varStart."ipAddresses = ".$quote.$ipAddresses.$quote.";\n".
$varStart."siteTotal = ".$quote.$siteTotal.$quote.";\n".
$varStart."siteUnique = ".$quote.$siteUnique.$quote.";\n";

$loop = FALSE;
$data .= $varStart."domainInfo = array(\n";
foreach($domainInfo as $key => $info) {
	if($loop){ $data .= ",\n"; }
	$data .= $quote.$key.$quote." => $info";
	$loop = 1;
}
$data .= "\n);\n".

$loop = FALSE;
$data .= $varStart."screenInfo = array(\n";
foreach($screenInfo as $key => $info) {
	if($loop){ $data .= ",\n"; }
	$data .= $quote.$key.$quote." => $info";
	$loop = 1;
}
$data .= "\n);\n".


$loop = FALSE;
$data .= $varStart."browserInfo = array(\n";
foreach($browserInfo as $key => $info) {
	if($loop){ $data .= ",\n"; }
	$data .= $quote.$key.$quote." => $info";
	$loop = 1;
}
$data .= "\n);\n".

$loop = FALSE;
$data .= $varStart."osInfo = array(\n";
foreach($osInfo as $key => $info) {
	if($loop){ $data .= ",\n"; }
	$data .= $quote.$key.$quote." => $info";
	$loop = 1;
}
$data .= "\n);\n".


$loop = FALSE;
$data .= $varStart."refInfo = array(\n";
foreach($refInfo as $key => $info) {
	if($loop){ $data .= ",\n"; }
	$data .= $quote.$key.$quote." => array('url' => '".$info['url']."', 'ttl' => ".$info['ttl'].")";
	$loop = 1;
}
$data .= "\n);\n".

$loop = FALSE;
$data .= $varStart."searchInfo = array(\n";
foreach($searchInfo as $key => $info) {
	if($loop){ $data .= ",\n"; }
	$data .= $quote.$key.$quote." => $info";
	$loop = 1;
}
$data .= "\n);\n".


$loop = FALSE;
$data .= $varStart."pageInfo = array(\n";
foreach($pageInfo as $info) {
	$page = preg_replace("/(\?.*)|(\_.*)|(\.php)/", "", basename ($info['url']));
	if($loop){ $data .= ",\n"; }
	$data .= $quote.$page.$quote." => array('url' => '".$info['url']."', 'ttl' => ".$info['ttl'].", 'unq' => ".$info['unq'].", 'ttlv' => ".$info['ttlv'].", 'unqv' => ".$info['unqv'].")";
	$loop = 1;
}

$data .= "\n);\n\n?".  chr(62);

if ($handle = fopen($logfile, 'w')) { 
	fwrite($handle, $data);
}
fclose($handle);

function getip() {
	if (getenv('HTTP_X_FORWARDED_FOR')) {
		$ip = $_SERVER['REMOTE_ADDR'];
		if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", getenv('HTTP_X_FORWARDED_FOR'), $ip3)) {
			$ip2 = array('/^0\./', '/^127\.0\.0\.1/', '/^192\.168\..*/', '/^172\.16\..*/', '/^10..*/', '/^224..*/', '/^240..*/');
			$ip = preg_replace($ip2, $ip, $ip3[1]);
		}
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	if ($ip == "") {
		$ip = "x.x.x.x";
	}
	return $ip;
}

function getBrowser($agent) {
	$browsers = array(
		"netcaptor" => array('name' => 'Netcaptor', 'rule' => 'netcaptor[ /]([0-9.]{1,10})'), 
		"explorer" => array('name' => 'Internet Explorer', 'rule' => '\(compatible; MSIE[ /]([0-9.]{1,10})'), 
		"firefox" => array('name' => 'Firefox', 'rule' => 'Firefox/([0-9.+]{1,10})'), 
		"opera" => array('name' => 'Opera', 'rule' => 'opera[ /]([0-9.]{1,10})'),
		"aol" => array('name' => 'AOL', 'rule' => 'aol[ /\-]([0-9.]{1,10})'), 
		"aol2"=> array('name' => 'AOL', 'rule' => 'aol[ /\-]?browser'), 
		"netscape" => array('name' => 'Netscape', 'rule' => 'netscape[0-9]?/([0-9.]{1,10})'),
		"netscape2" => array('name' => 'Netscape', 'rule' => '^mozilla/([0-4]\.[0-9.]{1,10})'),
		"mozilla" => array('name' => 'Mozilla', 'rule' => '^mozilla/[5-9]\.[0-9.]{1,10}.+rv:([0-9a-z.+]{1,10})'),
		"mozilla2" => array('name' => 'Mozilla', 'rule' => '^mozilla/([5-9]\.[0-9a-z.]{1,10})'),
		"mosaic" => array('name' => 'Mosaic', 'rule' => 'mosaic[ /]([0-9.]{1,10})'), 
		"k-meleon" => array('name' => 'K-Meleon', 'rule' => 'K-Meleon[ /]([0-9.]{1,10})'), 
		"konqueror" => array('name' => 'Konqueror', 'rule' => 'konqueror/([0-9.]{1,10})'), 
		"avantbrowser" => array('name' => 'Avant Browser', 'rule' => 'Avant[ ]?Browser'), 
		"avantgo" => array('name' => 'AvantGo', 'rule' => 'AvantGo[ /]([0-9.]{1,10})'), 
		"proxomitron" => array('name' => 'Proxomitron', 'rule' => 'Space[ ]?Bison/[0-9.]{1,10}'), 
		"safari" => array('name' => 'Safari', 'rule' => 'safari/([0-9.]{1,10})'), 
		"lynx" => array('name' => 'Lynx', 'rule' => 'lynx/([0-9a-z.]{1,10})'), 
		"links" => array('name' => 'Links', 'rule' => 'Links[ /]\(([0-9.]{1,10})'), 
		"galeon" => array('name' => 'Galeon', 'rule' => 'galeon/([0-9.]{1,10})')
	);
	$browser = "";
	foreach($browsers as $info) {
		if (eregi($info['rule'], $agent, $results)) {
			return ($info['name']." v".$results[1]);
		}
	}
	return ("Unknown");
}

function getOs($agent) {
	$os = array(
		"windows2003" => array('name' => 'Windows 2003', 'rule' => 'wi(n|ndows)[ \-]?(2003|nt[ /]?5\.2)'), 
		"windowsxp" => array('name' => 'Windows XP', 'rule' => 'Windows XP'), 
		"windowsxp2" => array('name' => 'Windows XP', 'rule' => 'wi(n|ndows)[ \-]?nt[ /]?5\.1'), 
		"windows2k" => array('name' => 'Windows 2000', 'rule' => 'wi(n|ndows)[ \-]?(2000|nt[ /]?5\.0)'), 
		"windows95" => array('name' => 'Windows 95', 'rule' => 'wi(n|ndows)[ \-]?95'), 
		"windowsce" => array('name' => 'Windows CE', 'rule' => 'wi(n|ndows)[ \-]?ce'), 
		"windowsme" => array('name' => 'Windows ME', 'rule' => 'win 9x 4\.90'), 
		"windowsme2" => array('name' => 'Windows ME', 'rule' => 'wi(n|ndows)[ \-]?me'), 
		"windowsnt" => array('name' => 'Windows NT', 'rule' => 'wi(n|ndows)[ \-]?nt[ /]?([0-4][0-9.]{1,10})'), 
		"windowsnt2" => array('name' => 'Windows NT', 'rule' => 'wi(n|ndows)[ \-]?nt'), 
		"windows98" => array('name' => 'Windows 98', 'rule' => 'wi(n|ndows)[ \-]?98'), 
		"windows" => array('name' => 'Windows', 'rule' => 'wi(n|n32|ndows)'), 
		"linux" => array('name' => 'Linux', 'rule' => 'mdk for ([0-9.]{1,10})'), 
		"linux2" => array('name' => 'Linux', 'rule' => 'linux[ /\-]([a-z0-9.]{1,10})'), 
		"linux3" => array('name' => 'Linux', 'rule' => 'linux'), 
		"macosx" => array('name' => 'MacOS X', 'rule' => 'Mac[ ]?OS[ ]?X'), 
		"macppc" => array('name' => 'MacOS PPC', 'rule' => 'Mac(_Power|intosh.+P)PC'), 
		"mac" => array('name' => 'MacOS', 'rule' => 'mac[^hk]'), 
		"amiga" => array('name' => 'Amiga', 'rule' => 'Amiga[ ]?OS[ /]([0-9.]{1,10})'), 
		"beos" => array('name' => 'BeOS', 'rule' => 'beos[ a-z]*([0-9.]{1,10})'), 
		"freebsd" => array('name' => 'FreeBSD', 'rule' => 'free[ \-]?bsd[ /]([a-z0-9.]{1,10})'), 
		"freebsd2" => array('name' => 'FreeBSD', 'rule' => 'free[ \-]?bsd'), 
		"irix" => array('name' => 'Irix', 'rule' => 'irix[0-9]*[ /]([0-9.]{1,10})'), 
		"netbsd" => array('name' => 'NetBSD', 'rule' => 'net[ \-]?bsd[ /]([a-z0-9.]{1,10})'), 
		"netbsd2" => array('name' => 'NetBSD', 'rule' => 'net[ \-]?bsd'), 
		"os2" => array('name' => 'OS/2 Warp', 'rule' => 'warp[ /]?([0-9.]{1,10})'), 
		"os22" => array('name' => 'OS/2 Warp', 'rule' => 'os[ /]?2'), 
		"openbsd" => array('name' => 'OpenBSD', 'rule' => 'open[ \-]?bsd[ /]([a-z0-9.]{1,10})'), 
		"openbsd2" => array('name' => 'OpenBSD', 'rule' => 'open[ \-]?bsd'), 
		"palm" => array('name' => 'PalmOS', 'rule' => 'Palm[ \-]?(Source|OS)[ /]?([0-9.]{1,10})'), 
		"palm2" => array('name' => 'PalmOS', 'rule' => 'Palm[ \-]?(Source|OS)')
	);
	foreach($os as $key => $info) {
		if (eregi($info['rule'], $agent, $results)) {
			if(strstr($key, "win")) {
				return ($info['name']);
			} else {
				return ($info['name']." ".$results[1]);
			}
		}
	}
	return ("Unspecified");
}

?>