<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	e107 DB configuration for alt_auth plugin
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
e107::includeLan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_e107db_conf.php');
e107::includeLan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_alt_auth.php');
define('ALT_AUTH_ACTION', 'e107db');
require_once(e_PLUGIN.'alt_auth/alt_auth_adminmenu.php');
require_once(e_PLUGIN.'alt_auth/extended_password_handler.php');





class alt_auth_e107db extends alt_auth_admin
{

	public function __construct()
	{
	}


	public function showForm()
	{
		$ns = e107::getRender();
		
		$parm = $this->altAuthGetParams('e107db');

		$frm = new form;


		$tab1 = "<table class='table adminform'>
		<colgroup span='2'>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>";

		$tab1 .= "<tr><td>".LAN_ALT_26."</td><td>";
		$tab1 .= E107DB_LAN_1;
		$tab1 .= "</td></tr>";

		$tab1 .= $this->alt_auth_get_db_fields('e107db', $frm, $parm, 'server|port|uname|pwd|db|prefix|classfilt');

		$tab1 .= "<tr><td>".E107DB_LAN_9."</td><td>";
		$tab1 .= $this->altAuthGetPasswordSelector('e107db_password_method', $frm, $parm['e107db_password_method'], FALSE);

		$tab1 .= "</td></tr></table>";



		$tab2 = "
		<table class='table adminform'>
		<colgroup>
		<col class='col-label' />
		<col class='col-control' />
		</colgroup>
		";

			$tab2 .= "<tr><td colspan='2'><br />".E107DB_LAN_11."</td></tr>";

		$tab2 .= $this->alt_auth_get_field_list('e107db',$frm, $parm, TRUE);

		$tab2 .= "</table>";

		$tabs = array(
			'tab1'  => array('caption'=>'Database', 'text'=>$tab1),
			'tab2'  => array('caption'=>'Data', 'text'=>$tab2),
		);

		$text = $frm -> form_open('post', e_SELF);

		$text .= e107::getForm()->tabs($tabs);

		$text .= "<div class='buttons-bar center'>";
		$text .= e107::getForm()->admin_button("update", LAN_UPDATE,'update');
		$text .= '</div>';

		$text .= $frm -> form_close();

		$ns->tablerender(E107DB_LAN_10, $text);

		echo e107::getMessage()->render();
		
		$ns->tablerender(LAN_ALT_40.LAN_ALT_41,$this->alt_auth_test_form('e107db',$frm));
	}
}


$e107dbAdmin = new alt_auth_e107db();

if(vartrue($_POST['update']))
{
	$message = $e107dbAdmin->alt_auth_post_options('e107db');
}


if(vartrue($message))
{
	e107::getRender()->tablerender('',"<div style='text-align:center;'>".$message.'</div>');
}

$e107dbAdmin->showForm();


require_once(e_ADMIN.'footer.php');



function e107db_conf_adminmenu()
{
	alt_auth_adminmenu();
}

?>
