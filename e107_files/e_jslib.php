<?php 
/*
 * e107 website system
 *
 * Copyright (c) 2001-2008 e107 Developers (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 *
 * Javascript files consolidation script (gzip compression)
 *
 * $Source: /cvs_backup/e107_0.8/e107_files/e_jslib.php,v $
 * $Revision: 1.6 $
 * $Date: 2009-10-07 11:05:55 $
 * $Author: secretr $
 *
 */

// prevent notices/warnings to break JS source
error_reporting(0);

//output cache if available before calling the api
e_jslib_cache_out();

//v0.8 - we need THEME defines here (do we?) - WE DON'T
//$_E107 = array('no_forceuserupdate' => 1, 'no_online' => 1, 'no_menus' => 1, 'no_prunetmp' => 1);
$_E107['minimal'] = true;

//admin or front-end call
if (strpos($_SERVER['QUERY_STRING'], '_admin') !== FALSE)
{
	define('ADMIN_AREA', true); //force admin area
}
else
{
	define('USER_AREA', true); //force user area
}

//call jslib handler, render content
require_once ("../class2.php");
require_once (e_HANDLER.'jslib_handler.php');
$jslib = new e_jslib();
$jslib->getContent();

exit;

//
// FUNCTIONS required for retrieveing cache without e107 API
//
 
/**
 * Output cache file contents if available (doesn't require e107 API)
 * 
 * @return void
 */
function e_jslib_cache_out()
{
	$encoding = e_jslib_browser_enc(); //NOTE - should be called first
	$cacheFile = e_jslib_is_cache($encoding);
	
	if ($cacheFile)
	{
		if (function_exists('date_default_timezone_set')) 
		{
		    date_default_timezone_set('UTC');
		}
		
		// last modification time
		$lmodified = filemtime($cacheFile);
		
		// not modified - send 304 and exit
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lmodified) 
		{
		    header("HTTP/1.1 304 Not Modified", true);
		    exit;
		}
		
		// send last modified date
		header('Cache-Control: must-revalidate');
		header('Last-modified: '.gmdate('r', $lmodified), true);
		
		// send content type and encoding
		header('Content-type: text/javascript', true);
		if ($encoding)
		{
			header('Content-Encoding: '.$encoding, true);
		}
		
		// Expire header - 1 year
		$time = time()+ 365 * 86400;
		header('Expires: '.gmdate('r', $time), true);
		
		//kill any output buffering - better performance
		while (@ob_end_clean()); 
		
		echo @file_get_contents($cacheFile);
		//TODO - debug
		//@file_put_contents('cache/e_jslib_log', "----------\ncache used - ".$cacheFile."\n\n", FILE_APPEND);
		exit;
	}
}

/**
 * Check jslib cache (doesn't require e107 API)
 *
 * @param string $encoding browser accepted encoding
 * @return string cache filename on success or empty string otherwise
 */
function e_jslib_is_cache($encoding)
{
	$cacheFile = e_jslib_cache_filename($encoding);
	if (is_file($cacheFile) && is_readable($cacheFile))
	{
		return $cacheFile;
	}
	
	return '';
}

/**
 * Detect browser accepted encoding (doesn't require e107 API)
 * It'll always return empty string if '_nogzip' found in QUERY_STRING
 *
 * @return string encoding
 */
function e_jslib_browser_enc()
{
	//NEW - option to disable completely gzip compression
	if(strpos($_SERVER['QUERY_STRING'], '_nogzip') !== false)
	{
		return '';
	}
	//double-compression fix - thanks Topper
	if (headers_sent() || ini_get('zlib.output_compression') || !isset($_SERVER["HTTP_ACCEPT_ENCODING"]))
	{
		$encoding = '';
	}
	elseif (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'x-gzip') !== false)
	{
		$encoding = 'x-gzip';
	}
	elseif (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip') !== false)
	{
		$encoding = 'gzip';
	}
	else
	{
		$encoding = '';
	}
	
	return $encoding;
}

/**
 * Creates cache filename (doesn't require e107 API)
 *
 * @param string $encoding
 * @return string cache filename
 */
function e_jslib_cache_filename($encoding = '')
{

	$cacheDir = './cache/';
	$hash = $_SERVER['QUERY_STRING'] && $_SERVER['QUERY_STRING'] !== '_nogzip' ? md5(str_replace('_nogzip', '', $_SERVER['QUERY_STRING'])) : 'nomd5';
	$cacheFile = $cacheDir.'S_e_jslib'.($encoding ? '_'.$encoding : '').'_'.$hash.'.cache.php';
	
	return $cacheFile;
}
?>