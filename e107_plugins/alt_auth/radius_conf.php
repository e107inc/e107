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
include_lan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_radius_conf.php');
include_lan(e_PLUGIN.'alt_auth/languages/'.e_LANGUAGE.'/admin_alt_auth.php');
define("ALT_AUTH_ACTION", "radius");
require_once(e_PLUGIN."alt_auth/alt_auth_adminmenu.php");

$message = '';
if($_POST['update'])
{
	$message .= alt_auth_post_options('radius');
}


if (!extension_loaded('radius'))
{
	$message .= "<br /><br /><div style='color:#f00; font-weight:bold'>".LAN_RADIUS_11."</div><br />";
}


if($message)
{
  $ns->tablerender("","<div style='text-align:center;'>".$message."</div>");
}


$sql -> db_Select("alt_auth", "*", "auth_type = 'radius' ");
while($row = $sql->db_Fetch())
{
  $radius[$row['auth_parmname']] = base64_decode(base64_decode($row['auth_parmval']));		// Encoding is new for 0.8
}


$frm = new form;
$text = $frm -> form_open("post",e_SELF);
$text .= "<table style='width:96%' class='fborder'>";
$text .= "<tr><td class='forumheader3'>".LAN_RADIUS_01."</td><td class='forumheader3'>";
$text .= $frm -> form_text("radius_server", 35, $radius['radius_server'], 120);
$text .= "</td></tr>";

$text .= "<tr><td class='forumheader3'>".LAN_RADIUS_02."</td><td class='forumheader3'>";
$text .= $frm -> form_text('radius_secret', 35, $radius['radius_secret'], 200);
$text .= "</td></tr>";

$tmp = alt_auth_get_field_list('radius',$frm, $ldap, FALSE);
if ($tmp)
{
	$text .= "<tr><td class='forumheader2' colspan='2'>".LAN_ALT_27."</td></tr>".$tmp;
	unset($tmp);
}

$text .= "<tr><td class='forumheader' colspan='2' style='text-align:center;'>";
$text .= $frm -> form_button("submit", "update", LAN_ALT_2);
$text .= "</td></tr>";

$text .= "</table>";
$text .= $frm -> form_close();

$ns -> tablerender(LAN_RADIUS_06,$text);
$ns->tablerender(LAN_ALT_40.LAN_ALT_41,alt_auth_test_form('radius',$frm));

require_once(e_ADMIN."footer.php");


function radius_conf_adminmenu()
{
	alt_auth_adminmenu();
}

?>
