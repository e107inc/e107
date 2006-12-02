<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|    	Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/log/log.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:35:27 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

define("log_INIT", TRUE);
$colour = strip_tags((isset($_REQUEST['color']) ? $_REQUEST['color'] : ''));
$res = strip_tags((isset($_REQUEST['res']) ? $_REQUEST['res'] : ''));
$self = strip_tags((isset($_REQUEST['eself']) ? $_REQUEST['eself'] : ''));
$ref = addslashes(strip_tags((isset($_REQUEST['referer']) ? $_REQUEST['referer'] : '')));
$date = date("z.Y", time());

if(strstr($ref, "admin")) {
	$ref = FALSE;
}

$screenstats = $res."@".$colour;
$agent = $_SERVER['HTTP_USER_AGENT'];
$ip = getip();

$oldref = $ref; // backup for search string being stripped of for referer
if($ref && !strstr($ref, $_SERVER['HTTP_HOST'])) {
	if(preg_match("#http://(.*?)($|/)#is", $ref, $match)) {
		$ref = $match[0];
	}
}

$infodata = time().chr(1).$ip.chr(1).$agent.chr(1).$colour.chr(1).$res.chr(1).$self.chr(1).$ref."\n";

$pageDisallow = "cache|file|eself|admin";
$tagRemove = "(\\\)|(\s)|(\')|(\")|(eself)|(&nbsp;)|(\.php)|(\.html)";
$tagRemove2 = "(\\\)|(\s)|(\')|(\")|(eself)|(&nbsp;)";

preg_match("#/(.*?)(\?|$)#si", $self, $match);
$match[1] = isset($match[1]) ? $match[1] : '';
$pageName = substr($match[1], (strrpos($match[1], "/")+1));
$PN = $pageName;
$pageName = preg_replace("/".$tagRemove."/si", "", $pageName);
if($pageName == "") $pageName = "index";
if(preg_match("/".$pageDisallow."/i", $pageName)) return;


$logPfile = "logs/logp_".$date.".php";
require_once($logPfile);

$flag = FALSE;
if(array_key_exists($pageName, $pageInfo)) {
	$pageInfo[$pageName]['ttl'] ++;
} else {
	$url = preg_replace("/".$tagRemove2."/si", "", $self);
	if(preg_match("/".$pageDisallow."/i", $url)) return;
	$pageInfo[$pageName] = array('url' => $url, 'ttl' => 1, 'unq' => 1);
	$flag = TRUE;
}

if(!strstr($ipAddresses, $ip)) {
	/* unique visit */
	if(!$flag) {
		$pageInfo[$pageName]['unq'] ++;
	}
	$siteUnique ++;
	$ipAddresses .= $ip.".";
	require_once("loginfo.php");
}

$siteTotal ++;
$info_data = var_export($pageInfo, true);
$date_stamp = date("z:Y", time());

$data = "<?php

/* e107 website system: Log file: {$date_stamp} */

\$ipAddresses = '{$ipAddresses}';
\$siteTotal = '{$siteTotal}';
\$siteUnique = '{$siteUnique}';

\$pageInfo = {$info_data};

?>";

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
	if($mode) {
		$ipa = explode(".", $ip);
		return sprintf('%02x%02x%02x%02x', $ipa[0], $ipa[1], $ipa[2], $ipa[3]);
	} else {
		return $ip;
	}
}

?>