<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/file_class.php,v $
|     $Revision: 1.4 $
|     $Date: 2009-11-07 11:20:28 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
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
	var	$dirFilter;				// Array of directory names to ignore (in addition to any set by caller)
	var $fileFilter;			// Array of file names to ignore (in addition to any set by caller)
	var $mode = 'default';

	// Constructor
	function e_file()
	{
		$this->setDefaults();
	}


	function setDefaults()
	{
		$this->dirFilter = array('/', 'CVS', '.svn');										// Default directory filter (exact matches only)
		$this->fileFilter = array('^thumbs\.db$','^Thumbs\.db$','.*\._$','^\.htaccess$','^index\.html$','^null\.txt$','\.bak$','^.tmp');		// Default file filter (regex format)
	}
	
	/**
	 * 
	 * @param object $path
	 * @param object $fmask [optional]
	 * @param object $omit [optional]
	 * @param object $recurse_level [optional]
	 * @return 
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

		if(!$handle = opendir($path))
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
						switch($this->mode) //TODO remove this check from the loop. 
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
						
							default:
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



	// Get a list of directories matching $fmask, omitting any in the $omit array - same calling syntax as get_files()
	// N.B. - no recursion - just looks in the specified directory.
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



	// Delete a complete directory tree
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

}
?>
