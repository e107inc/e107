<?php
//**************************************************************************
//*
//*  Calendar for e107 v7xx
//*
//**************************************************************************

if(e_LANGUAGE != "English" && file_exists(e_PLUGIN."calendar_menu/languages/admin/".e_LANGUAGE.".php"))
{
	include_once(e_PLUGIN."calendar_menu/languages/admin/".e_LANGUAGE.".php");
}
else
{
	include_once(e_PLUGIN."calendar_menu/languages/admin/English.php");
}


if (e_QUERY) {
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
}

if ($action == "") {
	$action = "config";
}

$var['config']['text'] = EC_ADLAN_A10;
$var['config']['link'] = "admin_config.php?config";
	
$var['cat']['text'] = EC_ADLAN_A11;
$var['cat']['link'] ="admin_cat.php?cat";

show_admin_menu(EC_ADLAN_A12, $action, $var);

?>
