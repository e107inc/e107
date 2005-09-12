<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	©Steve Dunstan 2001-2005
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
if(!defined("e_THEME")){ exit; }
// [multilanguage]
@include_once(e_THEME."khatru/languages/".e_LANGUAGE.".php");
@include_once(e_THEME."khatru/languages/English.php");

// [theme]
$themename = "khatru";
$themeversion = "1.0";
$themeauthor = "Steve Dunstan [jalist]";
$themeemail = "jalist@e107.org";
$themewebsite = "http://e107.org";
$themedate = "28/05/2005";
$themeinfo = "";
define("STANDARDS_MODE", TRUE);
$xhtmlcompliant = FALSE;
$csscompliant = FALSE;
define("IMODE", "lite");
define("THEME_DISCLAIMER", "<br /><i>".LAN_THEME_1."</i>");

// [layout]

$layout = "_default";


define("BOXOPEN", "
<div class='spacer'>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='caption1'><img src='".THEME."images/blank.gif' width='9' height='26' alt='' style='display: block;' /></td>
<td class='caption2'>");

define("BOXMAIN", "
</td>
<td class='caption3'><img src='".THEME."images/blank.gif' width='9' height='26' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='bodyleft'><img src='".THEME."images/blank.gif' width='5' height='1' alt='' style='display: block;' /></td>
<td class='bodymain'>
");

define("BOXCLOSE", "
</td>
<td class='bodyright'><img src='".THEME."images/blank.gif' width='5' height='1' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='bottomleft'><img src='".THEME."images/blank.gif' width='9' height='8' alt='' style='display: block;' /></td>
<td class='bottommain'><img src='".THEME."images/blank.gif' width='1' height='8' alt='' style='display: block;' /></td>
<td class='bottomright'><img src='".THEME."images/blank.gif' width='9' height='8' alt='' style='display: block;' /></td>
</tr>
</table>
</div>");

define("BOXOPEN2", "
<div class='spacer'>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='topleft'><img src='".THEME."images/blank.gif' width='9' height='7' alt='' style='display: block;' /></td>
<td class='top'><img src='".THEME."images/blank.gif' width='1' height='7' alt='' style='display: block;' /></td>
<td class='topright'><img src='".THEME."images/blank.gif' width='9' height='7' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='bodyleft'><img src='".THEME."images/blank.gif' width='5' height='1' alt='' style='display: block;' /></td>
<td class='bodymain'>
");

define("BOXCLOSE2", "
</td>
<td class='bodyright'><img src='".THEME."images/blank.gif' width='5' height='1' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='bottomleft'><img src='".THEME."images/blank.gif' width='9' height='8' alt='' style='display: block;' /></td>
<td class='bottommain'><img src='".THEME."images/blank.gif' width='1' height='8' alt='' style='display: block;' /></td>
<td class='bottomright'><img src='".THEME."images/blank.gif' width='9' height='8' alt='' style='display: block;' /></td>
</tr>
</table>
</div>
");



$HEADER = "
<table cellpadding='0' cellspacing='0'>
<tr>
<td id='logo1'><img src='".THEME."images/blank.gif' width='192' height='123' alt='' style='display: block;' /></td>
<td id='logo2'><img src='".THEME."images/blank.gif' width='1' height='123' alt='' style='display: block;' /></td>
<td id='logo3'><img src='".THEME."images/blank.gif' width='8' height='123' alt='' style='display: block;' /></td>
</tr>
</table>
<div id='banner'>
{BANNER}
</div>
<table style='width: 100%;' cellpadding='0' cellspacing='0'>
<tr>
<td style='width:180px;'></td>
<td class='centercontent'><img src='".THEME."images/blank.gif' width='1' height='1' alt='' /></td>
<td style='width:180px;'></td>
</tr>
<tr>
<td id='leftcontent'>
<div class='menuwrapper'>
<div class='columnwrap'>
{SITELINKS}
{MENU=1}
</div>
</div>
</td>
<td class='centercontent'>
<div class='columnwrap'>
";


$FOOTER = "
</div>
</td>
<td id='rightcontent'>
<div class='menuwrapper'>
<div class='columnwrap'>
{MENU=2}
</div>
</div>
</td>
</tr>
</table>
<div id='footer'>
<div class='columnwrap'>
<div style='text-align: center;' class='smalltext'>
{SITEDISCLAIMER}<br />{THEMEDISCLAIMER}
<br />
<a href='http://www.spreadfirefox.com/?q=affiliates&amp;id=0&amp;t=86'><img alt='e107 recommends Firefox' title='e107 recommends Firefox' src=' http://sfx-images.mozilla.org/affiliates/Buttons/125x50/takebacktheweb_125x50.png' style='border: 0;' /></a>



</div>		
</div>
</div>
";

$CUSTOMHEADER = "
<table cellpadding='0' cellspacing='0'>
<tr>
<td id='logo1'><img src='".THEME."images/blank.gif' width='192' height='123' alt='' style='display: block;' /></td>
<td id='logo2'><img src='".THEME."images/blank.gif' width='1' height='123' alt='' style='display: block;' /></td>
<td id='logo3'><img src='".THEME."images/blank.gif' width='8' height='123' alt='' style='display: block;' /></td>
</tr>
</table>
<div id='banner'>
{BANNER}
</div>
<table style='width: 100%;' cellpadding='0' cellspacing='0'>
<tr>
<td class='centercontent'>
<div class='columnwrap'>
";

$CUSTOMFOOTER = "
</div>
</td>
</tr>
</table>
<div id='footer'>
<div class='columnwrap'>
<div style='text-align: center;' class='smalltext'>
{SITEDISCLAIMER}<br />{THEMEDISCLAIMER}
<br />
<img src='".e_IMAGE."generic/php-small-trans-light.gif' alt='' /> <img src='".e_IMAGE."button.png' alt='' /> 
<img src='".e_IMAGE."generic/poweredbymysql-88.png' alt=''  /> &nbsp;&nbsp;&nbsp;&nbsp;
<a href='http://validator.w3.org/check?uri=referer' rel='external'><img src='".e_IMAGE."generic/valid-xhtml11.png' alt='' style='border: 0;' /></a> 
<a href='http://jigsaw.w3.org/css-validator/check/referer' rel='external'><img src='".e_IMAGE."generic/vcss.png' alt='' style='border: 0;' /></a>
</div>		
</div>
</div>
";

$CUSTOMPAGES = "doc.php";

function tablestyle($caption, $text)
{

	if($caption)
	{
        echo BOXOPEN.$caption.BOXMAIN.$text.BOXCLOSE;
	}
	else
	{
		echo BOXOPEN2.$text.BOXCLOSE2;
	}
}

// [linkstyle]

define('PRELINK', "");
define('POSTLINK', "");
define('LINKSTART', "");
define("LINKSTART_HILITE", "");
define('LINKEND', "<br />");
define('LINKDISPLAY', 2);
define('LINKALIGN', "left");

define("BULLET", "bullet.png");
define("bullet", "bullet.png");

$NEWSSTYLE = "
<div class='newheadline'>
{NEWSTITLE}
</div>
<div class='newsinfo'>
{NEWSAUTHOR}
, 
{NEWSDATE}
 // 
{NEWSCOMMENTS}{TRACKBACK}
</div>
<br />
<div class='newstext'>
{NEWSBODY}
{EXTENDED}
</div>
<br /><br />";
define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", LAN_THEME_3);
define("COMMENTOFFSTRING", LAN_THEME_2);
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", LAN_THEME_4);
define("POST_EXTENDEDSTRING", " ]<br />");
define("TRACKBACKSTRING", LAN_THEME_5);
define("TRACKBACKBEFORESTRING", ", ");




$CHATBOXSTYLE = 
"
<img src='".THEME."images/bullet.png' alt=''  />
<span class='chatb'>{USERNAME}</span>
<div class='smalltext'>
{TIMEDATE}<br />
</div>
{MESSAGE}
<br /><br />";


$COMMENTSTYLE = "
<table style='width:100%'>
<tr>
<td colspan='2' class='commentinfo'>
{SUBJECT}
<b>
{USERNAME}
</b>
 |
{TIMEDATE}
</td>
</tr>
<tr>
<td style='width:30%; vertical-align:top'>
<div class='spacer'>
{AVATAR}
</div>
<span class='smalltext'>
{COMMENTS}
<br />
{JOINED}
</span>
<br/>
{REPLY}
</td>
<td style='width:70%; vertical-align:top'>
{COMMENT}
<div style='text-align: right;' class='smallext'>{IPADDRESS}</div>
</td>
</tr>
</table>
<br />";


?>