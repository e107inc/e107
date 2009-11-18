<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - User classes
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/log/log.php,v $
 * $Revision: 1.8 $
 * $Date: 2009-11-18 01:05:47 $
 * $Author: e107coders $
 *
*/

/* File to log page accesses - called with
	e_PLUGIN_ABS."log/log.php?base64encode(referer=' + ref + '&color=' + colord + '&eself=' + eself + '&res=' + res + '\">' );)";
		referer= ref
		color= colord
		eself= eself 
		res= res
		err_direct - optional error flag
		err_referer - referrer if came via error page

// Normally the file is 'silent' - if any errors occur, not sure where they'll appear - (file type now text/html instead of text/css)
*/
define('log_INIT', TRUE);

$logVals = urldecode(base64_decode($_SERVER['QUERY_STRING']));
parse_str($logVals, $vals);

echo "\n";		// This is harmless data which seems to avoid intermittent problems.

//$logfp = fopen('logs/rcvstring.txt', 'a+'); fwrite($logfp, $logVals."\n"); fclose($logfp);
//$logfp = fopen('logs/rcvstring.txt', 'a+'); fwrite($logfp, print_r($vals, TRUE)."\n"); fclose($logfp);

$colour = strip_tags((isset($vals['colour']) ? $vals['colour'] : ''));
$res = strip_tags((isset($vals['res']) ? $vals['res'] : ''));
$self = strip_tags((isset($vals['eself']) ? $vals['eself'] : ''));
$ref = addslashes(strip_tags((isset($vals['referer']) ? $vals['referer'] : '')));
$date = date("z.Y", time());
$logPfile = "logs/logp_".$date.".php";

//$logString = "Colour: {$colour}  Res: {$res}  Self: {$self} Referrer: {$ref} ErrCode: {$vals['err_direct']}\n";
//$logfp = fopen('logs/rcvstring.txt', 'a+'); fwrite($logfp, $logString); fclose($logfp);


// vet resolution and colour depth some more - avoid dud values
if ($res && preg_match("#.*?((\d+)\w+?(\d+))#", $res, $match))
{
	$res = $match[2].'x'.$match[3];
}
else
{
	$res = '??';			// Can't decode resolution
}

if ($colour && preg_match("#.*?(\d+)#",$colour,$match))
{
	$colour = intval($match[1]);
}
else
{
	$colour='??';
}


if ($err_code = strip_tags((isset($vals['err_direct']) ? $vals['err_direct'] : '')))
{
	$ref = addslashes(strip_tags(isset($vals['err_referer']) ? $vals['err_referer'] : ''));
// Uncomment the next two lines to create a separate CSV format log of invalid accesses - error code, entered URL, referrer
//	$log_string = $err_code.",".$self.",".$ref;
//  $logfp = fopen("logs/errpages.csv", 'a+'); fwrite($logfp, $log_string."\n\r"); fclose($logfp);
	$err_code .= ':';
}

if(strstr($ref, 'admin')) 
{
	$ref = FALSE;
}

$screenstats = $res.'@'.$colour;
$agent = $_SERVER['HTTP_USER_AGENT'];
$ip = getip();

$oldref = $ref; // backup for search string being stripped off for referer
if($ref && !strstr($ref, $_SERVER['HTTP_HOST'])) 
{
	if(preg_match("#http://(.*?)($|/)#is", $ref, $match)) 
	{
		$ref = $match[0];
	}
}

$pageDisallow = "cache|file|eself|admin";
$tagRemove = "(\\\)|(\s)|(\')|(\")|(eself)|(&nbsp;)|(\.php)|(\.html)";
$tagRemove2 = "(\\\)|(\s)|(\')|(\")|(eself)|(&nbsp;)";

preg_match("#/(.*?)(\?|$)#si", $self, $match);
$match[1] = isset($match[1]) ? $match[1] : '';
$pageName = substr($match[1], (strrpos($match[1], "/")+1));
$PN = $pageName;
$pageName = preg_replace("/".$tagRemove."/si", "", $pageName);
if($pageName == "") $pageName = "index";

$pageName = $err_code.$pageName;			// Add the error code at the beginning, so its treated uniquely

if(preg_match("/".$pageDisallow."/i", $pageName)) return;


$p_handle = fopen($logPfile, 'r+');
if($p_handle && flock( $p_handle, LOCK_EX ) ) 
{
	$log_file_contents = '';
	while (!feof($p_handle))
	{  // Assemble a string of data
		$log_file_contents.= fgets($p_handle,1000);
	}
	$log_file_contents = str_replace(array('<'.'?php','?'.'>'),'',$log_file_contents);
	if (eval($log_file_contents) === FALSE) echo "error in log file contents<br /><br /><br /><br />";
}
else
{
	echo "Couldn't log data<br /><br /><br /><br />";
	exit;
}


$flag = FALSE;
if(array_key_exists($pageName, $pageInfo)) 
{  // Existing page - just increment stats
	$pageInfo[$pageName]['ttl'] ++;
}
else 
{  // First access of page
	$url = preg_replace("/".$tagRemove2."/si", "", $self);
	if(preg_match("/".$pageDisallow."/i", $url)) return;
	$pageInfo[$pageName] = array('url' => $url, 'ttl' => 1, 'unq' => 1);
	$flag = TRUE;
}

if(!strstr($ipAddresses, $ip)) 
{	/* unique visit */
	if(!$flag) 
	{
		$pageInfo[$pageName]['unq'] ++;
	}
	$siteUnique ++;
	$ipAddresses .= $ip.".";		// IP address is stored as hex string
	require_once("loginfo.php");
}


$siteTotal ++;
$info_data = var_export($pageInfo, true);
//$date_stamp = date("z:Y", time());			// Same as '$date' variable

$data = "<?php

/* e107 website system: Log file: {$date} */

\$ipAddresses = '{$ipAddresses}';
\$siteTotal = '{$siteTotal}';
\$siteUnique = '{$siteUnique}';

\$pageInfo = {$info_data};

?>";

if ($p_handle)
{
  ftruncate( $p_handle, 0 );
  fseek( $p_handle, 0 );
  fwrite($p_handle, $data);
  fclose($p_handle);
}


// Get current IP address - return as a hex-encoded string
function getip() 
{
	$ip = $_SERVER['REMOTE_ADDR'];
	if (getenv('HTTP_X_FORWARDED_FOR')) 
	{
		if (preg_match("#^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})#", getenv('HTTP_X_FORWARDED_FOR'), $ip3)) 
		{  
			$ip2 = array('#^0\..*#', 
				   '#^127\..*#', 							// Local loopbacks
				   '#^192\.168\..*#', 						// RFC1918 - Private Network
				   '#^172\.(?:1[6789]|2\d|3[01])\..*#', 	// RFC1918 - Private network
				   '#^10\..*#', 							// RFC1918 - Private Network
				   '#^169\.254\..*#', 						// RFC3330 - Link-local, auto-DHCP 
				   '#^2(?:2[456789]|[345][0-9])\..*#'		// Single check for Class D and Class E
				   );
			$ip = preg_replace($ip2, $ip, $ip3[1]);
		}
	}
	if ($ip == "") 
	{
		$ip = "x.x.x.x";
	}
	if (strpos($ip, ':') === FALSE)
	{	// Its an IPV4 address - return it as 32-character packed hex string
		$ipa = explode(".", $ip);
		return str_repeat('0000',5).'ffff'.sprintf('%02x%02x%02x%02x', $ipa[0], $ipa[1], $ipa[2], $ipa[3]);
	}
	else
	{	// Its IPV6
		if (strpos($ip,'.') !== FALSE)
		{  // IPV4 'tail' to deal with
			$temp = strrpos($ip,':') +1;
			$ipa = explode('.',substr($ip,$temp));
			$ip = substr($ip,0, $temp).sprintf('%02x%02x:%02x%02x', $ipa[0], $ipa[1], $ipa[2], $ipa[3]);
		}
		// Now 'normalise' the address
		$temp = explode(':',$ip);
		$s = 8 - count($temp);		// One element will of course be the blank
		foreach ($temp as $f)
		{
			if ($f == '')
			{
				$ret .= '0000';		// Always put in one set of zeros for the blank
				if ($s > 0)
				{
					$ret .= str_repeat('0000',$s);
					$s = 0;
				}
			}
			else
			{
				$ret .= sprintf('%04x',hexdec($f));
			}
		}
		return $ret;
	}
}


?>