<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/e107_handlers/pref_class.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

//
// Simple functionality:
//	Grab all prefs once, in one DB query. Reuse them throughout the session.
//
// get/set methods serve/consume strings (with slashes taken care of)
// getArray/setArray methods serve/consume entire arrays (since most prefs are such!)
//
// NOTE: Use of this class is VALUABLE (efficient) yet not NECESSARY (i.e. the system
//       will not break if it is ignored)... AS LONG AS there is no path consisting of:
//             - modify pref value(s) IGNORING this class
//		- retrieve pref value(s) USING this class
//       (while processing a single web page)
//	 Just to be safe I have changed a number of menu_pref edits to use setArray().
//

class prefs {
	var $prefVals;
	var $prefArrays;

	/**
	* Constructor
	*/
	function prefs() {
		global $sql;
		/* Preload core table */
		$table = 'core';
		$sql->db_Select($table);
		while ($row = $sql -> db_Fetch()) {
			extract($row);
			$this -> prefVals[$table][$e107_name] = stripslashes($e107_value);
		}
	}

	/**
	* Return current pref string $name from $table (only core for now)
	*
	* - @param		string $name -- name of pref row
	* - @param		string $table -- "core"
	* - @return		string pref value, slashes already stripped
	* - @access		public
	*/
	function get($name,$table="core"){	// retrieve the prefs for $name
		if($table == 'core') {
			return $this -> prefVals[$table][$name];
		}
		return FALSE;
	}

	/**
	* Return current array from pref string $name in $table (core only for now)
	*
	* - @param:		string $name -- name of pref row
	* - @param		string $table -- "core" only now
	* - @return		array pref values
	* - @access					public
	*/
	// retrieve prefs as an array of values
	function getArray($name,$table="core") {
		if(!is_array($this -> prefArrays[$table][$name])) {
			$this -> prefArrays[$table][$name] = unserialize($this -> get($name,$table));
		}
		return $this -> prefArrays[$table][$name];
	}


	/**
	* Update pref set and cache
	*
	* @param		string val -- pre-serialized string
	* @param		string $name -- name of pref row
	* @param		string $table -- "core" or "user"
	* @global		$$name
	* @access		public
	*
	* set("val") 			== 'core', 'pref'
	* set("val","rowname") 		== 'core', rowname
	* set("val","","user") 		== 'user', 'user_pref' for current user
	* set("val","","user",uid) 		== 'user', 'user_pref' for user uid
	* set("val","fieldname","user") 	== 'user', fieldname
	*
	*/
	function set($val,$name="",$table = "core", $uid=USERID) {
		global $sql;
		if (!strlen($name)) {
			switch ($table) {
				case 'core':	$name = "pref"; break;
				case 'user':	$name = "user_pref"; break;
			}
		}
		$val = addslashes($val);

		switch ( $table ) {
			case 'core':
			$sql -> db_Update($table, "e107_value='$val' WHERE e107_name='$name'");
			$this->prefVals[$table][$name]=$val;
			unset($this->prefArrays[$table][$name]);
			break;
			case 'user':
			$sql -> db_Update($table, "user_prefs='$val' WHERE user_id=$uid");
			break;
		}
	}


	/**
	* Update pref set and cache
	*
	* - @param		string $name -- name of pref row
	* - @param		string $table -- "core" or "user"
	* - @global		$$name
	* - @access		public
	*
	* set() 			== core, pref
	* set("rowname") 		== core, rowname
	* set("","user") 		== user, user_pref for current user
	* set("","user",uid) 		== user, user_pref for user uid
	* set("fieldname","user") 	== user, fieldname
	*
	* all pref sets other than menu_pref get toDB()
	*/
	function setArray($name="",$table = "core", $uid=USERID){
		global $tp;

		if (!strlen($name)) {
			switch ($table) {
				case 'core':	$name = "pref"; break;
				case 'user':	$name = "user_pref"; break;
			}
		}

		global $$name;
		if ($name != "menu_pref") {
			foreach($$name as $key => $prefvalue){
				$$name[$key] = $tp -> toDB($prefvalue);
			}
		}

		$tmp = serialize($$name);
		$this->set($tmp,$name,$table,$uid);
	}
}
?>