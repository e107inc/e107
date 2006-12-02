<?php

function alt_auth_get_authlist()
{
	$authlist = array("e107");
	$handle=opendir(e_PLUGIN."alt_auth");
	while ($file = readdir($handle))
	{
		if(preg_match("/^(.*)_auth\.php/",$file,$match))
		{
			$authlist[] = $match[1];
		}
	}
	closedir($handle);
	return $authlist;
}

function alt_auth_adminmenu()
{
	global $authlist;
	echo " ";
	if(!is_array($authlist))
	{
		$authlist = alt_auth_get_authlist();
	}
	define("ALT_AUTH_ACTION", "main");

	$var['main']['text'] = "Main config";
	$var['main']['link'] = e_PLUGIN."alt_auth/alt_auth_conf.php";
	show_admin_menu("alt auth", ALT_AUTH_ACTION, $var);
	$var = array();
	foreach($authlist as $a)
	{
		if($a != 'e107')
		{
			$var[$a]['text'] = "Config {$a}";
			$var[$a]['link'] = e_PLUGIN."alt_auth/{$a}_conf.php";
		}
	}
	show_admin_menu("Auth methods", ALT_AUTH_ACTION, $var);
}
?>