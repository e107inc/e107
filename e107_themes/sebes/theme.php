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
if(!defined("e_THEME")){ exit; }

// [multilanguage]
@include_once(e_THEME."sebes/languages/".e_LANGUAGE.".php");
@include_once(e_THEME."sebes/languages/English.php");

// [theme]
$themename = "sebes";
$themeversion = "1.1";
$themeauthor = "Steve Dunstan [jalist]";
$themeemail = "jalist@e107.org";
$themewebsite = "http://e107.org";
$themedate = "18/03/2005";
$themeinfo = "";
define("STANDARDS_MODE", TRUE);
$xhtmlcompliant = TRUE;
$csscompliant = TRUE;
define("IMODE", "lite");
define("THEME_DISCLAIMER", "<br /><i>".LAN_THEME_1."</i>");

// [layout]

$layout = "_default";

$HEADER = "
<div class='mainbox'>
<div id='banner'>
{BANNER}
</div>
<table style='width: 100%;' cellpadding='0' cellspacing='0'>
<tr>
<td><div id='logo1'></div></td>
<td id='heading'></td>
</tr>
<tr>
<td id='logo2'>&nbsp;</td>
<td id='searchbox'>
{CUSTOM=search+25+".THEME."images/search.png+19+18}
</td>
</tr>
</table>
<table style='width: 100%;' cellpadding='0' cellspacing='0'>
<tr>
<td id='doclinkcolumn'>
<div class='docinnerbox'>
{SETSTYLE=menu}
<div class='center'>[ <a href='".e_BASE."index.php'>{SITENAME}</a> ]</div><br /><br />
{SITELINKS}
{MENU=1}
</div>
</td>
<td id='docmaincolumn'>
<div class='docinnerbox'>
{SETSTYLE=main}
";


$FOOTER = "
</div>
</td>
</tr>
</table>
</div>

<div class='smalltext' style='text-align: center;'>{SITEDISCLAIMER}<br />{THEMEDISCLAIMER}</div>

";


define('PRELINK', "");
define('POSTLINK', "");
define('LINKSTART', "<img src='".THEME."images/arrow.png' alt='' /> ");
define("LINKSTART_HILITE", "<img src='".THEME."images/selarrow.png' alt='' /> ");
define('LINKEND', "<br />");
define('LINKDISPLAY', 2);
define('LINKALIGN', "left");


define("BULLET", "arrow.png");

/*	[newsstyle]	*/

$NEWSSTYLE = "
<div class='captiontext'><img src='".THEME."images/marrow.png' alt='' /> {NEWSTITLE}</div>
<div class='smalltext'>{NEWSAUTHOR} on {NEWSDATE} | {NEWSCOMMENTS}{TRACKBACK}
</div>
{NEWSBODY}
{EXTENDED}
<br /><br />";

define("ICONSTYLE", "");
define("COMMENTLINK", LAN_THEME_2);
define("COMMENTOFFSTRING", LAN_THEME_3);
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", LAN_THEME_4);
define("POST_EXTENDEDSTRING", " ]<br />");
define("TRACKBACKSTRING", LAN_THEME_5);
define("TRACKBACKBEFORESTRING", " | ");


//	[tablestyle]

function tablestyle($caption, $text, $mode)
{
	global $style;
	if($style == "menu")
	{
		echo "<div class='spacer'><div class='caption'><img src='".THEME."images/oarrow.png' alt='' /> $caption</div><div class='text'>$text</div></div><br />\n";
	}
	else
	{
		if($caption)
		{
			echo "<div class='spacer'><div class='caption'><img src='".THEME."images/marrow.png' alt='' /> $caption</div><br /></div>$text\n";
		}
		else
		{
			echo $text."\n";
		}
	}
}

$COMMENTSTYLE = "
<div class='comment'> 
<div class='lowlight'><div class='compad'>{USERNAME} {TIMEDATE}</div></div>
<div class='compad'>{COMMENT}</div>
</div>
";


$CHATBOXSTYLE = "
<div class='link2'><div class='linktext'><hr /><img src='".THEME."images/arrow.png' alt='' style='vertical-align: middle;' /> {USERNAME} | <span class='cbdate'>{TIMEDATE}</span></div></div>
<div class='smalltext'>
{MESSAGE}
</div>";


?>