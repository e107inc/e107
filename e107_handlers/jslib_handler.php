<?php
/*
 * e107 website system
 * 
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * $URL$
 * $Id$
 * 
 * FIXME - re-check and fix all browser cache related code
 * 
*/
global $pref, $eplug_admin;

class e_jslib
{
    
    function __construct()
    {

    }
    
    /**
     * Called from HEADERF
     * @return string
     */
    public function renderHeader($where = 'front', $return = false)
    {
    	$ret = '';
		
		/* DEPRECATED 
		$ret .= "
			<script type=\"text/javascript\">
				var e107Path = {
			        e_IMAGE:    '".e_IMAGE_ABS."',
			        SITEURL:    '".SITEURL."',
			        SITEURLBASE:    '".SITEURLBASE."',
			        e_HTTP:    '".e_HTTP."',
			        e_IMAGE_PACK:    '".e_IMAGE_ABS."',
			        e_PLUGIN:   '".e_PLUGIN_ABS."',
			        e_FILE:     '".e_FILE_ABS."',
			        e_MEDIA:     '".e_MEDIA_ABS."', 
			        e_WEB:     '".e_WEB_ABS."', 
			        e_ADMIN: 	'".(deftrue('ADMIN') ? e_ADMIN_ABS : '')."',
			        e_THEME:    '".e_THEME_ABS."',
			        THEME:      '".e_THEME_ABS."'
				};
			</script>
		";
		 */
    	// FIXED: convert e107.js.php to pure e107.js file
    	// FIXED: 'e_jslib_browser_cache' used by js_manager - add it together with all new prefs (JS management tab, Site preferences area)
		// FIXED: option to use external sources (e.g. google) even if JS is combined (script tags for external sources)
		if(!e107::getPref('e_jslib_nocombine'))
		{
			$e_jsmanager = e107::getJs();
			
			// render CDN frameworks
			$ret .= $e_jsmanager->renderJs('framework', null, true, true);
			
			$hash = md5(serialize(varset($pref['e_jslib'])).e107::getPref('e_jslib_browser_cache', 0).THEME.e_LANGUAGE.ADMIN).'_'.$where;
			// TODO disable cache in debug mod 
			$hash .= (e107::getPref('e_jslib_nocache')/* || deftrue('e_NOCACHE')*/ ? '_nocache' : '').(!e107::getPref('e_jslib_nobcache') || deftrue('e_NOCACHE') ? '_nobcache' : '').(e107::getPref('e_jslib_gzip') ? '' : '_nogzip');
			$ret .= "<script type='text/javascript' src='".e_JS."e_jslib.php?{$hash}'></script>\n";
			
			// render CDN libraries asap 
			$ret .= $e_jsmanager->renderJs('core', null, true, true);
			$ret .= $e_jsmanager->renderJs('plugin', null, true, true);
			$ret .= $e_jsmanager->renderJs('theme', null, true, true);
			
			if($return) return $ret;
			echo $ret;
			return;
		}
		
		$e_jsmanager = e107::getJs();
		if(!$return)
		{
			echo $ret;
			$ret = '';
		}
		// script tags
		$ret .= $e_jsmanager->renderJs('core', null, true, $return);
		$ret .= $e_jsmanager->renderJs('plugin', null, true, $return);
		$ret .= $e_jsmanager->renderJs('theme', null, true, $return);
		
		return $ret;
    }
    
    /**
     * Collect & output content of all available JS libraries  (requires e107 API)
     * FIXME 
     * - cache jslib in a pref on plugin/theme install only (plugin.xml, theme.xml)
     * - [done - e_jslib_*] the structure of the cached pref array?
     * - [done - js manager] kill all dupps
     * - jslib settings - Administration area (compression on/off, admin log on/off 
     * manual control for included JS - really not sure about this, 
     * Force Browser Cache refresh - timestamp added to the url hash)
     * - how and when to add JS lans for core libraries? 
     * - [done - js manager] separate methods for collecting & storing JS files (to be used in install/update routines) and output the JS content 
     */
    function getContent()
    {
        //global $pref, $eplug_admin, $THEME_JSLIB, $THEME_CORE_JSLIB;
        
		ob_start(); 
	    ob_implicit_flush(0);
		
		$e_jsmanager = e107::getJs();
		
		$lmodified = array();
		$e_jsmanager->renderJs('core', null, false);
		$lmodified[] = $e_jsmanager->getLastModfied('core');
		
		$e_jsmanager->renderJs('plugin', null, false);
		$lmodified[] = $e_jsmanager->getLastModfied('plugin');
		
		$e_jsmanager->renderJs('theme', null, false);
		$lmodified[] = $e_jsmanager->getLastModfied('theme');
		
		$lmodified[] = $e_jsmanager->getCacheId(); //e107::getPref('e_jslib_browser_cache', 0)
		
		// last modification time for loaded files
		$lmodified = max($lmodified);
				
		// send content type
		header('Content-type: text/javascript', true);
		$this->content_out($lmodified);
		
		// IT CAUSES GREAT TROUBLES ON SOME BROWSERS!
		/*
		if (function_exists('date_default_timezone_set')) 
		{
		    date_default_timezone_set('UTC');
		}
		

		// send last modified date
		if(deftrue('e_NOCACHE')) header('Cache-Control: must-revalidate', true);
		if($lmodified) header('Last-modified: '.gmdate("D, d M Y H:i:s", $lmodified).' GMT', true);
		//if($lmodified) header('Last-modified: '.gmdate('r', $lmodified), true);
		
		// Expire header - 1 year
		$time = time()+ 365 * 86400;
		header('Expires: '.gmdate("D, d M Y H:i:s", $time).' GMT', true);

		// If-Modified check only if cache disabled
		// if cache is enabled, cache file modification date is set to $lmodified
		if(!e107::getPref('syscachestatus'))
		{
			// not modified - send 304 and exit
			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $lmodified && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lmodified) 
			{
			    header("HTTP/1.1 304 Not Modified", true);
			    exit;
			}
		}

        //Output
        $this->content_out($lmodified);*/
    }
    
    /**
     * Output buffered content (requires e107 API)
     *
     */
    function content_out($lmodified)
    {
        global $admin_log;
        $pref = e107::getPref();
        $encoding = $this->browser_enc();
        
        $contents = ob_get_contents();
        ob_end_clean();
        
        if(!deftrue('e_NOCACHE')) header('Cache-Control: must-revalidate', true);
        
        $etag = md5($page).($encoding ? '-'.$encoding : '');
    	header('ETag: '.$etag, true);
    	if($encoding) header('Content-Encoding: ' . $encoding);
		
		if (!deftrue('e_NOCACHE') && isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			$IF_NONE_MATCH = str_replace('"','',$_SERVER['HTTP_IF_NONE_MATCH']);
			
			if($IF_NONE_MATCH == $etag || ($IF_NONE_MATCH == ($etag.'-'.$encoding)))
			{
				header('HTTP/1.1 304 Not Modified');
				exit();	
			}
		}
        
        if ($encoding)
        {
            $gzdata = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
            $size = strlen($contents);
            $crc = crc32($contents);
            
            $gzdata .= gzcompress($contents, 9);
            $gzdata = substr($gzdata, 0, strlen($gzdata) - 4);
            $gzdata .= pack("V", $crc) . pack("V", $size);
            
            $gsize = strlen($gzdata);
            $this->set_cache($gzdata, $encoding, $lmodified);
            
            header('Content-Length: '.$gsize);
            header('X-Content-size: ' . $size);
            print($gzdata);
            //TODO - log/debug
            //@file_put_contents('cache/e_jslib_log', "----------\n cache used - ".$encoding."\nOld size - $size, New compressed size - $gsize\nCache hash: ".($_SERVER['QUERY_STRING'] ? md5($_SERVER['QUERY_STRING']) : 'nomd5')."\n\n", FILE_APPEND);
        }
        else
        {
            //header('Content-Length: '.strlen($contents));
            $this->set_cache($contents, '', $lmodified);
            header('Content-Length: '.strlen($contents));
            print($contents);
            //TODO - log/debug
            //@file_put_contents('cache/e_jslib_log', "----------\nno cache used - raw\n\n", FILE_APPEND);
        }
        exit();
    }
    
    /**
     * Set Server Cache - create jslib[gzip-string][hash].js
     * (requires e107 API)
     *
     * @param string $contents
     * @param string $encoding browser accepted encoding
     * @param integer $lmodified last modfied time
     */
    function set_cache($contents, $encoding = '', $lmodified = 0)
    { 
        if (e107::getPref('e_jslib_nocache') == '0')
        {
            $cacheFile = $this->cache_filename($encoding);
			if(!$lmodified) $lmodified = time(); 
            @file_put_contents($cacheFile, $contents);
            @chmod($cacheFile, 0775);
            if($lmodified) @touch($cacheFile, $lmodified);
        }
    }
    
    /**
     * Detect browser accepted encoding (doesn't require e107 API)
     *
     * @return string encoding
     */
    function browser_enc()
    {
		//NEW - option to disable completely gzip compression
		if(strpos($_SERVER['QUERY_STRING'], '_nogzip'))
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
     * Create cache filename (require e107 API)
     *
     * @param string $encoding
     * @param string $cacheStr defaults to 'S_e_jslib'
     * @return string cache filename
     */
    function cache_filename($encoding = '', $cacheStr =  'S_e_jslib')
    {
        $cacheDir =  e_CACHE_CONTENT;
        $hash = $_SERVER['QUERY_STRING'] && $_SERVER['QUERY_STRING'] !== '_nogzip' ? md5(str_replace('_nogzip', '', $_SERVER['QUERY_STRING'])) : 'nomd5';
        $cacheFile = $cacheDir . $cacheStr . ($encoding ? '_' . $encoding : '') . '_' . $hash . '.cache.php';
       
        return $cacheFile;
    }
}
