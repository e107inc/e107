<?php

/**
* User input cleaning class
*
* @package e107
* @version $Revision: 1.5 $
* @author $Author: streaky $
*/
class einput {

	/**
	 * Handle user input, including SQL escaping, number validation (integers), word value cleaning and custom regexp cleaning
	 *
	 * @param string $string Input data
	 * @param bool $word_value Data should be a word value [a-z, A-Z, 0-9]
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
		return mysql_real_escape_string($string);
	}

	/**
	 * Strip slashes from string - takes into account magic_quotes_gpc setting, i.e. only stips if it's on - or the second arg is true
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