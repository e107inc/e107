<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc 
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/error.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

define("ERR_PAGE_ACTIVE", 'error');

//TODO - template(s)

//We need minimal mod
$_E107 = array('no_forceuserupdate', 'no_online', 'no_prunetmp');
require_once("class2.php");
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

//start session if required
if(!session_id()) session_start();

if (!defined('PAGE_NAME')) define('PAGE_NAME','Error page');
$errorHeader = '';
$errorText = '';
$errorNumber = 999;
$errFrom = isset($_SESSION['e107_http_referer']) ? $_SESSION['e107_http_referer'] : $_SERVER['HTTP_REFERER'];
$errReturnTo = isset($_SESSION['e107_error_return']) ? $_SESSION['e107_error_return'] : array();
unset($_SESSION['e107_http_referer'], $_SESSION['e107_error_return']);

$errTo = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$errorQuery = htmlentities($_SERVER['QUERY_STRING']);
$base_path = e_HTTP;
if (is_numeric(e_QUERY)) $errorNumber = intval(e_QUERY);

switch($errorNumber) 
{
  case 400 :
	$errorHeader = "HTTP/1.1 400 Bad Request";
	$errorText = "<h1><img src='".e_IMAGE_ABS."generic/warning.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_35."</h1><div class='installh'>".LAN_ERROR_36."</div><br /><div class='smalltext'>".LAN_ERROR_3."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<br /><a href='{$base_path}index.php'>".LAN_ERROR_20."</a></div>";
    break;
  case 401:
	$errorHeader = "HTTP/1.1 401 Unauthorized";
	$errorText = "<h1><img src='".e_IMAGE_ABS."generic/warning.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_1."</h1><div class='installh'>".LAN_ERROR_2."</div><br /><div class='smalltext'>".LAN_ERROR_3."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<br /><a href='{$base_path}index.php'>".LAN_ERROR_20."</a></div>";
	break;
  case 403:
	$errorHeader = "HTTP/1.1 403 Forbidden";
	$errorText = "<h1><img src='".e_IMAGE_ABS."generic/warning.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_4."</h1><div class='installh'>".LAN_ERROR_5."</div><br /><div class='smalltext'>".LAN_ERROR_6."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<br /><a href='{$base_path}index.php'>".LAN_ERROR_20."</a></div>";
	break;
  case 404:
	$errorHeader = "HTTP/1.1 404 Not Found";
	$errorText = "<h1><img src='".e_IMAGE_ABS."generic/warning.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_7."</h1>".LAN_ERROR_21.'<br />'.LAN_ERROR_9."<br /><br />";
	if (strlen($errFrom)) $errorText .= LAN_ERROR_23." <a href='{$errFrom}' rel='external'>{$errFrom}</a> ".LAN_ERROR_24." -- ".LAN_ERROR_19."<br /><br />";
	//.LAN_ERROR_23."<b>{$errTo}</b>".LAN_ERROR_24."<br /><br />" ???

	$errorText .= "<h3>".LAN_ERROR_45."</h3>";
	if($errReturnTo) 
	{
		foreach ($errReturnTo as $url => $label)
		{
			$errorText .= "<a href='{$url}'>".$label."</a><br />";
		}
		$errorText .= '<br />';
	}
	$errorText .= "<a href='{$base_path}index.php'>".LAN_ERROR_20."</a><br />";
	$errorText .= "<a href='{$base_path}search.php'>".LAN_ERROR_22."</a>";
	break;
  case 500:
	$errorHeader = "HTTP/1.1 500 Internal Server Error";
	$errorText = "<h1><img src='".e_IMAGE_ABS."generic/warning.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_10."</h1><div class='installh'>".LAN_ERROR_11."</div><br /><div class='smalltext'>".LAN_ERROR_12."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<br /><a href='{$base_path}index.php'>".LAN_ERROR_20."</a></div>";
	break;
  case 999:
	if (E107_DEBUG_LEVEL)
	{
	  echo LAN_ERROR_33."<br/><pre>\n";
	  print_r($_SERVER);
	  print_r($_REQUEST);
	  echo "\n</pre>\n";
	}
	else
	{
		header("location: ".e_HTTP."index.php");
		exit;
	}
    break;

  default :
	$errorText = "<h1>".LAN_ERROR_13." (".$errorQuery.")</h1><div class='installh'>".LAN_ERROR_14."</div><br /><div class='smalltext'>".LAN_ERROR_15."</div>
		<br /><div class='installh'><a href='{$base_path}index.php'>".LAN_ERROR_20."</a></div>";

//	default:
//	$errorText = LAN_ERROR_34." e_QUERY = '".e_QUERY."'<br/><a href='{$base_path}index.php'>".LAN_ERROR_20."</a>";
//	break;
}

if ($errorHeader) header($errorHeader);

require_once(HEADERF);

e107::getRender()->tablerender(PAGE_NAME, $errorText);
require_once(FOOTERF);
?>