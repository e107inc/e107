<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	default theme file														|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/

// [theme]

$themename = "e107";
$themeversion = "5.0";
$themeauthor = "jalist";
$themedate = "26/08/02";
$themeinfo = "compatible with e107 v5+";

// [layout]

$layout = "_default";
$admin_logo = "1";

$HEADER = 
"<div style=\"text-align:center\">
<table style=\"width:100%\" cellspacing=\"3\"><tr><td colspan=\"3\" style=\"text-align:left\">
{LOGO}
<br />
{SITETAG}
</td></tr><tr> <td style=\"width:15%; vertical-align: top;\">
{SETSTYLE=leftmenu}
{SITELINKS=menu}
{MENU=1}
</td><td style=\"width:70%; vertical-align: top;\">";

$FOOTER = 
"</td><td style=\"width:15%; vertical-align:top\">
{MENU=2}
</td></tr>
<tr>
<td colspan=\"3\" style=\"text-align:center\">
{SITEDISCLAIMER}
</td>
</tr>
</table>
<table style=\"width:60%\">
<tr>
<td style=\"width:33%; vertical-align:top\">
{MENU=3}
</td>
<td style=\"width:33%; vertical-align:top\">
{MENU=4}
</td>
<td style=\"width:33%; vertical-align:top\">
{MENU=5}
</td>
</tr>
</table></div>";



//	[newsstyle]

$NEWSSTYLE = "
<div class='border'>
	<div class='caption'>
		{NEWSTITLE}
	</div>
</div>
<div class='bodytable'>
{NEWSICON}
	<div style='text-align:justify'>
		{NEWSBODY}
		<br />
		{NEWSSOURCE}
		<br />
		{NEWSURL}
		<br />
	</div>
	<div style='text-align:center'>
		<hr />Category: 
		{NEWSCATEGORY}
		Posted by: 
		{NEWSAUTHOR}
		on
		{NEWSDATE}
		<br />
		{NEWSCOMMENTS}
		{EMAILICON}
		{PRINTICON}
		{EXTENDED}
	</div>
</div>";

define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Comments: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");
define("EXTENDEDSTRING", "Read more ...");
define("SOURCESTRING", "Source: ");
define("URLSTRING", "Link: ");


// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> ");
define(LINKEND, "<br />");
//define(LINKDISPLAY, 2);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//	[tablestyle]
function tablestyle($caption, $text, $mode=""){
//	echo "Style: ".$style.", Mode: ".$mode;
	if($mode == "mode2"){
		if($caption != ""){
			echo "<div class=\"border\"><div class=\"caption\">".$caption."</div></div>\n";
			if($text != ""){
				echo "\n<div class=\"bodytable\">".$text."</div>\n";
			}
		}else{
			echo "<div class=\"border\"><div class=\"bodytable\">".$text."</div></div><br />\n";
		}
	}else{
		if($caption != ""){
			echo "<div class=\"border\"><div class=\"caption2\">".$caption."</div></div>";
			if($text != ""){
				echo "<div class=\"bodytable2\">".$text."</div><br />\n";
			}
		}else{
			echo "<div class=\"bodytable2\">".$text."</div><br />\n";
		}
	}
}

// [commentstyle]

$COMMENTSTYLE = "
<table style=\"width:95%\">
<tr>
<td style=\"width:20%; vertical-align:top\">
<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> 
<b>
{USERNAME}
</b>
<div class=\"spacer\">
{AVATAR}
</div>
<span class=\"smalltext\">
Comments: 
{COMMENTS}
<br />
Joined: 
{JOINED}
</span>
</td>
<td style=\"width:80%; vertical-align:top\">
<span class=\"smalltext\">
{TIMEDATE}
</span>
<br />
{COMMENT}
<br /><i><span class=\"smalltext\">Signature: 
{SIGNATURE}
</span></i>
<br />
<div class=\"smalltext\">
{ADMINOPTIONS}
</div>
</td>
</tr>
</table>
<br />";

//	[chatboxstyle]

$CHATBOXSTYLE = "
<div class=\"indent\">
<span class=\"smalltext\">...: <b>
{USERNAME}
</b> :...<br />
{TIMEDATE}
</span><br />
<div class=\"mediumtext\" style=\"text-align:right\">
{MESSAGE}
</div>
<div class=\"smalltext\">
{ADMINOPTIONS}
</div></div>";

define(CB_STYLE, $CHATBOXSTYLE);

?>