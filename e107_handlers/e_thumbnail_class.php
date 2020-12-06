<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2020 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */



class e_thumbnail
{
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


	/** Stores watermark prefs
	 */
	protected $_watermark = array('activate'=>null);

	protected $_placeholder = false;

	protected $_thumbQuality = 65;

	/**
	 * Constructor - init paths
	 *
	 *
	 * @return void
	 */
	public function __construct()
	{

	}

	public function init($pref)
	{
		$this->parseRequest();

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



	}

	/**
	 * @param array $array keys: activate, text, size, pos, color, font, margin, shadowcolor, opacity. @see above.
	 */
	public function setWatermark($array)
	{
		$this->_watermark = (array) $array;

	}

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

	private function parseRequest()
	{
		//echo 'e_query='.str_replace('&amp;', '&', e_QUERY);
		$e_QUERY = e_QUERY;

		if(isset($_GET['id'])) // very-basic url-tampering prevention and path cloaking
		{
			$e_QUERY = base64_decode($_GET['id']);
		}

		parse_str(str_replace('&amp;', '&', $e_QUERY), $this->_request);

	//	file_put_contents(e_LOG."thumbRequests.log", var_export($this->_request, true)."\n\n", FILE_APPEND);

		// parse_str($_SERVER['QUERY_STRING'], $this->_request);
		return $this;
	}

	public function setRequest($array)
	{
		$this->_request = (array) $array;
	}

	public function checkSrc()
	{
		if(!vartrue($this->_request['src'])) // display placeholder when src is missing.
		{
			$this->_placeholder = true;
			return true;
		}

		$tp = e107::getParser();

		// convert raw to SC path
		$this->_request['src'] = str_replace($tp->getUrlConstants('raw'), $tp->getUrlConstants('sc'), $this->_request['src']);

		// convert absolute and full url to SC URL
		$this->_src = $tp->createConstants($this->_request['src'], 'mix');

		if(preg_match('#^(https?|ftps?|file)://#i', $this->_request['src']))
		{
			return false;
		}

		if(!is_writeable(e_CACHE_IMAGE))
		{
			echo 'Cache folder not writeable! ';
			return false;
		}

		// convert to relative server path
		$path = $tp->replaceConstants(str_replace('..', '', $this->_src)); //should be safe enough

		if(is_file($path) && is_readable($path))
		{
			$this->_src_path = $path;
			return true;
		}

		if($this->_debug === true)
		{
			echo "File Not Found: ".$path;
		}

		$this->_placeholder = true;
		return true;

	}


	public function sendImage()
	{

		if($this->_placeholder == true)
		{
			$width = ($this->_request['aw']) ? $this->_request['aw'] : $this->_request['w'];
			$height = ($this->_request['ah']) ? $this->_request['ah'] : $this->_request['h'];

			$parm = array('size' => $width."x".$height);

			$this->placeholder($parm);
			return false;
		}



		if(!$this->_src_path)
		{
			echo "no source";
			return $this;
		}

		$thumbnfo = pathinfo($this->_src_path);
		$options = $this->getRequestOptions();

/*		if($this->_debug === true)
		{
			var_dump($options);
		//	return false;
		}*/

	//	$cache_str = md5(serialize($options). $this->_src_path. $this->_thumbQuality);
	//	$fname = strtolower('Thumb_'.$thumbnfo['filename'].'_'.$cache_str.'.'.$thumbnfo['extension']).'.cache.bin';

		$fname = e107::getParser()->thumbCacheFile($this->_src_path, $options);

		$cache_filename = e_CACHE_IMAGE . $fname;
		if(($this->_cache === true) && is_file($cache_filename) && is_readable($cache_filename) && ($this->_debug !== true))
		{
			$thumbnfo['lmodified'] = filemtime($cache_filename);
			$thumbnfo['md5s'] = md5_file($cache_filename);
			$thumbnfo['fsize'] = filesize($cache_filename);

			// Send required headers
			if($this->_debug !== true)
			{
				$this->sendHeaders($thumbnfo);
			}

				//$bench->end()->logResult('thumb.php', $_GET['src'].' - 304 not modified');
			// 	exit;
			// check browser cache
			if (@$_SERVER['HTTP_IF_MODIFIED_SINCE'] && ($thumbnfo['lmodified'] <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) && (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $thumbnfo['md5s']))
			{
				header('HTTP/1.1 304 Not Modified');
				//$bench->end()->logResult('thumb.php', $_GET['src'].' - 304 not modified');
				exit;
			}

			// Send required headers
			//$this->sendHeaders($thumbnfo);

			eShims::readfile($cache_filename);
			//$bench->end()->logResult('thumb.php', $_GET['src'].' - retrieve cache');

			exit;
		}


	//	if($this->_debug === true)
	//	{
			// $start = microtime(true);
	//	}


	//	@require(e_HANDLER.'phpthumb/ThumbLib.inc.php');

		if(!$thumb = e107::getThumb($this->_src_path))
		{
			if(getperms('0'))
			{
				echo "Couldn't load thumb factory";
			}
			return null;
		}

		$sizeUp = ((isset($this->_request['w']) && $this->_request['w'] > 110) || (isset($this->_request['aw']) && ($this->_request['aw'] > 110))); // don't resizeUp the icon images.
	   	$thumb->setOptions(array(
		   	    'correctPermissions'    => true,
		   	    'resizeUp'              => $sizeUp,
		   	    'jpegQuality'           => $this->_thumbQuality,
			    'interlace'             => true // improves performance
		    ));

	/*	catch (Exception $e)
		{
		     echo $e->getMessage();
		     return $this;
		}*/

		// Image Cropping by Quadrant.
		if(!empty($options['c'])) // $quadrant T(op), B(ottom), C(enter), L(eft), R(right)
		{
			if(!empty($this->_request['ah']))
			{
				$this->_request['h'] = $this->_request['ah'];
			}

			if(!empty($this->_request['aw']))
			{
				$this->_request['w'] = $this->_request['aw'];
			}



			$thumb->adaptiveResizeQuadrant((integer) vartrue($this->_request['w'], 0), (integer) vartrue($this->_request['h'], 0), $options['c']);
		}
		if(isset($this->_request['w']) || isset($this->_request['h']))
		{
			$thumb->resize((integer) vartrue($this->_request['w'], 0), (integer) vartrue($this->_request['h'], 0));
		}
		elseif(!empty($this->_request['ah']))
		{
			//Typically gives a better result with images of people than adaptiveResize().
			$thumb->adaptiveResizeQuadrant((integer) vartrue($this->_request['aw'], 0), (integer) vartrue($this->_request['ah'], 0), 'T');
		}
		else
		{
			$thumb->adaptiveResize((integer) vartrue($this->_request['aw'], 0), (integer) vartrue($this->_request['ah'], 0));
		}


/*		if($this->_debug === true)
		{
			// echo "time: ".round((microtime(true) - $start),4);

			var_dump($thumb);
			return false;
		}*/

		// Watermark Option - See admin->MediaManager->prefs for details.

		if(($this->_watermark['activate'] < $options['w']
		|| $this->_watermark['activate'] < $options['aw']
		|| $this->_watermark['activate'] < $options['h']
		|| $this->_watermark['activate'] < $options['ah']
		) && $this->_watermark['activate'] > 0 && $this->_watermark['font'] !='')
		{
			$tp = e107::getParser();
			$this->_watermark['font'] = $tp->createConstants($this->_watermark['font'], 'mix');
			$this->_watermark['font'] =  realpath($tp->replaceConstants($this->_watermark['font'],'rel'));

		//	$thumb->WatermarkText($this->_watermark); // failing due to phpThumb::
		}
		//	echo "hello";


		// set cache
		$thumb->save($cache_filename);

		$this->_request = array(); // reset the request.

		if($this->_debug === true) // return the cache file path for testing.
		{
			return $cache_filename;
		}

		// show thumb
		$thumb->show();

		return $this;
	}

	private function getRequestOptions()
	{
		$ret = array();
		$ret['w'] = isset($this->_request['w']) ? intval($this->_request['w']) : false;
		$ret['h'] = isset($this->_request['h']) ? intval($this->_request['h']) : $ret['w'];
		$ret['aw'] = isset($this->_request['aw']) ? intval($this->_request['aw']) : false;
		$ret['ah'] = isset($this->_request['ah']) ? intval($this->_request['ah']) : $ret['aw'];
		$ret['c'] = isset($this->_request['c']) ? strtoupper(substr(filter_var($this->_request['c'],FILTER_SANITIZE_STRING),0,1)) : false;
	//	$ret['wm'] = isset($this->_request['wm']) ? intval($this->_request['wm']) : $ret['wm'];

		if($ret['c'] == 'A') // auto
		{
			$ret['c'] = 'T'; // default is 'Top';
		}

		return $ret;
	}

	private function sendHeaders($thumbnfo)
	{
		if(headers_sent($filename, $linenum))
		{
			echo 'Headers already sent in '.$filename.' on line '.$linenum;
			exit;
		}

		if (function_exists('date_default_timezone_set'))
		{
		    date_default_timezone_set('UTC');
		}
		//header('Pragma:');
		header('Cache-Control: must-revalidate');
	//	header('Cache-Control: public, max-age=3600');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $thumbnfo['lmodified']).' GMT');
		header('Content-Length: '.$thumbnfo['fsize']);
		header('Content-Disposition: filename='.$thumbnfo['basename']); // important for right-click save-as.

		$ctype = $this->ctype($thumbnfo['extension']);
		if(null !== $ctype)
		{
			header('Content-Type: '.$ctype);
		}

		// Expire header - 1 year
		$time = time() + 365 * 86400;
		header('Expires: '.gmdate("D, d M Y H:i:s", $time).' GMT');
		header("Etag: ".$thumbnfo['md5s']);

	}



	public function ctype($ftype)
	{
		static $known_types = array(
			'gif'  => 'image/gif',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
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
	public function placeholder($parm)
	{
		if($this->_debug === true)
		{
			echo "Placeholder activated";
			return null;
		}

		$getsize = isset($parm['size']) ? $parm['size'] : '100x100';

		header('location: https://placehold.it/'.$getsize);
		header('Content-Length: 0');
		exit();
	}


}
