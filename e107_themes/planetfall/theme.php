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
$themename = "planetfall";
$themeversion = "1.0";
$themeauthor = "CaMer0n";
$themeemail = "cameron@e107coders.org";
$themewebsite = "http://e107.org";
$themedate = "27 March 2005";
$themeinfo = "A port of the great Mambo theme 'Planetfall' by rhuk (rhuk@rhuk.net) [GNU/GPL]";
define("STANDARDS_MODE", TRUE);
define("IMODE", "dark");
$xhtmlcompliant = TRUE;
$csscompliant = TRUE;


define("THEME_DISCLAIMER", "<br /><i>".LAN_THEME_1."</i>");
function theme_head() {
	return "<link rel='stylesheet' href='".THEME."nav_menu.css' />\n";
}

// [layout]

$layout = "_default";

$HEADER = "
<div class='page_bg' style='margin-left:auto;margin-right:auto;text-align:center' >
	<table class='big_frame' style='width:798px' cellpadding='0' cellspacing='0' >
	<tr>
		<td colspan='3'>
		<img src='".THEME."images/top_bar.jpg' style='width:798px;height:9px' alt=''  />
		</td>
	</tr>
	<tr>
		<td class='logo' colspan='2'>
		<img src='".THEME."images/spacer.png' style='width:646px;height:9px' alt='' />
		<br />
		</td>
		<td class='top_right_box' style='width: 151px; padding-left: 5px;vertical-align:top'>
			<table cellpadding='0' cellspacing='1' border='0' style='width:120px' class='contentpaneopen'>
			<tr>
				<td class='contentheading' style='width:145px'>
				Search
				</td>
			</tr>
			<tr>
				<td>

				<div class='searchblock' id='searchblock'>
				<table><tr><td class='searchblock'>Enter Keywords:</td></tr><tr><td>
				{CUSTOM=search}
				</td></tr></table>
 				</div>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='3' class='silver_box' style='height:26px;'>
			<div id='silver_toolbar'>
				<div id='silver_date'>
				{CUSTOM=clock}
				</div>
				<div id='silver_menu'>
				<!-- render the links with rendertype 2-alt. -->
				{LINKSTYLE=top}
				{SITELINKS=flat:2}
				</div>
			<div style='clear: both;'></div>
			</div>
		</td>
	</tr>
	<tr>
		<td class='content_box' style='vertical-align:top'>
			<table border='0' cellpadding='0' cellspacing='0' style='width:100%'>
			<tr style='vertical-align:top'>
			<td>
			<div class='contentblock' id='contentblock' style='width:484px'>
			<br />";


$FOOTER = "
		<br />
		</div>
		<div class='footerblock' id='footerblock' style='text-align:left'>
		{SETSTYLE=nocap}
		{PLUGIN=other_news_menu/other_news2_menu}
		<br />
		</div>
		</td>
		</tr>
		</table>
	</td>
	<td class='middle_box' style='vertical-align:top;width:151px'>
			<div id='middle_box'>

			<div class='rightblock' id='rightblock' style='width:145px'>
				{SETSTYLE=rightest}
				{PLUGIN=poll/poll_menu}
                {MENU=2}
			</div>
			<div class='user2block' id='user2block' style='width:143px'>
			{MENU=3}
			</div>

			</div>
			</td>

		<td class='right_box' style='vertical-align:top;width: 151px'>
		<div id='right_box'>
			<!-- far right menu -->
			<div class='leftblock' id='leftblock' style='width:143px'>
			{SETSTYLE=rightest}
			<div class='menuheader' style='padding-top:3px'>Main Menu</div>
            {LINKSTYLE=default}
			{SITELINKS=flat}
			</div>
			<div class='user1block' id='user1block' style='text-align:left;width:143px'>
			{PLUGIN=login_menu}
			{SETSTYLE=rightest}
			{MENU=1}
			</div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan='3'>
		<img src='".THEME."images/top_bar.jpg' width='798' height='9' alt='' />
		</td>
	</tr>
	</table>
</div>
";

$CUSTOMHEADER = "
<div class='page_bg' style='margin-left:auto;margin-right:auto;text-align:center' >
	<table class='big_frame' style='width:798px' cellpadding='0' cellspacing='0' >
	<tr>
		<td colspan='2'>
		<img src='".THEME."images/top_bar.jpg' style='width:798px;height:9px' alt=''  />
		</td>
	</tr>
	<tr>
		<td class='logo' colspan='1'>
			<img src='".THEME."images/spacer.png' style='width:646px;height:9px' alt='' />
			<br />
		</td>
		<td class='top_right_box' style='width: 151px; padding-left: 5px;vertical-align:top'>
			<table cellpadding='0' cellspacing='1' border='0' style='width:120px' class='contentpaneopen'>
			<tr>
				<td class='contentheading' style='width:145px'>
				Search
				</td>
			</tr>
			<tr>
				<td>

				<div class='searchblock' id='searchblock'>
				<table><tr><td class='searchblock'>Enter Keywords:</td></tr><tr><td>
				{CUSTOM=search}
				</td></tr></table>
 				</div>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan='3' class='silver_box' style='height:26px;'>
			<div id='silver_toolbar'>
				<div id='silver_date'>
				{CUSTOM=clock}
				</div>
				<div id='silver_menu'>
				{LINKSTYLE=top}
				{SITELINKS=flat:2}
				</div>
			<div style='clear: both;'></div>
			</div>
		</td>
	</tr>
	<tr>
		<td class='content_box' style='vertical-align:top'>
			<table cellpadding='0' cellspacing='0' style='width:100%;border:0px'>
			<tr style='vertical-align:top'>
			<td >
			<div class='contentblock' id='contentblock' style='padding:3px'>
			<br />

	<!-- end header -->


";

$CUSTOMFOOTER = "

 <!--start footer -->
		<br />
		</div>
    	</td>
		</tr>
		</table>
	</td>


		<td class='right_box' style='vertical-align:top;width: 151px'>
		<div id='right_box'>
			<!-- far right menu -->
			<div class='leftblock' id='leftblock' style='width:143px'>
			{SETSTYLE=rightest}
			<div class='menuheader' style='padding-top:3px'>Main Menu</div>
            {LINKSTYLE=default}
			{SITELINKS=flat}
			</div>
			<div class='user1block' id='user1block' style='text-align:left;width:143px'>
			{PLUGIN=login_menu}
			{SETSTYLE=rightest}
			{MENU=1}
			</div>
		</div>
		</td>
	</tr>
	<tr>
		<td colspan='3'>
		<img src='".THEME."images/top_bar.jpg' style='width:798px' height='9' alt='' />
		</td>
	</tr>
	</table>
</div>
";

 $CUSTOMPAGES = "upload.php forum.php forum_post.php forum_viewforum.php forum_viewtopic.php user.php submitnews.php download.php links.php comment.php stats.php usersettings.php documentation";

$NEWSSTYLE = "
<div class='spacer'>
<div class='borderx'><div class='contentheading' style='padding:3px'>{NEWSTITLE}</div>
<div style='text-align:left;color:white;font-weight:bold'>Written by {NEWSAUTHOR}</div>
<div style='text-align:left;color:white;font-weight:bold'>{NEWSDATE}</div>
<div class='contentpaneopen'>{NEWSBODY}{EXTENDED}</div>
<div class='infobar' style='text-align:right'>{NEWSCOMMENTS}{TRACKBACK}</div>
</div>
</div>
";

define("ICONSTYLE", "");
define("COMMENTLINK", "Comments ");
define("COMMENTOFFSTRING", "Comments Off");
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read More..");
define("POST_EXTENDEDSTRING", " ]<br />");
define("TRACKBACKSTRING", "Trackback");
define("TRACKBACKBEFORESTRING", " | ");
define("NEWSLIST_CATICON","border:0px");

define("OTHERNEWS2_COLS",2);
define("OTHERNEWS2_LIMIT",2);

$OTHERNEWS2_STYLE = "
	<table cellpadding='4' cellspacing='0' style='margin-bottom:4px;width:100%'>
	<tr><td colspan='2'>
	<div class='contentheading' style='padding:3px'>
	{NEWSTITLELINK}
	</div>
    <div style='text-align:left;color:white;font-weight:bold'>Written by {NEWSAUTHOR}</div>
	<div style='text-align:left;color:white;font-weight:bold'>{NEWSDATE}</div>
	</td>

	</tr>
	<tr style='height:100%;'>
	<td style='padding:3px;vertical-align:top'>
	{NEWSBODY}
	</td>
	<td style='padding:5px;text-align:right;vertical-align:top'>
	{NEWSTHUMBNAIL}
	</td>
	</tr>
	</table>
";

//	[linkstyle]

function linkstyle($linkstyle){
	if($linkstyle == "top"){ // top menu (rendertype-2 ie. {SITELINKS=flat:2} )
    	$style['prelink'] = "<ul id='mainlevel-nav'>";
		$style['postlink'] = "</ul>";
		$style['linkstart'] = "<li>";
		$style['linkstart_hilite'] = "<li >";
		$style['linkend'] = "</li>";
		$style['linkdisplay'] = 1;
		$style['linkalign'] = "";
  		$style['linkclass'] = "mainlevel-nav";
	}else{       // default main menu style.
		$style['prelink'] = "<div style='text-align:left'>";
		$style['postlink'] = "</div>";
		$style['linkstart'] = "";
		$style['linkstart_hilite'] = "";
		$style['linkend'] = "<br />";
		$style['linkdisplay'] = 1;
		$style['linkalign'] = "left";
  		$style['linkclass'] = "mainlevel";
		$style['linkclass_hilite'] = "mainlevel-hilite";
	}
    return $style;
}

//	[tablestyle]

function tablestyle($caption, $text, $mode){
	global $style;
	if($style == "rightest"){
		echo "<div class='spacer' style='text-align:left'>
		<table cellpadding='0' cellspacing='0' class='moduletable'>
		<tr>
		<th class='menuheader' style='padding-right:3px'>$caption</th>
		</tr>
		<tr>
		<td style='text-align:left'>$text</td></tr></table></div>";
    }elseif($style=='nocap'){
		echo "<div>$text</div>";
	} else 	{
		if($caption){
		 	echo "<div class='spacer'>\n
		 	<div class='contentheading' style='padding:3px'>$caption</div>\n
		  	<div class='incontent'>$text</div>\n</div>\n";
		}else{
		  	echo "<div class='spacer'>\n
		 	<div >$text</div>\n</div>\n";
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

$CHATBOXSTYLE = "
<img src='".THEME."images/bullet2.gif' alt='' style='vertical-align: middle;' />
<b>{USERNAME}</b>
<div class='smalltext'>
{MESSAGE}
</div>
<br />";

?>