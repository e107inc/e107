<?php
/*
* e107 website system
*
* Copyright (c) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom FAQ install/uninstall/update routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/featurebox/featurebox_setup.php,v $
* $Revision: 1.1 $
* $Date: 2009-11-24 14:48:34 $
* $Author: e107coders $
*
*/

class featurebox_setup
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
		
		$query = "
		INSERT INTO #featurebox (`fb_id`, `fb_title`, `fb_text`, `fb_mode`, `fb_class`, `fb_rendertype`, `fb_template`, `fb_order`, `fb_image`, `fb_imageurl`, `fb_category`) VALUES 
		(1, 'Default Title', 'Default Message', 0, 0, 0, '0', 0, '', '', 0);		
		";
		
		$query2 = "
		INSERT INTO #featurebox_cat (`fb_cat_id`, `fb_cat_title`, `fb_cat_class`, `fb_cat_order`) VALUES 
		(1, 'General', 0, 0);
		";
		
		//FIXME - I should be able to put both INSERTs into the same $query. MySQL class issue. 
		$status = ($sql->db_Select_gen($query)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$mes->add("Adding Default table data.",$status);
		
		$status = ($sql->db_Select_gen($query2)) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$mes->add("Adding Default table data.",$status);
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
?>