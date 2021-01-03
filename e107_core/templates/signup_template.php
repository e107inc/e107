<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_themes/templates/signup_template.php,v $
 * $Revision: 12837 $
 * $Date: 2012-06-19 11:08:41 +0200 (di, 19 jun 2012) $
 * $Author: e107coders $
 */

if(!defined('e107_INIT'))
{
	exit;
}

// Shortcode Wrappers

$SIGNUP_WRAPPER['SIGNUP_DISPLAYNAME'] = "
<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap' ><label for='username'>"
	. LAN_SIGNUP_89 . "{SIGNUP_IS_MANDATORY=true}<br /><span class='smalltext'>" . LAN_SIGNUP_90 . "</span></label></td>
	<td class='forumheader3' style='width:70%'>{---}</td>
</tr>";

$SIGNUP_WRAPPER['SIGNUP_REALNAME'] = "
<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap'><label for='realname'>" . LAN_SIGNUP_91 . "{SIGNUP_IS_MANDATORY=realname}</label></td>
	<td class='forumheader3' style='width:70%' >{---}</td>
</tr>";


$SIGNUP_WRAPPER['SIGNUP_GDPR_INFO'] = "<tr style='vertical-align:top'><td class='forumheader' colspan='2'  style='text-align:center'>{---}</td></tr>";


$SIGNUP_WRAPPER['SIGNUP_SIGNATURE'] = "
<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap;vertical-align:top' ><label for='signature'>" . LAN_SIGNUP_93 . " {SIGNUP_IS_MANDATORY=signature}</label></td>
	<td class='forumheader3' style='width:70%'>{---}</td>
</tr>";

$SIGNUP_WRAPPER['SIGNUP_IMAGES'] = "
<tr>
	<td class='forumheader3' style='width:30%; vertical-align:top;white-space:nowrap' ><label for='avatar'>" . LAN_SIGNUP_94 . "{SIGNUP_IS_MANDATORY=avatar}</label></td>
	<td class='forumheader3' style='width:70%;vertical-align:top'>{---}</td>
</tr>";

$SIGNUP_WRAPPER['SIGNUP_IMAGECODE'] = "
<tr>
	<td class='forumheader3' style='width:30%'><label for='code-verify'>" . e107::getSecureImg()->renderLabel() . "{SIGNUP_IS_MANDATORY=true}</label></td>
	<td class='forumheader3' style='width:70%'>{---}</td>
</tr>";

$SIGNUP_WRAPPER['SIGNUP_LOGINNAME'] = "
<tr>
	<td class='forumheader3' style='width:30%'><label for='loginname'>" . LAN_SIGNUP_81 . "{SIGNUP_IS_MANDATORY=true}</label></td>
	<td class='forumheader3' style='width:70%'>{---}</td>
</tr>";

$SIGNUP_WRAPPER['SIGNUP_HIDE_EMAIL'] = "
<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap'><label>" . LAN_USER_83 . "</label></td>
	<td class='forumheader3' style='width:70%'>{---}</td>
</tr>";

$SIGNUP_WRAPPER['SIGNUP_EMAIL_CONFIRM'] = "
<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap'><label for='email-confirm'>" . LAN_SIGNUP_39 . "{SIGNUP_IS_MANDATORY=true}</label></td>
	<td class='forumheader3' style='width:70%'>{---}</td>
</tr>";

$SIGNUP_WRAPPER['SIGNUP_XUP'] = "<div class='center' style='display:block;padding:10px'>{---}
									<h2 class='signup-divider'><span>" . LAN_SIGNUP_120 . "</span></h2></div>";

$SIGNUP_WRAPPER['SIGNUP_PASSWORD1'] = "<tr>
				<td class='forumheader3' style='width:30%;white-space:nowrap'><label for='password1'>" . LAN_SIGNUP_83 . "{SIGNUP_IS_MANDATORY=true}</label></td>
				<td class='forumheader3' style='width:70%'>{---}</td>
			</tr>";

$SIGNUP_WRAPPER['SIGNUP_PASSWORD2'] = "<tr>
			<td class='forumheader3' style='width:30%;white-space:nowrap'><label for='password2'>" . LAN_SIGNUP_84 . "{SIGNUP_IS_MANDATORY=true}</label></td>
			<td class='forumheader3' style='width:70%'>{---}</td>
		</tr>";

$SIGNUP_WRAPPER['SIGNUP_USERCLASS_SUBSCRIBE'] = "<tr>
			<td class='forumheader3' style='width:30%;white-space:nowrap'><label>" . LAN_SIGNUP_113 . "{SIGNUP_IS_MANDATORY=subscribe}</label></td>
			<td class='forumheader3' style='width:70%'>{---}</td>
		</tr>";


// Template v2.x spec.


$SIGNUP_TEMPLATE['start'] = "
	{SIGNUP_FORM_OPEN} {SIGNUP_ADMINOPTIONS} {SIGNUP_SIGNUP_TEXT}";


$SIGNUP_TEMPLATE['body'] = "
	{SIGNUP_XUP}
	<div id='default'>
		{SIGNUP_XUP_ACTION}
		<table class='table fborder' >
			{SIGNUP_DISPLAYNAME}
			{SIGNUP_LOGINNAME}
			{SIGNUP_REALNAME}
			<tr>
				<td class='forumheader3' style='width:30%;white-space:nowrap'><label for='email'>" . LAN_USER_60 . "{SIGNUP_IS_MANDATORY=email}</label></td>
				<td class='forumheader3' style='width:70%'>
					{SIGNUP_EMAIL}
				</td>
			</tr>
			{SIGNUP_EMAIL_CONFIRM}
			{SIGNUP_PASSWORD1}
			{SIGNUP_PASSWORD2}
			{SIGNUP_HIDE_EMAIL}
			{SIGNUP_USERCLASS_SUBSCRIBE}
			{SIGNUP_EXTENDED_USER_FIELDS}
			{SIGNUP_SIGNATURE}
			{SIGNUP_IMAGES}
			{SIGNUP_IMAGECODE}
			{SIGNUP_GDPR_INFO}
			<tr style='vertical-align:top'>
				<td class='forumheader' colspan='2'  style='text-align:center'>
					<input class='button btn btn-primary' type='submit' name='register' value=\"{LAN=LAN_SIGNUP_79}\" />
					<br />
				</td>
			</tr>
		</table>
	</div>
	{SIGNUP_FORM_CLOSE}";


$SIGNUP_TEMPLATE['end']         = '';


$SIGNUP_TEMPLATE['coppa']       = "{SIGNUP_COPPA_TEXT}<br /><br />
								<div style='text-align:center'><b>{LAN=LAN_SIGNUP_17}</b>
									{SIGNUP_COPPA_FORM}
								</div>";

$SIGNUP_TEMPLATE['coppa-fail'] = "<div class='alert alert-danger alert-block' style='text-align:center'>{LAN=LAN_SIGNUP_9}</div>";


$SIGNUP_TEMPLATE['extended-category'] = "
	<tr>
		<td colspan='2' class='forumheader'>
			{EXTENDED_CAT_TEXT}
		</td>	
	</tr>";


$SIGNUP_TEMPLATE['extended-user-fields'] = "
	<tr>
		<td style='width:40%' class='forumheader3'>
			<label>{EXTENDED_USER_FIELD_TEXT}
			{EXTENDED_USER_FIELD_REQUIRED}</label>
		</td>
		<td style='width:60%' class='forumheader3'>
			{EXTENDED_USER_FIELD_EDIT}
		</td>
	</tr>";