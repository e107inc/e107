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

global $sc_style;
global $pref, $comrow, $row2, $tp, $NEWIMAGE, $USERNAME, $RATING;

$sc_style['SUBJECT']['pre'] = "<b>";
$sc_style['SUBJECT']['post'] = "</b>";

$sc_style['USERNAME']['pre'] = "<b>";
$sc_style['USERNAME']['post'] = "</b>";

$sc_style['TIMEDATE']['pre'] = "";
$sc_style['TIMEDATE']['post'] = "";

$sc_style['AVATAR']['pre'] = "";
$sc_style['AVATAR']['post'] = "";

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



// from e107.org 
$sc_style['REPLY']['pre'] 				= "<span class='comment-reply'>";
$sc_style['REPLY']['post'] 				= "</span>";

$sc_style['COMMENTEDIT']['pre']  		= '<span class="comment-edit">';
$sc_style['COMMENTEDIT']['post'] 		= '</span>';

$sc_style['COMMENT_AVATAR']['pre']  	= '<div class="comment-avatar center">';
$sc_style['COMMENT_AVATAR']['post'] 	= '</div>';

$sc_style['SUBJECT_INPUT']['pre']		= ""; //COMLAN_324
$sc_style['SUBJECT_INPUT']['post']		= "";

$sc_style['AUTHOR_INPUT']['pre']		= ""; // COMLAN_16
$sc_style['AUTHOR_INPUT']['post']		= "";

$sc_style['COMMENT_INPUT']['pre']		= "";// COMLAN_8
$sc_style['COMMENT_INPUT']['post']		= "";

$sc_style['COMMENT_BUTTON']['pre']		= "<div id='commentformbutton'>";
$sc_style['COMMENT_BUTTON']['post']		= "</div>";

$sc_style['COMMENT_RATE']['pre']  		= '<div class="comment-rate">';
$sc_style['COMMENT_RATE']['post'] 		= '</div>';

$sc_style['USER_AVATAR']['pre']  		= '<div class="comment-avatar center">';
$sc_style['USER_AVATAR']['post'] 		= '</div>';




$COMMENT_TEMPLATE['FORM']			= "
	<div class='comment-box comment-box-form clearfix'>
		<div class='comment-box-left'>
		{USER_AVATAR}
		</div>
		<div class='comment-box-right' style='text-align:left'>
			<div class='P10'>
				{AUTHOR_INPUT}
				{COMMENT_INPUT}
				{COMMENT_BUTTON}
			</div>
		</div>
	</div>
	<div class='clear_b'><!-- --></div>"; 


$COMMENT_TEMPLATE['ITEM'] = '
	<div class="comment-box-left">
		{COMMENT_AVATAR}
	</div>

	<div class="comment-box-right">
		<div class="P10">

			<span class="comment-box-username">{USERNAME}</span>
			<span class="comment-box-date">{TIMEDATE}</span>
			
			<span class="comment-status">{COMMENT_STATUS}</span>
			<div class="comment-user-badge-bar">
				{COMMENT_RATE}{COMMENT_REPLY} {COMMENTEDIT} {COMMENT_MODERATE}			
			</div>

			<div class="clear_b H5"><!-- --></div>
			<div id="{COMMENT_ITEMID}-edit" contentEditable="false">{COMMENT}</div>
			
		</div>
	</div>';
	



$COMMENT_TEMPLATE['LAYOUT'] 		= '{COMMENTFORM}{COMMENTS}{MODERATE}';
										

?>