<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
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

/**
* Class to cache data as files, improving site speed and throughput.
*
* @package     e107
* @version     $Revision$
* @author      $Author$
*/
class ecache {

	var $CachePageMD5;
	var $CachenqMD5;

	/**
	* @return string
	* @param string $query
	* @desc Internal class function that returns the filename of a cache file based on the query.
	* @scope private
	* If the tag begins 'menu_', e_QUERY is not included in the hash which creates the file name
	*/
	function cache_fname($CacheTag) {
		global $FILES_DIRECTORY;
		if(strpos($CacheTag, "nomd5_") === 0) {
			// Add 'nomd5' to indicate we are not calculating an md5
			$CheckTag = '_nomd5';
		}
		elseif (isset($this)) {
			if (defined("THEME")) {
				if (strpos($CacheTag, "nq_") === 0)	{
					// We do not care about e_QUERY, so don't use it in the md5 calculation
					if (!$this->CachenqMD5) {
						$this->CachenqMD5 = md5(e_BASE.(defined("ADMIN") && ADMIN == true ? "admin" : "").e_LANGUAGE.THEME.USERCLASS_LIST.filemtime(THEME.'theme.php'));
					}
					// Add 'nq' to indicate we are not using e_QUERY
					$CheckTag = '_nq_'.$this->CachenqMD5;
					
				} else {
					// It's a page - need the query in the hash
					if (!$this->CachePageMD5) {
						$this->CachePageMD5 = md5(e_BASE.e_LANGUAGE.THEME.USERCLASS_LIST.e_QUERY.filemtime(THEME.'theme.php'));
					}
					$CheckTag = '_'.$this->CachePageMD5;
				}
			} else {
				// Check if a custom CachePageMD5 is in use in e_module.php.  
				$CheckTag = ($this->CachePageMD5) ? "_".$this->CachePageMD5 : "";
			}
		} else {
			$CheckTag = '';
		}
		$q = preg_replace("#\W#", "_", $CacheTag);
		$fname = './'.e_BASE.$FILES_DIRECTORY.'cache/'.$q.$CheckTag.'.cache.php';
		return $fname;
	}

	/**
	* @return string
	* @param string $query
	* @param int $MaximumAge the time in minutes before the cache file 'expires'
	* @desc Returns the data from the cache file associated with $query, else it returns false if there is no cache for $query.
	* @scope public
	*/
	function retrieve($CacheTag, $MaximumAge = false, $ForcedCheck = false) {
		global $pref, $FILES_DIRECTORY, $tp;
		if (($pref['cachestatus'] || $ForcedCheck == true) && !$tp->checkHighlighting()) {
			$cache_file = (isset($this) ? $this->cache_fname($CacheTag) : ecache::cache_fname($CacheTag));
			if (file_exists($cache_file)) {
				if ($MaximumAge != false && (filemtime($cache_file) + ($MaximumAge * 60)) < time()) {
					unlink($cache_file);
					return false;
				} else {
					$ret = file_get_contents($cache_file);
					$ret = substr($ret, 5);
					return $ret;
				}
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	* @return void
	* @param string $CacheTag - name of tag for future retrieval
	* @param string $Data - data to be cached
	* @param bool   $ForceCache (optional, default false) - if TRUE, writes cache even when disabled
	* @param bool   $bRaw (optional, default false) - if TRUE, writes data exactly as provided instead of prefacing with php leadin
	* @desc Creates / overwrites the cache file for $query, $text is the data to store for $query.
	* @scope public
	*/
	function set($CacheTag, $Data, $ForceCache = false, $bRaw=0) {
		global $pref, $FILES_DIRECTORY, $tp;
		if (($pref['cachestatus'] || $ForceCache == true) && !$tp->checkHighlighting()) {
			$cache_file = (isset($this) ? $this->cache_fname($CacheTag) : ecache::cache_fname($CacheTag));
			file_put_contents($cache_file, ($bRaw? $Data : '<?php'.$Data) );
			@chmod($cache_file, 0777);
			@touch($cache_file);
		}
	}

	/**
	* @return bool
	* @param string $CacheTag
	* @desc Deletes cache files. If $query is set, deletes files named {$CacheTag}*.cache.php, if not it deletes all cache files - (*.cache.php)
	*/
	function clear($CacheTag = '') {
		global $pref, $FILES_DIRECTORY;
		$file = ($CacheTag) ? preg_replace("#\W#", "_", $CacheTag)."*.cache.php" : "*.cache.php";
		$dir = "./".e_BASE.$FILES_DIRECTORY."cache/";
		$ret = ecache::delete($dir, $file);
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
				if (is_file($dir.$file) && preg_match("/^{$pattern}$/", $file)) {
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