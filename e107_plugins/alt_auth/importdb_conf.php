<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Alt_auth plugin - 'importdb' configuration
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
include_lan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_importdb_conf.php');
include_lan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_alt_auth.php');
define('ALT_AUTH_ACTION', 'importdb');
require_once(e_PLUGIN.'alt_auth/alt_auth_adminmenu.php');
require_once(e_PLUGIN.'alt_auth/extended_password_handler.php');


if($_POST['update'])
{
//	$message = update_importdb_prefs();
	$message = alt_auth_post_options('importdb');
}

if($message)
{
	e107::getRender()->tablerender("","<div style='text-align:center;'>".$message."</div>");
}


show_importdb_form();

function show_importdb_form()
{
	$ns = e107::getRender();
	

	$parm = altAuthGetParams('importdb');

	$frm = new form;
	$text = $frm -> form_open('post', e_SELF);
	$text .= "<table cellpadding='0' cellspacing='0' class='adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>";


	$text .= "<tr><td colspan='2' class='forumheader3'>".IMPORTDB_LAN_11."</td></tr>";
	$text .= "<tr><td>".IMPORTDB_LAN_9."</td><td>";

	$text .= altAuthGetPasswordSelector('importdb_password_method', $frm, $parm['importdb_password_method'], TRUE);

	$text .= "</td></tr>";

	$text .= "</table><div class='buttons-bar center'>";
	$text .= $frm -> form_button("submit", "update", LAN_ALT_UPDATESET);
	$text .= "</div>";
	$text .= $frm -> form_close();

	$ns -> tablerender(IMPORTDB_LAN_10, $text);

	$ns->tablerender(LAN_ALT_40.LAN_ALT_41,alt_auth_test_form('importdb',$frm));

}

require_once(e_ADMIN.'footer.php');


function importdb_conf_adminmenu()
{
	alt_auth_adminmenu();
}

?>
