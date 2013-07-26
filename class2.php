<?php
/*
* e107 website system
*
* Copyright (C) 2008-2010 e107 Inc (e107.org)
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
if(isset($_E107['cli']) && !isset($_E107['debug']) && isset($_SERVER["HTTP_USER_AGENT"]))
{
	exit();
}

if(!isset($_E107['cli']))
{
	while (@ob_end_clean());  // destroy all ouput buffering
	ob_start();             // start our own.
	$oblev_at_start = ob_get_level(); 	// preserve when destroying globals in step C
}
//
// C: Find out if register globals is enabled and destroy them if so
// (DO NOT use the value of any variables before this point! They could have been set by the user)
//

// Can't be moved to e107, required here for e107_config vars security
$register_globals = true;
if(function_exists('ini_get'))
{
	$register_globals = ini_get('register_globals');
}

// Destroy! (if we need to)
if($register_globals == true)
{
	if(isset($_REQUEST['_E107'])) { unset($_E107); }
	while (list($global) = each($GLOBALS))
	{
		if (!preg_match('/^(_POST|_GET|_COOKIE|_SERVER|_FILES|_SESSION|GLOBALS|HTTP.*|_REQUEST|_E107|retrieve_prefs|eplug_admin|eTimingStart.*|oblev_.*)$/', $global))
		{
			unset($$global);
		}
	}
	unset($global);
}

// MOVED TO $e107->prepare_request()
// TODO - better ajax detection method (headers when possible)
//define('e_AJAX_REQUEST', isset($_REQUEST['ajax_used']));
//unset($_REQUEST['ajax_used']); // removed because it's auto-appended from JS (AJAX), could break something...
//
//if(isset($_E107['minimal']) || e_AJAX_REQUEST)
//{
//	$_e107vars = array('forceuserupdate', 'online', 'theme', 'menus', 'prunetmp');
//	foreach($_e107vars as $v)
//	{
//		$noname = 'no_'.$v;
//		if(!isset($_E107[$v]))
//		{
//			$_E107[$noname] = 1;
//		}
//		unset($_E107[$v]);
//	}
//}


// MOVED TO $e107->prepare_request()
// e107 uses relative url's, which are broken by "pretty" URL's. So for now we don't support / after .php
//if(($pos = strpos($_SERVER['PHP_SELF'], '.php/')) !== false) // redirect bad URLs to the correct one.
//{
//	$new_url = substr($_SERVER['PHP_SELF'], 0, $pos+4);
//	$new_loc = ($_SERVER['QUERY_STRING']) ? $new_url.'?'.$_SERVER['QUERY_STRING'] : $new_url;
//	header('Location: '.$new_loc);
//	exit();
//}
// If url contains a .php in it, PHP_SELF is set wrong (imho), affecting all paths.  We need to 'fix' it if it does.
//$_SERVER['PHP_SELF'] = (($pos = strpos($_SERVER['PHP_SELF'], '.php')) !== false ? substr($_SERVER['PHP_SELF'], 0, $pos+4) : $_SERVER['PHP_SELF']);

//
// D: Setup PHP error handling
//    (Now we can see PHP errors) -- but note that DEBUG is not yet enabled!
//
$error_handler = new error_handler();
set_error_handler(array(&$error_handler, 'handle_error'));

//
// E: Setup other essential PHP parameters
//
define('e107_INIT', true);

// MOVED TO $e107->prepare_request()
// setup some php options
//e107_ini_set('magic_quotes_runtime',     0);
//e107_ini_set('magic_quotes_sybase',      0);
//e107_ini_set('arg_separator.output',     '&amp;');
//e107_ini_set('session.use_only_cookies', 1);
//e107_ini_set('session.use_trans_sid',    0);


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

// MOVED TO e107->set_constants()
//define("MAGIC_QUOTES_GPC", (ini_get('magic_quotes_gpc') ? true : false));
//
//// Define the domain name and subdomain name.
//if($_SERVER['HTTP_HOST'] && is_numeric(str_replace(".","",$_SERVER['HTTP_HOST'])))
//{
//	$srvtmp = '';  // Host is an IP address.
//}
//else
//{
//	$srvtmp = explode('.',str_replace('www.', '', $_SERVER['HTTP_HOST']));
//}
//
//define('e_SUBDOMAIN', (count($srvtmp)>2 && $srvtmp[2] ? $srvtmp[0] : false)); // needs to be available to e107_config.
//
//if(e_SUBDOMAIN)
//{
//   	unset($srvtmp[0]);
//}
//
//define('e_DOMAIN',(count($srvtmp) > 1 ? (implode('.', $srvtmp)) : false)); // if it's an IP it must be set to false.
//
//unset($srvtmp);


// MOVED TO $e107->prepare_request()
//  Ensure thet '.' is the first part of the include path
//$inc_path = explode(PATH_SEPARATOR, ini_get('include_path'));
//if($inc_path[0] != '.')
//{
//	array_unshift($inc_path, '.');
//	$inc_path = implode(PATH_SEPARATOR, $inc_path);
//	e107_ini_set('include_path', $inc_path);
//}
//unset($inc_path);

//
// F: Grab e107_config, get directory paths and create $e107 object
//
@include(realpath(dirname(__FILE__).'/e107_config.php'));

if(!defined('e_POWEREDBY_DISABLE'))
{
	define('e_POWEREDBY_DISABLE', false);
}

if(isset($CLASS2_INCLUDE) && ($CLASS2_INCLUDE!=''))
{
	 require_once(realpath(dirname(__FILE__).'/'.$CLASS2_INCLUDE));
}

//define("MPREFIX", $mySQLprefix); moved to $e107->set_constants()

if(!isset($ADMIN_DIRECTORY))
{
  // e107_config.php is either empty, not valid or doesn't exist so redirect to installer..
  header('Location: install.php');
  exit();
}

//
// clever stuff that figures out where the paths are on the fly.. no more need for hard-coded e_HTTP :)
//
$tmp = realpath(dirname(__FILE__).'/'.$HANDLERS_DIRECTORY);

//Core functions - now API independent
@require_once($tmp.'/core_functions.php');
e107_require_once($tmp.'/e107_class.php');
unset($tmp);

$e107_paths = compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'DOWNLOADS_DIRECTORY','UPLOADS_DIRECTORY','SYSTEM_DIRECTORY', 'MEDIA_DIRECTORY','CACHE_DIRECTORY','LOGS_DIRECTORY');
$sql_info = compact('mySQLserver', 'mySQLuser', 'mySQLpassword', 'mySQLdefaultdb', 'mySQLprefix');
$e107 = e107::getInstance()->initCore($e107_paths, realpath(dirname(__FILE__)), $sql_info, varset($E107_CONFIG, array()));

e107::getSingleton('eIPHandler');			// This auto-handles bans etc


### NEW Register Autoload - do it asap
if(!function_exists('spl_autoload_register'))
{
	// PHP >= 5.1.2 required
	die('Fatal exception - spl_autoload_* required.');
}

// allow disable of autoloading - may be removed as e107::autoload_register() is flexible enough
if(!defset('E107_DISABLE_AUTOLOAD', false))
{
	e107::autoload_register(array('e107', 'autoload'));
}

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

// MOVED TO $e107->set_request()
//$inArray = array("'", ';', '/**/', '/UNION/', '/SELECT/', 'AS ');
//if (strpos($_SERVER['PHP_SELF'], 'trackback') === false)
//{
//	foreach($inArray as $res)
//	{
//		if(stristr($_SERVER['QUERY_STRING'], $res))
//		 {
//			die('Access denied.');
//		}
//	}
//}



//
// Start the parser; use it to grab the full query string
//

//DEPRECATED, BC
//$e107->url = e107::getUrl(); - caught by __get()
//TODO - find & replace $e107->url
//DEPRECATED, BC, $e107->tp caught by __get()
$tp = e107::getParser(); //TODO - find & replace $tp, $e107->tp

//define("e_QUERY", $matches[2]);
//define("e_QUERY", $_SERVER['QUERY_STRING']);


// MOVED TO $e107->set_request()
//$e_QUERY = str_replace("&","&amp;",$tp->post_toForm($e_QUERY));
//define('e_QUERY', $e_QUERY);

//$e_QUERY = e_QUERY;

// MOVED TO $e107->set_request()
//define('e_TBQS', $_SERVER['QUERY_STRING']);
//$_SERVER['QUERY_STRING'] = e_QUERY;

// MOVED TO $e107->set_constants()
//define('e_UC_PUBLIC', 0);
//define('e_UC_MAINADMIN', 250);
//define('e_UC_READONLY', 251);
//define('e_UC_GUEST', 252);
//define('e_UC_MEMBER', 253);
//define('e_UC_ADMIN', 254);
//define('e_UC_NOBODY', 255);

// MOVED TO $e107->set_urls() - DEPRECATED, use e107->getFolder()
//define('ADMINDIR', $ADMIN_DIRECTORY);

//
// H: Initialize debug handling
// (NO E107 DEBUG CONSTANTS OR CODE ARE AVAILABLE BEFORE THIS POINT)
// All debug objects and constants are defined in the debug handler
// i.e. from here on you can use E107_DEBUG_LEVEL or any
// E107_DBG_* constant for debug testing.
// TODO - rewrite the debug init phase, add e107 class getters
//
require_once(e_HANDLER.'debug_handler.php');

if(E107_DEBUG_LEVEL && isset($db_debug) && is_object($db_debug))
{
	$db_debug->Mark_Time('Start: Init ErrHandler');
}

//
// I: Sanity check on e107_config.php
//     e107_config.php upgrade check
// FIXME - obsolete check, rewrite it
if (!$ADMIN_DIRECTORY && !$DOWNLOADS_DIRECTORY)
{
	message_handler('CRITICAL_ERROR', 8, ': generic, ', 'e107_config.php');
	exit;
}

//
// J: MYSQL INITIALIZATION
//
e107::getSingleton('e107_traffic'); // We start traffic counting ASAP
//$eTraffic->Calibrate($eTraffic);

// e107_require_once(e_HANDLER.'mysql_class.php');

//DEPRECATED, BC, $e107->sql caught by __get()
$sql = e107::getDb(); //TODO - find & replace $sql, $e107->sql
$sql->db_SetErrorReporting(FALSE);

$sql->db_Mark_Time('Start: SQL Connect');
$merror=$sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);

// create after the initial connection.
//DEPRECATED, BC, call the method only when needed
$sql2 = e107::getDb('sql2'); //TODO find & replace all $sql2 calls

$sql->db_Mark_Time('Start: Prefs, misc tables');

//DEPRECATED, BC, call the method only when needed, $e107->admin_log caught by __get()
$admin_log = e107::getAdminLog(); //TODO - find & replace $admin_log, $e107->admin_log

if ($merror === 'e1')
{
	message_handler('CRITICAL_ERROR', 6, ': generic, ', 'class2.php');
	exit;
}
elseif ($merror === 'e2')
{
	message_handler("CRITICAL_ERROR", 7, ': generic, ', 'class2.php');
	exit;
}

//
// K: Load compatability mode.
//

/* PHP Compatabilty should *always* be on. */
e107_require_once(e_HANDLER.'php_compatibility_handler.php');

//
// L: Extract core prefs from the database
//
$sql->db_Mark_Time('Start: Extract Core Prefs');

// TODO - remove it from here, auto-loaded when required
e107_require_once(e_HANDLER.'cache_handler.php');

//DEPRECATED, BC, call the method only when needed, $e107->arrayStorage caught by __get()
e107_require_once(e_HANDLER.'arraystorage_class.php'); // ArrayData(); BC Fix only. 
$eArrayStorage = e107::getArrayStorage();  //TODO - find & replace $eArrayStorage with e107::getArrayStorage();

//DEPRECATED, BC, call the method only when needed, $e107->e_event caught by __get()
$e_event = e107::getEvent(); //TODO - find & replace $e_event, $e107->e_event

// TODO - DEPRECATED - remove
e107_require_once(e_HANDLER."pref_class.php");
$sysprefs = new prefs;

// Check core preferences
//FIXME - message_handler is dying after message_handler(CRITICAL_ERROR) call
e107::getConfig()->load(); // extra load, required if mysql handler already called e107::getConfig()
if(!e107::getConfig()->hasData())
{
	// Core prefs error - admin log
	e107::getAdminLog()->log_event('CORE_LAN8', 'CORE_LAN7', E_LOG_WARNING);

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
			e107::getAdminLog()->log_event('CORE_LAN8', 'CORE_LAN9', E_LOG_FATAL);

			message_handler('CRITICAL_ERROR', 3, __LINE__, __FILE__);
			// No old system, so point in the direction of resetcore :(
			message_handler('CRITICAL_ERROR', 4, __LINE__, __FILE__); //this will never appear till message_handler() is fixed

			exit;
		}
		else
		{
			// resurrect core from old prefs
			e107::getConfig()->loadData(e107::getConfig('core_old')->getPref(), false)
				->save(false, true);

			// resurrect core_backup from old prefs
			e107::getConfig('core_backup')->loadData(e107::getConfig('core_old')->getPref(), false)
				->save(false, true);
		}
	}

}

//DEPRECATED, BC, call e107::getPref/findPref() instead
$pref = e107::getPref();

//this could be part of e107->init() method now, prefs will be auto-initialized
//when proper called (e107::getPref())
// $e107->set_base_path(); moved to init().

//DEPRECATED, BC, call e107::getConfig('menu')->get('pref_name') only when needed
$menu_pref = e107::getConfig('menu')->getPref(); //extract menu prefs

// NEW - force ssl
if(e107::getPref('ssl_enabled') && !deftrue('e_SSL_DISABLE'))
{
	// NOTE: e_SSL_DISABLE check is here to help webmasters fix 'ssl_enabled'
	// if set by accident on site with no SSL support - just define it in e107_config.php
	if(strpos(e_REQUEST_URL, 'http://') === 0)
	{
		// e_REQUEST_URL and e_REQUEST_URI introduced
		$url = 'https://'.substr(e_REQUEST_URL, 7);
		header('Location: '.$url);
		exit;
	}
}

// $sql->db_Mark_Time('(Extracting Core Prefs Done)');

$sql->db_Mark_Time('Start: Init Language and detect changes');
$lng = e107::getLanguage(); // required for v1.0 BC. 
$lng->detect();

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
if($pref['redirectsiteurl'] && $pref['siteurl']) {

	if(isset($pref['multilanguage_subdomain']) && $pref['multilanguage_subdomain'])
	{
   		if(substr(e_REQUEST_URL, 7, 4)=='www.' || substr(e_REQUEST_URL, 8, 4)=='www.')
		{
			$self = e_REQUEST_URL;
			//if(e_QUERY){ $self .= '?'.e_QUERY; }
			$location = str_replace('://www.', '://', $self);
			header("Location: {$location}", true, 301); // send 301 header, not 302
			exit();
		}
	}
    else
	{
		// Find domain and port from user and from pref
		list($urlbase,$urlport) = explode(':',$_SERVER['HTTP_HOST'].':');
		if (!$urlport) { $urlport = $_SERVER['SERVER_PORT']; }
		if (!$urlport) { $urlport = 80; }
		$aPrefURL = explode('/',$pref['siteurl'],4);
		if (count($aPrefURL) > 2) // we can do this -- there's at least http[s]://dom.ain/whatever
		{
			$PrefRoot = $aPrefURL[2];
			list($PrefSiteBase,$PrefSitePort) = explode(':',$PrefRoot.':');
			if (!$PrefSitePort)
			{
				$PrefSitePort = ( $aPrefURL[0] == 'https:' ) ? 443 : 80;	// no port so set port based on 'scheme'
			}

			// Redirect only if
			// -- ports do not match (http <==> https)
			// -- base domain does not match (case-insensitive)
			// -- NOT admin area
			if (($urlport != $PrefSitePort || stripos($PrefSiteBase, $urlbase) === false) && strpos(e_REQUEST_SELF, ADMINDIR) === false)
			{
				$aeSELF = explode('/', e_REQUEST_SELF, 4);
				$aeSELF[0] = $aPrefURL[0];	// Swap in correct type of query (http, https)
				$aeSELF[1] = '';						// Defensive code: ensure http:// not http:/<garbage>/
				$aeSELF[2] = $aPrefURL[2];  // Swap in correct domain and possibly port
				$location = implode('/',$aeSELF).($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '');

			header("Location: {$location}", true, 301); // send 301 header, not 302
			exit();
			}
		}
	}
}

/**
 * Set the User's Language
 */
$sql->db_Mark_Time('Start: Set User Language');
// SESSION Needs to be started after:
// - Site preferences are available
// - Language detection (because of session.cookie_domain)
// to avoid multi-language 'access-denied' issues.
//session_start(); see e107::getSession() above
e107::getSession(); //init core _SESSION - actually here for reference only, it's done by language handler set() method
e107::getLanguage()->set();  // set e_LANGUAGE, USERLAN, Language Session / Cookies etc. requires $pref;

if(varset($pref['multilanguage']) && (e_LANGUAGE != $pref['sitelanguage']))
{
	$sql->mySQLlanguage  = e_LANGUAGE;
	$sql2->mySQLlanguage = e_LANGUAGE;
}

//do it only once and with the proper function
// e107_include_once(e_LANGUAGEDIR.e_LANGUAGE.'/'.e_LANGUAGE.'.php');
// e107_include_once(e_LANGUAGEDIR.e_LANGUAGE.'/'.e_LANGUAGE.'_custom.php');
include(e_LANGUAGEDIR.e_LANGUAGE.'/'.e_LANGUAGE.'.php'); // FASTEST - ALWAYS load
$customLan = e_LANGUAGEDIR.e_LANGUAGE.'/'.e_LANGUAGE.'_custom.php';
if(is_readable($customLan)) // FASTER - if exist, should be done 'once' by the core
{
	include($customLan);
}
unset($customLan);

$sql->db_Mark_Time('Start: Global Language Files');
if(isset($pref['lan_global_list']))
{
	foreach($pref['lan_global_list'] as $path)
	{
		e107::plugLan($path,'global',true);			
	}			
}



$sql->db_Mark_Time('Start: CHAP challenge');

$die = (e_AJAX_REQUEST == true) ? false : true; 
e107::getSession()
	->challenge() // Make sure there is a unique challenge string for CHAP login
	->check($die); // Token protection
unset($die);
	
//
// N: misc setups: online user tracking, cache
//
$sql->db_Mark_Time('Start: Misc resources. Online user tracking, cache');

//DEPRECATED, BC, call the method only when needed, $e107->ecache caught by __get()
$e107cache = e107::getCache(); //TODO - find & replace $e107cache, $e107->ecache

//DEPRECATED, BC, call the method only when needed, $e107->override caught by __get()
$override = e107::getSingleton('override', true); //TODO - find & replace $override, $e107->override

//DEPRECATED, BC, call the method only when needed, $e107->user_class caught by __get()
$e_userclass = e107::getUserClass();  //TODO - find & replace $e_userclass, $e107->user_class

//TODO - move the check to e107::notify()? What's the idea behind $pref['notify']?
if(isset($pref['notify']) && $pref['notify'] == true)
{
	e107_require_once(e_HANDLER.'notify_class.php');
}

//
// O: Start user session
//
$sql -> db_Mark_Time('Start: Init session');
init_session();			// Set up a lot of the user-related constants



//DEPRECATED but necessary. BC Fix.
function getip()
{
	return e107::getIPHandler()->ipDecode(USERIP);
}

// for multi-language these definitions needs to come after the language loaded.
define('SITENAME', trim($tp->toHTML($pref['sitename'], '', 'USER_TITLE,er_on')));
define('SITEBUTTON', $tp->replaceConstants($pref['sitebutton'],'abs'));
define('SITETAG', $tp->toHTML($pref['sitetag'], false, 'emotes_off,defs'));
define('SITEDESCRIPTION', $tp->toHTML($pref['sitedescription'], '', 'emotes_off,defs'));
define('SITEADMIN', $pref['siteadmin']);
define('SITEADMINEMAIL', $pref['siteadminemail']);
define('SITEDISCLAIMER', $tp->toHTML($pref['sitedisclaimer'], '', 'emotes_off,defs'));
define('SITECONTACTINFO', $tp->toHTML($pref['sitecontactinfo'], true, 'emotes_off,defs'));
define('SITEEMAIL', vartrue($pref['replyto_email'],$pref['siteadminemail']));
define('USER_REGISTRATION', vartrue($pref['user_reg'],false)); // User Registration System Active or Not. 

if(is_array($pref['xurl']))
{
	define('XURL_FACEBOOK', vartrue($pref['xurl']['facebook'], false));
	define('XURL_TWITTER', vartrue($pref['xurl']['twitter'], false));
	define('XURL_YOUTUBE', vartrue($pref['xurl']['youtube'], false));
	define('XURL_GOOGLE', vartrue($pref['xurl']['google'], false));
	define('XURL_LINKEDIN', vartrue($pref['xurl']['linkedin'], false));
	define('XURL_GITHUB', vartrue($pref['xurl']['github'], false));
	define('XURL_FLICKR', vartrue($pref['xurl']['flickr'], false));
	define('XURL_INSTAGRAM', vartrue($pref['xurl']['instagram'], false));
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

$js_body_onload = array();			// Initialise this array in case a module wants to add to it

// Load e_modules after all the constants, but before the themes, so they can be put to use.
if(isset($pref['e_module_list']) && $pref['e_module_list'])
{
	foreach ($pref['e_module_list'] as $mod)
	{
		if (is_readable(e_PLUGIN."{$mod}/e_module.php"))
		{
			require_once(e_PLUGIN."{$mod}/e_module.php");
 		}
	}
}



//
// P: THEME LOADING
//

$sql->db_Mark_Time('Start: Load Theme');

//###########  Module redefinable functions ###############
if (!function_exists('checkvalidtheme'))
{

	function checkvalidtheme($theme_check)
	{
		// arg1 = theme to check
		//global $ADMIN_DIRECTORY, $tp, $e107;
		$e107 = e107::getInstance();
		$tp = e107::getParser();
		$ADMIN_DIRECTORY = $e107->getFolder('admin');
		
		// e_QUERY not set when in single entry mod
		if (ADMIN && strpos($_SERVER['QUERY_STRING'], 'themepreview') !== false)
		{
			list($action, $id) = explode('.', $_SERVER['QUERY_STRING']);

	   		require_once(e_HANDLER.'theme_handler.php');
			$themeobj = new themeHandler;
            $themeArray = $themeobj->getThemes('id');
 			$themeDef = $themeobj->findDefault($themeArray[$id]);
		
			$id = intval($id);
			
            define('THEME_LAYOUT',$themeDef);

			define('PREVIEWTHEME', e_THEME.$themeArray[$id].'/');
			define('PREVIEWTHEMENAME', $themeArray[$id]);
			define('THEME', e_THEME.$themeArray[$id].'/');
			define('THEME_ABS', e_THEME_ABS.$themeArray[$id].'/');
			
	
			
			return;
		}
		if (@fopen(e_THEME.$theme_check.'/theme.php', 'r'))
		{
			define('THEME', e_THEME.$theme_check.'/');
			define('THEME_ABS', e_THEME_ABS.$theme_check.'/');
			$e107->site_theme = $theme_check;
		}
		else
		{
			function search_validtheme()
			{
				$e107 = e107::getInstance();
				$th = substr(e_THEME, 0, -1);
				$handle = opendir($th);
				while ($file = readdir($handle))
				{
					if (is_dir(e_THEME.$file) && is_readable(e_THEME.$file.'/theme.php'))
					{
						closedir($handle);
						$e107->site_theme = $file;
						return $file;
					}
				}
				closedir($handle);
			}
			$e107tmp_theme = search_validtheme();
			define('THEME', e_THEME.$e107tmp_theme.'/');
			define('THEME_ABS', e_THEME_ABS.$e107tmp_theme.'/');
			if (ADMIN && strpos(e_SELF, $ADMIN_DIRECTORY) === false)
			{
				echo '<script>alert("'.$tp->toJS(CORE_LAN1).'")</script>';
				$tm = e107::getSingleton('themeHandler');
				$tm->setTheme($e107tmp_theme);
			//	$config = e107::getConfig();
			//	$config->set('sitetheme','core');
				
			}
		}
		$themes_dir = $e107->getFolder('themes');
		$e107->http_theme_dir = "{$e107->server_path}{$themes_dir}{$e107->site_theme}/";
	}
}

//
// Q: ALL OTHER SETUP CODE
//
$sql->db_Mark_Time('Start: Misc Setup');

//------------------------------------------------------------------------------------------------------------------------------------//
if (!class_exists('e107table', false))
{
	/**
	 *	@package e107
	 */
	class e107table
	{

    	public 	$eMenuCount = 0;
		public 	$eMenuArea;
		public 	$eMenuTotal = array();
		public 	$eSetStyle;
		private $themeClass = '';
		private $adminThemeClass = '';
		public  $frontend = null;
		
		
		function __construct()
		{
		//	$this->themeClass 		= e107::getPref('sitetheme')."_theme"; // disabled at the moment. 
			$this->adminThemeClass 	= e107::getPref('admintheme')."_admintheme";	// Check for a class. 
		}
		
		/**
		 * @param $caption string caption text
		 * @param $text body text
		 * @param $mode unique identifier
		 * @param $return boolean : return the html instead of echo it. 
		 * @return null
		 */
		public function tablerender($caption, $text, $mode = 'default', $return = false)
		{
			$override_tablerender = e107::getSingleton('override', e_HANDLER.'override_class.php')->override_check('tablerender');

			if ($override_tablerender)
			{
				$result = call_user_func($override_tablerender, $caption, $text, $mode, $return);

				if ($result == 'return')
				{
					return '';
				}
				extract($result);
			}

			if ($return)
			{
            	if(!empty($text) && $this->eMenuArea)
				{
					$this->eMenuCount++;
				}
				
				ob_start();
				$this->tablestyle($caption, $text, $mode);	
				$ret=ob_get_contents();
				ob_end_clean();

				return $ret;

			}
			else
			{
            	if(!empty($text) && $this->eMenuArea)
		 		{
	         		$this->eMenuCount++;
			   	}
				
				$this->tablestyle($caption, $text, $mode);
				return '';
			}
		}




		/**
		 * Output the styled template. 
		 * @param $caption
		 * @param $text
		 * @param $mode
		 */
		private function tablestyle($caption, $text, $mode)
		{	
		
			if(class_exists($this->adminThemeClass) && ($this->frontend == false))
			{
				$thm = new $this->adminThemeClass();	
			}
			elseif(class_exists($this->themeClass)) // disabled at the moment. 
			{
				$thm = new $this->themeClass();
			}
			
			if(is_object(vartrue($thm)))
			{
				$thm->tablestyle($caption, $text, $mode, array('menuArea'=>$this->eMenuArea, 'menuCount'=>$this->eMenuCount,	'menuTotal'=>varset($this->eMenuTotal[$this->eMenuArea]), 'setStyle'=>$this->eSetStyle));
			}
			else 
			{
				tablestyle($caption, $text, $mode, array('menuArea'=>$this->eMenuArea,'menuCount'=>$this->eMenuCount,'menuTotal'=>varset($this->eMenuTotal[$this->eMenuArea]),'setStyle'=>$this->eSetStyle));	
			}

		}


	}

	


}
//#############################################################

//DEPRECATED, BC, call the method only when needed, $e107->ns caught by __get()
$ns = e107::getRender(); //TODO - find & replace $ns, $e107->ns

// EONE-134 - bad e_module could destroy e107 instance
$e107 = e107::getInstance();		// Is this needed now?
e107::getIPHandler()->ban();

if(varset($pref['force_userupdate']) && USER && !isset($_E107['no_forceuserupdate']) && $_SERVER['QUERY_STRING'] !== 'logout')
{
	if(force_userupdate($currentUser))
	{
	  header('Location: '.SITEURL.'usersettings.php?update');
	  exit();
	}
}

$sql->db_Mark_Time('Start: Signup/splash/admin');


if(($pref['membersonly_enabled'] && !isset($_E107['allow_guest'])) || $pref['maintainance_flag'])
{
	//XXX move force_userupdate() also?
	e107::getRedirect()->checkMaintenance();
	e107::getRedirect()->checkMembersOnly();
}

// ------------------------------------------------------------------------

if(!isset($_E107['no_prunetmp']))
{
	$sql->db_Delete('tmp', 'tmp_time < '.(time() - 300)." AND tmp_ip!='data' AND tmp_ip!='submitted_link'");
}


$sql->db_Mark_Time('(Start: Login/logout/ban/tz)');


if (isset($_POST['userlogin']) || isset($_POST['userlogin_x']))
{
	e107::getUser()->login($_POST['username'], $_POST['userpass'], $_POST['autologin'], varset($_POST['hashchallenge'],''), false);
//	e107_require_once(e_HANDLER.'login.php');
//	$usr = new userlogin($_POST['username'], $_POST['userpass'], $_POST['autologin'], varset($_POST['hashchallenge'],''));
}



// $_SESSION['ubrowser'] check not needed anymore - see session handler
// e_QUERY not defined in single entry mod
if (($_SERVER['QUERY_STRING'] == 'logout')/* || (($pref['user_tracking'] == 'session') && isset($_SESSION['ubrowser']) && ($_SESSION['ubrowser'] != $ubrowser))*/)
{
	if (USER)
	{
		if (check_class(varset($pref['user_audit_class'],'')))
		{  // Need to note in user audit trail
			$admin_log->user_audit(USER_AUDIT_LOGOUT, '');
		}
	}

	// $ip = e107::getIPHandler()->getIP(FALSE);			Appears to not be used, so removed
	$udata = (USER === true ? USERID.'.'.USERNAME : '0');

	// TODO - should be done inside online handler, more core areas need it (session handler for example)
	if (isset($pref['track_online']) && $pref['track_online'])
	{
		$sql->db_Update('online', "online_user_id = 0, online_pagecount=online_pagecount+1 WHERE online_user_id = '{$udata}'");
	}
	
	// earlier event trigger with user data still available 
	e107::getEvent()->trigger('logout');
	
	// first model logout and session destroy..
	e107::getUser()->logout();
	
	// it might be removed soon
	if ($pref['user_tracking'] == 'session')
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

/*
* Calculate time zone offset, based on session cookie set in e107.js.
* (Buyer beware: this may be wrong for the first pageview in a session,
* which is while the user is logged out, so not a problem...)
*
* Time offset is SECONDS. Seconds is much better than hours as a base,
* as some places have 30 and 45 minute time zones.
* It matches user clock time, instead of only time zones.
* Add the offset to MySQL/server time to get user time.
* Subtract the offset from user time to get server time.
*
*/

$e_deltaTime=0;

if (isset($_COOKIE['e107_tdOffset']))
{
	// Actual seconds of delay. See e107.js and footer_default.php
	$e_deltaTime = (15*floor((intval($_COOKIE['e107_tdOffset']) /60)/15))*60; // Delay in seconds rounded to the lowest quarter hour
}

if (isset($_COOKIE['e107_tzOffset']))
{
	// Relative client-to-server time zone offset in seconds.
	$e_deltaTime += (-(intval($_COOKIE['e107_tzOffset']) * 60 + date("Z")));
}

define('TIMEOFFSET', $e_deltaTime);

// ----------------------------------------------------------------------------
$sql->db_Mark_Time('(Start: Find/Load Theme)');

if(e_ADMIN_AREA) // Load admin phrases ASAP
{
	e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_admin.php');
}


if(!defined('THEME'))
{

	if (e_ADMIN_AREA && varsettrue($pref['admintheme']))
	{
		//We have now e_IFRAME mod and USER_AREA force
		// && (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE)

/*	  if (strpos(e_SELF, "newspost.php") !== FALSE)
	  {
		define("MAINTHEME", e_THEME.$pref['sitetheme']."/");		MAINTHEME no longer used in core distribution
	  }  */
		checkvalidtheme($pref['admintheme']);
	}
	elseif (USERTHEME !== false/* && USERTHEME != 'USERTHEME'*/ && !e_ADMIN_AREA)
	{
		checkvalidtheme(USERTHEME);
	}
	else
	{
		checkvalidtheme($pref['sitetheme']);
	}


}

$theme_pref = varset($pref['sitetheme_pref']);
// --------------------------------------------------------------
$sql->db_Mark_Time('(Start: Find/Load Theme-Layout)'); // needs to run after checkvalidtheme() (for theme previewing).

if(!defined("THEME_LAYOUT"))
{
    $def = "";   // no custom pages found yet.
    $cusPagePref = (varset($user_pref['sitetheme_custompages'])) ? $user_pref['sitetheme_custompages'] : varset($pref['sitetheme_custompages']);

	if(is_array($cusPagePref) && count($cusPagePref)>0)  // check if we match a page in layout custompages.
	{
	    //e_SELF.(e_QUERY ? '?'.e_QUERY : '');
		$c_url = str_replace(array('&amp;'), array('&'), e_REQUEST_URL);//.(e_QUERY ? '?'.e_QUERY : '');// mod_rewrite support
		// FIX - check against urldecoded strings
		$c_url = rawurldecode($c_url);
		
    	foreach($cusPagePref as $lyout=>$cusPageArray)
		{
			if(!is_array($cusPageArray)) { continue; }
			
			// NEW - Front page template check - early
			if(in_array('FRONTPAGE', $cusPageArray) && ($c_url == SITEURL || rtrim($c_url, '/') == SITEURL.'index.php'))
			{
				$def = $lyout;
				break;
			}
   			foreach($cusPageArray as $kpage)
			{
				if(substr($kpage, -1) === '!' )
				{
					$kpage = rtrim($kpage, '!');
					if(substr($c_url, - strlen($kpage)) === $kpage)
					{
						$def =  $lyout;
						break 2;
					}
					continue;
				}

				if ($kpage && ($kpage == defset('e_PAGE') || strpos($c_url, $kpage) !== false))
				{
            	 //	$def = ($lyout) ? $lyout : "legacyCustom";
					$def =  $lyout;
					break 2;
				}
			}
		}
	}

	/* Done via e_IFRAME and USER_AREA force combination, check moved to menu.php
	if(strpos(e_SELF.'?'.e_QUERY, $ADMIN_DIRECTORY. 'menus.php?configure')!==FALSE)
	{
		$menus_equery = explode('.', e_QUERY);
		$def = $menus_equery[1];
	}
	*/

	if(varset($pref['themecss']) && file_exists(THEME.$pref['themecss']))
	{
		define("THEME_STYLE", $pref['themecss']);
	}
	else
	{
		define("THEME_STYLE", 'style.css');
	}	

    if($def) // custom-page layout.
	{
    	define("THEME_LAYOUT",$def);
	}
	else // default layout.
	{
    	$deflayout = (!isset($user_pref['sitetheme_deflayout'])) ? varset($pref['sitetheme_deflayout']) : $user_pref['sitetheme_deflayout'];
		/**
		 * @ignore
		 */
		define("THEME_LAYOUT",$deflayout);  // default layout.
	}

    unset($def,$lyout,$cusPagePref,$menus_equery,$deflayout);

}

// -----------------------------------------------------------------------

$sql->db_Mark_Time('Start: Get menus');
if(!isset($_E107['no_menus']))
{
	e107::getMenu()->init();
}

// here we USE the theme
if(e_ADMIN_AREA)
{
	if(file_exists(THEME.'admin_theme.php')&&(strpos(e_SELF.'?'.e_QUERY, $ADMIN_DIRECTORY.'menus.php?configure')===FALSE)) // no admin theme when previewing.
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
	require_once (THEME.'theme.php');
	if(isset($SC_WRAPPER))
	{
		e107::scStyle($SC_WRAPPER);
	}
}


//----------------------------
//	Load shortcode handler
//----------------------------
// ********* This is probably a bodge! Work out what to do properly. Has to be done when $pref valid
//FIXED - undefined $register_sc
//$tp->sch_load(); - will be auto-initialized by first $tp->e_sc call - see e_parse->__get()

/*
$exclude_lan = array('lan_signup.php');  // required for multi-language.

if ($inAdminDir)
{
	e107_include_once(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);
	e107_include_once(e_LANGUAGEDIR.'English/admin/lan_'.e_PAGE);
}
elseif (!in_array('lan_'.e_PAGE,$exclude_lan) && !$isPluginDir)
{
	e107_include_once(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);
	e107_include_once(e_LANGUAGEDIR.'English/lan_'.e_PAGE);
}
*/

if ($pref['anon_post'] ? define('ANON', true) : define('ANON', false));

if (empty($pref['newsposts']) ? define('ITEMVIEW', 15) : define('ITEMVIEW', $pref['newsposts']));

if ($pref['antiflood1'] == 1)
{
	define('FLOODPROTECT', TRUE);
	define('FLOODTIMEOUT', max(varset($pref['antiflood_timeout'], 10), 3));
}
else
{
	/**
	 * @ignore
	 */
	define('FLOODPROTECT', FALSE);
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

//define('LOGINMESSAGE', ''); - not needed, breaks login messages
define('OPEN_BASEDIR', (ini_get('open_basedir') ? true : false));
define('SAFE_MODE', (ini_get('safe_mode') ? true : false));
define('FILE_UPLOADS', (ini_get('file_uploads') ? true : false));
define('INIT', true);
if(isset($_SERVER['HTTP_REFERER']))
{
	$tmp = explode("?", $_SERVER['HTTP_REFERER']);
	define('e_REFERER_SELF',($tmp[0] == e_REQUEST_SELF));
	unset($tmp);
}
else
{
	/**
	 * @ignore
	 */
	define('e_REFERER_SELF', FALSE);
}

//BC, DEPRECATED - use e107::getDateConvert(), catched by __autoload as well
/*if (!class_exists('convert'))
{
	require_once(e_HANDLER.'date_handler.php');
}*/

//@require_once(e_HANDLER."IPB_int.php");
//@require_once(e_HANDLER."debug_handler.php");
//-------------------------------------------------------------------------------------------------------------------------------------------
function js_location($qry)
{
	global $error_handler;
	if (count($error_handler->errors))
	{
		echo $error_handler->return_errors();
		exit;
	}
	else
	{
		echo "<script type='text/javascript'>document.location.href='{$qry}'</script>\n";
		exit;
	}
}

function check_email($email)
{
	return preg_match("/^([_a-zA-Z0-9-+]+)(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+)(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,6})$/" , $email) ? $email : false;
}

//---------------------------------------------------------------------------------------------------------------------------------------------
// $var is a single class number or name, or a comma-separated list of the same.
// If a class is prefixed with '-' this means 'exclude' - returns FALSE if the user is in this class (overrides 'includes').
// Otherwise returns TRUE if the user is in any of the classes listed in $var.
function check_class($var, $userclass = USERCLASS_LIST, $uid = 0)
{
	$e107 = e107::getInstance();
	if($var == e_LANGUAGE)
	{
		return TRUE;
	}

	if(is_numeric($uid) && $uid > 0)
	{	// userid has been supplied, go build that user's class list
		$userclass = class_list($uid);
	}

	if ($userclass == '')
	{
		return FALSE;
	}

	$class_array = !is_array($userclass) ? explode(',', $userclass) : $userclass;

	$varList = !is_array($var) ? explode(',', $var) : $var;
	$latchedAccess = FALSE;

	foreach($varList as $v)
	{
		$v = trim($v);
		$invert = FALSE;
		//value to test is a userclass name (or garbage, of course), go get the id
		if( ! is_numeric($v))
		{
			if (substr($v, 0, 1) == '-')
			{
				$invert = TRUE;
				$v = substr($v, 1);
			}
			$v = $e107->user_class->ucGetClassIDFromName($v);
		}
		elseif ($v < 0)
		{
			$invert = TRUE;
			$v = -$v;
		}
		if ($v !== FALSE)
		{
			// Ignore non-valid userclass names
			if (in_array($v, $class_array) || ($v === '0') || ($v === 0))
			{
				if ($invert)
				{
					return FALSE;
				}
				$latchedAccess = TRUE;
			}
			elseif ($invert && count($varList) == 1)
			{
				// Handle scenario where only an 'exclude' class is passed
				$latchedAccess = TRUE;
			}
		}
	}
	return $latchedAccess;
}



function getperms($arg, $ap = ADMINPERMS)
{
	// $ap = "4"; // Just for testing.

	if(!ADMIN || trim($ap) === '')
	{
		return FALSE;
	}

	if($arg === 0) // Common-error avoidance with getperms(0)
	{
		$arg = '0';
	}

	if ($ap === '0' || $ap === '0.') // BC fix.
	{
		return TRUE;
	}

	if ($arg == 'P' && preg_match("#(.*?)/".e107::getInstance()->getFolder('plugins')."(.*?)/(.*?)#", e_SELF, $matches))
	{
		$sql = e107::getDb('psql');

		// FIXME - cache it, avoid sql query here
		if ($sql->db_Select('plugin', 'plugin_id', "plugin_path = '".$matches[2]."' LIMIT 1 "))
		{
			$row = $sql->db_Fetch();
			$arg = 'P'.$row['plugin_id'];
		}
	}

	$ap_array = explode('.',$ap);

	if(in_array($arg,$ap_array,FALSE))
	{
		return TRUE;
	}
    elseif(strpos($arg, "|")) // check for multiple perms - separated by '|'.
	{
    	$tmp = explode("|", $arg);
		foreach($tmp as $val)
		{
		   	if(in_array($val,$ap_array))
			{
				return TRUE;
			}
		}
	}
	else
	{
		return FALSE;
	}
}

/**
 * @DEPRECATED
 * Get the user data from user and user_extended tables
 * SO MUCH DEPRECATED! Use e107::user($uid);
 *
 *
 * @return array
 */
function get_user_data($uid, $extra = '')
{
	if(e107::getPref('developer'))
	{
		e107::getAdminLog()->log_event(
			'Deprecated call - get_user_data()',
			'Call to deprecated function get_user_data() (class2.php)',
			E_LOG_INFORMATIVE,
			'DEPRECATED'
		);
		// TODO - debug screen Deprecated Functions (e107)
		e107::getMessage()->addDebug('Deprecated get_user_data() backtrace:<pre>'."\n".print_r(debug_backtrace(), true).'</pre>');
	}

	$var = array();
	$user = e107::getSystemUser($uid, true);
	if($user)
	{
		$var = $user->getUserData();
	}
	return $var;

	/*$e107 = e107::getInstance();
	$uid = (int)$uid;
	$var = array();
	if($uid == 0) { return $var; }
	if($ret = getcachedvars("userdata_{$uid}"))
	{
		return $ret;
	}

	$qry = "
	SELECT u.*, ue.* FROM `#user` AS u
	LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = u.user_id
	WHERE u.user_id = {$uid} {$extra}
	";
	if (!$e107->sql->db_Select_gen($qry))
	{
		$qry = "SELECT * FROM #user AS u WHERE u.user_id = {$uid} {$extra}";
		if(!$e107->sql->db_Select_gen($qry))
		{
			return FALSE;
		}
	}
	$var = $e107->sql->db_Fetch(MYSQL_ASSOC);

	if(!$e107->extended_struct = getcachedvars('extended_struct'))
	{
		if($tmp = $e107->ecache->retrieve_sys('nomd5_extended_struct'))
		{
			$e107->extended_struct = $e107->arrayStorage->ReadArray($tmp);
		}
		else
		{
			$qry = 'SHOW COLUMNS FROM `#user_extended` ';
			if($e107->sql->db_Select_gen($qry))
			{
				while($row = $e107->sql->db_Fetch())
				{
					$e107->extended_struct[] = $row;
				}
			}
			$tmp = $e107->arrayStorage->WriteArray($e107->extended_struct, false);
			$e107->ecache->set_sys('nomd5_extended_struct', $tmp);
			unset($tmp);
		}
		if(isset($e107->extended_struct))
		{
			cachevars('extended_struct', $e107->extended_struct);
		}
	}

	if(isset($e107->extended_struct) && is_array($e107->extended_struct))
	{
		foreach($e107->extended_struct as $row)
		{
			if($row['Default'] != '' && ($var[$row['Field']] == NULL || $var[$row['Field']] == '' ))
			{
				$var[$row['Field']] = $row['Default'];
			}
		}
	}


	if ($var['user_perms'] == '0.') $var['user_perms'] = '0';		// Handle some legacy situations
	//===========================================================
	$var['user_baseclasslist'] = $var['user_class'];			// Keep track of which base classes are in DB
	// Now look up the 'inherited' user classes
	$var['user_class'] = $e107->user_class->get_all_user_classes($var['user_class']);

	//===========================================================

	cachevars("userdata_{$uid}", $var);
	return $var;
	*/
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//SO MUCH DEPRECATED - use e107::getConfig(alias)->save() instead
function save_prefs($table = 'core', $uid = USERID, $row_val = '')
{
	global $pref, $user_pref, $tp, $PrefCache, $sql, $eArrayStorage, $theme_pref;

	if(e107::getPref('developer'))
	{
		e107::getAdminLog()->log_event(
			'Deprecated call - save_prefs()',
			'Call to deprecated function save_prefs() (class2.php)',
			E_LOG_INFORMATIVE,
			'DEPRECATED'
		);
		// TODO - debug screen Deprecated Functions (e107)
		e107::getMessage()->addDebug('Deprecated save_prefs() backtrace:<pre>'."\n".print_r(debug_backtrace(), true).'</pre>');
	}

	switch($table)
	{
		case 'core':
			//brute load, force update
			return e107::getConfig()->loadData($pref, false)->save(false, true);
			break;

		case 'theme':
			//brute load, force update
			return e107::getConfig()->set('sitetheme_pref', $theme_pref)->save(false, true);
			break;

		default:
			$_user_pref = $tp->toDB($user_pref, true, true, 'pReFs');
			$tmp = $eArrayStorage->WriteArray($_user_pref);
			$sql->db_Update('user', "user_prefs='$tmp' WHERE user_id=".intval($uid));
			return $tmp;
			break;
	}
	/*
  if ($table == 'core')
  {
		if ($row_val == '')
		{ 	// Save old version as a backup first
	  		$sql->db_Select_gen("REPLACE INTO `#core` (e107_name,e107_value) values ('SitePrefs_Backup', '".addslashes($PrefCache)."') ");

		  	// Now save the updated values
		  	// traverse the pref array, with toDB on everything
		  	$_pref = $tp->toDB($pref, true, true);
		  	// Create the data to be stored
	  		if($sql->db_Select_gen("REPLACE INTO `#core` (e107_name,e107_value) values ('SitePrefs', '".$eArrayStorage->WriteArray($_pref)."') "))
			{
		  		ecacXXXhe::clear_sys('Config_core');
				return true;
			}
			else
			{
            	return false;
			}
		}
  }
  elseif($table == "theme")
  {
  		$pref['sitetheme_pref'] = $theme_pref;
		save_prefs();
  }
  else
  {
	 //	$_user_pref = $tp -> toDB($user_pref);
	 //	$tmp=addslashes(serialize($_user_pref));
	 	$_user_pref = $tp->toDB($user_pref, true, true);
	 	$tmp = $eArrayStorage->WriteArray($_user_pref);
		$sql->db_Update('user', "user_prefs='$tmp' WHERE user_id=".intval($uid));
		return $tmp;
  }
	*/
}


//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//DEPRECATED - use e107::setRegistry()
function cachevars($id, $var)
{
	e107::setRegistry('core/cachedvars/'.$id, $var);
}
//DEPRECATED - use e107::getRegistry()
function getcachedvars($id)
{
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
		$sql=new db;

		if (FLOODPROTECT == true)
		{
			$sql->db_Select($table, '*', 'ORDER BY '.$orderfield.' DESC LIMIT 1', 'no_where');
			$row=$sql->db_Fetch();
			return ($row[$orderfield] > (time() - FLOODTIMEOUT) ? false : true);
		}
		else
		{
			return TRUE;
		}
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


	global $user_pref, $currentUser;

	$e107 = e107::getInstance();

	// New user model
	$user = e107::getUser();

	define('USERIP', e107::getIPHandler()->getIP(FALSE));
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
		define('USERTHEME', false);
		define('ADMIN', true);
		define('GUEST', false);
		define('USERCLASS', '');
		define('USEREMAIL', '');
		define('USERCLASS_LIST', '');
		define('USERCLASS', '');
		define('USERJOINED', '');
		return;
	}

	if ($user->hasBan())
	{
		$msg = e107::findPref('ban_messages/6');
		if($msg) echo e107::getParser()->toHTML($msg);
		exit;
	}

	if (!$user->isUser())
	{
		define('USER', false);
		define('USERID', 0);
		define('USERTHEME', false);
		define('ADMIN', false);
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
		define('USERVISITS', $user->get('user_visits'));
		define('USERSIGNATURE', $user->get('user_signature'));

		define('ADMIN', $user->isAdmin());
		define('ADMINID', $user->getAdminId());
		define('ADMINNAME', $user->getAdminName());
		define('ADMINPERMS', $user->getAdminPerms());
		define('ADMINEMAIL', $user->getAdminEmail());
		define('ADMINPWCHANGE', $user->getAdminPwchange());
		if(ADMIN) // XXX - why for admins only?
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
				if(e107::getPref('sitetheme') != $_POST['sitetheme'])
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
   				->save(false);
		}
		// XXX could go to e_user class as well END
		if(!defined("USERTHEME"	))
		{
			define('USERTHEME', ($user->getPref('sitetheme') && file_exists(e_THEME.$user->getPref('sitetheme')."/theme.php") ? $user->getPref('sitetheme') : false));				
		}
		
		$user_pref = $user->getPref();
	}

	define('USERCLASS_LIST', $user->getClassList(true));
	define('e_CLASS_REGEXP', $user->getClassRegex());
	define('e_NOBODY_REGEXP', '(^|,)'.e_UC_NOBODY.'(,|$)');

		/* XXX - remove it after everything is working well!!
		if(!isset($_E107['cli']))
		{
			list($uid, $upw)=(isset($_COOKIE[e_COOKIE]) && $_COOKIE[e_COOKIE] ? explode(".", $_COOKIE[e_COOKIE]) : explode(".", $_SESSION[e_COOKIE]));
        }
		else // FIXME - this will never happen - see above
		{
        	list($uid, $upw)= explode('.', $cli_log);
		}

		if (empty($uid) || empty($upw))
		{
			//$_SESSION[] = e_SELF."?".e_QUERY;

			cookie(e_COOKIE, '', (time() - 2592000));
			$_SESSION[e_COOKIE] = "";
			session_destroy();
			define('ADMIN', false);
			define('USER', false);
			define('USERID', 0);
			define('USERCLASS', '');
			define('USERCLASS_LIST', class_list());
			define('LOGINMESSAGE', CORE_LAN10.'<br /><br />');
			return (false);
		}

		$result = get_user_data($uid);
		if(is_array($result) && md5($result['user_password']) == $upw)
		{

			define('USERID', $result['user_id']);
			define('USERNAME', $result['user_name']);
			define('USERURL', (isset($result['user_homepage']) ? $result['user_homepage'] : false));
			define('USEREMAIL', $result['user_email']);
			define('USER', true);
			define('USERCLASS', $result['user_class']);
			//define('USERVIEWED', $result['user_viewed']);  - removed from the DB
			define('USERIMAGE', $result['user_image']);
			define('USERPHOTO', $result['user_sess']);

			$update_ip = ($result['user_ip'] != USERIP ? ", user_ip = '".USERIP."'" : "");
			if($result['user_currentvisit'] + 3600 < time() || !$result['user_lastvisit'])
			{
				$result['user_lastvisit'] = $result['user_currentvisit'];
				$result['user_currentvisit'] = time();
				$sql->db_Update('user', "user_visits = user_visits + 1, user_lastvisit = '{$result['user_lastvisit']}', user_currentvisit = '{$result['user_currentvisit']}' {$update_ip} WHERE user_id='".USERID."' ");
			}
			else
			{
				$result['user_currentvisit'] = time();
				$sql->db_Update('user', "user_currentvisit = '{$result['user_currentvisit']}'{$update_ip} WHERE user_id='".USERID."' ");
			}

			$currentUser = $result;
			$currentUser['user_realname'] = $result['user_login']; // Used by force_userupdate
			$e107->currentUser = &$currentUser;
			define('USERLV', $result['user_lastvisit']);

			if ($result['user_ban'] == 1)
			{
			  if (isset($pref['ban_messages']))
			  {
				echo $tp->toHTML(varsettrue($pref['ban_messages'][6]));		// Show message if one set
			  }
			  exit;
			}

			if ($result['user_admin'])
			{
				define('ADMIN', TRUE);
				define('ADMINID', $result['user_id']);
				define('ADMINNAME', $result['user_name']);
				define('ADMINPERMS', $result['user_perms']);
				define('ADMINEMAIL', $result['user_email']);
				define('ADMINPWCHANGE', $result['user_pwchange']);
				e107::getRedirect()->setPreviousUrl();

			}
			else
			{
				define('ADMIN', FALSE);
			}

			if($result['user_prefs'])
			{
               $user_pref =	(substr($result['user_prefs'],0,5) == "array") ? $eArrayStorage->ReadArray($result['user_prefs']) : unserialize($result['user_prefs']);
			}




			$tempClasses = class_list();
			if (check_class(varset($pref['allow_theme_select'],FALSE), $tempClasses))
			{	// User can set own theme
 				if (isset($_POST['settheme']))
				{
					if($pref['sitetheme'] != $_POST['sitetheme'])
					{
                		require_once(e_HANDLER."theme_handler.php");
						$utheme = new themeHandler;
	                    $ut = $utheme->themeArray[$_POST['sitetheme']];

                     	$user_pref['sitetheme'] 			= $_POST['sitetheme'];
						$user_pref['sitetheme_custompages'] = $ut['custompages'];
						$user_pref['sitetheme_deflayout'] 	= $utheme->findDefault($_POST['sitetheme']);
					}
					else
					{
                    	unset($user_pref['sitetheme'],$user_pref['sitetheme_custompages'],$user_pref['sitetheme_deflayout']);
					}

					save_prefs('user');
					unset($ut);
				}
   			}
   			elseif (isset($user_pref['sitetheme']))
   			{	// User obviously no longer allowed his own theme - clear it
   				unset($user_pref['sitetheme'],$user_pref['sitetheme_custompages'],$user_pref['sitetheme_deflayout']);
   				save_prefs('user');
			}


			define('USERTHEME', (isset($user_pref['sitetheme']) && file_exists(e_THEME.$user_pref['sitetheme']."/theme.php") ? $user_pref['sitetheme'] : false));
//			global $ADMIN_DIRECTORY, $PLUGINS_DIRECTORY;
		}*/
		/*else
		{
			define('USER', false);
			define('USERID', 0);
			define('USERTHEME', false);
			define('ADMIN', false);
			define('CORRUPT_COOKIE', true);
			define('USERCLASS', '');
		}
	}*/

	/*define('USERCLASS_LIST', class_list());
	define('e_CLASS_REGEXP', '(^|,)('.str_replace(',', '|', USERCLASS_LIST).')(,|$)');
	define('e_NOBODY_REGEXP', '(^|,)'.e_UC_NOBODY.'(,|$)');*/
}


$sql->db_Mark_Time('Start: Go online');
if(!isset($_E107['no_online']) && varset($pref['track_online']))
{
	e107::getOnline()->goOnline($pref['track_online'], $pref['flood_protect']);
}

/**
 * Set Cookie
 * @param string $name
 * @param string $value
 * @param integer $expire seconds
 * @param string $path
 * @param string $domain
 * @param boolean $secure
 * @return void
 */
function cookie($name, $value, $expire=0, $path = e_HTTP, $domain = '', $secure = 0)
{
	if(!e_SUBDOMAIN || (defined('MULTILANG_SUBDOMAIN') && MULTILANG_SUBDOMAIN === TRUE))
	{
		$domain = (e_DOMAIN != FALSE) ? ".".e_DOMAIN : "";
	}	
	
	setcookie($name, $value, $expire, $path, $domain, $secure);
}

// generic function for retaining values across pages. ie. cookies or sessions.
function session_set($name, $value, $expire='', $path = e_HTTP, $domain = '', $secure = 0)
{
	global $pref;
	if ($pref['user_tracking'] == 'session')
	{
		$_SESSION[$name] = $value;
	}
	else
	{
		if(($domain == '' && !e_SUBDOMAIN) || (defined('MULTILANG_SUBDOMAIN') && MULTILANG_SUBDOMAIN === TRUE))
		{
			$domain = (e_DOMAIN != FALSE) ? ".".e_DOMAIN : "";
		}	
		
		setcookie($name, $value, $expire, $path, $domain, $secure);
		$_COOKIE[$name] = $value;
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function message_handler($mode, $message, $line = 0, $file = '')
{
	e107_require_once(e_HANDLER.'message_handler.php');
	show_emessage($mode, $message, $line, $file);
}

/*
// -----------------------------------------------------------------------------
function table_exists($check)
{
	if (!$GLOBALS['mySQLtablelist'])
	{
		$tablist=mysql_list_tables($GLOBALS['mySQLdefaultdb']);
		while (list($temp) = mysql_fetch_array($tablist))
		{
			$GLOBALS['mySQLtablelist'][] = $temp;
		}
	}

	$mltable=MPREFIX.strtolower($check);

	foreach ($GLOBALS['mySQLtablelist'] as $lang)
	{
		if (strpos($lang, $mltable) !== FALSE)
		{
			return TRUE;
		}
	}
}
*/

function class_list($uid = '')
{
	$clist = array();

	if (is_numeric($uid) || USER === true)
	{
		if (is_numeric($uid))
		{
			if($ud = get_user_data($uid))
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
 * Will be deprecated, probably by e107::includeLan();
 *
 * @param string $path
 * @param boolean $force [optional] Please use the default
 * @return void
 */
function include_lan($path, $force = false)
{
	return e107::includeLan($path, $force);
}

/*
withdrawn - use loadLanFiles($path, 'admin') instead
// Searches a defined set of paths and file names to load language files used for admin (including install etc)
function include_lan_admin($path)
{
	include_lan($path.'languages/'.e_LANGUAGE.'/lan_config.php');
	include_lan($path.'languages/admin/'.e_LANGUAGE.'.php');
}
*/


// Routine looks in standard paths for language files associated with a plugin or theme - primarily for core routines, which won't know
// for sure where the author has put them.
// $unitName is the name (directory path) of the plugin or theme
// $type determines what is to be loaded:
//		'runtime'	- the standard runtime language file for a plugin
//		'admin'		- the standard admin language file for a plugin
//		'theme'		- the standard language file for a plugin (these are usually pretty small, so one is enough)
// Otherwise, $type is treated as part of a filename within the plugin's language directory, prefixed with the current language
// Returns FALSE on failure (not found).
// Returns the include_once error return if there is one
// Otherwise returns an empty string.

// Note - if the code knows precisely where the language file is located, use include_lan()

// $pref['noLanguageSubs'] can be set TRUE to prevent searching for the English files if the files for the current site language don't exist.
//DEPRECATED - use e107::loadLanFiles();
function loadLanFiles($unitName, $type='runtime')
{
	return e107::loadLanFiles($unitName, $type);
}





/**
 *	Check that all required user fields (including extended fields) are valid.
 *	@param array $currentUser - data for user
 *	@return boolean TRUE if update required
 */
function force_userupdate($currentUser)
{
	if (e_PAGE == 'usersettings.php' || strpos(e_SELF, ADMINDIR) == TRUE || (defined('FORCE_USERUPDATE') && (FORCE_USERUPDATE == FALSE)))
	{
		return FALSE;
	}

    $signup_option_names = array('realname', 'signature', 'image', 'timezone', 'class');

	foreach($signup_option_names as $key => $value)
	{
		if (e107::getPref('signup_option_'.$value, 0) == 2 && !$currentUser['user_'.$value])
		{
			return TRUE;
		}
    }

	if (!e107::getPref('disable_emailcheck',TRUE) && !trim($currentUser['user_email'])) return TRUE;

	if(e107::getDb()->db_Select('user_extended_struct', 'user_extended_struct_applicable, user_extended_struct_write, user_extended_struct_name, user_extended_struct_type', 'user_extended_struct_required = 1 AND user_extended_struct_applicable != '.e_UC_NOBODY))
	{
		while($row = e107::getDb()->db_Fetch())
		{
			if (!check_class($row['user_extended_struct_applicable'])) { continue; }		// Must be applicable to this user class
			if (!check_class($row['user_extended_struct_write'])) { continue; }				// And user must be able to change it
			$user_extended_struct_name = "user_{$row['user_extended_struct_name']}";
			if (!isset($currentUser[$user_extended_struct_name]))
			{
				//e107::admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, 'FORCE', 'Force User update', 'Trigger field: '.$user_extended_struct_name, FALSE, LOG_TO_ROLLING);
				return TRUE;
			}
			if (($row['user_extended_struct_type'] == 7) && ($currentUser[$user_extended_struct_name] == '0000-00-00'))
			{
				//e107::admin_log->e_log_event(4, __FILE__."|".__FUNCTION__."@".__LINE__, 'FORCE', 'Force User update', 'Trigger field: '.$user_extended_struct_name, FALSE, LOG_TO_ROLLING);
				return TRUE;
			}
		}
	}
	return FALSE;
}



/**
 * @package e107
 */
class error_handler
{

	var $errors;
	var $debug = false;

	function error_handler()
	{
		//
		// This is initialized before the current debug level is known
		//
		global $_E107;
		if(isset($_E107['debug']))
		{
			$this->debug = true;
			error_reporting(E_ALL);
			return;
		}
		if(isset($_E107['cli']))
		{
			error_reporting(E_ALL ^ E_NOTICE);
			return;
		}

		if ((isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'debug=') !== FALSE) || isset($_COOKIE['e107_debug_level']) && strpos($_SERVER['QUERY_STRING'], 'debug=-') !== TRUE )
		{
		   	$this->debug = true;
		  	error_reporting(E_ALL);
		}
		else
		{
			error_reporting(E_ERROR | E_PARSE);
		}
	}

	function handle_error($type, $message, $file, $line, $context) {
		$startup_error = (!defined('E107_DEBUG_LEVEL')); // Error before debug system initialized
		
		
		switch($type) {
			case E_NOTICE:
			if ($startup_error || E107_DBG_ALLERRORS || E107_DBG_ERRBACKTRACE)
			{
				$error['short'] = "Notice: {$message}, Line {$line} of {$file}<br />\n";
				$trace = debug_backtrace();
				$backtrace[0] = (isset($trace[1]) ? $trace[1] : "");
				$backtrace[1] = (isset($trace[2]) ? $trace[2] : "");
				$error['trace'] = $backtrace;
				$this->errors[] = $error;
			}
			break;
			case E_WARNING:
			if ($startup_error || E107_DBG_BASIC || E107_DBG_ERRBACKTRACE)
			{
				$error['short'] = "Warning: {$message}, Line {$line} of {$file}<br />\n";
				$trace = debug_backtrace();
				$backtrace[0] = (isset($trace[1]) ? $trace[1] : "");
				$backtrace[1] = (isset($trace[2]) ? $trace[2] : "");
				$error['trace'] = $backtrace;
				$this->errors[] = $error;
			}
			break;
			case E_USER_ERROR:
			if ($this->debug == true)
			{
				$error['short'] = "&nbsp;&nbsp;&nbsp;&nbsp;Internal Error Message: {$message}, Line {$line} of {$file}<br />\n";
				$trace = debug_backtrace();
				$backtrace[0] = (isset($trace[1]) ? $trace[1] : "");
				$backtrace[1] = (isset($trace[2]) ? $trace[2] : "");
				$error['trace'] = $backtrace;
				$this->errors[] = $error;
			}
			default:
			return true;
			break;
		}
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
				$ret .= "\t<tr>\n\t\t<td class='forumheader3' >{$value['short']}</td><td><input class='btn btn-info button e-expandit' data-target = 'bt_{$key}' type ='button' style='cursor: hand; cursor: pointer;' size='30' value='Back Trace'  />\n";
				$ret .= "\t<tr>\n<td style='display: none;' colspan='2' id='bt_{$key}'>".print_a($value['trace'], true)."</td></tr>\n";
				$ret .= "</td>\n\t</tr>";
				if($index == 0) { $index = 1; } else { $index = 0; }
			}
		
		}
		else
		{
			foreach ($this->errors as $key => $value)
			{
				$ret .= "<tr class='forumheader3'><td>{$value['short']}</td></tr>\n";
			}
		}

		return ($ret) ? "<table class='table table-condensed fborder'>\n".$ret."</table>" : FALSE;
	}

	function trigger_error($information, $level)
	{
		trigger_error($information);
	}
}

$sql->db_Mark_Time('(After class2)');


function e107_ini_set($var, $value)
{
	if (function_exists('ini_set'))
	{
		return ini_set($var, $value);
	}
	return FALSE;
}

// Return true if specified plugin installed, false if not
//DEPRECATED - use e107::isInstalled();
function plugInstalled($plugname)
{
	return e107::isInstalled($plugname);
	/*global $pref;
	// Could add more checks here later if appropriate
	return isset($pref['plug_installed'][$plugname]);*/
}
