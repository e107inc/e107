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
|     $Revision: 1.11 $
|     $Date: 2005-01-06 22:58:44 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

/**
* Class to cache data as files, improving site speed and throughput.
*
* @package     e107
* @version     $Revision: 1.11 $
* @author      $Author: streaky $
*/
class ecache {

	var $CachePageMD5;

	/**
	* @return string
	* @param string $query
	* @desc Internal class function that returns the filename of a cache file based on the query.
	* @scope private
	*/
	function cache_fname($CacheTag) {
		if($this->CachePageMD5 == ''){
			$this->CachePageMD5 = md5(e_BASE.e_LANGUAGE.THEME.USERCLASS.e_QUERY);
		}
		global $FILES_DIRECTORY;
		$q = preg_replace("#\W#", "_", $CacheTag);
		$fname = "./".e_BASE.$FILES_DIRECTORY."cache/".$q."-".$this->CachePageMD5.".cache.php";
		return $fname;
	}

	/**
	* @return string
	* @param string $query
	* @param int $MaximumAge the time in minutes before the cache file 'expires'
	* @desc Returns the data from the cache file associated with $query, else it returns false if there is no cache for $query.
	* @scope public
	*/
	function retrieve($CacheTag, $MaximumAge = false) {
		global $pref, $FILES_DIRECTORY;
		if ($pref['cachestatus']) //Save to file
		{
			$cache_file = $this->cache_fname($CacheTag);
			if(file_exists($cache_file)){
				if($MaximumAge != false && (filemtime($cache_file) + intval(($MaximumAge * 60))) > time()){
					unlink($cache_file);
					return false;
				} else {
					$ret = file_get_contents($cache_file);
					$ret = substr($ret, 5);
					return $ret;
				}
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
	function set($CacheTag, $Data) {
		global $pref, $FILES_DIRECTORY;
		if ($pref['cachestatus']) {
			$cache_file = $this->cache_fname($CacheTag);
			file_put_contents($cache_file, '<?php'.$text);
			@chmod($cache_file, 0777);
		}
	}

	/**
	* @return bool
	* @param string $query
	* @desc Deletes cache files. If $query is set, deletes files named {$query}*.cache.php, if not it deletes all cache files - (*.cache.php)
	*/
	function clear($CacheTag = '') {
		global $pref, $FILES_DIRECTORY;
		if ($pref['cachestatus'] || !$CacheTag) {
			$file = ($CacheTag) ? preg_replace("#\W#", "_", $query)."*.cache.php" : "*.cache.php";
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