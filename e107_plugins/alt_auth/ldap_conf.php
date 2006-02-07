<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/pm_conf.php
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).	
+---------------------------------------------------------------+
*/
$eplug_admin = true;
require_once("../../class2.php");
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
require_once("languages/ldap_auth_".e_LANGUAGE.".php");

$server_types[1]="LDAP";
$server_types[2]="ActiveDirectory";

$ldap_ver[1]="2";
$ldap_ver[2]="3";

if($_POST['update']){
	foreach($_POST as $k => $v){
		if(preg_match("/ldap_/",$k)){
			if($sql -> db_Select("alt_auth","*","auth_type='ldap' AND auth_parmname='{$k}' ")){
				$sql -> db_Update("alt_auth","auth_parmval='{$v}' WHERE  auth_type='ldap' AND auth_parmname='{$k}' ");
			} else {
				$sql -> db_Insert("alt_auth","'ldap','{$k}','{$v}' ");
			}
		}
	}
	$message = "Settings Updated";
}

if($message){
	$ns -> tablerender("","<div style='text-align:center;'>".$message."</div>");
}

$sql -> db_Select("alt_auth","*","auth_type = 'ldap' ");
while($row = $sql -> db_Fetch()){
	extract($row);
	$ldap[$auth_parmname]=$auth_parmval;
}

$frm = new form;
$text = $frm -> form_open("POST",e_SELF);
$text .= "<table style='width:96%'>";
$text .= "<tr><td class='forumheader3'>Server Type</td><td class='forumheader3'>";
$text .= $frm -> form_select_open("ldap_servertype");
foreach($server_types as $v){
//	echo "{$ldap_servertype} ? {$k}<br />";
	$sel = ($ldap['ldap_servertype'] == $v) ? " Selected" : "";
	$text .= $frm -> form_option($v,$sel,$v);
}
$text .= $frm -> form_select_close();
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LDAPLAN_1."</td><td class='forumheader3'>";
$text .= $frm -> form_text("ldap_server",35,$ldap['ldap_server'],60);
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LDAPLAN_2."</td><td class='forumheader3'>";
$text .= $frm -> form_text("ldap_basedn",35,$ldap['ldap_basedn'],60);
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LDAPLAN_3."</td><td class='forumheader3'>";
$text .= $frm -> form_text("ldap_user",35,$ldap['ldap_user'],60);
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LDAPLAN_4."</td><td class='forumheader3'>";
$text .= $frm -> form_text("ldap_passwd",35,$ldap['ldap_passwd'],60);
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LDAPLAN_5."</td><td class='forumheader3'>";
$text .= $frm -> form_select_open("ldap_version");
foreach($ldap_ver as $v){
	$sel = ($ldap['ldap_version'] == $v) ? " Selected" : "";
	$text .= $frm -> form_option($v,$sel,$v);
}
$text .= $frm -> form_select_close();
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader' colspan='2' style='text-align:center;'>";
$text .= $frm -> form_button("submit","update","Update settings");
$text .= "</td></tr>";

$text .= "</table>";
$text .= $frm -> form_close();

$ns -> tablerender(LDAPLAN_6,$text);



$ns -> tablerender(" ","<div style='text-align:center'><a href='".e_PLUGIN."alt_auth/alt_auth_conf.php'>Return to main alt_auth config</a></div>");


require_once(e_ADMIN."footer.php");
?>
