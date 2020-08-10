<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	LDAP configuration for alt_auth plugin
 *
 * $URL$
 * $Id$
 */

/**
 *	e107 Alternate authorisation plugin
 *
 *	@package	e107_plugins
 *	@subpackage	alt_auth
 *	@version 	$Id$;
 */
 
$eplug_admin = true;
require_once('../../class2.php');
require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER.'form_handler.php');
e107::includeLan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_ldap_conf.php');
e107::includeLan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_alt_auth.php');
define('ALT_AUTH_ACTION', 'ldap');
require_once(e_PLUGIN.'alt_auth/alt_auth_adminmenu.php');

$mes = e107::getMessage();


class alt_auth_ldap extends alt_auth_admin
{

	public function __construct()
	{
	}


	public function showForm($mes)
	{
		$server_types[1] = 'LDAP';
		$server_types[2] = 'ActiveDirectory';
		$server_types[3] = 'eDirectory';

		$ldap_ver[1]='2';
		$ldap_ver[2]='3';


		$ldap = $this->altAuthGetParams('ldap');
		if (!isset($ldap['ldap_edirfilter'])) $ldap['ldap_edirfilter'] == '';
		//print_a($ldap);

		$current_filter = "(&(cn=[USERNAME]){$ldap['ldap_edirfilter']})";

		$frm = new form;
		$text = $frm -> form_open('post',e_SELF);
		$text .= "<table class='table adminform'>";
		$text .= "<tr><td>".LDAPLAN_12."</td><td>";
		$text .= $frm -> form_select_open("ldap_servertype");
		foreach($server_types as $v)
		{
			$sel = (vartrue($ldap['ldap_servertype']) == $v) ? " Selected='selected'" : '';
			$text .= $frm -> form_option($v, $sel, $v);
		}
		$text .= $frm -> form_select_close();
		$text .= "</td></tr>";

		$text .= "<tr><td>".LDAPLAN_1."</td><td>";
		$text .= $frm -> form_text("ldap_server", 35, vartrue($ldap['ldap_server']), 120);
		$text .= "</td></tr>";
		$text .= "<tr><td>".LDAPLAN_2."</td><td>";
		$text .= $frm -> form_text("ldap_basedn", 35, vartrue($ldap['ldap_basedn']), 120);
		$text .= "</td></tr>";
		$text .= "<tr><td>".LDAPLAN_14."</td><td>";
		$text .= $frm -> form_text("ldap_ou", 35, vartrue($ldap['ldap_ou']), 60);
		$text .= "</td></tr>";

		$text .= "<tr><td>".LDAPLAN_3."</td><td>";
		$text .= $frm -> form_text("ldap_user", 35, vartrue($ldap['ldap_user']), 120);
		$text .= "</td></tr>";

		$text .= "<tr><td>".LDAPLAN_4."</td><td>";
		$text .= $frm -> form_text("ldap_passwd", 35, vartrue($ldap['ldap_passwd']), 120);
		$text .= "</td></tr>";

		$text .= "<tr><td>".LDAPLAN_5."</td><td>";
		$text .= $frm -> form_select_open("ldap_version");

		foreach($ldap_ver as $v)
		{
			$sel = ($ldap['ldap_version'] == $v) ? " Selected='selected'" : "";
			$text .= $frm -> form_option($v, $sel, $v);
		}

		$text .= $frm -> form_select_close();
		$text .= "</td></tr>";

		$text .= "<tr><td>".LDAPLAN_7."<br /><span class='smalltext'>".LDAPLAN_8."</span></td><td>";
		$text .= $frm -> form_text('ldap_edirfilter', 35, $ldap['ldap_edirfilter'], 120);
		$text .= "<br /><span class='smalltext'>".LDAPLAN_9."<br />".htmlentities($current_filter)."</span></td></tr>";

			$text .= "<tr><td class='forumheader2' colspan='2'>".LAN_ALT_27."</td></tr>";

			$this->add_extended_fields();
			$text .= $this->alt_auth_get_field_list('ldap',$frm, $ldap, FALSE);

		$text .= "<tr><td class='forumheader' colspan='2' style='text-align:center;'>";

		$text .= e107::getForm()->admin_button("update", LAN_UPDATE,'update');
		//$text .= $frm -> form_button('submit', 'update', LDAPLAN_13);
		$text .= "</td></tr>";

		$text .= "</table>\n";
		$text .= $frm -> form_close();

		e107::getRender()->tablerender(LDAPLAN_6, $mes->render(). $text);
		e107::getRender()->tablerender(LAN_ALT_40.LAN_ALT_41, $this->alt_auth_test_form('ldap',$frm));
	}
}


$ldapAdmin = new alt_auth_ldap();

$message = '';
if(vartrue($_POST['update']))
{
	$message .= $ldapAdmin->alt_auth_post_options('ldap');
}


if(!function_exists('ldap_connect'))
{
	// $message .= "<br /><br /><div style='color:#f00; font-weight:bold'>".LDAPLAN_11."</div><br />";
	$mes->addWarning(LDAPLAN_11);
}


if($message)
{
	e107::getRender()->tablerender('',"<div style='text-align:center;'>".$message.'</div>');
}

$ldapAdmin->showForm($mes);


require_once(e_ADMIN.'footer.php');


function ldap_conf_adminmenu()
{
	alt_auth_adminmenu();
}


