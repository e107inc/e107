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
|     $Source: /cvs_backup/e107_0.7/e107_themes/e107/theme.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-10-04 21:27:27 $
|     $Author: loloirie $
+----------------------------------------------------------------------------+
*/

// [multilanguage]

if(file_exists(e_THEME."e107/languages/".e_LANGUAGE.".php")){
  require_once(e_THEME."e107/languages/".e_LANGUAGE.".php");
}else{
  require_once(e_THEME."e107/languages/English.php");
}

// [theme]

$themename = "e107";
$themeversion = "5.0";
$themeauthor = "jalist";
$themedate = "26/08/02";
$themeinfo = "compatible with e107 v5+";

// [layout]

$layout = "_default";
$admin_logo = "1";

$HEADER = "
<div style='text-align:center'>
<table style='width:100%' cellspacing='3'><tr><td colspan='3' style='text-align:left'>
{LOGO}
<br />
{SITETAG}
</td></tr><tr> <td style='width:15%; vertical-align: top;'>
{SETSTYLE=leftmenu}
{SITELINKS=menu}
{MENU=1}
</td><td style='width:70%; vertical-align: top;'>
";

$NEWSHEADER = "
<div style='text-align:center'>
<table style='width:100%' cellspacing='3'><tr><td colspan='3' style='text-align:left'>
{LOGO}
<br />
{SITETAG}
</td></tr><tr> <td style='width:15%; vertical-align: top;'>
{SETSTYLE=leftmenu}
{SITELINKS=menu}
{MENU=1}
</td><td style='width:70%; vertical-align: top;'>
<div style='text-align:center'>
<table style='width:95%'>
<tr>
<td style='width:50%; vertical-align:top'>
{NEWS_CATEGORY=1}
</td>
<td style='width:50%; vertical-align:top'>
{NEWS_CATEGORY=2}
</td>
</tr>
<tr>
<td colspan='2'>
<hr />
<table style='width:100%'>
<tr>
<td style='width:33%; vertical-align:top'>
{MENU=6}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=7}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=8}
</td>
</tr>
</table>
<hr />
</td>
</tr>

<tr>
<td style='width:50%; vertical-align:top'>
{NEWS_CATEGORY=3}
</td>
<td style='width:50%; vertical-align:top'>
{NEWS_CATEGORY=4}
</td>
</tr>


</table>
</div>
";



$FOOTER = <<<EOT
</td><td style='width:15%; vertical-align:top'>
{MENU=2}
</td></tr>
<tr>
<td colspan='3' style='text-align:center'>
{SITEDISCLAIMER}
</td>
</tr>
</table>
<table style='width:60%'>
<tr>
<td style='width:33%; vertical-align:top'>
{MENU=3}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=4}
</td>
<td style='width:33%; vertical-align:top'>
{MENU=5}
</td>
</tr>
</table></div>
EOT;



//        [newsstyle]

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
                {ADMINOPTIONS}
                {EXTENDED}
        </div>
</div>";


$NEWSLISTSTYLE = "
{NEWSICON}
<b>
{NEWSTITLE}
</b>
<div class='smalltext'>
{NEWSAUTHOR}
on
{NEWSDATE}
{NEWSCOMMENTS}
</div>
<hr />
";




define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", LAN_THEME_1);
define("COMMENTOFFSTRING", LAN_THEME_2);
define("EXTENDEDSTRING", LAN_THEME_3);


// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
//define(LINKSTART, "<img src='".THEME."images/bullet2.gif' alt='bullet' /> ");
define(LINKSTART, " ");
define(LINKEND, "<br />");
//define(LINKDISPLAY, 2);                        // 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//        [tablestyle]
function tablestyle($caption, $text, $mode=""){
//        echo "Style: ".$style.", Mode: ".$mode;
        if($mode == "mode2"){
                if($caption != ""){
                        echo "<div class='border'><div class='caption'>".$caption."</div></div>\n";
                        if($text != ""){
                                echo "\n<div class='bodytable'>".$text."</div>\n";
                        }
                }else{
                        echo "<div class='border'><div class='bodytable'>".$text."</div></div><br />\n";
                }
        }else{
                if($caption != ""){
                        echo "<div class='border'><div class='caption2'>".$caption."</div></div>";
                        if($text != ""){
                                echo "<div class='bodytable2'>".$text."</div><br />\n";
                        }
                }else{
                        echo "<div class='bodytable2'>".$text."</div><br />\n";
                }
        }
}

// [commentstyle]


$COMMENTSTYLE = "
<div style='text-align:center'>
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
{LEVEL}
{COMMENTS}
<br />
{JOINED}
<br />
{REPLY}
</span>
</td>
<td style='width:70%; vertical-align:top'>
{COMMENT}
</td>
</tr>
</table>
</div>
<br />";


//        [chatboxstyle]

$CHATBOXSTYLE = "
<div class='indent'>
<span class='smalltext'>...: <b>
{USERNAME}
</b> :...<br />
{TIMEDATE}
</span><br />
<div class='mediumtext' style='text-align:right'>
{MESSAGE}
</div>
</div>";

?>
