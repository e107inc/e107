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

if (!defined('e107_INIT')) { exit; }
if (!defined("USER_WIDTH")){ define("USER_WIDTH", "width:100%"); }


$sc_style['SIGNUP_DISPLAYNAME']['pre'] = "
<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap' ><label for='username'>"
		.LAN_SIGNUP_89."{SIGNUP_IS_MANDATORY=true}<br /><span class='smalltext'>".LAN_SIGNUP_90."</span></label>
	</td>
	<td class='forumheader3' style='width:70%'>";
$sc_style['SIGNUP_DISPLAYNAME']['post'] = "
	</td>
</tr>";

$sc_style['SIGNUP_REALNAME']['pre'] = "
<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap'><label for='realname'>"
		.LAN_SIGNUP_91."{SIGNUP_IS_MANDATORY=realname}</label>
	</td>
	<td class='forumheader3' style='width:70%' >";
$sc_style['SIGNUP_REALNAME']['post'] = "
	</td>
</tr>";
$sc_style['SIGNUP_GDPR_INFO']['pre'] = "<tr style='vertical-align:top'><td class='forumheader' colspan='2'  style='text-align:center'>";
$sc_style['SIGNUP_GDPR_INFO']['post'] = "</td>
		</tr>";



if(!isset($SIGNUP_PASSWORD_LEN))
{
	$SIGNUP_PASSWORD_LEN = "
	<span class='smalltext'> (".LAN_SIGNUP_1." {$pref['signup_pass_len']} ".LAN_SIGNUP_2.")</span>";
}

if(!isset($SIGNUP_EXTENDED_USER_FIELDS))
{
	$SIGNUP_EXTENDED_USER_FIELDS	= "
	<tr>
		<td style='width:40%' class='forumheader3'>
			<label>{EXTENDED_USER_FIELD_TEXT}
			{EXTENDED_USER_FIELD_REQUIRED}</label>
		</td>
		<td style='width:60%' class='forumheader3'>
			{EXTENDED_USER_FIELD_EDIT}
		</td>
	</tr>";
}



$sc_style['SIGNUP_SIGNATURE']['pre'] = "
<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap;vertical-align:top' ><label for='signature'>".LAN_SIGNUP_93." {SIGNUP_IS_MANDATORY=signature}</label></td>
	<td class='forumheader3' style='width:70%'>";
	
$sc_style['SIGNUP_SIGNATURE']['post'] = "
	</td>
</tr>";
	
$sc_style['SIGNUP_IMAGES']['pre'] = "
<tr>
	<td class='forumheader3' style='width:30%; vertical-align:top;white-space:nowrap' ><label for='avatar'>".LAN_SIGNUP_94."{SIGNUP_IS_MANDATORY=avatar}</label></td>
	<td class='forumheader3' style='width:70%;vertical-align:top'>";
$sc_style['SIGNUP_IMAGES']['post'] = "
	</td>
</tr>";

$sc_style['SIGNUP_IMAGECODE']['pre'] = "
<tr>
	<td class='forumheader3' style='width:30%'><label for='code-verify'>".e107::getSecureImg()->renderLabel()."{SIGNUP_IS_MANDATORY=true}</label></td>
	<td class='forumheader3' style='width:70%'>";
$sc_style['SIGNUP_IMAGECODE']['post'] = "
	</td>
</tr>";

$sc_style['SIGNUP_LOGINNAME']['pre'] = "
<tr>
	<td class='forumheader3' style='width:30%'><label for='loginname'>".LAN_SIGNUP_81."{SIGNUP_IS_MANDATORY=true}</label></td>
	<td class='forumheader3' style='width:70%'>";
$sc_style['SIGNUP_LOGINNAME']['post'] = "
	</td>
</tr>";

$sc_style['SIGNUP_HIDE_EMAIL']['pre'] = "
<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap'><label>".LAN_USER_83."</label></td>
	<td class='forumheader3' style='width:70%'>";
$sc_style['SIGNUP_HIDE_EMAIL']['post'] = "
	</td>
</tr>";

$sc_style['SIGNUP_EMAIL_CONFIRM']['pre'] = "
<tr>
	<td class='forumheader3' style='width:30%;white-space:nowrap'><label for='email-confirm'>".LAN_SIGNUP_39."{SIGNUP_IS_MANDATORY=true}</label></td>
	<td class='forumheader3' style='width:70%'>";
$sc_style['SIGNUP_EMAIL_CONFIRM']['post'] = "
	</td>
</tr>";

$sc_style['SIGNUP_XUP']['pre'] = "<div class='center' style='display:block;padding:10px'>";
$sc_style['SIGNUP_XUP']['post'] = "<h2 class='signup-divider'><span>".LAN_SIGNUP_120."</span></h2></div>";

$sc_style['SIGNUP_PASSWORD1']['pre'] = "<tr>
				<td class='forumheader3' style='width:30%;white-space:nowrap'><label for='password1'>".LAN_SIGNUP_83."{SIGNUP_IS_MANDATORY=true}</label></td>
				<td class='forumheader3' style='width:70%'>";
$sc_style['SIGNUP_PASSWORD1']['post'] = "</td>
			</tr>";

$sc_style['SIGNUP_PASSWORD2']['pre'] = "<tr>
			<td class='forumheader3' style='width:30%;white-space:nowrap'><label for='password2'>".LAN_SIGNUP_84."{SIGNUP_IS_MANDATORY=true}</label></td>
			<td class='forumheader3' style='width:70%'>";
$sc_style['SIGNUP_PASSWORD2']['post'] = "</td>
		</tr>";

$sc_style['SIGNUP_USERCLASS_SUBSCRIBE']['pre'] = "<tr>
			<td class='forumheader3' style='width:30%;white-space:nowrap'><label>".LAN_SIGNUP_113."{SIGNUP_IS_MANDATORY=subscribe}</label></td>
			<td class='forumheader3' style='width:70%'>";
$sc_style['SIGNUP_USERCLASS_SUBSCRIBE']['post'] = "</td>
		</tr>";


if(!isset($COPPA_TEMPLATE))
{
	$COPPA_TEMPLATE = 
	LAN_SIGNUP_77." <a target='_blank' href='http://www.ftc.gov/privacy/coppafaqs.shtm'>".LAN_SIGNUP_14."</a>. "
	.LAN_SIGNUP_15." ".e107::getParser()->emailObfuscate(SITEADMINEMAIL,LAN_SIGNUP_14)." ".LAN_SIGNUP_16."<br />
	<br />
	<div style='text-align:center'><b>".LAN_SIGNUP_17."</b>
		{SIGNUP_COPPA_FORM}
	</div>";
}

if(!isset($COPPA_FAIL))
{
	$COPPA_FAIL = "<div style='text-align:center'>".LAN_SIGNUP_9."</div>";
}

//if(!defined($SIGNUP_TEXT))
{
	//$SIGNUP_TEXT =	$tp->parseTemplate("{SIGNUP_SIGNUP_TEXT}"); // .
	//LAN_SIGNUP_80." <b>".LAN_SIGNUP_29."</b><br /><br />".
	//LAN_SIGNUP_30."<br />".
	//LAN_SIGNUP_85;
}

if(!isset($SIGNUP_BEGIN))
{
	$SIGNUP_BEGIN = "
	{SIGNUP_FORM_OPEN} {SIGNUP_ADMINOPTIONS} {SIGNUP_SIGNUP_TEXT}";
}

if(!isset($SIGNUP_BODY))
{
	$SIGNUP_BODY = "
	{SIGNUP_XUP}
	<div id='default'>
		{SIGNUP_XUP_ACTION}
		<table class='table fborder' style='".USER_WIDTH."'>
			{SIGNUP_DISPLAYNAME}
			{SIGNUP_LOGINNAME}
			{SIGNUP_REALNAME}
			<tr>
				<td class='forumheader3' style='width:30%;white-space:nowrap'><label for='email'>".LAN_USER_60."{SIGNUP_IS_MANDATORY=email}</label></td>
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
					<input class='button btn btn-primary' type='submit' name='register' value=\"".LAN_SIGNUP_79."\" />
					<br />
				</td>
			</tr>
		</table>
	</div>
	{SIGNUP_FORM_CLOSE}";
}

if(!isset($SIGNUP_EXTENDED_CAT))
{
	$SIGNUP_EXTENDED_CAT = "
	<tr>
		<td colspan='2' class='forumheader'>
			{EXTENDED_CAT_TEXT}
		</td>	
	</tr>";
}

if(!isset($SIGNUP_END))
{
	$SIGNUP_END = '';
}

