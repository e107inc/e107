<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	wanker theme file
|
|	©Ricky Rivera 2002
|	http://lsof.host.sk
|	Ricky12369@host.sk
|
|	Based off "wanker" from Open Source Web Design
|	http://www.oswd.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

// [theme]

$themename = "wanker";
$themeversion = "2.0";
$themeauthor = "Ricky";
$themedate = "30/07/02";
$themeinfo = "based on 'wanker' by deadbeat, compatible with e107 v5+";

// [layout]

$layout = "_default";

$HEADER = "
<br />
<center>
<table cellspacing='0' cellpadding='2' border='0' bgcolor='#ffffff' width='98%'><tr><td>
<table cellspacing='0' cellpadding='1' border='0' bgcolor='#5A6F5A' width='100%'><tr><td>
<table cellspacing='0' cellpadding='1' border='0' bgcolor='#7F907F' width='100%'><tr><td>
<table cellspacing='1' cellpadding='0' border='0' width='100%'>
<tr>
<td align='center'>
<table cellspacing='0' cellpadding='1' border='0' bgcolor='#7C8AA4' width='100%'>
		
<tr><td bgcolor='#5A6F5A' align='left'><h3>".SITENAME." // ".SITETAG."</h3></td></tr>
<tr><td bgcolor='#5A6F5A' align='right' class='small'><b>:.·.: :'': :·.: :·: :::. ::''&nbsp;&nbsp;</b></td></tr>
</table>
</td>
</tr>
<tr>
<td align='right'>
<table cellspacing='1' cellpadding='4' border='0' bgcolor='#5A6F5A'>
<tr>
{SITELINKS=flat}
</tr>
</table>	
</td>
</tr>
<tr>
<td>
<table cellspacing='1' cellpadding='2' border='0' bgcolor='#5A6F5A' WIDTH='100%'>
<tr>
<table bgcolor='#CCCCCC' cellspacing='0' cellpadding='0' width='100%' border='0'>
<td style='width:20%; vertical-align: top;'>
{MENU=1}
</td>
<td style='width:60%; vertical-align: top;'>";

$FOOTER = "
<br />
<div style='text-align:center'>
{SITEDISCLAIMER}
</div>
</td>
<td style='width:20%; vertical-align:top'>
{MENU=2}
</td>		
</tr></table>
<tr>
<td align='right' class='small' bgcolor='#5A6F5A' width='100%'>.:· 
{CUSTOM=quote}
</td>
</tr>	
</table>
</td>
</tr>
<tr>
<td align='right' class='smalltext'>design: <a href='http://www.oswd.org/userinfo.phtml?user=deadbeat' target='_blank'>wanker</a> by <a href='mailto:jeff@hype52.com' target='_blank'>deadbeat</a> // based on <a href='http://www.oswd.org/userinfo.phtml?user=whompy' target='_blank'>libra</a> by <a href='http://www.oswd.org/userinfo.phtml?user=whompy' target='_blank'>whompy</a> // <a href='http://www.oswd.org' target='_blank'>open source web design</a></td>
</tr>
</table>
</td></tr>
</table>
</td></tr></table>							
</td></tr></table>
</center>
<br />";

//	[tablestyle]

function tablestyle($caption, $text){
	if($caption != ""){
		echo"
<table cellpadding='0' cellspacing='2' border='0' width='100%'>
	<tr>
		<td valign='top' class='basic' width='100%'>
		<table cellspacing='0' cellpadding='1' border='0' bgcolor='#5A6F5A' width='100%'><tr><td>
		<table cellspacing='0' cellpadding='2' border='0' bgcolor='#ffffff' width='100%'><tr><td>
			<table cellspacing='2' cellpadding='2' border='0' bgcolor='#7F907F' width='100%'>
				<tr><td class='small' bgcolor='#7F907F'><b>".$caption." .:·</b></td></tr>
				<tr><td class='basic' bgcolor='#9FAC9F'>".$text."</td></tr>
			</table>
		</td></tr></table>							
		</td></tr></table>
	</tr>
</table>";
	}else{
		echo "
<table cellpadding='2' cellspacing='5' border='0'>
	<tr>
		<td valign='top' class='basic' width='100%'>
		<table cellspacing='0' cellpadding='1' border='0' bgcolor='#5A6F5A' width='100%'><tr><td>
		<table cellspacing='0' cellpadding='2' border='0' bgcolor='#ffffff' width='100%'><tr><td>
			<table cellspacing='2' cellpadding='2' border='0' bgcolor='#7F907F' width='100%'>
				<tr><td class='basic' bgcolor='#9FAC9F'>".$text."</td></tr>
			</table>
		</td></tr></table>							
		</td></tr></table>
	</tr>
</table>";
	}
}

//	[newsstyle]

$NEWSSTYLE = "
<table cellpadding='0' cellspacing='2' border='0' width='100%'>
<tr>
<td valign='top' class='basic' width='100%'>
<table cellspacing='0' cellpadding='1' border='0' bgcolor='#5A6F5A' width='100%'><tr><td>
<table cellspacing='0' cellpadding='2' border='0' bgcolor='#ffffff' width='100%'><tr><td>
<table cellspacing='2' cellpadding='2' border='0' bgcolor='#7F907F' width='100%'>
<tr><td class='small' bgcolor='#7F907F'><b>
{NEWSTITLE}
 .:·</b></td></tr>
<tr><td class='basic' bgcolor='#9FAC9F'>
{NEWSICON}
{NEWSBODY}
<br />
<div style='text-align:right'>
{NEWSAUTHOR}
 | 
{NEWSDATE}
 | 
{NEWSCOMMENTS}
{EMAILICON}
{PRINTICON}
{ADMINOPTIONS}
{EXTENDED}
</td></tr>
</table>
</td></tr></table>							
</td></tr></table>
</tr>
</table>";


define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Comments: ");
define("COMMENTOFFSTRING", "Comments are turned off for this item");
define("EXTENDEDSTRING", "Read more ...");
define("SOURCESTRING", "Source: ");
define("URLSTRING", "Link: ");
define("ICONMAIL", "iconmail.png");
define("ICONPRINT", "iconprint.png");

// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "<td bgcolor='#7F907F' class='small' align='center'>/");
define(LINKEND, "</td>");
define(LINKALIGN, "right");

?>