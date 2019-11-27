<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Cache handler
 *
 * $URL$
 * $Id$
*/

if (!defined('e107_INIT')) { exit; }

define('CACHE_PREFIX','<?php exit;');

/**
 * Class to cache data as files, improving site speed and throughput.
 * FIXME - pref independant cache handler, cache drivers
 *
 * @package     e107
 * @subpackage	e107_handlers
 * @version     $Id$
 * @author      e107 Inc
 */
class ecache {

	public $CachePageMD5;
	public $CachenqMD5;
	public $UserCacheActive;			// Checkable flag - TRUE if user cache enabled
	public $SystemCacheActive;			// Checkable flag - TRUE if system cache enabled

	const CACHE_PREFIX = '<?php exit;';

	function __construct()
	{
		//$this->UserCacheActive = e107::getPref('cachestatus');
		//$this->SystemCacheActive = e107::getPref('syscachestatus');
	}

	/**
	 * Set the MD5 Hash
	 */
	public function setMD5($text, $hash=true)
	{
		if($text === null)
		{
			$this->CachePageMD5 = md5(e_BASE.e_LANGUAGE.THEME.USERCLASS_LIST.defset('e_QUERY').filemtime(THEME.'theme.php'));
			return $this;
		}

		$this->CachePageMD5 = ($hash === true) ? md5($text) : $text;
		return $this;
	}


	public function getMD5()
	{
		return $this->CachePageMD5;
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
						$this->CachePageMD5 = md5(e_BASE.e_LANGUAGE.THEME.USERCLASS_LIST.defset('e_QUERY').filemtime(THEME.'theme.php'));
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
		
		if($syscache === true)
		{
			$CheckTag = ''; // no MD5 on system cache. XXX To be Checked. 
		}

		$fname = e_CACHE_CONTENT.$q.$CheckTag.'.cache.php';
		//echo "cache f_name = $fname <br />";
		return $fname;
	}

	/**
	 * Retrieve Cache data.
	 * @param $CacheTag
	 * @param bool|int $MaximumAge the time in minutes before the cache file 'expires'
	 * @param bool $ForcedCheck check even if cache pref is disabled.
	 * @param bool $syscache set to true when checking sys cache.
	 * @return string
	 * @desc Returns the data from the cache file associated with $query, else it returns false if there is no cache for $query.
	 * @scope public
	 */
	public function retrieve($CacheTag, $MaximumAge = false, $ForcedCheck = false, $syscache = false)
	{
		if(($ForcedCheck != false ) || ($syscache == false && $this->UserCacheActive) || ($syscache == true && $this->SystemCacheActive) && !e107::getParser()->checkHighlighting())
		{
			$cache_file = (isset($this) && $this instanceof ecache ? $this->cache_fname($CacheTag, $syscache) : self::cache_fname($CacheTag, $syscache));

			if(file_exists($cache_file))
			{
				if ($MaximumAge !== false && (filemtime($cache_file) + ($MaximumAge * 60)) < time()) {
					unlink($cache_file);
					return false;
				}
				else
				{
					$ret = file_get_contents($cache_file);
					if (substr($ret,0,strlen(self::CACHE_PREFIX)) == self::CACHE_PREFIX)
					{
						$ret = substr($ret, strlen(self::CACHE_PREFIX));
					}
					elseif(substr($ret,0,5) == '<?php')
					{
						$ret = substr($ret, 5);		// Handle the history for now
					}
					return $ret;
				}
			}
			else
			{
			//	e107::getDebug()->log("Couldn't find cache file: ".json_encode($cache_file));
				return false;
			}
		}
		return false;
	}

	/**
	* @return string 
	* @param string $CacheTag
	* @param int $MaximumAge the time in minutes before the cache file 'expires'
	 * @param boolean $force
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
			return self::retrieve($CacheTag, $MaximumAge, $ForcedCheck, true);
		}
	}


	/**
	 *
	 * @param string $CacheTag - name of tag for future retrieval - should NOT contain an MD5. 
	 * @param string $Data - data to be cached
	 * @param boolean $ForceCache [optional] if TRUE, writes cache even when disabled in admin prefs. 
	 * @param boolean $bRaw [optional] if TRUE, writes data exactly as provided instead of prefacing with php leadin
	 * @param boolean $syscache [optional]
	 * @return none
	 */
	public function set($CacheTag, $Data, $ForceCache = false, $bRaw=0, $syscache = false)
	{
		if(defined('E107_INSTALL') && E107_INSTALL === true)
		{
			return null;
		}

		if(($ForceCache != false ) || ($syscache == false && $this->UserCacheActive) || ($syscache == true && $this->SystemCacheActive) && !e107::getParser()->checkHighlighting())
		{
			$cache_file = (isset($this) && $this instanceof ecache ? $this->cache_fname($CacheTag, $syscache) : self::cache_fname($CacheTag, $syscache));
			@file_put_contents($cache_file, ($bRaw? $Data : self::CACHE_PREFIX.$Data) );
			@chmod($cache_file, 0755); //Cache should not be world-writeable
			@touch($cache_file);
		}
	}

	/**
	* @return void
	* @param string $CacheTag - name of tag for future retrieval - should NOT contain an MD5
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
			self::set($CacheTag, $Data, $ForceCache, $bRaw, true);			
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
	public function clear($CacheTag = '', $syscache = false, $related = false)
	{

		$file = ($CacheTag) ? preg_replace("#\W#", "_", $CacheTag)."*.cache.php" : "*.cache.php";
		e107::getEvent()->triggerAdminEvent('cache_clear', "cachetag=$CacheTag&file=$file&syscache=$syscache");
		$ret = self::delete(e_CACHE_CONTENT, $file, $syscache);

		if($CacheTag && $related) //TODO - too dirty - add it to the $file pattern above
		{
			self::delete(e_CACHE_CONTENT, 'nq_'.$file, $syscache);
			self::delete(e_CACHE_CONTENT, 'nomd5_'.$file, $syscache);
			//ecache::delete(e_CACHE_CONTENT, 'nq_'.$file, $syscache);
			//ecache::delete(e_CACHE_CONTENT, 'nomd5_'.$file, $syscache);
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
			self::clear($CacheTag, true, $related);
		//	ecache::clear($CacheTag, true, $related);
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
	
	
	/**
	 * Clear Full Cache
	 * @param string $type: content | system| browser | db | image | js | css | library
	 * @example clearAll('db');
	 */
	 
	function clearAll($type,$mask = null)
	{		
		$path = null;
		
		if($type =='content')
		{
			$this->clear();	
			return;
		}
			
		if($type === 'system')
		{
			$this->clear_sys();
			return;	
		}

		if($type === 'browser')
		{
			e107::getConfig()->set('e_jslib_browser_cache', time())->save(false);
			return;	
		}

		if($type === 'db')
		{
			$path = e_CACHE_DB;
			$mask = ($mask == null) ? '.*\.php' : $mask;
		}

		if($type === 'image')
		{
			$path = e_CACHE_IMAGE;
			$mask = ($mask == null) ? '.*(\.cache\.bin|\.jpg|\.jpeg|\.png|\.gif)' : $mask;
		}

		if($type === 'js')
		{
			$path = e_WEB."cache/";
			$mask = ($mask == null) ? '.*\.js' : $mask;
		}

		if($type === 'css')
		{
			$path = e_WEB."cache/";
			$mask = ($mask == null) ? '.*\.css' : $mask;
		}

		if($type === 'library')
		{
			$path = e_CACHE_CONTENT;
			$mask = ($mask == null) ? 'S_Library_.*\.cache\.php' : $mask;
		}

		if((null == $path) || (null == $mask))
		{
			return;
		}
		
		$fl = e107::getFile(false);
		$fl->mode = 'fname';
		$files = $fl->get_files($path, $mask);

		if($files)
		{
			foreach ($files as $file)
			{
				unlink($path.$file);
			}
		}
	}
	
}
