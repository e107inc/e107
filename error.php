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
|     $Source: /cvs_backup/e107_0.7/error.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/



define("ERR_PAGE_ACTIVE",'error');

require_once("class2.php");

if (!defined('PAGE_NAME')) define('PAGE_NAME','Unknown page');
$errorHeader = '';
$errorText = '';
$errorNumber = 999;
$errFrom = $_SERVER['HTTP_REFERER'];
$errTo = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$errorQuery = htmlentities($_SERVER['QUERY_STRING']);
$base_path = e_HTTP;
if (is_numeric(e_QUERY)) $errorNumber = e_QUERY;

switch($errorNumber) 
{
  case 400 :
	$errorHeader = "HTTP/1.1 400 Bad Request";
	$errorText = "<div class='installe'><img src='".e_IMAGE_ABS."icons/icon3.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_35."</div><br /><div class='installh'>".LAN_ERROR_36."</div><br /><div class='smalltext'>".LAN_ERROR_3."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<br /><a href='{$base_path}index.php'>".LAN_ERROR_20."</a></div>";
	break;
  case 401:
	header("HTTP/1.1 401 Unauthorized");
	$errorText = "<div class='installe'><img src='".e_IMAGE_ABS."icons/icon3.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_1."</div><br /><div class='installh'>".LAN_ERROR_2."</div><br /><div class='smalltext'>".LAN_ERROR_3."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<br /><a href='{$base_path}index.php'>".LAN_ERROR_20."</a></div>";
	break;
  case 403:
	header("HTTP/1.1 403 Forbidden");
	$errorText = "<div class='installe'><img src='".e_IMAGE_ABS."icons/icon3.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_4."</div><br /><div class='installh'>".LAN_ERROR_5."</div><br /><div class='smalltext'>".LAN_ERROR_6."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<br /><a href='{$base_path}index.php'>".LAN_ERROR_20."</a></div>";
	break;
  case 404:
	header("HTTP/1.1 404 Not Found");
	$errorText = "<h3><img src='".e_IMAGE_ABS."icons/icon3.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_7."</h3><br />".LAN_ERROR_21."<br /><br />".LAN_ERROR_23."<b>{$errTo}</b>".LAN_ERROR_24."<br /><br />";

	if (strlen($errFrom)) $text .= LAN_ERROR_9." ( <a href='{$errFrom}' rel='external'>{$errFrom}</a> ) -- ".LAN_ERROR_19."<br />";


	$errorText .= "<br /><a href='{$base_path}index.php'>".LAN_ERROR_20."</a><br />";
	$errorText .= "<a href='{$base_path}search.php'>".LAN_ERROR_22."</a>";
	break;
  case 500:
	header("HTTP/1.1 500 Internal Server Error");
	$errorText = "<div class='installe'><img src='".e_IMAGE_ABS."icons/icon3.png' alt='".LAN_ERROR_37."' /> ".LAN_ERROR_10."</div><br /><div class='installh'>".LAN_ERROR_11."</div><br /><div class='smalltext'>".LAN_ERROR_12."</div>
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
	$errorText = "<div class='installe'>".LAN_ERROR_13." (".$errorQuery.")</div><br /><div class='installh'>".LAN_ERROR_14."</div><br /><div class='smalltext'>".LAN_ERROR_15."</div>
		<br /><div class='installh'><a href='{$base_path}index.php'>".LAN_ERROR_20."</a></div>";
/*
  default:
	$errorText = LAN_ERROR_34." e_QUERY = '".e_QUERY."'<br /><a href='{$base_path}index.php'>".LAN_ERROR_20."</a>";
	break; */
}

if ($errorHeader) header($errorHeader);

require_once(HEADERF);

$ns->tablerender(PAGE_NAME, $errorText);
require_once(FOOTERF);
?>
