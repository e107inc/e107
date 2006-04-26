<?php
/*
+----------------------------------------------------------------------------+
|	e107 website system
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
|
|	$Source: /cvs_backup/e107_0.7/e107_themes/reline/theme.php,v $
|	$Revision: 1.14 $
|	$Date: 2006-04-26 13:41:54 $
|	$Author: sweetas $
|
+----------------------------------------------------------------------------+
|	Photo Credits
|
|	mosaic glass by 
|	-> by massimo bassi [maXb] ( http://www.robamia.com )
|
|	funky_box 1
|	-> by Andrzej Pobiedzinski [Sarej] ( http://www.imagecreators.olsztyn.pl )
|
|	Lime Light | Orange Splash
|	-> by Martin R.W [matchstick] ( http://www.match-stick.co.uk )
|
|	Hands 4: holding a poster | Paper clipped | Post-It | Bubbles 3
|	-> by Davide Guglielmo [brokenarts] ( http://www.broken-arts.com )
|
+----------------------------------------------------------------------------+
*/


// Protect the file from direct access
if (!defined('e107_INIT')) { exit; }


// Get language definition files for this theme
@include_once(e_THEME."reline/languages/".e_LANGUAGE.".php");
@include_once(e_THEME."reline/languages/English.php");


// Set theme info
$themename = "Reline";
$themeversion = "1.0";
$themeauthor = "SweetAs";
$themedate = "24/04/06";
$themeinfo = "";
$xhtmlcompliant = TRUE;	// If set to TRUE will display an XHTML compliant logo in theme manager
$csscompliant = TRUE;	// If set to TRUE will display a CSS compliant logo in theme manager


// Define which icon set to use.
// There are two possible values here 'dark' and 'lite'.
// If your theme has a light background then use 'lite' and vice versa for dark themes.
// Because IE does not yet support Alpha transparency with PNG24 images, all of e107's 
// icons have been saved with both a light and dark matte background (to prevent jagged 
// edges that you see when no matte is present).
// The IMODE is specifying which of the icon sets to use.
// IE7 will support Alpha transparent PNG's at which point we will create a third set
// of icons (using alpha transparency instead of a matte) and IMODE will gradually be 
// filtered out as this new set will work with all background colours.
// Uncomment the line below to define IMODE (remove the // ). Default is 'lite'.

// define("IMODE", "lite");


// Theme disclaimer is displayed in your site disclaimer appended to the site disclaimer text.
// Uncomment the line below to set a theme disclaimer (remove the // ).

// define("THEME_DISCLAIMER", "Example theme disclaimer text."); 


// Dont render core style sheet link.
// the contents of e107_files/e107.css have been copied to this themes style.css.
// By setting $no_core_css to TRUE below we can prevent the <link> tag 
// that would normally be generated in the <head> of the page from being outputted.
// This saves us a call by the browser to a stylesheet that we no longer need.

$no_core_css = TRUE;


// Output into <head> section.
// Anything echoed from within the theme_head() function gets outputted into the <head> of your page.
// Please note that you have other choices for <head> based javascript. You can create a theme.js file 
// in your themes folder and a link will automatically be generated to it in the <head> of your page.
// If you have javascript that is independant of the theme rather than use theme.js or the theme_head()
// function below, its advisable to instead create a new file e107_files/user.js, placing your script 
// into this, and as with theme.js, a link will automatically be generated to this file.
// Uncomment the following three lines to use.

//function theme_head() {
//	echo "<script></script>";
//}


// Header and footer templates for the body of your site.
// These are the header and footer that wraps the content of a page.
// Note that these are the templates for *inbetween* the <body> and </body> tags. 
// The rest of the output (eg. the <head> section of the page) are covered by 
// the header and footer template files in e107_themes/templates.
// These are called header_default.php and footer_default.php.
// If you wish to use your own versions of these files uncomment the line below (remove the // ) 
// and edit it so that the text string represents the postfix of the new set of files.
// eg. using the example text e107 will include these files: 
// e107_themes/templates/header_your_version.php and e107_themes/templates/footer_your_version.php 
// instead of the default:
// e107_themes/templates/header_default.php and e107_themes/templates/footer_default.php 
// Please note however that using non default core header and footer templates is not recommended 
// and that the flexibility of these files enables you to add and edit content to them without editing 
// the files directly. Documentation for this can be found on e107.org.

// $layout = '_your_version'; // uncomment this line (remove the // ) to use alternative template files.


// Main header
$HEADER = "<table class='top_section'>
<tr>
<td>
<img src='".e_IMAGE."advanced.png' style='width: 145px; height: 90px; display: block; margin-left: 4px' alt='' />
</td>

<td class='top_back'>
<img src='".THEME_ABS."images/logo.png' style='width: 347px; height: 106px; display: block' alt='' />
</td>
</tr>

<tr>
<td class='left_section'>

<table class='collapse' style='width: 100%'>
<tr>
<td style='background-color: #126CA3'>
{SETSTYLE=blue_menu}
{MENU=1}
</td></tr>

<tr><td style='vertical-align: top'>
{SETSTYLE=leftmenu}
{MENU=2}
<br />
</td></tr></table>

</td>

<td style='vertical-align: top'>

<table class='collapse' style='width: 100%'>
<tr>
<td class='blue_bar'>
{SEARCH}
</td>
</tr>

<tr>
<td style='padding-right: 5px; vertical-align: top'>

<div class='cube_container'>
<img class='cube_image' src='".THEME_ABS."images/cube.png' alt='' />
</div>

{SITELINKS_ALT=no_icons+noclick}
</td>
</tr>
</table>

{SETSTYLE=default}
<br />
<table class='collapse' style='width: 100%'>
<tr>
<td style='vertical-align: top; padding: 10px; width: 100%'>
{WMESSAGE}
";


// Main footer
$FOOTER = "</td>

<td class='right_section'>
{SETSTYLE=paperclip}
{MENU=3}
{SETSTYLE=rightmenu}
{MENU=4}
{SETSTYLE=post_it}
{MENU=5}
</td>
</tr>
</table>
<br />
</td>
</tr>
</table>

<div style='text-align:center; width: 749px'>
<br />
{SITEDISCLAIMER}
<br /><br />
</div>";

// Custom footer for pages with no right menu area. Uncomment the $CUSTOMPAGES line below the 
// footer to activate and use.

$CUSTOMFOOTER['No_Right_Menu_Area'] = "</td>
</tr>
</table>
<br />
</td>
</tr>
</table>

<div style='text-align:center; width: 749px'>
<br />
{SITEDISCLAIMER}
<br /><br />
</div>";


 // Uncomment the below line (remove the // ) and enter in the filenames (or part of) for 
 // those pages you wish to use the custom page layout defined above (seperate with spaces).

 // $CUSTOMPAGES['No_Right_Menu_Area'] = "forum.php forum_post.php forum_viewtopic.php";


// Define attributes associated with site links.

define('PRELINK', ''); // Prefixed to all links as a group
define('POSTLINK', ''); // Postfixed to all links as a group
define('LINKSTART', ''); // Prefixed to each indivdual link
define('LINKEND', ''); // Postfixed to each indivdual link
define('LINKDISPLAY', 1);
define('LINKALIGN', 'left');


// News style

$NEWSSTYLE = "<div><div class='main_caption'>{STICKY_ICON}{NEWSTITLE}</div>{NEWSIMAGE}{NEWSBODY}{EXTENDED}</div><br />

<div style='padding: 2px'>
<img src='".THEME_ABS."images/comments_16.png' style='width: 16px; height: 16px; vertical-align: middle' alt='' />&nbsp;
{NEWSCOMMENTS}{TRACKBACK}
</div>

<table class='news_info'>
<tr>
<td>
{NEWSICON}
</td>

<td style='width: 100%'>
".LAN_THEME_5." {NEWSAUTHOR} ".LAN_THEME_6." {NEWSDATE}
</td>

<td style='white-space: nowrap'>
{EMAILICON}{PRINTICON}{PDFICON}{ADMINOPTIONS}
</td>
</tr>
</table>
<br />";


// Define attributes associated with news style.

define('ICONMAIL', 'email_16.png');
define('ICONPRINT', 'print_16.png');
define('ICONSTYLE', 'border: 0px');
define('COMMENTOFFSTRING', LAN_THEME_1);
define('COMMENTLINK', LAN_THEME_2);
define('PRE_EXTENDEDSTRING', '<br /><br />[ ');
define('EXTENDEDSTRING', LAN_THEME_3);
define('POST_EXTENDEDSTRING', ' ]<br />');
define('TRACKBACKSTRING', LAN_THEME_4);
define('TRACKBACKBEFORESTRING', '&nbsp;&nbsp;|&nbsp;&nbsp;');


// Table style

function tablestyle($caption, $text, $mode){
	global $style;
	
	if ($style == 'blue_menu') 
	{
		echo "<div class='blue_menu'>";
		if (USER && $mode == 'login') 
		{
			echo "<img src='".THEME_ABS."images/loggedin.png' style='width: 32px; height: 32px; display: block' alt='' /><br />";
		}
		echo "<span style='font-size: 12px'>".$caption."</span><br /><br />".$text."<br /></div>";
	} 
	else if ($style == 'paperclip') 
	{
		echo "<div style='text-align: center; width: 160px; margin-left: auto; margin-right: auto'>
		<table class='collapse' style='width: 130px; height: 175px'><tr><td class='paperclip'>";
		if ($caption) 
		{
			echo "<div style='padding: 6px 0px; text-align: right'>".$caption."</div>";
		}
		echo $text."</td></tr></table></div>";
	} 
	else if ($style == 'post_it') 
	{
		echo "<div class='post_it_container'><div class='post_it_back'><div class='post_it_top'><div class='post_it_bottom'>
		<div style='padding-bottom: 5px'>".$caption."</div>".$text."</div></div></div></div>";
	} 
	else if ($style == 'leftmenu' || $style == 'rightmenu') 
	{
		echo "<table class='collapse' style='width: 100%'><tr><td style='padding: 7px'>
		<div class='menu_caption'>".$caption."</div>".$text."</td></tr></table>";
	}  
	else 
	{
		echo "<div><div class='main_caption'>".$caption."</div>".$text."</div><br />";
	}
	
}


// Comment post style
$sc_style['REPLY']['pre'] = "<tr><td class='forumheader'>";
$sc_style['REPLY']['post'] = "";

$sc_style['SUBJECT']['pre'] = "<td class='forumheader'>";
$sc_style['SUBJECT']['post'] = "</td></tr>";

$sc_style['COMMENTEDIT']['pre'] = "<tr><td class='forumheader' colspan='2' style='text-align: right'>";
$sc_style['COMMENTEDIT']['post'] = "</td></tr>";

$sc_style['JOINED']['post'] = "<br />";

$sc_style['LOCATION']['post'] = "<br />";

$sc_style['RATING']['post'] = "<br /><br />";

$sc_style['RATING']['post'] = "<br />";

$COMMENTSTYLE = "<div class='spacer' style='text-align:center'><table class='fborder' style='width: 95%'>
<tr>
<td class='fcaption' colspan='2'>".LAN_THEME_5." {USERNAME} ".LAN_THEME_6." {TIMEDATE}
</td>
</tr>
{REPLY}{SUBJECT}
<tr>
<td style='width: 20%; vertical-align: top' class='forumheader3'>
<div style='text-align: center'>
{AVATAR}
</div>
{LEVEL}<span class='smalltext'>{JOINED}{COMMENTS}{LOCATION}{IPADDRESS}</span>
</td>
<td style='width: 80%; vertical-align: top' class='forumheader3'>
{COMMENT}
{RATING}
{SIGNATURE} 
</td>
</tr>
{COMMENTEDIT}
</table>
</div>";


// Search shortcode style
$SEARCH_SHORTCODE = "<input class='tbox search' type='text' name='q' size='20' value='Search...' maxlength='50' onclick=\"this.value=''\" />
<input type='image' name='s' src='".THEME_ABS."images/search.png'  value='".LAN_180."' style='width: 16px; height: 16px; border: 0px; vertical-align: middle'  />";


// Chatbox post style
$CHATBOXSTYLE = "<br /><img src='".e_IMAGE."admin_images/chatbox_16.png' alt='' style='width: 16px; height: 16px; vertical-align: bottom' />
<b>{USERNAME}</b><br />{TIMEDATE}<br />{MESSAGE}<br />";


?>