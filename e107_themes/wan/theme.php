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

<div>
<table cellspacing='0' cellpadding='2' class='bclr1'><tr><td>
<table cellspacing='0' cellpadding='1' class='bclr2a'><tr><td>
<table cellspacing='0' cellpadding='1' class='bclr3'><tr><td>
<table cellspacing='1' cellpadding='0' class='mw1'>
<tr>
<td style='text-align: center'>
<table cellspacing='0' cellpadding='1' class='bclr4'>
		
<tr><td class='tclr1'><h3>".SITENAME." // ".SITETAG."</h3></td></tr>
<tr><td class='tclr2'><b>:.·.: :'': :·.: :·: :::. ::''&nbsp;&nbsp;</b></td></tr>
</table>
</td>
</tr>
<tr>
<td style='text-align: right'>
<table cellspacing='1' cellpadding='4' class='bclr2'>
<tr>
{SITELINKS=flat}
</tr>
</table>	
</td>
</tr>
<tr>
<td>
<table cellspacing='1' cellpadding='2' class='bclr2a'>
<tr><td>
<table cellspacing='0' cellpadding='0' class='bclr5'>
<tr>
<td style='width:20%; vertical-align: top;'>
{MENU=1}
</td>
<td style='width:60%; vertical-align: top;'>";

$FOOTER = "

<div style='text-align:center'>
{SITEDISCLAIMER}
</div>
</td>
<td style='width:20%; vertical-align:top'>
{MENU=2}
</td>		
</tr></table>
</td></tr>
<tr>
<td class='bclr2a'>.:· 
{CUSTOM=quote}
</td>
</tr>	
</table>
</td>
</tr>
<tr>
<td class='smalltext'>design: <a href='http://www.oswd.org/userinfo.phtml?user=deadbeat' rel='external'>wanker</a> by <a href='mailto:jeff@hype52.com' rel='external'>deadbeat</a> // based on <a href='http://www.oswd.org/userinfo.phtml?user=whompy' rel='external'>libra</a> by <a href='http://www.oswd.org/userinfo.phtml?user=whompy' rel='external'>whompy</a> // <a href='http://www.oswd.org' rel='external'>open source web design</a></td>
</tr>
</table>
</td></tr>
</table>
</td></tr></table>							
</td></tr></table>
</div>
";

//	[tablestyle]

function tablestyle($caption, $text){
	if($caption != ""){
		echo"
<table cellpadding='0' cellspacing='2' class='base'>
	<tr>
		<td class='basic'>
		<table cellspacing='0' cellpadding='1' class='bclr2a'><tr><td>
		<table cellspacing='0' cellpadding='2' class='bclr1a'><tr><td>
			<table cellspacing='2' cellpadding='2' class='bclr3'>
				<tr><td class='tclr3a'><b>".$caption." .:·</b></td></tr>
				<tr><td class='tclr4'>".$text."</td></tr>
			</table>
		</td></tr></table>							
		</td></tr></table>
	</td></tr>
</table>";
	}else{
		echo "
<table cellpadding='2' cellspacing='5' style='border: 0px'>
	<tr>
		<td class='basic'>
		<table cellspacing='0' cellpadding='1' class='bclr2a'><tr><td>
		<table cellspacing='0' cellpadding='2' class='bclr1a'><tr><td>
			<table cellspacing='2' cellpadding='2' class='bclr3'>
				<tr><td class='tclr4'>".$text."</td></tr>
			</table>
		</td></tr></table>							
		</td></tr></table>
	</td></tr>
</table>";
	}
}

//	[newsstyle]

$NEWSSTYLE = "
<table cellpadding='0' cellspacing='2' class='base'>
<tr>
<td class='basic'>
<table cellspacing='0' cellpadding='1' class='bclr2a'><tr><td>
<table cellspacing='0' cellpadding='2' class='bclr1a'><tr><td>
<table cellspacing='2' cellpadding='2' class='bclr3'>
<tr><td class='tclr3'><b>
{NEWSTITLE}
 .:·</b></td></tr>
<tr><td class='tclr4'>
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
</div>
</td></tr>
</table>
</td></tr></table>							
</td></tr></table>
</td></tr>
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
define(LINKSTART, "<td class='bclr3'>/");
define(LINKEND, "</td>");
define(LINKALIGN, "right");

?>