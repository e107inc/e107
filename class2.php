<?php
/*
* e107 website system
*
* Copyright (C) 2008-2020 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* General purpose file
*
* $URL$
* $Id$
*
*/
//
// *** Code sequence for startup ***
// IMPORTANT: These items are in a carefully constructed order. DO NOT REARRANGE
// without checking with experienced devs! Various subtle things WILL break.
//
// A Get the current CPU time so we know how long all of this takes
// B Remove output buffering so we are in control of text sent to user
// C Remove registered globals (SECURITY for all following code)
// D Setup PHP error handling (now we can see php errors ;))
// E Setup other PHP essentials
// F Grab e107_config to get directory paths
// G Retrieve Query from URI (i.e. what are the request parameters?!)
// H Initialize debug handling (NOTE: A-G cannot use debug tools!)
// I: Sanity check to ensure e107_config is ok
// J: MYSQL setup (NOTE: A-I cannot use database!)
// K: Compatibility mode
// L: Retrieve core prefs
// M: Subdomain and language selection
// N: Other misc setups (NOTE: Put most 'random' things here that don't require user session or theme
// O: Start user session
// P: Load theme
// Q: Other setups

/**
 * @package e107
 */


//
// A: Honest global beginning point for processing time
//
$eTimingStart = microtime();					// preserve these when destroying globals in step C
if ( function_exists( 'getrusage' ) ) { $eTimingStartCPU = getrusage(); }
$oblev_before_start = ob_get_level();

//
// B: Remove all output buffering
//
if(!isset($_E107) || !is_array($_E107)) { $_E107 = array(); }
if(isset($_E107['cli'], $_SERVER["HTTP_USER_AGENT"]) && !isset($_E107['debug']))
{
	exit();
}

if(function_exists('utf8_encode') === false)
{
	echo "e107 requires the PHP <a href='http://php.net/manual/en/dom.setup.php'>XML</a> package. Please install it to use e107.  ";
	exit();
}

if(!isset($_E107['cli']))
{
	while (ob_get_length() !== false)  // destroy all ouput buffering
	{
        ob_end_clean();
	}
	ob_start();             // start our own.
	$oblev_at_start = ob_get_level(); 	// preserve when destroying globals in step C
}

if(!empty($_E107['minimal']))
{
	$_E107['no_prunetmp'] = true;
	$_E107['no_menus'] = true;
	$_E107['no_theme'] = true;
	$_E107['no_online'] = true;
	$_E107['no_lan'] = true;
	$_E107['no_module'] = true;
	$_E107['no_maintenance'] = true;
	$_E107['no_forceuserupdate'] = true;
	$_E107['no_event'] = true;
//	$_E107['no_session'] = true;
//	$_E107['no_parser'] = true;
	$_E107['no_override'] = true;
	$_E107['no_log'] = true;
//	$_E107['no_autoload'] = true;
}


//
// C: Find out if register globals is enabled and destroy them if so
// (DO NOT use the value of any variables before this point! They could have been set by the user)
//

// Can't be moved to e107, required here for e107_config vars security
/*$register_globals = true;
if(function_exists('ini_get'))
{
	$register_globals = ini_get('register_globals');
}*/

// Destroy! (if we need to)
/*
if($register_globals === true)
{
	if(isset($_REQUEST['_E107'])) { unset($_E107); }
	foreach($GLOBALS as $global=>$var)
	{
		if (!preg_match('/^(_POST|_GET|_COOKIE|_SERVER|_FILES|_SESSION|GLOBALS|HTTP.*|_REQUEST|_E107|retrieve_prefs|eplug_admin|eTimingStart.*|oblev_.*)$/', $global))
		{
			unset($$global);
		}
	}
	unset($global);
}*/


// Set Absolute file-path of directory containing class2.php
if(!defined('e_ROOT'))
{
	$e_ROOT = realpath(__DIR__ . '/');

	if ((substr($e_ROOT,-1) !== '/') && (substr($e_ROOT,-1) !== '\\') )
	{
		$e_ROOT .= DIRECTORY_SEPARATOR;  // Should function correctly on both windows and Linux now.
	}

	define('e_ROOT', $e_ROOT);
	unset($e_ROOT);
}

//
// D: Setup PHP error handling
//    (Now we can see PHP errors) -- but note that DEBUG is not yet enabled!
//
global $error_handler;
$error_handler = new error_handler();

//
// E: Setup other essential PHP parameters
//
define('e107_INIT', true);


// DEPRECATED, use e107::getConfig() and e107::getPlugConfig()
if(isset($retrieve_prefs) && is_array($retrieve_prefs))
{
	foreach ($retrieve_prefs as $key => $pref_name)
	{
		 $retrieve_prefs[$key] = preg_replace("/\W/", '', $pref_name);
	}
}
else
{
	unset($retrieve_prefs);
}

@include(e_ROOT.'e107_config.php');

if(!defined('e_POWEREDBY_DISABLE'))
{
	define('e_POWEREDBY_DISABLE', false);
}

if(!empty($CLASS2_INCLUDE))
{
	 require_once(e_ROOT.$CLASS2_INCLUDE);
}

if(empty($HANDLERS_DIRECTORY))
{
	$HANDLERS_DIRECTORY = 'e107_handlers/';
}

if(empty($PLUGINS_DIRECTORY))
{
	$PLUGINS_DIRECTORY = 'e107_plugins/';
}

//define("MPREFIX", $mySQLprefix); moved to $e107->set_constants()

if(empty($mySQLdefaultdb))
{
  // e107_config.php is either empty, not valid or doesn't exist so redirect to installer..
  header('Location: install.php');
  exit();
}

// Upgrade Compatibility - Disable CL_WIDGETS before e107_class.php is loaded.
$tmpPlugDir = e_ROOT.$PLUGINS_DIRECTORY;
if(is_dir($tmpPlugDir. '/cl_widgets'))
{
	rename($tmpPlugDir. '/cl_widgets',$tmpPlugDir. '/cl_widgets__');
}
unset($tmpPlugDir);
//
// clever stuff that figures out where the paths are on the fly.. no more need for hard-coded e_HTTP :)
//



$tmp = e_ROOT.$HANDLERS_DIRECTORY;

//Core functions - now API independent
@require_once($tmp.'/core_functions.php');
e107_require_once($tmp.'/e107_class.php');
unset($tmp);

/** @note compact() causes issues with PHP7.3 */
$dirPaths = array('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'DOWNLOADS_DIRECTORY','UPLOADS_DIRECTORY','SYSTEM_DIRECTORY', 'MEDIA_DIRECTORY','CACHE_DIRECTORY','LOGS_DIRECTORY', 'CORE_DIRECTORY', 'WEB_DIRECTORY');
$e107_paths = array();
foreach($dirPaths as $v)
{
	if(isset($$v))
	{
		$e107_paths[$v] = $$v;
	}
}

// $e107_paths = compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'DOWNLOADS_DIRECTORY','UPLOADS_DIRECTORY','SYSTEM_DIRECTORY', 'MEDIA_DIRECTORY','CACHE_DIRECTORY','LOGS_DIRECTORY', 'CORE_DIRECTORY', 'WEB_DIRECTORY');
$sql_info = compact('mySQLserver', 'mySQLuser', 'mySQLpassword', 'mySQLdefaultdb', 'mySQLprefix');
if(isset($mySQLport))
{
	$sql_info['mySQLport'] = $mySQLport;
}
$e107 = e107::getInstance()->initCore($e107_paths, e_ROOT, $sql_info, varset($E107_CONFIG, array()));
e107::getSingleton('eIPHandler');			// This auto-handles bans etc
unset($dirPaths,$e107_paths);

/**
 * NEW - system security levels
 * Could be overridden by e107_config.php OR $CLASS2_INCLUDE script (if not set earlier)
 *
 * 0 disabled
 * 5 safe mode (balanced)
 * 7 high
 * 9 paranoid
 * 10 insane
 * for more detailed info see e_session SECURITY_LEVEL_* constants
 * default is e_session::SECURITY_LEVEL_BALANCED (5)
 */
if(!defined('e_SECURITY_LEVEL'))
{
	require_once(e_HANDLER.'session_handler.php');
	define('e_SECURITY_LEVEL', e_session::SECURITY_LEVEL_BALANCED);
}

//
// Start the parser; use it to grab the full query string
//
if(!isset($_E107['no_parser']))
{
	$tp = e107::getParser(); //TODO - find & replace $tp, $e107->tp
}


//
// H: Initialize debug handling
// (NO E107 DEBUG CONSTANTS OR CODE ARE AVAILABLE BEFORE THIS POINT)
// All debug objects and constants are defined in the debug handler
// i.e. from here on you can use E107_DEBUG_LEVEL or any
// E107_DBG_* constant for debug testing.
//



require_once(e_HANDLER.'debug_handler.php');
e107_debug::init(); // defines E107_DEBUG_LEVEL
/** @var e107_db_debug $dbg */
$dbg = e107::getDebug();

if(E107_DEBUG_LEVEL)
{
    $dbg->active(true);

    /** @deprecated  $db_debug */
    $db_debug = $dbg;
	$dbg->logTime('Init ErrHandler');
}

//
// I: Sanity check on e107_config.php
//     e107_config.php upgrade check
// obsolete check, rewrite it
// if (!$ADMIN_DIRECTORY && !$DOWNLOADS_DIRECTORY)
// {
	// message_handler('CRITICAL_ERROR', 8, ': generic, ', 'e107_config.php');
// 	exit;
// }

//
// J: MYSQL INITIALIZATION
//
e107::getSingleton('e107_traffic'); // We start traffic counting ASAP
//$eTraffic->Calibrate($eTraffic);

//DEPRECATED, BC, $e107->sql caught by __get()
/** @var e_db $sql */
$sql = e107::getDb(); //TODO - find & replace $sql, $e107->sql
$sql->db_SetErrorReporting(false);

$dbg->logTime('SQL Connect');
$merror=$sql->db_Connect($sql_info['mySQLserver'], $sql_info['mySQLuser'], $sql_info['mySQLpassword'], $mySQLdefaultdb);
unset($sql_info);
// create after the initial connection.
//DEPRECATED, BC, call the method only when needed
$sql2 = e107::getDb('sql2'); //TODO find & replace all $sql2 calls



//DEPRECATED, BC, call the method only when needed, $e107->admin_log caught by __get()
if(!isset($_E107['no_log']))
{
	$admin_log = e107::getLog(); //TODO - find & replace $admin_log, $e107->admin_log
}
	if($merror === 'e1')
	{
		message_handler('CRITICAL_ERROR', 6, ': generic, ', 'class2.php');
		exit;
	}

	if ($merror === 'e2')
	{
		message_handler("CRITICAL_ERROR", 7, ': generic, ', 'class2.php');
		exit;
	}

//
// K: Load compatability mode.
//

/* PHP Compatabilty should *always* be on. */
$dbg->logTime('Php compatibility handler');
e107_require_once(e_HANDLER.'php_compatibility_handler.php');

// SITEURL constant depends on the database
// See https://github.com/e107inc/e107/issues/3033 for details.
$dbg->logTime('Set urlsdeferred');
$e107->set_urls_deferred();

//
// L: Extract core prefs from the database
//


// TODO - remove it from here, auto-loaded when required
$dbg->logTime('Load Cache Handler');
e107_require_once(e_HANDLER.'cache_handler.php');

//DEPRECATED, BC, call the method only when needed, $e107->arrayStorage caught by __get()
$dbg->logTime('Load Array Storage Handler');
e107_require_once(e_HANDLER.'arraystorage_class.php'); // ArrayData(); BC Fix only. 
$eArrayStorage = e107::getArrayStorage();  //TODO - find & replace $eArrayStorage with e107::getArrayStorage();

//DEPRECATED, BC, call the method only when needed, $e107->e_event caught by __get()
$dbg->logTime('Load Event Handler');
if(!isset($_E107['no_event']))
{
	$e_event = e107::getEvent(); //TODO - find & replace $e_event, $e107->e_event
}


$dbg->logTime('Load Core Prefs');



// Check core preferences
//FIXME - message_handler is dying after message_handler(CRITICAL_ERROR) call
e107::getConfig()->load(); // extra load, required if mysql handler already called e107::getConfig()
if(!e107::getConfig()->hasData())
{

	// Core prefs error - admin log
	e107::getLog()->add('CORE_LAN8', 'CORE_LAN7', E_LOG_WARNING);

	// Try for the automatic backup..
	if(e107::getConfig('core_backup')->hasData())
	{
		// auto backup found, use backup to restore the core
		e107::getConfig()->loadData(e107::getConfig('core_backup')->getPref(), false)
			->save(false, true);

		message_handler('CRITICAL_ERROR', 3, __LINE__, __FILE__);
	}
	else
	{
		// No auto backup, try for the 'old' prefs system.
		if(!e107::getConfig('core_old')->hasData())
		{
			// Core could not restore from automatic backup. Execution halted.
			e107::getLog()->add('CORE_LAN8', 'CORE_LAN9', E_LOG_FATAL);

			message_handler('CRITICAL_ERROR', 3, __LINE__, __FILE__);
			// No old system, so point in the direction of resetcore :(
			message_handler('CRITICAL_ERROR', 4, __LINE__, __FILE__); //this will never appear till message_handler() is fixed

			exit;
		}

// resurrect core from old prefs
		e107::getConfig()->loadData(e107::getConfig('core_old')->getPref(), false)
			->save(false, true);

		// resurrect core_backup from old prefs
		e107::getConfig('core_backup')->loadData(e107::getConfig('core_old')->getPref(), false)
			->save(false, true);
	}

}

$pref = e107::getPref(); // include pref class.
// e107_require_once(e_HANDLER. 'pref_class.php');
// TODO - DEPRECATED - remove
$sysprefs = new prefs;
//DEPRECATED, BC, call e107::getPref/findPref() instead

//this could be part of e107->init() method now, prefs will be auto-initialized
//when proper called (e107::getPref())
// $e107->set_base_path(); moved to init().

//DEPRECATED, BC, call e107::getConfig('menu')->get('pref_name') only when needed
if(!isset($_E107['no_menus']))
{
	$dbg->logTime('Load Menu Prefs');
	$menu_pref = e107::getConfig('menu')->getPref(); //extract menu prefs
}
// NEW - force ssl
if(empty($_E107['cli']) && e107::getPref('ssl_enabled') &&  !deftrue('e_SSL_DISABLE') )
{
	// NOTE: e_SSL_DISABLE check is here to help webmasters fix 'ssl_enabled'
	// if set by accident on site with no SSL support - just define it in e107_config.php
	if(strncmp(e_REQUEST_URL, 'http://', 7) === 0)
	{
		// e_REQUEST_URL and e_REQUEST_URI introduced
		$url = 'https://'.substr(e_REQUEST_URL, 7);
		e107::redirect($url);
		exit;
	}
}

// $dbg->logTime('(Extracting Core Prefs Done)');
if(!isset($_E107['no_lan']))
{
	$dbg->logTime('Init Language and detect changes');
	$lng = e107::getLanguage(); // required for v1.0 BC.
	$lng->detect();
}
else
{
	define('e_LAN', 'en');
}
//
// M: Subdomain and Language Selection
//

// if a cookie name pref isn't set, make one :)
// e_COOKIE used as unique session cookie name now (see session handler)
if (!$pref['cookie_name']) { $pref['cookie_name'] = 'e107cookie'; }
define('e_COOKIE', $pref['cookie_name']);

// MOVED TO $e107->set_urls()
//define('SITEURLBASE', ($pref['ssl_enabled'] == '1' ? 'https://' : 'http://').$_SERVER['HTTP_HOST']);
//define('SITEURL', SITEURLBASE.e_HTTP);

// if the option to force users to use a particular url for the site is enabled, redirect users there as needed
// Now matches RFC 2616 (sec 3.2): case insensitive, https/:443 and http/:80 are equivalent.
// And, this is robust against hack attacks. Malignant users can put **anything** in HTTP_HOST!
if(!empty($pref['redirectsiteurl']) && !empty($pref['siteurl'])) {

	if(isset($pref['multilanguage_subdomain']) && $pref['multilanguage_subdomain'])
	{
   		if(substr(e_REQUEST_URL, 7, 4) === 'www.' || substr(e_REQUEST_URL, 8, 4) === 'www.')
		{
			$self = e_REQUEST_URL;
			//if(e_QUERY){ $self .= '?'.e_QUERY; }
			$location = str_replace('://www.', '://', $self);
			if(defined('e_DEBUG') && e_DEBUG === true)
			{
				echo 'Redirecting to location: ' .$location;
			}

			e107::getRedirect()->go($location,true,301);
		//	header("Location: {$location}", true, 301); // send 301 header, not 302
			exit();
		}
	}
    elseif(deftrue('e_DOMAIN'))
	{
		// Find domain and port from user and from pref
		list($urlbase,$urlport) = explode(':',$_SERVER['HTTP_HOST'].':');
		if(!$urlport)
		{
			$urlport = (int) $_SERVER['SERVER_PORT'];
		}
		if(!$urlport)
		{
			$urlport = 80;
		}
		$aPrefURL = explode('/',$pref['siteurl'],4);
		if (count($aPrefURL) > 2) // we can do this -- there's at least http[s]://dom.ain/whatever
		{
			$PrefRoot = $aPrefURL[2];
			list($PrefSiteBase,$PrefSitePort) = explode(':',$PrefRoot.':');
			if (!$PrefSitePort)
			{
				$PrefSitePort = ( $aPrefURL[0] === 'https:' ) ? 443 : 80;	// no port so set port based on 'scheme'
			}

			// Redirect only if
			// -- ports do not match (http <==> https)
			// -- base domain does not match (case-insensitive)
			// -- NOT admin area
			if (($urlport !== $PrefSitePort || stripos($PrefSiteBase, $urlbase) === false) && strpos(e_REQUEST_SELF, ADMINDIR) === false)
			{
				$aeSELF = explode('/', e_REQUEST_SELF, 4);
				$aeSELF[0] = $aPrefURL[0];	// Swap in correct type of query (http, https)
				$aeSELF[1] = '';						// Defensive code: ensure http:// not http:/<garbage>/
				$aeSELF[2] = $aPrefURL[2];  // Swap in correct domain and possibly port
				$location = implode('/',$aeSELF).($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '');
				$location = filter_var($location, FILTER_SANITIZE_URL);
			//
		//	header("Location: {$location}", true, 301); // send 301 header, not 302
			if(defined('e_DEBUG') && e_DEBUG === true)
			{
				echo "DEBUG INFO: site-redirect preference enabled.<br />Redirecting to: <a hre='".$location."'>".$location. '</a>';
                echo '<br />e_DOMAIN: ' .e_DOMAIN;
				echo '<br />e_SUBDOMAIN: ' .e_SUBDOMAIN;
			}
			else
			{
				e107::getRedirect()->go($location,true,301);
			}

				exit();
			}
		}
	}
}

/**
 * Set the User's Language
 */
// SESSION Needs to be started after:
// - Site preferences are available
// - Language detection (because of session.cookie_domain)
// to avoid multi-language 'access-denied' issues.
//session_start(); see e107::getSession() above
if(!isset($_E107['no_session']) && !isset($_E107['no_lan']))
{
	$dbg->logTime('Load Session Handler');
	e107::getSession(); //init core _SESSION - actually here for reference only, it's done by language handler set() method

	$dbg->logTime('Set User Language Session');
	e107::getLanguage()->set();  // set e_LANGUAGE, USERLAN, Language Session / Cookies etc. requires $pref;
}
else
{
	define('e_LANGUAGE', 'English');
}

if(!empty($pref['multilanguage']) && (e_LANGUAGE !== $pref['sitelanguage']))
{
	$sql->mySQLlanguage  = e_LANGUAGE;
	$sql2->mySQLlanguage = e_LANGUAGE;
}

//do it only once and with the proper function
// e107_include_once(e_LANGUAGEDIR.e_LANGUAGE.'/'.e_LANGUAGE.'.php');
// e107_include_once(e_LANGUAGEDIR.e_LANGUAGE.'/'.e_LANGUAGE.'_custom.php');
// v1 Custom language File Path.
if(!isset($_E107['no_lan']))
{
	$dbg->logTime('Include Global Core Language Files');
	if((e_ADMIN_AREA === true) && !empty($pref['adminlanguage']))
	{
		include(e_LANGUAGEDIR.$pref['adminlanguage'].'/'.$pref['adminlanguage'].'.php');
	}
	else
	{
		include(e_LANGUAGEDIR.e_LANGUAGE.'/'.e_LANGUAGE.'.php'); // FASTEST - ALWAYS load
	}


	$customLan = e_LANGUAGEDIR.e_LANGUAGE.'/'.e_LANGUAGE.'_custom.php';
	if(is_readable($customLan)) // FASTER - if exist, should be done 'once' by the core
	{
		include($customLan);
	}

	// v2 Custom language File Path.
	$customLan2 = e_SYSTEM.'/lans/'.e_LANGUAGE.'_custom.php';
	if(is_readable($customLan2)) // FASTER - if exist, should be done 'once' by the core
	{
		include($customLan2);
	}
	unset($customLan, $customLan2);

	$lng->bcDefs(); // defined v1.x definitions for old templates.

	$dbg->logTime('Include Global Plugin Language Files');

	if(isset($pref['lan_global_list']))
	{
		foreach($pref['lan_global_list'] as $path)
		{
			if(e107::plugLan($path, 'global', true) === false)
			{
				e107::plugLan($path, 'global');
			}

		}
	}
}

if(!isset($_E107['no_session']))
{
	$dbg->logTime('CHAP challenge');

	$die = e_AJAX_REQUEST !== true;
	e107::getSession()
		->challenge() // Make sure there is a unique challenge string for CHAP login
		->check($die); // Token protection
	unset($die);
}
//
// N: misc setups: online user tracking, cache
//
$dbg->logTime('Misc resources. Online user tracking, cache');


/**
 * @deprecated  BC, call the method only when needed, $e107->ecache caught by __get()
*/
$e107cache = e107::getCache(); //TODO - find & replace $e107cache, $e107->ecache

//DEPRECATED, BC, call the method only when needed, $e107->override caught by __get()
if(!isset($_E107['no_override']))
{
	$override = e107::getSingleton('override');
}
//DEPRECATED, BC, call the method only when needed, $e107->user_class caught by __get()
if(!isset($_E107['no_session']))
{
	$e_userclass = e107::getUserClass();  //TODO - find & replace $e_userclass, $e107->user_class
}


if(!isset($_E107['no_event']))
{
	$dbg->logTime('Init Event Handler');
	e107::getEvent()->init();
	$dbg->logTime('Register Core Events');
	e107::getNotify()->registerEvents();
}
//
// O: Start user session
//

if(!isset($_E107['no_session']))
{
	$dbg->logTime('User session');
	init_session();			// Set up a lot of the user-related constants
}
else
{
	define('ADMIN', false);
	define('USER', true);
	define('USERCLASS_LIST', '0');
}





$developerMode = (vartrue($pref['developer'],false) || E107_DEBUG_LEVEL > 0);


	// for multi-language these definitions needs to come after the language loaded.
if(!defined('SITENAME')) // Allow override by English_custom.php or English_global.php plugin files.
{
	define('SITENAME', trim($tp->toHTML($pref['sitename'], '', 'USER_TITLE,er_on,defs')));
}
if(!defined('SITEDESCRIPTION')) // Allow override by English_custom.php or English_global.php plugin files.
{
	define('SITEDESCRIPTION', $tp->toHTML($pref['sitedescription'], '', 'emotes_off,defs'));
}

define('SITEBUTTON', $tp->replaceConstants($pref['sitebutton'],'abs'));
define('SITETAG', $tp->toHTML($pref['sitetag'], false, 'emotes_off,defs'));

define('SITEADMIN', $pref['siteadmin']);
define('SITEADMINEMAIL', $pref['siteadminemail']);
define('SITEDISCLAIMER', $tp->toHTML($pref['sitedisclaimer'], '', 'emotes_off,defs'));
define('SITECONTACTINFO', $tp->toHTML($pref['sitecontactinfo'], true, 'emotes_off,defs'));
define('SITEEMAIL', vartrue($pref['replyto_email'],$pref['siteadminemail']));
define('USER_REGISTRATION', vartrue($pref['user_reg'],false)); // User Registration System Active or Not.
define('e_DEVELOPER', $developerMode);
define('e_VERSION', varset($pref['version']));

unset($developerMode);

if(!empty($pref['xurl']) && is_array($pref['xurl']))
{
	define('XURL_FACEBOOK', vartrue($pref['xurl']['facebook'], false));
	define('XURL_TWITTER', vartrue($pref['xurl']['twitter'], false));
	define('XURL_YOUTUBE', vartrue($pref['xurl']['youtube'], false));
	define('XURL_GOOGLE', vartrue($pref['xurl']['google'], false));
	define('XURL_LINKEDIN', vartrue($pref['xurl']['linkedin'], false));
	define('XURL_GITHUB', vartrue($pref['xurl']['github'], false));
	define('XURL_FLICKR', vartrue($pref['xurl']['flickr'], false));
	define('XURL_INSTAGRAM', vartrue($pref['xurl']['instagram'], false));
	define('XURL_PINTEREST', vartrue($pref['xurl']['pinterest'], false));
	define('XURL_STEAM', vartrue($pref['xurl']['steam'], false));
	define('XURL_VIMEO', vartrue($pref['xurl']['vimeo'], false));
	define('XURL_TWITCH', vartrue($pref['xurl']['twitch'], false));
	define('XURL_VK', vartrue($pref['xurl']['vk'], false));
}
else
{
	define('XURL_FACEBOOK',false);
	define('XURL_TWITTER', false);
	define('XURL_YOUTUBE', false);
	define('XURL_GOOGLE', false);
	define('XURL_LINKEDIN', false);
	define('XURL_GITHUB', false);
	define('XURL_FLICKR', false);
	define('XURL_INSTAGRAM', false);
	define('XURL_PINTEREST', false);
	define('XURL_STEAM', false);
	define('XURL_VIMEO', false);
	define('XURL_TWITCH', false);
	define('XURL_VK', false);
}

if(!defined('MAIL_IDENTIFIER'))
{
	define('MAIL_IDENTIFIER', 'X-e107-id');	
}

/* Withdrawn 0.8
// legacy module.php file loading.
if (isset($pref['modules']) && $pref['modules']) {
	$mods=explode(",", $pref['modules']);
	foreach ($mods as $mod) {
		if (is_readable(e_PLUGIN."{$mod}/module.php")) {
			require_once(e_PLUGIN."{$mod}/module.php");
		}
	}
}
*/

$dbg->logTime('Load Plugin Modules');

$js_body_onload = array();			// Initialise this array in case a module wants to add to it

// Load e_modules after all the constants, but before the themes, so they can be put to use.
if(!isset($_E107['no_module']))
{
	if(isset($pref['e_module_list']) && $pref['e_module_list'])
	{
		foreach ($pref['e_module_list'] as $mod)
		{
			if (is_readable(e_PLUGIN."{$mod}/e_module.php"))
			{
				$dbg->logTime('[e_module in '.$mod.']');
				require_once(e_PLUGIN."{$mod}/e_module.php");
	        }
		}
	}
}

//
// P: THEME LOADING
//



if(!defined('USERTHEME') && !isset($_E107['no_theme']))
{
	$dbg->logTime('Load Theme');
	$userSiteTheme = e107::getUser()->getPref('sitetheme');
	if (
		empty($userSiteTheme) ||
		(defined('e_MENUMANAGER_ACTIVE') && e_MENUMANAGER_ACTIVE === true) ||
		!file_exists(e_THEME.$userSiteTheme. '/theme.php')
	)
		$userSiteTheme = false;
	define('USERTHEME', $userSiteTheme);
}


//
// Q: ALL OTHER SETUP CODE
//
$dbg->logTime('Misc Setup');

//------------------------------------------------------------------------------------------------------------------------------------//

if(!isset($_E107['no_theme']))
{
	$ns = e107::getRender(); //  load theme render class.

	if (!class_exists('e107table', false))  // BC Fix.
	{
		class e107table extends e_render
		{

		}
	}
}

// EONE-134 - bad e_module could destroy e107 instance
$e107 = e107::getInstance();		// Is this needed now?
$dbg->logTime('IP Handler and Ban Check');
e107::getIPHandler()->ban();

if(USER && !isset($_E107['no_forceuserupdate']) && $_SERVER['QUERY_STRING'] !== 'logout' && varset($pref['force_userupdate']))
{
	if(isset($currentUser) && force_userupdate($currentUser))
	{
	  header('Location: '.SITEURL.'usersettings.php?update');
	  exit();
	}
}

$dbg->logTime('Signup/splash/admin');


if(($pref['membersonly_enabled'] && !isset($_E107['allow_guest'])) || ($pref['maintainance_flag'] && empty($_E107['cli']) && empty($_E107['no_maintenance'])))
{
	//XXX move force_userupdate() also?
	e107::getRedirect()->checkMaintenance();
	e107::getRedirect()->checkMembersOnly();
}

// ------------------------------------------------------------------------

if(!isset($_E107['no_prunetmp']))
{
	$sql->delete('tmp', 'tmp_time < '.(time() - 300)." AND tmp_ip!='data' AND tmp_ip!='submitted_link'");
}


$dbg->logTime('Login/logout/ban/tz');


if (isset($_POST['userlogin']) || isset($_POST['userlogin_x']))
{
	e107::getUser()->login($_POST['username'], $_POST['userpass'], $_POST['autologin'], varset($_POST['hashchallenge']), false);
//	e107_require_once(e_HANDLER.'login.php');
//	$usr = new userlogin($_POST['username'], $_POST['userpass'], $_POST['autologin'], varset($_POST['hashchallenge'],''));
}



// $_SESSION['ubrowser'] check not needed anymore - see session handler
// e_QUERY not defined in single entry mod
if (($_SERVER['QUERY_STRING'] === 'logout')/* || (($pref['user_tracking'] == 'session') && isset($_SESSION['ubrowser']) && ($_SESSION['ubrowser'] != $ubrowser))*/)
{
	if (USER)
	{
		if (check_class(varset($pref['user_audit_class']))) // Need to note in user audit trail
		{
			e107::getLog()->user_audit(USER_AUDIT_LOGOUT, null, USERID, USERNAME);
		}
	}

	// $ip = e107::getIPHandler()->getIP(false);			Appears to not be used, so removed
	$udata = (USER === true ? USERID.'.'.USERNAME : '0');

	// TODO - should be done inside online handler, more core areas need it (session handler for example)
	if (isset($pref['track_online']) && $pref['track_online'])
	{
		$sql->update('online', "online_user_id = 0, online_pagecount=online_pagecount+1 WHERE online_user_id = '{$udata}'");
	}
	
	// earlier event trigger with user data still available 
	e107::getEvent()->trigger('logout');
	
	// first model logout and session destroy..
	e107::getUser()->logout();
	
	// it might be removed soon
	if ($pref['user_tracking'] === 'session')
	{
		session_destroy();
		$_SESSION[e_COOKIE]='';
		// @TODO: Need to destroy the session cookie as well (not done by session_destroy()
	}
	cookie(e_COOKIE, '', (time() - 2592000));
	
	e107::getRedirect()->redirect(SITEURL);
	// header('location:'.e_BASE.'index.php');
	exit();
}



/**
 * @addtogroup timezone
 * @{
 */

/**
 * Generate an array of time zones.
 *
 * @return array
 *  Array of time zones.
 */
function systemTimeZones()
{
	// Never do something time consuming twice if you can hold onto the results
	// and re-use them. So we re-use the statically cached value to save time
	// and memory.
	static $zones = array();

	// If Timezone list is not populated yet.
	if(empty($zones))
	{
		$zonelist = timezone_identifiers_list();
		$timeNow = date('m/d/Y H:i', $_SERVER['REQUEST_TIME']);
/*
		$zonelist = DateTimeZone::listIdentifiers(
			DateTimeZone::AFRICA |
			DateTimeZone::AMERICA |
			DateTimeZone::ANTARCTICA |
			DateTimeZone::ASIA |
			DateTimeZone::ATLANTIC |
			DateTimeZone::AUSTRALIA |
			DateTimeZone::EUROPE |
			DateTimeZone::INDIAN |
			DateTimeZone::PACIFIC |
			DateTimeZone::UTC
		);*/

		foreach($zonelist as $zone)
		{
			// Because many time zones exist in PHP only for backward compatibility
			// reasons and should not be used, the list is filtered by a regular
			// expression.
			if(preg_match('!^((Africa|America|Antarctica|Arctic|Asia|Atlantic|Australia|Europe|Indian|Pacific)/|UTC$)!', $zone))
			{
				$dateTimeZone = new DateTimeZone($zone);
				$dateTime = new DateTime($timeNow, $dateTimeZone);
				$offset = $dateTime->format('O');
				$offset = chunk_split($offset, 3, ':');

				$zones[$zone] = str_replace('_', ' ', $zone) . ' (' . rtrim($offset, ':') . ')';
			}
		}

		// Sort time zones alphabetically.
		asort($zones);
	}

	return $zones;
}

/**
 * Validate a timezone.
 *
 * @param string $zone
 *  Timezone.
 *
 * @return bool
 */
function systemTimeZoneIsValid($zone = '')
{
	$zones = systemTimeZones();

	$zoneKeys = array_keys($zones);

	if(in_array($zone, $zoneKeys, true))
	{
		return true;
	}

	return false;
}

$e_deltaTime = 0;

if (isset($_COOKIE['e107_tdOffset']))
{
	// Actual seconds of delay. See e107.js and footer_default.php
	$e_deltaTime = (15*floor(((int) $_COOKIE['e107_tdOffset'] /60)/15))*60; // Delay in seconds rounded to the lowest quarter hour
}

if (isset($_COOKIE['e107_tzOffset']))
{
	// Relative client-to-server time zone offset in seconds.
	$e_deltaTime += (-((int)$_COOKIE['e107_tzOffset'] * 60 + date('Z')));
}

define('TIMEOFFSET', $e_deltaTime);

/**
 * @} End of "addtogroup timezone".
 */



// ----------------------------------------------------------------------------


if(e_ADMIN_AREA && !isset($_E107['no_lan'])) // Load admin phrases ASAP
{
	e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');
}


if(!defined('THEME') && !isset($_E107['no_theme']))
{
	$dbg->logTime('Find/Load Theme');

	if (e_ADMIN_AREA && vartrue($pref['admintheme']))
	{
		e_theme::initTheme($pref['admintheme']);
	}
	elseif (deftrue('USERTHEME') && e_ADMIN_AREA === false)
	{
		e_theme::initTheme(USERTHEME);
	}
	else
	{
		e_theme::initTheme($pref['sitetheme']);
	}

}

$theme_pref = varset($pref['sitetheme_pref']);
// --------------------------------------------------------------


// Load library dependencies.

if(!isset($_E107['no_theme']))
{
	$dbg->logTime('Load Libraries');
	if(deftrue('e_ADMIN_AREA'))
	{
		$clearThemeCache = (deftrue('e_ADMIN_HOME', false) || deftrue('e_ADMIN_UPDATE', false));
		e107::getTheme('current', $clearThemeCache)->loadLibrary();
		unset($clearThemeCache);
	}
	else
	{
		e107::getTheme('current')->loadLibrary();
	}
}
//echo "\nRun Time:  " . number_format(( microtime(true) - $startTime), 4) . " Seconds\n";
// -----------------------------------------------------------------------


// here we USE the theme
if(!isset($_E107['no_theme']))
{
	$dbg->logTime("Load admin_/theme.php file");
	if(e_ADMIN_AREA)
	{
		$dbg->logTime('Loading Admin Theme');
		if(file_exists(THEME.'admin_theme.php') && !deftrue('e_MENUMANAGER_ACTIVE')) // no admin theme when previewing.
		{
			require_once (THEME.'admin_theme.php');
		}
		else
		{
			require_once (THEME.'theme.php');
		}
	}
	else
	{
		$dbg->logTime('Loading Site Theme');
		require_once (THEME.'theme.php');

		if(isset($SC_WRAPPER))
		{
			e107::scStyle($SC_WRAPPER);
		}
	}
	$dbg->logTime("Init Theme Class");
	e107::getRender()->_init(e_ADMIN_AREA); // initialize theme class.

	if ($pref['anon_post'])
	{
		define('ANON', true);
	}
	else
	{
		define('ANON', false);
	}

	if(empty($pref['newsposts']))
	{
		define('ITEMVIEW', 15);
	}
	else
	{
		define('ITEMVIEW', $pref['newsposts']);
	}

	$layout = isset($layout) ? $layout : '_default';
	define('HEADERF', e_CORE."templates/header{$layout}.php");
	define('FOOTERF', e_CORE."templates/footer{$layout}.php");

	if (!file_exists(HEADERF))
	{
		message_handler('CRITICAL_ERROR', 'Unable to find file: '.HEADERF, __LINE__ - 2, __FILE__);
	}

	if (!file_exists(FOOTERF))
	{
		message_handler('CRITICAL_ERROR', 'Unable to find file: '.FOOTERF, __LINE__ - 2, __FILE__);
	}
}



if (!empty($pref['antiflood1']) && !defined('FLOODPROTECT'))
{
	define('FLOODPROTECT', true);
	define('FLOODTIMEOUT', max(varset($pref['antiflood_timeout'], 10), 3));
}
else
{
	/**
	 * @ignore
	 */
	define('FLOODPROTECT', false);
}



//define('LOGINMESSAGE', ''); - not needed, breaks login messages
define('OPEN_BASEDIR', (ini_get('open_basedir') ? true : false));
define('SAFE_MODE', false);

define('FILE_UPLOADS', (ini_get('file_uploads') ? true : false));
define('INIT', true);
if(isset($_SERVER['HTTP_REFERER']))
{
	$tmp = explode('?', $_SERVER['HTTP_REFERER']);
	define('e_REFERER_SELF',($tmp[0] === e_REQUEST_SELF));
	unset($tmp);
}
else
{
	/**
	 * @ignore
	 */
	define('e_REFERER_SELF', false);
}


/**
 * @deprecated Use e107::getRedirect()->go($url) instead.
 * @param $qry
 */
function js_location($qry)
{
	trigger_error('<b>js_location() is deprecated.</b> e107::getRedirect()->go($url) instead.', E_USER_DEPRECATED); // NO LAN

	global $error_handler;
	if (count($error_handler->errors))
	{
		echo $error_handler->return_errors();
		exit;
	}

	echo "<script type='text/javascript'>document.location.href='{$qry}'</script>\n";
	exit;
}

function check_email($email)
{

	if(empty($email))
	{
		return false;
	}

	if(is_numeric(substr($email,-1))) // fix for eCaptcha accidently typed on wrong line.
	{
		return false;
	}

	if(filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		return $email;	
	}
	
	return false; 
	
	// return preg_match("/^([_a-zA-Z0-9-+]+)(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+)(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,6})$/" , $email) ? $email : false;
}

//---------------------------------------------------------------------------------------------------------------------------------------------
	/**
	 * @param mixed $var is a single class number or name, or a comma-separated list of the same.
	 * @param mixed $userclass a custom list of userclasses or leave blank for the current user's permissions.
	 * If a class is prefixed with '-' this means 'exclude' - returns false if the user is in this class (overrides 'includes').
	 * Otherwise returns true if the user is in any of the classes listed in $var.
	 * @param int   $uid
	 * @return bool
	 */
function check_class($var, $userclass = null, $uid = 0)
{
	if ($userclass === null)
	{
		$userclass = defset('USERCLASS_LIST', '0');
	}
	$e107 = e107::getInstance();
	if ($var === e_LANGUAGE)
	{
		return true;
	}

	if (e107::isCli())
	{
		global $_E107;
		if (empty($_E107['phpunit']))
		{
			return true;
		}
	}

	if (is_numeric($uid) && $uid > 0)
	{    // userid has been supplied, go build that user's class list
		$userclass = class_list($uid);
	}

	if ($userclass == '')
	{
		return false;
	}

	$class_array = !is_array($userclass) ? explode(',', $userclass) : $userclass;

	$varList = !is_array($var) ? explode(',', (string) $var) : $var;
	$latchedAccess = false;

	foreach ($varList as $v)
	{
		$v = trim($v);
		$invert = false;
		//value to test is a userclass name (or garbage, of course), go get the id
		if (!is_numeric($v))
		{
			if ($v === '')
			{
				return false;
			}

			if ($v[0] === '-')
			{
				$invert = true;
				$v = substr($v, 1);
			}
			$v = $e107->user_class->ucGetClassIDFromName($v);
		}
		elseif ($v < 0)
		{
			$invert = true;
			$v = -$v;
		}
		if ($v !== false)
		{
			//	var_dump($v);
			// Ignore non-valid userclass names
			if (($v === '0') || ($v === 0) || in_array($v, $class_array))
			{
				if ($invert)
				{
					return false;
				}
				$latchedAccess = true;
			}
			elseif ($invert && count($varList) == 1)
			{
				// Handle scenario where only an 'exclude' class is passed
				$latchedAccess = true;
			}
		}
	}

	return $latchedAccess;
}


/**
 * @param                   $arg
 * @param bool|mixed|string $ap
 * @return bool
 */
function getperms($arg, $ap = ADMINPERMS, $path = e_SELF)
{
	// $ap = "4"; // Just for testing.

	if(!ADMIN || trim($ap) === '')
	{
		return false;
	}

	if($arg === 0) // Common-error avoidance with getperms(0)
	{
		$arg = '0';
	}

	if ($ap === '0' || $ap === '0.') // BC fix.
	{
		return true;
	}

	if ($arg === 'P' && preg_match('#(.*?)/' .e107::getInstance()->getFolder('plugins'). '(.*?)/(.*?)#', $path, $matches))
	{
		$sql = e107::getDb('psql');
	/*	$id = e107::getPlug()->load($matches[2])->getId();
		$arg = 'P'.$id;*/

		if ($sql->select('plugin', 'plugin_id', "plugin_path = '".$matches[2]."' LIMIT 1 "))
		{
			$row = $sql->fetch();
			$arg = 'P'.$row['plugin_id'];
		}
	}

	$ap_array = explode('.',$ap);

	if (in_array($arg,$ap_array,false))
	{
		return true;
	}

	if(strpos($arg, "|"))
	{
    	$tmp = explode("|", $arg);
		foreach($tmp as $val)
		{
		   	if(in_array($val,$ap_array))
			{
				return true;
			}
		}
	}


	return false;

}

/**
 * @deprecated
 * Get the user data from user and user_extended tables
 * SO MUCH DEPRECATED! Use e107::user($uid);
 * @param  int $uid
 * @param string $extra
 * @return array
 */
function get_user_data($uid, $extra = '')
{
	trigger_error('<b>get_user_data() is deprecated.</b> Use e107::user($uid) instead.', E_USER_DEPRECATED); // NO LAN

	if(e107::getPref('developer'))
	{
		e107::getLog()->add(
			'Deprecated call - get_user_data()',
			'Call to deprecated function get_user_data() (class2.php) '."\n".print_r(debug_backtrace(null,2), true),
			E_LOG_INFORMATIVE,
			'DEPRECATED'
		);
		// TODO - debug screen Deprecated Functions (e107)
		e107::getMessage()->addDebug('Deprecated get_user_data() backtrace:<pre>'."\n".print_r(debug_backtrace(null,2), true).'</pre>');
	}

	unset($extra);

	$var = array();
	$user = e107::getSystemUser($uid);
	if($user)
	{
		$var = $user->getUserData();
	}
	return $var;
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//SO MUCH DEPRECATED 
/**
 * @deprecated Use instead: e107::getConfig(alias)->->setPref($array)->save();
 * @example Use instead: e107::getConfig(alias)->->setPref($array)->save();  Not to be used for saving plugin or theme prefs!
 * @param string    $table
 * @param int|mixed $uid
 * @param string    $row_val
 * @return bool|int|string
 */
function save_prefs($table = 'core', $uid = USERID, $row_val = '')
{
	trigger_error('<b>save_prefs() is deprecated.</b> Use e107::getConfig(table)->->setPref($array)->save() instead.', E_USER_DEPRECATED); // NO LAN

	global $pref, $user_pref, $tp, $PrefCache, $sql, $eArrayStorage, $theme_pref;
	unset($row_val);

	if(e107::getPref('developer'))
	{
		$backtrace = debug_backtrace(false);
		
		e107::getLog()->add(
			'Deprecated call - save_prefs()',
			"Call to deprecated function save_prefs() (class2.php). Backtrace:\n".print_r($backtrace, true),
			E_LOG_INFORMATIVE,
			'DEPRECATED'
		);

		e107::getMessage()->addDebug('Deprecated save_prefs() backtrace:<pre>'."\n".print_r($backtrace, true).'</pre>');
	}




	switch($table)
	{
		case 'core':
			//brute load, force update

			if(count($pref) < 100) // precaution for old plugins
			{
				$backtrace = debug_backtrace(false);

				e107::getLog()->add(
				'Core pref corruption avoided',
				"Call to deprecated function save_prefs() (class2.php) with too few prefs. Backtrace:\n".print_r($backtrace, true),
				E_LOG_INFORMATIVE,
				'DEPRECATED'
				);


				e107::getMessage()->addDebug('Core-pref corruption avoided. Too few prefs sent to save_prefs(). Backtrace:<pre>'."\n".print_r($backtrace, true).'</pre>');
				return false;
			}


			return e107::getConfig()->loadData($pref, false)->save(false, true);
			break;

		case 'theme':
			//brute load, force update
			return e107::getConfig()->set('sitetheme_pref', $theme_pref)->save(false, true);
			break;

		default:
			$_user_pref = $tp->toDB($user_pref, true, true, 'pReFs');
			$tmp = e107::serialize($_user_pref);
			$sql->update('user', "user_prefs='$tmp' WHERE user_id=". (int)$uid);
			return $tmp;
			break;
	}


}


//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

/**
 * @deprecated use e107::setRegistry()
 * @param $id
 * @param $var
 */
function cachevars($id, $var)
{
	trigger_error('<b>cachevars() is deprecated.</b> Use e107::setRegistry() instead.', E_USER_DEPRECATED); // NO LAN

	e107::setRegistry('core/cachedvars/'.$id, $var);
}


/**
 * @deprecated  use e107::getRegistry()
 * @param $id
 * @return mixed
 */
function getcachedvars($id)
{
	trigger_error('<b>getcachedvars() is deprecated.</b> Use e107::getRegistry() instead.', E_USER_DEPRECATED); // NO LAN

	return e107::getRegistry('core/cachedvars/'.$id, false);
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

/**
 * @package e107
 */
class floodprotect
{
	function flood($table, $orderfield)
	{
		/*
		# Test for possible flood
		#
		# - parameter #1                string $table, table being affected
		# - parameter #2                string $orderfield, date entry in respective table
		# - return                                boolean
		# - scope                                        public
		*/
		$sql= e107::getDb('flood');

		if (FLOODPROTECT === true)
		{
			$sql->select($table, '*', 'ORDER BY '.$orderfield.' DESC LIMIT 1', 'no_where');
			$row=$sql->fetch();
			return ($row[$orderfield] <= (time() - FLOODTIMEOUT));
		}

		return true;
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
/**
 * The whole could happen inside e_user class
 * @return void
 */
function init_session()
{
	/*
	# Validate user
	#
	# - parameters none
	# - return boolean
	# - scope public
	*/

	// ----------------------------------------

	// Set 'UTC' as default timezone to avoid PHP warnings.
	date_default_timezone_set('UTC');

	global $user_pref, $currentUser, $_E107;

	e107::getDebug()->logTime('[init_session: getInstance]');
	$e107 = e107::getInstance();

	// New user model
	e107::getDebug()->logTime('[init_session: getUser]');
	$user = e107::getUser();

	// Get user timezone.
	e107::getDebug()->logTime('[init_session: getTimezone]');
	$tzUser = $user->getTimezone();

	// If user timezone is valid.
	e107::getDebug()->logTime('[init_session: systemTimeZoneIsValid]');
	if (varset($tzUser, false) && systemTimeZoneIsValid($tzUser))
	{
		// Sets the default timezone used by all date/time functions.
		date_default_timezone_set($tzUser);
		// Save timezone for later use.
		define('USERTIMEZONE', $tzUser);

		unset($tzUser);
	}
	else
	{
		// Use system default timezone.
		$pref = e107::getPref();
		$tz = vartrue($pref['timezone'], 'UTC');

		// Sets the default timezone used by all date/time functions.
		date_default_timezone_set($tz);
		// Save timezone for later use.
		define('USERTIMEZONE', $tz);

		unset($tz);
	}

	e107::getDebug()->log("Timezone: ".USERTIMEZONE); // remove later on.

	e107::getDebug()->logTime('[init_session: getIP]');
	define('USERIP', e107::getIPHandler()->getIP());

	e107::getDebug()->logTime('[init_session: getToken]');
	define('POST_REFERER', md5($user->getToken()));

	// Check for intruders - outside the model for now
	// TODO replace __referer with e-token, remove the above
	if((isset($_POST['__referer']) && !$user->checkToken($_POST['__referer']))
		|| (isset($_GET['__referer']) && !$user->checkToken($_GET['__referer'])))
	{
		// Die, die, die! DIE!!!
		die('Unauthorized access!');
	}

    if(e107::isCli())
	{
		define('USER', true);
		define('USERID', 1);
		define('USERNAME', 'e107-cli');
		define('ADMINNAME', 'e107-cli');
		define('USERTHEME', false);
		define('ADMIN', true);
		define('ADMINPERMS', '0');
		define('GUEST', false);
		define('USERCLASS', '');
		define('USEREMAIL', '');
		define('USERCLASS_LIST', '253,254,250,251,0'); // needed to run some queries.
		define('USERJOINED', '');
		define('e_CLASS_REGEXP', '(^|,)(253|254|250|251|0)(,|$)');
		define('e_NOBODY_REGEXP', '(^|,)255(,|$)');
		return;
	}

	e107::getDebug()->logTime('[init_session: hasBan]');
	if ($user->hasBan())
	{
		$msg = e107::findPref('ban_messages/6');
		if($msg) echo e107::getParser()->toHTML($msg);
		exit;
	}

	e107::getDebug()->logTime('[init_session: Constants]');
	define('ADMIN', $user->isAdmin());
	define('ADMINID', $user->getAdminId());
	define('ADMINNAME', $user->getAdminName());
	define('ADMINPERMS', $user->getAdminPerms());
	define('ADMINEMAIL', $user->getAdminEmail());
	define('ADMINPWCHANGE', $user->getAdminPwchange());

	e107::getDebug()->logTime('[init_session: isUser]');
	if (!$user->isUser())
	{
		define('USER', false);
		define('USERID', 0);
		define('USERTHEME', false);
		define('GUEST', true);
		define('USERCLASS', '');
		define('USEREMAIL', '');
		define('USERSIGNATURE', '');

		if($user->hasSessionError())
		{
			define('LOGINMESSAGE', CORE_LAN10);
			define('CORRUPT_COOKIE', true);
		}
	}
	else
	{
		// we shouldn't use getValue() here, it's there for e.g. shortcodes, profile page render etc.
		define('USERID', $user->getId());
		define('USERNAME', $user->get('user_name'));
		define('USERURL', $user->get('user_homepage', false)); //required for BC
		define('USEREMAIL', $user->get('user_email'));
		define('USER', true);
		define('USERCLASS', $user->get('user_class'));
		define('USERIMAGE', $user->get('user_image'));
		define('USERPHOTO', $user->get('user_sess'));
		define('USERJOINED', $user->get('user_join'));
		define('USERCURRENTVISIT', $user->get('user_currentvisit'));
		define('USERVISITS', $user->get('user_visits'));
		define('USERSIGNATURE', $user->get('user_signature'));


		if(ADMIN && empty($_E107['no_online']) && empty($_E107['no_forceuserupdate'])) // XXX - why for admins only?
		{
			e107::getRedirect()->setPreviousUrl();
		}

		define('USERLV', $user->get('user_lastvisit'));

		// BC - FIXME - get rid of them!
		$currentUser = $user->getData();
		$currentUser['user_realname'] = $user->get('user_login'); // Used by force_userupdate
		$e107->currentUser = &$currentUser;

		// if(defined('SETTHEME')) //override - within e_module for example. 
		// {
			// $_POST['sitetheme'] = SETTHEME;
			// $_POST['settheme'] = 1;
		// }

		// XXX could go to e_user class as well
		if ($user->checkClass(e107::getPref('allow_theme_select', false), false))
		{	// User can set own theme
 			if (isset($_POST['settheme']))
			{
				$uconfig = $user->getConfig();
				if(e107::getPref('sitetheme') !== $_POST['sitetheme'])
				{
                	require_once(e_HANDLER."theme_handler.php");
					$utheme = new themeHandler;
                    $ut = $utheme->themeArray[$_POST['sitetheme']];

                    $uconfig->setPosted('sitetheme', $_POST['sitetheme'])
                    	->setPosted('sitetheme_custompages', $ut['custompages'])
                    	->setPosted('sitetheme_deflayout', $utheme->findDefault($_POST['sitetheme']));
				}
				else
				{
					$uconfig->remove('sitetheme')
						->remove('sitetheme_custompages')
						->remove('sitetheme_deflayout');
				}

				$uconfig->save(true);
				unset($ut);
			}


   		}
   		elseif ($user->getPref('sitetheme'))
   		{
   			$user->getConfig()
   				->remove('sitetheme')
   				->remove('sitetheme_custompages')
   				->remove('sitetheme_deflayout')
   				->save();
		}




		$user_pref = $user->getPref();
	}

	e107::getDebug()->logTime('[init_session: getClassList]');
	define('USERCLASS_LIST', $user->getClassList(true));
	define('e_CLASS_REGEXP', $user->getClassRegex());
	define('e_NOBODY_REGEXP', '(^|,)'.e_UC_NOBODY.'(,|$)');

}


$dbg->logTime('Go online');

if(!isset($_E107['no_online']))
{
	e107::getOnline()->goOnline($pref['track_online'], $pref['antiflood1']);
}

$dbg->logTime('(After Go online)');
$dbg->logTime('Frontpage detection');

$fpUrl = str_replace(SITEURL, '', rtrim(e_REQUEST_URL, '?/'));
$fpPref = e107::getFrontpage();

if($fpUrl === $fpPref)
{
	e107::canonical('_SITEURL_');
}
unset($fpUrl, $fpPref);

$dbg->logTime('Legacy Route detection');
// Reverse lookup of current URI against legacy e_url entry to determine route.
if(!deftrue('e_SINGLE_ENTRY') && deftrue('e_CURRENT_PLUGIN'))
{
	if($route = e107::detectRoute(e_CURRENT_PLUGIN, e_REQUEST_URI))
	{
		e107::route($route);
	}

	unset($route);
}






/**
 * Set Cookie
 *
 * @param string  $name
 * @param string  $value
 * @param integer $expire seconds
 * @param string  $path
 * @param string  $domain
 * @param int     $secure
 * @return void
 */
function cookie($name, $value, $expire=0, $path = e_HTTP, $domain = '', $secure = 0)
{
	global $_E107;

	if(!empty($_E107['cli']))
	{
		return null;
	}
/*
	if(!e_SUBDOMAIN || (defined('MULTILANG_SUBDOMAIN') && MULTILANG_SUBDOMAIN === true))
	{
		$domain = (e_DOMAIN !== false) ? ".".e_DOMAIN : "";
	}
	*/
	if((empty($domain) && !e_SUBDOMAIN) || (defined('MULTILANG_SUBDOMAIN') && MULTILANG_SUBDOMAIN === true))
	{
		$domain = (e_DOMAIN !== false) ? ".".e_DOMAIN : '';
	}

	if(defined('e_MULTISITE_MATCH') && deftrue('e_ADMIN_AREA'))
	{
		$path = '/';
	}
	
	setcookie($name, $value, $expire, $path, $domain, $secure, true);
}

//
/**
 *
 * generic function for retaining values across pages. ie. cookies or sessions.
 * @deprecated Use e107::getUserSession()->makeUserCookie($userData, $autologin); instead.
 * @param $name
 * @param $value
 * @param string $expire
 * @param string $path
 * @param string $domain
 * @param int $secure
 */
function session_set($name, $value, $expire='', $path = e_HTTP, $domain = '', $secure = 0)
{

	//$userData = ['user_name
//	e107::getUserSession()->makeUserCookie($userData, $autologin);

	global $pref;
	if ($pref['user_tracking'] === 'session')
	{
		$_SESSION[$name] = $value;
	}
	else
	{
		if((empty($domain) && !e_SUBDOMAIN) || (defined('MULTILANG_SUBDOMAIN') && MULTILANG_SUBDOMAIN === true))
		{
			$domain = (e_DOMAIN !== false) ? ".".e_DOMAIN : "";
		}

		if(defined('e_MULTISITE_MATCH'))
		{
			$path = '/';
		}
		
		setcookie($name, $value, $expire, $path, $domain, $secure, true);
		$_COOKIE[$name] = $value;
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function message_handler($mode, $message, $line = 0, $file = '')
{
	if(!defined('e_HANDLER'))
	{
		echo $message;
		return;
	}



	e107_require_once(e_HANDLER.'message_handler.php');
	show_emessage($mode, $message, $line, $file);
}



function class_list($uid = '')
{
	$clist = array();

	if (is_numeric($uid) || USER === true)
	{
		if (is_numeric($uid))
		{
			if($ud = e107::user($uid))
			{
				$admin_status = $ud['user_admin'];
				$class_list = $ud['user_class'];
				$admin_perms = $ud['user_perms'];
			}
			else
			{
				$admin_status = false;
				$class_list = "";
				$admin_perms = "";
			}
		}
		else
		{
			$admin_status = ADMIN;
			$class_list = USERCLASS;
			$admin_perms = ADMINPERMS;
		}

		if ($class_list)
		{
			$clist = explode(',', $class_list);
		}

		$clist[] = e_UC_MEMBER;

		if ($admin_status == true)
		{
			$clist[] = e_UC_ADMIN;
		}

		if ($admin_perms === '0')
		{
			$clist[] = e_UC_MAINADMIN;
		}
	}
	else
	{
		$clist[] = e_UC_GUEST;
	}

	$clist[] = e_UC_READONLY;
	$clist[] = e_UC_PUBLIC;

	return implode(',', $clist);
}

// ---------------------------------------------------------------------------


/**
 * @deprecated  by e107::lan();
 * @param string $path
 * @param boolean $force [optional] Please use the default
 * @return bool
 */
function include_lan($path, $force = false)
{
	trigger_error('<b>include_lan() is deprecated.</b> Use e107::lan() instead.', E_USER_DEPRECATED); // NO LAN

	return e107::includeLan($path, $force);
}



/**
 *	Check that all required user fields (including extended fields) are valid.
 *	@param array $currentUser - data for user
 *	@return boolean true if update required
 */
function force_userupdate($currentUser)
{
	if (e_PAGE == 'usersettings.php' || (defined('FORCE_USERUPDATE') && (FORCE_USERUPDATE == false)) || strpos(e_SELF, ADMINDIR) == true )
	{
		return false;
	}

    $signup_option_names = array('realname', 'signature', 'image', 'timezone', 'class');

	foreach($signup_option_names as $key => $value)
	{
		if (!$currentUser['user_'.$value] && e107::getPref('signup_option_'.$value, 0) == 2)
		{
			return true;
		}
    }

	if (!e107::getPref('disable_emailcheck',true) && !trim($currentUser['user_email'])) return true;

	if(e107::getDb()->select('user_extended_struct', 'user_extended_struct_applicable, user_extended_struct_write, user_extended_struct_name, user_extended_struct_type', 'user_extended_struct_required = 1 AND user_extended_struct_applicable != '.e_UC_NOBODY))
	{
		while($row = e107::getDb()->fetch())
		{
			if (!check_class($row['user_extended_struct_applicable'])) { continue; }		// Must be applicable to this user class
			if (!check_class($row['user_extended_struct_write'])) { continue; }				// And user must be able to change it
			$user_extended_struct_name = "user_{$row['user_extended_struct_name']}";
			if (!isset($currentUser[$user_extended_struct_name]))
			{
				//e107::admin_log->addEvent(4, __FILE__."|".__FUNCTION__."@".__LINE__, 'FORCE', 'Force User update', 'Trigger field: '.$user_extended_struct_name, false, LOG_TO_ROLLING);
				return true;
			}
			if (($row['user_extended_struct_type'] == 7) && ($currentUser[$user_extended_struct_name] == '0000-00-00'))
			{
				//e107::admin_log->addEvent(4, __FILE__."|".__FUNCTION__."@".__LINE__, 'FORCE', 'Force User update', 'Trigger field: '.$user_extended_struct_name, false, LOG_TO_ROLLING);
				return true;
			}
		}
	}
	return false;
}



/**
 * @package e107
 */
class error_handler
{

	public $errors = [];
	public $debug = false;
	protected $xdebug = false;
	protected $docroot = '';
	protected $label = array();
	protected $color = null;

	function __construct()
	{
		$this->label = array(E_NOTICE => "Notice", E_WARNING => "Warning", E_DEPRECATED => "Deprecated", E_STRICT => "Strict");
		$this->color = array(E_NOTICE=> 'info', E_WARNING=>'warning', E_DEPRECATED => 'danger', E_STRICT => 'primary');
		$this->docroot = e_ROOT; // dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR;

		// This is initialized before the current debug level is known
		if(function_exists('xdebug_get_function_stack'))
		{
			$this->xdebug = true;
		}

		//
		global $_E107;

		if(!empty($_E107['debug']))
		{
			$this->debug = true;
			error_reporting(E_ALL);
			return;
		}

		if(!empty($_E107['cli']))
		{
			error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
			return;
		}

		if ((isset($_SERVER['QUERY_STRING']) && (strpos($_SERVER['QUERY_STRING'], 'debug=') !== false)) || isset($_COOKIE['e107_debug_level']) && ((strpos($_SERVER['QUERY_STRING'], 'debug=-')) === false) )
		{
		   	$this->debug = true;
		  	error_reporting(E_ALL);
		}
		else
		{
			error_reporting(E_ERROR | E_PARSE);
		}

		set_error_handler(array(&$this, 'handle_error'));
	}

	/**
	 * Deftrue function independent of core function.
	 * @param $value
	 * @return bool
	 */
	private function deftrue($value)
	{
		return defined($value) && constant($value);
	}

	private function addError($type, $message, $line, $file)
	{
		$error = [];
		$error['short'] = "<span class='label label-".$this->color[$type]."'>".$this->label[$type]."</span> {$message}, Line <mark>{$line}</mark> of {$file}<br />\n";

		if($this->xdebug)
		{
			$backtrace = xdebug_get_function_stack();
		}
		else
		{
			$trace = debug_backtrace();
			$backtrace[0] = (isset($trace[1]) ? $trace[1] : "");
			$backtrace[1] = (isset($trace[2]) ? $trace[2] : "");
		}

		$error['trace'] = $backtrace;
		$this->errors[] = $error;

	}


	/**
	 * @param $type
	 * @param $message
	 * @param $file
	 * @param $line
	 * @param $context (deprecated since PHP 7.2.0)
	 * @return bool
	 */
	function handle_error($type, $message, $file, $line, $context = null) {
		$startup_error = (!defined('E107_DEBUG_LEVEL')); // Error before debug system initialized

		switch($type)
		{
			case E_USER_DEPRECATED:
				if($this->deftrue('E107_DBG_DEPRECATED'))
				{
					$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3);
					e107::getDebug()->logDeprecated($message, varset($trace[2]['file']), varset($trace[2]['line']));
				}
			break;

			case E_NOTICE:
		//	case E_STRICT:

			if ($startup_error || $this->deftrue('E107_DBG_ALLERRORS')  || $this->deftrue('E107_DBG_ERRBACKTRACE'))
			{
				$this->addError($type, $message,$line,$file);
			}
			break;
			case E_WARNING:
			if ($startup_error || $this->deftrue('E107_DBG_BASIC') || $this->deftrue('E107_DBG_ERRBACKTRACE'))
			{
				$this->addError($type, $message,$line,$file);
			}
			break;
			case E_USER_ERROR:
			if ($this->debug == true)
			{
				$error['short'] = "&nbsp;&nbsp;&nbsp;&nbsp;Internal Error Message: {$message}, Line <mark>{$line}</mark> of {$file}<br />\n";
				$trace = debug_backtrace();
				$backtrace[0] = (isset($trace[1]) ? $trace[1] : "");
				$backtrace[1] = (isset($trace[2]) ? $trace[2] : "");
				$error['trace'] = $backtrace;
				$this->errors[] = $error;
			}
			break;

			default:
			return true;
			break;
		}

		return null;
	}



	function render_trace($array)
	{
		if($this->xdebug == false)
		{
			return print_a($array, true);
		}

		array_pop($array);


		$text = "<table class='table table-bordered table-striped table-condensed'>
		<tr class='danger'><th>#</th><th>Function</th><th>Location</th></tr>";
		foreach($array as $key=>$val)
		{
			$text .= "
			<tr>
				<td>".$key."</td>
				<td>";
			$text .= !empty($val['class']) ? $val['class']."->" : '';
			$text .= !empty($val['include_filename']) ? "include: ". str_replace($this->docroot,'', $val['include_filename']) : '';
			$text .= !empty($val['function']) ? htmlentities($val['function'])."(" : "";
			$text .= !empty($val['params']) ? print_r($val['params'],true) : '';
			$text .= !empty($val['function']) ? ")" : "";
			$text .="</td>
				<td style='width:20%'>";
			$text .= str_replace($this->docroot,'', $val['file']).":".$val['line'];
			$text .= "</td>
			</tr>";
		}

		$text .= "</table>";

		return $text;

	}

	function return_errors()
	{
		$index = 0; $colours[0] = "#C1C1C1"; $colours[1] = "#B6B6B6";
        $ret = "";

	//	print_a($this->errors);

		if (E107_DBG_ERRBACKTRACE)
		{



			foreach ($this->errors as $key => $value)
			{
				$ret .= "\t<tr>\n\t\t<td>{$value['short']}</td><td><input class='btn btn-info button e-expandit' data-target = 'bt_{$key}' type ='button' style='cursor: hand; cursor: pointer;' size='30' value='Back Trace'  />\n";
					$ret .= "</td>\n\t</tr>";
				$ret .= "\t<tr>\n<td style='display: none;' colspan='2' id='bt_{$key}'>".$this->render_trace($value['trace'])."</td></tr>\n";

				if($index == 0) { $index = 1; } else { $index = 0; }
			}
		
		}
		else
		{
			foreach ($this->errors as $key => $value)
			{
				$ret .= "<tr><td>{$value['short']}</td></tr>\n";
			}
		}

		return ($ret) ? "<table class='table table-condensed'>\n".$ret."</table>" : false;
	}

	/**
	 * @param $information
	 * @param $level
	 */
	function trigger_error($information, $level)
	{
		trigger_error($information, $level);
	}
}




/**
 * Manage Headers sent to browser. 
 * It is important to specify one of Expires or Cache-Control max-age, and one of Last-Modified or ETag, for all cacheable resources. 
 * It is redundant to specify both Expires and Cache-Control: max-age, or to specify both Last-Modified and ETag. 
 * Reference : http://css-tricks.com/snippets/php/intelligent-php-cache-control/
 * XXX Etag cannot be relied on as some hosts have Etag disabled. 
 * XXX session_cache_limiter('private') will override some of the things below and bring back our browser cache issues. 
 */
class e_http_header
{
	private $content;
	private $etag;
	private $compress_output = false;
	private $compression_level = 6;
	private $compression_browser_support = false;
	private $compression_server_support = false;
	private $headers = array();
	private $length = 0;

	
	function __construct()
	{
		if (strpos(varset($_SERVER['HTTP_ACCEPT_ENCODING']), 'gzip') !== false)
		{
			$this->compression_browser_support = true;
		}
		
		if(function_exists("gzencode") && ini_get("zlib.output_compression")=='')
		{
			$this->compression_server_support = true;
		}	

		if($this->compression_server_support == true && $this->compression_browser_support == true)
		{
			$this->compress_output = (bool) e107::getPref('compress_output', false);
		}


	}
	
	
	function setContent($content,$search=null,$replace=null)
	{
		global $_E107;
		if($content == 'buffer' && empty($_E107['cli']))
		{
			$this->length = ob_get_length();
			$this->content =  ob_get_clean();

			if(!empty($search) && !empty($replace))
			{
				$this->content = str_replace($search, $replace, $this->content);
				$this->length = strlen($this->content);
			}

		}
		else
		{
			$this->content = $content;
			$this->length = strlen($content);
		}

		$this->etag = md5($this->content);

	//print_a($this->length);

	//	return $this->content;

	}


	/**
	 * Return Content (with or without encoding)
	 * @return mixed
	 */
	function getOutput()
	{
		return $this->content;
	}
	
	function setHeader($header, $force=false, $response_code=null)
	{
		if(e107::isCli())
		{
			return null;
		}

		list($key,$val) = explode(':',$header,2);
		$this->headers[$key] = $val;
		header($header, $force, $response_code);
	}
			
	function debug() // needs to be disabled if PHP gzip is to work
	{
		if(!ADMIN)
		{
			return null;
		}

		
		$text = "<h3>Server Headers</h3>";
		$server = getallheaders();
		ksort($server);
		$text .= print_a($server,true);
		$text .= "<h3>e107 Headers</h3>";
		ksort($this->headers);
		$text .= print_a($this->headers,true);
		$text .= "<h4>Compress Output</h4>";
		$text .= print_a($this->compress_output,true);
		
		$server = array();
		foreach($_SERVER as $k=>$v)
		{
			if(strncmp($k, 'HTTP', 4) === 0)
			{
				$server[$k] = $v;	
			}	
		}

		$text .= "<h3>_SERVER</h3>";
		$text .= "<h4>zlib.output_compression</h4>";
		$text .= print_a(ini_get("zlib.output_compression"),true);


		$text .=print_a($server,true);

		if($this->compress_output === true)
		{

			$text = gzencode($text, $this->compression_level);
		}

		echo $text;
		
	}




	
	function send()
	{

		$canCache = e107::canCache();
		
	// $this->setHeader("Cache-Control: must-revalidate", true); 
		 
		if(isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['QUERY_STRING'] != 'logout' && $canCache && !deftrue('e_NOCACHE'))
		{
			// header("Cache-Control: must-revalidate", true);	
			if(e107::getPref('site_page_expires')) // TODO - allow per page
			{ 
				if (function_exists('date_default_timezone_set')) 
				{
				    date_default_timezone_set('UTC');
				}
				$time = time()+ (integer) e107::getPref('site_page_expires');
				$this->setHeader('Expires: '.gmdate("D, d M Y H:i:s", $time).' GMT', true);
			}
		}
		else
		{
			$canCache = false;
		}
		

		if($this->compress_output !== false)
		{
			$this->setHeader('ETag: "'.$this->etag.'-gzip"', true);
			$this->content = gzencode($this->content, $this->compression_level);
			$this->length = strlen($this->content);
			$this->setHeader('Content-Encoding: gzip', true);
			$this->setHeader("Content-Length: ".$this->length, true);
		} 
		else 
		{

			$this->setHeader('ETag: "'.$this->etag.'"', true);
			$this->setHeader("Content-Length: ".$this->length, true);
		}
		
		if(defset('X-POWERED-BY') !== false)
		{
			$this->setHeader("X-Powered-By: e107", true); // no less secure than e107-specific html. 
		}

		if($this->compression_server_support == true)
		{
			$this->setHeader('Vary: Accept-Encoding');	
		}
		else
		{
			$this->setHeader('Vary: Accept');
		}

		if(defset('X-FRAME-SAMEORIGIN') !== false)
		{
			$this->setHeader('X-Frame-Options: SAMEORIGIN');
		}

		// should come after the Etag header
		if ($canCache && isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			$IF_NONE_MATCH = str_replace('"','',$_SERVER['HTTP_IF_NONE_MATCH']);
			if($IF_NONE_MATCH == $this->etag || ($IF_NONE_MATCH == ($this->etag."-gzip")))
			{
				$this->setHeader('HTTP/1.1 304 Not Modified'); 
				exit();	
			}
		}
	}
					
				
			
		
	
	
}

$dbg->logTime('(After class2)');
