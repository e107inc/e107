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
|     $Source: /cvs_backup/e107_0.7/e107_themes/human_condition/theme.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-22 17:56:12 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

// [multilanguage]

if(file_exists(e_THEME."ranyart/languages/".e_LANGUAGE.".php")){
  require_once(e_THEME."ranyart/languages/".e_LANGUAGE.".php");
}else{
  require_once(e_THEME."ranyart/languages/English.php");
}

// [theme]
$themename = "human condition";
$themeversion = "1.0";
$themeauthor = "jalist";
$themedate = "19/01/2005";
$themeinfo = "based on the Wordpress theme, <a href='http://wordpress.org'>http://wordpress.org</a>";
define("STANDARDS_MODE", TRUE);
// [layout]

$layout = "_default";

$HEADER = "
<div id='rap'>
<h1 id='header'><a href='#' title='e107 v0.7'>".$pref['sitename']."</a></h1>
<div class='post'>
{SETSTYLE=post}
";


$FOOTER = "
{SETSTYLE=default}
</div>
<div id='menu'>
{SITELINKS}
{MENU=1}
<br /><hr /><span class='smalltext'>{SITEDISCLAIMER}<br />'$themename' $themeversion by $themeauthor :: $themeinfo</span>
</div>
</div>

";

define("TP_commenticon", "<img src='".THEME."images/comment.png' alt='' style='vertical-align:middle;' />");

$NEWSSTYLE = "
<div class='textstyle4'><b>{NEWSTITLE}</b></div>
<div class='postinfo'>{NEWSCATEGORY}: {NEWSAUTHOR} @ {NEWSDATE}</div>
<div class='textstyle3'>{NEWSBODY}</div>
<div class='postinfo'>".TP_commenticon." {NEWSCOMMENTS}</div>\n<br />\n";



define("DATEHEADERCLASS", "button");
//	define("DATEHEADERCLASS", "nextprev");	// uncomment this line for a different style of news date header

define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", LAN_THEME_1);
define("COMMENTOFFSTRING", LAN_THEME_2);

define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", LAN_THEME_3);
define("POST_EXTENDEDSTRING", " ]<br />");


// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src='".THEME."images/bullet2.gif' alt='bullet' /> ");
define(LINKEND, "<br />");
define(LINKDISPLAY, 2);                        // 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//        [tablestyle]

function tablestyle($caption, $text, $mode="")
{
	global $style;
	
	if($style == "post")
	{

		if(!$caption)
		{
			echo "<div id='spacer'>$text</div>\n";
		}
		else if(!$text)
		{
			echo "<div id='spacer'><div class='date'>$caption</div></div>\n";
		}
		else
		{
			echo "<div id='spacer'><div class='date'>$caption</div>\n$text\n</div>\n";
		}
	}
	else
	{
		if(!$caption)
		{
			echo "<div id='spacer'>$text</div>\n";
		}

		else if(!$text)
		{
			echo "<div id='spacer'>$caption</div>\n";
		}
		else
		{
			echo "<div id='spacer'><div id='menubox'><b>$caption</b><br />$text</div></div>";
		}
	}
}

$COMMENTSTYLE = "
<table style='width:100%'>
<tr>
<td colspan='2' class='forumheader3'>
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
</td>
</tr>
</table>
<br />";

$POLLSTYLE = <<< EOF
<b>Poll:</b> {QUESTION}
<br /><br />
{OPTIONS=<div class='alttd8'>OPTION</div>BAR<br /><span class='smalltext'>PERCENTAGE VOTES</span><br />\n}
<br /><div style='text-align:center' class='smalltext'>{AUTHOR}<br />{VOTE_TOTAL} {COMMENTS}
<br />
{OLDPOLLS}
</div>
EOF;

$CHATBOXSTYLE = "
<img src='".THEME."images/bullet2.gif' alt='bullet' />
<b>{USERNAME}</b><br />{TIMEDATE}
<div class='smalltext'>
{MESSAGE}
</div>
<br />";

?>
