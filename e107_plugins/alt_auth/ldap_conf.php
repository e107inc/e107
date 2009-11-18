<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/alt_auth/ldap_conf.php,v $
|     $Revision: 1.7 $
|     $Date: 2009-11-18 01:05:22 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
$eplug_admin = true;
require_once("../../class2.php");
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
include_lan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE."/admin_ldap_conf.php");
include_lan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_alt_auth.php');
define('ALT_AUTH_ACTION', 'ldap');
require_once(e_PLUGIN.'alt_auth/alt_auth_adminmenu.php');


$server_types[1]="LDAP";
$server_types[2]="ActiveDirectory";
$server_types[3]="eDirectory";

$ldap_ver[1]="2";
$ldap_ver[2]="3";

$message = '';
if($_POST['update'])
{
	$message .= alt_auth_post_options('ldap');
}


if(!function_exists('ldap_connect'))
{
	$message .= "<br /><br /><div style='color:#f00; font-weight:bold'>".LDAPLAN_11."</div><br />";
}


if($message)
{
  $ns->tablerender("","<div style='text-align:center;'>".$message."</div>");
}


$ldap['ldap_edirfilter'] == "";
$sql -> db_Select("alt_auth", "*", "auth_type = 'ldap' ");
while($row = $sql->db_Fetch())
{
  $ldap[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));		// Encoding is new for 0.8
}
//print_a($ldap);

$current_filter = "(&(cn=[USERNAME]){$ldap['ldap_edirfilter']})";

$frm = new form;
$text = $frm -> form_open("post",e_SELF);
$text .= "<table style='width:96%' class='fborder'>";
$text .= "<tr><td class='forumheader3'>".LDAPLAN_12."</td><td class='forumheader3'>";
$text .= $frm -> form_select_open("ldap_servertype");
foreach($server_types as $v)
{
	$sel = ($ldap['ldap_servertype'] == $v) ? " Selected" : "";
	$text .= $frm -> form_option($v, $sel, $v);
}
$text .= $frm -> form_select_close();
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LDAPLAN_1."</td><td class='forumheader3'>";
$text .= $frm -> form_text("ldap_server", 35, $ldap['ldap_server'], 120);
$text .= "</td></tr>";
$text .= "<tr><td class='forumheader3'>".LDAPLAN_2."</td><td class='forumheader3'>";
$text .= $frm -> form_text("ldap_basedn", 35, $ldap['ldap_basedn'], 120);
$text .= "</td></tr>";
$text .= "<tr><td class='forumheader3'>".LDAPLAN_14."</td><td class='forumheader3'>";
$text .= $frm -> form_text("ldap_ou", 35, $ldap['ldap_ou'], 60);
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LDAPLAN_3."</td><td class='forumheader3'>";
$text .= $frm -> form_text("ldap_user", 35, $ldap['ldap_user'], 120);
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LDAPLAN_4."</td><td class='forumheader3'>";
$text .= $frm -> form_text("ldap_passwd", 35, $ldap['ldap_passwd'], 120);
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LDAPLAN_5."</td><td class='forumheader3'>";
$text .= $frm -> form_select_open("ldap_version");

foreach($ldap_ver as $v)
{
	$sel = ($ldap['ldap_version'] == $v) ? " Selected" : "";
	$text .= $frm -> form_option($v, $sel, $v);
}

$text .= $frm -> form_select_close();
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LDAPLAN_7."<br /><span class='smalltext'>".LDAPLAN_8."</span></td><td class='forumheader3'>";
$text .= $frm -> form_text("ldap_edirfilter", 35, $ldap['ldap_edirfilter'], 120);
$text .= "<br /><span class='smalltext'>".LDAPLAN_9."<br />".htmlentities($current_filter)."</span></td></tr>";

	$text .= "<tr><td class='forumheader2' colspan='2'>".LAN_ALT_27."</td></tr>";

	add_extended_fields();
	$text .= alt_auth_get_field_list('ldap',$frm, $ldap, FALSE);

$text .= "<tr><td class='forumheader' colspan='2' style='text-align:center;'>";
$text .= $frm -> form_button("submit", "update", LDAPLAN_13);
$text .= "</td></tr>";

$text .= "</table>";
$text .= $frm -> form_close();

$ns -> tablerender(LDAPLAN_6,$text);
$ns->tablerender(LAN_ALT_40.LAN_ALT_41,alt_auth_test_form('ldap',$frm));

require_once(e_ADMIN."footer.php");

function ldap_conf_adminmenu()
{
	alt_auth_adminmenu();
}

?>
