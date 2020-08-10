<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - Login menu
 *
 *
*/


/**
 *	e107 Login menu plugin
 *
 *	Handles the login menu options
 *
 *	@package	e107_plugins
 *	@subpackage	login
 *
 *	@todo - sanitise options
 */

$eplug_admin = TRUE;

require_once('../../class2.php');
if (!getperms('4')) 
{ 
	e107::redirect('admin');
	exit() ;
}

e107::includeLan(e_PLUGIN.'login_menu/languages/'.e_LANGUAGE.'.php');
require_once(e_ADMIN.'auth.php');

require_once(e_PLUGIN.'login_menu/login_menu_class.php');
$loginClass = new login_menu_class();
$menuPref = e107::getConfig('menu');				// Pref object
$loginPrefs = $menuPref->getPref('login_menu');		// Array of login-related values

$mes = e107::getMessage();
$frm = e107::getForm();

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

    $menuPref->reset();
	foreach($loginPrefs as $k => $v)
	{
		$menuPref->setPref('login_menu/'.$k, $v);
	}
	//$menuPref->setPref('login_menu', $loginPrefs);
	$menuPref->save(false, true, false);
	e107::getLog()->add('MISC_03','', E_LOG_INFORMATIVE,'');
	//$ns->tablerender("", '<div style=\'text-align:center\'><b>'.LAN_SETSAVED.'</b></div>');
	$mes->addSuccess(LAN_SAVED);
	$ns->tablerender("", $mes->render() . $text); 
}

if (!isset($loginPrefs['new_news']))
{	// Assume no prefs defined
	$loginPrefs['new_news'] = '0';
	$loginPrefs['new_comments'] = '0';
	$loginPrefs['new_members'] = '0';
}


$text = "
	<form method='post' action='".e_SELF."'>
	<table class='table adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
	
    ".$loginClass->render_config_links()."
    ";

    
  /*
    <tr>
   	 <td colspan="2">'.LAN_LOGINMENU_42.'</td>
    </tr>
  */

 $text .= "
	<tr>
		<td>".LAN_LOGINMENU_31."</td>
		<td>".$frm->checkbox('pref[new_news]', 1, varset($loginPrefs['new_news'],0))."</td>
	</tr>

	<tr>
		<td>".LAN_LOGINMENU_34."</td>
		<td>".$frm->checkbox('pref[new_comments]', 1, varset($loginPrefs['new_comments'],0))."</td>
	</tr>

	<tr>
		<td>".LAN_LOGINMENU_36."</td>
		<td>".$frm->checkbox('pref[new_members]', 1, varset($loginPrefs['new_members'],0))."</td>
	</tr>
	
		".$loginClass->render_config_stats()."
	</table>
	
	<div class='buttons-bar center'>
		".$frm->admin_button('update_menu', LAN_SAVE, 'update')."
	</div>

	</form>
	";

$ns->tablerender(LAN_LOGINMENU_41, $text);

require_once(e_ADMIN."footer.php");

