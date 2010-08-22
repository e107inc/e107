<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

//
// Simple functionality:
// Grab all prefs once, in one DB query. Reuse them throughout the session.
//
// get/set methods serve/consume strings (with slashes taken care of)
// getArray/setArray methods serve/consume entire arrays (since most prefs are such!)
//
// NOTE: Use of this class is VALUABLE (efficient) yet not NECESSARY (i.e. the system
//       will not break if it is ignored)... AS LONG AS there is no path consisting of:
//             - modify pref value(s) IGNORING this class
//  - retrieve pref value(s) USING this class
//       (while processing a single web page)
//  Just to be safe I have changed a number of menu_pref edits to use setArray().
//

class prefs 
{
	var $prefVals;
	var $prefArrays;

	// Default prefs to load
	var $DefaultRows = "e107_name='e107' OR e107_name='menu_pref' OR e107_name='notify_prefs'";

	// Read prefs from DB - get as many rows as are required with a single query.
	// $RowList is an array of pref entries to retrieve.
	// If $use_default is TRUE, $RowList entries are added to the default array. Otherwise only $RowList is used.
	// Returns TRUE on success (measured as getting at least one row of data); false on error.
	// Any data read is buffered (in serialised form) here - retrieve using get()
	function ExtractPrefs($RowList = "", $use_default = FALSE) 
	{
		global $sql;
		$Args = '';
		if($use_default)
		{
			$Args = $this->DefaultRows;
		}
		if(is_array($RowList))
		{
			foreach($RowList as $v)
			{
				$Args .= ($Args ? " OR e107_name='{$v}'" : "e107_name='{$v}'");
			}
		}
		if (!$sql->db_Select('core', '*', $Args, 'default'))
		{
			return FALSE;
		}
		while ($row = $sql->db_Fetch())
		{
			$this->prefVals['core'][$row['e107_name']] = $row['e107_value'];
		}
		return TRUE;
	}


	/**
	* Return current pref string $name from $table (only core for now)
	*
	* - @param  string $name -- name of pref row
	* - @param  string $table -- "core"
	* - @return  string pref value, slashes already stripped. FALSE on error
	* - @access  public
	*/
	function get($Name) 
	{
		if(isset($this->prefVals['core'][$Name]))
		{
			if($this->prefVals['core'][$Name] != '### ROW CACHE FALSE ###')
			{
				return $this->prefVals['core'][$Name];		// Dava from cache
			} 
			else 
			{
				return false;
			}
		}

		// Data not in cache - retrieve from DB
		$get_sql = new db; // required so sql loops don't break using $tp->toHTML(). 
		if($get_sql->db_Select('core', '*', "`e107_name` = '{$Name}'", 'default')) 
		{
			$row = $get_sql->db_Fetch();
			$this->prefVals['core'][$Name] = $row['e107_value'];
			return $this->prefVals['core'][$Name];
		} 
		else 
		{	// Data not in DB - put a 'doesn't exist' entry in cache to save another DB access
			$this->prefVals['core'][$Name] = '### ROW CACHE FALSE ###';
			return false;
		}
	}

	/**
	* Return current array from pref string $name in $table (core only for now)
	*
	* - @param:  string $name -- name of pref row
	* - @param  string $table -- "core" only now
	* - @return  array pref values
	* - @access     public
	*/
	// retrieve prefs as an array of values
	function getArray($name) {
		return unserialize($this->get($name));
	}


	/**
	* Update pref set and cache
	*
	* @param  string val -- pre-serialized string
	* @param  string $name -- name of pref row
	* @param  string $table -- "core" or "user"
	* @global  $$name
	* @access  public
	*
	* set("val")    == 'core', 'pref'
	* set("val","rowname")   == 'core', rowname
	* set("val","","user")   == 'user', 'user_pref' for current user
	* set("val","","user",uid)   == 'user', 'user_pref' for user uid
	* set("val","fieldname","user")  == 'user', fieldname
	*
	*/
	function set($val, $name = "", $table = "core", $uid = USERID) {
		global $sql;
		if (!strlen($name)) {
			switch ($table) {
				case 'core':
				$name = "pref";
				break;
				case 'user':
				$name = "user_pref";
				break;
			}
		}
		$val = addslashes($val);

		switch ($table ) {
			case 'core':
			if(!$sql->db_Update($table, "e107_value='$val' WHERE e107_name='$name'"))
			{
				$sql->db_Insert($table, "'{$name}', '{$val}'");
			}
			$this->prefVals[$table][$name] = $val;
			unset($this->prefArrays[$table][$name]);
			break;
			case 'user':
			$sql->db_Update($table, "user_prefs='$val' WHERE user_id=$uid");
			break;
		}
	}


	/**
	* Update pref set and cache
	*
	* - @param  string $name -- name of pref row
	* - @param  string $table -- "core" or "user"
	* - @global  $$name
	* - @access  public
	*
	* set()    == core, pref
	* set("rowname")   == core, rowname
	* set("","user")   == user, user_pref for current user
	* set("","user",uid)   == user, user_pref for user uid
	* set("fieldname","user")  == user, fieldname
	*
	* all pref sets other than menu_pref get toDB()
	*/
	function setArray($name = "", $table = "core", $uid = USERID) {
		global $tp;

		if (!strlen($name)) {
			switch ($table) {
				case 'core':
				$name = "pref";
				break;
				case 'user':
				$name = "user_pref";
				break;
			}
		}

		global $$name;
		if ($name != "menu_pref") {
			foreach($$name as $key => $prefvalue) {
				$$name[$key] = $tp->toDB($prefvalue);
			}
		}

		$tmp = serialize($$name);
		$this->set($tmp, $name, $table, $uid);
	}
}
?>