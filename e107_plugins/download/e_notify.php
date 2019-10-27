<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

// v2.x Standard 
class download_notify extends notify // plugin-folder + '_notify' 
{		
	function config()
	{
		$config = array();
	
		$config[] = array(
			'name'			=> LAN_DL_NT_01, //  "Broken download reported"
			'function'		=> "user_download_brokendownload_reported",
			'category'		=> ''
		);	
		
		return $config;
	}
	
	function user_download_brokendownload_reported($data) 
	{
		$download_url = e107::url('download', 'item', $data, array('mode' => 'full')); 
		
		if (isset($data['id']) && isset($data['data']))
		{
			$message = 'Notify test: Broken download reported';
		}
		else
		{	
			$message = LAN_DL_NT_02;
			$message .= " <a href=".$download_url.">".$data['download_name']."</a><br>"; 
			$message .= str_replace("[x]", $data['user'], LAN_DL_NT_03)."<br>";
			$message .= $data['report_add']; 
		}
		
		$this->send('user_download_brokendownload_reported', LAN_DL_NT_01, $message);
	}
	
}