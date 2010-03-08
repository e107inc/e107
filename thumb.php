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
 * @version $Id$
 *
 * On-the-fly thumbnail generator
 */

define('e107_INIT', true);

$thumbpage = new e_thumbpage();

if(!$thumbpage->checkSrc())
{
	die(' Access denied!');
}
$thumbpage->sendImage();

/**
 * Handle on-the-fly created thumbnails
 *
 * @author secretr
 *
 */
class e_thumbpage
{
	protected $_request = array();
	protected $_src = null;

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
		e107::getInstance()->initCore($e107_paths, $self, $sql_info, varset($e107_CONFIG, array()));
		unset($tmp, $self);

		// parse request
		$this->parseRequest();
	}

	function parseRequest()
	{
		parse_str(str_replace('&amp;', '&', e_QUERY), $request);
		$this->_request = $request;
	}

	function checkSrc()
	{
		if(!vartrue($this->_request['src']))
		{
			return false;
		}

		$tp = e107::getParser();
		// FIXME - better way, maybe additional e_parse method, returning path SC array only
		$search = array('e_BASE/', 'e_IMAGE/', 'e_MEDIA/', 'e_PLUGIN/', 'e_THEME/', 'e_WEB/');
		$replace = array('{e_BASE}', '{e_IMAGE}', '{e_MEDIA}', '{e_PLUGIN}', '{e_THEME}', '{e_WEB}');
		$this->_request['src'] = str_replace($search, $replace,$this->_request['src']);

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

		$path = $tp->replaceConstants($this->_src);

		if(is_file($path) && is_readable($path))
		{
			$this->_src_path = $path;
			return true;
		}
		return false;
	}

	function sendImage()
	{
		if(!$this->_src_path)
		{
			return $this;
		}

		$thumbnfo = pathinfo($this->_src_path);
		$options = $this->getRequestOptions();

		$cache_str = md5(serialize($options).md5_file($this->_src_path));
		$fname = strtolower('Thumb_'.$thumbnfo['filename'].'_'.$cache_str.'.'.$thumbnfo['extension']).'.cache.bin';

		if(is_file(e_CACHE_IMAGE.$fname) && is_readable(e_CACHE_IMAGE.$fname))
		{
			$thumbnfo['lmodified'] = filemtime(e_CACHE_IMAGE.$fname);
			$thumbnfo['md5s'] = md5_file(e_CACHE_IMAGE.$fname);
			$thumbnfo['fsize'] = filesize(e_CACHE_IMAGE.$fname);

			// check browser cache
			if (@$_SERVER['HTTP_IF_MODIFIED_SINCE'] && ($thumbnfo['lmodified'] <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) && (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $thumbnfo['md5s']))
			{
				header('HTTP/1.1 304 Not Modified');
				exit;
			}

			// Send required headers
			$this->sendHeaders($thumbnfo);

			@readfile(e_CACHE_IMAGE.$fname);
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

		if(isset($this->_request['w']))
		{
			$thumb->resize($this->_request['w'], varset($this->_request['h'], $this->_request['w']));
		}
		else
		{
			$thumb->adaptiveResize($this->_request['aw'], varset($this->_request['ah'], $this->_request['aw']));
		}

		// set cache
		$thumb->save(e_CACHE_IMAGE.$fname);

		// show thumb
		$thumb->show();
		exit;
	}

	function getRequestOptions()
	{
		$ret = array();
		$ret['w'] = isset($this->_request['w']) ? intval($this->_request['w']) : false;
		$ret['h'] = isset($this->_request['h']) ? intval($this->_request['h']) : $ret['w'];
		$ret['aw'] = isset($this->_request['aw']) ? intval($this->_request['aw']) : false;
		$ret['ah'] = isset($this->_request['ah']) ? intval($this->_request['ah']) : $ret['aw'];
		return $ret;
	}

	public function sendHeaders($thumbnfo)
	{
		if(headers_sent())
		{
			echo 'Headers already sent! ';
			exit;
		}

		header('Cache-Control: must-revalidate');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $thumbnfo['lmodified']).' GMT');
		header('Content-Length: '.$thumbnfo['fsize']);
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
