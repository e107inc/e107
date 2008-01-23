<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/login_menu/login_menu_template.php,v $
|     $Revision: 1.2 $
|     $Date: 2008-01-23 01:12:15 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/

if (!isset($LOGIN_MENU_FORM)){
    $sc_style['LM_SIGNUP_LINK']['pre'] = "<br />[ ";
    $sc_style['LM_SIGNUP_LINK']['post'] = " ]";
    
    $sc_style['LM_FPW_LINK']['pre'] = "<br />[ ";
    $sc_style['LM_FPW_LINK']['post'] = " ]";
    
    $sc_style['LM_RESEND_LINK']['pre'] = "<br />[ ";
    $sc_style['LM_RESEND_LINK']['post'] = " ]";
    
    $sc_style['LM_REMEMBERME']['pre'] = "<br />";
    $sc_style['LM_REMEMBERME']['post'] = "";
    
	$LOGIN_MENU_FORM = "
    	{LM_MESSAGE}
    	<div style='text-align: center'>
            ".LOGIN_MENU_L1."<br />   
            {LM_USERNAME_INPUT}<br />
            ".LOGIN_MENU_L2."<br />
            {LM_PASSWORD_INPUT}<br />
            {LM_IMAGECODE}
            {LM_LOGINBUTTON}
            {LM_REMEMBERME}<br />
            {LM_SIGNUP_LINK}
            {LM_FPW_LINK}
            {LM_RESEND_LINK}
    	</div>
	";
}

if (!isset($LOGIN_MENU_LOGGED)){
    $sc_style['LM_ADMINLINK']['pre'] = '';
	$sc_style['LM_ADMINLINK']['post'] = '<br />';
	
    $sc_style['LM_EXTERNAL_LINKS']['pre'] = '<br />';
	$sc_style['LM_EXTERNAL_LINKS']['post'] = '<br />';
	
    $sc_style['LM_STATS']['pre'] = '<br /><br /><span class="smalltext">'.LOGIN_MENU_L25.':<br />';
	$sc_style['LM_STATS']['post'] = '</span>';
	
    $sc_style['LM_LISTNEW_LINK']['pre'] = '<br />';
	$sc_style['LM_LISTNEW_LINK']['post'] = '';

	$LOGIN_MENU_LOGGED = '
		{LM_MAINTENANCE}
		{LM_ADMINLINK_BULLET} {LM_ADMINLINK}
		{LM_BULLET} {LM_USERSETTINGS}<br />
		{LM_BULLET}	{LM_PROFILE}<br />
		{LM_EXTERNAL_LINKS}
		{LM_BULLET} {LM_LOGOUT}
		{LM_STATS}
		{LM_LISTNEW_LINK}
	';
}

if (!isset($LOGIN_MENU_EXTERNAL_LINK)){
	$LOGIN_MENU_EXTERNAL_LINK = '
		{LM_BULLET} {LM_EXTERNAL_LINK}<br />
	';
}

if (!isset($LOGIN_MENU_STATS)){
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
        {LM_NEW_CHAT}
        {LM_NEW_FORUM}
        {LM_NEW_USERS}
    ';
}

if (!isset($LOGIN_MENU_MESSAGE)){
	$LOGIN_MENU_MESSAGE = '<div class="core-sysmsg loginbox">{LM_MESSAGE_TEXT}</div>';
}
?>