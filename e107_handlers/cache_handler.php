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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/cache_handler.php,v $
|     $Revision: 1.8 $
|     $Date: 2004-12-10 21:51:15 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

/**
 * Class to cache data as files, improving site speed and throughput.
 *
 * @package     e107
 * @version     $Revision: 1.8 $
 * @author      $Author: streaky $
 */
class ecache {
	/**
	* @return string
	* @desc Internal class function that returns an md5 hash of e_BASE.e_LANGUAGE.THEME.USERCLASS.e_QUERY - used for the filename of the cache file, making sure, for example, that the file is unique to the theme.
	* @scope private
	*/
	function e107cache_page_md5() {
		return md5(e_BASE.e_LANGUAGE.THEME.USERCLASS.e_QUERY);
	}

	/**
	* @return string
	* @param string $query
	* @desc Internal class function that returns the filename of a cache file based on the query.
	* @scope private
	*/
	function cache_fname($query) {
		global $FILES_DIRECTORY;
		$q = preg_replace("#\W#", "_", $query);
		$fname = "./".e_BASE.$FILES_DIRECTORY."cache/".$q."-".$this->e107cache_page_md5().".cache.php";
		return $fname;
	}

	/**
	* @return string
	* @param string $query
	* @desc Returns the data from the cache file associated with $query, else it returns false if there is no cache for $query.
	* @scope public
	*/
	function retrieve($query) {
		global $pref, $FILES_DIRECTORY;
		if ($pref['cachestatus']) //Save to file
		{
			if ($cache_file = $this->cache_fname($query)) {
				$ret = file_get_contents($cache_file);
				$ret = substr($ret, 6);
				if ($ret == false) {
					return FALSE;
				}
				return ('' == $ret) ? '<!-- null -->' :
				$ret;
			} else {
				return FALSE;
			}
		}
	}

	/**
	* @return void
	* @param string $query
	* @param string $text
	* @desc Creates / overwrites the cache file for $query, $text is the data to store for $query.
	* @scope public
	*/
	function set($query, $text) {
		global $pref, $FILES_DIRECTORY;
		if ($pref['cachestatus']) {
			$cache_file = $this->cache_fname($query);
			file_put_contents($cache_file, "<?php\n<!-- BEGIN CACHE FILE: $query -->\n\n".$text."\n\n<!-- END CACHE FILE: $query -->");
			@chmod($cache_file, 0777);
		}
	}

	/**
	* @return bool
	* @param string $query
	* @desc Deletes cache files. If $query is set, deletes files named {$query}*.cache.php, if not it deletes all cache files - (*.cache.php)
	*/
	function clear($query = '') {
		global $pref, $FILES_DIRECTORY;
		if ($pref['cachestatus'] || !$query) {
			$file = ($query) ? preg_replace("#\W#", "_", $query)."*.cache.php" :
			"*.cache.php";
			$dir = "./".e_BASE.$FILES_DIRECTORY."cache/";
			$ret = $this->delete($dir, $file);
		}
		return $ret;
	}

	/**
	* @return bool
	* @param string $dir
	* @param string $pattern
	* @desc Internal class function to allow deletion of cache files using a pattern, default '*.*'
	* @scope private
	*/
	function delete($dir, $pattern = "*.*") {
		$deleted = false;
		$pattern = str_replace(array("\*", "\?"), array(".*", "."), preg_quote($pattern));
		if (substr($dir, -1) != "/") {
			$dir .= "/";
		}
		if (is_dir($dir)) {
			$d = opendir($dir);
			while ($file = readdir($d)) {
				if (is_file($dir.$file) && ereg("^".$pattern."$", $file)) {
					if (unlink($dir.$file)) {
						$deleted[] = $file;
					}
				}
			}
			closedir($d);
			return true;
		} else {
			return false;
		}
	}
}

?>