<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL$
 * $Id$
 */

/**
 * File/folder manipulation handler
 *
 * @package     e107
 * @subpackage	e107_handlers
 * @version     $Id$
 * @author      e107 Inc.
 */

if (!defined('e107_INIT')) { exit; }


/*
Class to return a list of files, with options to specify a filename matching string and exclude specified directories.
get_files() is the usual entry point.
	$path - start directory (doesn't matter whether it has a trailing '/' or not - its stripped)
	$fmask - regex expression of file names to match (empty string matches all). Omit the start and end delimiters - '#' is added here.
				If the first character is '~', this becomes a list of files to exclude (the '~' is stripped)
				Note that 'special' characters such as '.' must be escaped by the caller
				There is a standard list of files which are always excluded (not affected by the leading '~')
				The regex is case-sensitive.
	$omit - specifies directories to exclude, in addition to the standard list. Does an exact, case-sensitive match.
				'standard' or empty string - uses the standard exclude list
				Otherwise a single directory name, or an array of names.
	$recurse_level - number of directory levels to search.

	If the standard file or directory filter is unacceptable in a special application, the relevant variable can be set to an empty array (emphasis - ARRAY).

setDefaults() restores the defaults - preferable to setting using a 'fixed' string. Can be called prior to using the class without knowledge of what went before.

get_dirs() returns a list of the directories in a specified directory (no recursion) - similar critera to get_files()

rmtree() attempts to remove a complete directory tree, including the files it contains


Note:
	Directory filters look for an exact match (i.e. regex not supported)
	Behaviour is slightly different to previous version:
		$omit used to be applied to just files (so would recurse down a tree even if no files match) - now used for directories
		The default file and directory filters are always applied (unless modified between instantiation/set defaults and call)

*/


class e_file
{
	/**
	 * Array of directory names to ignore (in addition to any set by caller)
	 * @var array
	 */
	public	$dirFilter;

	/**
	 * Array of file names to ignore (in addition to any set by caller)
	 * @var array
	 */
	public $fileFilter;

	/**
	 * Defines what array format should return get_files() method
	 * If one of 'fname', 'path', 'full' - numerical array.
	 * If default - associative array (depends on $finfo value).
	 *
	 * @see get_files()
	 * @var string one of the following: default (BC) | fname | path | full
	 */
	public $mode = 'default';

	/**
	 * Defines what info should gatter get_files method.
	 * Works only in 'default' mode.
	 *
	 * @var string default (BC) | image | file | all
	 */
	public $finfo = 'default';

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->setDefaults();
	}

	/**
	 * Set default parameters
	 * @return e_file
	 */
	function setDefaults()
	{
		$this->dirFilter = array('/', 'CVS', '.svn'); // Default directory filter (exact matches only)
		$this->fileFilter = array('^thumbs\.db$','^Thumbs\.db$','.*\._$','^\.htaccess$','^index\.html$','^null\.txt$','\.bak$','^.tmp'); // Default file filter (regex format)
		return $this;
	}

	/**
	 * Set fileinfo mode
	 * @param string $val
	 * @return e_file
	 */
	public function setFileInfo($val='default')
	{
		$this->finfo = $val;
		return $this;
	}

	/**
	 * Read files from given path
	 *
	 * @param string $path
	 * @param string $fmask [optional]
	 * @param string $omit [optional]
	 * @param integer $recurse_level [optional]
	 * @return array of file names/paths
	 */
	function get_files($path, $fmask = '', $omit='standard', $recurse_level = 0)
	{
		$ret = array();
		$invert = FALSE;
		if (substr($fmask,0,1) == '~')
		{
			$invert = TRUE;						// Invert selection - exclude files which match selection
			$fmask = substr($fmask,1);
		}

		if($recurse_level < 0)
		{
			return $ret;
		}
		if(substr($path,-1) == '/')
		{
			$path = substr($path, 0, -1);
		}

		if(!is_dir($path) || !$handle = opendir($path))
		{
			return $ret;
		}
		if (($omit == 'standard') || ($omit == ''))
		{
			$omit = array();
		}
		else
		{
			if (!is_array($omit))
			{
				$omit = array($omit);
			}
		}
		while (false !== ($file = readdir($handle)))
		{
			if(is_dir($path.'/'.$file))
			{	// Its a directory - recurse into it unless a filtered directory or required depth achieved
				// Must always check for '.' and '..'
				if(($file != '.') && ($file != '..') && !in_array($file, $this->dirFilter) && !in_array($file, $omit) && ($recurse_level > 0))
				{
					$xx = $this->get_files($path.'/'.$file, $fmask, $omit, $recurse_level - 1);
					$ret = array_merge($ret,$xx);
				}
			}
			else
			{
				// Now check against standard reject list and caller-specified list
				if (($fmask == '') || ($invert != preg_match("#".$fmask."#", $file)))
				{	// File passes caller's filter here
					$rejected = FALSE;

					// Check against the generic file reject filter
					foreach($this->fileFilter as $rmask)
					{
						if(preg_match("#".$rmask."#", $file))
						{
							$rejected = TRUE;
							break;			// continue 2 may well work
						}
					}
					if($rejected == FALSE)
					{
						switch($this->mode)
						{
							case 'fname':
								$ret[] = $file;
							break;

							case 'path':
								$ret[] = $path."/";
							break;

							case 'full':
								$ret[] = $path."/".$file;
							break;

							case 'all':
							default:
								if('default' != $this->finfo)
								{
									$finfo = $this->get_file_info($path."/".$file, ('file' != $this->finfo)); // -> 'all' & 'image'
								}
								$finfo['path'] = $path."/";  // important: leave this slash here and update other file instead.
								$finfo['fname'] = $file;

								$ret[] = $finfo;
							break;
						}
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * Collect file information
	 * @param string $path_to_file
	 * @param boolean $imgcheck
	 * @return array
	 */
	function get_file_info($path_to_file, $imgcheck = true)
	{
		$finfo = array();

		if($imgcheck && ($tmp = getimagesize($path_to_file)))
		{
			$finfo['img-width'] = $tmp[0];
			$finfo['img-height'] = $tmp[1];
			$finfo['mime'] = $tmp['mime'];
		}

		$tmp = stat($path_to_file);
		if($tmp)
		{
			$finfo['fsize'] = $tmp['size'];
			$finfo['modified'] = $tmp['mtime'];
		}

		// associative array elements: dirname, basename, extension, filename
		$finfo['pathinfo'] = pathinfo($path_to_file);

		return $finfo;
	}

	/**
	 * Get a list of directories matching $fmask, omitting any in the $omit array - same calling syntax as get_files()
	 * N.B. - no recursion - just looks in the specified directory.
	 * @param string $path
	 * @param strig $fmask
	 * @param string $omit
	 * @return array
	 */
	function get_dirs($path, $fmask = '', $omit='standard')
	{
		$ret = array();
		if(substr($path,-1) == '/')
		{
			$path = substr($path, 0, -1);
		}

		if(!$handle = opendir($path))
		{
			return $ret;
		}

		if($omit == 'standard')
		{
			$omit = array();
		}
		else
		{
			if (!is_array($omit))
			{
				$omit = array($omit);
			}
		}
		while (false !== ($file = readdir($handle)))
		{
			if(is_dir($path.'/'.$file) && ($file != '.') && ($file != '..') && !in_array($file, $this->dirFilter) && !in_array($file, $omit) && ($fmask == '' || preg_match("#".$fmask."#", $file)))
			{
				$ret[] = $file;
			}
		}
		return $ret;
	}

	/**
	 * Delete a complete directory tree
	 * @param string $dir
	 * @return boolean success
	 */
	function rmtree($dir)
	{
		if (substr($dir, -1) != '/')
		{
			$dir .= '/';
		}
		if ($handle = opendir($dir))
		{
			while ($obj = readdir($handle))
			{
				if ($obj != '.' && $obj != '..')
				{
					if (is_dir($dir.$obj))
					{
						if (!$this->rmtree($dir.$obj))
						{
							return false;
						}
					}
					elseif (is_file($dir.$obj))
					{
						if (!unlink($dir.$obj))
						{
							return false;
						}
					}
				}
			}

			closedir($handle);

			if (!@rmdir($dir))
			{
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 *	Parse a file size string (e.g. 16M) and compute the simple numeric value.
	 *
	 *	@param string $source - input string which may include 'multiplier' characters such as 'M' or 'G'. Converted to 'decoded value'
	 *	@param int $compare - a 'compare' value
	 *	@param string $action - values (gt|lt)
	 *
	 *	@return int file size value.
	 *		If the decoded value evaluates to zero, returns the value of $compare
	 *		If $action == 'gt', return the larger of the decoded value and $compare
	 *		If $action == 'lt', return the smaller of the decoded value and $compare
	 */
	function file_size_decode($source, $compare = 0, $action = '')
	{
		$source = trim($source);
		if (strtolower(substr($source, -1, 1)) == 'b')
			$source = substr($source, 0, -1); // Trim a trailing byte indicator
		$mult = 1;
		if (strlen($source) && (strtoupper(substr($source, -1, 1)) == 'B'))
			$source = substr($source, 0, -1);
		if (!$source || is_numeric($source))
		{
			$val = $source;
		}
		else
		{
			$val = substr($source, 0, -1);
			switch (substr($source, -1, 1))
			{
				case 'T':
					$val = $val * 1024;
				case 'G':
					$val = $val * 1024;
				case 'M':
					$val = $val * 1024;
				case 'K':
				case 'k':
					$val = $val * 1024;
				break;
			}
		}
		if ($val == 0)
			return $compare;
		switch ($action)
		{
			case 'lt':
				return min($val, $compare);
			case 'gt':
				return max($val, $compare);
			default:
				return $val;
		}
		return 0;
	}

	/**
	 * Parse bytes to human readable format
	 * Former Download page function
	 * @param mixed $size file size in bytes or file path if $retrieve is true
	 * @param boolean $retrieve defines the type of $size
	 *
	 * @return string formatted size
	 */
	function file_size_encode($size, $retrieve = false)
	{
		if($retrieve)
		{
			$size = filesize($size);
		}
		$kb = 1024;
		$mb = 1024 * $kb;
		$gb = 1024 * $mb;
		$tb = 1024 * $gb;
		if(!$size)
		{
			return '0&nbsp;'.CORE_LAN_B;
		}
		if ($size < $kb)
		{
			return $size."&nbsp;".CORE_LAN_B;
		}
		else if($size < $mb)
		{
			return round($size/$kb, 2)."&nbsp;".CORE_LAN_KB;
		}
		else if($size < $gb)
		{
			return round($size/$mb, 2)."&nbsp;".CORE_LAN_MB;
		}
		else if($size < $tb)
		{
			return round($size/$gb, 2)."&nbsp;".CORE_LAN_GB;
		}
		else
		{
			return round($size/$tb, 2)."&nbsp;".CORE_LAN_TB;
		}
	}

}
