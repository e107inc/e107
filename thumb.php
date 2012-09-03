<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * On-the-fly thumbnail generator
 *
 * $URL$
 * $Id$
 */

 /**
 * @package e107
 * @subpackage core
 * @author secretr
 * @version $Id$
 *
 * @todo cache management - max age, max size, image cache manager (?), cron (?)
 *
 * On-the-fly thumbnail generator
 */

define('e107_INIT', true);

// error_reporting(E_ALL);


error_reporting(0); // suppress all errors or image will be corrupted. 
ini_set('gd.jpeg_ignore_warning', 1);
//require_once './e107_handlers/benchmark.php';
//$bench = new e_benchmark();
//$bench->start();

$thumbpage = new e_thumbpage();

if(!$thumbpage->checkSrc())
{
	die(' Access denied!');
}
$thumbpage->sendImage();

// Check your e_LOG folder
//$bench->end()->logResult('thumb.php', $_GET['src'].' - no cache');
exit;

class e_thumbpage
{
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
	protected $_watermark = array();

	/**
	 * Constructor - init paths
	 * @todo FIX e107 (new folder structure), simplify all this, e.g. e107::getInstance()->initMinimal($path_to_e107_config);
	 *
	 * @return void
	 */
	public function __construct()
	{
		// initial path
		$self = realpath(dirname(__FILE__));

		// Config
		include($self.'/e107_config.php');
		$tmp = $self.'/'.$HANDLERS_DIRECTORY;

		//Core functions - now API independent
		@require($tmp.'/core_functions.php');
		//e107 class
		@require($tmp.'/e107_class.php');

		$e107_paths = compact(
			'ADMIN_DIRECTORY',
			'FILES_DIRECTORY',
			'IMAGES_DIRECTORY',
			'THEMES_DIRECTORY',
			'PLUGINS_DIRECTORY',
			'HANDLERS_DIRECTORY',
			'LANGUAGES_DIRECTORY',
			'HELP_DIRECTORY',
			'DOWNLOADS_DIRECTORY',
			'UPLOADS_DIRECTORY',
			'MEDIA_DIRECTORY',
			'CACHE_DIRECTORY',
			'LOGS_DIRECTORY',
			'WEB_DIRECTORY',
			'SYSTEM_DIRECTORY',
			'CORE_DIRECTORY'
		);
		$sql_info = array(); //compact('mySQLserver', 'mySQLuser', 'mySQLpassword', 'mySQLdefaultdb', 'mySQLprefix', 'mySQLcharset');
		//e107::getInstance()->initCore($e107_paths, $self, $sql_info, varset($e107_CONFIG, array()));
		$e107 = e107::getInstance();
		
		$e107->site_path = substr(md5($mySQLdefaultdb.".".$mySQLprefix),0,10);
		
		$e107->prepare_request();
		$e107->setDirs($e107_paths, varset($E107_CONFIG, array()));
		$e107->set_constants();
		$e107->set_paths();
		$e107->file_path = $e107->fix_windows_paths($self)."/";
		$e107->set_base_path();
		$e107->set_request(false);
		$e107->set_urls(false);
		unset($tmp, $self);
	
		// basic Admin area detection - required for proper path parsing
		define('ADMIN', strpos(e_SELF, ($e107->getFolder('admin')) !== false || strpos(e_PAGE, 'admin') !== false));
		$e107->set_urls(false);
			
		$pref = $e107->getPref(); //TODO optimize/benchmark
		
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
				
		// parse request
		$this->parseRequest();
	}

	function parseRequest()
	{
		//echo 'e_query='.str_replace('&amp;', '&', e_QUERY);
		$e_QUERY = e_QUERY;
		
		if(isset($_GET['id'])) // very-basic url-tampering prevention and path cloaking
		{
			$e_QUERY = base64_decode($_GET['id']);	
		}
		
		parse_str(str_replace('&amp;', '&', $e_QUERY), $this->_request);
		
		// parse_str($_SERVER['QUERY_STRING'], $this->_request);
		return $this;
	}

	function checkSrc()
	{
		if(!vartrue($this->_request['src']))
		{
			return false;
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
		
		// echo "path=".$path."<br />";
		return false;
	}

	function sendImage()
	{
		//global $bench;
		if(!$this->_src_path)
		{
			return $this;
		}

		$thumbnfo = pathinfo($this->_src_path);
		$options = $this->getRequestOptions();

		$cache_str = md5(serialize($options).$this->_src_path);
		$fname = strtolower('Thumb_'.$thumbnfo['filename'].'_'.$cache_str.'.'.$thumbnfo['extension']).'.cache.bin';

		if(is_file(e_CACHE_IMAGE.$fname) && is_readable(e_CACHE_IMAGE.$fname))
		{
			$thumbnfo['lmodified'] = filemtime(e_CACHE_IMAGE.$fname);
			$thumbnfo['md5s'] = md5_file(e_CACHE_IMAGE.$fname);
			$thumbnfo['fsize'] = filesize(e_CACHE_IMAGE.$fname);
			
			// Send required headers
			$this->sendHeaders($thumbnfo);
		

			// check browser cache
			if (@$_SERVER['HTTP_IF_MODIFIED_SINCE'] && ($thumbnfo['lmodified'] <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) && (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $thumbnfo['md5s']))
			{
				header('HTTP/1.1 304 Not Modified');
				//$bench->end()->logResult('thumb.php', $_GET['src'].' - 304 not modified');
				exit;
			}

			// Send required headers
			//$this->sendHeaders($thumbnfo);

			@readfile(e_CACHE_IMAGE.$fname);
			//$bench->end()->logResult('thumb.php', $_GET['src'].' - retrieve cache');
			exit;
		}

		// TODO - wrap it around generic e107 thumb handler
		@require(e_HANDLER.'phpthumb/ThumbLib.inc.php');
		try
		{
		    $thumb = PhpThumbFactory::create($this->_src_path);
		    $thumb->setOptions(array('correctPermissions' => true));
		}
		catch (Exception $e)
		{
		     echo $e->getMessage();
		     return $this;
		}

		if(isset($this->_request['w']) || isset($this->_request['h']))
		{
			$thumb->resize((integer) vartrue($this->_request['w'], 0), (integer) vartrue($this->_request['h'], 0));
		}
		else
		{
			$thumb->adaptiveResize((integer) vartrue($this->_request['aw'], 0), (integer) vartrue($this->_request['ah'], 0));
		}
	
		// Watermark Option - See admin->MediaManager->prefs for details. 
		
		if($this->_watermark['activate'] < $options['w'] 
		|| $this->_watermark['activate'] < $options['aw']
		|| $this->_watermark['activate'] < $options['h']
		|| $this->_watermark['activate'] < $options['ah']
		)
		{
			$tp = e107::getParser();
			$this->_watermark['font'] = $tp->createConstants($this->_watermark['font'], 'mix');
			$this->_watermark['font'] =  realpath($tp->replaceConstants($this->_watermark['font'],'rel'));
			
			$thumb->WatermarkText($this->_watermark);			
		}
	
	
		// set cache
		$thumb->save(e_CACHE_IMAGE.$fname);

		// show thumb
		$thumb->show();
	}

	function getRequestOptions()
	{
		$ret = array();
		$ret['w'] = isset($this->_request['w']) ? intval($this->_request['w']) : false;
		$ret['h'] = isset($this->_request['h']) ? intval($this->_request['h']) : $ret['w'];
		$ret['aw'] = isset($this->_request['aw']) ? intval($this->_request['aw']) : false;
		$ret['ah'] = isset($this->_request['ah']) ? intval($this->_request['ah']) : $ret['aw'];
	//	$ret['wm'] = isset($this->_request['wm']) ? intval($this->_request['wm']) : $ret['wm'];
		return $ret;
	}

	public function sendHeaders($thumbnfo)
	{
		if(headers_sent())
		{
			echo 'Headers already sent! ';
			exit;
		}

		if (function_exists('date_default_timezone_set')) 
		{
		    date_default_timezone_set('UTC');
		}
		//header('Pragma:');
		header('Cache-Control: must-revalidate');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $thumbnfo['lmodified']).' GMT');
		header('Content-Length: '.$thumbnfo['fsize']);
		header('Content-Disposition: filename='.$thumbnfo['basename']); // important for right-click save-as. 

		$ctype = self::ctype($thumbnfo['extension']);
		if(null !== $ctype)
		{
			header('Content-Type: '.$ctype);
		}

		// Expire header - 1 year
		$time = time() + 365 * 86400;
		header('Expires: '.gmdate("D, d M Y H:i:s", $time).' GMT');
		header("Etag: ".$thumbnfo['md5s']);
		
	}

	public static function ctype($ftype)
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
}

?>