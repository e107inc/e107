<?php

/**
* User input cleaning class
*
* @package e107
* @version $Revision: 1.3 $
* @author $Author: streaky $
*/
class einput {

	/**
	 * Escape SQL data to prevent injections
	 *
	 * @param string $string [dirty input data]
	 * @return string [escaped data]
	 */
	function sql_escape_string($string = "") {
		return mysql_real_escape_string($string);
	}

	/**
	 * Handle user input, including SQL escaping, number validation (integers), word value cleaning and custom regexp cleaning
	 *
	 * @param string $string Input data
	 * @param bool $word_value Data should be a word value [a-z, A-Z, 0-9]
	 * @param bool $numeric Data should be numeric [0-9]
	 * @param bool $sql Input data will be used in an sql query (escape data to prevent SQL injections)
	 * @param string $custom_regexp preg_replace() regexp to perform on input data (replaced with a null string), see http://us2.php.net/preg-replace
	 * @return string [int if numeric == true]
	 */
	function clean_input($string, $sql = false, $word_value = false, $integer = false, $custom_regexp = false) {
		
		if ($numeric == true) {
			return intval($string);
		}
		if( $word_value == true) {
			$string = preg_replace("#\W#", "", $string);
		}
		if ($custom_regexp == true) {
			preg_replace($custom_regexp, "", $string);
		}
		if ($sql == true) {
			$string = (is_object($this) ? $this->sql_escape_string($string) : einput::sql_escape_string($string));
		}
		
		return $string;
	}
}

?>