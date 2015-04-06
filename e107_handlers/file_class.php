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
if(defined('SAFE_MODE') && SAFE_MODE === false)
{
	@set_time_limit(10 * 60);	// throws error in safe-mode. 
}

//session_write_close();
@e107_ini_set("max_execution_time", 10 * 60);
//while (@ob_end_clean()); // kill all output buffering else it eats server resources
//ob_implicit_flush(TRUE);


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
	
	public $filesRejected = array();

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
	
	
	
	private $authKey = false; // Used when retrieving files from e107.org. 

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
		$this->fileFilter = array('^thumbs\.db$','^Thumbs\.db$','.*\._$','^\.htaccess$','^\.cvsignore$','^\.ftpquota$','^index\.html$','^null\.txt$','\.bak$','^.tmp'); // Default file filter (regex format)
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
	
	
	public function setFileFilter($filter)
	{
		$this->fileFilter = $filter;	
		return $this;	
	}

	/**
	 * Clean and rename file name
	 * @param $f array as returned by get_files();
	 * @param $rename boolean  - set to true to rename file.
	 * @return array
	 */
	public function cleanFileName($f,$rename=false)
	{
		$fullpath = $f['path'].$f['fname'];		
		$newfile = preg_replace("/[^a-z0-9-\._]/", "-", strtolower($f['fname']));	
		$newpath = $f['path'].$newfile;
		
		if($rename == true)
		{
						
			if(!rename($fullpath,$newpath))
			{
				$f['error'] = "Couldn't rename $fullpath to $newpath";
			}	
		}
		
		$f['fname'] = $newfile;
			
		return $f;	
	}
	
	function setMode($mode)
	{
		$this->mode= $mode; 
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
			$omit = $this->fileFilter;
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
					foreach($omit as $rmask)
					{
						if(preg_match("#".$rmask."#", $file))
						{
							$rejected = TRUE;
							$this->filesRejected[] = $file;
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
								else 
								{
									$finfo['path'] = $path.'/';  // important: leave this slash here and update other file instead.
									$finfo['fname'] = $file;
								}
							//	$finfo['path'] = $path.'/';  // important: leave this slash here and update other file instead.
							//	$finfo['fname'] = $file;

								$ret[] = $finfo;
							break;
						}
					}
				}
			}
		}
		return $ret;
	}


	function getFileExtension($mimeType)
	{
		$extensions = array(
			'image/jpeg'=>'.jpg',
			'image/png'	=> '.png',
			'image/gif'	=> '.gif'
		);	
		
		if(isset($extensions[$mimeType]))
		{
			return $extensions[$mimeType];		
		}
		
	}



	/**
	 * Collect file information
	 * @param string $path_to_file
	 * @param boolean $imgcheck
	 * @param boolean $auto_fix_ext
	 * @return array
	 */
	function get_file_info($path_to_file, $imgcheck = true, $auto_fix_ext = true)
	{
		$finfo = array();
		
		if(filesize($path_to_file) < 2) // Don't try and read 0 byte files. 
		{
			return false; 	
		}
		
		$finfo['pathinfo'] = pathinfo($path_to_file);
		
		if(class_exists('finfo')) // Best Mime detection method. 
		{
			$fin = new finfo(FILEINFO_MIME);
			list($mime, $other) = explode(";", $fin->file($path_to_file));
			
			if(!empty($mime))
			{
				$finfo['mime'] = $mime;	
			}
			
		}

        if($auto_fix_ext)
        {
            // Auto-Fix Files without an extensions using known mime-type.
            if(empty($finfo['pathinfo']['extension']) && !is_dir($path_to_file) && !empty($finfo['mime']))
            {
                if($ext = $this->getFileExtension($finfo['mime']))
                {
                    $finfo['pathinfo']['extension'] = $ext;


                    $newFile = $path_to_file . $ext;
                    if(!file_exists($newFile))
                    {
                        if(rename($path_to_file,$newFile)===true)
                        {
                            $finfo['pathinfo'] = pathinfo($newFile);
                            $path_to_file = $newFile;
                        }
                    }
                }
            }
        }


		if($imgcheck && ($tmp = getimagesize($path_to_file)))
		{
			$finfo['img-width'] = $tmp[0];
			$finfo['img-height'] = $tmp[1];
			
			if(empty($finfo['mime']))
			{
				$finfo['mime'] = $tmp['mime'];	
			}
			
		}
		
		$tmp = stat($path_to_file);

		if($tmp)
		{
			
			$finfo['fsize'] = $tmp['size'];
			$finfo['modified'] = $tmp['mtime'];
		}

		// associative array elements: dirname, basename, extension, filename
		

		$finfo['fullpath'] 	= $path_to_file;
		$finfo['fname'] 	= basename($path_to_file);
		$finfo['path'] 		= dirname($path_to_file).'/';

		if(empty($finfo['mime'])) // last resort. 
		{
			$finfo['mime'] = 'application/'.$finfo['pathinfo']['extension'];
		}
	
		
		
		return $finfo;
	}


	/**
	 *	 Grab a remote file and save it in the /temp directory. requires CURL
	 *	@param $remote_url
	 *	@param $local_file
	 *	@param $type  media, temp, or import
	 *	@return boolean TRUE on success, FALSE on failure (which includes absence of CURL functions)
	 */
	function getRemoteFile($remote_url, $local_file, $type='temp')
	{
		// check for cURL
		if (!function_exists('curl_init')) 
		{
			if(E107_DEBUG_LEVEL > 0)
			{ 
				e107::getAdminLog()->addDebug('getRemoteFile() requires cURL to be installed in file_class.php');
			}
			return FALSE;			// May not be installed
		}

		$path = ($type == 'media') ? e_MEDIA : e_TEMP; 
		
		if($type == 'import')
		{
			$path = e_IMPORT;
		}
		
        $fp = fopen($path.$local_file, 'w'); // media-directory is the root. 
       
        $cp = curl_init($remote_url);
		curl_setopt($cp, CURLOPT_FILE, $fp);
		curl_setopt($cp, CURLOPT_REFERER, e_REQUEST_HTTP);
		curl_setopt($cp, CURLOPT_HEADER, 0);
		curl_setopt($cp, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
		curl_setopt($cp, CURLOPT_COOKIEFILE, e_SYSTEM.'cookies.txt');

        $buffer = curl_exec($cp);
       
        curl_close($cp);
        fclose($fp);
       
        return ($buffer) ? true : false;
    }
	
	/**
	 * FIXME add POST support
	 * Get Remote contents
	 * $options array:
	 * - 'timeout' (integer): timeout in seconds
	 * - 'post' (array|urlencoded string): POST data
	 * - 'header' (array) headers, example: array('Content-Type: text/xml', 'X-Custom-Header: SomeValue');
	 * @param string $address
	 * @param array $options [optional] 
	 * @return string
	 */
	function getRemoteContent($address, $options = array())
	{
		// Could do something like: if ($timeout <= 0) $timeout = $pref['get_remote_timeout'];  here
		$postData = varset($options['post'], null);
		$timeout = (integer) vartrue($options['timeout'], 10);
		$timeout = min($timeout, 120);
		$timeout = max($timeout, 3);
		$fileContents = '';
		$this->error = '';
		$this->errornum = null;
		
		$mes = e107::getMessage();
			
		$address = str_replace(array("\r", "\n", "\t"), '', $address); // May be paranoia, but streaky thought it might be a good idea	
		// ... and there shouldn't be unprintable characters in the URL anyway		
		$requireCurl = false;
		
		if(vartrue($options['decode'], false)) $address = urldecode($address);

		// Keep this in first position. 
		if (function_exists("curl_init")) // Preferred. 
		{
			$cu = curl_init();
			curl_setopt($cu, CURLOPT_URL, $address);
			curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cu, CURLOPT_HEADER, 0);
			curl_setopt($cu, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($cu, CURLOPT_SSL_VERIFYPEER, FALSE); 
			curl_setopt($cu, CURLOPT_REFERER, e_REQUEST_HTTP);
			curl_setopt($cu, CURLOPT_FOLLOWLOCATION, 0); 
			curl_setopt($cu, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
			curl_setopt($cu, CURLOPT_COOKIEFILE, e_SYSTEM.'cookies.txt');
			curl_setopt($cu, CURLOPT_COOKIEJAR, e_SYSTEM.'cookies.txt');
			if($postData !== null)
			{
				curl_setopt($cu, CURLOPT_POST, true);
				// if array -> will encode the data as multipart/form-data, if URL-encoded string - application/x-www-form-urlencoded
				curl_setopt($cu, CURLOPT_POSTFIELDS, $postData);
				$requireCurl = true;
			}
			if(isset($options['header']) && is_array($options['header']))
			{
				curl_setopt($cu, CURLOPT_HTTPHEADER, $options['header']);
				$requireCurl = true;
			}
	
			if(!file_exists(e_SYSTEM.'cookies.txt'))
			{
				file_put_contents(e_SYSTEM.'cookies.txt','');	
			}
			
			$fileContents = curl_exec($cu);
			if (curl_error($cu))
			{
				$this->errornum = curl_errno($cu);
				$this->error = "Curl error: ".$this->errornum.", ".curl_error($cu);
				return FALSE;
			}
			curl_close($cu);
			return $fileContents;
		}
		
		// CURL is required, abort...
		if($requireCurl == true) return false;

		if (function_exists('file_get_contents') && ini_get('allow_url_fopen'))
		{
			$old_timeout = e107_ini_set('default_socket_timeout', $timeout);
			$data = file_get_contents($address);

			//		  $data = file_get_contents(htmlspecialchars($address));	// buggy - sometimes fails.
			if ($old_timeout !== FALSE)
			{
				e107_ini_set('default_socket_timeout', $old_timeout);
			}
			if ($data !== FALSE)
			{
				$fileContents = $data;
				return $data;
			}
			$this->error = "File_get_contents(XML) error";		// Fill in more info later
			return FALSE;
		}

		if (ini_get("allow_url_fopen"))
		{
			$old_timeout = e107_ini_set('default_socket_timeout', $timeout);
			$remote = @fopen($address, "r");
			if (!$remote)
			{
				$this->error = "fopen: Unable to open remote XML file: ".$address;
				return FALSE;
			}
		}
		else
		{
			$old_timeout = $timeout;
			$tmp = parse_url($address);
			if (!$remote = fsockopen($tmp['host'], 80, $errno, $errstr, $timeout))
			{
				$this->error = "Sockets: Unable to open remote XML file: ".$address;
				return FALSE;
			}
			else
			{
				socket_set_timeout($remote, $timeout);
				fputs($remote, "GET ".urlencode($address)." HTTP/1.0\r\n\r\n");
			}
		}
		$fileContents = "";
		while (!feof($remote))
		{
			$fileContents .= fgets($remote, 4096);
		}
		fclose($remote);
		if ($old_timeout != $timeout)
		{
			if ($old_timeout !== FALSE)
			{
				e107_ini_set('default_socket_timeout', $old_timeout);
			}
		}
		return $fileContents;
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
	
	
	/** Recursive Chmod function. 
	 * @param string path to folder
	 * @param string perms for files
	 * @param string perms for directories
	 * @example chmod_R('mydir', 0644, 0755); 
	 */
	function chmod($path, $filemode=0644, $dirmode=0755) 
	{
    	if (is_dir($path) ) 
    	{
	        if (!chmod($path, $dirmode)) 
	        {
	            $dirmode_str=decoct($dirmode);
	            print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
	            print "  `-> the directory '$path' will be skipped from recursive chmod\n";
	            return;
	        }
	        $dh = opendir($path);
	        while (($file = readdir($dh)) !== false) 
	        {
	            if($file != '.' && $file != '..')   // skip self and parent pointing directories
	            { 
	                $fullpath = $path.'/'.$file;
	                $this->chmod($fullpath, $filemode,$dirmode);
	            }
	        }
	        closedir($dh);
	    }
		else 
		{
			if (is_link($path)) 
			{
	            print "link '$path' is skipped\n";
	            return;
	        }
			
	        if (!chmod($path, $filemode)) 
	        {
	            $filemode_str=decoct($filemode);
	            print "Failed applying filemode '$filemode_str' on file '$path'\n";
	            return;
	        }
	    }
	} 
	
	
	
	

	/**
	 * File retrieval function. by Cam.
	 * @param $file actual path or {e_xxxx} path to file. 
	 * 
	 */
	function send($file) 
	{
		global $e107;
		
		$pref 					= e107::getPref();
		$tp 					= e107::getParser();
		
		$DOWNLOADS_DIR 			= e107::getFolder('DOWNLOADS');		
		$DOWNLOADS_DIRECTORY 	= ($DOWNLOADS_DIR[0] == DIRECTORY_SEPARATOR) ? $DOWNLOADS_DIR : e_BASE.$DOWNLOADS_DIR; // support for full path eg. /home/account/folder. 
		$FILES_DIRECTORY 		= e_BASE.e107::getFolder('FILES');
		$MEDIA_DIRECTORY		= realpath(e_MEDIA); //  could be image, file or other type. 
		$SYSTEM_DIRECTORY		= realpath(e_SYSTEM); // downloading of logs etc. via browser if required. (Admin-only)
		
		$file = $tp->replaceConstants($file);
		
			
		@set_time_limit(10 * 60);
		@session_write_close();
		@e107_ini_set("max_execution_time", 10 * 60);
		while (@ob_end_clean()); // kill all output buffering else it eats server resources
		@ob_implicit_flush(TRUE);
		
		
		$filename = $file;
		$file = basename($file);
		$path = realpath($filename);
		$path_downloads = realpath($DOWNLOADS_DIRECTORY);
		$path_public = realpath($FILES_DIRECTORY."public/");
		
		if(strstr($path, $SYSTEM_DIRECTORY) && !ADMIN)
		{
			header("location: {$e107->base_path}");
			exit();
		}
		
		if(!strstr($path, $path_downloads) && !strstr($path,$path_public) && !strstr($path, $MEDIA_DIRECTORY) && !strstr($path, $SYSTEM_DIRECTORY)) 
		{
	        if(E107_DEBUG_LEVEL > 0 && ADMIN)
			{
				echo "Failed to Download <b>".$file."</b><br />";
				echo "The file-path <b>".$path."<b> didn't match with either of 
				<ul><li><b>{$path_downloads}</b></li>
				<li><b>{$path_public}</b></li></ul><br />";
				echo "Downloads Path: ".$path_downloads. " (".$DOWNLOADS_DIRECTORY.")";
				exit();
	        }
			else
			{
				header("location: {$e107->base_path}");
				exit();
			}
		} 
		else 
		{
			if (is_file($filename) && is_readable($filename) && connection_status() == 0) 
			{
				$seek = 0;
				if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
				{
					$file = preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1);
				}
				if (isset($_SERVER['HTTP_RANGE']))
				{
					$seek = intval(substr($_SERVER['HTTP_RANGE'] , strlen('bytes=')));
				}
				$bufsize = 2048;
				ignore_user_abort(true);
				$data_len = filesize($filename);
				if ($seek > ($data_len - 1)) { $seek = 0; }
				if ($filename == null) { $filename = basename($this->data); }
				$res =& fopen($filename, 'rb');
				if ($seek)
				{
					fseek($res , $seek);
				}
				$data_len -= $seek;
				header("Expires: 0");
				header("Cache-Control: max-age=30" );
				header("Content-Type: application/force-download");
				header("Content-Disposition: attachment; filename=\"{$file}\"");
				header("Content-Length: {$data_len}");
				header("Pragma: public");
				if ($seek)
				{
					header("Accept-Ranges: bytes");
					header("HTTP/1.0 206 Partial Content");
					header("status: 206 Partial Content");
					header("Content-Range: bytes {$seek}-".($data_len - 1)."/{$data_len}");
				}
				while (!connection_aborted() && $data_len > 0)
				{
					echo fread($res , $bufsize);
					$data_len -= $bufsize;
				}
				fclose($res);
			} 
			else 
			{
	            if(E107_DEBUG_LEVEL > 0 && ADMIN)
				{
	              	echo "file failed =".$file."<br />";
					echo "path =".$path."<br />";
	                exit();
				}
				else
				{
				  	header("location: ".e_BASE."index.php");
					exit();
				}
			}
		}
	}	


		
		
	/**
	 * Runs through the zip archive array and finds the root directory. 
	 */
	public function getRootFolder($unarc)
	{
		foreach($unarc as $d)
		{
			$target = trim($d['stored_filename'],'/');
		
			$test = basename(str_replace(e_TEMP,"", $d['stored_filename']),'/');
			
			if($d['folder'] == 1 && $target == $test)  // 
			{
			//	$text .= "\\n test = ".$test;
				$text = "getRootDirectory: ".$d['stored_filename'];
				$text .= "<br />test=".$test; 
				$text .= "<br />target=".$target;
				
				if(E107_DEBUG_LEVEL > 0)
				{
					e107::getMessage()->addDebug($text); 
				// 	echo "<script>alert('".$text."')</script>";
				}
				return $target; 
		
			}			
		}
		
		return false;
						
	}		
		
	
	/**
	 * Zip up folders and files 
	 * @param array $filePaths
	 * @param string $newFile

	 */	
	public function zip($filePaths=null, $newFile='', $options=array())
	{
		if(empty($newFile))
		{
			$newFile = e_BACKUP.eHelper::title2sef(SITENAME)."_".date("Y-m-d-H-i-s").".zip";	
		}		
		
		if(is_null($filePaths))
		{
			return "No file-paths set!";	
		}
			
		require_once(e_HANDLER.'pclzip.lib.php');	
		$archive = new PclZip($newFile);

		$removePath = (!empty($options['remove_path'])) ? $options['remove_path'] : e_BASE;

		if ($archive->create($filePaths, PCLZIP_OPT_REMOVE_PATH, $removePath) == 0)
		{		
			$error = $archive->errorInfo(true);
			e107::getAdminLog()->addError($error)->save('FILE',E_LOG_NOTICE);
			return false;
		}
		else
		{
			return $newFile;		
		}
	}		

	
	/**
	 * Recursive Directory removal . 
	 */
	public function removeDir($dir) 
	{ 
	    if (is_dir($dir)) 
	    { 
	        $objects = scandir($dir); 
	        foreach ($objects as $object) 
	        { 
	            if ($object != "." && $object != "..") 
	            { 
	                if (filetype($dir."/".$object) == "dir")
					{
						 $this->removeDir($dir."/".$object);
					}
					else
					{
						 @unlink($dir."/".$object); 
					}
	            } 
	        }
			
	        reset($objects); 
	        @rmdir($dir); 
	    }
	}

	
	/**
	 * File-class wrapper for upload handler. (Preferred for v2.x) 
	 * Process files uploaded in a form post. ie. $_FILES. 
	 */
	public function getUploaded($uploaddir, $fileinfo = false, $options = null)
	{
		require_once(e_HANDLER."upload_handler.php");
		return process_uploaded_files($uploaddir, $fileinfo, $options);	

	}
	
	
	/**
	 * Unzip Plugin or Theme zip file and move to plugin or theme folder. 
	 * @param string $localfile - filename located in e_TEMP
	 * @param string $type - addon type, either 'plugin' or 'theme', (possibly 'language' in future). 
	 * @return string unzipped folder name on success or false. 
	 */
	public function unzipArchive($localfile, $type)
	{
		$mes = e107::getMessage();
		
		chmod(e_TEMP.$localfile, 0755);
		require_once(e_HANDLER."pclzip.lib.php");
		
		$archive 	= new PclZip(e_TEMP.$localfile);
		$unarc 		= ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_TEMP, PCLZIP_OPT_SET_CHMOD, 0755)); // Store in TEMP first. 
		$dir 		= $this->getRootFolder($unarc);	
		$destpath 	= ($type == 'theme') ? e_THEME : e_PLUGIN;
		$typeDiz 	= ucfirst($type);
		
		@copy(e_TEMP.$localfile, e_BACKUP.$dir.".zip"); // Make a Backup in the system folder. 
		
		if($dir && is_dir($destpath.$dir))
		{
			$mes->addError("(".ucfirst($type).") Already Downloaded - ".basename($destpath).'/'.$dir); 
			 
			if(file_exists(e_TEMP.$localfile))
			{	
				@unlink(e_TEMP.$localfile);
			}
			
			$this->removeDir(e_TEMP.$dir);
			return false;
		}
	
		if($dir == '')
		{
			$mes->addError("Couldn't detect the root folder in the zip."); //  flush();
			@unlink(e_TEMP.$localfile);
			return false;		
		}
	
		if(is_dir(e_TEMP.$dir)) 
		{
			if(!rename(e_TEMP.$dir,$destpath.$dir))
			{
				$mes->addError("Couldn't Move ".e_TEMP.$dir." to ".$destpath.$dir." Folder"); //  flush(); usleep(50000);
				@unlink(e_TEMP.$localfile);
				return false;
			}	
			

			
		//	$dir 		= basename($unarc[0]['filename']);
		//	$plugPath	= preg_replace("/[^a-z0-9-\._]/", "-", strtolower($dir));	
			//$status = "Done"; // ADMIN_TRUE_ICON;		
			@unlink(e_TEMP.$localfile);	
			
			return $dir;
		}
		
		return false; 
	}
	
	
	/**
	 *	Get an array of permitted filetypes according to a set hierarchy.
	 *	If a specific file name given, that's used. Otherwise the default hierarchy is used
	 *
	 *	@param string $file_mask - comma-separated list of allowed file types
	 *	@param string $filename - optional override file name - defaults ignored
	 *
	 *	@return array of filetypes
	 */
	function getFiletypeLimits($file_mask = false, $filename = '') // Wrapper only for now. 
	{
		require_once(e_HANDLER."upload_handler.php");
		$limits =  get_filetypes($file_mask, $filename);
		ksort($limits);
		return $limits; 
	}

}
