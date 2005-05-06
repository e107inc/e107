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
$date = date("z.Y", time());

if(strstr($ref, "admin")) {
	$ref = FALSE;
}

$screenstats = $res."@".$colour;
$agent = $_SERVER['HTTP_USER_AGENT'];
$ip = getip();

if($ref && !strstr($ref, $_SERVER['HTTP_HOST']))
{
	if(preg_match("#http://(.*?)($|/)#is", $ref, $match))
	{
		$ref = $match[0];
	}
}

$infodata = time().chr(1).$ip.chr(1).$agent.chr(1).$colour.chr(1).$res.chr(1).$self.chr(1).$ref."\n";



$pageName = preg_replace("/(\?.*)|(\_.*)|(\.php)|(\s)|(\')|(\")|(eself)|(&nbsp;)/", "", basename ($self));
$pageName = str_replace("\\", "", $pageName);
$pageName = trim(chop($pageName));
if($pageName == "")
{
	$pageName = "index";
}

$logPfile = "logs/logp_".$date.".php";
require_once($logPfile);

$flag = FALSE;
if(array_key_exists($pageName, $pageInfo)) {
	$pageInfo[$pageName]['ttl'] ++;
} else {
	$pageInfo[$pageName] = array('url' => $self, 'ttl' => 1, 'unq' => 1);
	$flag = TRUE;
}

if(!strstr($ipAddresses, $ip))
{
	/* unique visit */

	if(!$flag) {
		$pageInfo[$pageName]['unq'] ++;
	}

	$siteUnique ++;
	$ipAddresses .= $ip.".";

	require_once("loginfo.php");

}

$siteTotal ++;

$varStart = chr(36);
$quote = chr(34);

$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n".
$varStart."ipAddresses = ".$quote.$ipAddresses.$quote.";\n".
$varStart."siteTotal = ".$quote.$siteTotal.$quote.";\n".
$varStart."siteUnique = ".$quote.$siteUnique.$quote.";\n";

$loop = FALSE;
$data .= $varStart."pageInfo = array(\n";
foreach($pageInfo as $info)
{
	$page = preg_replace("/(\?.*)|(\_.*)|(\.php)|(\s)|(\')|(\")|(eself)|(&nbsp;)/", "", basename ($info['url']));
	$page = str_replace("\\", "", $page);
	$info['url'] = preg_replace("/(\s)|(\')|(\")|(eself)|(&nbsp;)/", "", $info['url']);
	$info['url'] = str_replace("\\", "", $info['url']);
	$page = trim(chop($page));
	if($page && !strstr($page, "cache") && !strstr($page, "file:"))
	{
		if($loop){ $data .= ",\n"; }
		$data .= $quote.$page.$quote." => array('url' => '".$info['url']."', 'ttl' => ".$info['ttl'].", 'unq' => ".$info['unq'].")";
		$loop = 1;
	}
}

$data .= "\n);\n\n?".  chr(62);

if ($handle = fopen($logPfile, 'w')) { 
	fwrite($handle, $data);
}
fclose($handle);

function getip($mode=TRUE) {
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
	if($mode)
	{
		$ipa = explode(".", $ip);
		return sprintf('%02x%02x%02x%02x', $ipa[0], $ipa[1], $ipa[2], $ipa[3]);
	}
	else
	{
		return $ip;
	}
}

?>