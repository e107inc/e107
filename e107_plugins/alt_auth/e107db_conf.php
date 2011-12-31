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
include_lan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_e107db_conf.php');
include_lan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_alt_auth.php');
define('ALT_AUTH_ACTION', 'e107db');
require_once(e_PLUGIN.'alt_auth/alt_auth_adminmenu.php');
require_once(e_PLUGIN.'alt_auth/extended_password_handler.php');


if($_POST['update'])
{
	$message = alt_auth_post_options('e107db');
}


if($message)
{
	e107::getRender()->tablerender('',"<div style='text-align:center;'>".$message.'</div>');
}



show_e107db_form();


function show_e107db_form()
{
	$ns = e107::getRender();
	
	$parm = altAuthGetParams('e107db');

	$frm = new form;
	$text = $frm -> form_open('post', e_SELF);
	$text .= "<table cellpadding='0' cellspacing='0' class='fborder'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>";

	$text .= "<tr><td class='forumheader3'>".LAN_ALT_26."</td><td class='forumheader3'>";
	$text .= E107DB_LAN_1;
	$text .= "</td></tr>";

	$text .= alt_auth_get_db_fields('e107db', $frm, $parm, 'server|uname|pwd|db|prefix|classfilt');

	$text .= "<tr><td class='forumheader3'>".E107DB_LAN_9."</td><td class='forumheader3'>";
	$text .= altAuthGetPasswordSelector('e107db_password_method', $frm, $parm['e107db_password_method'], FALSE);

	$text .= "</td></tr>";

	$text .= "<tr><td colspan='2'><br />".E107DB_LAN_11."</td></tr>";

	$text .= alt_auth_get_field_list('e107db',$frm, $parm, TRUE);

	$text .= "</table><div class='buttons-bar center'>";
	$text .= $frm -> form_button("submit", "update", LAN_ALT_UPDATESET);
	$text .= '</div>';
	$text .= $frm -> form_close();

	$ns->tablerender(E107DB_LAN_10, $text);
	
	$ns->tablerender(LAN_ALT_40.LAN_ALT_41,alt_auth_test_form('e107db',$frm));
}

require_once(e_ADMIN.'footer.php');



function e107db_conf_adminmenu()
{
	alt_auth_adminmenu();
}

?>
