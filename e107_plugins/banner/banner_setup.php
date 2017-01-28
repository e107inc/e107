<?php
/*
* e107 website system
*
* Copyright (c) 2008-2016 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*
*/


class banner_setup
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

		$insert =  array(
			'banner_id'             => 0,
			'banner_clientname'     => '',
			'banner_clientlogin'    => '',
			'banner_clientpassword' => '',
			'banner_image'          => '{e_PLUGIN}banner/images/banner1.png',
			'banner_clickurl'       => 'https://e107.org',
			'banner_impurchased'    => '0',
			'banner_startdate'      => '0',
			'banner_enddate'        => '0',
			'banner_active'         => '0',
			'banner_clicks'         => '0',
			'banner_impressions'    => '0',
			'banner_ip'             => '',
			'banner_description'    => '',
			'banner_campaign'       => 'e107promo'
		);

		$status = ($sql->insert('banner', $insert)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$mes->add(LAN_DEFAULT_TABLE_DATA." banner", $status);


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


