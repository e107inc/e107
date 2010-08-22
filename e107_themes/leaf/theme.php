<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

// [multilanguage]
include_lan(e_THEME."leaf/languages/".e_LANGUAGE.".php");

// [theme]
$themename = "Leaf";
$themeversion = "1.0";
$themeauthor = "William Moffett [que]";
$themeemail = "que@e107.net";
$themewebsite = "http://e107themes.com";
$themedate = "04/23/05";
$themeinfo = "'Leaf' by <a href='http://e107themes.com' rel='external'>Que</a>, based on the nucleus cms theme by Ivan Fong aka <a href='http://www.stanch.net/'>Stanch</a>.";
$xhtmlcompliant = TRUE;
$csscompliant = TRUE;
$no_core_css = TRUE;
$admin_logo = "1";
$logo = rand(1, 4);

// [layout]
$layout = "_default";

// [theme settings]
define("STANDARDS_MODE", TRUE);
define("IMODE", "lite");
define("THEME_DISCLAIMER", "<br /><i>".$themeinfo."</i>");

// [page defines used for css controll on per page basis]
define("e_PAGECLASS", str_replace(substr(strrchr(e_PAGE, "."), 0), "", e_PAGE));

// [navigation]
$register_sc[] = 'UL';

// [credit links]
$register_sc[] = 'LINKS';

// [colorstyle] Used for sidbar menus and forum header background color custimization.
$colorstyle ="E2EDF0";


// [header function]
function theme_head() {
	global $logo, $colorstyle;
	return "<link rel='alternate stylesheet' type='text/css' href='".THEME_ABS."style.css' title='Small' />
	<link rel='alternate stylesheet' type='text/css' href='".THEME_ABS."fontstyles/medium.css' title='Medium' />
	<link rel='alternate stylesheet' type='text/css' href='".THEME_ABS."fontstyles/large.css' title='Large' />
	<style type='text/css'>
	#header{
		position: relative;
		width: 700px;
		height: 151px;
		margin: auto;
		background: url(".THEME_ABS."images/01_header0".$logo.".jpg) no-repeat;
	}
	/* Sidbar menu content styles */
	.loginform, .searchform, .chatboxform, .onlineform{
		background-color: #".$colorstyle.";
	}
	.defaultform{
		background-color: #".$colorstyle.";
	}
	.forumheader, .forumheader4, .finfobar {
		background-color: #".$colorstyle.";
	}
	</style>";
}


$HEADER = "
<div id='header'><!--Start Header-->
  <h1><a href='".SITEURL."' title='{SITENAME} home page' accesskey='0'>{SITENAME}</a></h1>
  <div id='navigation'>
    <h3 class='hidden'>Navigation</h3>
    {UL}
  </div>
  <div id='fontcontrol'>
    <h3 class='hidden'>Adjust font size:</h3>
    <ul>
      <li class='font1'><a href='#' onclick=\"setActiveStyleSheet('Small'); return false;\" title='Small' accesskey='S'><i>Small</i></a></li>
      <li class='font2'><a href='#' onclick=\"setActiveStyleSheet('Medium'); return false;\" title='Medium' accesskey='M'><i>Medium</i></a></li>
      <li class='font3'><a href='#' onclick=\"setActiveStyleSheet('Large'); return false;\" title='Large' accesskey='L'><i>Large</i></a></li>
    </ul>
  </div>
</div><!-- Close Header-->
<div id='wrapper'><!--Start Wrapper-->
<div id='container'><!--Start Container-->
  <div id='content'><!--Start Content-->
    <div class='contentdiv'><!--Start Contentdiv-->
        <div class='div".e_PAGECLASS."'>
        <!--Database Generated Content-->
	".(e_PAGECLASS == "news" ? "<h2>".LAN_THEME_7."</h2>" : "")."";


$FOOTER = "<!--End Database Generated Content-->
      </div><!--Close Div pageclass-->
    </div><!--Close Contentdiv-->
  </div><!--Close Content-->
</div><!--Close Container-->

<div id='sidebar'>
  <div class='sidebardiv'>
{SETSTYLE=sidebar}
<!-- Menu1 -->
{MENU=1}
<!-- End Menu1 -->
<!-- Menu2 -->
{MENU=2}
<!-- End Menu2 -->
{SETSTYLE}
{LINKS}
  </div><!-- Close sidebardiv -->
</div><!-- Close sidebar_full -->
<div class='clearing'>&nbsp;</div>
</div><!--Close Wrapper-->
<div id='footer'>
<div id='credits'>{SITEDISCLAIMER}<br />{THEME_DISCLAIMER}</div>
<!--End notes/Credits-->
</div>
<!--Close the tags like a good code monkey ;)-->";

$CUSTOMHEADER = "
<div id='header'><!--Start Header-->
 <h1><a href='".SITEURL."' title='{SITENAME} : home page' accesskey='0'>{SITENAME}</a></h1>
  <div id='navigation'>
    <h3 class='hidden'>Navigation</h3>
    {UL}
  </div>
  <div id='fontcontrol'>
    <h3 class='hidden'>Adjust font size:</h3>
    <ul>
      <li class='font1'><a href='#' onclick=\"setActiveStyleSheet('Small'); return false;\" title='Small' accesskey='S'><i>Small</i></a></li>
      <li class='font2'><a href='#' onclick=\"setActiveStyleSheet('Medium'); return false;\" title='Medium' accesskey='M'><i>Medium</i></a></li>
      <li class='font3'><a href='#' onclick=\"setActiveStyleSheet('Large'); return false;\" title='Large' accesskey='L'><i>Large</i></a></li>
    </ul>
  </div>
</div><!-- Close Header-->
<div id='wrapper'><!--Start Wrapper-->
<div id='container_full'><!--Start Container-->
  <div id='content_full'><!--Start Content-->
    <div class='contentdiv'><!--Start Contentdiv-->
        <div class='div".e_PAGECLASS."'><!--Start Div pageclass-->
        <!--Database Generated Content-->";


$CUSTOMFOOTER = "<!--End Database Generated Content-->
      </div><!--Close Div pageclass-->
    </div><!--Close Contentdiv-->
  </div><!--Close Content-->
</div><!--Close Container-->
<div class='clearing'>&nbsp;</div>
</div><!--Close Wrapper-->
<div id='footer'>
<div id='credits'>{SITEDISCLAIMER}<br />{THEME_DISCLAIMER}</div>
<!--End notes/Credits-->
</div>
<!--Close the tags like a good code monkey ;)-->";

$CUSTOMPAGES = "content_manager.php signup.php fpw.php forum.php forum_viewforum.php forum_viewtopic.php theme.php usersettings.php submitnews.php";

// [newsstyle]
function news_style($news) {

	$mydate  = strftime("%d/%m :", $news['news_datestamp']);
	$NEWSSTYLE = "<!-- news item --><div class='contentbody'>
	        <h3 class='news'>$mydate&nbsp; {NEWSTITLE}</h3>
	{NEWSICON}&nbsp;{STICKY_ICON}&nbsp;
	{NEWSBODY}
	{EXTENDED}
	        <br /><br />
	        <div class='itemdetails'>
	          <span class='item1'>{NEWSAUTHOR}</span>&nbsp;
	          <span class='item2'>{NEWSCATEGORY}</span>&nbsp;
	          <span class='item3'>{NEWSCOMMENTS}&nbsp;</span>
	          <span class='item4'>{EMAILICON}&nbsp;</span>
	          <span class='item5'>{PRINTICON}&nbsp;</span>
	          <span class='item5'>{PDFICON}&nbsp;</span>
	        </div>
	        </div><!-- end news item -->";
	return $NEWSSTYLE;
}

// [newsliststyle]
$NEWSLISTSTYLE = "";

define("ICONSTYLE", "border:0");
define("COMMENTLINK", LAN_THEME_1);
define("COMMENTOFFSTRING", LAN_THEME_2);
define("EXTENDEDSTRING", LAN_THEME_4);

// [linkstyle]
define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "");
define(LINKEND, "");
define(LINKDISPLAY, "");  // 1 - along top, 2 - in left or right column

// [tablestyle]
function tablestyle($caption, $text, $mode=""){
	global $style;
	if(ADMIN){
        	// echo "Style: ".$style.", Mode: ".$mode;
	}
	if($style == "sidebar"){  // sidebar styles

		echo "<div class='sidebarbody'>";

		if($mode){
		        if($caption != ""){
		                echo "<h3 class='".$mode."'>".$caption."</h3>";
		                if($text != ""){
		                        echo "<div class='sidebarin'><div class='defaultform'>".$text."</div></div>\n";
		                }
		        }else{
		                echo "<div class='sidebarin'><div class='defaultform'>".$text."</div></div>\n";
		        }
		}else{
			if($caption != ""){
		                echo "<h3>".$caption."</h3>";
		                if($text != ""){
		                	echo "<div class='sidebarin'><div class='defaultform'>".$text."</div></div>\n";
		                }
		        }else{
		                echo "<div class='sidebarin'><div class='defaultform'>".$text." </div></div>\n";
		        }
		}
		echo "</div>\n";
	}else{
		echo "<h2>".$caption."</h2>
		<div class='contentbody'>".$text."</div>";
 	}
}

// [commentstyle]
$COMMENTSTYLE = "";

// [chatboxstyle]
$CHATBOXSTYLE = "";

?>