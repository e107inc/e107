<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Installation and update handler for event calendar plugin
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/languages/English_admin_calendar_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Event calendar plugin
 *
 *	Plugin file - installation and update handling
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }




class calendar_menu_setup // must match folder name ie. <pluginfolder>_setup
{

	function install_post($param)
	{
		$somethingFailed = FALSE;
		
		$mes = eMessage::getInstance();
		if ($this->insertDefaultCategory(FALSE, $mes) == FALSE)
		{
			$somethingFailed = TRUE;
		}
		
		
		if ($somethingFailed)
		{
			$mes->add(EC_ADINST_LAN_04, E_MESSAGE_SUCCESS);		
		}
		else
		{
		}

	}
  
  
	function install_pre($param)
	{
		//    echo "Calendar uninstall routine<br />";
	}



	/**
	 *	Called to see if plugin upgrade is required
	 *
	 *	@param $param mixed - no purpose currently
	 *	@return boolean TRUE if upgrade required, FALSE otherwise
	 */
	function upgrade_required($param = FALSE)
	{
		$required = FALSE;

		$data = e107::pref('calendar_menu');
		if (count($data) == 0)
		{
			$required = TRUE;
		}
		//print_a($data);
		return $required;
	}



	/**
	 *	Upgrade stage 1
	 *
	 *	@param $param mixed - no purpose currently
	 *	@return - none
	 */
	function upgrade_pre($param = FALSE)
	{
		$mes = eMessage::getInstance();
		$calPref = e107::pref('calendar_menu');
		if (count($calPref) == 0)
		{		// Need to move prefs over
			unset($calPref);

			$calNew = e107::getPlugConfig('calendar_menu');		// Initialize calendar_menu prefs.
			$corePrefs = e107::getConfig('core');				// Core Prefs Object.
			$pref = e107::pref('core');              		 	// Core Prefs Array.

			foreach($pref as $k=>$v)
			{
				if(substr($k, 0, 10) == 'eventpost_')
				{
					$calNew->add($k, $v);
					$corePrefs->remove($k);
				}
			}
			$calNew->save();
			$corePrefs->save();

			$calPref = e107::pref('calendar_menu');         // retrieve calendar_menu pref array.
			//print_a($calPref);

			
			$mes->add(EC_ADINST_LAN_10, E_MESSAGE_SUCCESS);	
		}
		else
		{
			$mes->add(EC_ADINST_LAN_09, E_MESSAGE_INFO);		// Nothing to do - prefs already moved
		}
	}



	/**
	 *	Upgrade final stage - add default category
	 *
	 *	@param $param mixed - no purpose currently
	 *	@return - none
	 */
	function upgrade_post($param)
	{
		$this->insertDefaultCategory(TRUE);
	}


	/**
	 *	Make sure default category is in calendar database; add it if not.
	 *
	 *	Adds an appropriate message to the passed message handler.
	 *	Returns TRUE if the call can be deemed a success (category present or added); FALSE on error
	 */
	function insertDefaultCategory($isUpgrade)   // Insert the text for the default category into the DB here  
	{
		$mes = eMessage::getInstance();
	    $sql = e107::getDb();
	
		require_once(e_PLUGIN.'calendar_menu/ecal_class.php');		// Gets the define for the 'Default' category
		if ($isUpgrade && $sql->db_Select('event_cat','event_cat_name',"event_cat_name='".EC_DEFAULT_CATEGORY."' "))
		{
			$mes->add(EC_ADINST_LAN_08, E_MESSAGE_INFO);		// Nothing to do - default category already present
			return TRUE;
		}
		$ec_insert_entries = "INSERT INTO `#event_cat` (event_cat_name, event_cat_description, event_cat_ahead, event_cat_msg1, event_cat_msg2, event_cat_lastupdate)
		 VALUES ('".EC_DEFAULT_CATEGORY."', '".EC_ADINST_LAN_03."', 5,
		'".EC_ADINST_LAN_01."', '".EC_ADINST_LAN_02."',
		'".intval(time())."') ";
		
		if ($result = $sql->db_Select_gen($ec_insert_entries))
		{
			$mes->add(EC_ADINST_LAN_06, E_MESSAGE_SUCCESS);	
		}
		else
		{
			$mes->add(EC_ADINST_LAN_07, E_MESSAGE_ERROR);		
		}
		return $result;
	}
}

?>