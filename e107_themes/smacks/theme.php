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

$themename = "smacks";
$themeversion = "3.0";
$themeauthor = "jalist";
$themedate = "23/06/2003";
$themeinfo = "";

// [layout]

$layout = "_default";

$logo = "e107_themes/smacks/images/logo.png";

$HEADER = opentable()."
<div style='text-align:center'>
<table style='width:100%' cellspacing='10' cellpadding='0'>
<tr>
<td style='width: 80%; vertical-align:top'>
<table style='width:100%' cellspacing='0' cellpadding='0'>
<tr>
<td style='width: 10%; vertical-align:top'>
<img src='".e_BASE.$logo."' alt='' />
<br />
{SITETAG}
</td>
<td style='width: 1%; vertical-align:top'></td>
<td class='topbar' style='width: 90%; vertical-align:top'>
<table style='width:100%' cellspacing='5' cellpadding='0'>
<tr>
<td style='width: 33%; vertical-align:top'>
{MENU=2}
</td>
<td style='width: 34%; vertical-align:top'>
{MENU=3}
</td>
<td style='width: 33%; vertical-align:top'>
{MENU=4}
</td></tr></table>
</td>
</tr>
</table>
<br />
{SETSTYLE=default}
";


$FOOTER = "
</td><td style='width:20%; vertical-align:top'>

{SETSTYLE=rightmenu}
{SITELINKS}
{MENU=1}
</td>
</tr>
</table>
</div>".
closetable()."
<div style='text-align:center'>
{SITEDISCLAIMER}
<br />
<img src='".e_IMAGE."generic/php-small-trans-light.gif' alt='' /> <img src='".e_IMAGE."button.png' alt='' /> <img src='".e_IMAGE."generic/poweredbymysql-88.png' alt='' />
</div>";

//	[newsstyle]

$NEWSSTYLE = "
<div class='border'>
<div class='caption'>
{NEWSTITLE}

<span class='smalltext'>posted by 
{NEWSAUTHOR}
 on 
{NEWSDATE}
{NEWSCOMMENTS}
{EMAILICON}
{PRINTICON}
{ADMINOPTIONS}
</span>
</div>
</div>
<div class='bodytable' style='text-align:left'>
{NEWSICON}
{NEWSBODY}
{EXTENDED}
</div>
<div style='text-align:right'>


</div>
<br />";

define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Read/Post Comment: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");

define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");



//	[tablestyle]

function tablestyle($caption, $text, $mode=""){
	global $style;
	if($style == "default"){
		if($caption != ""){
			echo "<div class='border'><div class='caption'>".$caption."</div></div>\n";
			if($text != ""){
				echo "\n<div class='bodytable'>".$text."</div>\n";
			}
		}else{
			echo "<div class='border'><div class='bodytable'>".$text."</div></div><br />\n";
		}

	}else if($style == "customtable"){
		echo "<img src='".THEME."/images/bullet2.gif'>&nbsp;<b><span class='captiontext'>".$caption."</span></b><hr />".$text;
	}else{
		if($caption != ""){
			echo "<div class='border'><div class='caption2'>".$caption."</div><div class='bodytable2'>".$text."</div></div><br />\n";
		}else{
			echo "<div class='bodytable2'>".$text."</div><br />\n";
		}
	}
}

//	[table]

function opentable(){
return "<div class='spacer'>
<table style='width:90%' cellpadding='0' cellspacing='0' border='0' >
<tr>
<td class='toplf'><img src='".e_IMAGE."generic\blank.gif' width='15' height='15' alt='' style='display: block;'/></td>
<td class='topcr'><img src='".e_IMAGE."generic\blank.gif' width='1' height='15' alt='' style='display: block;'/></td>
<td class='toprt'><img src='".e_IMAGE."generic\blank.gif' width='15' height='15' alt='' style='display: block;'/></td>
</tr>
<tr>
<td class='bodylt'><img src='".e_IMAGE."generic\blank.gif' width='15' height='1' alt='' style='display: block;'/></td>
<td class='bodycr'>";
}

function closetable(){
return "</td>
<td class='bodyrt'><img src='".e_IMAGE."generic\blank.gif' width='15' height='1' alt='' style='display: block;'/></td>
</tr>
<tr>
<td class='bottomlt'><img src='".e_IMAGE."generic\blank.gif' width='15' height='15' alt='' style='display: block;'/></td>
<td class='bottomcr'><img src='".e_IMAGE."generic\blank.gif' width='1' height='15' alt='' style='display: block;'/></td>
<td class='bottomrt'><img src='".e_IMAGE."generic\blank.gif' width='15' height='15' alt='' style='display: block;'/></td>
</tr>
</table>
</div>";
}

// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src='".THEME."images/bullet2.gif' alt='bullet' /> ");
define(LINKEND, "<br />");
define(LINKDISPLAY, 2);
define(LINKALIGN, "left");

?>