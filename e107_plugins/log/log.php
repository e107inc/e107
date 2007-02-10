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
| File locking, modified getip() 18.01.07
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/log/log.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-02-10 15:54:47 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

// File called with:
// e_PLUGIN_ABS."log/log.php?referer=' + ref + '&color=' + colord + '&eself=' + eself + '&res=' + res + '\">' );\n";
// referer= ref
// color= colord
// eself= eself 
// res= res
define("log_INIT", TRUE);
$colour = strip_tags((isset($_REQUEST['color']) ? $_REQUEST['color'] : ''));
$res = strip_tags((isset($_REQUEST['res']) ? $_REQUEST['res'] : ''));
$self = strip_tags((isset($_REQUEST['eself']) ? $_REQUEST['eself'] : ''));
$ref = addslashes(strip_tags((isset($_REQUEST['referer']) ? $_REQUEST['referer'] : '')));
$date = date("z.Y", time());

if(strstr($ref, "admin")) 
{
	$ref = FALSE;
}

$screenstats = $res."@".$colour;
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
if(preg_match("/".$pageDisallow."/i", $pageName)) return;


$logPfile = "logs/logp_".$date.".php";

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



function getip($mode=TRUE) 
{
  if (getenv('HTTP_X_FORWARDED_FOR')) 
  {
	$ip = $_SERVER['REMOTE_ADDR'];
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
  else 
  {
	$ip = $_SERVER['REMOTE_ADDR'];
  }
  if ($ip == "") 
  {
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