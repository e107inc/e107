<?php
/*
 * e107 website system
 * 
 * Copyright (c) 2001-2008 e107 Developers (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * e107 Javascript API
 * 
 * $Source: /cvs_backup/e107_0.8/e107_files/e_jslib.php,v $
 * $Revision: 1.2 $
 * $Date: 2008-11-19 12:52:22 $
 * $Author: secretr $
 * 
*/

/*
    called from header_default.php - just after e_header processing
*/
    error_reporting(0);

//output cache if available before calling the api
    e_jslib_cache_out();

//v0.8 - we need THEME defines here (do we?)
    $_E107 = array('no_forceuserupdate' => 1, 'no_online' => 1,'no_menus' => 1,'no_prunetmp' => 1);
    
//admin or front-end call
    if(strpos($_SERVER['QUERY_STRING'], '_admin') !== FALSE)
        $eplug_admin = TRUE;
        
    require_once("../class2.php");
    require_once(e_HANDLER.'jslib_handler.php');
    $jslib = new e_jslib();
    $jslib->core_run(); 

 
    /**
     * FUNCTIONS required for retrieveing cache without e107 API
     * 
     */       
    
    /**
     *  Output cache file contents if available (doesn't require e107 API)
     *
     */
    function e_jslib_cache_out() {
    	$encoding = e_jslib_browser_enc();
    	$cacheFile = e_jslib_is_cache($encoding);
    	
    	if($cacheFile) {
    		while (@ob_end_clean()); // kill all output buffering for better performance
    		
			header("Last-modified: " . gmdate("D, d M Y H:i:s",mktime(0,0,0,15,2,2004)) . " GMT");
			header('Content-type: text/javascript', TRUE);
			if($encoding)
				header('Content-Encoding: '.$encoding);
			
			$tmp = @file_get_contents($cacheFile);
			header('Content-Length: '.strlen($tmp));
    		echo $tmp;
    		//TODO - log
    		//@file_put_contents('cache/e_jslib_log', "----------\ncache used - ".$cacheFile."\n\n", FILE_APPEND);
    		exit;
    	}
    }
    
    /**
     * Check jslib cache (doesn't require e107 API)
     *
     * @param string $encoding browser accepted encoding
     * @return mixed cache filename on success or false otherwise
     */
    function e_jslib_is_cache($encoding) {
    
        $cacheFile = e_jslib_cache_file($encoding);
        $mAge = 24 * 60;
		
        if(is_file($cacheFile) && is_readable($cacheFile)) {

        	if ((@filemtime($cacheFile) + ($mAge * 60)) < time()) {
        		unlink($cacheFile);
        		return false;
        	}
        	
        	return $cacheFile;
    	} 
    	
    	return false;
    }
    
    /**
     * Detect browser accepted encoding (doesn't require e107 API)
     *
     * @return string encoding
     */
    function e_jslib_browser_enc() {
    	
         //double-compression fix - thanks Topper
    	if( headers_sent() || ini_get('zlib.output_compression') || !isset($_SERVER["HTTP_ACCEPT_ENCODING"]) ){
    	
            $encoding = '';
        } elseif ( strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'x-gzip') !== false ){
        
            $encoding = 'x-gzip';
        } elseif ( strpos($_SERVER["HTTP_ACCEPT_ENCODING"],'gzip') !== false ){
        
            $encoding = 'gzip';
        } else {
        
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
    function e_jslib_cache_file($encoding='') {
 	      
    	$cacheDir = './cache/';
    	$hash = $_SERVER['QUERY_STRING'] ? md5($_SERVER['QUERY_STRING']) : 'nomd5';
    	$cacheFile = $cacheDir.'S_e_jslib'.($encoding ? '_'.$encoding : '').'_'.$hash.'.cache.php';
    	
    	return $cacheFile;
    }
?>