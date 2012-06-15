<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_themes/templates/comment_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
if (!defined("USER_WIDTH")){ define("USER_WIDTH", "width:100%"); }

global $sc_style, $comment_shortcodes;
global $pref, $comrow, $row2, $tp, $NEWIMAGE, $USERNAME, $RATING;

$sc_style['SUBJECT']['pre'] = "<b>";
$sc_style['SUBJECT']['post'] = "</b>";

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

/*
$COMMENTSTYLE = "
<table class='fborder' style='".USER_WIDTH."'>
<tr>
	<td colspan='2' class='forumheader'>
		{SUBJECT} {USERNAME} {TIMEDATE} {REPLY} {COMMENTEDIT}
	</td>
</tr>
<tr>
	<td style='width:30%; vertical-align:top;'>
		{AVATAR}<span class='smalltext'>{COMMENTS}{JOINED}</span>
	</td>
	<td style='width:70%; vertical-align:top;'>
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
*/

// from e107.org 


$sc_style['COMMENTEDIT']['pre']  		= '<div class="clear_b H10"><!-- --></div><div class="comments-box-reply smalltext f-right">';
$sc_style['COMMENTEDIT']['post'] 		= '</div>';

$sc_style['COMMENT_AVATAR']['pre']  	= '<div class="center">';
$sc_style['COMMENT_AVATAR']['post'] 	= '</div>';


$COMMENT_TEMPLATE['ITEM'] = '
	<div class="comments-box-left">
		{COMMENT_AVATAR}
	</div>

	<div class="comments-box-right">

		<div class="P10">

			<span class="comments-box-username">{USERNAME}</span>
			<span class="comments-box-date">{TIMEDATE}</span>
			<span class="comments-reply">{REPLY}</span>

			<div class="comments-user-badge-bar">

				{SUPPORT}

			</div>

			<div class="clear_b H5"><!-- --></div>

			{COMMENT}

			{COMMENTEDIT}

		</div>

	</div>';
	
$COMMENT_TEMPLATE['ITEM_START'] 	= '<div class="comments-box clearfix">';
$COMMENT_TEMPLATE['ITEM_END']		= '</div><div class="clear_b"><!-- --></div>';

$COMMENT_TEMPLATE['LAYOUT'] 		= '{COMMENTFORM}{COMMENTS}{MODERATE}';
										
$COMMENT_TEMPLATE['FORM']			= ""; //TODO 


	
/*
$COMMENTSTYLE = '

<div class="comments-box clearfix">

	<div class="comments-box-left">
		{COMMENT_AVATAR}
	</div>

	<div class="comments-box-right">

		<div class="P10">

			<span class="comments-box-username">{USERNAME}</span>
			<span class="comments-box-date">{TIMEDATE}</span>
			<span class="comments-reply">{REPLY}</span>

			<div class="comments-user-badge-bar">

				{SUPPORT}

			</div>

			<div class="clear_b H5"><!-- --></div>

			{COMMENT}

			{COMMENTEDIT}

		</div>

	</div>



';
*/

?>