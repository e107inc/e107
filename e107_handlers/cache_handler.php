<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Cache handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/cache_handler.php,v $
 * $Revision: 1.16 $
 * $Date: 2009-11-18 01:04:43 $
 * $Author: e107coders $
*/

if (!defined('e107_INIT')) { exit; }

define('CACHE_PREFIX','<?php exit;');

/**
 * Class to cache data as files, improving site speed and throughput.
 * 
 * @package     e107
 * @category	e107_handlers
 * @version     $Revision: 1.16 $
 * @author      $Author: e107coders $
 */
class ecache {

	var $CachePageMD5;
	var $CachenqMD5;
	var $UserCacheActive;			// Checkable flag - TRUE if user cache enabled
	var $SystemCacheActive;			// Checkable flag - TRUE if system cache enabled

	function ecache()
	{
		global $pref;
		$this->UserCacheActive = varsettrue($pref['cachestatus']);
		$this->SystemCacheActive = varsettrue($pref['syscachestatus']);
	}

	/**
	* @return string
	* @param string $query
	* @desc Internal class function that returns the filename of a cache file based on the query.
	* @scope private
	* If the tag begins 'menu_', e_QUERY is not included in the hash which creates the file name
	*/
	function cache_fname($CacheTag, $syscache = false)
	{
		global $FILES_DIRECTORY;
		if(strpos($CacheTag, "nomd5_") === 0) {
			// Add 'nomd5' to indicate we are not calculating an md5
			$CheckTag = '_nomd5';
		}
		elseif (isset($this) && $this instanceof ecache) 
		{
			if (defined("THEME")) 
			{
				if (strpos($CacheTag, "nq_") === 0)	
				{
					// We do not care about e_QUERY, so don't use it in the md5 calculation
					if (!$this->CachenqMD5) 
					{
						$this->CachenqMD5 = md5(e_BASE.(defined("ADMIN") && ADMIN == true ? "admin" : "").e_LANGUAGE.THEME.USERCLASS_LIST.filemtime(THEME.'theme.php'));
					}
					// Add 'nq' to indicate we are not using e_QUERY
					$CheckTag = '_nq_'.$this->CachenqMD5;
					
				} 
				else 
				{
					// It's a page - need the query in the hash
					if (!$this->CachePageMD5) 
					{
						$this->CachePageMD5 = md5(e_BASE.e_LANGUAGE.THEME.USERCLASS_LIST.e_QUERY.filemtime(THEME.'theme.php'));
					}
					$CheckTag = '_'.$this->CachePageMD5;
				}
			} 
			else 
			{
				// Check if a custom CachePageMD5 is in use in e_module.php.  
				$CheckTag = ($this->CachePageMD5) ? "_".$this->CachePageMD5 : "";
			}
		} 
		else 
		{
			$CheckTag = '';
		}
		$q = ($syscache ? "S_" : "C_").preg_replace("#\W#", "_", $CacheTag);
		$fname = e_CACHE.$q.$CheckTag.'.cache.php';
		//echo "cache f_name = $fname <br />";
		return $fname;
	}

	/**
	* @return string
	* @param string $query
	* @param int $MaximumAge the time in minutes before the cache file 'expires'
	* @desc Returns the data from the cache file associated with $query, else it returns false if there is no cache for $query.
	* @scope public
	*/
	function retrieve($CacheTag, $MaximumAge = false, $ForcedCheck = false, $syscache = false) 
	{
		global $pref, $tp;
		if(($ForcedCheck != false ) || ($syscache == false && varsettrue($pref['cachestatus'])) || ($syscache == true && varsettrue($pref['syscachestatus'])) && !$tp->checkHighlighting()) 
		{
			$cache_file = (isset($this) && $this instanceof ecache ? $this->cache_fname($CacheTag, $syscache) : ecache::cache_fname($CacheTag, $syscache));
			if (file_exists($cache_file)) 
			{
				if ($MaximumAge != false && (filemtime($cache_file) + ($MaximumAge * 60)) < time()) {
					unlink($cache_file);
					return false;
				} 
				else 
				{
					$ret = file_get_contents($cache_file);
					if (substr($ret,0,strlen(CACHE_PREFIX)) == CACHE_PREFIX)
					{
						$ret = substr($ret, strlen(CACHE_PREFIX));
					}
					else
					{
						$ret = substr($ret, 5);		// Handle the history for now
					}
					return $ret;
				}
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	* @return string
	* @param string $query
	* @param int $MaximumAge the time in minutes before the cache file 'expires'
	* @desc Returns the data from the cache file associated with $query, else it returns false if there is no cache for $query.
	* @scope public
	*/
	function retrieve_sys($CacheTag, $MaximumAge = false, $ForcedCheck = false)
	{
		if(isset($this) && $this instanceof ecache)
		{
			return $this->retrieve($CacheTag, $MaximumAge, $ForcedCheck, true);
		}
		else
		{
			return ecache::retrieve($CacheTag, $MaximumAge, $ForcedCheck, true);
		}
	}


	/**
	 * 
	 * @param string $CacheTag - name of tag for future retrieval
	 * @param data $Data		- data to be cached
	 * @param boolean $ForceCache [optional] if TRUE, writes cache even when disabled
	 * @param boolean $bRaw [optional] if TRUE, writes data exactly as provided instead of prefacing with php leadin
	 * @param boolean $syscache [optional]
	 * @return 
	 */
	public function set($CacheTag, $Data, $ForceCache = false, $bRaw=0, $syscache = false) 
	{
		global $pref, $FILES_DIRECTORY, $tp;
		if(($ForceCache != false ) || ($syscache == false && varsettrue($pref['cachestatus'])) || ($syscache == true && varsettrue($pref['syscachestatus'])) && !$tp->checkHighlighting()) 
		{
			$cache_file = (isset($this) && $this instanceof ecache ? $this->cache_fname($CacheTag, $syscache) : ecache::cache_fname($CacheTag, $syscache));
			file_put_contents($cache_file, ($bRaw? $Data : CACHE_PREFIX.$Data) );
			@chmod($cache_file, 0755); //Cache should not be world-writeable
			@touch($cache_file);
		}
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
	function set_sys($CacheTag, $Data, $ForceCache = false, $bRaw=0)
	{
		if(isset($this) && $this instanceof ecache)
		{
			return $this->set($CacheTag, $Data, $ForceCache, $bRaw, true);
		}
		else
		{
			ecache::set($CacheTag, $Data, $ForceCache, $bRaw, true);
		}
	}


	/**
	 * Deletes cache files. If $query is set, deletes files named {$CacheTag}*.cache.php, if not it deletes all cache files - (*.cache.php)
	 * 
	 * @param string $CacheTag
	 * @param boolean $syscache
	 * @param boolean $related clear also 'nq_' and 'nomd5_' entries 
	 * @return bool
	 * 
	 */
	function clear($CacheTag = '', $syscache = false, $related = false)
	{
		$file = ($CacheTag) ? preg_replace("#\W#", "_", $CacheTag)."*.cache.php" : "*.cache.php";
		e107::getEvent()->triggerAdminEvent('cache_clear', "cachetag=$CacheTag&file=$file&syscache=$syscache");	
		$ret = ecache::delete(e_CACHE, $file, $syscache);
		
		if($CacheTag && $related) //TODO - too dirty - add it to the $file pattern above
		{
			ecache::delete(e_CACHE, 'nq_'.$file, $syscache);
			ecache::delete(e_CACHE, 'nomd5_'.$file, $syscache);
		}
		return $ret;
	}

	/**
	* @return bool
	* @param string $CacheTag
	* @desc Deletes cache files. If $query is set, deletes files named {$CacheTag}*.cache.php, if not it deletes all cache files - (*.cache.php)
	*/
	function clear_sys($CacheTag = '', $related = false)
	{
		if(isset($this) && $this instanceof ecache)
		{
			return $this->clear($CacheTag, true, $related);
		}
		else
		{
			ecache::clear($CacheTag, true, $related);
		}
	}

	/**
	* @return bool
	* @param string $dir
	* @param string $pattern
	* @desc Internal class function to allow deletion of cache files using a pattern, default '*.*'
	* @scope private
	*/
	function delete($dir, $pattern = "*.*", $syscache = false) {
		$deleted = false;
		$pattern = ($syscache ? "S_" : "C_").$pattern;
		$pattern = str_replace(array("\*", "\?"), array(".*", "."), preg_quote($pattern));
		if (substr($dir, -1) != "/") {
			$dir .= "/";
		}
		if (is_dir($dir))
		{
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