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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/compatability_class.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-11-27 14:40:18 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

/**
 * Provides missing functionality in the form of constants and functions for older versions of PHP, and optionally to make old plugins compatable with new e107 versions.
 *
 * @package     e107
 * @version     $Revision: 1.2 $
 * @author      Author: streaky
 */
class e107Compat {

	/**
	* @return void
	* @param bool $e107
	* @desc Enables compatabilty mode, creating functions and constants from newer PHP versions, to make older PHP versions compatable with them. If $e107 == true the function will call e107Compat::e107Compat(), which will make old plugins compatable by re adding functions that were removed from e107 to be replaced by classes for example.
	*/
	function e107Compat($e107 = false) {
		$this->PHPCompatability();
		if ($e107 == true) {
			// echo 'e107Compatable Mode'; // for debug only
			$this->e107Compatability();
		}
	}

	/**
	* @return void
	* @desc Creates functions from newer PHP versions in older PHP installs.
	*/
	function PHPCompatability() {
		if (!function_exists('file_get_contents')) {
			function file_get_contents($filename) {
				$fd = fopen("$filename", "rb");
				$content = fread($fd, filesize($filename));
				fclose($fd);
				return $content;
			}
		}
		if (!function_exists('file_put_contents')) {
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
	}

	/**
	* @return void
	* @desc Creates functions used in old e107 versions, making old plugins compatable with current version.
	*/
	function e107Compatability() {
		function retrieve_cache($query) {
			global $e107cache, $e107_debug;
			if (!is_object($e107cache)) {
				return FALSE;
			}
			$ret = $e107cache->retrieve($query);
			if ($e107_debug && $ret) {
				echo "cache used for: $query <br />";
			}
			return $ret;
		}
		function set_cache($query, $text) {
			global $e107cache;
			if (!is_object($e107cache)) {
				return FALSE;
			}
			if ($e107_debug) {
				echo "cache set for: $query <br />";
			}
			$e107cache->set($query, $text);
		}
		function clear_cache($query) {
			global $e107cache;
			if (!is_object($e107cache)) {
				return FALSE;
			}
			return $e107cache->clear($query);
		}
	}
}

?>

