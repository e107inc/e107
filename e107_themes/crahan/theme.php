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

// [theme]
$themename = "CraHan";
$themeversion = "1.0";
$themeauthor = "Steve Dunstan [jalist]";
$themeemail = "jalist@e107.org";
$themewebsite = "http://e107.org";
$themedate = "29/01/2005";
$themeinfo = "Based on the theme by CraHan at his homepage http://n00.be, design used with permission *smooch*";
define("STANDARDS_MODE", TRUE);

$logo = "logo".rand(1, 4);

// [layout]

$layout = "_default";

$HEADER = "
<div id='wrapper'>
<div id='menu'>
<div class='content'>
<div class='fixfuckedie'>
{MENU=1}
</div>
</div>
</div>
<div id='main'>
<div id='sitebanner'>
<div id='$logo'><div class='smalltext' style='text-align: right; vertical-align: bottom;'>{SITENAME}&nbsp;</div></div>
<div id='navbar'>
{SITELINKS}
</div>
</div>
<div class='content'>
<div class='fixfuckedie'>
";

$FOOTER = "
</div>
</div>
<br />
<br />
<div class='smalltext' style='text-align: center;'>{SITEDISCLAIMER}</div>
</div>
</div>
";

$NEWSSTYLE = "
<div class='header'>
<div class='left'>{STICKY_ICON} {NEWSTITLE}</div>
<div class='right'>{NEWSDATE}</div>
</div>
<div class='bodytable' style='text-align:left'>
{NEWSBODY}
{EXTENDED}
</div>
<br />
<div class='newssmalltext'>[ {NEWSAUTHOR} :: {NEWSCOMMENTS}{TRACKBACK} ]</div>
<br />
<br />";

define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "comment(s): ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");
define("TRACKBACKSTRING", "Trackbacks: ");
define("TRACKBACKBEFORESTRING", " :: ");


// [linkstyle]

define('PRELINK', "");
define('POSTLINK', " ::");
define('LINKSTART', ":: ");
define('LINKSTART_HILITE', "> ");
define('LINKEND', "");
define('LINKDISPLAY', 1);
define('LINKALIGN', "left");


//	[tablestyle]

function tablestyle($caption, $text, $mode)
{
	echo "<div class='header'>$caption</div>\n$text\n<br /><br />\n";
}

$COMMENTSTYLE = "

<div style='padding-left: 25px;'>{COMMENT}<br /><br />
<div class='newssmalltext'>[ Comment by {USERNAME} :: {TIMEDATE} ]</div>
</div>
<br /><br />



";

$POLLSTYLE = <<< EOF
<b>Poll:</b> {QUESTION}
<br /><br />
{OPTIONS=OPTION<br />BAR<br /><span class='smalltext'>PERCENTAGE VOTES</span><br />\n}
<br /><div style='text-align:center' class='smalltext'>{AUTHOR}<br />{VOTE_TOTAL} {COMMENTS}
<br />
{OLDPOLLS}
</div>
EOF;

$CHATBOXSTYLE = "
<img src='".e_IMAGE."admin_images/chatbox_16.png' alt='' style='vertical-align: middle;' />
<b>{USERNAME}</b>
<div class='smalltext'>
{MESSAGE}
</div>
<br />";

?>