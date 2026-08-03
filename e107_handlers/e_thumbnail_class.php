<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */



use Intervention\Image\ImageManagerStatic as Image;

/**
 *
 */
class e_thumbnail
{
	/**
	 * Largest thumbnail this class will produce, in either direction. A request
	 * for more is clamped rather than refused, because the caller is usually a
	 * stored URL and a page of broken images is a worse answer than a bounded
	 * one.
	 */
	const MAX_DIMENSION = 4000;

	/**
	 * Encodings the caller may ask for through type=. The value reaches
	 * e_parse::thumbCacheFile() and becomes part of a filename on disk, so an
	 * unlisted one is dropped rather than passed on.
	 *
	 * @var array
	 */
	protected static $_types = array('gif', 'jpg', 'jpeg', 'png', 'webp');

	private $_debug = false;

	private $_cache = true;

	/**
	 * Page request
	 * @var array
	 */
	protected $_request = array();

	/**
	 * @var string image source path (e107 path shortcode)
	 */
	protected $_src = null;

	/**
	 * @var string source path modified/sanitized
	 */
	protected $_src_path = null;

	/**
	 * The source path as the request spelled it, before realpath() resolved it.
	 * The cache filename is derived from this rather than from _src_path so it
	 * matches the one e_parse::thumbUrl() computes from the URL.
	 *
	 * @var string
	 */
	protected $_src_cache = null;


	/** Stores watermark prefs
	 */
	protected $_watermark = array('activate'=>null);

	protected $_placeholder = false;

	protected $_thumbQuality = 65;

	protected $_upsize = true;

	protected $_forceWebP = false;

	/**
	 * Constructor - init paths
	 *
	 *
	 * @return void
	 */
	public function __construct()
	{

	}

	/**
	 * Initialize the class with e107 core prefs.
	 *
	 * @param array      $pref
	 * @param array|null $request keys: src, w, h, aw, ah, c(rop), type. Null reads the query string.
	 * @return null
	 */
	public function init($pref, $request = null)
	{
		if($request === null)
		{
			$this->parseRequest();
		}
		else
		{
			$this->setRequest($request);
		}

		if(!empty($this->_request['noinit']))
		{
			return null;
		}

		$this->_watermark = array(
			'activate'		=> vartrue($pref['watermark_activate'], false),
			'text'			=> vartrue($pref['watermark_text']),
			'size'			=> vartrue($pref['watermark_size'], 20),
			'pos'			=> vartrue($pref['watermark_pos'],"BR"),
			'color'			=> vartrue($pref['watermark_color'],'fff'),
			'font'			=> vartrue($pref['watermark_font']),
			'margin'		=> vartrue($pref['watermark_margin'],30),
			'shadowcolor'	=> vartrue($pref['watermark_shadowcolor'], '000000'),
			'opacity'		=> vartrue($pref['watermark_opacity'], 20)
		);

		$this->_thumbQuality = vartrue($pref['thumbnail_quality'],65);

		$this->_upsize = ((isset($this->_request['w']) && $this->_request['w'] > 110) || (isset($this->_request['aw']) && ($this->_request['aw'] > 110))); // don't resizeUp the icon images.

		$this->_forceWebP = empty($this->_request['type']) && !empty($pref['thumb_to_webp']) && (strpos( varset($_SERVER['HTTP_ACCEPT'], ''), 'image/webp' ) !== false) ? true : false;
	//	var_dump($this);
	//	exit;
		return null;
	}

	/**
	 * @param array $array keys: activate, text, size, pos, color, font, margin, shadowcolor, opacity. @see above.
	 */
	public function setWatermark($array)
	{
		$this->_watermark = (array) $array;

	}

	/**
	 * Enabled/Disable debugging/testing.
	 * @param $val
	 */
	public function setDebug($val)
	{
		$this->_debug = (bool) $val;
	}


	/**
	 * Enable/disable image caching.
	 * @param bool $val
	 */
	public function setCache($val)
	{
		$this->_cache = (bool) $val;
	}

	/**
	 * @return $this
	 */
	private function parseRequest()
	{

		$e_QUERY = !empty($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : e_QUERY;

		if(isset($_GET['id'])) // path cloaking
		{
			$e_QUERY = base64_decode($_GET['id']);
		}

		$e_QUERY= str_replace('e_AVATAR', 'e_AVATAR/', $e_QUERY); // FIXME  Quick and dirty fix.

		parse_str(str_replace('&amp;', '&', $e_QUERY), $this->_request);

		$this->sanitizeRequest();

		return $this;
	}

	/**
	 * Manually Set the Request parameters
	 * @param array $array keys: w, h, ah, aw, c(rop)
	 */
	public function setRequest($array)
	{
		$this->_request = (array) $array;

		$this->sanitizeRequest();
	}

	/**
	 * Bound the dimensions and the encoding, whatever the request asked for.
	 *
	 * w, h, aw and ah reach GD from an unauthenticated caller, so an unbounded
	 * value is an allocation of that caller's choosing.
	 *
	 * @return void
	 */
	private function sanitizeRequest()
	{
		$dimensions = array('w', 'h', 'aw', 'ah');

		foreach($dimensions as $key)
		{
			if(isset($this->_request[$key]))
			{
				$this->_request[$key] = max(0, min((int) $this->_request[$key], self::MAX_DIMENSION));
			}
		}

		if(isset($this->_request['type']))
		{
			$type = strtolower((string) $this->_request['type']);

			if(in_array($type, self::$_types, true))
			{
				$this->_request['type'] = $type;
			}
			else
			{
				unset($this->_request['type']);
			}
		}
	}

	/**
	 * @return array|string|string[]
	 */
	private function getImageInfo()
	{
		$thumbnfo = pathinfo($this->_src_path);

		if($this->_forceWebP === true || (!empty($this->_request['type']) && $this->_request['type'] == 'webp'))
		{
			$thumbnfo['extension'] = 'webp';
		}

		return $thumbnfo;
	}

	/**
	 * Validate and Sanitize the Request.
	 * @return bool true when request is okay.
	 */
	public function checkSrc()
	{
		if(!vartrue($this->_request['src'])) // display placeholder when src is missing.
		{
			$this->_placeholder = true;
			return true;
		}

		$tp = e107::getParser();

		// convert raw to SC path
		$this->_request['src'] = str_replace($tp->getUrlConstants('raw'), $tp->getUrlConstants(), $this->_request['src']);

		// convert absolute and full url to SC URL
		$this->_src = $tp->createConstants($this->_request['src'], 'mix');

		if(preg_match('#^[a-z][a-z0-9+.-]*://#i', $this->_request['src']))
		{
			return false;
		}

		if(!is_writable(e_CACHE_IMAGE))
		{
			error_log('e_thumbnail: cache folder not writeable: '.e_CACHE_IMAGE);
			return false;
		}

		// convert to relative server path
		$path = $tp->replaceConstants(str_replace('..', '', $this->_src));

		$resolved = $this->containedPath($path);

		if($resolved === false)
		{
			return false;
		}

		if($resolved !== '' && is_file($resolved) && is_readable($resolved))
		{
			$this->_src_path = $resolved;
			$this->_src_cache = $path;
			return true;
		}

		if($this->_debug === true || defined("e_DEBUG_THUMBNAIL"))
		{
			echo "File Not Found: ".$path;
		}

		$this->_placeholder = true;
		return true;

	}

	/**
	 * The directories this endpoint will read from.
	 *
	 * Deliberately narrower than e_file::getSendRoots(), which is the set the
	 * download handler serves from and which includes e_SYSTEM.
	 *
	 * @return array
	 */
	protected function thumbRoots()
	{
		return array(
			e_MEDIA,
			e_AVATAR,
			e_IMAGE,
			e_THEME,
			e_PLUGIN,
			e_WEB,
			// Where a v1.x site kept the images that its stored [img] bbcode and
			// its download entries still point at. Named one subdirectory at a
			// time: e_FILE itself also holds e107_files/downloads/, which is the
			// download plugin's userclass-gated storage on an old install.
			e_FILE.'public/',
			e_FILE.'downloadimages/',
			e_FILE.'downloadthumbs/',
		);
	}

	/**
	 * Subtrees inside those roots that hold per-recipient files.
	 *
	 * pm_class::send_file() releases a private message attachment only to a
	 * party to the message. A thumbnailer has no session to ask that question
	 * of, so it does not serve them at all.
	 *
	 * Both of the directories send_file() reads from, not just the current one:
	 * an install upgraded from a release that stored attachments beside the
	 * plugin still holds them there, and pm_class deletes from that path to
	 * this day.
	 *
	 * @see pm_class::send_file()
	 * @return array
	 */
	protected function privateRoots()
	{
		return array(e_MEDIA.'plugins/pm/', e_PLUGIN.'pm/attachments/');
	}

	/**
	 * @param string $path canonical path, of a file or of a directory
	 * @return bool
	 */
	private function isPrivate($path)
	{
		$roots = array();

		foreach($this->privateRoots() as $root)
		{
			if(is_dir($root))
			{
				$roots[] = $root;
			}
		}

		if(empty($roots))
		{
			return false;
		}

		$file = e107::getFile();

		return $file->resolveSendPath($path, $roots) !== false
			|| $file->resolveSendRoot($path, $roots) !== false;
	}

	/**
	 * Canonicalise $path and decide what may be done with it.
	 *
	 * Returns the canonical path when a permitted root holds the file, '' when
	 * the file is absent from a directory a permitted root holds, and false
	 * otherwise. The empty string is the placeholder: an image that has been
	 * deleted is not an attack, and refusing it would break every page that
	 * still links to one.
	 *
	 * @param string $path path with {e_XXX} constants already expanded
	 * @return string|false
	 */
	private function containedPath($path)
	{
		$file = e107::getFile();
		$roots = $this->thumbRoots();

		$resolved = $file->resolveSendPath($path, $roots);

		if($resolved !== false)
		{
			return $this->isPrivate($resolved) ? false : $resolved;
		}

		$dir = $path;

		for($depth = 0; $depth < 64; $depth++)
		{
			$parent = dirname($dir);

			if($parent === $dir)
			{
				break;
			}

			$dir = $parent;

			if(!is_dir($dir))
			{
				continue;
			}

			$resolved = $file->resolveSendRoot($dir, $roots);

			return ($resolved === false || $this->isPrivate($resolved)) ? false : '';
		}

		return false;
	}

	/**
	 * @return $this|false|string|void
	 */
	public function sendImage()
	{
		if($this->_placeholder == true)
		{
			$width = vartrue($this->_request['aw']) ? $this->_request['aw'] : varset($this->_request['w'], 0);
			$height = vartrue($this->_request['ah']) ? $this->_request['ah'] : varset($this->_request['h'], 0);

			$parm = array('size' => $width."x".$height);

			$this->placeholder($parm);
			return false;
		}

		if(!$this->_src_path) // would only happen during testing.
		{
			echo "no source";
			return $this;
		}

		$thumbnfo = $this->getImageInfo();
		$options = $this->getRequestOptions();

		$fname = e107::getParser()->thumbCacheFile($this->_src_cache, $options);
		$cache_filename = e_CACHE_IMAGE . $fname;

		$this->sendCachedImage($cache_filename, $thumbnfo);

		// No Cached file found - proceed with image creation.

		$img = Image::make($this->_src_path);


		if(!empty($options['c'])) // Cropping:  $quadrant T(op), B(ottom), C(enter), L(eft), R(right)
		{
			if(!empty($this->_request['ah']))
			{
				$this->_request['h'] = $this->_request['ah'];
			}

			if(!empty($this->_request['aw']))
			{
				$this->_request['w'] = $this->_request['aw'];
			}

			$key = $options['c'];
			$fit = array(
				'T' => 'top',
				'C' => 'center',
				'B' => 'bottom',
				'L' => 'left',
				'R' => 'right'
			);

			$position = varset($fit[$key],'top');

			$img->fit(vartrue($this->_request['w'], null), vartrue($this->_request['h'], null), null, $position);
		}
		elseif(!empty($this->_request['w']) || !empty($this->_request['h']))
		{
			$img->resize(vartrue($this->_request['w'], null), vartrue($this->_request['h'], null), function ($constraint)
			{
		        $constraint->aspectRatio();

		        if(!$this->_upsize)
				{
		            $constraint->upsize();
				}

			});
		}
		elseif(!empty($this->_request['ah']) || !empty($this->_request['aw']))
		{
			$img->fit(vartrue($this->_request['aw'], null), vartrue($this->_request['ah'], null), null, 'top');
		}

		/*
			todo watermark support. @see http://image.intervention.io/api/text
			todo also @see: http://image.intervention.io/api/insert
		*/


		$img->save($cache_filename, $this->_thumbQuality, $thumbnfo['extension']);

		$this->_request = array(); // reset the request.

		if($this->_debug === true) // return the cache file path for testing.
		{
			return $cache_filename;
		}


		$imageData = $img->encode($thumbnfo['extension'], $this->_thumbQuality);
		$thumbnfo['fsize'] = strlen($imageData);

		$this->sendHeaders($thumbnfo);
		echo $imageData;

		exit;
	}




	/**
	 * When caching is enabled, send the cached image with headers and then exit the script.
	 * @param string $cache_filename
	 * @param array $thumbnfo
	 * @return null
	 */
	private function sendCachedImage($cache_filename, $thumbnfo)
	{
		if(!$this->_cache || !is_file($cache_filename) || !is_readable($cache_filename) || $this->_debug)
		{
			return null;
		}

		$thumbnfo['lmodified'] = filemtime($cache_filename);
		$thumbnfo['md5s'] = md5_file($cache_filename);
		$thumbnfo['fsize'] = filesize($cache_filename);

			// Send required headers
		if($this->_debug !== true)
		{
			$this->sendHeaders($thumbnfo);
		}

		// check browser cache
		if (@$_SERVER['HTTP_IF_MODIFIED_SINCE'] && ($thumbnfo['lmodified'] <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) && (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $thumbnfo['md5s']))
		{
			header('HTTP/1.1 304 Not Modified');
			//$bench->end()->logResult('thumb.php', $_GET['src'].' - 304 not modified');
			exit;
		}

		eShims::readfile($cache_filename);

		exit;

	}

	/**
	 * @return array
	 */
	private function getRequestOptions()
	{
		$ret = array();
		$ret['w'] = isset($this->_request['w']) ? intval($this->_request['w']) : false;
		$ret['h'] = isset($this->_request['h']) ? intval($this->_request['h']) : $ret['w'];
		$ret['aw'] = isset($this->_request['aw']) ? intval($this->_request['aw']) : false;
		$ret['ah'] = isset($this->_request['ah']) ? intval($this->_request['ah']) : $ret['aw'];
		$ret['c'] = isset($this->_request['c']) ? strtoupper(substr(e107::getParser()->filter($this->_request['c'], 'str'),0,1)) : false;
	//	$ret['wm'] = isset($this->_request['wm']) ? intval($this->_request['wm']) : $ret['wm'];

		if($ret['c'] == 'A') // auto
		{
			$ret['c'] = 'T'; // default is 'Top';
		}

		if(!empty($this->_request['type']))
		{
			$ret['type'] = $this->_request['type'];
		}
		elseif($this->_forceWebP)
		{
			$ret['type'] = 'webp'; 
		}

		return $ret;
	}

	/**
	 * @param $thumbnfo
	 * @return void
	 */
	private function sendHeaders($thumbnfo)
	{

		if(headers_sent($filename, $linenum))
		{
			error_log('e_thumbnail: headers already sent in '.$filename.' on line '.$linenum);
			exit;
		}

		if (function_exists('date_default_timezone_set'))
		{
		    date_default_timezone_set('UTC');
		}

		header('Cache-Control: must-revalidate');

		if(isset($thumbnfo['lmodified']))
		{
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $thumbnfo['lmodified']).' GMT');
		}

		if(isset($thumbnfo['fsize'])) // extra check, if empty image will fail.
		{
			header('Content-Length: '.$thumbnfo['fsize']);
		}

		header('Content-Disposition: filename='.$thumbnfo['basename']); // important for right-click save-as.

		$ctype = $this->ctype($thumbnfo['extension']);
		if(null !== $ctype)
		{
			header('Content-Type: '.$ctype);
		}

		header('X-Content-Type-Options: nosniff');

		// Expire header - 1 year
		$time = time() + 365 * 86400;
		header('Expires: '.gmdate("D, d M Y H:i:s", $time).' GMT');

		if(isset($thumbnfo['md5s']))
		{
			header("Etag: ".$thumbnfo['md5s']);
		}

	}


	/**
	 * @param $ftype
	 * @return mixed|string|null
	 */
	private function ctype($ftype)
	{
		static $known_types = array(
			'gif'  => 'image/gif',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'webp'  => 'image/webp',
			//'bmp'  => 'image/bmp',
		);

		$ftype = strtolower($ftype);
		if(isset($known_types[$ftype]))
		{
			return $known_types[$ftype];
		}
		return null;
	}


	// Display a placeholder image.

	/**
	 * @param array $parm expects 'size' as "{width}x{height}", eg. "800x350"
	 * @return void|null
	 */
	private function placeholder($parm)
	{
		if($this->_debug === true)
		{
			echo "Placeholder activated";
			return null;
		}

		$size = vartrue($parm['size'], '');
		list($width, $height) = array_pad(explode('x', $size, 2), 2, 0);

		$svg = $this->placeholderImage($width, $height);

		header('Content-Type: image/svg+xml');
		header('Content-Length: '.strlen($svg));
		header('Cache-Control: public, max-age=604800');
		header('X-Content-Type-Options: nosniff');
		header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'");
		echo $svg;
		exit();
	}


	/**
	 * Build an SVG placeholder for a missing image, generated locally so that
	 * no third-party service is involved. Dimensions are clamped to 1-4000;
	 * invalid input falls back to 100x100.
	 *
	 * @param int|string $width
	 * @param int|string $height
	 * @return string SVG markup
	 */
	public function placeholderImage($width, $height)
	{
		$width = (int) $width;
		$height = (int) $height;

		if($width < 1)
		{
			$width = 100;
		}

		if($height < 1)
		{
			$height = 100;
		}

		$width = min($width, 4000);
		$height = min($height, 4000);

		$fontSize = max(10, min(40, (int) (min($width, $height) / 5)));
		$label = $width.'x'.$height;

		return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewBox="0 0 '.$width.' '.$height.'">'
			.'<rect width="100%" height="100%" fill="#dde0e5"/>'
			.'<text x="50%" y="50%" dy=".35em" fill="#6e7681" font-family="sans-serif" font-size="'.$fontSize.'" text-anchor="middle">'.$label.'</text>'
			.'</svg>';
	}


}
