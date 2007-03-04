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
|     $Source: /cvs_backup/e107_0.8/error.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-03-04 21:49:06 $
|     $Author: mrpete $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
if(!e_QUERY || (e_QUERY != 401 && e_QUERY != 403 && e_QUERY != 404 && e_QUERY != 500))
{
	if (E107_DEBUG_LEVEL)
	{
		echo LAN_ERROR_33."<br/><pre>\n";
		print_r($_SERVER);
		print_r($_REQUEST);
		echo "\n</pre>\n";
	} else {		
		echo "<script type='text/javascript'>document.location.href='index.php'</script>\n";
		header("location: ".e_HTTP."index.php");
		exit;
	}
}

require_once(HEADERF);

$errFrom = $_SERVER['HTTP_REFERER'];
$errTo = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

switch(e_QUERY) {
	case 401:
	header("HTTP/1.1 401 Unauthorized");
	$text = "<div class='installe'><img src='".e_IMAGE_ABS."packs/".$imode."/generic/warning.png' alt='Error Icon'> ".LAN_ERROR_1."</div><br /><div class='installh'>".LAN_ERROR_2."</div><br /><div class='smalltext'>".LAN_ERROR_3."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<a href='index.php'>".LAN_ERROR_20."</a></div>";
	break;

	case 403:
	header("HTTP/1.1 403 Forbidden");
	$text = "<div class='installe'><img src='".e_IMAGE_ABS."packs/".$imode."/generic/warning.png' alt='Error Icon'> ".LAN_ERROR_4."</div><br /><div class='installh'>".LAN_ERROR_5."</div><br /><div class='smalltext'>".LAN_ERROR_6."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<a href='index.php'>".LAN_ERROR_20."</a></div>";
	break;

	case 404:
	header("HTTP/1.1 404 Not Found");
	$text = "<h3><img src='".e_IMAGE_ABS."packs/".$imode."/generic/warning.png' alt='Error Icon'> ".LAN_ERROR_7."</h3><br />".LAN_ERROR_21."<br /><br />".LAN_ERROR_23."<b>{$errTo}</b>".LAN_ERROR_24."<br /><br />";

	if (strlen($errFrom)) $text .= LAN_ERROR_9." ( <a href='{$errFrom}' rel='external'>{$errFrom}</a> ) -- ".LAN_ERROR_19."<br />";

	$base_path = e_HTTP;

	$text .= "<br /><a href='{$base_path}index.php'>".LAN_ERROR_20."</a><br />";
	$text .= "<a href='{$base_path}search.php'>".LAN_ERROR_22."</a></p>";
	break;

	case 500:
	header("HTTP/1.1 500 Internal Server Error");
	$text = "<div class='installe'><img src='".e_IMAGE_ABS."packs/".$imode."/generic/warning.png' alt='Error Icon'> ".LAN_ERROR_10."</div><br /><div class='installh'>".LAN_ERROR_11."</div><br /><div class='smalltext'>".LAN_ERROR_12."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<a href='index.php'>".LAN_ERROR_20."</a></div>";
	break;
	$text = "<div class='installe'>".LAN_ERROR_13." (".$_SERVER['QUERY_STRING'].")</div><br /><div class='installh'>".LAN_ERROR_14."</div><br /><div class='smalltext'>".LAN_ERROR_15."</div>
		<br /><div class='installh'>".LAN_ERROR_2."<a href='index.php'>".LAN_ERROR_20."</a></div>";

	default:
	$text = LAN_ERROR_34." e_QUERY = '".e_QUERY."'<br/><a href='index.php'>".LAN_ERROR_20."</a>";
	break;
}

$ns->tablerender(PAGE_NAME, $text);
require_once(FOOTERF);
?>