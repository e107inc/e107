<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/phpinfo.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("0")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");

ob_start();
phpinfo();
$phpinfo .= ob_get_contents();

$phpinfo = eregi_replace("^.*\<body\>", "", $phpinfo);
/*
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html><head>
<style type="text/css"><!--
body {background-color: #ffffff; color: #000000;}
body, td, th, h1, h2 {font-family: sans-serif;}
pre {margin: 0px; font-family: monospace;}
a:link {color: #000099; text-decoration: none;}
a:hover {text-decoration: underline;}
table {border-collapse: collapse;}
.center {text-align: center;}
.center table { margin-left: auto; margin-right: auto; text-align: left;}
.center th { text-align: center; !important }
td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccccff; font-weight: bold;}
.h {background-color: #9999cc; font-weight: bold;}
.v {background-color: #cccccc;}
i {color: #666666;}
img {float: right; border: 0px;}
hr {width: 600px; align: center; background-color: #cccccc; border: 0px; height: 1px;}
//--></style>
<title>phpinfo()</title></head>
<body>
*/



ob_end_clean();
//$phpinfo = substr($phpinfo, 554, -19);
//$phpinfo = str_replace('width="600"', 'width="450"', $phpinfo);

$ns -> tablerender("PHPInfo", $phpinfo);

require_once("footer.php");
?>	