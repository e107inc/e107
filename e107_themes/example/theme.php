<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

// [theme]

$themename = "example";
$themeversion = "2.0";
$themeauthor = "jalist";
$themedate = "23/06/2003";
$themeinfo = "";

// [layout]

$layout = "_default";

$HEADER = "
<div style='text-align:center'>
<table style='width:100%' cellspacing='0' cellpadding='0'>
<tr>
<td style='background-color:#E2E2E2; text-align:left'>
{LOGO}
</td></tr>
<tr><td style='background-color:#000'></td></tr>
<tr><td style='background-color:#fff'></td></tr>
<tr><td style='background-color:#ccc'>&nbsp;
{SITETAG}
</td></tr>
<tr>
<td style='background-color:#000'></td>
</tr>
</table>
<table style='width:100%' cellspacing='10' cellpadding='10'>
<tr> 
<td style='width:15%; vertical-align: top;'>
{SETSTYLE=leftmenu}
(SITELINKS=menu}
{MENU=1}
<br />
</td>
{SETSTYLE=default}
<td style='width:70%; vertical-align: top'>";

$FOOTER = "
<br />
</td>
<td style='width:15%; vertical-align:top'>
{SETSTYLE=rightmenu}
{MENU=2}
</td>
</tr>
</table>
</div>
<table style='width:100%' cellspacing='0' cellpadding='0'>
<tr>
<td style='background-color:#000'></td>
</tr>
<tr>
<td style='background-color:#fff'></td>
</tr>
<tr>
<td style='background-color:#E2E2E2; text-align:center'>
{SITEDISCLAIMER}
<br />
<img src='".e_IMAGE."generic/php-small-trans-light.gif' alt='' /> <img src='".e_IMAGE."button.png' alt='' /> <img src='".e_IMAGE."generic/poweredbymysql-88.png' alt='' />
</td>
</tr>
<tr>
<td style='background-color:#000'></td>
</tr>
</table>";

$NEWSSTYLE = "
<div class='caption'>
{NEWSTITLE}
</div>
<div class='bodytable' style='text-align:left'>
{NEWSICON}
{NEWSBODY}
{EXTENDED}
</div>
<div style='text-align:right'>
{EMAILICON}
{PRINTICON}
{ADMINOPTIONS}
<br />
Posted by 
{NEWSAUTHOR}
on
{NEWSDATE}
&nbsp;::&nbsp;
{NEWSCOMMENTS}
</div>
<br />";

define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Read/Post Comment: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");

define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");


// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src='".THEME."images/bullet2.gif' alt='bullet' /> ");
define(LINKEND, "<br />");
define(LINKDISPLAY, 2);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//	[tablestyle]

function tablestyle($caption, $text){
	global $style;
	if($style == "leftmenu"){
		if($caption != ""){
			echo "<div class='border2'><div class='caption2'>".$caption."</div></div>";
			if($text != ""){
				echo "\n<div class='bodytable2'>".$text."</div>";
			}
		}else{
			echo "<div class='border2'><div class='bodytable2'>".$text."</div></div><br />";
		}
	}else if($style == "default"){
		if($caption != ""){
			echo "<div class='border'><div class='caption'>".$caption."</div></div><div class='bodytable'>".$text."</div><br />";
		}else{
			echo "<div class='bodytable'>".$text."</div><br />";
		}
	}else{
		if($caption != ""){
			echo "<div class='border3'><div class='caption3'>".$caption."</div></div><div class='bodytable3'>".$text."</div><br />";
		}else{
			echo "<div class='bodytable3'>".$text."</div><br />";
		}
	}
}

?>