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

$themename = "Blue Patriot";
$themeversion = "1.0";
$themeauthor = "Rufe";
$themedate = "28/10/2003";
$themeinfo = "";

define("THEME_DISCLAIMER", "<br /><i>Blue Patriot</i> theme by Rufe");

// [layout]


$layout = "_default";
//750
$HEADER = "
<div align=\"center\">

<table width=\"750\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
	<tr>
		<td style=\"background-image : url(".THEME."images/blue_patriot_01.jpg);\">
			<img src=\"".THEME."images/blue_patriot_01.jpg\" width=\"4\" height=\"26\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_02.jpg);\">
			<img src=\"".THEME."images/blue_patriot_02.jpg\" width=\"213\" height=\"26\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_03.jpg);\">
			<img src=\"".THEME."images/blue_patriot_03.jpg\" width=\"28\" height=\"26\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_04.jpg);\">
			<img src=\"".THEME."images/blue_patriot_04.jpg\" width=\"496\" height=\"26\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_05.jpg);\">
			<img src=\"".THEME."images/blue_patriot_05.jpg\" width=\"9\" height=\"26\" alt=\"\" /></td>
	</tr>
	<tr>
		<td style=\"background-image : url(".THEME."images/blue_patriot_06.jpg);\">
			<img src=\"".THEME."images/blue_patriot_06.jpg\" width=\"4\" height=\"25\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_07.jpg);\">
			<img src=\"".THEME."images/blue_patriot_07.jpg\" width=\"213\" height=\"25\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_08.jpg);\">
			<img src=\"".THEME."images/blue_patriot_08.jpg\" width=\"28\" height=\"25\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_09.jpg);\">
			<img src=\"".THEME."images/blue_patriot_09.jpg\" width=\"496\" height=\"25\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_10.jpg);\">
			<img src=\"".THEME."images/blue_patriot_10.jpg\" width=\"9\" height=\"25\" alt=\"\" /></td>
	</tr>
	<tr>
		<td style=\"background-image : url(".THEME."images/blue_patriot_11.jpg);\">
			<img src=\"".THEME."images/blue_patriot_11.jpg\" width=\"4\" height=\"55\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_12.jpg);\">
			<img src=\"".THEME."images/blue_patriot_12.jpg\" width=\"213\" height=\"55\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_13.jpg);\">
			<img src=\"".THEME."images/blue_patriot_13.jpg\" width=\"28\" height=\"55\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_14.jpg);\">
				</td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_15.jpg);\">
			<img src=\"".THEME."images/blue_patriot_15.jpg\" width=\"9\" height=\"55\" alt=\"\" /></td>
	</tr>
	<tr>
		<td style=\"background-image : url(".THEME."images/blue_patriot_16.jpg);\">
			<img src=\"".THEME."images/blue_patriot_16.jpg\" width=\"4\" height=\"26\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_17.jpg);\">
		<div align=center>
		{CUSTOM=search}
		</div>
		</td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_18.jpg);\">
			<img src=\"".THEME."images/blue_patriot_18.jpg\" width=\"28\" height=\"26\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_19.jpg);\">
		
	{SITELINKS}
		</td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_20.jpg);\">
			<img src=\"".THEME."images/blue_patriot_20.jpg\" width=\"9\" height=\"26\" alt=\"\" /></td>
	</tr>
	<tr>
		<td style=\"background-image : url(".THEME."images/blue_patriot_21.jpg);\">
			<img src=\"".THEME."images/blue_patriot_21.jpg\" width=\"4\" height=\"5\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_22.jpg);\">
			<img src=\"".THEME."images/blue_patriot_22.jpg\" width=\"213\" height=\"5\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_23.jpg);\">
			<img src=\"".THEME."images/blue_patriot_23.jpg\" width=\"28\" height=\"5\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_24.jpg);\">
			<img src=\"".THEME."images/blue_patriot_24.jpg\" width=\"496\" height=\"5\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_25.jpg);\">
			<img src=\"".THEME."images/blue_patriot_25.jpg\" width=\"9\" height=\"5\" alt=\"\" /></td>
	</tr>
	<tr>
		<td style=\"background-image : url(".THEME."images/blue_patriot_26.jpg);\">
			<img src=\"".THEME."images/blue_patriot_26.jpg\" width=\"4\" height=\"238\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_27.jpg);\" valign=\"top\">
			<br />
			{MENU=1}
			
			</td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_28.jpg);\">
			<img src=\"".THEME."images/blue_patriot_28.jpg\" width=\"28\" height=\"238\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_29.jpg);\" valign=\"top\" width='100%'>
<table width='95%' cellspacing='0' cellpadding='0' border='0'>
<tr>
    <td> 
{MENU=3}


";

$FOOTER = 
"

<br>
<br>
<br>
<br>

{MENU=2}

</td>
</tr>
</table>



</td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_30.jpg);\">
			<img src=\"".THEME."images/blue_patriot_30.jpg\" width=\"9\" height=\"238\" alt=\"\" /></td>
	</tr>
	<tr>
		<td style=\"background-image : url(".THEME."images/blue_patriot_31.jpg);\">
			<img src=\"".THEME."images/blue_patriot_31.jpg\" width=\"4\" height=\"9\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_32.jpg);\">
			<img src=\"".THEME."images/blue_patriot_32.jpg\" width=\"213\" height=\"9\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_33.jpg);\">
			<img src=\"".THEME."images/blue_patriot_33.jpg\" width=\"28\" height=\"9\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_34.jpg);\">
			<img src=\"".THEME."images/blue_patriot_34.jpg\" width=\"496\" height=\"9\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_35.jpg);\">
			<img src=\"".THEME."images/blue_patriot_35.jpg\" width=\"9\" height=\"9\" alt=\"\" /></td>
	</tr>
	<tr>
		<td style=\"background-image : url(".THEME."images/blue_patriot_36.jpg);\">
			<img src=\"".THEME."images/blue_patriot_36.jpg\" width=\"4\" height=\"19\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_37.jpg);\">
			
	
	
	
			
			</td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_38.jpg);\">
			<img src=\"".THEME."images/blue_patriot_38.jpg\" width=\"28\" height=\"19\" alt=\"\" /></td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_39.jpg);\">
			
			<div align='center'>
			{SITEDISCLAIMER}
			</div>
			</td>
		<td style=\"background-image : url(".THEME."images/blue_patriot_40.jpg);\">
			<img src=\"".THEME."images/blue_patriot_40.jpg\" width=\"9\" height=\"19\" alt=\"\" /></td>
	</tr>
</table>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />

</div>
";



$NEWSSTYLE = "

{NEWSICON}<br /><br /><br />
{NEWSTITLE}<br /><br />

Posted {NEWSDATE} by {NEWSAUTHOR}<br />
<br /><br />

{NEWSBODY}<br />
{EXTENDED}<br />
<br />
{EMAILICON}
{PRINTICON}
{ADMINOPTIONS}<br />
<br /><br /><br />
{NEWSCOMMENTS}<br />
";

define("ICONSTYLE", "float: left; border:0");
define("COMMENTLINK", "Read/Post Comment: ");
define("COMMENTOFFSTRING", "");

define("PRE_EXTENDEDSTRING", "<br /><br />[ ");
define("EXTENDEDSTRING", "Read the rest ...");
define("POST_EXTENDEDSTRING", " ]<br />");

define("PRE_SOURCESTRING", "<br />");
define("SOURCESTRING", "Source: ");
define("POST_SOURCESTRING", "<br />");

define("PRE_URLSTRING", "<br />");
define("URLSTRING", "Link: ");
define("POST_URLSTRING", "<br />");




// [linkstyle]

define(PRELINK, "");
define(POSTLINK, "");
define(LINKSTART, "");
define(LINKEND, "");
define(LINKDISPLAY, 1);			// 1 - along top, 2 - in left or right column
define(LINKALIGN, "left");


//	[tablestyle]


function tablestyle($caption, $text){
	global $style;
//echo "Mode: ".$style;

	echo "
<table border='0' cellpadding='0' cellspacing='0' width='100%'>
<tr><td class='bodytable' colspan=2><br /></td></tr>
	<tr>
		<td CLASS='blue' align='left' >".$caption."</td>
		<td CLASS='white' align='right' valign='top'>".$caption."</td>
	</tr>
	<tr><td class='bodytable' colspan=2><br /></td></tr>
	<tr>
		<td class='bodytable' colspan=2>
		
		".$text."
	
		</td>
	</tr>
</table>
";

}








// [commentstyle]

$COMMENTSTYLE = "
<table style='width:95%'>
<tr>
<td style='width:20%; vertical-align:top'>
<img src='".THEME."images/bullet2.gif' alt='bullet' /> 
<b>
{USERNAME}
</b>
<div class='spacer'>
{AVATAR}
</div>
<span class='smalltext'>
Comments: 
{COMMENTS}
<br />
Joined: 
{JOINED}
</span>
</td>
<td style='width:80%; vertical-align:top'>
<span class='smalltext'>
{TIMEDATE}
</span>
<br />
{COMMENT}
<br /><i><span class='smalltext'>Signature: 
{SIGNATURE}
</span></i>
</td>
</tr>
</table>
<br />";

//	[chatboxstyle]

$CHATBOXSTYLE = "
<span class='smalltext'><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b>
{USERNAME}
</b><br />
{TIMEDATE}
</span><br />
<div class='mediumtext'>
{MESSAGE}
</div>
";

?>