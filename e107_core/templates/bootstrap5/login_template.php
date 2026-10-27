<?php
// $Id$

if (!defined('e107_INIT')) { exit; }

// Bootstrap 5 version of the login page. Bootstrap 5 removed .form-group, so the spacing comes from the margin utilities.

$LOGIN_TEMPLATE['page']['header'] = "
	<div id='login-template'>
		<div class='center'>
			{LOGO: login}
		</div>";

$LOGIN_TEMPLATE['page']['body'] = '
		{LOGIN_TABLE_LOGINMESSAGE}
	<h2 class="form-signin-heading">{LAN=LOGIN_4}</h2><span>';

$LOGIN_WRAPPER['page']['LOGIN_TABLE_USERNAME'] = "<div class='form-group my-2'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_PASSWORD'] = "<div class='form-group my-2'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_SECIMG_SECIMG'] = "<div class='form-group my-2'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_SECIMG_TEXTBOC'] = "<div class='form-group my-2'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_SUBMIT'] = "<div class='form-group my-2'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_FOOTER_USERREG'] = "<div class='form-group my-2'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_LOGINMESSAGE'] = "<div class='alert alert-danger'>{---}</div>";

$LOGIN_TEMPLATE['page']['body'] .= '
	{LOGIN_TABLE_USERNAME}
	{LOGIN_TABLE_PASSWORD}
	{SOCIAL_LOGIN: size=3x}
		{LOGIN_TABLE_SECIMG_SECIMG} {LOGIN_TABLE_SECIMG_TEXTBOC}
	{LOGIN_TABLE_SUBMIT: class=btn btn-primary btn-lg w-100 button}

 ';

$LOGIN_TEMPLATE['page']['footer'] =  "
			<div class='login-page-footer'>
				<div class='login-page-signup-link'><p>{LOGIN_TABLE_SIGNUP_LINK}</p></div>
				<div class='login-page-fpw-link'><p>{LOGIN_TABLE_FPW_LINK}</p></div>
			</div>
	</div>";
