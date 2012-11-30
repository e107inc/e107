<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Login menu template
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/login_menu/login_menu_template.php,v $
 * $Revision: 12943 $
 * $Date: 2012-08-11 05:05:22 +0300 (съб, 11 авг 2012) $
 * $Author: e107coders $
 */

/**
 *	e107 Login menu plugin
 *
 *	Template file for login menu
 *
 *	@package	e107_plugins
 *	@subpackage	login
 *	@version 	$Id: login_menu_template.php 12943 2012-08-11 02:05:22Z e107coders $;
 */
if (!defined('e107_INIT')){ exit; } 

/*
    NEW SHORTCODES/PARAMETERS:

    $LOGIN_MENU_LOGGED
    - LM_REMEMBERME (parm: 'href' or empty)
    - LM_SIGNUP_LINK (parm: 'href' or empty)
    - LM_FPW_LINK (parm: 'href' or empty)
    - LM_RESEND_LINK (parm: 'href' or empty)
    - LM_IMAGECODE_NUMBER
    - LM_IMAGECODE_BOX

    $LOGIN_MENU_MESSAGE
    - LM_MESSAGE_TEXT

    DEPRECATED SHORTCODES:
    - LM_IMAGECODE - use LM_IMAGECODE_NUMBER, LM_IMAGECODE_BOX instead
*/



    $sc_style['LM_FPW_LINK']['pre'] =
    $sc_style['LM_SIGNUP_LINK']['pre'] =
    $sc_style['LM_RESEND_LINK']['pre'] = '<div class="login-box-item">';
    $sc_style['LM_FPW_LINK']['post'] =
    $sc_style['LM_SIGNUP_LINK']['post'] =
    $sc_style['LM_RESEND_LINK']['post'] = '</div>';

    $sc_style['LM_LOGINBUTTON']['pre'] = '<div class="login-box-botton">';
    $sc_style['LM_LOGINBUTTON']['post'] = '</div>';

    $sc_style['LM_REMEMBERME']['pre'] = '<div class="login-box-remmeber">';
    $sc_style['LM_REMEMBERME']['post'] = '</div>';

    $sc_style['LM_IMAGECODE_NUMBER']['pre'] = '
		<div class="clear_b"></div>
		<div class="login-box-imagecode">
			'.LAN_THEME_IMAGECODE.'
		</div>
		<div class="login-box-tbox">
			<span class="login-box-label">
	';
    $sc_style['LM_IMAGECODE_NUMBER']['post'] = '</span>';

    $sc_style['LM_IMAGECODE_BOX']['pre'] = '';
    $sc_style['LM_IMAGECODE_BOX']['post'] = '</div><div class="clear_b"></div>';

$LOGIN_MENU_FORM = "{LM_MESSAGE}";

if ((varset($pref['password_CHAP'],0) == 2) && ($pref['user_tracking'] == "session"))
{
  $LOGIN_MENU_FORM .= '
	<div class="modal hide fade" id="clogin" tabindex="-1" role="dialog" aria-labelledby="cloginLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="cloginLabel">'.LAN_THEME_LOGIN.'</h3>
		</div>
		<div class="modal-body">
			<div style="text-align: right" id="nologinmenuchap">
				'.'Javascript must be enabled in your browser if you wish to log into this site'.'
			</div>
			<div style="text-align: right; display:none" id="loginmenuchap">
	';
}
else
{
  $LOGIN_MENU_FORM .= '
	<div class="modal hide fade" id="clogin" tabindex="-1" role="dialog" aria-labelledby="cloginLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="cloginLabel">'.LAN_THEME_LOGIN.'</h3>
		</div>
		<div class="modal-body">
			<div>
	';
}

$LOGIN_MENU_FORM .= '
				<div class="clear_b"></div>
				<div class="login-box-tbox">
					{LM_USERNAME_INPUT}
				</div>
				<div class="clear_b H5"></div>
				<div class="login-box-tbox">
					{LM_PASSWORD_INPUT}
				</div>
				<div class="clear_b H5"></div>
				{LM_IMAGECODE_NUMBER}{LM_IMAGECODE_BOX}
				<div class="clear_b H5"></div>
				{LM_REMEMBERME}
				<div class="clear_b H5"></div>
				{LM_LOGINBUTTON}
				<div class="clear_b H5"></div>
				{FB_LOGIN_BUTTON} 
			</div>
		</div>
		<div class="modal-footer">
			{LM_SIGNUP_LINK} {LM_FPW_LINK} {LM_RESEND_LINK}
		</div>
	</div>
';

$LOGIN_MENU_MESSAGE = '<div class="login-menu-message">{LM_MESSAGE_TEXT}</div>';

/*
    NEW SHORTCODES and/or PARAMETERS:

    $LOGIN_MENU_LOGGED
    - LM_ADMIN_CONFIGURE (parm: 'href' or empty)
    - LM_ADMINLINK (parm: 'href' or empty)
    - LM_PROFILE_HREF
    - LM_LOGOUT_HREF
    - LM_USERSETTINGS_HREF
    - LM_EXTERNAL_LINKS
    - LM_STATS
    - LM_LISTNEW_LINK

    $LOGIN_MENU_EXTERNAL_LINK
    - LM_EXTERNAL_LINK (parm: 'href' or empty)
    - LM_EXTERNAL_LINK_LABEL

    $LOGIN_MENU_STATS
    - LM_NEW_NEWS
    - LM_NEW_COMMENTS
    - LM_NEW_USERS
    - LM_PLUGIN_STATS

    $LM_STATITEM_SEPARATOR - plugin stats separator

    $LOGIN_MENU_STATITEM
    - LM_STAT_NEW
    - LM_STAT_LABEL
    - LM_STAT_EMPTY

*/

	$sc_style['LM_MAINTENANCE']['pre'] = '
		<div class="login-maintenance mediumtext">
			<strong>
	';
	$sc_style['LM_MAINTENANCE']['post'] = '
			</strong>
		</div>
		<div class="login-box-separator"></div>
	';
	
	$sc_style['USER_AVATAR']['pre'] 			= '';
	$sc_style['USER_AVATAR']['post']			= '';
	
	$sc_style['LM_STATS']['pre'] 				= '<div class="login-box-separator"></div>
		<h5><strong>'.LOGIN_MENU_L25.':</strong></h5>';
	$sc_style['LM_STATS']['post'] 				= '';
	
	$sc_style['LM_LISTNEW_LINK']['pre'] 		= '<div class="login-box-separator"></div>';
	$sc_style['LM_LISTNEW_LINK']['post'] 		= '';
	
	$sc_style['LM_ADMIN_CONFIGURE']['pre'] 		= '<div class="btn btn-primary">';
	$sc_style['LM_ADMIN_CONFIGURE']['post'] 	= '</div>';

$LOGIN_MENU_LOGGED = '
	<div class="modal hide fade" id="clogin" tabindex="-1" role="dialog" aria-labelledby="cloginLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="cloginLabel">'.LAN_THEME_WELCOME.' '.USERNAME.'</h3>
		</div>
		<div class="modal-body">
			{LM_MAINTENANCE}
			<div class="login-box-avatar">{USER_AVATAR}</div>
			<div class="login-box-liks">
				{LM_ADMINLINK}
				{LM_USERSETTINGS}
				{LM_PROFILE}
				{LM_LOGOUT}
			</div>
			{LM_STATS}
			{LM_LISTNEW_LINK}
			{LM_EXTERNAL_LINKS}
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			{LM_ADMIN_CONFIGURE}
		</div>
	</div>
';

$LOGIN_MENU_EXTERNAL_LINK = '<div class="login-box-separator">{LM_EXTERNAL_LINK}</div>';

	$sc_style['LM_NEW_USERS']['pre'] 		=
	$sc_style['LM_NEW_FORUM']['pre']		=
	$sc_style['LM_NEW_CHAT']['pre'] 		=
	$sc_style['LM_NEW_COMMENTS']['pre'] 	=
	$sc_style['LM_NEW_NEWS']['pre'] 		= '<div class="smalltext PL10">';
	
	$sc_style['LM_NEW_USERS']['post'] 		=
	$sc_style['LM_NEW_FORUM']['post'] 		=
	$sc_style['LM_NEW_CHAT']['post'] 		=
	$sc_style['LM_NEW_COMMENTS']['post'] 	=
	$sc_style['LM_NEW_NEWS']['post'] 		= '</div>';

$LOGIN_MENU_STATS = '
	{LM_NEW_NEWS}
	{LM_NEW_COMMENTS}
	{LM_NEW_USERS}
	{LM_PLUGIN_STATS}
';

$LM_STATITEM_SEPARATOR = '<div class="login-box-separator"></div>';
$LOGIN_MENU_STATITEM = '
	{LM_STAT_NEW} {LM_STAT_LABEL}{LM_STAT_EMPTY}
';
