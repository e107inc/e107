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
$themename = "kubrick";
$themeversion = "1.0";
$themeauthor = "Steve Dunstan [jalist]";
$themedate = "29/01/2005";
$themeinfo = "Based on 'kubrick' by Michael Heilemann (http://binarybonsai.com/kubrick/).<br />This theme is intended for minimilist blog sites, as such the forum etc isn't skinned.";
define("STANDARDS_MODE", TRUE);

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
{SITEDISCLAIMER}
Based on 'kubrick' by <a href='http://binarybonsai.com/kubrick/'>Michael Heilemann</a>.</p> 
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
{SITEDISCLAIMER}
Based on 'kubrick' by <a href='http://binarybonsai.com/kubrick/'>Michael Heilemann</a>.</p> 
</div> 
</div> 
";

$CUSTOMPAGES = "forum.php forum_post.php forum_viewforum.php forum_viewtopic.php user.php submitnews.php download.php links.php stats.php usersettings.php";

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
define("COMMENTLINK", "comment: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");
define("TRACKBACKSTRING", "Trackbacks: ");
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