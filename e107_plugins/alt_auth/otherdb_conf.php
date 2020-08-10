<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Alt_auth plugin - 'otherdb' configuration
 *
 * $URL$
 * $Id$
 * 
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
e107::includeLan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_otherdb_conf.php');
e107::includeLan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_alt_auth.php');
define('ALT_AUTH_ACTION', 'otherdb');
require_once(e_PLUGIN.'alt_auth/alt_auth_adminmenu.php');
require_once(e_PLUGIN.'alt_auth/extended_password_handler.php');




class alt_auth_otherdb extends alt_auth_admin
{

	public function __construct()
	{
	}




	public function showForm()
	{
		$ns = e107::getRender();

		$parm = $this->altAuthGetParams('otherdb');

		$frm = new form;
		$text = $frm -> form_open("post", e_SELF);


		$tab1 = "<table class='table adminform'>
		<colgroup>
		<col class='col-label' />
		<col class='col-control' />
		</colgroup>
		";

		$tab1 .= "<tr><td>".LAN_ALT_26."</td><td>";
		$tab1 .= OTHERDB_LAN_15;
		$tab1 .= "</td></tr>";

		$tab1 .= $this->alt_auth_get_db_fields('otherdb', $frm, $parm, 'server|port|uname|pwd|db|table|ufield|pwfield|salt');
		$tab1 .= "<tr><td>".OTHERDB_LAN_9."</td><td>";
		
		$tab1 .= $this->altAuthGetPasswordSelector('otherdb_password_method', $frm, $parm['otherdb_password_method'], TRUE);

		$tab1 .= "</td></tr>
		</table>
		";

		$tab2 = "
		<table class='table adminform'>
		<colgroup>
		<col class='col-label' />
		<col class='col-control' />
		</colgroup>
		";

		$tab2 .= "<tr><td class='forumheader2' colspan='2'>".LAN_ALT_27."</td></tr>";

		$tab2 .= $this->alt_auth_get_field_list('otherdb',$frm, $parm, FALSE);



		$tab2 .= '</table>';

		$tabs = array(
			'tab1'  => array('caption'=>'Database', 'text'=>$tab1),
			'tab2'  => array('caption'=>'Data', 'text'=>$tab2),
		);

		$text .= e107::getForm()->tabs($tabs);

		$text .= "<div class='buttons-bar center'>";
		$text .= e107::getForm()->admin_button("update", LAN_UPDATE,'update');
		$text .= '</div>';

		$text .= $frm -> form_close();

		$ns -> tablerender(OTHERDB_LAN_10, $text);
		
		$ns->tablerender(LAN_ALT_40.LAN_ALT_41, $this->alt_auth_test_form('otherdb',$frm));
	}
}


$otherdbAdmin = new alt_auth_otherdb();

if(!empty($_POST['update']))
{
	$message = $otherdbAdmin->alt_auth_post_options('otherdb');
}


if(!empty($message))
{
	echo e107::getMessage()->addSuccess($message)->render();
}


$otherdbAdmin->showForm($mes);


require_once(e_ADMIN.'footer.php');



function otherdb_conf_adminmenu()
{
	alt_auth_adminmenu();
}

