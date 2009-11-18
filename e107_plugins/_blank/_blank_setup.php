<?php
/*
* e107 website system
*
* Copyright (C) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom install/uninstall/update routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/_blank/_blank_setup.php,v $
* $Revision: 1.2 $
* $Date: 2009-11-18 01:49:18 $
* $Author: marj_nl_fr $
*
*/

class _blank_setup
{
	
 	function install_pre($var)
	{
		// print_a($var);
		// echo "custom install 'pre' function<br /><br />";
	}

	function install_post($var)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();
		
	//	$query = "INSERT INTO #_blank SQL insert query goes here;";
		
	//	if($sql->db_Select_gen($query))
		{
			$mes->add("Custom - Install Message.", E_MESSAGE_SUCCESS);
		}
	//	else
		{
			$mes->add("Custom - Failed to add default table data.", E_MESSAGE_ERROR);	
		}

	}
	
	function uninstall_options()
	{
	
		$listoptions = array(0=>'option 1',1=>'option 2');
		
		$options = array();
		$options['mypref'] = array(
				'label'		=> 'Custom Uninstall Label',
				'preview'	=> 'Preview Area',
				'helpText'	=> 'Custom Help Text',
				'itemList'	=> $listoptions,
				'itemDefault'	=> 1
		);
		
		return $options;
	}
	

	function uninstall_post($var)
	{
		// print_a($var);
	}

	function upgrade_post($var)
	{
		// $sql = e107::getDb();
	}
	
}
?>