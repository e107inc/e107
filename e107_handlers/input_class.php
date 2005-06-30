<?php

/**
+-------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/input_class.php,v $
|     $Revision: 1.9 $
|     $Date: 2005-06-30 13:18:42 $
|     $Author: streaky $
+-------------------------------------------------------------+
*/

/**
* User input cleaning class
*
* @package e107
* @version $Revision: 1.9 $
* @author $Author: streaky $
*/
class einput {

	// new, better function.. docs soon :)
	function escape($string, $gpc_data = true) {
		// Stripslashes if needed
		if ($gpc_data == true && get_magic_quotes_gpc()) {
			$string = stripslashes($string);
		}
		// Replace new lines (quick / dirty fix to issues caused by real_escape)
		$string = str_replace(array("\r", "\n"), array("--#R--", "--#N--"), $string);
		// Escape data
		$string = mysql_real_escape_string($string);
		// Put back new lines
		$string = str_replace(array("--#R--", "--#N--"), array("\r", "\n"), $string);
		return $string;
	}

	/**
	 * Escape SQL data to help prevent injections
	 *
	 * @param string $string [dirty input data]
	 * @return string [escaped data]
	 */
	function sql_escape_string($string = "") {
		// Replace new lines (quick / dirty fix to issues caused by real_escape)
		$string = str_replace(array("\r", "\n"), array("--#R--", "--#N--"), $string);
		// Escape data
		$string = mysql_real_escape_string($string);
		// Put back new lines
		$string = str_replace(array("--#R--", "--#N--"), array("\r", "\n"), $string);
		return $string;
	}

	/**
	 * Strip slashes from string, for use before escaping data for sql queries - takes into account magic_quotes_gpc setting, i.e. only stips if it's on - or the second arg is true
	 *
	 * @param string $string [input string]
	 * @param bool $ignore_magic_quotes_gpc [overide magic_quotes_gpc setting, i.e. always strip slashes
	 * @return string
	 */
	function strip_input($string = "", $ignore_magic_quotes_gpc = false) {
		if(get_magic_quotes_gpc() == true || $ignore_magic_quotes_gpc == true) {
			$string = stripslashes($string);
		}
		return $string;
	}
}

?>