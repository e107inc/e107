<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/debug_handler.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

//
// IMPORTANT Info for devs who want to add to and/or use debug definitions!
//
// MAKING NEW DEBUG DEFS
// The debug levels are Single Bit Binary Values. i.e, 1,2,4,8,16...
// In the table below, if you want to define a new value:
// - If it is debug info ALL devs will often want, then pick one of
//   the remaining "FILLIN" items and give it the name and definition you need
// - If it is a detail item not often used, simply add yours to the end of the
//   list, multiplying the previous value by 2 to get the the next 'bit' number
// - In either case, create one or more shortcut/abbreviations in $aDebugShortcuts
//   to make it easy for dev's to specify the new display item.
//


if (!defined('e107_INIT')) { exit; }


/**
 *
 */
class e107_debug {

	private static $debug_level = 0;

    /* DEBUG shortcuts */
	private static $aDebugShortcuts = array(
		'all'		 	  => 255,     // all basics
		'basic'			=> 255,     // all basics
		'b'				  => 255,     // all basics
		'warn'			=> 1,       // just php warnings, parse errrors, debug log, etc
		'showsql'		=> 2,       // sql basics
		'counts'		=> 4,       // traffic counters

		'detail'		=> 16740351,   // (0+0xfffff-32768-4096) all details, except notice and inline sc
		'd' 			  => 16740351,   // all details, except notice and inline sc
		'time' 			=> 256,     // time details and php errors
		'sql' 			=> 512,     // sql details and php errors
		'paths' 		=> 1024,		// dump path strings
		'bbsc' 			=> 2048,		// show bb and sc details
		'sc'			  => 4096,   		// Shortcode paths dumped inline
		'backtrace' => 8192,		// show backtrace when PHP has errors
		'deprecated'	=> 16384,   // show if code is using deprecated functions
		'notice'		=> 32768,   // detailed notice error messages?
		'inc'       =>  65536,  // include files
		'everything'=> 16773119,   //(0+0xffffff-4096) 24 bits set, except shortcode paths 
														// removed: inline debug breaks pages!
	);

	function __construct()
	{

	}

	/**
	 * @return string[]
	 */
	public static function getAliases()
	{
		return array(
			'off'           => 'Off',
			'basic'         => 'Basic',
			'counts'        => 'Traffic Counters',
			'showsql'       => 'SQL Analysis',
			'time'          => 'Time Analysis',
			'notice'        => 'Notices (PHP)',
			'warn'          => 'Warnings (PHP)',
			'backtrace'     => 'Backtraces (PHP)',
			'deprecated'    => 'Deprecated Functions (PHP)',
			'inc'           => 'Included Files (PHP)',
			'paths' 		=> 'Paths + Variables',		// dump path strings
			'bbsc' 			=> 'BBCodes + Shortcodes',		// show bb and sc details
			'sc'			=> 'Shortcode Placement',   		// Shortcode paths dumped inline
			'sql' 			=> 'SQL Analysis (Detailed)',     // sql details and php errors
			'everything' 	=> 'All Details',
		);

	}

	/**
	 *  Returns the currently active debug mode as an alias. eg. 'basic'
	 *  @return string|null
	 */
	public static function getShortcut()
	{
		if(deftrue('e_MENU'))
		{
			list($tmp,$alias) = explode('=', e_MENU);
			return str_replace(['!','+','-', '0', '1'],'',$alias);
		}

		if(empty($_COOKIE['e107_debug_level']))
		{
			return null;
		}

		$a = self::$aDebugShortcuts;
		unset($a['all'], $a['b'], $a['d']); // remove duplicates.

		$keys = array_flip($a);

		list($tmp,$level) = explode('=',$_COOKIE['e107_debug_level']);

		$level = (int) $level;

		return isset($keys[$level]) ? $keys[$level] : null;
	}

	/**
	 * @return bool
	 */
	public static function activated()
    {
        if (isset($_COOKIE['e107_debug_level']) || deftrue('e_DEBUG') || (strpos(e_MENU, "debug") === 0)) // ADMIN and getperms('0') are not available at this point.
        {
            return true;
        }

        return false;
    }


	/**
	 * @return bool
	 */
	public static function init()
    {
        if(!self::activated())
        {
            self::setConstants();
            return false;
        }

        if (preg_match('/debug(=?)(.*?),?(!|\+|stick|-|unstick|$)/', e_MENU, $debug_param) || isset($_COOKIE['e107_debug_level']))
        {
            $dVals = '';
            if (!isset($debug_param[1]) || ($debug_param[1] == '')) $debug_param[1] = '=';
            if (isset($_COOKIE['e107_debug_level']))
            {
                $dVals = substr($_COOKIE['e107_debug_level'], 6);
            }

            if (preg_match('/debug(=?)(.*?),?(!|\+|stick|-|unstick|$)/', e_MENU))
            {
                $dVals = $debug_param[1] == '=' ? $debug_param[2] : 'everything';
            }

            $aDVal = explode('.', $dVals); // support multiple values, OR'd together
            $dVal = 0;


            foreach ($aDVal as $curDVal)
            {
                if (isset(self::$aDebugShortcuts[$curDVal]))
                {
                    $dVal |= self::$aDebugShortcuts[$curDVal];
                }
                else
                {
                    $dVal |= intval($curDVal);
                }
            }

            if (isset($debug_param[3]))
            {
                if ($debug_param[3] == '!' || $debug_param[3] == '+' || $debug_param[3] == 'stick')
                {
                    cookie('e107_debug_level', 'level=' . $dVal, time() + 86400);
                }

                if ($debug_param[3] == '-' || $debug_param[3] == 'unstick')
                {
                    cookie('e107_debug_level', '', time() - 3600);
                }
            }

            self::$debug_level = $dVal;
        }

        self::setConstants();

        return true;
    }


    /**
     * Define all debug constants -- each one will be zero or a value
     * USING DEBUG DEFINITIONS
     * Since these are Bit Values, **never** test using < or > comparisons. Always
     * test using boolean operations, such as
     * @example if (E107_DBG_PATH)
     * @example if (E107_DBG_SQLQUERIES | E107_DBG_SQLDETAILS)
     * Since constants are defined for all possible bits, you should never need to use a number value like
     * @example if (E107_DEBUG_LEVEL & 256)
     * And there's never a reason to use
     * if (E107_DEBUG_LEVEL > 254)
     */
	private static function setConstants()
    {

        if(!defined('E107_DEBUG_LEVEL'))
        {
            define('E107_DEBUG_LEVEL', self::getLevel());
        }

        // Basic levels
        define('E107_DBG_BASIC',		(E107_DEBUG_LEVEL & 1));       // basics: worst php errors, sql errors, log, etc
        define('E107_DBG_SQLQUERIES',	(E107_DEBUG_LEVEL & 2));       // display all sql queries
        define('E107_DBG_TRAFFIC',		(E107_DEBUG_LEVEL & 4));       // display traffic counters
        define('E107_DBG_FILLIN8',		(E107_DEBUG_LEVEL & 8));       // fill in what it is
        define('E107_DBG_FILLIN16',		(E107_DEBUG_LEVEL & 16));      // fill in what it is
        define('E107_DBG_FILLIN32',		(E107_DEBUG_LEVEL & 32));      // fill in what it is
        define('E107_DBG_FILLIN64',		(E107_DEBUG_LEVEL & 64));      // fill in what it is
        define('E107_DBG_FILLIN128',	(E107_DEBUG_LEVEL & 128));     // fill in what it is

        // Gory detail levels
        define('E107_DBG_TIMEDETAILS',(E107_DEBUG_LEVEL &   256));    // detailed time profile
        define('E107_DBG_SQLDETAILS',	(E107_DEBUG_LEVEL &   512));    // detailed sql analysis
        define('E107_DBG_PATH',     	(E107_DEBUG_LEVEL &  1024));    // show e107 predefined paths
        define('E107_DBG_BBSC',     	(E107_DEBUG_LEVEL &  2048));    // Show BBCode/ Shortcode usage in postings
        define('E107_DBG_SC',       	(E107_DEBUG_LEVEL &  4096));    // Dump (inline) SC filenames as used
        define('E107_DBG_ERRBACKTRACE',	(E107_DEBUG_LEVEL &  8192));    // show backtrace for php errors
        define('E107_DBG_DEPRECATED', (E107_DEBUG_LEVEL & 16384));    // Show use of deprecated functions
        define('E107_DBG_ALLERRORS',	(E107_DEBUG_LEVEL & 32768));    // show ALL php errors (including notices), not just fatal issues
        define('E107_DBG_INCLUDES',   (E107_DEBUG_LEVEL & 65536));    // show included file list
        define('E107_DBG_NOTICES',   (E107_DEBUG_LEVEL & 32768));    // show included file list

        if(!defined('e_DEBUG'))
        {
            $e_DEBUG = (E107_DEBUG_LEVEL > 0);
            define('e_DEBUG', $e_DEBUG);
        }

    }

	/**
	 * @return int
	 */
	public static function getLevel()
    {
        return self::$debug_level;
    }

	/**
	 * @param $level
	 * @return void
	 */
	public static function setLevel($level = 0)
    {
       self::$debug_level = $level;
    }


	/**
	 * @return void
	 */
	function set_error_reporting()
	{
	}
}

// Quick debug message logger
// Example: e7debug(__FILE__.__LINE__.": myVar is ".print_r($myVar,TRUE));
/*
function e7debug($message,$TraceLev=1)
{
  if (!E107_DEBUG_LEVEL) return;
	global $db_debug;
	if (is_object($db_debug))
	{
		$db_debug->log($message,$TraceLev);
	}
}
*/
