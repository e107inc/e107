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
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Login menu plugin
 *
 *	Template file for login menu
 *
 *	@package	e107_plugins
 *	@subpackage	login
 *	@version 	$Id$;
 */
if (!defined('e107_INIT')){ exit; } 

if ( ! isset($LOGIN_MENU_FORM))
{

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

    $sc_style['LM_SIGNUP_LINK']['pre'] = "<br />[ ";
    $sc_style['LM_SIGNUP_LINK']['post'] = " ]";

    $sc_style['LM_FPW_LINK']['pre'] = "<br />[ ";
    $sc_style['LM_FPW_LINK']['post'] = " ]";

    $sc_style['LM_RESEND_LINK']['pre'] = "<br />[ ";
    $sc_style['LM_RESEND_LINK']['post'] = " ]";

    $sc_style['LM_REMEMBERME']['pre'] = "<br />";
    $sc_style['LM_REMEMBERME']['post'] = "";

    $sc_style['LM_IMAGECODE_NUMBER']['pre'] = "<br />";
    $sc_style['LM_IMAGECODE_NUMBER']['post'] = "<br />";

    $sc_style['LM_IMAGECODE_BOX']['pre'] = "";
    $sc_style['LM_IMAGECODE_BOX']['post'] = "<br />";

	$LOGIN_MENU_FORM = "{LM_MESSAGE}";

	if ((varset($pref['password_CHAP'],0) == 2) && ($pref['user_tracking'] == "session"))
	{
	  $LOGIN_MENU_FORM .= "
    	<div style='text-align: center' id='nologinmenuchap'>"."Javascript must be enabled in your browser if you wish to log into this site"."
		</div>
    	<div style='text-align: center; display:none' id='loginmenuchap'>";
	}
	else
	{
	  $LOGIN_MENU_FORM .= "
    	<div style='text-align: center'>";
	}

	$LOGIN_MENU_FORM .= "
            {LM_USERNAME_LABEL}<br />
            {LM_USERNAME_INPUT}<br />
            {LM_PASSWORD_LABEL}<br />
            {LM_PASSWORD_INPUT}<br />
            {LM_IMAGECODE_NUMBER}{LM_IMAGECODE_BOX}
            {LM_LOGINBUTTON}
            {LM_REMEMBERME}<br />
            {LM_SIGNUP_LINK}
            {LM_FPW_LINK}
            {LM_RESEND_LINK}
			{FB_LOGIN_BUTTON} 
    	</div>
	";
}


if ( ! isset($LOGIN_MENU_MESSAGE))
{
	$LOGIN_MENU_MESSAGE = '<div class="login-menu-message">{LM_MESSAGE_TEXT}</div>';
}

if ( ! isset($LOGIN_MENU_LOGGED))
{

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

    ---------------- Legacy Layout --------------------------------
 
   	$sc_style['LM_MAINTENANCE']['pre'] = '<div style="text-align:center"><strong>';
	$sc_style['LM_MAINTENANCE']['post'] = '</strong></div><br />';

    $sc_style['LM_ADMINLINK']['pre'] = '';
	$sc_style['LM_ADMINLINK']['post'] = '<br />';

    $sc_style['LM_EXTERNAL_LINKS']['pre'] = '<br />';
	$sc_style['LM_EXTERNAL_LINKS']['post'] = '<br />';

    $sc_style['LM_STATS']['pre'] = '<br /><br /><span class="smalltext">'.LOGIN_MENU_L25.':<br />';
	$sc_style['LM_STATS']['post'] = '</span>';

    $sc_style['LM_LISTNEW_LINK']['pre'] = '<br /><br />';
	$sc_style['LM_LISTNEW_LINK']['post'] = '';

    $sc_style['LM_ADMIN_CONFIGURE']['pre'] = '';
	$sc_style['LM_ADMIN_CONFIGURE']['post'] = '<br />';

	$LOGIN_MENU_LOGGED = '
		{LM_MAINTENANCE}
		{LM_ADMINLINK_BULLET} {LM_ADMINLINK}
		{LM_BULLET} {LM_USERSETTINGS}<br />
		{LM_BULLET}	{LM_PROFILE}<br />
		{LM_ADMINLINK_BULLET} {LM_ADMIN_CONFIGURE}
		{LM_EXTERNAL_LINKS}
		{LM_BULLET} {LM_LOGOUT}
		{LM_STATS}
		{LM_LISTNEW_LINK}
	';
*/

// New Template for v2. Bullets via CSS etc. Login-Menu Stats may require work. 

    $sc_style['LM_MAINTENANCE']['pre'] = '<li class="login-menu-maintenance">';
	$sc_style['LM_MAINTENANCE']['post'] = '</li>';

    $sc_style['LM_ADMINLINK']['pre'] = '<li class="login-menu-admin">';
	$sc_style['LM_ADMINLINK']['post'] = '</li>';

    $sc_style['LM_EXTERNAL_LINKS']['pre'] = '<li class="login-menu-external">';
	$sc_style['LM_EXTERNAL_LINKS']['post'] = '</li>';

    $sc_style['LM_STATS']['pre'] = '<li class="nav-header login-menu-stats smalltext">'.LAN_LOGINMENU_25.':</li><li>';
	$sc_style['LM_STATS']['post'] = '</li>';

    $sc_style['LM_LISTNEW_LINK']['pre'] = '<li class="login-menu-listnew">';
	$sc_style['LM_LISTNEW_LINK']['post'] = '</li>';

    $sc_style['LM_ADMIN_CONFIGURE']['pre'] = '<li class="login-menu-admin-config">';
	$sc_style['LM_ADMIN_CONFIGURE']['post'] = '</li>';
	
    $sc_style['LM_LOGOUT']['pre'] = '<li class="login-menu-logout">';
	$sc_style['LM_LOGOUT']['post'] = '</li>';
	
    $sc_style['LM_LOGOUT']['pre'] = '<li class="login-menu-logout">';
	$sc_style['LM_LOGOUT']['post'] = '</li>';

	
	$LOGIN_MENU_LOGGED = '
	<ul class="login-menu-logged nav nav-list">
		{LM_MAINTENANCE}
		{LM_ADMINLINK}
		<li class="login-menu-usersettings {LM_ACTIVE: mode=usersettings}">{LM_USERSETTINGS}</li>
		<li class="login-menu-profile {LM_ACTIVE: mode=profile}">{LM_PROFILE}</li>
		{LM_ADMIN_CONFIGURE}
		{LM_EXTERNAL_LINKS}
		{LM_LOGOUT}
		{LM_STATS}
		{LM_LISTNEW_LINK}
	</ul>
	';
}

if ( ! isset($LOGIN_MENU_EXTERNAL_LINK))
{
	$LOGIN_MENU_EXTERNAL_LINK = '
		{LM_BULLET} {LM_EXTERNAL_LINK}<br />
	';
}

if ( ! isset($LOGIN_MENU_STATS))
{
    $sc_style['LM_NEW_NEWS']['pre'] = '';
	$sc_style['LM_NEW_NEWS']['post'] = '<br />';

    $sc_style['LM_NEW_COMMENTS']['pre'] = '';
	$sc_style['LM_NEW_COMMENTS']['post'] = '<br />';

    $sc_style['LM_NEW_CHAT']['pre'] = '';
	$sc_style['LM_NEW_CHAT']['post'] = '<br />';

    $sc_style['LM_NEW_FORUM']['pre'] = '';
	$sc_style['LM_NEW_FORUM']['post'] = '<br />';

    $sc_style['LM_NEW_USERS']['pre'] = '';
	$sc_style['LM_NEW_USERS']['post'] = '<br />';

	$LOGIN_MENU_STATS = '
        {LM_NEW_NEWS}
        {LM_NEW_COMMENTS}
        {LM_NEW_USERS}
        {LM_PLUGIN_STATS}
    ';
}

$LM_STATITEM_SEPARATOR = '<br />';
if (!isset($LOGIN_MENU_STATITEM))
{

	$LOGIN_MENU_STATITEM = '
        {LM_STAT_NEW} {LM_STAT_LABEL}{LM_STAT_EMPTY}
    ';
}
