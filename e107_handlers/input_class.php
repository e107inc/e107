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
|     $Revision: 1.8 $
|     $Date: 2005-06-30 13:01:12 $
|     $Author: streaky $
+-------------------------------------------------------------+
*/

/**
* User input cleaning class
*
* @package e107
* @version $Revision: 1.8 $
* @author $Author: streaky $
*/
class einput {

	/**
	 * Handle user input, including SQL escaping, number validation (integers), word value cleaning and custom regexp cleaning
	 * 
	 * Example (escapes data for use in an SQL query and forces it to word values ([a-z, A-Z, 0-9, _]) - strip slashes first if from request data - use einput::strip_input():
	 * <code>$text = einput::clean_input($text, true, true);</code>
	 *
	 * @param string $string Input data
	 * @param bool $word_value Data should be a word value [a-z, A-Z, 0-9, _]
	 * @param bool $numeric Data should be a number
	 * @param bool $sql Input data will be used in an sql query (escape data to prevent SQL injections)
	 * @param string $custom_regexp preg_replace() regexp to perform on input data (replaced with a null string), see http://us2.php.net/preg-replace
	 * @return string clean data
	 */
	function clean_input($string = "", $sql = false, $word_value = false, $numeric = false, $custom_regexp = false) {

		if ($numeric == true && is_numeric($string) == false) {
			return intval($string);
		}
		if( $word_value == true && is_numeric($string) == false) {
			$string = preg_replace("#\W#", "", $string);
		}
		if ($custom_regexp == true) {
			preg_replace($custom_regexp, "", $string);
		}
		if ($sql == true && is_numeric($string) == false) {
			$string = einput::sql_escape_string($string);
		}

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
}

?>