<?php
/*
* e107 website system
*
* Copyright (c) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom TinyMce4 install/uninstall/update routines
**
*/

if (!defined('e107_INIT')) { exit; }


class tinymce4_setup
{
	function upgrade_required()
	{
		$list = e107::getConfig()->get('e_meta_list'); 
			
		if(!empty($list) && in_array('tinymce4',$list))
		{
			return true; 	
		}	
			
		if(file_exists(e_PLUGIN."tinymce4/e_meta.php")) // Outdated file. 
		{
			e107::getMessage()->addInfo("Please delete the outdated file <b>".e_PLUGIN."tinymce4/e_meta.php</b> and then run the updating process."); 
		//	print_a(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,8)); 
			return true; 
		}
		
		return false; 
	}
	

	
/*	
 	function install_pre($var)
	{

	}

	function install_post($var)
	{
	
	}

	function uninstall_options()
	{
	
	}

	function uninstall_post($var)
	{

	}

	function upgrade_pre($var)
	{
		
	}

	function upgrade_post($var)
	{

	}
 
 */
}
