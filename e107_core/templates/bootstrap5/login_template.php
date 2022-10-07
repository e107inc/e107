<?php
// $Id$

if (!defined('e107_INIT')) { exit; }


// Starter for v2. - Bootstrap 
$LOGIN_TEMPLATE['page']['header'] = "
	<div id='login-template'>
		<div class='m-auto'>
			{LOGO: login}
		</div>";

$LOGIN_TEMPLATE['page']['body'] = '
		{LOGIN_TABLE_LOGINMESSAGE}
        <h2 class="form-signin-heading">{LAN=LOGIN_4}</h2>';
	if (e107::pref('core', 'password_CHAP') == 2)
	{
		$LOGIN_TEMPLATE['page']['body'] .= "
    	<div style='text-align: center' id='nologinmenuchap'>"."Javascript must be enabled in your browser if you wish to log into this site"."
		</div>
    	<span style='display:none' id='loginmenuchap'>";
	}
	else
	{
	  $LOGIN_TEMPLATE['page']['body'] .= "<span>";
	}

$LOGIN_WRAPPER['page']['LOGIN_TABLE_USERNAME'] = '<div class="row my-3 align-items-center">{---}</div>';
$LOGIN_WRAPPER['page']['LOGIN_TABLE_PASSWORD'] = '<div class="row my-3 align-items-center">{---}</div>';
$LOGIN_WRAPPER['page']['LOGIN_TABLE_SECIMG_SECIMG'] = '<div class="row my-3 align-items-center">{---}</div>';
$LOGIN_WRAPPER['page']['LOGIN_TABLE_SECIMG_TEXTBOC'] = '<div class="row my-3 align-items-center">{---}</div>';
$LOGIN_WRAPPER['page']['LOGIN_TABLE_REMEMBERME'] = '<div class="row my-3 align-items-center">{---}</div>';
$LOGIN_WRAPPER['page']['LOGIN_TABLE_SUBMIT'] = '<div class="row my-3 align-items-center">{---}</div>';
$LOGIN_WRAPPER['page']['LOGIN_TABLE_FOOTER_USERREG'] = '<div class="row my-3 align-items-center">{---}</div>';
$LOGIN_WRAPPER['page']['LOGIN_TABLE_LOGINMESSAGE'] = "<div class='alert alert-danger'>{---}</div>";


// $LOGIN_WRAPPER['page']['LOGIN_TABLE_FPW_LINK'] = '<div class="row my-3 align-items-center">{---}</div>';

$LOGIN_TEMPLATE['page']['body'] .= '
        {LOGIN_TABLE_USERNAME}
        {LOGIN_TABLE_PASSWORD}
        {SOCIAL_LOGIN: size=3x}
		{LOGIN_TABLE_SECIMG_SECIMG} {LOGIN_TABLE_SECIMG_TEXTBOC}
        {LOGIN_TABLE_REMEMBERME}
        {LOGIN_TABLE_SUBMIT}

 ';

$LOGIN_TEMPLATE['page']['footer'] =  "
			<div class='login-page-footer'>
				<div class='login-page-signup-link'><p>{LOGIN_TABLE_SIGNUP_LINK}</p></div>
				<div class='login-page-fpw-link'><p>{LOGIN_TABLE_FPW_LINK}</p></div>
			</div>
	</div>";
	



