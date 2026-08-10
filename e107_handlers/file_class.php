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
 * @package       e107
 * @subpackage    e107_handlers
 * @version       $Id$
 * @author        e107 Inc.
 */

if(!defined('e107_INIT'))
{
	exit;
}
if(defined('SAFE_MODE') && SAFE_MODE === false)
{
	@set_time_limit(10 * 60);    // throws error in safe-mode.
}

//session_write_close();
@ini_set("max_execution_time", 10 * 60);
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


/**
 *
 */
class e_file
{

	/**
	 * How many redirects an outbound request may follow before it is refused.
	 */
	const CURL_MAX_REDIRECTS = 5;

	/**
	 * Array of directory names to ignore (in addition to any set by caller)
	 *
	 * @var array
	 */
	public $dirFilter = array();

	/**
	 * Array of file names to ignore (in addition to any set by caller)
	 *
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
	 *
	 * @return e_file
	 */
	function setDefaults()
	{

		$this->dirFilter = array('/', 'CVS', '.svn'); // Default directory filter (exact matches only)
		$this->fileFilter = array('^thumbs\.db$', '^Thumbs\.db$', '.*\._$', '^\.htaccess$', '^\.cvsignore$', '^\.ftpquota$', '^index\.html$', '^null\.txt$', '\.bak$', '^.tmp'); // Default file filter (regex format)

		return $this;
	}

	/**
	 * Set fileinfo mode
	 *
	 * @param string $val
	 * @return e_file
	 */
	public function setFileInfo($val = 'default')
	{

		$this->finfo = $val;

		return $this;
	}


	/**
	 * @param $filter
	 * @return $this
	 */
	public function setFileFilter($filter)
	{

		$this->fileFilter = $filter;

		return $this;
	}

	/**
	 * Clean and rename file name
	 *
	 * @param $f      array as returned by get_files();
	 * @param $rename boolean  - set to true to rename file.
	 * @return array
	 */
	public function cleanFileName($f, $rename = false)
	{

		$fullpath = $f['path'] . $f['fname'];
		$newfile = preg_replace("/[^a-z0-9-\._]/", "-", strtolower($f['fname']));
		$newpath = $f['path'] . $newfile;

		if($rename == true)
		{

			if(!rename($fullpath, $newpath))
			{
				$f['error'] = "Couldn't rename $fullpath to $newpath";
			}
		}

		$f['fname'] = $newfile;

		return $f;
	}

	/**
	 * @param $mode
	 * @return void
	 */
	function setMode($mode)
	{

		$this->mode = $mode;
	}


	/**
	 * @return null
	 */
	public function getErrorMessage()
	{

		return $this->error;
	}


	/**
	 * @return null
	 */
	public function getErrorCode()
	{

		return $this->errornum;
	}


	/**
	 * Read files from given path
	 *
	 * @param string  $path
	 * @param string  $fmask         [optional]
	 * @param string  $omit          [optional]
	 * @param int     $recurse_level [optional]
	 * @return array of file names/paths
	 */
	function get_files($path, $fmask = '', $omit = 'standard', $recurse_level = 0)
	{

		$ret = array();
		$invert = false;
		if(!empty($fmask) && strpos($fmask, '~') === 0)
		{
			$invert = true;                        // Invert selection - exclude files which match selection
			$fmask = substr($fmask, 1);
		}

		if($recurse_level < 0)
		{
			return $ret;
		}


		if(substr($path, -1) == '/')
		{
			$path = substr($path, 0, -1);
		}


		if(!is_dir($path) || !$handle = opendir($path))
		{
			return $ret;
		}
		if(($omit == 'standard') || ($omit == ''))
		{
			$omit = $this->fileFilter;
		}
		else
		{
			if(!is_array($omit))
			{
				$omit = array($omit);
			}
		}

		while(false !== ($file = readdir($handle)))
		{
			if($file === '.' || $file === '..')
			{
				continue;
			}

			if(is_dir($path . '/' . $file))
			{    // Its a directory - recurse into it unless a filtered directory or required depth achieved
				// Must always check for '.' and '..'
				if(($recurse_level > 0) && !in_array($file, $this->dirFilter) && !in_array($file, $omit))
				{
					$xx = $this->get_files($path . '/' . $file, $fmask, $omit, $recurse_level - 1);
					$ret = array_merge($ret, $xx);
				}
			}
			else
			{
				// Now check against standard reject list and caller-specified list
				if(($fmask == '') || ($invert != preg_match("#" . $fmask . "#", $file)))
				{    // File passes caller's filter here
					$rejected = false;

					// Check against the generic file reject filter
					foreach($omit as $rmask)
					{
						if(preg_match("#" . $rmask . "#", $file))
						{
							$rejected = true;
							$this->filesRejected[] = $file;
							break;            // continue 2 may well work
						}
					}
					if($rejected == false)
					{
						switch($this->mode)
						{
							case 'fname':
								$ret[] = $file;
								break;

							case 'path':
								$ret[] = $path . "/";
								break;

							case 'full':
								$ret[] = $path . "/" . $file;
								break;

							case 'all':
							default:
								if('default' != $this->finfo)
								{
									$finfo = $this->getFileInfo($path . "/" . $file, ('file' != $this->finfo)); // -> 'all' & 'image'
								}
								else
								{
									$finfo['path'] = $path . '/';  // important: leave this slash here and update other file instead.
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
	 *
	 * @param $mimeType
	 * @return string|null
	 */
	function getFileExtension($mimeType)
	{

		$extensions = array(
			'application/ecmascript'                                                    => '.es',
			'application/epub+zip'                                                      => '.epub',
			'application/java-archive'                                                  => '.jar',
			'application/javascript'                                                    => '.js',
			'application/json'                                                          => '.json',
			'application/msword'                                                        => '.doc',
			'application/octet-stream'                                                  => '.bin',
			'application/ogg'                                                           => '.ogx',
			'application/pdf'                                                           => '.pdf',
			'application/rtf'                                                           => '.rtf',
			'application/typescript'                                                    => '.ts',
			'application/vnd.amazon.ebook'                                              => '.azw',
			'application/vnd.apple.installer+xml'                                       => '.mpkg',
			'application/vnd.mozilla.xul+xml'                                           => '.xul',
			'application/vnd.ms-excel'                                                  => '.xls',
			'application/vnd.ms-fontobject'                                             => '.eot',
			'application/vnd.ms-powerpoint'                                             => '.ppt',
			'application/vnd.oasis.opendocument.presentation'                           => '.odp',
			'application/vnd.oasis.opendocument.spreadsheet'                            => '.ods',
			'application/vnd.oasis.opendocument.text'                                   => '.odt',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '.pptx',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => '.xlsx',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => '.docx',
			'application/vnd.visio'                                                     => '.vsd',
			'application/x-7z-compressed'                                               => '.7z',
			'application/x-abiword'                                                     => '.abw',
			'application/x-bzip'                                                        => '.bz',
			'application/x-bzip2'                                                       => '.bz2',
			'application/x-csh'                                                         => '.csh',
			'application/x-rar-compressed'                                              => '.rar',
			'application/x-sh'                                                          => '.sh',
			'application/x-shockwave-flash'                                             => '.swf',
			'application/x-tar'                                                         => '.tar',
			'application/xhtml+xml'                                                     => '.xhtml',
			'application/xml'                                                           => '.xml',
			'application/zip'                                                           => '.zip',
			'audio/aac'                                                                 => '.aac',
			'audio/midi'                                                                => '.midi',
			'audio/mpeg'                                                                => '.mp3',
			'audio/ogg'                                                                 => '.oga',
			'audio/wav'                                                                 => '.wav',
			'audio/webm'                                                                => '.weba',
			'font/otf'                                                                  => '.otf',
			'font/ttf'                                                                  => '.ttf',
			'font/woff'                                                                 => '.woff',
			'font/woff2'                                                                => '.woff2',
			'image/bmp'                                                                 => '.bmp',
			'image/gif'                                                                 => '.gif',
			'image/jpeg'                                                                => '.jpg',
			'image/png'                                                                 => '.png',
			'image/svg+xml'                                                             => '.svg',
			'image/tiff'                                                                => '.tiff',
			'image/webp'                                                                => '.webp',
			'image/x-icon'                                                              => '.ico',
			'text/calendar'                                                             => '.ics',
			'text/css'                                                                  => '.css',
			'text/csv'                                                                  => '.csv',
			'text/html'                                                                 => '.html',
			'text/plain'                                                                => '.txt',
			'video/mp4'                                                                 => '.mp4',
			'video/mpeg'                                                                => '.mpeg',
			'video/ogg'                                                                 => '.ogv',
			'video/webm'                                                                => '.webm',
			'video/x-msvideo'                                                           => '.avi',
		);

		if(isset($extensions[$mimeType]))
		{
			return $extensions[$mimeType];
		}

		return null;
	}

	/**
	 * Return information about a file, including mime-type
	 *
	 * @param string $path_to_file
	 * @param bool   $imgcheck
	 * @param bool   $auto_fix_ext
	 * @return array|bool
	 * @deprecated - use getFileInfo() instead.
	 */
	public function get_file_info($path_to_file, $imgcheck = true, $auto_fix_ext = true)
	{

		return $this->getFileInfo($path_to_file, $imgcheck, $auto_fix_ext);
	}

	/**
	 * Collect file information
	 *
	 * @param string  $path_to_file
	 * @param boolean $imgcheck
	 * @param boolean $auto_fix_ext
	 * @return array|bool
	 */
	public function getFileInfo($path_to_file, $imgcheck = true, $auto_fix_ext = true)
	{

		$finfo = array();

		if(!file_exists($path_to_file) || filesize($path_to_file) < 2) // Don't try and read 0 byte files.
		{
			return false;
		}

		$finfo['pathinfo'] = pathinfo($path_to_file);
		$finfo['mime'] = $this->getMime($path_to_file);

		if($auto_fix_ext && $finfo['mime'] === false)
		{

			if(class_exists('finfo')) // Best Mime detection method.
			{
				$fin = new finfo(FILEINFO_MIME);
				$result = $fin->file($path_to_file);
                $parts = explode(";", $result);

				$mime = trim($parts[0]);

				if(!empty($mime))
				{
					$finfo['mime'] = $mime;
				}


			}

			// Auto-Fix Files without an extensions using known mime-type.
			if(empty($finfo['pathinfo']['extension']) && !empty($finfo['mime']) && !is_dir($path_to_file))
			{
				if($ext = $this->getFileExtension($finfo['mime']))
				{
					$finfo['pathinfo']['extension'] = $ext;


					$newFile = $path_to_file . $ext;
					if(!file_exists($newFile))
					{
						if(rename($path_to_file, $newFile) === true)
						{
							$finfo['pathinfo'] = pathinfo($newFile);
							$path_to_file = $newFile;
						}
					}
				}
			}
		}


		if($imgcheck && is_file($path_to_file) && ($tmp = getimagesize($path_to_file)))
		{
			$finfo['img-width'] = $tmp[0];
			$finfo['img-height'] = $tmp[1];

			if(empty($finfo['mime']))
			{
				$finfo['mime'] = $tmp['mime'];
			}

		}

		if($tmp = stat($path_to_file))
		{

			$finfo['fsize'] = $tmp['size'];
			$finfo['modified'] = $tmp['mtime'];
		}

		$finfo['fullpath'] = $path_to_file;
		$finfo['fname'] = basename($path_to_file);
		$finfo['path'] = dirname($path_to_file) . '/';

		return $finfo;
	}


	/**
	 * Reject URLs that point at private/reserved IP ranges or non-HTTP(S) protocols.
	 * Define `e_REMOTE_FILE_ALLOW_PRIVATE` to bypass for legitimate intranet use.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function isUrlSafe($url)
	{
		return $this->resolveOutboundTarget($url) !== false;
	}


	/**
	 * The addresses a single hop is allowed to reach, or false when refused.
	 *
	 * Two questions, because a site can give them two answers.
	 * resolveOutboundTarget() is e107's own rule about schemes and addresses;
	 * isUrlSafe() is the public one a site or a plugin overrides to add rules of
	 * its own. Only the first was asked of a Location, so an overridden policy
	 * governed the URL as typed and nothing the chain went on to reach, which is
	 * the shape of the defect this walk exists to close.
	 *
	 * @param string $url
	 * @return array|false as {@see e_file::resolveOutboundTarget()}
	 */
	private function permittedOutboundTarget($url)
	{
		$target = $this->resolveOutboundTarget($url);

		if($target === false || !$this->isUrlSafe($url))
		{
			return false;
		}

		return $target;
	}


	/**
	 * Resolve $url to the addresses an outbound request is allowed to reach.
	 *
	 * Half of the per-hop predicate, beside {@see e_file::isUrlSafe()}. It runs
	 * on the URL as typed and again on every Location a redirect chain
	 * produces, and the addresses it returns are what the connection is pinned
	 * to: without the pin the validating lookup and the connecting lookup are
	 * two different lookups, and an attacker owns the interval between them.
	 *
	 * Define `e_REMOTE_FILE_ALLOW_PRIVATE` to bypass the address check for
	 * legitimate intranet use. The scheme check is not bypassable.
	 *
	 * @param string $url
	 * @return array|false array('scheme', 'host', 'port', 'addresses'), or
	 *                     false when the policy refuses the URL
	 */
	public function resolveOutboundTarget($url)
	{
		if(!is_string($url) || $url === '')
		{
			return false;
		}

		$parts = @parse_url($url);
		if(empty($parts['host']))
		{
			return false;
		}

		$scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
		if($scheme !== 'http' && $scheme !== 'https')
		{
			return false;
		}

		$target = array(
			'scheme'    => $scheme,
			'host'      => $parts['host'],
			'port'      => isset($parts['port']) ? (int) $parts['port'] : ($scheme === 'https' ? 443 : 80),
			'addresses' => array(),
		);

		if(defined('e_REMOTE_FILE_ALLOW_PRIVATE') && e_REMOTE_FILE_ALLOW_PRIVATE === true)
		{
			return $target;
		}

		$literal = $this->hostLiteralIp($target['host']);
		$addresses = ($literal !== false) ? array($literal) : $this->resolveHostname($target['host']);

		if(empty($addresses))
		{
			return false;
		}

		foreach($addresses as $ip)
		{
			$canonical = $this->canonicalizeIp($ip);
			if($canonical === false)
			{
				return false;
			}
			if(!filter_var($canonical, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
			{
				return false;
			}
		}

		$target['addresses'] = $addresses;

		return $target;
	}


	/**
	 * Every A and AAAA address $host has.
	 *
	 * @param string $host
	 * @return string[] empty when the name does not resolve
	 */
	protected function resolveHostname($host)
	{
		$records = @dns_get_record($host, DNS_A | DNS_AAAA);
		if(!is_array($records))
		{
			return array();
		}

		$addresses = array();
		foreach($records as $r)
		{
			if(!empty($r['ip']))    { $addresses[] = $r['ip']; }
			if(!empty($r['ipv6']))  { $addresses[] = $r['ipv6']; }
		}

		return $addresses;
	}


	/**
	 * The address an authority host is a literal for.
	 *
	 * @param string $host as it appears in the authority, brackets and all
	 * @return string|false false when $host is a name rather than a literal
	 */
	private function hostLiteralIp($host)
	{
		if($host === '')
		{
			return false;
		}

		if($host[0] === '[' && substr($host, -1) === ']')
		{
			$host = substr($host, 1, -1);
		}

		return filter_var($host, FILTER_VALIDATE_IP) ? $host : false;
	}


	/**
	 * Reduce an IP literal to the form that filter_var() can range-check
	 * reliably across PHP versions. IPv4-mapped IPv6 (`::ffff:x.y.z.w`)
	 * is unwrapped to its IPv4 counterpart because PHP < 8.3 returns the
	 * mapped form unchanged from FILTER_FLAG_NO_RES_RANGE, which is an
	 * SSRF bypass.
	 *
	 * @param string $ip
	 * @return string|false canonical IP, or false if the input is not a valid IP
	 */
	private function canonicalizeIp($ip)
	{
		$packed = @inet_pton($ip);
		if($packed === false)
		{
			return false;
		}

		if(strlen($packed) === 16 && substr($packed, 0, 10) === str_repeat("\x00", 10) && substr($packed, 10, 2) === "\xff\xff")
		{
			return inet_ntop(substr($packed, 12, 4));
		}

		return $ip;
	}


	/**
	 * Is $path absolute on the current platform?
	 *
	 * Covers POSIX `/`, Windows `C:\`, and UNC `\\server\share`. e107 supports
	 * absolute DOWNLOADS_DIRECTORY and UPLOADS_DIRECTORY overrides pointing
	 * outside the docroot.
	 *
	 * @param string $path
	 * @return bool
	 */
	public static function isAbsolutePath($path)
	{
		if(!is_string($path) || $path === '')
		{
			return false;
		}

		if($path[0] === '/' || $path[0] === '\\')
		{
			return true;
		}

		return (bool) preg_match('#^[A-Za-z]:[/\\\\]#', $path);
	}


	/**
	 * Is $path a relative path with no way out of its parent directory?
	 *
	 * Rejects traversal components, absolute paths, NUL bytes and {e_XXX}
	 * constants. A name such as `my..file.zip` passes: the test is on whole
	 * path components, not on the substring.
	 *
	 * @param string $path
	 * @return bool
	 */
	public static function isSafeRelativePath($path)
	{
		if(!is_string($path) || $path === '')
		{
			return false;
		}

		// Under e_SINGLE_ENTRY the query string reaches callers undecoded, so
		// both forms have to be clean.
		$forms = array($path, rawurldecode($path));

		foreach($forms as $form)
		{
			if(strpos($form, "\0") !== false || strpbrk($form, '{}') !== false)
			{
				return false;
			}

			if(self::isAbsolutePath($form))
			{
				return false;
			}

			foreach(preg_split('#[/\\\\]#', $form) as $part)
			{
				if($part === '..')
				{
					return false;
				}
			}
		}

		return true;
	}


	/**
	 * The directories send() will serve from when a caller does not name its own.
	 *
	 * Returned unresolved; resolveSendPath() canonicalises and drops whatever
	 * does not exist. e_UPLOAD is included because the download plugin serves
	 * user uploads from it, and an absolute UPLOADS_DIRECTORY would otherwise
	 * fall outside every root.
	 *
	 * @return array
	 */
	public function getSendRoots()
	{
		$downloads = e107::getFolder('DOWNLOADS');
		if(!self::isAbsolutePath($downloads))
		{
			$downloads = e_BASE . $downloads;
		}

		$uploads = e107::getFolder('UPLOADS');
		if(!self::isAbsolutePath($uploads))
		{
			$uploads = e_BASE . $uploads;
		}

		return array(
			$downloads,
			e_BASE . e107::getFolder('FILES') . 'public/',
			e_MEDIA,
			e_SYSTEM,
			$uploads,
		);
	}


	/**
	 * Canonicalise $filename and confirm one of $roots contains it.
	 *
	 * Returns the resolved path so callers stream from the canonical name
	 * rather than the one they checked, which is what stops a symlink being
	 * swapped between the check and the read.
	 *
	 * @param string     $filename path with {e_XXX} constants already expanded
	 * @param array|null $roots    allowed directories, null for getSendRoots()
	 * @return string|false canonical path, or false when no root contains it
	 */
	public function resolveSendPath($filename, $roots = null)
	{
		if(!is_string($filename) || $filename === '')
		{
			return false;
		}

		// realpath() throws ValueError on PHP 8 for a NUL byte, and returns NULL
		// rather than false on PHP 5.6/7.
		if(strpos($filename, "\0") !== false)
		{
			return false;
		}

		$path = @realpath($filename);
		if(empty($path))
		{
			return false;
		}

		return $this->matchSendRoot($path, $roots, $filename) === false ? false : $path;
	}


	/**
	 * Canonicalise the directory $dir and confirm it is one of $roots, or is
	 * held by one of them.
	 *
	 * resolveSendPath() answers "held by", which a root does not satisfy for
	 * itself. A caller walking up to the directory that would have held a
	 * missing file needs the root-inclusive question instead, or a file
	 * directly inside a root looks as though it came from outside one.
	 *
	 * @param string     $dir   directory path, constants already expanded
	 * @param array|null $roots allowed directories, null for getSendRoots()
	 * @return string|false canonical directory, or false when no root holds it
	 */
	public function resolveSendRoot($dir, $roots = null)
	{
		if(!is_string($dir) || $dir === '' || strpos($dir, "\0") !== false)
		{
			return false;
		}

		$path = @realpath($dir);
		if(empty($path) || !is_dir($path))
		{
			return false;
		}

		$subject = rtrim($path, '/\\') . DIRECTORY_SEPARATOR;

		return $this->matchSendRoot($subject, $roots, $dir) === false ? false : $path;
	}


	/**
	 * @param string     $path     canonical path, with a trailing separator when
	 *                             a root is to count as holding itself
	 * @param array|null $roots    allowed directories, null for getSendRoots()
	 * @param string     $filename the name the caller asked about, for the log
	 * @return string|false
	 */
	private function matchSendRoot($path, $roots, $filename)
	{
		if($roots === null)
		{
			$roots = $this->getSendRoots();
		}

		$windows = (DIRECTORY_SEPARATOR === '\\');
		$resolved = 0;

		foreach((array) $roots as $root)
		{
			if(!is_string($root) || $root === '' || strpos($root, "\0") !== false)
			{
				continue;
			}

			$dir = @realpath($root);
			if(empty($dir))
			{
				continue;
			}

			$resolved++;
			$dir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;
			$len = strlen($dir);

			if($windows ? (strncasecmp($path, $dir, $len) === 0) : (strncmp($path, $dir, $len) === 0))
			{
				return $dir;
			}
		}

		// deftrue() rather than the constant: thumb.php bootstraps e107_class.php
		// without class2.php, so E107_DEBUG_LEVEL is not always defined here.
		if($resolved === 0 && deftrue('E107_DEBUG_LEVEL'))
		{
			e107::getLog()->addDebug('e_file::resolveSendPath(): no configured root could be resolved; refusing to send ' . $filename);
		}

		return false;
	}


	/**
	 *     Grab a remote file and save it in the /temp directory. requires CURL
	 *
	 * @param string $remote_url
	 * @param string $local_file string filename to save as
	 * @param string $type       media, temp, or import
	 * @return boolean TRUE on success, FALSE on failure (which includes absence of CURL functions)
	 */
	function getRemoteFile($remote_url, $local_file, $type = 'temp', $timeout = 40)
	{

		$this->error = '';
		$this->setErrorNum(null);

		// check for cURL
		if(!function_exists('curl_init'))
		{
			$msg = 'e_file::getRemoteFile() requires cURL to be installed.';
			if(E107_DEBUG_LEVEL > 0)
			{
				e107::getLog()->addDebug($msg);
			}

			error_log($msg);

			return false;            // May not be installed
		}

		if(!$this->isUrlSafe($remote_url))
		{
			$this->error = 'Refused to fetch URL with non-HTTP(S) scheme or private/reserved IP: ' . $remote_url;
			error_log($this->error);
			return false;
		}

		$fp = fopen($this->remoteFilePath($type) . $local_file, 'w'); // media-directory or temp directory is the root.

		if($fp === false)
		{
			$this->error = 'Could not open ' . $this->remoteFilePath($type) . $local_file . ' for writing';
			error_log($this->error);

			return false;
		}

		// The transfer's own budget belongs to cURL: curlOptions() sets
		// CURLOPT_TIMEOUT from this same value, and curlFollow() spends one
		// budget across the whole redirect chain. PHP's execution limit is a
		// different thing and applies to the whole process, so it is only ever
		// raised here, never lowered. set_time_limit($timeout) re-armed it at
		// exactly $timeout, which turned this file's own ten minute grant above
		// into 40 seconds for everything that ran after the first download, and
		// 0, meaning no limit at all, into 40 seconds on CLI.
		$executionLimit = (int) ini_get('max_execution_time');

		if($executionLimit > 0 && $executionLimit < $timeout)
		{
			@set_time_limit($timeout);
		}

		$buffer = $this->curlFollow($remote_url, array('timeout' => $timeout),
			function($cp) use ($fp)
			{
				curl_setopt($cp, CURLOPT_FILE, $fp);
			},
			function() use ($fp)
			{
				// The bodies of the 3xx answers are not the download.
				rewind($fp);
				ftruncate($fp, 0);
			}
		);

		fclose($fp);

		if($buffer === false && !empty($this->error)) // Fixes curl_error output - here see #1936
		{
			error_log($this->error);
		}

		return (bool) $buffer;
	}


	/**
	 * The directory getRemoteFile() writes a $type download into.
	 *
	 * @param string $type media, import, or temp
	 * @return string
	 */
	public function remoteFilePath($type)
	{

		if($type === 'media')
		{
			return e_MEDIA;
		}

		if($type === 'import')
		{
			return e_IMPORT;
		}

		return e_TEMP;
	}

	/**
	 * @param string     $address
	 * @param array|null $options
	 *
	 * @return CurlHandle|false false when the outbound request policy refuses $address
	 */
	public function initCurl($address, $options = null)
	{

		return $this->curlHandle($address, $options, null);
	}


	/**
	 * initCurl(), with the option of reusing a target the caller has already
	 * put through the policy, so that one hop costs one name lookup.
	 *
	 * @param string     $address
	 * @param array|null $options
	 * @param array|null $target  as resolveOutboundTarget()
	 * @return CurlHandle|false
	 */
	private function curlHandle($address, $options, $target)
	{

		$curlOptions = $this->curlOptions($address, $options, $target);

		if($curlOptions === false)
		{
			$this->error = 'Refused to fetch URL with non-HTTP(S) scheme or private/reserved IP: ' . $address;

			return false;
		}

		if(!file_exists(e_SYSTEM . 'cookies.txt'))
		{
			file_put_contents(e_SYSTEM . 'cookies.txt', '');
		}

		$cu = curl_init();

		// curl_setopt_array() applies the options in order and stops at the
		// first one libcurl refuses, so a handle that half took the policy
		// would otherwise go out looking fine.
		if(!curl_setopt_array($cu, $curlOptions))
		{
			curl_close($cu);
			$this->error = 'Could not apply the outbound request options for: ' . $address;

			return false;
		}

		return $cu;

	}


	/**
	 * The cURL options an outbound request is issued with.
	 *
	 * Built as an array rather than applied to a handle so that the policy is
	 * readable: a cURL handle will not tell you what was set on it, and a
	 * default quietly flipped back is the commonest regression here.
	 *
	 * initCurl() is public and third-party code calls it directly, so the
	 * refusal lives here and not only in e_file's own callers. The addresses
	 * the connection is pinned to are the ones the policy resolved, whether
	 * that was done here or by the redirect walk that passes $target in.
	 *
	 * @param string     $address
	 * @param array|null $options as initCurl()
	 * @param array|null $target  a target the caller has already resolved
	 * @return array|false CURLOPT_* map, or false when the policy refuses $address
	 */
	public function curlOptions($address, $options = null, $target = null)
	{

		if($target === null)
		{
			$target = $this->resolveOutboundTarget($address);
		}

		if($target === false)
		{
			return false;
		}

		$options = (array) $options;

		$timeout = $this->outboundTimeout($options);

		$referer = $target['scheme'] . '://' . $target['host'];

		$curlOptions = array(
			CURLOPT_URL            => $address,
			CURLOPT_TIMEOUT        => $timeout,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => 0,
			CURLOPT_REFERER        => $referer,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_MAXREDIRS      => self::CURL_MAX_REDIRECTS,
			CURLOPT_USERAGENT      => "Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/81.0",
			CURLOPT_COOKIEFILE     => e_SYSTEM . 'cookies.txt',
			CURLOPT_COOKIEJAR      => e_SYSTEM . 'cookies.txt',
		);

		if(defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTP') && defined('CURLPROTO_HTTPS'))
		{
			$curlOptions[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
			$curlOptions[CURLOPT_REDIR_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
		}

		$pin = $this->curlResolveEntry($target);

		if($pin !== false)
		{
			$curlOptions[CURLOPT_RESOLVE] = array($pin);
		}

		if(defined('e_CURL_PROXY'))
		{
			$curlOptions[CURLOPT_PROXY] = e_CURL_PROXY;     // PROXY details with port
		}

		if(defined('e_CURL_PROXYUSERPWD'))
		{
			$curlOptions[CURLOPT_PROXYUSERPWD] = e_CURL_PROXYUSERPWD;   // Use if proxy have username and password
		}

		if(defined('e_CURL_PROXYTYPE'))
		{
			$curlOptions[CURLOPT_PROXYTYPE] = e_CURL_PROXYTYPE; // If expected to cal
		}

		if(!empty($options['post']))
		{
			$curlOptions[CURLOPT_POST] = true;
			// if array -> will encode the data as multipart/form-data, if URL-encoded string - application/x-www-form-urlencoded
			$curlOptions[CURLOPT_POSTFIELDS] = $options['post'];
		}

		if(!empty($options['postfields']))
		{
			$curlOptions[CURLOPT_POSTFIELDS] = $options['postfields'];
		}

		if(!empty($options['customrequest'])) // ie. GET, PUT, POST
		{
			$curlOptions[CURLOPT_CUSTOMREQUEST] = $options['customrequest'];
		}

		if(isset($options['header']) && is_array($options['header']))
		{
			$curlOptions[CURLOPT_HTTPHEADER] = $options['header'];
		}

		return $curlOptions;

	}


	/**
	 * A CURLOPT_RESOLVE entry pinning $target to the addresses that passed the
	 * policy, so cURL cannot look the name up a second time.
	 *
	 * @param array $target as returned by resolveOutboundTarget()
	 * @return string|false false when there is nothing to pin
	 */
	private function curlResolveEntry($target)
	{

		if(empty($target['addresses']) || $this->hostLiteralIp($target['host']) !== false)
		{
			return false;
		}

		$addresses = array();

		foreach($target['addresses'] as $ip)
		{
			$addresses[] = (strpos($ip, ':') !== false) ? '[' . $ip . ']' : $ip;
		}

		// Several addresses in one entry arrived in libcurl 7.59.0. An older
		// build discards an entry it cannot parse, which would leave the
		// connection unpinned, so offer it a single address instead.
		if($this->curlVersionNumber() < 0x073B00)
		{
			$addresses = array($addresses[0]);
		}

		return $target['host'] . ':' . $target['port'] . ':' . implode(',', $addresses);
	}


	/**
	 * @return int linked libcurl version as 0xMMmmpp, 0 when unknown
	 */
	protected function curlVersionNumber()
	{

		$version = function_exists('curl_version') ? curl_version() : null;

		return isset($version['version_number']) ? (int) $version['version_number'] : 0;
	}


	/**
	 * Issue $address and follow any redirect one hop at a time, putting every
	 * Location back through the outbound request policy.
	 *
	 * The walk is transport neutral because the defect is. libcurl and the
	 * stream wrappers both re-issue the request at whatever the server answers
	 * with and revalidate nothing, which makes a policy applied to the URL as
	 * typed exactly one hop deep on either of them. CURLOPT_PREREQFUNCTION
	 * would be tidier than walking the chain, but it needs libcurl 7.80 and
	 * PHP 8.2 and does nothing for the stream transport; walking works on
	 * every build e107 supports and is testable without a live connection.
	 *
	 * @param string        $address
	 * @param array|null    $options    as initCurl()
	 * @param callable      $fetchHop   function($url, array $options, array $target)
	 *                                  returning array('result', 'status', 'location')
	 *                                  or false
	 * @param callable|null $onRedirect called when a further hop is about to be issued
	 * @return mixed the final hop's body, or false
	 */
	private function followOutbound($address, $options, $fetchHop, $onRedirect = null)
	{

		$options = (array) $options;
		$url = $address;
		$redirects = 0;

		// CURLOPT_FOLLOWLOCATION spent the caller's timeout on the whole
		// transfer, redirects included. Walking the chain by hand would
		// otherwise multiply it by the hop cap.
		$deadline = time() + $this->outboundTimeout($options);

		while(true)
		{
			$target = $this->permittedOutboundTarget($url);

			if($target === false)
			{
				$this->error = 'Refused to fetch URL with non-HTTP(S) scheme or private/reserved IP: ' . $url;

				return false;
			}

			$remaining = $deadline - time();

			if($remaining <= 0)
			{
				$this->error = 'Ran out of time following redirects from: ' . $address;

				return false;
			}

			$options['timeout'] = $remaining;

			$hop = call_user_func($fetchHop, $url, $options, $target);

			if($hop === false)
			{
				return false;
			}

			$status = (int) $hop['status'];
			$location = (string) $hop['location'];

			if($status < 300 || $status > 399 || $location === '')
			{
				return $hop['result'];
			}

			$redirects++;

			if($redirects > self::CURL_MAX_REDIRECTS)
			{
				$this->error = 'Refused to follow more than ' . self::CURL_MAX_REDIRECTS . ' redirects from: ' . $address;

				return false;
			}

			if($status === 301 || $status === 302 || $status === 303)
			{
				// What CURLOPT_FOLLOWLOCATION did: drop the body and fall back
				// to GET. CURLOPT_CUSTOMREQUEST survives a redirect in libcurl
				// too, so it survives here.
				unset($options['post'], $options['postfields']);
			}

			if(isset($options['header']) && is_array($options['header']))
			{
				$options['header'] = $this->sameOriginHeaders($options['header'], $target, $location);
			}

			$url = $location;

			if($onRedirect !== null)
			{
				call_user_func($onRedirect);
			}
		}
	}


	/**
	 * The seconds an outbound request is allowed, as a caller may ask for it.
	 *
	 * @param array $options as initCurl()
	 * @return int
	 */
	private function outboundTimeout($options)
	{

		$timeout = (int) vartrue($options['timeout'], 10);
		$timeout = min($timeout, 120);

		return max($timeout, 3);
	}


	/**
	 * Credential-bearing headers a caller supplied for one origin, dropped when
	 * the next hop is somewhere else.
	 *
	 * Replacing CURLOPT_FOLLOWLOCATION with a walk means e107 owns the rules
	 * libcurl used to apply across a hop, and libcurl has stripped these on a
	 * host change since 7.58.0 (CVE-2018-1000007).
	 *
	 * @param string[] $headers
	 * @param array    $from as resolveOutboundTarget()
	 * @param string   $to   the URL the next hop goes to
	 * @return string[]
	 */
	private function sameOriginHeaders($headers, $from, $to)
	{

		$parts = @parse_url($to);
		$scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
		$host = isset($parts['host']) ? $parts['host'] : '';
		$port = isset($parts['port']) ? (int) $parts['port'] : ($scheme === 'https' ? 443 : 80);

		if($scheme === $from['scheme'] && $port === $from['port'] && strcasecmp($host, $from['host']) === 0)
		{
			return $headers;
		}

		$kept = array();

		foreach($headers as $header)
		{
			if(!preg_match('#^\s*(authorization|cookie|proxy-authorization)\s*:#i', $header))
			{
				$kept[] = $header;
			}
		}

		return $kept;
	}


	/**
	 * Resolve a Location against the URL it was answered from.
	 *
	 * libcurl hands CURLINFO_REDIRECT_URL over already absolute; a stream
	 * wrapper hands the header over as the server wrote it.
	 *
	 * @param string $base
	 * @param string $location
	 * @return string '' when there is nothing to follow
	 */
	private function absoluteUrl($base, $location)
	{

		$location = trim($location);

		if($location === '')
		{
			return '';
		}

		if(preg_match('#^[a-z][a-z0-9+.-]*:#i', $location))
		{
			return $location;
		}

		$parts = @parse_url($base);

		if(empty($parts['scheme']) || empty($parts['host']))
		{
			return '';
		}

		if(strpos($location, '//') === 0)
		{
			return $parts['scheme'] . ':' . $location;
		}

		$authority = $parts['scheme'] . '://' . $parts['host'];

		if(!empty($parts['port']))
		{
			$authority .= ':' . (int) $parts['port'];
		}

		if($location[0] === '/')
		{
			return $authority . $location;
		}

		$path = (isset($parts['path']) && $parts['path'] !== '') ? $parts['path'] : '/';
		$slash = strrpos($path, '/');
		$path = ($slash === false) ? '/' : substr($path, 0, $slash + 1);

		return $authority . $path . $location;
	}


	/**
	 * getRemoteContent()'s and getRemoteFile()'s transport when ext/curl is
	 * present.
	 *
	 * @param string        $address
	 * @param array|null    $options    as initCurl()
	 * @param callable|null $onHandle   applied to each hop's handle before it runs
	 * @param callable|null $onRedirect called when a further hop is about to be issued
	 * @return mixed curl_exec()'s return for the final hop, or false
	 */
	private function curlFollow($address, $options = null, $onHandle = null, $onRedirect = null)
	{

		return $this->followOutbound($address, $options,
			function($url, $hopOptions, $target) use ($onHandle)
			{
				return $this->curlHop($url, $hopOptions, $target, $onHandle);
			},
			$onRedirect
		);
	}


	/**
	 * One hop over ext/curl.
	 *
	 * @param string        $url
	 * @param array         $options
	 * @param array         $target   as resolveOutboundTarget()
	 * @param callable|null $onHandle
	 * @return array|false array('result', 'status', 'location'), or false
	 */
	private function curlHop($url, $options, $target, $onHandle)
	{

		$cu = $this->curlHandle($url, $options, $target);

		if($cu === false)
		{
			return false;
		}

		if($onHandle !== null)
		{
			call_user_func($onHandle, $cu);
		}

		$result = curl_exec($cu);

		if(curl_errno($cu))
		{
			$this->setErrorNum(curl_errno($cu));
			$this->error = "Curl error: " . curl_errno($cu) . ", " . curl_error($cu);
			curl_close($cu);

			return false;
		}

		if(!$this->peerWasPinned($cu, $target))
		{
			$this->error = 'Refused an answer from an address the outbound request policy did not resolve: ' . $url;
			curl_close($cu);

			return false;
		}

		$hop = array(
			'result'   => $result,
			'status'   => (int) curl_getinfo($cu, CURLINFO_HTTP_CODE),
			'location' => (string) curl_getinfo($cu, CURLINFO_REDIRECT_URL),
		);

		curl_close($cu);

		return $hop;
	}


	/**
	 * Did the connection reach one of the addresses the policy resolved?
	 *
	 * CURLOPT_RESOLVE is what pins it, but libcurl discards an entry it cannot
	 * parse and ignores the option outright behind a proxy, in both cases
	 * without an error, so the peer libcurl actually reached is read back
	 * rather than assumed. Behind a proxy there is nothing to read back: the
	 * proxy performs the name resolution and the address policy is advisory.
	 *
	 * @param resource|CurlHandle $cu
	 * @param array               $target as resolveOutboundTarget()
	 * @return bool
	 */
	private function peerWasPinned($cu, $target)
	{

		if(empty($target['addresses']) || defined('e_CURL_PROXY'))
		{
			return true;
		}

		$peer = $this->canonicalizeIp((string) curl_getinfo($cu, CURLINFO_PRIMARY_IP));

		if($peer === false)
		{
			return true;
		}

		$peer = @inet_pton($peer);

		foreach($target['addresses'] as $address)
		{
			$address = $this->canonicalizeIp($address);

			if($address !== false && @inet_pton($address) === $peer)
			{
				return true;
			}
		}

		return false;
	}


	/**
	 * getRemoteContent()'s transport when ext/curl is absent: the same walk,
	 * the same cap, the same policy on every hop.
	 *
	 * @param string     $address
	 * @param array|null $options as getRemoteContent()
	 * @return string|false
	 */
	private function streamFollow($address, $options)
	{

		// This transport has always run to its own timeout rather than the
		// caller's.
		$timeout = 5;
		$options = (array) $options;
		$options['timeout'] = $timeout;

		$old_timeout = ini_set('default_socket_timeout', $timeout);

		$result = $this->followOutbound($address, $options,
			function($url, $hopOptions, $target)
			{
				return $this->streamHop($url, $hopOptions, $target);
			}
		);

		if($old_timeout !== false)
		{
			@ini_set('default_socket_timeout', $old_timeout);
		}

		return $result;
	}


	/**
	 * One hop over the stream wrappers.
	 *
	 * @param string $url
	 * @param array  $options
	 * @param array  $target as resolveOutboundTarget()
	 * @return array|false array('result', 'status', 'location'), or false
	 */
	private function streamHop($url, $options, $target)
	{

		$timeout = $this->outboundTimeout($options);

		if(!function_exists('fopen') || !ini_get('allow_url_fopen'))
		{
			return $this->socketHop($url, $target, $timeout);
		}

		$request = $this->pinnedRequest($url, $target);

		if($request === false)
		{
			$this->error = 'Refused to fetch URL with non-HTTP(S) scheme or private/reserved IP: ' . $url;

			return false;
		}

		$context = array(
			'http' => array(
				'follow_location' => 0,
				'max_redirects'   => 1,
				'timeout'         => $timeout,
			),
			'ssl'  => array(
				'verify_peer'      => true,
				'verify_peer_name' => true,
				'peer_name'        => $target['host'],
			),
		);

		if($request['host'] !== '')
		{
			$context['http']['header'] = 'Host: ' . $request['host'];
		}

		$fp = @fopen($request['url'], 'r', false, stream_context_create($context));

		if($fp === false)
		{
			$this->error = 'Unable to fetch remote file: ' . $url;

			return false;
		}

		$meta = stream_get_meta_data($fp);
		$body = stream_get_contents($fp);
		fclose($fp);

		$headers = isset($meta['wrapper_data']) ? (array) $meta['wrapper_data'] : array();

		return array(
			'result'   => $body,
			'status'   => $this->headerStatus($headers),
			'location' => $this->absoluteUrl($url, $this->headerValue($headers, 'location')),
		);
	}


	/**
	 * One hop over a raw socket: no ext/curl and no allow_url_fopen.
	 *
	 * @param string $url
	 * @param array  $target as resolveOutboundTarget()
	 * @param int    $timeout
	 * @return array|false array('result', 'status', 'location'), or false
	 */
	private function socketHop($url, $target, $timeout)
	{

		$parts = @parse_url($url);

		if(empty($parts['host']))
		{
			return false;
		}

		// Connect to the address that passed the policy, not to whatever the
		// name resolves to by the time the socket opens.
		$peer = empty($target['addresses']) ? $target['host'] : $target['addresses'][0];
		$peer = (strpos($peer, ':') !== false) ? '[' . $peer . ']' : $peer;

		$transport = ($target['scheme'] === 'https') ? 'ssl://' : 'tcp://';
		$context = stream_context_create(array(
			'ssl' => array(
				'verify_peer'      => true,
				'verify_peer_name' => true,
				'peer_name'        => $target['host'],
			),
		));

		$remote = @stream_socket_client($transport . $peer . ':' . $target['port'], $errno, $errstr, $timeout,
			STREAM_CLIENT_CONNECT, $context);

		if($remote === false)
		{
			$this->error = "Sockets: Unable to open remote XML file: " . $url;

			return false;
		}

		$requestTarget = isset($parts['path']) ? $parts['path'] : '/';

		if(isset($parts['query']))
		{
			$requestTarget .= '?' . $parts['query'];
		}

		$host = $parts['host'];

		if(isset($parts['port']))
		{
			$host .= ':' . (int) $parts['port'];
		}

		stream_set_timeout($remote, $timeout);
		fwrite($remote, "GET " . $requestTarget . " HTTP/1.0\r\nHost: " . $host . "\r\nConnection: close\r\n\r\n");

		$response = '';

		while(!feof($remote))
		{
			$response .= fgets($remote, 4096);
		}

		fclose($remote);

		$split = explode("\r\n\r\n", $response, 2);
		$headers = explode("\r\n", $split[0]);

		return array(
			'result'   => isset($split[1]) ? $split[1] : '',
			'status'   => $this->headerStatus($headers),
			'location' => $this->absoluteUrl($url, $this->headerValue($headers, 'location')),
		);
	}


	/**
	 * The URL a stream should open, and the Host header that goes with it, so
	 * that the connection lands on an address the policy resolved.
	 *
	 * CURLOPT_RESOLVE has no stream equivalent: the address goes into the URL
	 * and the name goes into the Host header and into the certificate check.
	 *
	 * @param string $url
	 * @param array  $target as resolveOutboundTarget()
	 * @return array|false array('url', 'host'), 'host' being '' when nothing
	 *                     needed rewriting
	 */
	private function pinnedRequest($url, $target)
	{

		$parts = @parse_url($url);

		if(empty($parts['host']) || empty($parts['scheme']))
		{
			return false;
		}

		if(empty($target['addresses']) || $this->hostLiteralIp($target['host']) !== false)
		{
			return array('url' => $url, 'host' => '');
		}

		$address = $target['addresses'][0];
		$address = (strpos($address, ':') !== false) ? '[' . $address . ']' : $address;

		$host = $parts['host'];

		if(isset($parts['port']))
		{
			$host .= ':' . (int) $parts['port'];
			$address .= ':' . (int) $parts['port'];
		}

		$userinfo = '';

		if(isset($parts['user']))
		{
			$userinfo = $parts['user'];
			$userinfo .= isset($parts['pass']) ? ':' . $parts['pass'] : '';
			$userinfo .= '@';
		}

		$pinned = $parts['scheme'] . '://' . $userinfo . $address;
		$pinned .= isset($parts['path']) ? $parts['path'] : '/';
		$pinned .= isset($parts['query']) ? '?' . $parts['query'] : '';

		return array('url' => $pinned, 'host' => $host);
	}


	/**
	 * @param string[] $headers a response's header lines, status line included
	 * @return int 0 when there is no status line
	 */
	private function headerStatus($headers)
	{

		$status = 0;

		foreach($headers as $header)
		{
			if(preg_match('#^HTTP/[0-9.]+\s+(\d{3})#i', $header, $match))
			{
				$status = (int) $match[1];
			}
		}

		return $status;
	}


	/**
	 * @param string[] $headers a response's header lines
	 * @param string   $name    lower case
	 * @return string '' when the header is absent
	 */
	private function headerValue($headers, $name)
	{

		$value = '';

		foreach($headers as $header)
		{
			if(stripos($header, $name . ':') === 0)
			{
				$value = trim(substr($header, strlen($name) + 1));
			}
		}

		return $value;
	}


	/**
	 * FIXME add POST support
	 * Get Remote contents
	 * $options array:
	 * - 'timeout' (int): timeout in seconds
	 * - 'post' (array|urlencoded string): POST data
	 * - 'header' (array) headers, example: array('Content-Type: text/xml', 'X-Custom-Header: SomeValue');
	 *
	 * @param string $address
	 * @param array  $options [optional]
	 * @return string
	 */
	public function getRemoteContent($address, $options = array())
	{

		// Could do something like: if ($timeout <= 0) $timeout = $pref['get_remote_timeout'];  here

		//	$fileContents = '';
		$this->error = '';
		$this->setErrorNum(null);

		//	$mes = e107::getMessage();

		// May be paranoia, but streaky thought it might be a good idea

		$address = str_replace(array("\r", "\n", "\t", '&amp;'), array('', '', '', '&'), $address);

		if(!empty($options['decode']))
		{
			$address = urldecode($address);
		}

		// Keep this in first position.
		if(function_exists("curl_init")) // Preferred.
		{
			return $this->curlFollow($address, $options);
		}

		return $this->streamFollow($address, $options);
	}


	/**
	 * Get a list of directories matching $fmask, omitting any in the $omit array - same calling syntax as get_files()
	 * N.B. - no recursion - just looks in the specified directory.
	 *
	 * @param string $path
	 * @param string $fmask
	 * @param string $omit
	 * @return array
	 */
	function get_dirs($path, $fmask = '', $omit = 'standard')
	{

		$ret = array();
		$path = rtrim($path, '/');
		if($path[strlen($path) - 1] === '/')
			//	if(substr($path, -1) == '/')
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
			if(!is_array($omit))
			{
				$omit = array($omit);
			}
		}

		while(false !== ($file = readdir($handle)))
		{
			if(($file != '.') && ($file != '..') && !in_array($file, $this->dirFilter) && !in_array($file, $omit) && is_dir($path . '/' . $file) && ($fmask == '' || preg_match("#" . $fmask . "#", $file)))
			{
				$ret[] = $file;
			}
		}

		return $ret;
	}

	/**
	 * Delete a complete directory tree
	 *
	 * @param string $dir
	 * @return boolean success
	 */
	function rmtree($dir)
	{

		if(substr($dir, -1) != '/')
		{
			$dir .= '/';
		}
		if($handle = opendir($dir))
		{
			while($obj = readdir($handle))
			{
				if($obj != '.' && $obj != '..')
				{
					if(is_dir($dir . $obj))
					{
						if(!$this->rmtree($dir . $obj))
						{
							return false;
						}
					}
					elseif(is_file($dir . $obj))
					{
						if(!unlink($dir . $obj))
						{
							return false;
						}
					}
				}
			}

			closedir($handle);

			if(!@rmdir($dir))
			{
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 *    Parse a file size string (e.g. 16M) and compute the simple numeric value.
	 *
	 * @param string $source  - input string which may include 'multiplier' characters such as 'M' or 'G'. Converted to 'decoded value'
	 * @param int    $compare - a 'compare' value
	 * @param string $action  - values (gt|lt)
	 *
	 * @return int file size value in bytes.
	 *        If the decoded value evaluates to zero, returns the value of $compare
	 *        If $action == 'gt', return the larger of the decoded value and $compare
	 *        If $action == 'lt', return the smaller of the decoded value and $compare
	 */
	function file_size_decode($source, $compare = 0, $action = '')
	{

		$source = trim($source);
		$source = strtoupper($source);

		list($val, $unit) = array_pad(preg_split('#(?<=\d)(?=[a-z])#i', $source), 2, '');

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
	 *
	 * @param mixed   $size     file size in bytes or file path if $retrieve is true
	 * @param boolean $retrieve defines the type of $size
	 * @param int     $decimal
	 * @return string formatted size
	 */
	function file_size_encode($size, $retrieve = false, $decimal = 2)
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
			return '0&nbsp;' . CORE_LAN_B;
		}
		if($size < $kb)
		{
			return $size . "&nbsp;" . CORE_LAN_B;
		}
		elseif($size < $mb)
		{
			return round($size / $kb, $decimal) . "&nbsp;" . CORE_LAN_KB;
		}
		elseif($size < $gb)
		{
			return round($size / $mb, $decimal) . "&nbsp;" . CORE_LAN_MB;
		}
		elseif($size < $tb)
		{
			return round($size / $gb, $decimal) . "&nbsp;" . CORE_LAN_GB;
		}
		else
		{
			return round($size / $tb, 2) . "&nbsp;" . CORE_LAN_TB;
		}
	}


	/** Recursive Chmod function.
	 *
	 * @param string  $path     to folder
	 * @param int     $filemode perms for files
	 * @param int     $dirmode  perms for directories
	 * @example chmod_R('mydir', 0644, 0755);
	 */
	function chmod($path, $filemode = 0644, $dirmode = 0755)
	{

		if(is_dir($path))
		{
			if(!chmod($path, $dirmode))
			{
				$dirmode_str = decoct($dirmode);
				print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
				print "  `-> the directory '$path' will be skipped from recursive chmod\n";

				return;
			}
			$dh = opendir($path);
			while(($file = readdir($dh)) !== false)
			{
				if($file != '.' && $file != '..')   // skip self and parent pointing directories
				{
					$fullpath = $path . '/' . $file;
					$this->chmod($fullpath, $filemode, $dirmode);
				}
			}
			closedir($dh);
		}
		else
		{
			if(is_link($path))
			{
				print "link '$path' is skipped\n";

				return;
			}

			if(!chmod($path, $filemode))
			{
				$filemode_str = decoct($filemode);
				print "Failed applying filemode '$filemode_str' on file '$path'\n";

				return;
			}
		}
	}


	/**
	 * Copy a file, or copy the contents of a folder.
	 *
	 * @param string $source Source path
	 * @param string $dest   Destination path
	 * @param array  $options
	 * @return  bool     Returns true on success, false on error
	 */
	function copy($source, $dest, $options = array())
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
	 *
	 * @param string $file actual path or {e_xxxx} path to file.
	 * @param array  $opts (optional) type | disposition | encoding values.
	 *                     'roots' => array of directories to serve from, which
	 *                     REPLACES the getSendRoots() default rather than adding
	 *                     to it, so a caller handling untrusted input can narrow
	 *                     itself to a single directory.
	 *
	 */
	function send($file, $opts = array())
	{

		global $e107;

		//	$pref 					= e107::getPref();
		$tp = e107::getParser();

		$file = $tp->replaceConstants($file);

		$filename = $file;
		$file = basename($file);

		$roots = isset($opts['roots']) ? $opts['roots'] : null;
		$path = $this->resolveSendPath($filename, $roots);

		// Checked before the output buffers go, so a realpath() warning cannot
		// end up inside the body of a download.
		if($path === false)
		{
			if(E107_DEBUG_LEVEL > 0 && ADMIN)
			{
				$list = ($roots === null) ? $this->getSendRoots() : (array) $roots;
				echo "Failed to Download <b>" . $file . "</b><br />";
				echo "The file-path <b>" . $filename . "</b> is not inside any permitted directory:<ul>";
				foreach($list as $root)
				{
					echo "<li><b>" . $root . "</b></li>";
				}
				echo "</ul>";
				exit();
			}
			else
			{
				header("location: {$e107->base_path}");
				exit();
			}
		}

		@set_time_limit(10 * 60);
		@session_write_close();
		@ini_set("max_execution_time", 10 * 60);
		while(ob_get_length() !== false)  // destroy all ouput buffering
		{
			ob_end_clean();
		}
		@ob_implicit_flush();

		// $path, not $filename, from here down: the canonical name is the one
		// that passed the containment check.
		if(is_file($path) && is_readable($path) && connection_status() == 0)
		{
			$seek = 0;
			if(strpos(varset($_SERVER['HTTP_USER_AGENT'], ''), "MSIE") !== false)
			{
				$file = preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1);
			}
			if(isset($_SERVER['HTTP_RANGE']))
			{
				$seek = intval(substr($_SERVER['HTTP_RANGE'], strlen('bytes=')));
			}
			$bufsize = 2048;
			ignore_user_abort(true);
			$data_len = filesize($path);
			if($seek > ($data_len - 1))
			{
				$seek = 0;
			}
			//	if ($filename == null) { $filename = basename($this->data); }
			$res = fopen($path, 'rb');
			if($seek)
			{
				fseek($res, $seek);
			}
			$data_len -= $seek;

			$contentType = vartrue($opts['type'], 'application/force-download');
			$contentDisp = vartrue($opts['disposition'], 'attachment');

			header('Expires: 0');
			header("Cache-Control: max-age=30");
			header('Content-Type: ' . $contentType);
			header('Content-Disposition: ' . $contentDisp . '; filename="' . $file . '"');
			header("Content-Length: {$data_len}");
			header("Pragma: public");

			if(!empty($opts['encoding']))
			{
				header('Content-Transfer-Encoding: ' . $opts['encoding']);
			}

			if($seek)
			{
				header("Accept-Ranges: bytes");
				header("HTTP/1.0 206 Partial Content");
				header("status: 206 Partial Content");
				header("Content-Range: bytes {$seek}-" . ($data_len - 1) . "/{$data_len}");
			}
			while(!connection_aborted() && $data_len > 0)
			{
				echo fread($res, $bufsize);
				$data_len -= $bufsize;
			}
			fclose($res);
		}
		else
		{
			if(E107_DEBUG_LEVEL > 0 && ADMIN)
			{
				$mes = __METHOD__ . " -- File failed: " . $file . "\n";
				$mes .= "Path: " . $path . "\n";
				$mes .= "Backtrace: ";
				$mes .= print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true);
				trigger_error($mes);
				exit();
			}
			else
			{
				header("location: " . e_BASE . "index.php");
				exit();
			}
		}
	}


	/**
	 * Write the guard files that keep the contents of a directory from being
	 * fetched or listed directly.
	 *
	 * The caller decides, never the helper. getUserDir() is shared between
	 * plugins with opposite requirements, and the question of whether a
	 * directory's files may be fetched off the web server belongs to whatever
	 * owns them.
	 *
	 * The deny rule is read by Apache and by nothing else. On nginx, lighttpd
	 * or IIS it is an inert text file, and whatever else keeps those files
	 * private has to go on doing so unaided. The blank index.html works on any
	 * server, but only against a directory listing.
	 *
	 * It refuses with FileInfo and Limit class directives because those are the
	 * only classes e107 has ever needed: e107.htaccess asks for nothing outside
	 * FileInfo, Options, Indexes and Limit. A directive whose class the host has
	 * not granted through AllowOverride is a fatal configuration error rather
	 * than an ignored line, so an AuthConfig "Require all denied" would answer
	 * 500 for the whole subtree on a host that grants e107 what e107 asks for.
	 *
	 * Cheap enough to call ahead of every write: two file_exists() once the
	 * directory is covered, and nothing already there is rewritten.
	 *
	 * @param string $path directory to protect; not created if it is missing
	 * @return boolean true when both guard files are in place
	 */
	public function protectDirectory($path)
	{

		if(empty($path) || !is_dir($path))
		{
			return false;
		}

		$deny = "# Written by e107. The files in this directory are not for direct download.\n"
			. "<IfModule mod_alias.c>\n\tRedirectMatch 403 ^\n</IfModule>\n"
			. "<IfModule mod_rewrite.c>\n\tRewriteEngine On\n\tRewriteRule .* - [F]\n</IfModule>\n"
			. "<IfModule !mod_authz_core.c>\n\tOrder allow,deny\n\tDeny from all\n</IfModule>\n";

		$path = rtrim($path, '/\\') . '/';

		$guards = array('.htaccess' => $deny, 'index.html' => '');

		$done = true;

		foreach($guards as $file => $contents)
		{
			if(file_exists($path . $file))
			{
				continue;
			}

			if(file_put_contents($path . $file, $contents) === false)
			{
				$done = false;
			}
		}

		return $done;
	}


	/**
	 * Return a user specific file directory for the current plugin with the option to create one if it does not exist.
	 *
	 * @param int         $user userid
	 * @param boolean     $create
	 * @param null|string $subDir
	 * @return string
	 */
	public function getUserDir($user, $create = false, $subDir = null)
	{

		$tp = e107::getParser();

		$baseDir = e_MEDIA . 'plugins/' . e_CURRENT_PLUGIN . '/';

		if(!empty($subDir))
		{
			$subDir = e107::getParser()->filter($subDir, 'w');
			$baseDir .= rtrim($subDir, '/') . '/';
		}

		if(is_numeric($user))
		{
			$baseDir .= ($user > 0) ? "user_" . $tp->leadingZeros($user, 6) : "anon";
		}

		if($create == true && !is_dir($baseDir))
		{
			mkdir($baseDir, 0755, true); // recursively
		}

		$baseDir = rtrim($baseDir, '/') . "/";

		return $baseDir;
	}


	/** First line of {@see scriptExecutionRule()}, and how a written rule is recognised again. */
	const SCRIPT_RULE_MARKER = '# e107 script execution rule';


	/**
	 * The rule e107_media carries, kept here rather than at the call site so a
	 * host with a different idea of what an interpreter is has one file to edit.
	 *
	 * Every directive is AllowOverride FileInfo class. That is deliberate: the
	 * rule is written without an administrator asking, onto sites nobody has
	 * surveyed, and a directive the host does not permit in .htaccess is not
	 * ignored but a fatal parse error, which would answer every request for
	 * every avatar and every site image with a 500. FileInfo is the one class
	 * e107's own e107.htaccess proves is granted, through ErrorDocument and
	 * RewriteEngine. Require needs AuthConfig and Deny needs Limit, so neither
	 * appears here; the refusal is RedirectMatch, which is FileInfo.
	 *
	 * @return string
	 */
	public static function scriptExecutionRule()
	{

		$scripts = "phar|php|php[0-9]|phps|phtml|pht|shtml|cgi|htaccess|htpasswd";

		return self::SCRIPT_RULE_MARKER . ". Delete it and the directory runs what it holds.\n"
			. "#\n"
			. "# This tree is public by design: avatars, site images and every\n"
			. "# {e_MEDIA_IMAGE} URL a theme emits are fetched from it directly, so\n"
			. "# nothing here stops an image being read. What it stops is a file being\n"
			. "# executed, because the bytes under this directory arrive from uploads\n"
			. "# and from remote feeds.\n"
			. "#\n"
			. "# e107 appends this block once and recognises it by the line above, so\n"
			. "# an edited copy survives an upgrade.\n"
			. "\n"
			. "RemoveHandler .phar .php .php3 .php4 .php5 .php6 .php7 .php8 .phps .phtml .pht .shtml .cgi\n"
			. "RemoveType .phar .php .php3 .php4 .php5 .php6 .php7 .php8 .phps .phtml .pht .shtml .cgi\n"
			. "\n"
			. "<FilesMatch \"(?i)\\.(" . $scripts . ")(\\.|$)\">\n"
			. "\tSetHandler none\n"
			. "</FilesMatch>\n"
			. "\n"
			. "<IfModule mod_alias.c>\n"
			. "\tRedirectMatch 403 \"(?i)\\.(" . $scripts . ")(\\.|$)\"\n"
			. "</IfModule>\n"
			. "\n"
			. "# A directory holding an .htaccess of its own replaces the parent's\n"
			. "# rewrite rules rather than adding to them, so the refusal e107.htaccess\n"
			. "# makes of these two methods is repeated here.\n"
			. "<IfModule mod_rewrite.c>\n"
			. "\tRewriteEngine On\n"
			. "\tRewriteCond %{REQUEST_METHOD} ^(TRACE|TRACK)\n"
			. "\tRewriteRule .* - [F]\n"
			. "</IfModule>\n";
	}


	/**
	 * Stop a directory executing what it holds, while leaving it readable.
	 *
	 * The opposite of {@see protectDirectory()} in what it allows and the same in
	 * how it is applied. That one denies every request for the directory, which
	 * is right for a private message attachment and wrong for e107_media: a deny
	 * rule there takes every avatar, every site image and every theme's
	 * {e_MEDIA_IMAGE} URL off the site. This one refuses only the extensions a
	 * web server hands to an interpreter.
	 *
	 * A file whose extension is not one of those is untouched, so the bytes a
	 * site already serves go on being served byte for byte.
	 *
	 * Read by Apache and by nothing else. On nginx, lighttpd or IIS it is an
	 * inert text file and the server's own configuration has to do the same job.
	 *
	 * The rule is appended to whatever the directory already holds rather than
	 * skipped, because a hosting panel or a cache plugin leaving its own
	 * .htaccess in the media tree is common and would otherwise mean the tree
	 * never gets a rule at all. Re-running is a no-op: the block carries
	 * {@see SCRIPT_RULE_MARKER} and is written once.
	 *
	 * @param string $path directory to cover; not created if it is missing
	 * @return boolean true when the rule is in place
	 */
	public function blockScriptExecution($path)
	{

		if(empty($path) || !is_dir($path))
		{
			return false;
		}

		$file = rtrim($path, '/\\') . '/.htaccess';
		$existing = is_file($file) ? (string) @file_get_contents($file) : '';

		if(strpos($existing, self::SCRIPT_RULE_MARKER) !== false)
		{
			return true;
		}

		if(!is_writable(is_file($file) ? $file : $path))
		{
			return false;
		}

		$rule = ($existing === '' ? '' : "\n") . self::scriptExecutionRule();

		return @file_put_contents($file, $rule, FILE_APPEND) !== false;
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
			$target = trim($d['stored_filename'], '/');

			$test = basename(str_replace(e_TEMP, "", $d['stored_filename']), '/');

			if($d['folder'] == 1 && $target == $test)  //
			{
				//	$text .= "\\n test = ".$test;
				$text = "getRootDirectory: " . $d['stored_filename'];
				$text .= "<br />test=" . $test;
				$text .= "<br />target=" . $target;

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


	private function addToZip(ZipArchive $zip, $src, $localname)
	{

		if(is_dir($src))
		{
			$dir = opendir($src);

			// add empty directories
			$zip->addEmptyDir($localname);

			while(false !== ($file = readdir($dir)))
			{
				if(($file != '.') && ($file != '..'))
				{
					$this->addToZip($zip, $src . '/' . $file, $localname . '/' . $file);
				}
			}

			closedir($dir);
		}
		elseif(is_file($src))
		{
			if(!$zip->addFile($src, $localname))
			{
				$this->error = "Could not add file: $src";
				e107::getLog()->addError($this->error)->save('FILE', E_LOG_NOTICE);
			}
		}
	}


	/**
	 * Zip up folders and files
	 *
	 * @param array  $filePaths
	 * @param string $newFile
	 * @param array  $options
	 * @return bool|string
	 */
	 public function zip($filePaths = null, $newFile = '', $options = array())
	{
		if(empty($newFile))
		{
			$newFile = e_BACKUP . eHelper::title2sef(SITENAME) . "_" . date("Y-m-d-H-i-s") . ".zip";
		}

		if($filePaths === null)
		{
			return "No file-paths set!";
		}

		$zip = new ZipArchive();

		if($zip->open($newFile, ZipArchive::CREATE) !== true)
		{
			$this->error = "Cannot open <$newFile>\n";
			e107::getLog()->addError($this->error)->save('FILE', E_LOG_NOTICE);

			return false;
		}

		$removePath = (!empty($options['remove_path'])) ? $options['remove_path'] : e_BASE;

		foreach($filePaths as $file)
		{
			$localname = str_replace($removePath, '', $file);
			$this->addToZip($zip, $file, rtrim($localname, '/'));
		}

		$zip->close();

		return $newFile;
	}


	/**
	 * Delete a file.
	 *
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

		if(is_dir($dir))
		{
			$objects = scandir($dir);
			foreach($objects as $object)
			{
				if($object != "." && $object != "..")
				{
					if(filetype($dir . "/" . $object) == "dir")
					{
						$this->removeDir($dir . "/" . $object);
					}
					else
					{
						@unlink($dir . "/" . $object);
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
	 * and    system options configured by the main admin.
	 *
	 * @param string $uploaddir  Target directory (checked that it exists, but path not otherwise changed)
	 *
	 * @param string $fileinfo   Determines any special handling of file name (combines previous $fileinfo and $avatar parameters):
	 *                           FALSE - default option; no processing
	 *                           = 'attachment+extra_text' Indicates an attachment (related to forum post or PM), and specifies some optional text which is
	 *                           incorporated into the final file name (the original $fileinfo parameter).
	 *                           = 'prefix+extra_text' - indicates an attachment or file, and specifies some optional text which is prefixed to the file name
	 *                           = 'unique'
	 *                           - if the proposed destination file doesn't exist, saved under given name
	 *                           - if the proposed destination file does exist, prepends time() to the file name to make it unique
	 *                           =  'avatar'
	 *                           - indicates an avatar is being uploaded (not used - options must be set elsewhere)
	 *
	 * @param array  $options    = [  An array of supplementary options, all of which will be given appropriate defaults if not defined:
	 *                           'filetypes'            => (string)         Name of file containing list of valid file types
	 *                           - Always looks in the admin directory
	 *                           - defaults to e_ADMIN.filetypes.xml, else e_ADMIN.admin_filetypes.php for admins (if file exists), otherwise e_ADMIN.filetypes.php for users.
	 *                           - FALSE disables this option (which implies that 'extra_file_types' is used)
	 *                           'file_mask'            => (string)         Comma-separated list of file types which if defined limits the allowed file types to those which are in both this list and the
	 *                           file specified by the 'filetypes' option. Enables restriction to, for example, image files.
	 *                           'filetypes'            => (bool)         file).
	 *                           if TRUE, accepts totally unknown file extensions which are in $options['filetypes'] file.
	 *                           otherwise specifies a comma-separated list of additional permitted file extensions
	 *                           'final_chmod'        => (int)         - chmod() to be applied to uploaded files (0644 default)  (This routine expects an integer value, so watch formatting/decoding - its normally
	 *                           specified in octal. Typically use intval($permissions,8) to convert)
	 *                           'max_upload_size'        => (int)         - maximum size of uploaded files in bytes, or as a string with a 'multiplier' letter (e.g. 16M) at the end.
	 *                           - otherwise uses $pref['upload_maxfilesize'] if set
	 *                           - overriding limit of the smaller of 'post_max_size' and 'upload_max_size' if set in php.ini
	 *                           (Note: other parts of E107 don't understand strings with a multiplier letter yet)
	 *                           'file_array_name'    => (string)         - the name of the 'input' array - defaults to file_userfile[] - otherwise as set.
	 *                           'max_file_count'    => (int)         - maximum number of files which can be uploaded - default is 'unlimited' if this is zero or not set.
	 *                           'overwrite'            => (bool)         - if TRUE, existing file of the same name is overwritten; otherwise returns 'duplicate file' error (default FALSE)
	 *                           'save_to_db'        => (int)         - [obsolete] storage type - if set and TRUE, uploaded files were saved in the database (rather than as flat files)
	 *                           ]
	 * @return boolean|array
	 *                           Returns FALSE if the upload directory doesn't exist, or various other errors occurred which restrict the amount of meaningful information.
	 *                           Returns an array, with one set of entries per uploaded file, regardless of whether saved or
	 *                           discarded (not all fields always present) - $c is array index:
	 *                           $uploaded[$c]['name'] - file name - as saved to disc
	 *                           $uploaded[$c]['rawname'] - original file name, prior to any addition of identifiers etc (useful for display purposes)
	 *                           $uploaded[$c]['type'] - mime type (if set - as sent by browser)
	 *                           $uploaded[$c]['size'] - size in bytes (should be zero if error)
	 *                           $uploaded[$c]['error'] - numeric error code (zero = OK)
	 *                           $uploaded[$c]['index'] - if upload successful, the index position from the file_userfile[] array - usually numeric, but may be alphanumeric if coded
	 *                           $uploaded[$c]['message'] - text of displayed message relating to file
	 *                           $uploaded[$c]['line'] - only if an error occurred, has line number (from __LINE__)
	 *                           $uploaded[$c]['file'] - only if an error occurred, has file name (from __FILE__)
	 *
	 *    On exit, uploaded files should all have been removed from the temporary directory.
	 *    No messages displayed - its caller's responsibility to handle errors and display info to
	 *    user (or can use handle_upload_messages() from this module)
	 *
	 *    Details of uploaded files are in $_FILES['file_userfile'] (or other array name as set) on entry.
	 *    Elements passed (from PHP) relating to each file:
	 *        ['name']    - the original name
	 *        ['type']    - mime type (if provided - not checked by PHP)
	 *        ['size']    - file size in bytes
	 *        ['tmp_name'] - temporary file name on server
	 *        ['error']    - error code. 0 = 'good'. 1..4 main others, although up to 8 defined for later PHP versions
	 *    Files stored in server's temporary directory, unless another set
	 */
	public function getUploaded($uploaddir, $fileinfo = false, $options = array())
	{

		require_once(e_HANDLER . "upload_handler.php");

		if($uploaddir == e_UPLOAD || $uploaddir == e_TEMP || $uploaddir == e_AVATAR_UPLOAD)
		{
			$path = $uploaddir;
		}
		elseif(defined('e_CURRENT_PLUGIN'))
		{
			$path = $this->getUserDir(USERID, true, str_replace("../", '', $uploaddir)); // .$this->get;
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
	 * @param null   $extensions
	 * @return array
	 */
	public function scandir($dir, $extensions = null)
	{

		$list = array();

		$ext = str_replace(",", "|", $extensions);

		$tmp = scandir($dir);
		foreach($tmp as $v)
		{
			if($v == '.' || $v == '..')
			{
				continue;
			}

			if(!empty($ext) && !preg_match("/\.(" . $ext . ")$/i", $v))
			{

				continue;
			}

			$list[] = $v;
		}

		return $list;
	}


	/**
	 * @param string $folder
	 * @param null   $type
	 * @return bool|string
	 */
	public function gitPull($folder = '', $type = null)
	{

		$gitPath = defset('e_GIT', 'git'); // addo to e107_config.php to
		$mes = e107::getMessage();


		//	$text = 'umask 0022'; //Could correct permissions issue with 0664 files.
		// Change Dir.
		$folder = e107::getParser()->filter($folder, 'file'); // extra filter to keep RIPS happy.

		switch($type)
		{
			case "plugin":
				$dir = realpath(e_PLUGIN . basename($folder));
				break;

			case "theme":
				$dir = realpath(e_THEME . basename($folder));
				break;

			default:
				$dir = e_ROOT;
		}


		//	$cmd1 = 'cd '.$dir;
		$cmd2 = 'cd ' . $dir . '; ' . $gitPath . ' reset --hard'; // Remove any local changes.
		$cmd3 = 'cd ' . $dir . '; ' . $gitPath . ' pull';    // Run Pull request


		$text = '';


		$mes->addDebug($cmd2);
		$mes->addDebug($cmd3);

		//	$text = `$cmd1 2>&1`;
		$text .= `$cmd2 2>&1`;
		$text .= `$cmd3 2>&1`;


		if(deftrue('e_DEBUG') || deftrue('e_GIT_DEBUG'))
		{
			$message = date('r') . "\t\tgitPull()\t\t" . $text;
			file_put_contents(e_LOG . "fileClass.log", $message, FILE_APPEND);
		}

		//	$text .= `$cmd4 2>&1`;

		//	$text .= `$cmd5 2>&1`;

		return print_a($text, true);

	}


	/**
	 * Returns true is the URL is valid and false if it is not.
	 *
	 * @param $url
	 * @return bool
	 */
	public function isValidURL($url)
	{

		if(!$this->isUrlSafe($url))
		{
			return false;
		}

		ini_set('default_socket_timeout', 1);

		// The probe must not follow redirects: nothing would revalidate the
		// target it lands on. A 302 already counts as reachable below, so
		// reporting on the URL as handed in leaves the answer unchanged.
		//
		// The default stream context is the wrong place to carry that. Once a
		// stream has read through it, PHP 5.6 no longer lets
		// stream_context_set_default() reach the copy later reads take, so the
		// restore is silently lost and `follow_location => 0` stays behind for
		// the rest of the request. master needs PHP 8, so it cannot reach that
		// today, but the code is shared with release/v2.3.x and the branch's
		// 5.6 floor is a stated goal. fopen() has taken a context argument on
		// every supported version, so the option travels with this one request
		// and nothing global is touched.
		$context = stream_context_create(array('http' => array(
			'follow_location' => 0,
			'max_redirects'   => 1,
			// Without this a 3xx or 4xx is an fopen() failure and the status
			// line, which is the whole answer, never arrives.
			'ignore_errors'   => true,
		)));

		$headers = array();
		$stream  = @fopen($url, 'r', false, $context);

		if($stream !== false)
		{
			// The HTTP wrapper declares this in the scope fopen() ran in.
			$headers = isset($http_response_header) ? $http_response_header : array();
			fclose($stream);
		}

		if(empty($headers[0]))
		{
			return false;
		}

		return (stripos($headers[0], "200 OK") || strpos($headers[0], "302"));
	}


	/**
	 * Unzip Plugin or Theme zip file and move to plugin or theme folder.
	 *
	 * @param string $localfile - filename located in e_TEMP
	 * @param string $type      - addon type, either 'plugin' or 'theme', (possibly 'language' in future).
	 * @param bool   $overwrite
	 * @return string unzipped folder name on success or false.
	 */
	public function unzipArchive($localfile, $type, $overwrite = false)
	{

		$mes = e107::getMessage();

		chmod(e_TEMP . $localfile, 0755);

		$fileinfo = array();

		$dir = false;

		if(class_exists('ZipArchive')) // PHP7 compat. method.
		{
			$zip = new ZipArchive;

			if($zip->open(e_TEMP . $localfile) === true)
			{
				for($i = 0; $i < $zip->numFiles; $i++)
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
				chmod(e_TEMP . $dir, 0755);

				if(empty($dir) && deftrue('e_DEBUG'))
				{
					print_a($fileinfo);
				}


				$zip->close();
			}


		}
	/*	else // Legacy Method.
		{
			require_once(e_HANDLER . "pclzip.lib.php");

			$archive = new PclZip(e_TEMP . $localfile);
			$unarc = ($fileList = $archive->extract(PCLZIP_OPT_PATH, e_TEMP, PCLZIP_OPT_SET_CHMOD, 0755)); // Store in TEMP first.
			$dir = $this->getRootFolder($unarc);
		}*/


		$destpath = ($type == 'theme') ? e_THEME : e_PLUGIN;
		//	$typeDiz 	= ucfirst($type);

		@copy(e_TEMP . $localfile, e_BACKUP . $dir . ".zip"); // Make a Backup in the system folder.

		if($dir && is_dir($destpath . $dir))
		{
			if($overwrite === true)
			{
				if(file_exists(e_TEMP . $localfile))
				{
					$time = date("YmdHi");
					if(rename($destpath . $dir, e_BACKUP . $dir . "_" . $time))
					{
						$mes->addSuccess(ADLAN_195);
					}
				}
			}
			else
			{

				$mes->addError("(" . ucfirst($type) . ") Already Downloaded - " . basename($destpath) . '/' . $dir);

				if(file_exists(e_TEMP . $localfile))
				{
					@unlink(e_TEMP . $localfile);
				}

				$this->removeDir(e_TEMP . $dir);

				return false;
			}
		}

		if(empty($dir))
		{
			$mes->addError("Couldn't detect the root folder in the zip."); //  flush();
			@unlink(e_TEMP . $localfile);

			return false;
		}

		if(is_dir(e_TEMP . $dir))
		{
			$res = rename(e_TEMP . $dir, $destpath . $dir);
			if($res === false)
			{
				$mes->addError("Couldn't Move " . e_TEMP . $dir . " to " . $destpath . $dir . " Folder"); //  flush(); usleep(50000);
				@unlink(e_TEMP . $localfile);

				return false;
			}


			//	$dir 		= basename($unarc[0]['filename']);
			//	$plugPath	= preg_replace("/[^a-z0-9-\._]/", "-", strtolower($dir));
			//$status = "Done"; // ADMIN_TRUE_ICON;
			@unlink(e_TEMP . $localfile);

			return $dir;
		}

		return false;
	}


	/**
	 * @param string|boolean $file_mask - comma-separated list of allowed file types
	 * @param string         $filename  - optional override file name - defaults ignored
	 *
	 * @return array of filetypes
	 * @deprecated Use getAllowedFileTypes()
	 *             Get an array of permitted filetypes according to a set hierarchy.
	 *             If a specific file name given, that's used. Otherwise the default hierarchy is used
	 *
	 */
	function getFiletypeLimits($file_mask = false, $filename = '') // Wrapper only for now.
	{

		require_once(e_HANDLER . "upload_handler.php");
		$limits = get_filetypes($file_mask, $filename);
		ksort($limits);

		return $limits;
	}


	/**
	 * Download and extract a zipped copy of e107
	 *
	 * @param string $url              "core" to download the e107 core from Git master or
	 *                                 a custom download URL
	 * @param string $destination_path The e107 root where the downloaded archive should be extracted,
	 *                                 with a directory separator at the end
	 * @return array|bool FALSE on failure;
	 *                                 An array of successful and failed path extractions
	 */
	public function unzipGithubArchive($url = 'core', $destination_path = e_BASE)
	{

		switch($url)
		{
			case "core":
				$localfile = 'e107-master.zip';
				$remotefile = 'https://codeload.github.com/e107inc/e107/zip/master';
				$excludes = array(
					'e107-master/.codeclimate.yml',
					'e107-master/.editorconfig',
					'e107-master/.gitignore',
					'e107-master/.gitmodules',
					'e107-master/CONTRIBUTING.md', # moved to ./.github/CONTRIBUTING.md
					'e107-master/LICENSE',
					'e107-master/README.md',
					'e107-master/composer.json',
					'e107-master/composer.lock',
					'e107-master/install.php',
					'e107-master/favicon.ico',
				);
				$excludeMatch = array(
					'/.github/',
					'/e107_tests/',
				);
				break;

			// language.
			// eg. https://github.com/e107translations/Spanish/archive/v2.1.5.zip
			default:
				// 'e107-master.zip';
				$localfile = str_replace(array('https://github.com/e107translations/', '/archive/v'), array('', '-'), $url); //remove dirs.
				$remotefile = $url;
				$excludes = array();
				$excludeMatch = array('alt_auth', 'tagwords', 'faqs');

		}

		// Delete any existing file.
		if(file_exists(e_TEMP . $localfile))
		{
			unlink(e_TEMP . $localfile);
		}

		// One budget covers the whole transfer, and these archives run to tens of
		// megabytes, so the 40 second default asks for a sustained rate a home
		// connection does not always have. 120 is what outboundTimeout() allows.
		$result = $this->getRemoteFile($remotefile, $localfile, 'temp', 120);

		if($result === false)
		{
			return false;
		}


		chmod(e_TEMP . $localfile, 0755);
		require_once(e_HANDLER . "pclzip.lib.php");

		$zipBase = str_replace('.zip', '', $localfile); // eg. e107-master
		$excludes[] = $zipBase;

		$newFolders = array(
			$zipBase . '/e107_admin/'     => $destination_path . e107::getFolder('ADMIN'),
			$zipBase . '/e107_core/'      => $destination_path . e107::getFolder('CORE'),
			$zipBase . '/e107_docs/'      => $destination_path . e107::getFolder('DOCS'),
			$zipBase . '/e107_handlers/'  => $destination_path . e107::getFolder('HANDLERS'),
			$zipBase . '/e107_images/'    => $destination_path . e107::getFolder('IMAGES'),
			$zipBase . '/e107_languages/' => $destination_path . e107::getFolder('LANGUAGES'),
			$zipBase . '/e107_media/'     => $destination_path . e107::getFolder('MEDIA'),
			$zipBase . '/e107_plugins/'   => $destination_path . e107::getFolder('PLUGINS'),
			$zipBase . '/e107_system/'    => $destination_path . e107::getFolder('SYSTEM'),
			$zipBase . '/e107_themes/'    => $destination_path . e107::getFolder('THEMES'),
			$zipBase . '/e107_web/'       => $destination_path . e107::getFolder('WEB'),
			$zipBase . '/'                => $destination_path
		);

		$srch = array_keys($newFolders);
		$repl = array_values($newFolders);

		$archive = new PclZip(e_TEMP . $localfile);
		$unarc = ($fileList = $archive->extract(PCLZIP_OPT_PATH, e_TEMP, PCLZIP_OPT_SET_CHMOD, 0755)); // Store in TEMP first.

		$error = array();
		$success = array();
		$skipped = array();


		foreach($unarc as $k => $v)
		{
			if(
				$this->matchFound($v['stored_filename'], $excludeMatch) ||
				in_array($v['stored_filename'], $excludes)
			)
			{
				$skipped[] = $v['stored_filename'];
				continue;
			}

			$oldPath = $v['filename'];
			$newPath = str_replace($srch, $repl, $v['stored_filename']);

			if($v['folder'] == 1 && is_dir($newPath))
			{
				// $skipped[] =  $newPath. " (already exists)";
				continue;
			}
			@mkdir(dirname($newPath), 0755, true);
			if(!rename($oldPath, $newPath))
			{
				$error[] = $newPath;
			}
			else
			{
				$success[] = $newPath;
			}

		}

		return array('success' => $success, 'error' => $error, 'skipped' => $skipped);
	}


	/**
	 * @param $file
	 * @param $array
	 * @return bool
	 */
	private function matchFound($file, $array)
	{

		if(empty($array))
		{
			return false;
		}

		foreach($array as $term)
		{
			if(strpos($file, $term) !== false)
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
	 * @param int    $options
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
	 * @param int    $mode
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
	 * @param int    $mode
	 *   Mode is used.
	 * @param bool   $recursive
	 *   Default to FALSE.
	 * @param null   $context
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
	 *
	 * @param string         $filename          is the full path+name to the uploaded file on the server
	 * @param string         $target_name       is the intended name of the file once transferred
	 * @param array          $allowed_filetypes is an array of permitted file extensions, in lower case, no leading '.'
	 *                                          (usually generated from filetypes.xml/filetypes.php)
	 * @param boolean|string $unknown           - handling of file types unknown to us/define additional types
	 *                                          if FALSE, rejects totally unknown file extensions (even if in $allowed_filetypes).
	 *                                          if $unknown is TRUE, accepts totally unknown file extensions.
	 *                                          otherwise $unknown is a comma-separated list of additional permitted file extensions
	 * @return boolean - TRUE if file acceptable, FALSE if unacceptable. Use getErrorCode() immediately after to retrieve error code:
	 *                                          1 - file type not allowed
	 *                                          2 - can't read file contents
	 *                                          3 - illegal file contents (usually '<?php')
	 *                                          4 - not an image file
	 *                                          5 - bad image parameters - REMOVED
	 *                                          6 - not in supplementary list
	 *                                          7 - suspicious file contents
	 *                                          8 - unknown file type
	 *                                          9 - unacceptable file type (prone to exploits)
	 */
	function isClean($filename, $target_name = '', $allowed_filetypes = array(), $unknown = false)
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

		if(!in_array($file_ext, $archives) && stripos($tstr, '<?php') !== false)
		{
			$this->setErrorNum(3); // Pretty certain exploit

			return false;
		}

		if(!in_array($file_ext, $archives) && strpos($tstr, '<?') !== false)                // Bit more tricky - can sometimes be OK
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
			case 'webp':

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
			case 'webm':
			case 'ppt':
			case 'pptx':

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
	 * Check filename, path or URL against filetypes.xml
	 *
	 * @param        $file - real path to file.
	 * @param string $targetFile
	 * @return boolean
	 */
	public function isAllowedType($file, $targetFile = '')
	{

		if(empty($targetFile))
		{
			$targetFile = $file;
		}

		$remote = false;

		if(strpos($targetFile, 'http') === 0) // remote file.
		{
			$tmp = parse_url($targetFile);
			$targetFile = $tmp['path'];
			$remote = true;
			if(!empty($tmp['host']) && ($tmp['host'] === 'localhost' || $tmp['host'] === '127.0.0.1'))
			{
				return false;
			}
		}

		$ext = pathinfo($targetFile, PATHINFO_EXTENSION);

		$types = $this->getAllowedFileTypes();

		if(isset($types[$ext]))
		{
			if($remote)
			{
				return true;
			}

			$maxSize = $types[$ext] * 1024;
			$fileSize = filesize($file);

			//	echo "\nisAllowedType(".basename($file).") ".$fileSize ." / ".$maxSize;

			if($fileSize <= $maxSize)
			{
				return true;
			}

		}

		return false;

	}

	/**
	 * @return string[]
	 */
	private function getMimeTypes()
	{

		return array(
			'asc'  => 'text/plain',
			'css'  => 'text/css',
			'csv'  => 'text/csv',
			'etx'  => 'text/x-setext',
			'htm'  => 'text/html',
			'html' => 'text/html',
			'ics'  => 'text/calendar',
			'ini'  => 'text/plain',
			'log'  => 'text/plain',
			'php'  => 'text/html',
			'sgm'  => 'text/sgml',
			'sgml' => 'text/sgml',
			'txt'  => 'text/plain',
			'yaml' => 'text/yaml',
			'yml'  => 'text/yaml',


			// images
			'bmp'  => 'image/bmp',
			'gif'  => 'image/gif',
			'ico'  => 'image/vnd.microsoft.icon',
			'jpe'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'pbm'  => 'image/x-portable-bitmap',
			'pgm'  => 'image/x-portable-graymap',
			'png'  => 'image/png',
			'pnm'  => 'image/x-portable-anymap',
			'ppm'  => 'image/x-portable-pixmap',
			'ras'  => 'image/x-cmu-raster',
			'svg'  => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			'tif'  => 'image/tiff',
			'tiff' => 'image/tiff',
			'webp' => 'image/webp',
			'xbm'  => 'image/x-xbitmap',
			'xpm'  => 'image/x-xpixmap',
			'xwd'  => 'image/x-xwindowdump',

			// archives
			'7z'   => 'application/x-7z-compressed',
			'cab'  => 'application/vnd.ms-cab-compressed',
			'exe'  => 'application/x-msdownload',
			'gz'   => 'application/gzip',
			'msi'  => 'application/x-msdownload',
			'rar'  => 'application/x-rar-compressed',
			'zip'  => 'application/zip',


			// video

			'3gp'     => 'video/3gpp',
			'asf'     => 'video/x-ms-asf',
			'avi'     => 'video/x-msvideo',
			'flv'     => 'video/x-flv',
			'm4v'     => 'video/mp4',
			'mkv'     => 'video/x-matroska',
			'mov'     => 'video/quicktime',
			'mp4'     => 'video/mp4',
			'mp4v'    => 'video/mp4',
			'mpe'     => 'video/mpeg',
			'mpeg'    => 'video/mpeg',
			'mpg'     => 'video/mpeg',
			'mpg4'    => 'video/mp4',
			'ogv'     => 'video/ogg',
			'qt'      => 'video/quicktime',
			'webm'    => 'video/webm',
			'wmv'     => 'video/x-ms-wmv',

			// audio
			'aac'     => 'audio/x-aac',
			'aif'     => 'audio/x-aiff',
			'flac'    => 'audio/flac',
			'm4a'     => 'audio/mp4',
			'mid'     => 'audio/midi',
			'midi'    => 'audio/midi',
			'mp3'     => 'audio/mpeg',
			'mp4a'    => 'audio/mp4',
			'oga'     => 'audio/ogg',
			'ogg'     => 'audio/ogg',
			'wav'     => 'audio/x-wav',
			'wma'     => 'audio/x-ms-wma',

			// adobe
			'ai'      => 'application/postscript',
			'eps'     => 'application/postscript',
			'pdf'     => 'application/pdf',
			'ps'      => 'application/postscript',
			'psd'     => 'image/vnd.adobe.photoshop',

			// ms office
			'doc'     => 'application/msword',
			'docx'    => 'application/msword',
			'ppt'     => 'application/vnd.ms-powerpoint',
			'pptx'    => 'application/vnd.ms-powerpoint',
			'rtf'     => 'application/rtf',
			'xls'     => 'application/vnd.ms-excel',
			'xlsx'    => 'application/vnd.ms-excel',

			// open office
			'odt'     => 'application/vnd.oasis.opendocument.text',
			'ods'     => 'application/vnd.oasis.opendocument.spreadsheet',

			// other
			'atom'    => 'application/atom+xml',
			'bz2'     => 'application/x-bzip2',
			'cer'     => 'application/pkix-cert',
			'crl'     => 'application/pkix-crl',
			'crt'     => 'application/x-x509-ca-cert',
			'cu'      => 'application/cu-seeme',
			'deb'     => 'application/x-debian-package',
			'dvi'     => 'application/x-dvi',
			'eot'     => 'application/vnd.ms-fontobject',
			'epub'    => 'application/epub+zip',
			'iso'     => 'application/x-iso9660-image',
			'jar'     => 'application/java-archive',
			'js'      => 'application/javascript',
			'json'    => 'application/json',
			'latex'   => 'application/x-latex',
			'ogx'     => 'application/ogg',
			'rss'     => 'application/rss+xml',
			'swf'     => 'application/x-shockwave-flash',
			'tar'     => 'application/x-tar',
			'torrent' => 'application/x-bittorrent',
			'ttf'     => 'application/x-font-ttf',
			'woff'    => 'application/x-font-woff',
			'wsdl'    => 'application/wsdl+xml',
			'xml'     => 'application/xml',

		);

	}


	/**
	 * Return the mime-type based on the file's extension.
	 *
	 * @param string $filename
	 * @return string
	 */
	public function getMime($filename)
	{

		$filename = basename($filename);

		$tmp = explode('.', $filename);

		if(count($tmp) < 2) // no extension.
		{
			return false;
		}

		$ext = strtolower(end($tmp));

		$types = $this->getMimeTypes();

		if(isset($types[$ext]))
		{
			return $types[$ext];
		}
		else
		{
			return 'application/octet-stream';
		}


	}


	/**
	 * New in v2.1.9
	 * Get image (string) mime type
	 * or when extended - array [(string) mime-type, (array) associated extensions)].
	 * A much faster way to retrieve mimes than getimagesize()
	 *
	 * @param            $filename
	 * @param bool|false $extended
	 * @return array|string
	 */
	function getImageMime($filename, $extended = false)
	{

		// mime types as returned from image_type_to_mime_type()
		// and associated file extensions
		$imageExtensions = array(
			'image/gif'                     => array('gif'),
			'image/jpeg'                    => array('jpg'),
			'image/png'                     => array('png'),
			'application/x-shockwave-flash' => array('swf', 'swc'),
			'image/psd'                     => array('psd'),
			'image/bmp'                     => array('bmp'),
			'image/tiff'                    => array('tiff'),
			'application/octet-stream'      => array('jpc', 'jpx', 'jb2'),
			'image/jp2'                     => array('jp2'),
			'image/iff'                     => array('iff'),
			'image/vnd.wap.wbmp'            => array('wbmp'),
			'image/xbm'                     => array('xbm'),
			'image/vnd.microsoft.icon'      => array('ico'),
			'image/webp'                    => array('webp'),
		);

		$ret = image_type_to_mime_type(exif_imagetype($filename));

		if($extended)
		{
			return array(
				$ret,
				$ret && isset($imageExtensions[$ret]) ? $imageExtensions[$ret] : array()
			);
		}

		return $ret;

	}


	/**
	 *    New in v2.1.9
	 *  Get array of file types (file extensions) which are permitted - reads an XML-formatted definition file.
	 *    (Similar to @See{get_allowed_filetypes()}, but expects an XML file)
	 *
	 * @param string $class - e_UC_MEMBER etc if a specific class of file-types is required. Otherwise, it defaults to the perms of the current user.
	 * @return array - where key is the file type (extension); value is max upload size
	 */
	public function getAllowedFileTypes($class = null)
	{

		$ret = array();
		$file_array = array();

		/*		if($file_mask)
				{
					$file_array = explode(',', $file_mask);
					foreach($file_array as $k => $f)
					{
						$file_array[$k] = trim($f);
					}
				}*/

		if(!is_readable(e_SYSTEM . "filetypes.xml"))
		{
			return array();
		}

		$xml = e107::getXml();
		$xml->setOptArrayTags('class'); // class tag should be always array
		$temp_vars = $xml->loadXMLfile(e_SYSTEM . "filetypes.xml", 'filetypes');

		if($temp_vars === false)
		{
			echo "Error reading filetypes.xml<br />";

			return $ret;
		}

		foreach($temp_vars['class'] as $v1)
		{
			$v = $v1['@attributes'];

			if(!is_numeric($v['name']))
			{
				$v['name'] = e107::getUserClass()->getClassFromKey($v['name'], $v['name']); // convert 'admin' etc to numeric equivalent.
			}

			if(($class === null && check_class($v['name'])) || (int) $class === (int) $v['name'])
			{
			//	$current_perms[$v['name']] = array('type' => $v['type'], 'maxupload' => $v['maxupload']);
				$a_filetypes = explode(',', $v['type']);
				foreach($a_filetypes as $ftype)
				{
					$ftype = strtolower(trim(str_replace('.', '', $ftype))); // File extension

					//	if(!$file_mask || in_array($ftype, $file_array)) // We can load this extension
					{
						if(isset($ret[$ftype]))
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
