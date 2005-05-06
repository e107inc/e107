<?php
	
// e107 requires PHP > 4.3.0, all functions that are used in e107, introduced in newer
// versions than that should be recreated in here for compatabilty reasons..
	
if (!function_exists('file_put_contents')) {
	/**
	* @return int
	* @param string $filename
	* @param mixed $data
	* @desc Write a string to a file
	*/
	function file_put_contents($filename, $data) {
		if (($h = @fopen($filename, 'w+')) === false) {
			return false;
		}
		if (($bytes = @fwrite($h, $data)) === false) {
			return false;
		}
		fclose($h);
		return $bytes;
	}
}


// Following code is borrowed from PEAR::Compat - <http://pear.php.net/package/PHP_Compat/>


/**
 * Replace stripos()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.stripos
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.2 $
 * @since       PHP 5
 * @require     PHP 4.0.1 (trigger_error)
 */
if (!function_exists('stripos')) {
    function stripos($haystack, $needle, $offset = null)
    {
        if (!is_scalar($haystack)) {
            trigger_error('stripos() expects parameter 1 to be string, ' . gettype($haystack) . ' given', E_USER_WARNING);
            return false;
        }

        if (!is_scalar($needle)) {
            trigger_error('stripos() needle is not a string or an integer.', E_USER_WARNING);
            return false;
        }

        if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
            trigger_error('stripos() expects parameter 3 to be long, ' . gettype($offset) . ' given', E_USER_WARNING);
            return false;
        }

        // Manipulate the string if there is an offset
        $fix = 0;
        if (!is_null($offset)) {
            if ($offset > 0) {
                $haystack = substr($haystack, $offset, strlen($haystack) - $offset);
                $fix = $offset;
            }
        }

        $segments = explode(strtolower($needle), strtolower($haystack), 2);

        // Check there was a match
        if (count($segments) == 1) {
            return false;
        }

        $position = strlen($segments[0]) + $fix;
        return $position;
    }
}
	
?>