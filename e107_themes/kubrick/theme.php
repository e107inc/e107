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
// [multilanguage]
@include_once(e_THEME."kubrick/languages/".e_LANGUAGE.".php");
@include_once(e_THEME."kubrick/languages/English.php");

// [theme]
$themename = "kubrick";
$themeversion = "1.0";
$themeauthor = "Steve Dunstan [jalist]";
$themeemail = "jalist@e107.org";
$themewebsite = "http://e107.org";
$themedate = "29/01/2005";
$themeinfo = "Based on 'kubrick' by Michael Heilemann (http://binarybonsai.com/kubrick/).<br />This theme is intended for minimilist blog sites, as such the forum etc isn't skinned.";
define("STANDARDS_MODE", TRUE);
$xhtmlcompliant = TRUE;
$csscompliant = TRUE;
define("IMODE", "lite");
define("THEME_DISCLAIMER", "<br /><i>".LAN_THEME_1."</i>");

// [layout]

$layout = "_default";

$HEADER = "<div id='page'>
<div id='header'>
<div id='headerimg'>
<h1><a href='{SITEURL}' title='{SITENAME}'>{SITENAME}</a></h1>
<br /><br /><br /><br /><br />
<div class='sitetag'>{SITETAG}</div>
</div>
</div>
<div class='sitelinks'>{SITELINKS}</div>
<hr />
<div id='content' class='narrowcolumn'> 
";

$FOOTER = "
</div> 
<div id='sidebar'> 
{MENU=1}
</div> 
<hr /> 
<div id='footer'>
<p>
{SITEDISCLAIMER}<br />{THEMEDISCLAIMER}
</p> 
</div> 
</div> 
";

$CUSTOMHEADER = "<div id='page2'>
<div id='header'>
<div id='headerimg'>
<h1><a href='{SITEURL}' title='{SITENAME}'>{SITENAME}</a></h1>
<br /><br /><br /><br /><br />
<div class='sitetag'>{SITETAG}</div>
</div>
</div>
<div class='sitelinks'>{SITELINKS}</div>
<hr />
<div id='content' class='widecolumn'> 
";

$CUSTOMFOOTER = "
</div> 
<hr /> 
<div id='footer'>
<p>
{SITEDISCLAIMER}<br />{THEMEDISCLAIMER}
</p> 
</div> 
</div> 
";

$CUSTOMPAGES = "forum.php forum_post.php forum_viewforum.php forum_viewtopic.php user.php submitnews.php download.php links.php stats.php usersettings.php signup.php";

$NEWSSTYLE = "
<h2>{NEWSTITLE}</h2>
<small>on {NEWSDATE} | by {NEWSAUTHOR}</small>
<div class='entry' style='text-align:left'>
{NEWSBODY}
{EXTENDED}
</div>
<div style='text-align:right' class='smalltext'>
{NEWSCOMMENTS}{TRACKBACK}
</div>
<br />";
define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", LAN_THEME_3);
define("COMMENTOFFSTRING", LAN_THEME_2);
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", LAN_THEME_4);
define("POST_EXTENDEDSTRING", " ]<br />");
define("TRACKBACKSTRING", LAN_THEME_5);
define("TRACKBACKBEFORESTRING", " | ");


// [linkstyle]

define('PRELINK', "");
define('POSTLINK', "");
define('LINKSTART', "");
define('LINKEND', "&nbsp;&nbsp;&nbsp;");
define('LINKDISPLAY', 1);
define('LINKALIGN', "left");


//	[tablestyle]

function tablestyle($caption, $text, $mode)
{
	echo "<h3>$caption</h3>\n<br />\n$text\n<br /><br />\n";	
}

$COMMENTSTYLE = "
<table style='width: 450px;'>
<tr>
<td style='width: 30%; vertical-align: top;'><span class='mediumtext'>{USERNAME}</span><br /><span class='smalltext'>{TIMEDATE}</span><br />{AVATAR}{REPLY}</td>
<td style='width: 70%; vertical-align: top;'><span class='mediumtext'>{COMMENT}</span></td>
</tr>
</table>
<br /><br />
<br /><br />
";


$CHATBOXSTYLE = "
<img src='".e_IMAGE."admin_images/chatbox_16.png' alt='' style='vertical-align: middle;' />
<b>{USERNAME}</b>
<div class='smalltext'>
{MESSAGE}
</div>
<br />";

?>