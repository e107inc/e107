<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2013 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/radius_conf.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
$eplug_admin = true;
require_once("../../class2.php");
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
e107::includeLan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_radius_conf.php');
e107::includeLan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_alt_auth.php');
define("ALT_AUTH_ACTION", "radius");
require_once(e_PLUGIN."alt_auth/alt_auth_adminmenu.php");
$mes = e107::getMessage();



class alt_auth_radius extends alt_auth_admin
{
	private $radius;

	public function __construct()
	{
	}


	public function readOptions()
	{
		$this->radius = $this->altAuthGetParams('radius');
	}

	
	public function showForm($mes)
	{
		$ns = e107::getRender();
		$frm = new form;
		$text = $frm->form_open('post',e_SELF);
		$text .= "<table class='table adminform'>";
		$text .= "<tr><td>".LAN_RADIUS_01."</td><td>";
		$text .= $frm->form_text('radius_server', 35, vartrue($this->radius['radius_server']), 120);
		$text .= "</td></tr>\n";

		$text .= "<tr><td>".LAN_RADIUS_02."</td><td>";
		$text .= $frm->form_text('radius_secret', 35, vartrue($this->radius['radius_secret']), 200);
		$text .= "</td></tr>\n";

		$tmp = $this->alt_auth_get_field_list('radius', $frm, $this->radius, FALSE);
		if ($tmp)
		{
			$text .= "<tr><td class='forumheader2' colspan='2'>".LAN_ALT_27."</td></tr>\n".$tmp;
			unset($tmp);
		}

		$text .= "<tr><td class='forumheader' colspan='2' style='text-align:center;'>";
		// $text .= $frm -> form_button("submit", "update", LAN_ALT_2);
		$text .= e107::getForm()->admin_button('update', LAN_UPDATE,'update');
		$text .= "</td></tr>\n";

		$text .= "</table>\n";
		$text .= $frm->form_close();
		$ns->tablerender(LAN_RADIUS_06, $mes->render().$text);
		$ns->tablerender(LAN_ALT_40.LAN_ALT_41, $this->alt_auth_test_form('radius',$frm));
	}
}


$message = '';
$radiusAdmin = new alt_auth_radius();

if(vartrue($_POST['update']))
{
	// $message .= alt_auth_post_options('radius');
	$mes->addSuccess($radiusAdmin->alt_auth_post_options('radius'));
}


if (!extension_loaded('radius'))
{
	// $message .= "<br /><br /><div style='color:#f00; font-weight:bold'>".LAN_RADIUS_11."</div><br />";
	$mes->addWarning(LAN_RADIUS_11);
}


if($message)
{
  $ns->tablerender('',"<div style='text-align:center;'>".$message."</div>");
}

$radiusAdmin->readOptions();
$radiusAdmin->showForm($mes);



require_once(e_ADMIN.'footer.php');


function radius_conf_adminmenu()
{
	alt_auth_adminmenu();
}


