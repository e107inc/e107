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
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/comment_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-14 16:02:49 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

global $sc_style, $comment_shortcodes;
global $pref, $comrow, $tp, $NEWIMAGE, $USERNAME, $RATING;

$sc_style['SUBJECT']['pre'] = "";
$sc_style['SUBJECT']['post'] = "";

$sc_style['USERNAME']['pre'] = "<b>";
$sc_style['USERNAME']['post'] = "</b>";

$sc_style['TIMEDATE']['pre'] = "";
$sc_style['TIMEDATE']['post'] = "";

$sc_style['REPLY']['pre'] = "";
$sc_style['REPLY']['post'] = "";

$sc_style['AVATAR']['pre'] = "<div class='spacer'>";
$sc_style['AVATAR']['post'] = "</div>";

$sc_style['COMMENTS']['pre'] = "";
$sc_style['COMMENTS']['post'] = "<br />";

$sc_style['JOINED']['pre'] = "";
$sc_style['JOINED']['post'] = "<br />";

$sc_style['COMMENT']['pre'] = "";
$sc_style['COMMENT']['post'] = "<br />";

$sc_style['RATING']['pre'] = "";
$sc_style['RATING']['post'] = "<br />";

$sc_style['IPADDRESS']['pre'] = "";
$sc_style['IPADDRESS']['post'] = "<br />";

$sc_style['LEVEL']['pre'] = "";
$sc_style['LEVEL']['post'] = "<br />";

$sc_style['LOCATION']['pre'] = "";
$sc_style['LOCATION']['post'] = "<br />";

$sc_style['SIGNATURE']['pre'] = "";
$sc_style['SIGNATURE']['post'] = "<br />";


$COMMENTSTYLE = "
<table class='fborder' style='width:100%; border:1px solid #000;' border='0'>
<tr>
	<td colspan='2' class='forumheader'>
		{SUBJECT} {USERNAME} {TIMEDATE} {REPLY}
	</td>
</tr>
<tr>
	<td style='width:30%; vertical-align:top'>
		{AVATAR}<span class='smalltext'>{COMMENTS}{JOINED}</span>
	</td>
	<td style='width:70%; vertical-align:top'>
		{COMMENT}
		{RATING}
		{IPADDRESS}
		{LEVEL}
		{LOCATION}
		{SIGNATURE}
	</td>
</tr>
</table>
<br />";


?>