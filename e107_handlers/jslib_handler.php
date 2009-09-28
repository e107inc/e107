<?php
/*
 * e107 website system
 * 
 * Copyright (c) 2001-2008 e107 Developers (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * $Source: /cvs_backup/e107_0.8/e107_handlers/jslib_handler.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-09-28 19:17:58 $
 * $Author: secretr $
 * 
*/
global $pref, $eplug_admin, $THEME_JSLIB, $THEME_CORE_JSLIB;

class e_jslib
{
    
    function e_jslib()
    {

    }
    
    /**
     * Collect & output all available JS libraries  (requires e107 API)
     * FIXME 
     * - cache jslib in a pref on plugin/theme install only (plugin.xml, theme.xml)
     * - the structure of the cached pref array?
     * - kill all dupps
     * - jslib settings - Administration area (compression on/off, admin log on/off 
     * manual control for included JS - really not sure about this, 
     * Force Browser Cache refresh - timestamp added to the url hash)
     * - how and when to add JS lans for core libraries? 
     * - separate methods for collecting & storing JS files (to be used in install/update routines) and output the JS content 
     */
    function core_run()
    {
        global $pref, $eplug_admin, $THEME_JSLIB, $THEME_CORE_JSLIB;
        
		ob_start(); 
	    ob_implicit_flush(0);
	
	    header("Last-modified: " . gmdate("D, d M Y H:i:s",mktime(0,0,0,15,2,2004)) . " GMT");
	    header('Content-type: text/javascript', TRUE);
        
        //array - uses the same format as $core_jslib
        if (!isset($THEME_CORE_JSLIB) || ! is_array($THEME_CORE_JSLIB))
            $THEME_CORE_JSLIB = array();
            
        //array - uses the same format as $core_jslib
        if (!isset($THEME_JSLIB) || ! is_array($THEME_JSLIB))
            $THEME_JSLIB = array();
            
        //available values - admin,front,all,none
        $core_jslib = array( //FIXME - core jslib prefs, debug options
            'jslib/prototype/prototype.js' => 'all' , 
            'jslib/scriptaculous/scriptaculous.js' => 'all',
        	'jslib/scriptaculous/effects.js' => 'all',
        	'jslib/e107.js.php' => 'all'
            //'jslib/core/decorate.js' => 'all'
        );
        
        $core_jslib = array_merge($core_jslib, $THEME_CORE_JSLIB, varsettrue($pref['e_jslib_core'], array()));
        $where_now = $eplug_admin ? 'admin' : 'front';
        
        //1. Core libs - prototype + scriptaculous effects
        echo "/* Prototype/Scriptaculous/Core libraries */\n";
        foreach ($core_jslib as $core_path => $where)
        {
            if ($where != 'all' && $where != $where_now)
                continue;
            
            if (substr($core_path, - 4) == '.php')
            {
                include_once (e_FILE . '/' . trim($core_path, '/'));
                echo "\n\n";
            }
            else
            {
                echo file_get_contents(e_FILE . '/' . trim($core_path, '/'));
                echo "\n\n";
            }
        }
        
        //2. Plugins output - all 3-rd party libs
        if (varset($pref['e_jslib']))
        {
            foreach ($pref['e_jslib']['plugins'] as $plugin_name => $plugin_libs)
            {
                if ($plugin_libs)
                {
                    foreach ($plugin_libs as $plugin_lib => $where)
                    {
                        //available values - admin,front,all
                        if ($where != 'all' && $where != $where_now)
                            continue;
                        
                        $lib_path = $plugin_name . '/' . trim($plugin_lib, '/');
                        
                        echo "/* $plugin_name libraries */\n\n";
                        
                        if (substr($plugin_lib, - 4) == '.php')
                        {
                            include_once (e_PLUGIN . $lib_path);
                            echo "\n\n";
                        }
                        else
                        {
                            echo file_get_contents(e_PLUGIN . $lib_path);
                            echo "\n\n";
                        }
                    }
                }
            }
        }
        
        //3. Theme libs
        if (varset($THEME_JSLIB) && is_array($THEME_JSLIB))
        {
            echo "/* Theme libraries */\n\n";
            foreach ($THEME_JSLIB as $lib_path => $where)
            {
                if ($where != 'all' && $where != $where_now)
                    continue;
                
                if (substr($lib_path, - 4) == '.php')
                {
                    include_once (THEME . '/' . trim($lib_path, '/'));
                    echo "\n\n";
                }
                else
                {
                    echo file_get_contents(THEME . '/' . trim($lib_path, '/'));
                    echo "\n\n";
                }
            }
        }
        
        //Output
        $this->content_out();
    }
    
    /**
     * Output buffered content (requires e107 API)
     *
     */
    function content_out()
    {
        global $pref, $admin_log;
        
        $encoding = $this->browser_enc();
        
        $contents = ob_get_contents();
        ob_end_clean();
        
        if ($encoding)
        {
            $gzdata = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
            $size = strlen($contents);
            $crc = crc32($contents);
            
            $gzdata .= gzcompress($contents, 9);
            $gzdata = substr($gzdata, 0, strlen($gzdata) - 4);
            $gzdata .= pack("V", $crc) . pack("V", $size);
            
            $gsize = strlen($gzdata);
            $this->set_cache($gzdata, $encoding);
            
            header('Content-Encoding: ' . $encoding);
            //header('Content-Length: '.$gsize);
            header('X-Content-size: ' . $size);
            print($gzdata);
            //TODO - log/debug
            //@file_put_contents('cache/e_jslib_log', "----------\n cache used - ".$encoding."\nOld size - $size, New compressed size - $gsize\nCache hash: ".($_SERVER['QUERY_STRING'] ? md5($_SERVER['QUERY_STRING']) : 'nomd5')."\n\n", FILE_APPEND);
        }
        else
        {
            //header('Content-Length: '.strlen($contents));
            $this->set_cache($contents);
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
     */
    function set_cache($contents, $encoding = '')
    {
        global $pref;
        
        if (varsettrue($pref['syscachestatus']))
        {
            $cacheFile = $this->cache_file($encoding);
            @file_put_contents($cacheFile, $contents);
            @chmod($cacheFile, 0775);
            @touch($cacheFile);
        }
    }
    
    /**
     * Detect browser accepted encoding (doesn't require e107 API)
     *
     * @return string encoding
     */
    function browser_enc()
    {
        //double-compression fix (thanks Topper), remove possible php warning
        if ( headers_sent() || ini_get('zlib.output_compression') || !isset($_SERVER["HTTP_ACCEPT_ENCODING"]) )
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
     * Create cache filename (doesn't require e107 API)
     *
     * @param string $encoding
     * @param string $cacheStr
     * @return string cache filename
     */
    function cache_file($encoding = '', $cacheStr =  'S_e_jslib')
    {
        $cacheDir = 'cache/';
        $hash = $_SERVER['QUERY_STRING'] ? md5($_SERVER['QUERY_STRING']) : 'nomd5';
        $cacheFile = $cacheDir . $cacheStr . ($encoding ? '_' . $encoding : '') . '_' . $hash . '.cache.php';
        
        return $cacheFile;
    }
}
?>