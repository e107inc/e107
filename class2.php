<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/class2.php,v $
|     $Revision: 1.210 $
|     $Date: 2005-09-01 17:09:27 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

// Find out if register globals is enabled and destroy them if so
$register_globals = true;
if(function_exists('ini_get')) {
	$register_globals = ini_get('register_globals');
}
// Destroy! (if we need to)
if($register_globals == true){
	while (list($global) = each($GLOBALS)) {
		if (!preg_match('/^(_POST|_GET|_COOKIE|_SERVER|_FILES|GLOBALS|HTTP.*|_REQUEST|retrieve_prefs)$/', $global)) {
			unset($$global);
		}
	}
	unset($global);
}

if(isset($retrieve_prefs) && is_array($retrieve_prefs)) {
	foreach ($retrieve_prefs as $key => $pref_name) {
		 $retrieve_prefs[$key] = preg_replace("/\W/", '', $pref_name);
	}
} else {
	unset($retrieve_prefs);
}

// setup error handling first of all.
error_reporting(E_ERROR | E_PARSE);
$error_handler = new error_handler();
set_error_handler(array(&$error_handler, "handle_error"));
// Honest global beginning point for processing time
$eTimingStart = microtime();
$start_ob_level = ob_get_level();

define("E107_BOOTSTRAP_LOADED", 1);

ini_set('magic_quotes_runtime', 0);
ini_set('magic_quotes_sybase',  0);

// Grab e107_config, get directory paths, and create the $e107 object
@include_once(realpath(dirname(__FILE__).'/e107_config.php'));
if(!isset($ADMIN_DIRECTORY)){
	// e107_config.php is either empty, not valid or doesn't exist so redirect to installer..
	header("Location: install.php");
}

// clever stuff that figures out where the paths are on the fly.. no more need fo hard-coded e_HTTP :)
e107_require_once(realpath(dirname(__FILE__).'/'.$HANDLERS_DIRECTORY).'/e107_class.php');
$e107_paths = compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'DOWNLOADS_DIRECTORY');
$e107 = new e107($e107_paths, realpath(dirname(__FILE__)));

$inArray = array("'", ";", "/**/", "/UNION/", "/SELECT/", "AS ");
if (strpos($_SERVER['PHP_SELF'], "trackback") === FALSE)
{
	foreach($inArray as $res)
	{
		if(stristr($_SERVER['QUERY_STRING'], $res))
		{
			die("Access denied.");
		}
	}
}

if (preg_match("/\[(.*?)\].*?/i", $_SERVER['QUERY_STRING'], $matches)) {
	define("e_MENU", $matches[1]);
	define("e_QUERY", str_replace($matches[0], "", preg_replace("#&|/?".session_name().".*#i", "", $_SERVER['QUERY_STRING'])));
} else {
	define("e_QUERY", preg_replace("#&|/?".session_name().".*#i", "", $_SERVER['QUERY_STRING']));
}
$e_QUERY = e_QUERY;

define("e_TBQS", $_SERVER['QUERY_STRING']);
$_SERVER['QUERY_STRING'] = e_QUERY;

define("e_UC_PUBLIC", 0);
define("e_UC_READONLY", 251);
define("e_UC_GUEST", 252);
define("e_UC_MEMBER", 253);
define("e_UC_ADMIN", 254);
define("e_UC_NOBODY", 255);
define("ADMINDIR", $ADMIN_DIRECTORY);

// All debug objects and constants are defined in the debug handler
if (strpos(e_MENU, 'debug=') !== FALSE || isset($_COOKIE['e107_debug_level'])) {

	require_once(e_HANDLER.'debug_handler.php');
	$db_debug = new e107_db_debug;
} else {
	define('E107_DEBUG_LEVEL',0);
}

if(is_object($db_debug)) { $db_debug->Mark_Time('Start: Init ErrHandler');  }

// e107_config.php upgrade check
if (!$ADMIN_DIRECTORY && !$DOWNLOADS_DIRECTORY) {
	message_handler("CRITICAL_ERROR", 8, ": generic, ", "e107_config.php");
	exit;
}

@require_once(e_HANDLER.'traffic_class.php');
$eTraffic=new e107_traffic; // We start traffic counting ASAP
$eTraffic->Calibrate($eTraffic);

if (!$mySQLuser) {
	header("location:install.php");
	exit;
}
define("MPREFIX", $mySQLprefix);

e107_require_once(e_HANDLER."mysql_class.php");
e107_require_once(e_HANDLER.'e_parse_class.php');

$tp = new e_parse;

$sql =& new db;
$sql2 =& new db;

$sql->db_SetErrorReporting(FALSE);

$sql->db_Mark_Time('Start: SQL Connect');
$merror=$sql->db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
$sql->db_Mark_Time('Start: Prefs, misc tables');

require_once(e_HANDLER.'admin_log_class.php');
$admin_log = new e_admin_log();

if ($merror == "e1") {
	message_handler("CRITICAL_ERROR", 6, ": generic, ", "class2.php");
	exit;
}
else if ($merror == "e2") {
	message_handler("CRITICAL_ERROR", 7, ": generic, ", "class2.php");
	exit;
}

/* New compatabilty mode.
At a later date add a check to load e107 compat mode by $pref
PHP Compatabilty should *always* be on. */
e107_require_once(e_HANDLER."php_compatibility_handler.php");
e107_require_once(e_HANDLER."e107_Compat_handler.php");


e107_require_once(e_HANDLER."pref_class.php");
$sysprefs = new prefs;

// Extract core prefs from the database
e107_require_once(e_HANDLER.'cache_handler.php');
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();

$sql->db_Mark_Time('Start: Extracting Core Prefs');
$PrefCache = ecache::retrieve('SitePrefs', 24 * 60, true);
if(!$PrefCache){
	// No cache of the prefs array, going for the db copy..
	$retrieve_prefs[] = 'SitePrefs';
	$sysprefs->ExtractPrefs($retrieve_prefs, TRUE);
	$PrefData = $sysprefs->get('SitePrefs');
	$pref = $eArrayStorage->ReadArray($PrefData);
	if(!$pref){
		// prefs aren't in the SitePrefs column, spit out an error
		message_handler("CRITICAL_ERROR", 3, __LINE__, __FILE__);
		// Try for the automatic backup..
		$PrefData = $sysprefs->get('SitePrefs_Backup');
		$pref = $eArrayStorage->ReadArray($PrefData);
		if(!$pref){
			// No auto backup, try for the 'old' prefs system.
			$PrefData = $sysprefs->get('pref');
			$pref = unserialize($PrefData);
			if(!is_array($pref)){
				// No old system, so point in the direction of resetcore :(
				message_handler("CRITICAL_ERROR", 4, __LINE__, __FILE__);
				exit;
			} else {
				// old prefs found, remove old system, and update core with new system
				$PrefOutput = $eArrayStorage->WriteArray($pref);
				if(!$sql->db_Update('core', "e107_value='{$PrefOutput}' WHERE e107_name='SitePrefs'")){
					$sql->db_Insert('core', "'SitePrefs', '{$PrefOutput}'");
				}
				if(!$sql->db_Update('core', "e107_value='{$PrefOutput}' WHERE e107_name='SitePrefs_Backup'")){
					$sql->db_Insert('core', "'SitePrefs_Backup', '{$PrefOutput}'");
				}
				$sql->db_Delete('core', "`e107_name` = 'pref'");
			}
		} else {
			// auto backup found, use backup to restore the core
			if(!$sql->db_Update('core', "`e107_value` = '".addslashes($PrefStored)."' WHERE `e107_name` = 'SitePrefs'")){
				$sql->db_Insert('core', "'SitePrefs', '".addslashes($PrefStored)."'");
			}
		}
	}
	// write pref cache array
	$PrefCache = $eArrayStorage->WriteArray($pref, false);
	// store the prefs in cache if cache is enabled
	ecache::set('SitePrefs', $PrefCache);
} else {
	// cache of core prefs was found, so grab all the useful core rows we need
	$sysprefs->DefaultIgnoreRows .= '|SitePrefs';
	$sysprefs->prefVals['core']['SitePrefs'] = $PrefCache;
	$sysprefs->ExtractPrefs($retrieve_prefs, TRUE);
	$pref = $eArrayStorage->ReadArray($PrefCache);
}

// extract menu prefs
$menu_pref = unserialize(stripslashes($sysprefs->get('menu_pref')));

$sql->db_Mark_Time('(Extracting Core Prefs Done)');

define("SITEURLBASE", ($pref['ssl_enabled'] == '1' ? "https://" : "http://").$_SERVER['HTTP_HOST']);
define("SITEURL", SITEURLBASE.e_HTTP);

// if a cookie name pref isn't set, make one :)
if (!$pref['cookie_name']) {
	$pref['cookie_name'] = "e107cookie";
}

// start a session if session based login is enabled
if ($pref['user_tracking'] == "session") {
	session_start();
}

define("e_SELF", ($pref['ssl_enabled'] ? "https://".$_SERVER['HTTP_HOST'].($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_FILENAME']) : "http://".$_SERVER['HTTP_HOST'].($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_FILENAME'])));


// if the option to force users to use a particular url for the site is enabled, redirect users there
if($pref['redirectsiteurl'] && $pref['siteurl']) {
	$siteurl = SITEURLBASE."/";
	if (strpos($pref['siteurl'], $siteurl) === FALSE) {
		$location = str_replace($siteurl, $pref['siteurl'], e_SELF).(e_QUERY ? "?".e_QUERY : "");
		header("Location: {$location}");
		exit();
	}
}


// sort out the users language selection
if (isset($_POST['setlanguage']) || isset($_GET['elan'])) {
	if($_GET['elan']){  // query support, for language selection splash pages. etc
	$_POST['sitelanguage'] = $_GET['elan'];
	}
	$sql->mySQLlanguage = $_POST['sitelanguage'];
	if ($pref['user_tracking'] == "session") {
		$_SESSION['e107language_'.$pref['cookie_name']] = $_POST['sitelanguage'];
	} else {
		setcookie('e107language_'.$pref['cookie_name'], $_POST['sitelanguage'], time() + 86400, "/");
		$_COOKIE['e107language_'.$pref['cookie_name']] = $_POST['sitelanguage'];
		if (strpos(e_SELF, e_ADMIN) === FALSE) {
			$locat = (!$_GET['elan'] && e_QUERY) ? e_SELF."?".e_QUERY : e_SELF;
			header("Location:".$locat);
		}
	}
}

$user_language='';
// Multi-language options.
if (isset($pref['multilanguage']) && $pref['multilanguage']) {

	if ($pref['user_tracking'] == "session") {
		$user_language=$_SESSION['e107language_'.$pref['cookie_name']];
		$sql->mySQLlanguage=($user_language) ? $user_language : "";
	} else {
		$user_language=$_COOKIE['e107language_'.$pref['cookie_name']];
		$sql->mySQLlanguage=($user_language) ? $user_language : "";
	}

	// Get Language List for rights checking.
	$handle=opendir(e_LANGUAGEDIR);
	while ($file = readdir($handle)) {
		if (is_dir(e_LANGUAGEDIR.$file) && $file !="." && $file !=".." && $file !="CVS") {
			$lanlist[] = $file;
		}
	}
	closedir($handle);
	$tmplan = implode(",",$lanlist);
}
define("e_LANLIST",(isset($tmplan) ? $tmplan : ""));
$page=substr(strrchr($_SERVER['PHP_SELF'], "/"), 1);
define("e_PAGE", $page);

$sql->db_Mark_Time('(Start: Pref/multilang done)');

// online user tracking class
$e_online = new e_online();

// cache class
$e107cache = new ecache;

if (isset($pref['del_unv']) && $pref['del_unv']) {
	$threshold=(time() - ($pref['del_unv'] * 60));
	$sql->db_Delete("user", "user_ban = 2 AND user_join < '{$threshold}' ");
}

e107_require_once(e_HANDLER."override_class.php");
$override=new override;

e107_require_once(e_HANDLER."event_class.php");
$e_event=new e107_event;

if ($pref['notify']) {
	e107_require_once(e_HANDLER.'notify_class.php');
}

//$e_event -> trigger("testing");

if (isset($pref['modules']) && $pref['modules']) {
	$mods=explode(",", $pref['modules']);
	foreach ($mods as $mod) {
		if (is_readable(e_PLUGIN."{$mod}/module.php")) {
			require_once(e_PLUGIN."{$mod}/module.php");
		}
	}
}

//###########  Module redefinable functions ###############
if (!function_exists('checkvalidtheme')) {
	function checkvalidtheme($theme_check) {
		// arg1 = theme to check
		global $ADMIN_DIRECTORY, $tp, $e107;

		if (strpos(e_QUERY, "themepreview") !== FALSE) {
			list($action, $id) = explode('.', e_QUERY);
			require_once(e_HANDLER."theme_handler.php");
			$themeArray = themeHandler :: getThemes("id");
			define("PREVIEWTHEME", e_THEME.$themeArray[$id]."/");
			define("PREVIEWTHEMENAME", $themeArray[$id]);
			define("THEME", e_THEME.$themeArray[$id]."/");
			define("THEME_ABS", e_THEME_ABS.$themeArray[$id]."/");
			return;
		}
		if (@fopen(e_THEME.$theme_check."/theme.php", "r")) {
			define("THEME", e_THEME.$theme_check."/");
			define("THEME_ABS", e_THEME_ABS.$theme_check."/");
			$e107->site_theme = $theme_check;
		} else {
			function search_validtheme() {
				global $e107;
				$th=substr(e_THEME, 0, -1);
				$handle=opendir($th);
				while ($file = readdir($handle)) {
					if (is_dir(e_THEME.$file) && is_readable(e_THEME.$file.'/theme.php')) {
						closedir($handle);
						$e107->site_theme = $file;
						return $file;
					}
				}
				closedir($handle);
			}
			$e107tmp_theme = search_validtheme();
			define("THEME", e_THEME.$e107tmp_theme."/");
			define("THEME_ABS", e_THEME_ABS.$e107tmp_theme."/");
			if (ADMIN && strpos(e_SELF, $ADMIN_DIRECTORY) === FALSE) {
				echo '<script>alert("'.$tp->toJS(CORE_LAN1).'")</script>';
			}
		}
		$themes_dir = $e107->e107_dirs["THEMES_DIRECTORY"];
		$e107->http_theme_dir = "{$e107->server_path}{$themes_dir}{$e107->site_theme}/";
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
if (!class_exists('e107_table')) {
	class e107table {
		function tablerender($caption, $text, $mode = "default", $return = false) {
			/*
			# Render style table
			# - parameter #1:                string $caption, caption text
			# - parameter #2:                string $text, body text
			# - return                                null
			# - scope                                        public
			*/
			global $override;

			if ($override_tablerender = $override->override_check('tablerender')) {
				$result=call_user_func($override_tablerender, $caption, $text, $mode, $return);

				if ($result == "return") {
					return;
				}
				extract($result);
			}

			if ($return) {
				ob_start();
				tablestyle($caption, $text, $mode);
				$ret=ob_get_contents();
				ob_end_clean();
				return $ret;
			} else {
				tablestyle($caption, $text, $mode);
			}
		}
	}
}
//#############################################################


$sql->db_Mark_Time('Start: Init session');
$ns=new e107table;
init_session();

define("SITENAME", trim($tp->toHTML($pref['sitename'], "", "emotes_off defs")));
define("SITEBUTTON", $pref['sitebutton']);
define("SITETAG", $tp->toHTML($pref['sitetag'], FALSE, "emotes_off defs"));
define("SITEDESCRIPTION", $tp->toHTML($pref['sitedescription'], "", "emotes_off defs"));
define("SITEADMIN", $pref['siteadmin']);
define("SITEADMINEMAIL", $pref['siteadminemail']);
define("SITEDISCLAIMER", $tp->toHTML($pref['sitedisclaimer'], "", "emotes_off defs"));

$e107->ban();

if(USER && isset($pref['force_userupdate']) && $pref['force_userupdate'] && e_PAGE != "usersettings.php"){
	if(force_userupdate()){
		header("Location: ".e_BASE."usersettings.php?update");
	};
}

$sql->db_Mark_Time('Start: Go online');
if(isset($pref['track_online']) && $pref['track_online'])
{
	$e_online->online($pref['track_online'], $pref['flood_protect']);
}
$sql->db_Mark_Time('Start: Signup/splash/admin');

define("e_SIGNUP", e_BASE.(file_exists(e_BASE."customsignup.php") ? "customsignup.php" : "signup.php"));
define("e_LOGIN", e_BASE.(file_exists(e_BASE."customlogin.php") ? "customlogin.php" : "login.php"));

if ($pref['membersonly_enabled'] && !USER && e_PAGE != e_SIGNUP && e_PAGE != "index.php" && e_PAGE != "fpw.php" && e_PAGE != e_LOGIN && strpos(e_PAGE, "admin") === FALSE && e_PAGE != 'membersonly.php' && e_PAGE != 'sitedown.php') {
	header("Location: ".e_HTTP."membersonly.php");
	exit;
}

$sql->db_Delete("tmp", "tmp_time < '".(time() - 300)."' AND tmp_ip!='data' AND tmp_ip!='submitted_link'");

$language=($pref['sitelanguage'] ? $pref['sitelanguage'] : "English");
define("MAGIC_QUOTES_GPC", (ini_get('magic_quotes_gpc') ? TRUE : FALSE));
define("e_LAN", $language);

define("USERLAN", ($user_language && (strpos(e_SELF, $PLUGINS_DIRECTORY) !== FALSE || (strpos(e_SELF, $ADMIN_DIRECTORY) === FALSE && file_exists(e_LANGUAGEDIR.$user_language."/lan_".e_PAGE)) || (strpos(e_SELF, $ADMIN_DIRECTORY) !== FALSE && file_exists(e_LANGUAGEDIR.$user_language."/admin/lan_".e_PAGE)) || file_exists(dirname($_SERVER['SCRIPT_FILENAME'])."/languages/".$user_language."/lan_".e_PAGE)    || (    (strpos(e_SELF, $ADMIN_DIRECTORY) == FALSE) && (strpos(e_SELF, $PLUGINS_DIRECTORY) == FALSE) && file_exists(e_LANGUAGEDIR.$user_language."/".$user_language.".php")  )   ) ? $user_language : FALSE));
define("e_LANGUAGE", (!USERLAN || !defined("USERLAN") ? $language : USERLAN));

e107_include(e_LANGUAGEDIR.e_LANGUAGE."/".e_LANGUAGE.".php");
e107_include_once(e_LANGUAGEDIR.e_LANGUAGE."/".e_LANGUAGE."_custom.php");

// send the charset to the browser - overides spurious server settings with the lan pack settings.
header("Content-type: text/html; charset=".CHARSET);

// following lines commented - because they bog down the core and don't *actually* [seem] to do much...
// it might be the case that these are needed for admin, but they certainly aren't needed for anywhere else.
/*foreach ($pref as $key => $prefvalue) {
$pref[$key] = $tp->toFORM($prefvalue);
}*/

if ($pref['maintainance_flag'] && ADMIN == FALSE && strpos(e_SELF, "admin.php") === FALSE && strpos(e_SELF, "sitedown.php") === FALSE) {
	header("Location: ".SITEURL."sitedown.php");
	exit;
}

if (strpos(e_SELF, $ADMIN_DIRECTORY) !== FALSE || strpos(e_SELF, "admin.php") !== FALSE) {
	e107_include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_".e_PAGE);
	e107_include_once(e_LANGUAGEDIR."English/admin/lan_".e_PAGE);
} else if (strpos(e_SELF, $PLUGINS_DIRECTORY) === FALSE) {
	e107_include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_".e_PAGE);
	e107_include_once(e_LANGUAGEDIR."English/lan_".e_PAGE);
}

$sql->db_Mark_Time('(Start: Login/logout/ban/tz)');

if (isset($_POST['userlogin'])) {
	e107_require_once(e_HANDLER."login.php");
	$usr = new userlogin($_POST['username'], $_POST['userpass'], $_POST['autologin']);
}

if (e_QUERY == 'logout') {
	$ip = $e107->getip();
	$udata=(USER === TRUE) ? USERID.".".USERNAME : "0";
	$sql->db_Update("online", "online_user_id = '0', online_pagecount=online_pagecount+1 WHERE online_user_id = '{$udata}' LIMIT 1");

	if ($pref['user_tracking'] == "session") {
		session_destroy();
		$_SESSION[$pref['cookie_name']]="";
	}

	cookie($pref['cookie_name'], "", (time() - 2592000));
	$e_event->trigger("logout");
	echo "<script type='text/javascript'>document.location.href = '".SITEURL."index.php'</script>\n";
	exit;
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

if (isset($_COOKIE['e107_tdOffset'])) {
	// Actual seconds of delay. See e107.js and footer_default.php
	$e_deltaTime = $_COOKIE['e107_tdOffset'];
}

if (isset($_COOKIE['e107_tzOffset'])) {
	// Relative client-to-server time zone offset in seconds.
	$e_deltaTime += (-($_COOKIE['e107_tzOffset'] * 60 + date("Z")));
}

define("TIMEOFFSET", $e_deltaTime);

$sql->db_Mark_Time('Start: Get menus');

$menu_data = $e107cache->retrieve("menus_".USERCLASS_LIST);
$menu_data = $eArrayStorage->ReadArray($menu_data);
if(!is_array($menu_data)) {
	if ($sql->db_Select('menus', '*', "menu_location > 0 AND menu_class IN (".USERCLASS_LIST.") ORDER BY menu_order")) {
		while ($row = $sql->db_Fetch()) {
			$eMenuList[$row['menu_location']][]=$row;
			$eMenuActive[]=$row['menu_name'];
		}
	}
	$menu_data['menu_list'] = $eMenuList;
	$menu_data['menu_active'] = $eMenuActive;
	$menu_data = $eArrayStorage->WriteArray($menu_data, false);
	$e107cache->set("menus_".USERCLASS_LIST, $menu_data);
	unset($menu_data);
} else {
	$eMenuList = $menu_data['menu_list'];
	$eMenuActive = $menu_data['menu_active'];
	unset($menu_data);
}

$sql->db_Mark_Time('(Start: Find/Load Theme)');

if(!defined("THEME")){
	if ((strpos(e_SELF, "usersettings.php") !== FALSE && is_numeric(e_QUERY) && getperms("4") && ADMIN) || (strpos(e_SELF, $ADMIN_DIRECTORY) !== FALSE || strpos(e_SELF, "admin") !== FALSE || (isset($eplug_admin) && $eplug_admin == TRUE)) && $pref['admintheme']) {

		if (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') !== FALSE) {
			checkvalidtheme($pref['sitetheme']);
		} else if (strpos(e_SELF, "newspost.php") !== FALSE) {
			define("MAINTHEME", e_THEME.$pref['sitetheme']."/");
			checkvalidtheme($pref['admintheme']);
		}
		else {
			checkvalidtheme($pref['admintheme']);
		}
	} else {
		if (USERTHEME !== FALSE && USERTHEME != "USERTHEME") {
			checkvalidtheme(USERTHEME);
		} else {
			checkvalidtheme($pref['sitetheme']);
		}
	}
}

if (strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE && (strpos(e_SELF, $ADMIN_DIRECTORY) !== FALSE || strpos(e_SELF, "admin") !== FALSE || (isset($eplug_admin) && $eplug_admin == TRUE))) {
	if (file_exists(THEME.'admin_theme.php')) {
		require_once(THEME.'admin_theme.php');
	} else {
		require_once(THEME."theme.php");
	}
} else {
	require_once(THEME."theme.php");
}

if(!defined("IMODE")) define("IMODE", "lite");

if ($pref['anon_post'] ? define("ANON", TRUE) : define("ANON", FALSE));

if (Empty($pref['newsposts']) ? define("ITEMVIEW", 15) : define("ITEMVIEW", $pref['newsposts']));

if ($pref['antiflood1'] == 1) {
	define('FLOODPROTECT', TRUE);
	define('FLOODTIMEOUT', $pref['antiflood_timeout']);
}

define("HEADERF", e_THEME."templates/header{$layout}.php");
define("FOOTERF", e_THEME."templates/footer{$layout}.php");

if (!file_exists(HEADERF)) {
	message_handler("CRITICAL_ERROR", "Unable to find file: ".HEADERF, __LINE__ - 2, __FILE__);
}

if (!file_exists(FOOTERF)) {
	message_handler("CRITICAL_ERROR", "Unable to find file: ".FOOTERF, __LINE__ - 2, __FILE__);
}

define("LOGINMESSAGE", "");
define("OPEN_BASEDIR", (ini_get('open_basedir') ? TRUE : FALSE));
define("SAFE_MODE", (ini_get('safe_mode') ? TRUE : FALSE));
define("FILE_UPLOADS", (ini_get('file_uploads') ? TRUE : FALSE));
define("INIT", TRUE);
if(isset($_SERVER['HTTP_REFERER'])) {
	$tmp = explode("?", $_SERVER['HTTP_REFERER']);
	define("e_REFERER_SELF",($tmp[0] == e_SELF));
} else {
	define('e_REFERER_SELF', FALSE);
}

if (!class_exists('convert'))
{
	require_once(e_HANDLER."date_handler.php");
}


//@require_once(e_HANDLER."IPB_int.php");
//@require_once(e_HANDLER."debug_handler.php");
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function js_location($qry){
	echo "<script type='text/javascript'>document.location.href='{$qry}'</script>\n"; exit;
}

function check_email($var) {
	return (preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $var)) ? $var : FALSE;
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function check_class($var, $userclass = USERCLASS, $debug = FALSE)
{
	if($var == e_LANGUAGE){
		return TRUE;
	}

	if (!$var || $var == "")
	{
		return TRUE;
	}

	if(strpos($var, ",") !== FALSE)
	{
		$lans = explode(",",e_LANLIST);
		$varList = explode(",", $var);
		rsort($varList); // check the language first.(ie. numbers come last)
		foreach($varList as $v)
		{
			if (in_array($v,$lans) && strpos($v, e_LANGUAGE) === FALSE) {
				return FALSE;
			}

			if(check_class($v, $userclass, $debug))	{
				return TRUE;
			}


		}
		return FALSE;
	}

	if (preg_match("/^([0-9]+)$/", $var))
	{
		if ($var == e_UC_MEMBER && USER == TRUE)
		{
			return TRUE;
		}

		if ($var == e_UC_GUEST && USER == FALSE) {
			return TRUE;
		}

		if ($var == e_UC_PUBLIC) {
			return TRUE;
		}

		if ($var == e_UC_NOBODY) {
			return FALSE;
		}

		if ($var == e_UC_ADMIN && ADMIN) {
			return TRUE;
		}
		if ($var == e_UC_READONLY) {
			return TRUE;
		}
	}

	if ($debug) {
		echo "USERCLASS: ".$userclass.", \$var = $var : ";
	}

	if (!defined("USERCLASS") || $userclass == "") {
		if ($debug) {
			echo "FALSE<br />";
		}
		return FALSE;
	}

	// user has classes set - continue
	if (preg_match("/^([0-9]+)$/", $var)) {
		$tmp=explode(',', $userclass);
		if (is_numeric(array_search($var, $tmp))) {
			if ($debug) {
				echo "TRUE<br />";
			}
			return TRUE;
		}
	} else {



		// var is name of class ...
		$sql=new db;
		if ($sql->db_Select("userclass_classes", "*", "userclass_name='$var' ")) {
			$row=$sql->db_Fetch();
			$tmp=explode(',', $userclass);
			if (is_numeric(array_search($row['userclass_id'], $tmp))) {
				if ($debug) {
					echo "TRUE<br />";
				}
				return TRUE;
			}
		}
	}

	if ($debug) {
		echo "NOTNUM! FALSE<br />";
	}

	return FALSE;
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function getperms($arg, $ap = ADMINPERMS) {
	global $PLUGINS_DIRECTORY;

	if ($ap == "0") {
		return TRUE;
	}

	if ($ap == "") {
		return FALSE;
	}

	$ap='.'.$ap;

	if ($arg == 'P' && preg_match("#(.*?)/".$PLUGINS_DIRECTORY."(.*?)/(.*?)#", e_SELF, $matches)) {
		$psql=new db;
		if ($psql->db_Select('plugin', 'plugin_id', "plugin_path = '".$matches[2]."' ")) {
			$row=$psql->db_Fetch();
			$arg='P'.$row[0];
		}
	}

	if (strpos($ap, ".".$arg.".") !== FALSE) {
		return TRUE;
	} else {
		return FALSE;
	}
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function save_prefs($table = 'core', $uid = USERID, $row_val = '') {
	global $pref, $user_pref, $tp, $PrefCache, $sql, $eArrayStorage;
	if ($table == 'core') {
		if ($row_val == '') {
			// Save old version as a backup
			if(!$sql->db_Update('core', "e107_value='".addslashes($PrefCache)."' WHERE e107_name='SitePrefs_Backup'")){
				$sql->db_Insert('core', "'SitePrefs', '".addslashes($PrefCache)."'");
			}

			// traverse the pref array, with toDB on everything
			$_pref = $tp->recurse_toDB($pref, true);
			// Create the data to be stored
			$PrefCache1 = $eArrayStorage->WriteArray($_pref);
			if(!$sql->db_Update('core', "e107_value='{$PrefCache1}' WHERE e107_name = 'SitePrefs'")){
				$sql->db_Insert('core', "'SitePrefs', '{$PrefCache1}'");
			}
			ecache::clear('SitePrefs');
		}
	} else {

		$_user_pref = $tp->recurse_toDB($user_pref);

		$tmp=addslashes(serialize($_user_pref));
		$sql->db_Update("user", "user_prefs='$tmp' WHERE user_id=$uid");
		return $tmp;
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

class e_online {
	function online($online_tracking = false, $flood_control = false) {
		if($online_tracking == true || $flood_control == true) {
			global $online_timeout, $online_warncount, $online_bancount;
			if(!isset($online_timeout)) {
				$online_timeout = 300;
			}
			if(!isset($online_warncount)) {
				$online_warncount = 90;
			}
			if(!isset($online_bancount)) {
				$online_bancount = 100;
			}

			$page = (strpos(e_SELF, "forum_") !== FALSE) ? e_SELF.".".e_QUERY : e_SELF;
			$page = (strpos(e_SELF, "comment") !== FALSE) ? e_SELF.".".e_QUERY : $page;
			$page = (strpos(e_SELF, "content") !== FALSE) ? e_SELF.".".e_QUERY : $page;

			global $sql, $pref, $e107, $listuserson, $e_event;
			$ip = $e107->getip();
			$udata = (USER === true ? USERID.".".USERNAME : "0");

			if (USER) {
				// Find record that matches IP or visitor, or matches user info
				if ($sql->db_Select("online", "*", "(`online_ip` = '{$ip}' AND `online_user_id` = '0') OR `online_user_id` = '{$udata}'")) {
					$row = $sql->db_Fetch();

					if ($row['online_user_id'] == $udata) {
						//Matching user record
						if ($row['online_timestamp'] < (time() - $online_timeout)) {
							//It has been at least 'timeout' seconds since this user has connected
							//Update user record with timestamp, current IP, current page and set pagecount to 1
							$query = "online_timestamp='".time()."', online_ip='{$ip}', online_location='{$page}', online_pagecount=1 WHERE online_user_id='{$row['online_user_id']}' LIMIT 1";
						} else {
							if (!ADMIN) {
								$row['online_pagecount'] ++;
							}
							// Update user record with current IP, current page and increment pagecount
							$query = "online_ip='{$ip}', `online_location` = '{$page}', `online_pagecount` = '{$row['online_pagecount']}' WHERE `online_user_id` = '{$row['online_user_id']}' LIMIT 1";
						}
					} else {
						//Found matching visitor record (ip only) for this user
						if ($row['online_timestamp'] < (time() - $online_timeout)) {
							// It has been at least 'timeout' seconds since this user has connected
							// Update record with timestamp, current IP, current page and set pagecount to 1
							$query = "`online_timestamp` = '".time()."', `online_user_id` = '{$udata}', `online_location` = '{$page}', `online_pagecount` = 1 WHERE `online_ip` = '{$ip}' AND `online_user_id` = '0' LIMIT 1";
						} else {
							if (!ADMIN) {
								$row['online_pagecount'] ++;
							}
							//Update record with current IP, current page and increment pagecount
							$query = "`online_user_id` = '{$udata}', `online_location` = '{$page}', `online_pagecount` = {$row['online_pagecount']} WHERE `online_ip` = '{$ip}' AND `online_user_id` = '0' LIMIT 1";
						}
					}
					$sql->db_Update("online", $query);
				} else {
					$sql->db_Insert("online", " '".time()."', 'null', '{$udata}', '{$ip}', '{$page}', 1");
				}
			} else {
				//Current page request is from a visitor
				if ($sql->db_Select("online", "*", "`online_ip` = '{$ip}' AND `online_user_id` = '0'")) {
					$row = $sql->db_Fetch();

					if ($row['online_timestamp'] < (time() - $online_timeout)) //It has been at least 'timeout' seconds since this ip has connected
					{
						//Update record with timestamp, current page, and set pagecount to 1
						$query = "`online_timestamp` = '".time()."', `online_location` = '{$page}', `online_pagecount` = 1 WHERE `online_ip` = '{$ip}' AND `online_user_id` = '0' LIMIT 1";
					} else {
						//Update record with current page and increment pagecount
						$row['online_pagecount'] ++;
						//   echo "here {$online_pagecount}";
						$query="`online_location` = '{$page}', `online_pagecount` = {$row['online_pagecount']} WHERE `online_ip` = '{$ip}' AND `online_user_id` = '0' LIMIT 1";
					}
					$sql->db_Update("online", $query);
				} else {
					$sql->db_Insert("online", " '".time()."', 'null', '0', '{$ip}', '{$page}', 1");
				}
			}

			if (ADMIN || $pref['autoban'] != 1) {
				$row['online_pagecount'] = 1;
			}
			if ($row['online_pagecount'] > $online_bancount && $row['online_ip'] != "127.0.0.1") {
				echo "HERE";
				$sql->db_Insert("banlist", "'{$ip}', '0', 'Hit count exceeded ({$row['online_pagecount']} requests within allotted time)' ");
				$e_event->trigger("flood", $ip);
				exit;
			}
			if ($row['online_pagecount'] >= $online_warncount && $row['online_ip'] != "127.0.0.1") {
				echo "<div style='text-align:center; font: 11px verdana, tahoma, arial, helvetica, sans-serif;'><b>Warning!</b><br /><br />The flood protection on this site has been activated and you are warned that if you carry on requesting pages you could be banned.<br /></div>";
				exit;
			}

			$sql->db_Delete("online", "`online_timestamp` < ".(time() - $online_timeout));

			global $members_online, $total_online, $member_list, $listuserson;
			$total_online = $sql->db_Count("online");
			if ($members_online = $sql->db_Select("online", "*", "online_user_id != '0' ")) {
				$member_list = '';
				$listuserson = array();
				while ($row = $sql->db_Fetch()) {
					$vals = explode(".", $row['online_user_id'], 2);
					$member_list .= "<a href='".e_BASE."user.php?id.{$vals[0]}'>{$vals[1]}</a> ";
					$listuserson[$row['online_user_id']] = $row['online_location'];
				}
			}
			define("TOTAL_ONLINE", $total_online);
			define("MEMBERS_ONLINE", $members_online);
			define("GUESTS_ONLINE", $total_online - $members_online);
			define("ON_PAGE", $sql->db_Count("online", "(*)", "WHERE `online_location` = '{$page}' "));
			define("MEMBER_LIST", $member_list);
		} else {
			define("e_TRACKING_DISABLED", true);
			define("TOTAL_ONLINE", "");
			define("MEMBERS_ONLINE", "");
			define("GUESTS_ONLINE", "");
			define("ON_PAGE", "");
			define("MEMBER_LIST", ""); //
		}
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function cachevars($id, $var) {
	global $cachevar;
	$cachevar[$id]=$var;
}

function getcachedvars($id) {
	global $cachevar;
	return ($cachevar[$id] ? $cachevar[$id] : false);
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class floodprotect {
	function flood($table, $orderfield) {
		/*
		# Test for possible flood
		#
		# - parameter #1                string $table, table being affected
		# - parameter #2                string $orderfield, date entry in respective table
		# - return                                boolean
		# - scope                                        public
		*/
		$sql=new db;

		if (FLOODPROTECT == TRUE) {
			$sql->db_Select($table, "*", "ORDER BY ".$orderfield." DESC LIMIT 1", "no_where");
			$row=$sql->db_Fetch();
			return ($row[$orderfield] > (time() - FLOODTIMEOUT) ? FALSE : TRUE);
		} else {
			return TRUE;
		}
	}
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function init_session() {
	/*
	# Validate user
	#
	# - parameters none
	# - return boolean
	# - scope public
	*/
	global $sql, $pref, $user_pref, $tp, $currentUser;

	if (!isset($_COOKIE[$pref['cookie_name']]) && !isset($_SESSION[$pref['cookie_name']])) {
		define("USER", FALSE);
		define("USERTHEME", FALSE);
		define("ADMIN", FALSE);
		define("GUEST", TRUE);
		define('USERCLASS', '');
		define('USEREMAIL', '');
	} else {
		list($uid, $upw)=($_COOKIE[$pref['cookie_name']] ? explode(".", $_COOKIE[$pref['cookie_name']]) : explode(".", $_SESSION[$pref['cookie_name']]));

		if (empty($uid) || empty($upw)) // corrupt cookie?
		{
			cookie($pref['cookie_name'], "", (time() - 2592000));
			$_SESSION[$pref['cookie_name']] = "";
			session_destroy();
			define("ADMIN", FALSE);
			define("USER", FALSE);
			define("USERCLASS", "");
			define("LOGINMESSAGE", "Corrupted cookie detected - logged out.<br /><br />");
			return (FALSE);
		} if(array_key_exists('ue_upgrade', $pref)) {
			$qry = "
			SELECT u.*, ue.* FROM #user AS u
			LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id
			WHERE u.user_id='{$uid}' AND md5(u.user_password)='{$upw}'
			";
		} else {
			$qry = "SELECT * FROM #user AS u WHERE u.user_id='{$uid}' AND md5(u.user_password)='{$upw}'";
		}
		if ($sql->db_Select_gen($qry)) {
			$result=$sql->db_Fetch();
			$currentUser = $result;
			//extract($result); // removed in preference of the $result array

			define("USERID", $result['user_id']);
			define("USERNAME", $result['user_name']);
			define("USERURL", $result['user_homepage']);
			define("USEREMAIL", $result['user_email']);
			define("USER", TRUE);
			define("USERCLASS", $result['user_class']);
			define("USERREALM", $result['user_realm']);
			define("USERVIEWED", $result['user_viewed']);
			define("USERIMAGE", $result['user_image']);
			define("USERSESS", $result['user_sess']);

			if ($result['user_currentvisit'] + 3600 < time()) {
				$result['user_lastvisit'] = $result['user_currentvisit'];
				$result['user_currentvisit'] = time();
				$sql->db_Update("user", "user_visits = user_visits + 1, user_lastvisit = '{$result['user_lastvisit']}', user_currentvisit = '{$result['user_currentvisit']}', user_viewed = '' WHERE user_name='".USERNAME."' ");
			}

			define("USERLV", $result['user_lastvisit']);

			if ($result['user_ban'] == 1) {
				exit;
			}

			$user_pref = unserialize($result['user_prefs']);

			if (isset($_POST['settheme'])) {
				$user_pref['sitetheme'] = ($pref['sitetheme'] == $_POST['sitetheme'] ? "" : $_POST['sitetheme']);
				save_prefs("user");
			}

			define("USERTHEME", ($user_pref['sitetheme'] && file_exists(e_THEME.$user_pref['sitetheme']."/theme.php") ? $user_pref['sitetheme'] : FALSE));
			global $ADMIN_DIRECTORY, $PLUGINS_DIRECTORY;
			if ($result['user_admin']) {
				define("ADMIN", TRUE);
				define("ADMINID", $result['user_id']);
				define("ADMINNAME", $result['user_name']);
				define("ADMINPERMS", $result['user_perms']);
				define("ADMINEMAIL", $result['user_email']);
				define("ADMINPWCHANGE", $result['user_pwchange']);
			} else {
				define("ADMIN", FALSE);
			}
		} else {
			define("USER", FALSE);
			define("USERTHEME", FALSE);
			define("ADMIN", FALSE);
			define("CORRUPT_COOKIE", TRUE);
			define("USERCLASS", "");
		}
	}

	define('USERCLASS_LIST', class_list());
	define('e_CLASS_REGEXP', "(^|,)(".str_replace(",", "|", USERCLASS_LIST).")(,|$)");
}

function cookie($name, $value, $expire, $path = "/", $domain = "", $secure = 0) {
	setcookie($name, $value, $expire, $path, $domain, $secure);
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function message_handler($mode, $message, $line = 0, $file = "") {
	e107_require_once(e_HANDLER."message_handler.php");
	show_emessage($mode, $message, $line, $file);
}

// -----------------------------------------------------------------------------
function table_exists($check) {
	if (!$GLOBALS['mySQLtablelist']) {
		$tablist=mysql_list_tables($GLOBALS['mySQLdefaultdb']);
		while (list($temp) = mysql_fetch_array($tablist)) {
			$GLOBALS['mySQLtablelist'][] = $temp;
		}
	}

	$mltable=MPREFIX.strtolower($check);

	foreach ($GLOBALS['mySQLtablelist'] as $lang) {
		if (strpos($lang, $mltable) !== FALSE) {
			return TRUE;
		}
	}
}

function class_list($uid = '') {
	$clist=array();

	if ($uid == '')
	{
		if (USER === TRUE)
		{
			if(USERCLASS)
			{
				$clist=explode(',', USERCLASS);
			}
			$clist[]=e_UC_MEMBER;
			if (ADMIN === TRUE) {
				$clist[] = e_UC_ADMIN;
			}
		} else {
			$clist[] = e_UC_GUEST;
		}
		$clist[]=e_UC_READONLY;
		$clist[]=e_UC_PUBLIC;
		return implode(',', $clist);
	}
}

// ---------------------------------------------------------------------------
function e107_include($fname) {
	global $e107_debug;
	$ret = ($e107_debug ? include($fname) : @include($fname));
	return $ret;
}

function e107_include_once($fname) {
	global $e107_debug;
	if(is_readable($fname)){
		$ret = (!$e107_debug)? @include_once($fname) : include_once($fname);
	}
	return (isset($ret)) ? $ret : "";
}

function e107_require_once($fname) {
	global $e107_debug;
	$ret = ($e107_debug ? require_once($fname) : @require_once($fname));
	return $ret;
}

function e107_require($fname) {
	global $e107_debug;
	$ret = ($e107_debug ? require($fname) : @require($fname));
	return $ret;
}

function utf8_html_entity_decode($string) {
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	foreach($trans_tbl as $k => $v) {
		$ttr[$v] = utf8_encode($k);
	}
	$string = strtr($string, $ttr);
	return $string;
}

if(!function_exists("print_a")) {
	function print_a($var, $return = false) {
		if(!$return){
			echo '<pre>'.print_r($var, true).'</pre>';
			return true;
		} else {
			return '<pre>'.print_r($var, true).'</pre>';
		}
	}
}

function force_userupdate(){
	// returns TRUE if required fields are empty in the user's profile.
	global $sql,$pref,$currentUser;
	$signupval = explode(".", $pref['signup_options']);
	if(in_array("2",$signupval)){
		$signup_name = array("login", "homepage", "icq", "aim", "msn", "birthday", "location", "signature", "image", "timezone", "usrclass");
		foreach($signupval as $key=>$sign){
			$field = "user_".$signup_name[$key];
			$req = $signupval[$key];
			if($req ==2 && $currentUser[$field] == ""){
			 //	echo "field = $field ";
			 return TRUE;
			}
		}
	}

	// extended user.
	if($sql -> db_Select("user_extended_struct", "user_extended_struct_name", " user_extended_struct_required = '1' ")){
		while($row = $sql -> db_Fetch()){
			//extract($row); //really neccessary?
			$user_extended_struct_name = "user_{$row['user_extended_struct_name']}";
			if(!$currentUser[$user_extended_struct_name]){
				return TRUE;
			}
		}
	}

	return FALSE;
}

class error_handler {

	var $errors;
	var $debug = false;

	function error_handler() {
		if ((isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'debug=') !== FALSE) || isset($_COOKIE['e107_debug_level'])) {
			$this->debug = true;
			error_reporting(E_ALL);
		} else {
			error_reporting(E_ERROR | E_PARSE);
		}
	}

	function handle_error($type, $message, $file, $line, $context) {
		switch($type) {
			case E_NOTICE:
			if ($this->debug == true) {
				$error['short'] = "Notice: {$message}, Line {$line} of {$file}<br />\n";
				$trace = debug_backtrace();
				$backtrace[0] = (isset($trace[1]) ? $trace[1] : "");
				$backtrace[1] = (isset($trace[2]) ? $trace[2] : "");
				$error['trace'] = $backtrace;
				$this->errors[] = $error;
			}
			break;
			case E_WARNING:
			if ($this->debug == true) {
				$error['short'] = "Warning: {$message}, Line {$line} of {$file}<br />\n";
				$trace = debug_backtrace();
				$backtrace[0] = (isset($trace[1]) ? $trace[1] : "");
				$backtrace[1] = (isset($trace[2]) ? $trace[2] : "");
				$error['trace'] = $backtrace;
				$this->errors[] = $error;
			}
			break;
			case E_USER_ERROR:
			if ($this->debug == true) {
				$error['short'] = "Internal Error Message: {$message}, Line {$line} of {$file}<br />\n";
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

	function return_errors() {
		$index = 0; $colours[0] = "#C1C1C1"; $colours[1] = "#B6B6B6";
		$ret = "<table class='fborder'>\n";
		foreach ($this->errors as $key => $value) {
			$ret .= "\t<tr>\n\t\t<td class='forumheader3' >{$value['short']}</td><td><input class='button' type ='button' style='cursor: hand; cursor: pointer;' size='30' value='Back Trace' onclick=\"expandit('bt_{$key}')\" /></t>\n\t</tr>\n";
			$ret .= "\t<tr>\n<td style='display: none;' colspan='2' id='bt_{$key}'>".print_a($value['trace'], true)."</td></tr>\n";
			if($index == 0) { $index = 1; } else { $index = 0; }
		}
		$ret .= "</table>";
		return $ret;
	}

	function trigger_error($information, $level) {
		trigger_error($information);
	}
}

$sql->db_Mark_Time('(After class2)');

?>