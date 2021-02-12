<?php 
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 *
 * Javascript files consolidation script (gzip compression)
 *
 * $URL$
 * $Id$
 *
 */

// prevent notices/warnings to break JS source
error_reporting(0);

//admin or front-end call
if (strpos($_SERVER['QUERY_STRING'], '_admin') !== FALSE)
{
	define('ADMIN_AREA', true); //force admin area
}
else
{
	define('USER_AREA', true); //force user area
}
// no-browser-cache check
if (strpos($_SERVER['QUERY_STRING'], '_nobcache') !== FALSE)
{
	define('e_NOCACHE', true); //force no browser cache
}
else
{
	define('e_NOCACHE', false); 
}

// no-server-cache check
if (strpos($_SERVER['QUERY_STRING'], '_nocache') !== FALSE)
{
	define('e_NOSCACHE', true); //force no system cache
}
else
{
	define('e_NOSCACHE', false); 
}

if(!e_NOCACHE) session_cache_limiter('private');

$eJslibCacheDir = null;

//output cache if available before calling the api
e_jslib_cache_out();

//v0.8 - we need THEME defines here (do we?) - WE DON'T
//$_E107 = array('no_forceuserupdate' => 1, 'no_online' => 1, 'no_menus' => 1, 'no_prunetmp' => 1);
$_E107['minimal'] = true;

//call jslib handler, render content
require_once ("../../class2.php"); 
//require_once (e_HANDLER.'jslib_handler.php');
//$jslib = new e_jslib();
$jslib = e107::getObject('e_jslib', null, e_HANDLER.'jslib_handler.php');
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
		//kill any output buffering - better performance and 304 not modified requirement
		while (ob_get_length() !== false)  // destroy all ouput buffering
		{
	        ob_end_clean();
		}
		
		/* IT CAUSES GREAT TROUBLES ON SOME BROWSERS!
		if (function_exists('date_default_timezone_set')) 
		{
		    date_default_timezone_set('UTC');
		}
		
		// last modification time
		$lmodified = filemtime($cacheFile);
		
		// send last modified date
		//header('Cache-Control: must-revalidate');
		//header('Last-modified: '.gmdate('r', $lmodified), true);
		if($lmodified) header('Last-modified: '.gmdate("D, d M Y H:i:s", $lmodified).' GMT', true);*/
		
		// send content type and encoding
		header('Content-type: text/javascript', true);
		if ($encoding)
		{
			header('Content-Encoding: '.$encoding, true);
		}
		
		if (!e_NOCACHE) header("Cache-Control: must-revalidate", true);	
		
		/*// Expire header - 1 year
		$time = time()+ 365 * 86400;
		//header('Expires: '.gmdate('r', $time), true);
		header('Expires: '.gmdate("D, d M Y H:i:s", $time).' GMT', true);
		
		header('Cache-Control: must-revalidate', true);
		
		// not modified check by last modified time - send 304 and exit
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lmodified) 
		{
		    header("HTTP/1.1 304 Not Modified", true);
		    exit;
		}*/
			
		$page = @file_get_contents($cacheFile);
		$etag = md5($page).($encoding ? '-'.$encoding : '');
		
		header('Content-Length: '.strlen($page), true);
		header('ETag: '.$etag, true);
		
		// not modified check by Etag
		if (!e_NOCACHE && isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			$IF_NONE_MATCH = str_replace('"','',$_SERVER['HTTP_IF_NONE_MATCH']);
			
			if($IF_NONE_MATCH == $etag || ($IF_NONE_MATCH == ($etag.'-'.$encoding)))
			{
				header('HTTP/1.1 304 Not Modified');
				exit();	
			}
		}
		
		echo $page;
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
	//if(!e_NOSCACHE) return '';
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
	$cacheDir = e_jslib_cache_path();
	$hash = $_SERVER['QUERY_STRING'] && $_SERVER['QUERY_STRING'] !== '_nogzip' ? md5(str_replace('_nogzip', '', $_SERVER['QUERY_STRING'])) : 'nomd5';
	$cacheFile = $cacheDir.'S_e_jslib'.($encoding ? '_'.$encoding : '').'_'.$hash.'.cache.php';
	
	return $cacheFile;
}

/**
 * Retrieve cache system path (doesn't require e107 API)
 *
 * @return string path to cache folder
 */
function e_jslib_cache_path()
{
	global $eJslibCacheDir;
	
	if(null === $eJslibCacheDir)
	{
		include('../../e107_config.php');
	
		if($CACHE_DIRECTORY)
		{
			$eJslibCacheDir = '../'.$CACHE_DIRECTORY.'content/';
		}
		elseif (isset($E107_CONFIG) && isset($E107_CONFIG['CACHE_DIRECTORY'])) 
		{
			$eJslibCacheDir = '../'.$E107_CONFIG['CACHE_DIRECTORY'].'content/';
		}
		else $eJslibCacheDir = '';
	}
	return $eJslibCacheDir;
}
?>