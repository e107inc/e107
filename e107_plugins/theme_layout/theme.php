<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

// [theme]

$themename = "Yourtheme";
$themeversion = "1.0";
$themeauthor = "you";
$themedate = "26/08/02";
$themeinfo = "";

define("THEME_DISCLAIMER", "<br />This is my new theme.</a>");

// [layout]
$layout = "_default";

// $HEADER and $FOOTER go here

//	[newsstyle]

// $NEWSSTYLE goes here

define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Read/Post Comment: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");
define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");

// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<img src='".THEME."images/bullet3.gif' alt='bullet' /> ");
define(LINKEND, "&nbsp;&nbsp;");
define(LINKALIGN, "center");


//	[tablestyle]
function tablestyle($caption, $text, $mode=""){
	if($caption != ""){
		echo "<div class='border'><div class='caption'><b>".$caption."</b></div>\n";
		if($text != ""){
			echo "\n<div class='bodytable'>".$text."</div>\n";
		}
		echo "</div>";
	}else{
		echo "<div class='border'><div class='bodytable'>".$text."</div></div><br />\n";
	}
	echo "<br />";
}

// [commentstyle]

// $COMMENTSTYLE goes here

//	[chatboxstyle]

// $CHATBOXSTYLE GOES HERE

// [pollstyle]

// $POLLSTYLE goes here

// [forumstyle]

// $FORUMSTYLE goes here

?>