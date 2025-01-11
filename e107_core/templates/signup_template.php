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

$SIGNUP_WRAPPER['SIGNUP_DISPLAYNAME'] = 		"<div class='form-group row m-2'>
													<label class='col-sm-3 control-label' for='username'>{LAN=SIGNUP_89}{SIGNUP_IS_MANDATORY=true}<br /><span class='smalltext'>{LAN=SIGNUP_90}</span></label>
													<div class='col-sm-9'>{---}</div>
												</div>";

$SIGNUP_WRAPPER['SIGNUP_REALNAME'] = 			"<div class='form-group row m-2'>
													<label class='col-sm-3 control-label' for='realname'>{LAN=SIGNUP_91}{SIGNUP_IS_MANDATORY=realname}</label>
													<div class='col-sm-9'>{---}</div>
												</div>";

$SIGNUP_WRAPPER['SIGNUP_GDPR_INFO']             = "<div class='form-group row m-2 text-center'>{---}</div> ";


$SIGNUP_WRAPPER['SIGNUP_SIGNATURE'] = 			"<div class='form-group row m-2'>
													<label class='col-sm-3 control-label' for='signature'>{LAN=SIGNUP_93}{SIGNUP_IS_MANDATORY=signature}</label>
													<div class='col-sm-9'>{---}</div>
												</div>";

$SIGNUP_WRAPPER['SIGNUP_IMAGES'] = 				"<div class='form-group row m-2'>
													<label class='col-sm-3 control-label'for='avatar'>{LAN=SIGNUP_94}{SIGNUP_IS_MANDATORY=avatar}</label>
													<div class='col-sm-9'>{---}</div>
												</div>";

$SIGNUP_WRAPPER['SIGNUP_IMAGECODE'] = 			"<div class='form-group row m-2'>
													<label class='col-sm-3 control-label' for='code-verify'>" . e107::getSecureImg()->renderLabel()."{SIGNUP_IS_MANDATORY=true}</label>
													<div class='col-sm-9'>{---}</div>
												</div>";

$SIGNUP_WRAPPER['SIGNUP_LOGINNAME'] = 			"<div class='form-group row m-2'>
													<label class='col-sm-3 control-label' for='loginname'>{LAN=SIGNUP_81}{SIGNUP_IS_MANDATORY=true}</label>
													<div class='col-sm-9'>{---}</div>
												</div>";

$SIGNUP_WRAPPER['SIGNUP_HIDE_EMAIL'] = 			"<div class='form-group row m-2'>
													<label class='col-sm-3 control-label'>{LAN=USER_83}</label>
													<div class='col-sm-9'>{---}</div>
												</div>";

$SIGNUP_WRAPPER['SIGNUP_EMAIL_CONFIRM'] = 		"<div class='form-group row m-2'>
													<label class='col-sm-3 control-label' for='email-confirm'>{LAN=SIGNUP_39}{SIGNUP_IS_MANDATORY=true}</label>
													<div class='col-sm-9'>{---}</div>
												</div>";

$SIGNUP_WRAPPER['SIGNUP_XUP']                   = "<div class='text-center'>{---}
												<h2 class='signup-divider'><span>{LAN=SIGNUP_120}</span></h2></div>";

$SIGNUP_WRAPPER['SIGNUP_PASSWORD1'] = 			"<div class='form-group row m-2'>
													<label class='col-sm-3 control-label' for='password1'>{LAN=SIGNUP_83}{SIGNUP_IS_MANDATORY=true}</label>
													<div class='col-sm-9'>{---}</div>
												</div>";

$SIGNUP_WRAPPER['SIGNUP_PASSWORD2'] = 			"<div class='form-group row m-2'>
													<label class='col-sm-3 control-label' for='password2'>{LAN=SIGNUP_84}{SIGNUP_IS_MANDATORY=true}</label>
													<div class='col-sm-9'>{---}</div>
												</div>";

$SIGNUP_WRAPPER['SIGNUP_USERCLASS_SUBSCRIBE'] = "<div class='form-group row m-2 '>
													<label class='col-sm-3 control-label'>{LAN=SIGNUP_113}{SIGNUP_IS_MANDATORY=subscribe}</label>
													<div class='col-sm-9 checkbox'>{---}</div>
												</div>";
 


// Signup Template


$SIGNUP_TEMPLATE['start'] = "
	{SIGNUP_FORM_OPEN} {SIGNUP_ADMINOPTIONS} {SIGNUP_SIGNUP_TEXT}";


$SIGNUP_TEMPLATE['body'] = "
	{SIGNUP_XUP}
	<div id='default'>
		{SIGNUP_XUP_ACTION}
			{SIGNUP_DISPLAYNAME}
			{SIGNUP_LOGINNAME}
			{SIGNUP_REALNAME}
			<div class='form-group row m-2'>
				<label class='col-sm-3 control-label' for='password1'>{LAN=LAN_USER_60}{SIGNUP_IS_MANDATORY=email}</label>
				<div class='col-sm-9'>{SIGNUP_EMAIL}</div>
			</div>
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
			<div class='form-group row m-2 text-center'>
				{SIGNUP_BUTTON}
			</div>	 
	</div>
	{SIGNUP_FORM_CLOSE}";

$SIGNUP_TEMPLATE['end']                     = '';

$SIGNUP_TEMPLATE['coppa']                   = "{SIGNUP_COPPA_TEXT}<br /><br />
											<div style='text-align:center'><b>{LAN=LAN_SIGNUP_17}</b>
												{SIGNUP_COPPA_FORM}
											</div>";

$SIGNUP_TEMPLATE['coppa-fail']              = "<div class='alert alert-danger alert-block' style='text-align:center'>{LAN=LAN_SIGNUP_9}</div>";

$SIGNUP_TEMPLATE['extended-category']       = "
											<div class='form-group row m-2'>
												<div class='col-sm-9 col-md-offset-3'>{EXTENDED_CAT_TEXT}</div>
											</div>";

$SIGNUP_TEMPLATE['extended-user-fields']    = "
											<div class='form-group row m-2'>
												<label class='col-sm-3 control-label'>{EXTENDED_USER_FIELD_TEXT}{EXTENDED_USER_FIELD_REQUIRED}</label>
												<div class='col-sm-9'>{EXTENDED_USER_FIELD_EDIT}</div>
											</div>";