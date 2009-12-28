<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Login menu
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/login_menu/config.php,v $
 * $Revision: 1.10 $
 * $Date: 2009-12-28 21:36:13 $
 * $Author: e107steved $
 *
*/


/**
 *	e107 Login menu plugin
 *
 *	Handles the login menu options
 *
 *	@package	e107_plugins
 *	@subpackage	login
 *	@version 	$Id: config.php,v 1.10 2009-12-28 21:36:13 e107steved Exp $;
 *
 *	@todo - sanitise options
 */

$eplug_admin = TRUE;

require_once('../../class2.php');
if (!getperms('4')) 
{ 
	header('location:'.e_BASE.'index.php');
	exit() ;
}

include_lan(e_PLUGIN.'login_menu/languages/'.e_LANGUAGE.'.php');
require_once(e_ADMIN.'auth.php');

require_once(e_PLUGIN.'login_menu/login_menu_class.php');
$loginClass = new login_menu_class();
$menuPref = e107::getConfig('menu');				// Pref object
$loginPrefs = $menuPref->getPref('login_menu');		// Array of login-related values

if (isset($_POST['update_menu']))
{
    //sort/show/hide links - Start
	if(varset($_POST['external_links'])) 
	{   
	    $_POST['pref']['external_links'] = array();
        asort($_POST['external_links_order']);
        
		foreach ($_POST['external_links_order'] as $key => $value) 
		{
        	if(array_key_exists($key, $_POST['external_links']))
			{
                $_POST['pref']['external_links'][] = $key;
			}
        }

        $_POST['pref']['external_links'] = $_POST['pref']['external_links'] ? implode(',', $_POST['pref']['external_links']) : '';

        unset($_POST['external_links']);
        
	} 
	else 
	{
        $_POST['pref']['external_links'] = '';
    }
    
    unset($_POST['external_links_order']);
    //sort/show/hide links - End
    
    //show/hide stats - Start
	if(varset($_POST['external_stats'])) 
	{
	    
	    $_POST['pref']['external_stats'] = implode(',', array_keys($_POST['external_stats']));
        unset($_POST['external_stats']);
        
	} 
	else 
	{
        $_POST['pref']['external_stats'] = '';
    }
    //show/hide stats - End

	unset($loginPrefs);
	$loginPrefs = $_POST['pref'];
	if (!isset($loginPrefs['new_news']))	{ $loginPrefs['new_news'] = '0';   }
	if (!isset($loginPrefs['new_comments']))	{ $loginPrefs['new_comments'] = '0';  }
	if (!isset($loginPrefs['new_members']))	{ $loginPrefs['new_members'] = '0'; }
	foreach($loginPrefs as $k => $v)
	{
		$menuPref->setPref('login_menu/'.$k, $v);
	}
	//$menuPref->setPref('login_menu', $loginPrefs);
	$menuPref->save(false, true, false);
	$admin_log->log_event('MISC_03','', E_LOG_INFORMATIVE,'');
	$ns->tablerender("", '<div style=\'text-align:center\'><b>'.LAN_SETSAVED.'</b></div>');

}

if (!isset($loginPrefs['new_news']))
{	// Assume no prefs defined
	$loginPrefs['new_news'] = '0';
	$loginPrefs['new_comments'] = '0';
	$loginPrefs['new_members'] = '0';
}


$text = '
	<div style="text-align:center">
	<form action="'.e_SELF.'" method="post">
	<table class="fborder" >
	
    '.$loginClass->render_config_links().'
    
    <tr>
    <td colspan="2" class="fcaption">'.LOGIN_MENU_L42.'</td>
    </tr>
    
	<tr>
	<td style="width:35%" class="forumheader3">'.LOGIN_MENU_L31.'</td>
	<td style="width:65%" class="forumheader3">
	<input type="checkbox" name="pref[new_news]" value="1"'.($loginPrefs['new_news'] == 1 ? ' checked="checked"' : '').' />
	</td>
	</tr>

	<tr>
	<td style="width:35%" class="forumheader3">'.LOGIN_MENU_L34.'</td>
	<td style="width:65%" class="forumheader3">
	<input type="checkbox" name="pref[new_comments]" value="1"'.($loginPrefs['new_comments'] == 1 ? ' checked="checked"' : '').' />
	</td>
	</tr>

	<tr>
	<td style="width:35%" class="forumheader3">'.LOGIN_MENU_L36.'</td>
	<td style="width:65%" class="forumheader3">
	<input type="checkbox" name="pref[new_members]" value="1"'.($loginPrefs['new_members'] == 1 ? ' checked="checked"' : '').' />
	</td>
	</tr>
	
	'.$loginClass->render_config_stats().'

	<tr>
	<td colspan="2" class="forumheader" style="text-align: center;"><input class="button" type="submit" name="update_menu" value="'.LAN_SAVE.'" /></td>
	</tr>
	</table>
	</form>
	</div>
	';

$ns->tablerender(LOGIN_MENU_L41, $text);

require_once(e_ADMIN."footer.php");

?>