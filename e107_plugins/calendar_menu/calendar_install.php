<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/calendar_install.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-08-11 21:24:42 $
|     $Author: e107steved $
|
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }



class calendarInstall
{
/*
  function calendarInstall()
  {
    echo "Calendar constructor routine<br />";
  }
*/

  // Installation routine  
  function postInstall($param)
  {
//    echo "Calendar install routine<br />";
	$result = $this->insertDefaultCategory(FALSE);
	return $result;
  }
  
  
  function preUninstall($param)
  {
//    echo "Calendar uninstall routine<br />";
  }
  
  
  function postUpgrade($param)
  {
//    echo "Calendar upgrade routine<br />";
	$result = $this->insertDefaultCategory(TRUE);
	return $result;
  }

  // Insert the text for the default category into the DB here  
  function insertDefaultCategory($isUpgrade = FALSE)
  {
    global $sql;

	require_once('ecal_class.php');	// Gets the define for the 'Default' category
	if ($isUpgrade && $sql->db_Select('event_cat','event_cat_name',"event_cat_name='".EC_DEFAULT_CATEGORY."' ", TRUE))
	{
	  return EC_ADINST_LAN_08.'<br />';
	}
$ec_insert_entries = "INSERT INTO `#event_cat` (event_cat_name, event_cat_description, event_cat_ahead, event_cat_msg1, event_cat_msg2, event_cat_lastupdate)
 VALUES ('".EC_DEFAULT_CATEGORY."', '".EC_ADINST_LAN_03."', 5,
'".EC_ADINST_LAN_01."', '".EC_ADINST_LAN_02."',
'".intval(time())."') ";
	$result = $sql->db_Select_gen($ec_insert_entries);
	return ($result) ? EC_ADINST_LAN_06.'<br />' : EC_ADINST_LAN_07.'<br />';
  }
}

?>
