<?php
/*
* e107 website system
*
* Copyright (c) 2008-2016 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom FAQ install/uninstall/update routines
*
*/
e107::includeLan(e_PLUGIN.'rss_menu/languages/'.e_LANGUAGE.'_admin_rss_menu.php');

class rss_menu_setup
{
/*	
 	function install_pre($var)
	{
		// print_a($var);
		// echo "custom install 'pre' function<br /><br />";
	}
*/
	function install_post($var)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();

		$insert = array(
			'rss_id'        => 0,
			'rss_name'      => RSS_NEWS,
			'rss_url'       => 'news',
			'rss_topicid'   => '',
			'rss_path'      => 'news',
			'rss_text'      => RSS_PLUGIN_LAN_7,
			'rss_datestamp' => time(),
			'rss_class'     => '0',
			'rss_limit'     => '9'
		);


	//	$mes->addInfo(print_a($insert,true));

		$status = ($sql->insert('rss', $insert)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$mes->add(LAN_DEFAULT_TABLE_DATA.": rss",$status);  


	}
/*	
	function uninstall_options()
	{
	
	}


	function uninstall_post($var)
	{
		// print_a($var);
	}

	function upgrade_post($var)
	{
		// $sql = e107::getDb();
	}
*/	
}
