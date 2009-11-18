<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     (c) e107 Inc 2008-2009
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/calendar_setup.php,v $
|     $Revision: 1.3 $
|     $Date: 2009-11-18 01:05:23 $
|     $Author: e107coders $
|
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }


class calendar_menu_setup // must match folder name ie. <pluginfolder>_setup
{

	function install_post($param)
	{
		$mes = eMessage::getInstance();
		if($this->insertDefaultCategory(FALSE))
		{
			$mes->add(EC_ADINST_LAN_06, E_MESSAGE_SUCCESS);	
		}
		else
		{
			$mes->add(EC_ADINST_LAN_07, E_MESSAGE_ERROR);		
		}
		
		$mes->add(EC_ADINST_LAN_04, E_MESSAGE_SUCCESS);		

	}
  
  
	function install_pre($param)
	{
		//    echo "Calendar uninstall routine<br />";
	}
  
  
	function upgrade_post($param)
	{
		$mes = eMessage::getInstance();
		if($this->insertDefaultCategory(TRUE))
		{
			$mes->add(EC_ADINST_LAN_06, E_MESSAGE_SUCCESS);	
		}
		else
		{
			$mes->add(EC_ADINST_LAN_07, E_MESSAGE_ERROR);		
		}
	}


	function insertDefaultCategory($isUpgrade = FALSE)   // Insert the text for the default category into the DB here  
	{
	    $sql = e107::getDb();
	
		require_once('ecal_class.php');	// Gets the define for the 'Default' category
		if ($isUpgrade && $sql->db_Select('event_cat','event_cat_name',"event_cat_name='".EC_DEFAULT_CATEGORY."' ", TRUE))
		{
		  return EC_ADINST_LAN_08.'<br />';
		}
		$ec_insert_entries = "INSERT INTO `#event_cat` (event_cat_name, event_cat_description, event_cat_ahead, event_cat_msg1, event_cat_msg2, event_cat_lastupdate)
		 VALUES ('".EC_DEFAULT_CATEGORY."', '".EC_ADINST_LAN_03."', 5,
		'".EC_ADINST_LAN_01."', '".EC_ADINST_LAN_02."',
		'".intval(time())."') ";
		
		return $sql->db_Select_gen($ec_insert_entries);
	}
}

?>