<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
+----------------------------------------------------------------------------+
*/
$eplug_admin = true;
require_once("../../class2.php");
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
include_lan(e_PLUGIN."alt_auth/languages/".e_LANGUAGE."/lan_otherdb_auth.php");
define("ALT_AUTH_ACTION", "otherdb");
require_once(e_PLUGIN."alt_auth/alt_auth_adminmenu.php");

if($_POST['update'])
{
	$message = update_otherdb_prefs();
}

if($message)
{
	$ns->tablerender("","<div style='text-align:center;'>".$message."</div>");
}


show_otherdb_form();

function show_otherdb_form()
{
	global $sql, $tp, $ns;
	
	$password_methods = array("md5", "plaintext");
	$db_types = array("e107" => "mysql - e107 database", "mysql" => "mysql - generic database");

	$sql -> db_Select("alt_auth", "*", "auth_type = 'otherdb' ");
	$parm = array();
	while($row = $sql->db_Fetch())
	{
		$parm[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));
	}

	$frm = new form;
	$text = $frm -> form_open("POST", e_SELF);
	$text .= "<table style='width:96%'>";

	$text .= "<tr><td class='forumheader3'>".OTHERDB_LAN_1."</td><td class='forumheader3'>";
	$text .= $frm -> form_select_open("otherdb_dbtype");
	foreach($db_types as $k => $v)
	{
		$sel = ($parm['otherdb_dbtype'] == $k) ? " Selected" : "";
		$text .= $frm -> form_option($v, $sel, $k);
	}
	$text .= $frm -> form_select_close();
	$text .= "</td></tr>";

	$text .= "<tr><td class='forumheader3'>".OTHERDB_LAN_2."</td><td class='forumheader3'>";
	$text .= $frm -> form_text("otherdb_server", 35, $parm['otherdb_server'], 120);
	$text .= "</td></tr>";

	$text .= "<tr><td class='forumheader3'>".OTHERDB_LAN_3."</td><td class='forumheader3'>";
	$text .= $frm -> form_text("otherdb_username", 35, $parm['otherdb_username'], 120);
	$text .= "</td></tr>";

	$text .= "<tr><td class='forumheader3'>".OTHERDB_LAN_4."</td><td class='forumheader3'>";
	$text .= $frm -> form_text("otherdb_password", 35, $parm['otherdb_password'], 120);
	$text .= "</td></tr>";

	$text .= "<tr><td class='forumheader3'>".OTHERDB_LAN_5."</td><td class='forumheader3'>";
	$text .= $frm -> form_text("otherdb_database", 35, $parm['otherdb_database'], 120);
	$text .= "</td></tr>";

	$text .= "<tr><td class='forumheader3'>".OTHERDB_LAN_6."</td><td class='forumheader3'>";
	$text .= $frm -> form_text("otherdb_table", 35, $parm['otherdb_table'], 120);
	$text .= "</td></tr>";

	$text .= "<tr><td class='forumheader2' colspan='2'>".OTHERDB_LAN_11."</td></tr>";

	$text .= "<tr><td class='forumheader3'>".OTHERDB_LAN_7."</td><td class='forumheader3'>";
	$text .= $frm -> form_text("otherdb_user_field", 35, $parm['otherdb_user_field'], 120);
	$text .= "</td></tr>";

	$text .= "<tr><td class='forumheader3'>".OTHERDB_LAN_8."</td><td class='forumheader3'>";
	$text .= $frm -> form_text("otherdb_password_field", 35, $parm['otherdb_password_field'], 120);
	$text .= "</td></tr>";

	$text .= "<tr><td class='forumheader3'>".OTHERDB_LAN_9."</td><td class='forumheader3'>";
	$text .= $frm -> form_select_open("otherdb_password_method");
	foreach($password_methods as $v)
	{
		$sel = ($parm['otherdb_password_method'] == $v) ? " Selected" : "";
		$text .= $frm -> form_option($v, $sel, $v);
	}
	$text .= $frm -> form_select_close();
	$text .= "</td></tr>";

	$text .= "<tr><td class='forumheader' colspan='2' style='text-align:center;'>";
	$text .= $frm -> form_button("submit", "update", "Update settings");
	$text .= "</td></tr>";

	$text .= "</table>";
	$text .= $frm -> form_close();

	$ns -> tablerender(OTHERDB_LAN_10, $text);
}

require_once(e_ADMIN."footer.php");


function update_otherdb_prefs()
{
	global $sql;
	foreach($_POST as $k => $v)
	{
		$v = base64_encode(base64_encode($v));

		if(preg_match("/otherdb_/", $k))
		{
			if($sql -> db_Select("alt_auth", "*", "auth_type='otherdb' AND auth_parmname='{$k}' "))
			{
				$sql -> db_Update("alt_auth", "auth_parmval='{$v}' WHERE  auth_type='otherdb' AND auth_parmname='{$k}' ");
			}
			else
			{
				$sql -> db_Insert("alt_auth", "'otherdb','{$k}','{$v}' ");
			}
		}
	}
	return "Settings Updated";
}

function otherdb_conf_adminmenu()
{
	alt_auth_adminmenu();
}

?>
