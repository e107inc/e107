<?php
// $Id$

if (!defined('e107_INIT')) { exit; }
/*
// ##### LOGIN HEADER TABLE -----------------------------------------------------------------------
if(!isset($LOGIN_TABLE_HEADER)) //FIXME - use {LOGO} . 
{
	$LOGIN_TABLE_HEADER .= "
	<div style='width:100%;text-align:center; margin-left: auto;margin-right: auto'><br />
		<div style='text-align:center;width:70%;margin-left: auto;margin-right: auto'>
			{LOGO=login}";
}

// ##### LOGIN TABLE -----------------------------------------------------------------------------
if(!isset($LOGIN_TABLE))
{
		$LOGIN_TABLE = "";
  if($LOGIN_TABLE_LOGINMESSAGE != "")
  {
				$LOGIN_TABLE .= "<div style='text-align:center'>{LOGIN_TABLE_LOGINMESSAGE}</div>";
		}
  
  if (($pref['user_tracking'] == "session") && varset($pref['password_CHAP'],0))
  {
	if ($pref['password_CHAP'] == 2)
	{
		$LOGIN_TABLE .= "
    	<div style='text-align: center' id='nologinmenuchap'>"."Javascript must be enabled in your browser if you wish to log into this site"."
		</div>
    	<div style='text-align: center; display:none' id='loginmenuchap'>";
	}
	else
	{
	  $LOGIN_TABLE .= "<div style='text-align:center'>";
	}
	$LOGIN_TABLE .= $rs -> form_open("post", e_SELF,'','','',' onsubmit="hashLoginPassword(this)"');
  }
  else
  {
	$LOGIN_TABLE .= "<div style='text-align:center'>".$rs -> form_open("post", e_SELF);
  }
  
  $LOGIN_TABLE .= 
		"<table class='fborder' style='width:60%;margin-right:auto;margin-left:auto' >\n
		<tr>\n
		  <td class='forumheader' style='text-align:center;' colspan='3'>".LAN_LOGIN_4."</td>\n
		</tr>\n
		<tr>\n
		  <td class='forumheader3' style='width:40%'>{LOGIN_USERNAME_LABEL}</td>\n
		  <td class='forumheader3' style='width:40%'>{LOGIN_TABLE_USERNAME}</td>\n
		  <td class='forumheader3' rowspan='".($LOGIN_TABLE_SECIMG_SECIMG ? 3 : 2)."' style='width:20%; vertical-align: middle; margin-left: auto; margin-right: auto; text-align: center;'>".(file_exists(THEME."images/password.png") ? "<img src='".THEME_ABS."images/password.png' alt='' />\n" : "<img src='".e_IMAGE."generic/password.png' alt='' />\n" )."</td>\n</tr>\n
		  <tr>\n<td class='forumheader3'>".LAN_LOGIN_2."</td>\n<td class='forumheader3'>{LOGIN_TABLE_PASSWORD}
		  </td>\n</tr>\n";
		  
		  
	if(LOGIN_CAPTCHA) //FIXME - add shortcode for this. 
	{
		$LOGIN_TABLE .= "<tr><td class='forumheader3'>{LOGIN_TABLE_SECIMG_LAN}</td>\n<td class='forumheader3'>{LOGIN_TABLE_SECIMG_HIDDEN} {LOGIN_TABLE_SECIMG_SECIMG} {LOGIN_TABLE_SECIMG_TEXTBOC}</td>\n</tr>\n";
	}

	$LOGIN_TABLE .= "<tr>\n<td class='forumheader2' style='text-align:center;' colspan='3'>{LOGIN_TABLE_AUTOLOGIN}<span class='smalltext'>{LOGIN_TABLE_AUTOLOGIN_LAN}</span><br />{LOGIN_TABLE_SUBMIT}</td>\n</tr>\n</table>".
	$rs -> form_close()."\n</div>";
}
// ##### ------------------------------------------------------------------------------------------

// ##### LOGIN TABLE FOOTER -----------------------------------------------------------------------
if(!isset($LOGIN_TABLE_FOOTER))
{
			$LOGIN_TABLE_FOOTER = "
			<div style='margin-bottom:100px; width:70%;margin-right:auto;margin-left:auto'>
				<div style='text-align:center'><br />
					{LOGIN_TABLE_FOOTER_USERREG}
					
				</div>
			</div>
		</div>
	</div>";
}
// ##### ------------------------------------------------------------------------------------------*/



// Starter for v2. - Bootstrap 
$LOGIN_TEMPLATE['page']['header'] = "
	<div id='login-template'>
		<div class='center'>
			{LOGO: login}
		</div>";

$LOGIN_TEMPLATE['page']['body'] = '
		{LOGIN_TABLE_LOGINMESSAGE}
        <h2 class="form-signin-heading">'.LAN_LOGIN_4.'</h2>';
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

$LOGIN_WRAPPER['page']['LOGIN_TABLE_USERNAME'] = "<div class='form-group'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_PASSWORD'] = "<div class='form-group'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_SECIMG_SECIMG'] = "<div class='form-group'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_SECIMG_TEXTBOC'] = "<div class='form-group'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_REMEMBERME'] = "<div class='form-group checkbox'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_SUBMIT'] = "<div class='form-group'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_FOOTER_USERREG'] = "<div class='form-group'>{---}</div>";
$LOGIN_WRAPPER['page']['LOGIN_TABLE_LOGINMESSAGE'] = "<div class='alert alert-danger'>{---}</div>";


// $LOGIN_WRAPPER['page']['LOGIN_TABLE_FPW_LINK'] = "<div class='form-group'>{---}</div>";

$LOGIN_TEMPLATE['page']['body'] .= '
        {LOGIN_TABLE_USERNAME}
        {LOGIN_TABLE_PASSWORD}
        {SOCIAL_LOGIN: size=3x}
		{LOGIN_TABLE_SECIMG_SECIMG} {LOGIN_TABLE_SECIMG_TEXTBOC}
        {LOGIN_TABLE_REMEMBERME}
        {LOGIN_TABLE_SUBMIT=large}

 ';

$LOGIN_TEMPLATE['page']['footer'] =  "
			<div class='login-page-footer'>
				<div class='login-page-signup-link'><p>{LOGIN_TABLE_SIGNUP_LINK}</p></div>
				<div class='login-page-fpw-link'><p>{LOGIN_TABLE_FPW_LINK}</p></div>
			</div>
	</div>";
	



?>