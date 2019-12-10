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

/**
 * Flag used by prepareDirectory() method -- create directory if not present.
 */
define('FILE_CREATE_DIRECTORY', 1);

/**
 * Flag used by prepareDirectory() method -- file permissions may be changed.
 */
define('FILE_MODIFY_PERMISSIONS', 2);


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
	
	
	
//	private $authKey = false; // Used when retrieving files from e107.org.


	private $error = null;

	private $errornum = null;

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
			

	public function getErrorMessage()
	{
		return $this->error;
	}


	public function getErrorCode()
	{
		return $this->errornum;
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


	/**
	 * Return an extension for a specific mime-type.
	 * @param $mimeType
	 * @return mixed|null
	 */
	function getFileExtension($mimeType)
	{
		$extensions = array(
		  'application/ecmascript'          => '.es',
		  'application/epub+zip'            => '.epub',
		  'application/java-archive'        => '.jar',
		  'application/javascript'          => '.js',
		  'application/json'                => '.json',
		  'application/msword'              => '.doc',
		  'application/octet-stream'        => '.bin',
		  'application/ogg'                 => '.ogx',
		  'application/pdf'                 => '.pdf',
		  'application/rtf'                 => '.rtf',
		  'application/typescript'          => '.ts',
		  'application/vnd.amazon.ebook'    => '.azw',
		  'application/vnd.apple.installer+xml' => '.mpkg',
		  'application/vnd.mozilla.xul+xml' => '.xul',
		  'application/vnd.ms-excel'        => '.xls',
		  'application/vnd.ms-fontobject'   => '.eot',
		  'application/vnd.ms-powerpoint'   => '.ppt',
		  'application/vnd.oasis.opendocument.presentation' => '.odp',
		  'application/vnd.oasis.opendocument.spreadsheet' => '.ods',
		  'application/vnd.oasis.opendocument.text' => '.odt',
		  'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',
		  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '.xlsx',
		  'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '.docx',
		  'application/vnd.visio'           => '.vsd',
		  'application/x-7z-compressed'     => '.7z',
		  'application/x-abiword'           => '.abw',
		  'application/x-bzip'              => '.bz',
		  'application/x-bzip2'             => '.bz2',
		  'application/x-csh'               => '.csh',
		  'application/x-rar-compressed'    => '.rar',
		  'application/x-sh'                => '.sh',
		  'application/x-shockwave-flash'   => '.swf',
		  'application/x-tar'               => '.tar',
		  'application/xhtml+xml'           => '.xhtml',
		  'application/xml'                 => '.xml',
		  'application/zip'                 => '.zip',
		  'audio/aac'                       => '.aac',
		  'audio/midi'                      => '.midi',
		  'audio/mpeg'                      => '.mp3',
		  'audio/ogg'                       => '.oga',
		  'audio/wav'                       => '.wav',
		  'audio/webm'                      => '.weba',
		  'font/otf'                        => '.otf',
		  'font/ttf'                        => '.ttf',
		  'font/woff'                       => '.woff',
		  'font/woff2'                      => '.woff2',
		  'image/bmp'                       => '.bmp',
		  'image/gif'                       => '.gif',
		  'image/jpeg'                      => '.jpg',
		  'image/png'                       => '.png',
		  'image/svg+xml'                   => '.svg',
		  'image/tiff'                      => '.tiff',
		  'image/webp'                      => '.webp',
		  'image/x-icon'                    => '.ico',
		  'text/calendar'                   => '.ics',
		  'text/css'                        => '.css',
		  'text/csv'                        => '.csv',
		  'text/html'                       => '.html',
		  'text/plain'                      => '.txt',
		  'video/mp4'                       => '.mp4',
		  'video/mpeg'                      => '.mpeg',
		  'video/ogg'                       => '.ogv',
		  'video/webm'                      => '.webm',
		  'video/x-msvideo'                 => '.avi',
		);

		if(isset($extensions[$mimeType]))
		{
			return $extensions[$mimeType];		
		}

		return null;
	}



	/**
	 * Collect file information
	 * @param string $path_to_file
	 * @param boolean $imgcheck
	 * @param boolean $auto_fix_ext
	 * @return array|bool
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

			unset($other);
			
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
			switch($finfo['pathinfo']['extension'])
			{
				case "svg":
					$finfo['mime'] = 'image/svg+xml';
					break;

				case "mp3":
					$finfo['mime'] = 'audio/mpeg';
					break;

				case "ogg":
					$finfo['mime'] = 'audio/ogg';
					break;

				case "mp4":
					$finfo['mime'] = 'video/mp4';
					break;

				case "3gp":
					$finfo['mime'] = 'video/3gpp';
					break;

				default:
					$finfo['mime'] = 'application/'.$finfo['pathinfo']['extension'];
			}

		}
	
		
		
		return $finfo;
	}


	/**
	 *	 Grab a remote file and save it in the /temp directory. requires CURL
	 *	@param string $remote_url
	 *	@param $local_file string filename to save as
	 *	@param string $type  media, temp, or import
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

        $cp = $this->initCurl($remote_url);
		curl_setopt($cp, CURLOPT_FILE, $fp);
		curl_setopt($cp, CURLOPT_TIMEOUT, 40);//FIXME Make Pref - avoids get file timeout on slow connections

        $buffer = curl_exec($cp);
		//FIXME addDebug curl_error output - here see #1936
       
        curl_close($cp);
        fclose($fp);
       
        return ($buffer) ? true : false;
    }

	/**
	 * @param string $address
	 * @param array|null $options
	 */
	function initCurl($address, $options =null)
	{
		$cu = curl_init();

		$timeout = (integer) vartrue($options['timeout'], 10);
		$timeout = min($timeout, 120);
		$timeout = max($timeout, 3);

		$urlData = parse_url($address);
		$referer = $urlData['scheme']."://".$urlData['host'];

		if(empty($referer))
		{
			$referer = e_REQUEST_HTTP;
		}

		curl_setopt($cu, CURLOPT_URL, $address);
		curl_setopt($cu, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cu, CURLOPT_HEADER, 0);
		curl_setopt($cu, CURLOPT_REFERER, $referer);
		curl_setopt($cu, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($cu, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($cu, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($cu, CURLOPT_COOKIEFILE, e_SYSTEM.'cookies.txt');
		curl_setopt($cu, CURLOPT_COOKIEJAR, e_SYSTEM.'cookies.txt');

		if(defined('e_CURL_PROXY'))
		{
			curl_setopt($cu, CURLOPT_PROXY, e_CURL_PROXY);     // PROXY details with port
		}

		if(defined('e_CURL_PROXYUSERPWD'))
		{
			curl_setopt($cu, CURLOPT_PROXYUSERPWD, e_CURL_PROXYUSERPWD);   // Use if proxy have username and password
		}

		if(defined('e_CURL_PROXYTYPE'))
		{
			curl_setopt($cu, CURLOPT_PROXYTYPE, e_CURL_PROXYTYPE); // If expected to cal
		}

		if(!empty($options['post']))
		{
			curl_setopt($cu, CURLOPT_POST, true);
				// if array -> will encode the data as multipart/form-data, if URL-encoded string - application/x-www-form-urlencoded
			curl_setopt($cu, CURLOPT_POSTFIELDS, $options['post']);
		}

		if(isset($options['header']) && is_array($options['header']))
		{
			curl_setopt($cu, CURLOPT_HTTPHEADER, $options['header']);
		}

		if(!file_exists(e_SYSTEM.'cookies.txt'))
		{
			file_put_contents(e_SYSTEM.'cookies.txt','');
		}

		return $cu;

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

	//	$fileContents = '';
		$this->error = '';
		$this->setErrorNum(null);

	//	$mes = e107::getMessage();
			
		$address = str_replace(array("\r", "\n", "\t"), '', $address); // May be paranoia, but streaky thought it might be a good idea	
		// ... and there shouldn't be unprintable characters in the URL anyway		
		$requireCurl = false;
		
		if(vartrue($options['decode'], false)) $address = urldecode($address);

		// Keep this in first position.
		if (function_exists("curl_init")) // Preferred. 
		{

			$cu = $this->initCurl($address, $options);

			$fileContents = curl_exec($cu);
			if (curl_error($cu))
			{
				$errorCode = curl_errno($cu);
				$this->setErrorNum($errorCode);
				$this->error = "Curl error: ".$errorCode.", ".curl_error($cu);
				return FALSE;
			}
			curl_close($cu);
			return $fileContents;
		}
		
		// CURL is required, abort...
		if($requireCurl == true) return false;

		$timeout = 5;

		if (function_exists('file_get_contents') && ini_get('allow_url_fopen'))
		{
			$old_timeout = e107_ini_set('default_socket_timeout', $timeout);

			$context = array(
				'ssl' => array(
					'verify_peer'      => false,
					'verify_peer_name' => false,
				),
			);

			$data = file_get_contents($address, false, stream_context_create($context));

			//		  $data = file_get_contents(htmlspecialchars($address));	// buggy - sometimes fails.
			if ($old_timeout !== FALSE)
			{
				e107_ini_set('default_socket_timeout', $old_timeout);
			}
			if ($data !== FALSE)
			{
			//	$fileContents = $data;
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
	 * @param string $fmask
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
	 *	@return int file size value in bytes.
	 *		If the decoded value evaluates to zero, returns the value of $compare
	 *		If $action == 'gt', return the larger of the decoded value and $compare
	 *		If $action == 'lt', return the smaller of the decoded value and $compare
	 */
	function file_size_decode($source, $compare = 0, $action = '')
	{

		$source = trim($source);
		$source = strtoupper($source);

		list($val, $unit) = preg_split('#(?<=\d)(?=[a-z])#i', $source);

		$val = (int) $val;

		if(!$source || is_numeric($source))
		{
			$val = (int) $source;
		}
		else
		{
			switch($unit)
			{
				case 'T':
				case 'TB':
					$val = $val * 1024 * 1024 * 1024 * 1024;
					break;
				case 'G':
				case 'GB':
					$val = $val * 1024 * 1024 * 1024;
					break;
				case 'M':
				case 'MB':
					$val = $val * 1024 * 1024;
					break;
				case 'K':
				case 'KB':
					$val = $val * 1024;
					break;
			}
		}
		if($val == 0)
		{
			return $compare;
		}

		switch($action)
		{
			case 'lt':
				return min($val, $compare);
			case 'gt':
				return max($val, $compare);
			default:
				return $val;
		}
		//	return 0;
	}

	/**
	 * Parse bytes to human readable format
	 * Former Download page function
	 * @param mixed $size file size in bytes or file path if $retrieve is true
	 * @param boolean $retrieve defines the type of $size
	 * @param integer $decimal
	 * @return string formatted size
	 */
	function file_size_encode($size, $retrieve = false, $decimal =2)
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
			return round($size/$kb, $decimal)."&nbsp;".CORE_LAN_KB;
		}
		else if($size < $gb)
		{
			return round($size/$mb, $decimal)."&nbsp;".CORE_LAN_MB;
		}
		else if($size < $tb)
		{
			return round($size/$gb, $decimal)."&nbsp;".CORE_LAN_GB;
		}
		else
		{
			return round($size/$tb, 2)."&nbsp;".CORE_LAN_TB;
		}
	}
	
	
	/** Recursive Chmod function. 
	 * @param string $path to folder
	 * @param integer $filemode perms for files
	 * @param integer $dirmode perms for directories
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
	 * Copy a file, or copy the contents of a folder.
	 * @param   string    $source    Source path
	 * @param   string   $dest      Destination path
	 * @param   array    $options
	 * @return  bool     Returns true on success, false on error
	 */
	function copy($source, $dest, $options=array())
	{

		$perm = !empty($options['perm']) ? $options['perm'] : 0755;
		$filter = !empty($options['git']) ? "" : ".git"; // filter out .git by default.

		// Simple copy for a file
		if(is_file($source))
		{
			return copy($source, $dest);
		}

		// Make destination directory
		if(!is_dir($dest))
		{
			mkdir($dest, $perm);
		}

		// Directory - so copy it.
		$dir = scandir($source);
		foreach($dir as $folder)
		{
			// Skip pointers
			if($folder === '.' || $folder == '..' || $folder === $filter)
			{
				continue;
			}

			$this->copy("$source/$folder", "$dest/$folder", $perm);
		}

		return true;
	}
	

	/**
	 * File retrieval function. by Cam.
	 * @param $file string actual path or {e_xxxx} path to file.
	 * 
	 */
	function send($file) 
	{
		global $e107;
		
	//	$pref 					= e107::getPref();
		$tp 					= e107::getParser();
		
		$DOWNLOADS_DIR 			= e107::getFolder('DOWNLOADS');		
		$DOWNLOADS_DIRECTORY 	= ($DOWNLOADS_DIR[0] == DIRECTORY_SEPARATOR) ? $DOWNLOADS_DIR : e_BASE.$DOWNLOADS_DIR; // support for full path eg. /home/account/folder. 
		$FILES_DIRECTORY 		= e_BASE.e107::getFolder('FILES');
		$MEDIA_DIRECTORY		= realpath(e_MEDIA); //  could be image, file or other type. 
		$SYSTEM_DIRECTORY		= realpath(e_SYSTEM); // downloading of logs or hidden files etc. via browser if required.
		
		$file = $tp->replaceConstants($file);
		
			
		@set_time_limit(10 * 60);
		@session_write_close();
		@e107_ini_set("max_execution_time", 10 * 60);
		while(@ob_end_clean()); // kill all output buffering else it eats server resources
		@ob_implicit_flush(TRUE);
		
		
		$filename = $file;
		$file = basename($file);
		$path = realpath($filename);
		$path_downloads = realpath($DOWNLOADS_DIRECTORY);
		$path_public = realpath($FILES_DIRECTORY."public/");
		
		
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
			//	if ($filename == null) { $filename = basename($this->data); }
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
	 * Return a user specific file directory for the current plugin with the option to create one if it does not exist.
	 *
	 * @param int     $user userid
	 * @param boolean $create
	 * @param null|string    $subDir
	 * @return bool|string
	 */
	public function getUserDir($user, $create = false, $subDir = null)
	{
		$tp = e107::getParser();

		$baseDir = e_MEDIA.'plugins/'.e_CURRENT_PLUGIN.'/';

		if(!empty($subDir))
		{
			$subDir = e107::getParser()->filter($subDir,'w');
			$baseDir .= rtrim($subDir,'/').'/';
		}

		if(is_numeric($user))
		{
			$baseDir .= ($user > 0) ? "user_". $tp->leadingZeros($user, 6) : "anon";
		}

		if($create == true && !is_dir($baseDir))
		{
			mkdir($baseDir, 0755, true); // recursively
		}

		$baseDir = rtrim($baseDir,'/')."/";

		return $baseDir;
	}


	/**
	 * Runs through the zip archive array and finds the root directory.
	 *
	 * @param $unarc
	 * @return bool|string
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
	 *
	 * @param array  $filePaths
	 * @param string $newFile
	 * @param array  $options
	 * @return bool|string
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
	 * Delete a file.
	 * @param $file
	 * @return bool
	 */
	public function delete($file)
	{
		if(empty($file))
		{
			return false;
		}

		$file = e107::getParser()->replaceConstants($file);

		if(file_exists($file))
		{
			return unlink($file);
		}

		return false;

	}


	/**
	 * Recursive Directory removal .
	 *
	 * @param $dir
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
	 * Routine processes the array of uploaded files according to both specific options set by the caller,
	 * and	system options configured by the main admin.
	 *
	 *		@param string $uploaddir Target directory (checked that it exists, but path not otherwise changed)
	 *
	 *		@param string $fileinfo Determines any special handling of file name (combines previous $fileinfo and $avatar parameters):
	 *			FALSE - default option; no processing
	 *          @param string $fileinfo = 'attachment+extra_text' Indicates an attachment (related to forum post or PM), and specifies some optional text which is
	 *				incorporated into the final file name (the original $fileinfo parameter).
	 *		    @param string  $fileinfo = 'prefix+extra_text' - indicates an attachment or file, and specifies some optional text which is prefixed to the file name
	 *			@param string  $fileinfo = 'unique'
	 *				- if the proposed destination file doesn't exist, saved under given name
	 *				- if the proposed destination file does exist, prepends time() to the file name to make it unique
	 *			@param string  $fileinfo =  'avatar'
	 *				- indicates an avatar is being uploaded (not used - options must be set elsewhere)
	 *
	 *		@param array $options An array of supplementary options, all of which will be given appropriate defaults if not defined:
	 *          @param $options['filetypes'] Name of file containing list of valid file types
	 *				- Always looks in the admin directory
	 *				- defaults to e_ADMIN.filetypes.xml, else e_ADMIN.admin_filetypes.php for admins (if file exists), otherwise e_ADMIN.filetypes.php for users.
	 *				- FALSE disables this option (which implies that 'extra_file_types' is used)
	 *		    @param string $options['file_mask'] Comma-separated list of file types which if defined limits the allowed file types to those which are in both this list and the
	 *				file specified by the 'filetypes' option. Enables restriction to, for example, image files.
	 *		    @param bool $options['extra_file_types'] - if is FALSE or undefined, rejects totally unknown file extensions (even if in $options['filetypes'] file).
	 *				if TRUE, accepts totally unknown file extensions which are in $options['filetypes'] file.
	 *				otherwise specifies a comma-separated list of additional permitted file extensions
	 *		    @param int 	$options['final_chmod'] - chmod() to be applied to uploaded files (0644 default)  (This routine expects an integer value, so watch formatting/decoding - its normally
	 *				specified in octal. Typically use intval($permissions,8) to convert)
	 *		    @param int 	$options['max_upload_size'] - maximum size of uploaded files in bytes, or as a string with a 'multiplier' letter (e.g. 16M) at the end.
	 *				- otherwise uses $pref['upload_maxfilesize'] if set
	 *				- overriding limit of the smaller of 'post_max_size' and 'upload_max_size' if set in php.ini
	 *				(Note: other parts of E107 don't understand strings with a multiplier letter yet)
	 *		@param string 	$options['file_array_name'] - the name of the 'input' array - defaults to file_userfile[] - otherwise as set.
	 *		@param int 	$options['max_file_count'] - maximum number of files which can be uploaded - default is 'unlimited' if this is zero or not set.
	 *		@param bool $options['overwrite'] - if TRUE, existing file of the same name is overwritten; otherwise returns 'duplicate file' error (default FALSE)
	 *		@param int 	$options['save_to_db'] - [obsolete] storage type - if set and TRUE, uploaded files were saved in the database (rather than as flat files)
	 *
	 *	@return boolean|array
	 *		Returns FALSE if the upload directory doesn't exist, or various other errors occurred which restrict the amount of meaningful information.
	 *		Returns an array, with one set of entries per uploaded file, regardless of whether saved or
	 *		discarded (not all fields always present) - $c is array index:
	 *		 	$uploaded[$c]['name'] - file name - as saved to disc
	 *			$uploaded[$c]['rawname'] - original file name, prior to any addition of identifiers etc (useful for display purposes)
	 *			$uploaded[$c]['type'] - mime type (if set - as sent by browser)
	 *			$uploaded[$c]['size'] - size in bytes (should be zero if error)
	 *			$uploaded[$c]['error'] - numeric error code (zero = OK)
	 *			$uploaded[$c]['index'] - if upload successful, the index position from the file_userfile[] array - usually numeric, but may be alphanumeric if coded
	 *			$uploaded[$c]['message'] - text of displayed message relating to file
	 *			$uploaded[$c]['line'] - only if an error occurred, has line number (from __LINE__)
	 *			$uploaded[$c]['file'] - only if an error occurred, has file name (from __FILE__)
	 *
	 *	On exit, uploaded files should all have been removed from the temporary directory.
	 *	No messages displayed - its caller's responsibility to handle errors and display info to
	 *	user (or can use handle_upload_messages() from this module)
	 *
	 *	Details of uploaded files are in $_FILES['file_userfile'] (or other array name as set) on entry.
	 *	Elements passed (from PHP) relating to each file:
	 *		['name']	- the original name
	 *		['type']	- mime type (if provided - not checked by PHP)
	 *		['size']	- file size in bytes
	 *		['tmp_name'] - temporary file name on server
	 *		['error']	- error code. 0 = 'good'. 1..4 main others, although up to 8 defined for later PHP versions
	 *	Files stored in server's temporary directory, unless another set
	 */
	public function getUploaded($uploaddir, $fileinfo = false, $options = array())
	{
		require_once(e_HANDLER."upload_handler.php");

		if($uploaddir == e_UPLOAD || $uploaddir == e_TEMP || $uploaddir == e_AVATAR_UPLOAD)
		{
			$path = $uploaddir;
		}
		elseif(defined('e_CURRENT_PLUGIN'))
		{
			$path = $this->getUserDir(USERID, true, str_replace("../",'',$uploaddir)); // .$this->get;
		}
		else
		{
			return false;
		}

		return process_uploaded_files($path, $fileinfo, $options);

	}


	/**
	 * Quickly scan and return a list of files in a directory.
	 *
	 * @param string $dir
	 * @param null $extensions
	 * @return array
	 */
	public function scandir($dir, $extensions=null)
	{
		$list = array();

		$ext = str_replace(",","|",$extensions);

		$tmp = scandir($dir);
		foreach($tmp as $v)
		{
			if($v == '.' || $v == '..')
			{
				continue;
			}

			if(!empty($ext) && !preg_match("/\.(".$ext.")$/i", $v))
			{

				continue;
			}

			$list[] = $v;
		}

		return $list ;
	}


	/**
	 * @param string $folder
	 * @param null   $type
	 * @return bool|string
	 */
	public function gitPull($folder='', $type=null)
	{
		$gitPath = defset('e_GIT','git'); // addo to e107_config.php to
		$mes = e107::getMessage();


	//	$text = 'umask 0022'; //Could correct permissions issue with 0664 files.
		// Change Dir.
		$folder = e107::getParser()->filter($folder,'file'); // extra filter to keep RIPS happy.

		switch($type)
		{
			case "plugin":
				$dir = realpath(e_PLUGIN.basename($folder));
				break;

			case "theme":
				$dir = realpath(e_THEME.basename($folder));
				break;

			default:
				$dir = e_ROOT;
		}



	//	$cmd1 = 'cd '.$dir;
		$cmd2 = 'cd '.$dir.'; '.$gitPath.' reset --hard'; // Remove any local changes.
		$cmd3 = 'cd '.$dir.'; '.$gitPath.' pull'; 	// Run Pull request



		$text = '';


		$mes->addDebug($cmd2);
		$mes->addDebug($cmd3);

	//	$text = `$cmd1 2>&1`;
		$text .= `$cmd2 2>&1`;
		$text .= `$cmd3 2>&1`;



		if(deftrue('e_DEBUG') || deftrue('e_GIT_DEBUG'))
		{
			$message = date('r')."\t\tgitPull()\t\t".$text;
			file_put_contents(e_LOG."fileClass.log",$message,FILE_APPEND);
		}

	//	$text .= `$cmd4 2>&1`;

	//	$text .= `$cmd5 2>&1`;

		return print_a($text,true);

	}



	/**
	 * Returns true is the URL is valid and false if it is not.
	 * @param $url
	 * @return bool
	 */
	public function isValidURL($url)
	{
		ini_set('default_socket_timeout', 1);
	   $headers = get_headers($url);
	//   print_a($headers);

	   return (stripos($headers[0],"200 OK") || stripos($headers[0],"302")) ? true : false;
	}


	/**
	 * Unzip Plugin or Theme zip file and move to plugin or theme folder.
	 *
	 * @param string $localfile - filename located in e_TEMP
	 * @param string $type - addon type, either 'plugin' or 'theme', (possibly 'language' in future).
	 * @param bool   $overwrite
	 * @return string unzipped folder name on success or false.
	 */
	public function unzipArchive($localfile, $type, $overwrite=false)
	{
		$mes = e107::getMessage();
		
		chmod(e_TEMP.$localfile, 0755);

		$fileinfo = array();

		$dir = false;

		if(class_exists('ZipArchive')) // PHP7 compat. method.
		{
			$zip = new ZipArchive;

			if($zip->open(e_TEMP.$localfile) === true)
			{
				for($i = 0; $i < $zip->numFiles; $i++ )
				{
					$filename = $zip->getNameIndex($i);

                    $fileinfo = pathinfo($filename);

                    if($fileinfo['dirname'] === '.')
                    {
                        $dir = $fileinfo['basename'];
                        break;
                    }
                    elseif($fileinfo['basename'] === 'plugin.php' || $fileinfo['basename'] === 'theme.php')
                    {
						$dir = $fileinfo['dirname'];
                    }

			     //   $stat = $zip->statIndex( $i );
			    //    print_a( $stat['name']  );
				}


				$zip->extractTo(e_TEMP);
				chmod(e_TEMP.$dir, 0755);

				if(empty($dir) && e_DEBUG)
				{
					print_a($fileinfo);
				}


				$zip->close();
			}




		}
		else // Legacy Method.
		{
			require_once(e_HANDLER."pclzip.lib.php");
		
			$archive 	= new PclZip(e_TEMP.$localfile);
			$unarc 		= ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_TEMP, PCLZIP_OPT_SET_CHMOD, 0755)); // Store in TEMP first.
			$dir 		= $this->getRootFolder($unarc);
		}



		$destpath 	= ($type == 'theme') ? e_THEME : e_PLUGIN;
	//	$typeDiz 	= ucfirst($type);
		
		@copy(e_TEMP.$localfile, e_BACKUP.$dir.".zip"); // Make a Backup in the system folder. 
		
		if($dir && is_dir($destpath.$dir))
		{
			if($overwrite === true)
			{
				if(file_exists(e_TEMP.$localfile))
				{
					$time = date("YmdHi");
					if(rename($destpath.$dir, e_BACKUP.$dir."_".$time))
					{
						$mes->addSuccess(ADLAN_195);
					}
				}
			}
			else
			{

				$mes->addError("(".ucfirst($type).") Already Downloaded - ".basename($destpath).'/'.$dir);

				if(file_exists(e_TEMP.$localfile))
				{
					@unlink(e_TEMP.$localfile);
				}

				$this->removeDir(e_TEMP.$dir);
				return false;
			}
		}
	
		if(empty($dir))
		{
			$mes->addError("Couldn't detect the root folder in the zip."); //  flush();
			@unlink(e_TEMP.$localfile);
			return false;		
		}
	
		if(is_dir(e_TEMP.$dir)) 
		{
			$res = rename(e_TEMP.$dir,$destpath.$dir);
			if($res === false)
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
	 *	@param string|boolean $file_mask - comma-separated list of allowed file types
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





	public function unzipGithubArchive($url='core')
	{

		switch($url)
		{
			case "core":
				$localfile      = 'e107-master.zip';
				$remotefile     = 'https://codeload.github.com/e107inc/e107/zip/master';
				$excludes       = array('e107-master/install.php','e107-master/favicon.ico');
				$excludeMatch   = false;
				break;

			// language.
			// eg. https://github.com/e107translations/Spanish/archive/v2.1.5.zip
			default:
				$localfile      = str_replace('https://github.com/e107translations/','',$url); // 'e107-master.zip';
				$localfile      = str_replace('/archive/v','-',$localfile); //remove dirs.
				$remotefile     = $url;
				$excludes       = array();
				$excludeMatch   = array('alt_auth','tagwords','faqs');

		}

		// Delete any existing file.
		if(file_exists(e_TEMP.$localfile))
		{
			unlink(e_TEMP.$localfile);
		}

		$result = $this->getRemoteFile($remotefile, $localfile, 'temp');

		if($result === false)
		{
			return false;
		}



		chmod(e_TEMP.$localfile, 0755);
		require_once(e_HANDLER."pclzip.lib.php");

		$zipBase = str_replace('.zip','',$localfile); // eg. e107-master
		$excludes[] = $zipBase;

		$newFolders = array(
			$zipBase.'/e107_admin/'       => e_BASE.e107::getFolder('ADMIN'),
			$zipBase.'/e107_core/'        => e_BASE.e107::getFolder('CORE'),
			$zipBase.'/e107_docs/'        => e_BASE.e107::getFolder('DOCS'),
			$zipBase.'/e107_handlers/'    => e_BASE.e107::getFolder('HANDLERS'),
			$zipBase.'/e107_images/'      => e_BASE.e107::getFolder('IMAGES'),
			$zipBase.'/e107_languages/'   => e_BASE.e107::getFolder('LANGUAGES'),
			$zipBase.'/e107_media/'       => e_BASE.e107::getFolder('MEDIA'),
			$zipBase.'/e107_plugins/'     => e_BASE.e107::getFolder('PLUGINS'),
			$zipBase.'/e107_system/'      => e_BASE.e107::getFolder('SYSTEM'),
			$zipBase.'/e107_themes/'      => e_BASE.e107::getFolder('THEMES'),
			$zipBase.'/e107_web/'         => e_BASE.e107::getFolder('WEB'),
			$zipBase.'/'                  => e_BASE
		);

		$srch = array_keys($newFolders);
		$repl = array_values($newFolders);

		$archive 	= new PclZip(e_TEMP.$localfile);
		$unarc 		= ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_TEMP, PCLZIP_OPT_SET_CHMOD, 0755)); // Store in TEMP first.

		$error = array();
		$success = array();
	//	$skipped = array();



		foreach($unarc as $k=>$v)
		{
			if($this->matchFound($v['stored_filename'],$excludeMatch))
			{
				continue;
			}

			if(in_array($v['stored_filename'],$excludes))
			{
				continue;
			}

			$oldPath = $v['filename'];
			$newPath =  str_replace($srch,$repl, $v['stored_filename']);

/*
			$success[] = $newPath;
			continue;*/

			if($v['folder'] ==1 && is_dir($newPath))
			{
				// $skipped[] =  $newPath. " (already exists)";
				continue;
			}

			if(!rename($oldPath,$newPath))
			{
				$error[] =  $newPath;
			}
			else
			{
				$success[] = $newPath;
			}

		}


		return array('success'=>$success, 'error'=>$error);

	}




	private function matchFound($file,$array)
	{
		if(empty($array))
		{
			return false;
		}

		foreach($array as $term)
		{
			if(strpos($file,$term)!==false)
			{
				return true;
			}

		}

		return false;

	}

	/**
	 * Checks that the directory exists and is writable.
	 *
	 * @param string $directory
	 *   A string containing the name of a directory path. A trailing slash will be trimmed from a path.
	 * @param int $options
	 *   A bitmask to indicate if the directory should be created if it does not exist (FILE_CREATE_DIRECTORY) or
	 *   made writable if it is read-only (FILE_MODIFY_PERMISSIONS).
	 *
	 * @return bool
	 *   TRUE if the directory exists (or was created) and is writable. FALSE otherwise.
	 */
	public function prepareDirectory($directory, $options = FILE_MODIFY_PERMISSIONS)
	{
		$directory = e107::getParser()->replaceConstants($directory);
		$directory = rtrim($directory, '/\\');

		// Check if directory exists.
		if(!is_dir($directory))
		{
			// Let mkdir() recursively create directories and use the default directory permissions.
			if(($options & FILE_CREATE_DIRECTORY) && @$this->mkDir($directory, null, true))
			{
				return $this->_chMod($directory);
			}

			return false;
		}

		// The directory exists, so check to see if it is writable.
		$writable = is_writable($directory);

		if(!$writable && ($options & FILE_MODIFY_PERMISSIONS))
		{
			return $this->_chMod($directory);
		}

		return $writable;
	}

	/**
	 * (Non-Recursive) Sets the permissions on a file or directory.
	 *
	 * @param string $path
	 *   A string containing a file, or directory path.
	 * @param int $mode
	 *   Integer value for the permissions. Consult PHP chmod() documentation for more information.
	 *
	 * @return bool
	 *   TRUE for success, FALSE in the event of an error.
	 */
	private function _chMod($path, $mode = null)
	{
		if(!isset($mode))
		{
			if(is_dir($path))
			{
				$mode = 0775;
			}
			else
			{
				$mode = 0664;
			}
		}

		if(@chmod($path, $mode))
		{
			return true;
		}

		return false;
	}

	/**
	 * Creates a directory.
	 *
	 * @param string $path
	 *   A string containing a file path.
	 * @param int $mode
	 *   Mode is used.
	 * @param bool $recursive
	 *   Default to FALSE.
	 * @param null $context
	 *   Refer to http://php.net/manual/ref.stream.php
	 *
	 * @return bool
	 *   Boolean TRUE on success, or FALSE on failure.
	 */
	public function mkDir($path, $mode = null, $recursive = false, $context = null)
	{
		if(!isset($mode))
		{
			$mode = 0775;
		}

		if(!isset($context))
		{
			return mkdir($path, $mode, $recursive);
		}
		else
		{
			return mkdir($path, $mode, $recursive, $context);
		}
	}


	/**
	 * @param int|null $int
	 */
	private function setErrorNum($int)
	{
		$this->errornum = $int;
	}



	/**
	 * New in v2.1.9
	 * Check uploaded file to try and identify dodgy content.
	 *	@param string $filename is the full path+name to the uploaded file on the server
	 *	@param string $target_name is the intended name of the file once transferred
	 *	@param array $allowed_filetypes is an array of permitted file extensions, in lower case, no leading '.'
	 *			(usually generated from filetypes.xml/filetypes.php)
	 *	@param boolean|string $unknown - handling of file types unknown to us/define additional types
	 *			if FALSE, rejects totally unknown file extensions (even if in $allowed_filetypes).
	 *			if $unknown is TRUE, accepts totally unknown file extensions.
	 *			otherwise $unknown is a comma-separated list of additional permitted file extensions
	 *	@return boolean - TRUE if file acceptable, FALSE if unacceptable. Use getErrorCode() immediately after to retrieve error code:
	 *		1 - file type not allowed
	 *		2 - can't read file contents
	 *		3 - illegal file contents (usually '<?php')
	 *		4 - not an image file
	 *		5 - bad image parameters - REMOVED
	 *		6 - not in supplementary list
	 *		7 - suspicious file contents
	 *		8 - unknown file type
	 *		9 - unacceptable file type (prone to exploits)
	 */
	function isClean($filename, $target_name='', $allowed_filetypes = array(), $unknown = false)
	{
		if(empty($target_name)) // no temp file, just use the filename.
		{
			$target_name = $filename;
		}

		$this->setErrorNum(null);
		// 1. Start by checking against filetypes - that's the easy one!
		$file_ext = pathinfo($target_name, PATHINFO_EXTENSION);

		$file_ext = strtolower($file_ext);

		// 2. For all files, read the first little bit to check for any flags etc
		$res = fopen($filename, 'rb');
		$tstr = fread($res, 2048);
		fclose($res);

		if($tstr === false)
		{
			$this->setErrorNum(2); // If can't read file, not much use carrying on!
			return false;
		}

		$archives = array('zip', 'gzip', 'gz', 'tar', 'bzip', '7z', 'rar');

		if(!in_array($file_ext,$archives) && stripos($tstr, '<?php') !== false)
		{
			$this->setErrorNum(3); // Pretty certain exploit
			return false;
		}

		if(!in_array($file_ext,$archives) && strpos($tstr, '<?') !== false)                // Bit more tricky - can sometimes be OK
		{
			if(stripos($tstr, '<?xpacket') === false && stripos($tstr, '<?xml ') === false)    // Allow the XMP header produced by CS4 and xml files.
			{
				$this->setErrorNum(7);
				return false;
			}
		}

		// 3. Now do what we can based on file extension
		switch($file_ext)
		{

			case 'jpg':
			case 'gif':
			case 'png':
			case 'jpeg':
			case 'pjpeg':
			case 'bmp':
			case 'swf':
			case 'fla':
	//		case 'flv':
			case 'swc':
			case 'psd':
			case 'ai':
			case 'eps':
			case 'svg':
			case 'tiff':
			case 'jpc': // http://fileinfo.com/extension/jpc
			case 'jpx': // http://fileinfo.com/extension/jpx
			case 'jb2': // http://fileinfo.com/extension/jb2
			case 'jp2': // http://fileinfo.com/extension/jp2
			case 'iff':
			case 'wbmp':
			case 'xbm':
			case 'ico':

				$ret = $this->getImageMime($filename);

				if($ret === false)
				{
					$this->setErrorNum(4);  // exif_imagetype didn't recognize the image mime
					return false;
				}

				// getimagesize() is extremely slow + it can't handle all required media!!! Abandon this check!
				//	return 5; // Zero size picture or bad file format
				break;

			case 'zip':
			case 'gzip':
			case 'gz':
			case 'tar':
			case 'bzip':
			case 'pdf':
			case 'doc':
			case 'docx':
			case 'xls':
			case 'xlsx':
			case 'rar':
			case '7z':
			case 'csv':
			case 'mp3':
			case 'wav':
			case 'mp4':
			case 'mpg':
			case 'mpa':
			case 'wma':
			case 'wmv':
			case 'flv': //Flash stream
			case 'f4v': //Flash stream
			case 'mov': //media
			case 'avi': //media
			case 'xml':

				break; // Just accept these

			case 'php':
			case 'php5':
			case 'php7':
			case 'htm':
			case 'html':
			case 'cgi':
			case 'pl':

				$this->setErrorNum(9); // Never accept these! Whatever the user thinks!
				return false;

			default: // Unknown file type.

				$this->setErrorNum(8);
				return false;
		}

		return true; // Accepted here
	}




	/**
	 * New in v2.1.9
	 * Check filename or path against filetypes.xml
	 * @param $file - real path to file.
	 * @return boolean
	 */
	public function isAllowedType($file,$targetFile='')
	{
		if(empty($targetFile))
		{
			$targetFile = $file;
		}

		$ext = pathinfo($targetFile, PATHINFO_EXTENSION);

		$types = $this->getAllowedFileTypes();

		if(isset($types[$ext]))
		{
			$maxSize = $types[$ext] * 1024;
			$fileSize = filesize($file);

		//	echo "\nisAllowedType(".basename($file).") ".$fileSize ." / ".$maxSize;

			if($fileSize  <= $maxSize)
			{
				return true;
			}

		}

		return false;

	}





	/**
	 * New in v2.1.9
	 * Get image (string) mime type
	 * or when extended - array [(string) mime-type, (array) associated extensions)].
	 * A much faster way to retrieve mimes than getimagesize()
	 *
	 * @param $filename
	 * @param bool|false $extended
	 * @return array|string|false
	 */
	function getImageMime($filename, $extended = false)
	{
		// mime types as returned from image_type_to_mime_type()
		// and associated file extensions
		$imageExtensions = array(
			'image/gif' 					=> array('gif'),
			'image/jpeg' 					=> array('jpg'),
			'image/png' 					=> array('png'),
			'application/x-shockwave-flash' => array('swf', 'swc'),
			'image/psd' 					=> array('psd'),
			'image/bmp' 					=> array('bmp'),
			'image/tiff' 					=> array('tiff'),
			'application/octet-stream' 		=> array('jpc', 'jpx', 'jb2'),
			'image/jp2' 					=> array('jp2'),
			'image/iff' 					=> array('iff'),
			'image/vnd.wap.wbmp' 			=> array('wbmp'),
			'image/xbm' 					=> array('xbm'),
			'image/vnd.microsoft.icon' 		=> array('ico')
		);

		$ret = image_type_to_mime_type(exif_imagetype($filename));

		if($extended)
		{
			return array(
				$ret,
				$ret && isset($imageExtensions[$ret]) ? $imageExtensions[$ret]: array()
			);
		}

		return $ret;

	}





	/**
	 *	New in v2.1.9
	 *  Get array of file types (file extensions) which are permitted - reads an XML-formatted definition file.
	 *	(Similar to @See{get_allowed_filetypes()}, but expects an XML file)
	 *
	 *	@param string $file_mask - comma-separated list of allowed file types - only those specified in both $file_mask and $def_file are returned
	 *	@return array - where key is the file type (extension); value is max upload size
	 */
	public function getAllowedFileTypes($file_mask = '')
	{
		$ret = array();
		$file_array = array();

		if ($file_mask)
		{
			$file_array = explode(',', $file_mask);
			foreach ($file_array as $k=>$f)
			{
				$file_array[$k] = trim($f);
			}
		}

		if(!is_readable(e_SYSTEM."filetypes.xml"))
		{
			return array();
		}

		$xml = e107::getXml();
		$xml->setOptArrayTags('class'); // class tag should be always array
		$temp_vars = $xml->loadXMLfile(e_SYSTEM."filetypes.xml", 'filetypes', false);

		if ($temp_vars === false)
		{
			echo "Error reading filetypes.xml<br />";
			return $ret;
		}

		foreach ($temp_vars['class'] as $v1)
		{
			$v = $v1['@attributes'];
			if (check_class($v['name']))
			{
				$current_perms[$v['name']] = array('type'=>$v['type'], 'maxupload'=>$v['maxupload']	);
				$a_filetypes = explode(',', $v['type']);
				foreach ($a_filetypes as $ftype)
				{
					$ftype = strtolower(trim(str_replace('.', '', $ftype))); // File extension

					if (!$file_mask || in_array($ftype, $file_array)) // We can load this extension
					{
						if (isset($ret[$ftype]))
						{
							$ret[$ftype] = $this->file_size_decode($v['maxupload'], $ret[$ftype], 'gt'); // Use largest value
						}
						else
						{
							$ret[$ftype] = $this->file_size_decode($v['maxupload']);
						}
					}
				}
			}
		}

		return $ret;
	}



}
