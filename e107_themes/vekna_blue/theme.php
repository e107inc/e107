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

// [theme]
$themename = "vekna blue";
$themeversion = "1.0";
$themeauthor = "Steve Dunstan [jalist]";
$themedate = "09/03/2005";
$themeinfo = "Based on, and with permission from Arach's site, http://e107.vekna.com";
define("STANDARDS_MODE", TRUE);

function theme_head() {
	return "<link rel='stylesheet' href='".THEME."nav_menu.css' />\n";
}


// [layout]

$layout = "_default";

/*
to use an icon image next to links, add the following line ...
{SITELINKS_ALT=".e_IMAGE."blah.png}
instead of
{SITELINKS_ALT=no_icons}
then make change to nav_menu.css (documented in that file)
*/

$HEADER = "
<table id='wrapptable' cellSpacing='2' cellPadding='2' style='width: 800'> 
<tr>
<td colspan='2'>
<div>{SITELINKS_ALT=no_icons}</div>
</td>
</tr>
<tr>
<td colspan='2'>
<div id='logo'>
&nbsp;... {SITENAME}
</div>
</td>
</tr>
<tr>
<td style='width: 600px; vertical-align: top;'>
{SETSTYLE=main}
";

$FOOTER = "
</td>
<td style='width:200px; vertical-align: top;'>
{SETSTYLE=menu}
{MENU=1}
</td>
</tr>

<tr>
<td class='infobar' colspan='2' style='text-align: center;'>{SITEDISCLAIMER}</td>
</tr>


</table>
";

$CUSTOMHEADER = "
<table id='wrapptable' cellSpacing='2' cellPadding='2' style='width: 800'> 
<tr>
<td colspan='2'>
<div>{SITELINKS_ALT=no_icons}</div>
</td>
</tr>
<tr>
<td colspan='2'>
<div id='logo'>
&nbsp;... {SITENAME}
</div>
</td>
</tr>
<tr>
<td style='width: 800px; vertical-align: top;'>
{SETSTYLE=main}
";

$CUSTOMFOOTER = "
</td>
</tr>
</table>
";

$CUSTOMPAGES = "forum.php forum_post.php forum_viewforum.php forum_viewtopic.php user.php submitnews.php download.php links.php comment.php stats.php usersettings.php";

$NEWSSTYLE = "
<div class='spacer'>
<div class='borderx'><div id='line2'>{NEWSTITLE}</div>
<div class='incontent'>{NEWSBODY}{EXTENDED}</div>
<div class='infobar'>{NEWSAUTHOR} on {NEWSDATE} | {NEWSCOMMENTS}{TRACKBACK}</div>
</div>
</div>
";

define("ICONSTYLE", "");
define("COMMENTLINK", "Read/Post Comment: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");
define("TRACKBACKSTRING", "Trackbacks: ");
define("TRACKBACKBEFORESTRING", " | ");


// [linkstyle]




define('PRELINK', "<ul>");
define('POSTLINK', "</ul>");
define('LINKSTART', "<li>");
define("LINKSTART_HILITE", "");
define('LINKEND', "</li>");
define('LINKDISPLAY', 1);
define('LINKALIGN', "left");


//	[tablestyle]

function tablestyle($caption, $text, $mode)
{
	global $style;
	if($style == "menu")
	{
		echo "<div class='spacer'><div class='caption'><div class='captionpadder'><h4>$caption</h4></div></div><div class='menubody'><div class='menupadder'>$text</div></div><div class='menubottom'></div></div>";	
	}
	else
	{
		if($caption)
		{
			echo "<div class='spacer'>\n<div class='borderx'><div id='line2'>$caption</div>\n<div class='incontent'>$text</div>\n</div>\n";
		}
		else
		{
			echo "<div class='spacer'>\n<div class='borderx'>\n<div class='incontent'>$text</div>\n</div>\n";
		}
	}
}

$COMMENTSTYLE = "
<table style='width: 100%;' cellspacing='10'>
<tr>
<td style='width: 30%; text-align: right; vertical-align: top;'><span class='mediumtext'><b>{USERNAME}</b></span><br /><span class='smalltext'>{TIMEDATE}</span><br />{AVATAR}<span class='smalltext'>{REPLY}</span></td>
<td style='width: 70%;'>
{COMMENT}
</td>
</tr>
</table>
";

define("CBWIDTH", "100%");

$CHATBOXSTYLE = "
<img src='".THEME."images/bullet2.gif' alt='' style='vertical-align: middle;' />
<b>{USERNAME}</b>
<div class='smalltext'>
{MESSAGE}
</div>
<br />";

?>