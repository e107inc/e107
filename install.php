<?php
/*
* e107 website system
*
* Copyright (C) 2008-2012 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* e107 v2.x Installation file
*
*/

// minimal software version
define('MIN_PHP_VERSION',   '8.0');
define('MIN_MYSQL_VERSION', '4.1.2');
define('MAKE_INSTALL_LOG', true);

// ensure CHARSET is UTF-8 if used
//define('CHARSET', 'utf-8');

/* Default Options and Paths for Installer */
$MySQLprefix	     = 'e107_';
$HANDLERS_DIRECTORY  = "e107_handlers/"; // needed for e107 class init

header('Content-type: text/html; charset=utf-8');

define("e107_INIT", TRUE);
define("DEFAULT_INSTALL_THEME", 'bootstrap5');
define('HELPICON', "<span class='e-tip glyphicon glyphicon-question-sign' style='float:right;padding-top:3px'></span>"); // <i class="glyphicon glyphicon-question-sign"></i>

$e107info = array();
require_once("e107_admin/ver.php");

define("e_VERSION", $e107info['e107_version']);

$e_ROOT = realpath(__DIR__ ."/");
if ((substr($e_ROOT,-1) !== '/') && (substr($e_ROOT,-1) !== '\\') )
{
	$e_ROOT .= DIRECTORY_SEPARATOR;  // Should function correctly on both windows and Linux now.
}
define('e_ROOT', $e_ROOT);
unset($e_ROOT);


class installLog
{

	const logFile = "e107Install.log";

	// The install log lives under e107_system/, whose shipped .htaccess denies
	// web access. The per-site logs directory (e107_system/<hash>/logs) needs the
	// site hash, which is not known this early - it derives from the database name
	// and prefix collected mid-wizard - so the installer logs to the system root.
	const logDir = "e107_system";


	/**
	 * @param Throwable $exception
	 * @return void
	 */
	static function exceptionHandler($exception)
	{
		$message = $exception->getMessage();
		self::add($message, "error");
	}

	static function errorHandler($errno=null, $errstr=null, $errfile=null, $errline=null)
	{

		$error = "Error on line ".$errline." in file ".$errfile." : ".$errstr;

		switch($errno)
		{
			case E_ERROR:
		    case E_CORE_ERROR:
		    case E_COMPILE_ERROR:
		    case E_PARSE:
		        self::add($error, "fatal");
		        break;
		    case E_USER_ERROR:
		    case E_RECOVERABLE_ERROR:
		        self::add($error, "error");
		        break;
		    case E_WARNING:
		    case E_CORE_WARNING:
		    case E_COMPILE_WARNING:
		    case E_USER_WARNING:
		        self::add($error, "warn");
		        break;
		    case E_NOTICE:
		    case E_USER_NOTICE:
		        self::add($error, "notice");
		        break;
		    case E_STRICT:
		        self::add($error, "debug");
		        break;
		    default:
		        if(!empty($errno))
		        {
		             self::add($error, "warn");
		        }


		}

		return true;
	}


	static function clear()
	{
		$dir = __DIR__ .'/'.self::logDir;
		if(!MAKE_INSTALL_LOG || !is_writable($dir))
		{
			return null;
		}

		$logFile = $dir .'/'.self::logFile;
		file_put_contents($logFile,'');

	}

	/**
	 * Write a line of text to the log file (if enabled) - prepend time/date, append \n
	 * @param string $message
	 * @param string $type
	 * @return null
	 */
	static function add($message, $type='info')
	{
		$dir = __DIR__ .'/'.self::logDir;
		if(!MAKE_INSTALL_LOG || !is_writable($dir))
		{
			return null;
		}

		$logFile = $dir .'/'.self::logFile;

		$now    = time();
		$message = $now.', '.date('c')."\t".$type."\t".$message."\n";

		file_put_contents($logFile, $message, FILE_APPEND);

		return null;
	}


}

set_exception_handler(array('installLog','exceptionHandler'));
set_error_handler(array('installLog',"errorHandler"));
register_shutdown_function(array('installLog',"errorHandler"));



/*define("e_UC_PUBLIC", 0);
define("e_UC_MAINADMIN", 250);
define("e_UC_READONLY", 251);
define("e_UC_GUEST", 252);
define("e_UC_MEMBER", 253);
define("e_UC_ADMIN", 254);
define("e_UC_NOBODY", 255);*/

define("E107_INSTALL",true);

if($_SERVER['QUERY_STRING'] !== "debug") // install.php?debug
{
	error_reporting(0); // suppress all errors unless debugging.
}
else
{
	error_reporting(E_ALL);
}

if($_SERVER['QUERY_STRING'] === 'clear')
{
	unset($_SESSION);
}

//error_reporting(E_ALL);

/*function e107_ini_set($var, $value)
{
	if (function_exists('ini_set'))
	{
		ini_set($var, $value);
	}
}*/

// setup some php options

ini_set('arg_separator.output',     '&amp;');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid',    0);

if (function_exists('date_default_timezone_set'))
{
	date_default_timezone_set('UTC');
}

define('MAGIC_QUOTES_GPC', false); // (ini_get('magic_quotes_gpc') ? true : false));

$php_version = PHP_VERSION;
if(version_compare($php_version, MIN_PHP_VERSION, "<"))
{
	die_fatal_error('A minimum version of PHP '.MIN_PHP_VERSION.' is required');   // no  LAN DEF translation accepted by lower versions <5.3
}

// Check needed to continue (extension check in stage 4 is too late)
if(!class_exists('DOMDocument', false))
{
	die_fatal_error("You need to install the DOM extension to install e107."); // NO LAN 
}

//  Ensure that '.' is the first part of the include path
$inc_path = explode(PATH_SEPARATOR, ini_get('include_path'));
if($inc_path[0] !== ".")
{
	array_unshift($inc_path, ".");
	$inc_path = implode(PATH_SEPARATOR, $inc_path);
	ini_set("include_path", $inc_path);
}
unset($inc_path);

if(!function_exists("mysql_connect")  && !defined('PDO::ATTR_DRIVER_NAME'))
{
	die_fatal_error("e107 requires PHP to be installed or compiled with PDO or the MySQL extension to work correctly, please see the MySQL manual for more information.");
}

# Check for the realpath(). Some hosts (I'm looking at you, Awardspace) are totally dumb and
# they think that disabling realpath() will somehow (I'm assuming) help improve their pathetic
# local security. Fact is, it just prevents apps from doing their proper local inclusion security
# checks. So, we refuse to work with these people.
$functions_ok = true;
$disabled_functions = ini_get('disable_functions');
if (trim($disabled_functions) != '')
{
	$disabled_functions = explode( ',', $disabled_functions );
	foreach ($disabled_functions as $function)
	{
		if(trim($function) === "realpath")
		{
			$functions_ok = false;
		}
	}
}
if($functions_ok == true && function_exists("realpath") == false)
{
	$functions_ok = false;
}
if($functions_ok == false)
{
	die_fatal_error("e107 requires the realpath() function to be enabled and your host appears to have disabled it. This function is required for some <b>important</b> security checks and there is <b>NO workaround</b>. Please contact your host for more information.");
}

//obsolete $installer_folder_name = 'e107_install';
include_once("./{$HANDLERS_DIRECTORY}core_functions.php");
include_once("./{$HANDLERS_DIRECTORY}e107_class.php");
require_once("./{$HANDLERS_DIRECTORY}install_state.php");

function check_class($whatever='')
{
	unset($whatever);
	return true;
}

function getperms($arg, $ap = '')
{
	unset($arg,$ap);
	return true;
}

/**
 * Inspect e107_config.php to classify the installation, reading both the v2.4
 * return-array format and the legacy globals format.
 *
 *  - fresh:     no usable config yet (missing, empty, or the 0-byte seed)
 *  - pending:   a provisioning token is present but no database is configured
 *  - installed: real database credentials are present
 *
 * The database itself is never consulted, so an outage cannot change the verdict.
 *
 * @return array{mode:string,token:string|null}
 */
function install_config_state()
{
	$file = 'e107_config.php';
	if(!is_file($file) || filesize($file) <= 1)
	{
		return array('mode' => 'fresh', 'token' => null);
	}

	$mySQLdefaultdb = null;
	$E107_CONFIG = array();
	$config = @include($file);

	if(is_array($config) && isset($config['database']))
	{
		$db = isset($config['database']['db']) ? $config['database']['db'] : null;
		$other = (isset($config['other']) && is_array($config['other'])) ? $config['other'] : array();
	}
	else
	{
		$db = $mySQLdefaultdb;
		$other = is_array($E107_CONFIG) ? $E107_CONFIG : array();
	}

	$token = (isset($other['install_token']) && is_string($other['install_token'])) ? $other['install_token'] : null;

	if(!empty($db))
	{
		return array('mode' => 'installed', 'token' => $token);
	}
	if($token !== null)
	{
		return array('mode' => 'pending', 'token' => $token);
	}

	return array('mode' => 'fresh', 'token' => null);
}

/**
 * Store the signed resume blob in a hardened, short-lived cookie so the same
 * browser can resume without re-pasting. Confidentiality of the cookie is
 * defence in depth; it is never trusted without a valid signature.
 *
 * @param string $value signed blob
 * @return void
 */
function install_set_state_cookie($value)
{
	$secure = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off');
	$path = defined('e_HTTP') ? e_HTTP : '/';

	// eShims::setcookie forwards the 7.3+ options array untouched on modern PHP
	// and folds SameSite into the path on the PHP 5.6 floor, so the SameSite
	// version branch lives in one place instead of being duplicated here.
	eShims::setcookie('e107install_state', $value, array(
		'expires'  => 0,
		'path'     => $path,
		'secure'   => $secure,
		'httponly' => true,
		'samesite' => 'Strict',
	));
}

$installState = install_config_state();

// Fail closed: a completed installation shuts the interactive installer out.
// Reinstalling requires removing e107_config.php from the filesystem. The
// unattended (?create_tables) entry is gated separately, in
// create_tables_unattended.
if($installState['mode'] === 'installed' && !isset($_GET['create_tables']))
{
	die_fatal_error("e107 is already installed. To reinstall, first remove <b>e107_config.php</b>.");
}

$GLOBALS['e_install_token'] = $installState['token'];

// Same-browser convenience: when no signed state is posted (e.g. the admin
// returned to the installer), fall back to the resume cookie. It is still only
// trusted after signature verification, exactly like the posted field.
if(!isset($_POST['previous_steps']) && isset($_COOKIE['e107install_state']))
{
	$_POST['previous_steps'] = $_COOKIE['e107install_state'];
}

$override = array();

if(isset($_POST['previous_steps']) && $installState['token'] !== null)
{
	$tmp = install_state_verify($_POST['previous_steps'], $installState['token']);
	if(is_array($tmp) && isset($tmp['paths']['hash']) && is_string($tmp['paths']['hash']) && preg_match('/^[a-f0-9]{10}$/', $tmp['paths']['hash']))
	{
		$override = array('site_path' => $tmp['paths']['hash']);
	}
	unset($tmp);
}

$e107_paths = array();
$e107 = e107::getInstance();
$ebase = realpath(__DIR__);
if($e107->initInstall($e107_paths, $ebase, $override)===false)
{
	die_fatal_error("Error creating the following empty file: <b>".$ebase.DIRECTORY_SEPARATOR."e107_config.php</b><br />Please create it manually and then run the installation again.");
}

unset($e107_paths,$override,$ebase);

// NEW - session handler
require_once(e_HANDLER.'session_handler.php');
define('e_SECURITY_LEVEL', e_session::SECURITY_LEVEL_NONE);
define('e_COOKIE', 'e107install');
e107::getSession(); // starts session, creates default namespace
// session_start();

function include_lan($path, $force = false)
{
	$result = include($path);

	if(is_array($result))
	{
		foreach($result as $key => $value)
		{
			if(!defined($key))
			{
				define($key, $value);
			}
		}

	}

}
//obsolete $e107->e107_dirs['INSTALLER'] = "{$installer_folder_name}/";

if(isset($_GET['create_tables']))
{
	create_tables_unattended();
	exit;
}



$e_install = new e_install();
$e_forms = new e_forms();

$e_install->template->SetTag("installer_css_http", $_SERVER['PHP_SELF']."?object=stylesheet");
//obsolete $e_install->template->SetTag("installer_folder_http", e_HTTP.$installer_folder_name."/");
$e_install->template->SetTag("files_dir_http", e_FILE_ABS);

$e_install->renderPage();

/**
 * Set Cookie
 * @param string $name
 * @param string $value
 * @param int $expire seconds
 * @param string $path
 * @param string $domain
 * @param boolean $secure
 * @return void
 */
function cookie($name, $value, $expire=0, $path = e_HTTP, $domain = '', $secure = false)
{
	setcookie($name, $value, $expire, $path, $domain, (bool) $secure);
}

class e_install
{
//	private   $paths;
	public    $template;
	private   $debug_info;
//	private   $debug_db_info;
	private   $e107;
	public    $previous_steps;
	private   $stage;
	private   $post_data;
	private   $required = array();

	private   $session;
	protected $pdo = false;
	protected $debug = false;
	private   $token = null;       // provisioning token / HMAC key for this request
	private   $validState = true;  // was signature-verified wizard state presented?
	private   $locked = false;     // does e107_config.php already hold a lock token?

	//	public function __construct()
	function __construct()
	{
		// notice removal, required from various core routines
		define('USERID', 1);
		define('USER', true);
		define('ADMIN', true);
	//	define('e_UC_MAINADMIN', 250);
		define('E107_DEBUG_LEVEL',0);

		if($_SERVER['QUERY_STRING'] === "debug")
		{
			$this->debug = true;
		}

		if(defined('PDO::ATTR_DRIVER_NAME'))
		{
			 $this->pdo = true;
			 define('e_PDO', true);
		}

		if(!empty($this->previous_steps['mysql']['prefix']))
		{
			define('MPREFIX', $this->previous_steps['mysql']['prefix']);
		}

		$tp = e107::getParser();
		
		// session instance
		$this->session = e107::getSession();

		// $this->logLine('Query string: ');
		$this->template = new SimpleTemplate();

		if(ob_get_level() > 1)
		{
			while (@ob_end_clean())
			{
				unset($whatever);
			}
		}
		global $e107;
		$this->e107 = $e107;

		$this->token = isset($GLOBALS['e_install_token']) ? $GLOBALS['e_install_token'] : null;
		$this->locked = ($this->token !== null);

		$verified = null;
		if(isset($_POST['previous_steps']) && $this->token !== null)
		{
			$verified = install_state_verify($_POST['previous_steps'], $this->token);
		}

		if($verified !== null)
		{
			// Trusted, signature-verified state.
			// Save unfiltered admin password (#4004) - " are transformed into &#34;
			$rawAdminPass = isset($verified['admin']['password']) ? $verified['admin']['password'] : null;

			$this->previous_steps = $tp->filter($verified);

			if($rawAdminPass !== null)
			{
				$this->previous_steps['admin']['password'] = $rawAdminPass;
			}

			$this->validState = true;
		}
		else
		{
			// Fresh installs legitimately carry no signed state (stage 1 -> 2). A
			// locked install presenting no valid state is gated by renderPage().
			$this->previous_steps = array();
			$this->validState = !$this->locked;
		}

		unset($_POST['previous_steps']);
		$this->get_lan_file();
		$this->post_data = $tp->filter($_POST);



		$this->template->SetTag('required', '');
		if(isset($this->previous_steps['language']))
		{
			define("e_LANGUAGE", $this->previous_steps['language']);
			include_lan(e_LANGUAGEDIR.e_LANGUAGE."/".e_LANGUAGE.".php");
			include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_admin.php");
		}
	}



	function add_button($id, $title='', $align = "right", $type = "submit")
	{

		global $e_forms;

		$e_forms->form .= "<div class='buttons-bar inline' style='text-align: {$align}; z-index: 10;'>";
		if($id !== 'start')
		{
			//		$this->form .= "<a class='btn btn-large ' href='javascript:history.go(-1)'>&laquo; ".LAN_BACK."</a>&nbsp;";
			$prevStage = ($this->stage - 1);
			$e_forms->form .= "<button class='btn btn-default btn-secondary btn-large no-validate ' name='back' value='".$prevStage."' type='submit'>&laquo; ".LAN_BACK."</button>&nbsp;";
		}
		if($id !== 'back')
		{
			$e_forms->form .= "<input type='{$type}' id='{$id}' name='{$id}' value='{$title} &raquo;' class='btn btn-large btn-primary' />";
		}
		$e_forms->form .= "</div>\n";
	}

	function renderPage()
	{
		if(!isset($_POST['stage']))
		{
			$_POST['stage'] = 1;
		}
		$_POST['stage'] = (int) $_POST['stage'];

		if(!empty($_POST['back']))
		{
			$_POST['stage'] = (int) $_POST['back'];
		}

		if($this->locked && !$this->validState)
		{
			// A lock exists but no validly-signed state was presented (lost
			// session or tampered field): gate behind a paste-to-resume prompt
			// instead of acting on untrusted input or restarting the wizard.
			$this->stage_resume();
		}
		else
		{
			// From stage 2 onwards the installation is locked to a provisioning
			// token; mint it on first advance, then reuse it every request.
			if($_POST['stage'] >= 2)
			{
				$this->token = $this->ensureLock();
			}

			switch ($_POST['stage'])
			{
				case 1:
					$this->stage_1();
					break;
				case 2:
					$this->stage_2();
					break;
				case 3:
					$this->stage_3();
					break;
				case 4:
					$this->stage_4();
					break;
				case 5:
					$this->stage_5();
					break;
				case 6:
					$this->stage_6();
					break;
				case 7:
					$this->stage_7();
					break;
				case 8:
					$this->stage_8();
					break;
				default:
					$this->raise_error("Install stage information from client makes no sense to me.");
			}
		}

		if($_SERVER['QUERY_STRING'] === "debug")
		{
			$this->template->SetTag("debug_info", print_a($this->previous_steps,TRUE));
		}
		else
		{
			// Never render the install object here: it holds the provisioning
			// token (the HMAC signing key) and the submitted credentials. Show
			// only the structured error info.
			$this->template->SetTag("debug_info", (!empty($this->debug_info) ? print_a($this->debug_info,TRUE) : ""));
		}

		echo $this->template->ParseTemplate(template_data(), TEMPLATE_TYPE_DATA);
	}

	/**
	 * Ensure the installation is locked to a provisioning token, minting one on
	 * first advance and adopting any existing lock thereafter. The token is the
	 * HMAC key that authenticates wizard state for the rest of the install.
	 *
	 * @return string the provisioning token
	 */
	private function ensureLock()
	{
		if($this->token !== null)
		{
			return $this->token; // already locked; never rotate mid-install
		}

		// Re-read in case another request minted the lock since the file-scope read.
		$state = install_config_state();
		if($state['token'] !== null)
		{
			$GLOBALS['e_install_token'] = $state['token'];
			return $state['token'];
		}

		$token = install_state_generate_token();
		if($token === false)
		{
			die_fatal_error("e107 could not generate a secure installation token. PHP needs a CSPRNG (random_bytes or OpenSSL).");
		}

		$this->write_config($this->buildPendingConfig($token, install_state_sign($this->strippedSteps(), $token)));
		$GLOBALS['e_install_token'] = $token;
		$this->locked = true;

		return $token;
	}

	/**
	 * Render the resume gate shown when a lock exists but no valid signed state
	 * was presented. The admin pastes the resume blob (recoverable from
	 * e107_config.php) to continue; the token itself is never shown.
	 *
	 * @return null
	 */
	private function stage_resume()
	{
		global $e_forms;
		$this->stage = 1;

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", '');
		$this->template->SetTag("stage_title", LANINS_004);
		$this->template->SetTag("percent", 0);
		$this->template->SetTag("bartype", 'warning');

		$requested = isset($_POST['stage']) ? (int) $_POST['stage'] : 2;
		if($requested < 2)
		{
			$requested = 2;
		}

		$e_forms->start_form("resume", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));
		$e_forms->add_plain_html(
			"<div class='alert alert-warning'>An installation is already in progress and is locked to this server.<br />"
			."Paste your resume code below to continue. It is stored in <b>e107_config.php</b> as <b>install_state</b>.</div>"
			."<div class='form-group'><textarea class='form-control' name='previous_steps' rows='4' style='width:100%;' required='required'></textarea></div>"
		);
		$e_forms->add_hidden_data("stage", $requested);
		$this->add_button("start", LAN_CONTINUE);

		$this->template->SetTag("stage_content", $e_forms->return_form());

		return null;
	}

	/**
	 * Wizard state minus the administrator login password. Used for the recovery
	 * copies (cookie and on-disk e107_config.php) so the login password is never
	 * persisted; it is re-collected if a recovery resume lands past that stage.
	 *
	 * @return array
	 */
	private function strippedSteps()
	{
		$steps = $this->previous_steps;
		if(isset($steps['admin']['password']))
		{
			unset($steps['admin']['password']);
		}

		return $steps;
	}

	/**
	 * Escape a value for safe interpolation inside a single-quoted PHP string in
	 * a generated config file. Prevents a quote in a password or other field from
	 * breaking out of the literal (let alone injecting PHP).
	 *
	 * @param mixed $value
	 * @return string
	 */
	private function configString($value)
	{
		return str_replace(array('\\', "'"), array('\\\\', "\\'"), (string) $value);
	}

	/**
	 * Build the install-pending e107_config.php: a lock holding the provisioning
	 * token and the latest recovery blob, but no database credentials. class2.php
	 * treats this as "not installed" and redirects back to the installer.
	 *
	 * @param string $token
	 * @param string $signedBlob recovery copy of the signed wizard state
	 * @return string
	 */
	public function buildPendingConfig($token, $signedBlob)
	{
		$tokenStr = $this->configString($token);
		$blobStr  = $this->configString($signedBlob);

		return "<?php
/*
 * e107 website system
 *
 * Installation in progress. This is a temporary lock written by the e107
 * installer. Delete this file to abandon the installation and start over.
 */

return [
    'database' => [],
    'paths'    => [],
    'other'    => [
        'install_pending' => true,
        'install_token'   => '{$tokenStr}',
        'install_state'   => '{$blobStr}',
    ]
];
";
	}

	/**
	 * Build the finished v2.4 e107_config.php. The site hash is resolved by the
	 * caller (server-side, never from client state) and every interpolated value
	 * is escaped, so generated wizard input can neither break the file nor inject
	 * PHP.
	 *
	 * @param array  $steps    previous_steps
	 * @param string $sitePath the already-resolved site hash, recomputed
	 *                         server-side from the database name and prefix
	 * @return string
	 */
	public function buildConfigFile($steps, $sitePath)
	{
		$overridable = $this->e107->overridableDirs();
		$mustComment = array(
			'HELP_DIRECTORY',      // derived from DOCS_DIRECTORY
			'DOWNLOADS_DIRECTORY', // derived from MEDIA_DIRECTORY + site_path
			'UPLOADS_DIRECTORY',   // derived from SYSTEM_DIRECTORY + site_path
			'LOGS_DIRECTORY',      // derived from SYSTEM_DIRECTORY + site_path
		);

		$v24PathLines = '';
		foreach($overridable as $name => $default)
		{
			$prefix = in_array($name, $mustComment, true) ? '// ' : '';
			$short = strtolower(str_replace('_DIRECTORY', '', $name));
			$v24PathLines .= '        '.$prefix.str_pad("'".$short."'", 11)." => '".$default."',\n";
		}

		$server   = $this->configString($steps['mysql']['server']);
		$user     = $this->configString($steps['mysql']['user']);
		$password = $this->configString($steps['mysql']['password']);
		$db       = $this->configString($steps['mysql']['db']);
		$prefix   = $this->configString($steps['mysql']['prefix']);
		$email    = $this->configString(isset($steps['admin']['email']) ? $steps['admin']['email'] : '');

		$hash = $this->configString($sitePath);

		return "<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-".date('Y')." e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 configuration file
 *
 * This file has been generated by the installation script on ".date('r').".
 */

// -- Optional --
// const e_EMAIL_CRITICAL = '{$email}';  // email the admin if a critical error occurs.
// const e_LOG_CRITICAL = true; // log critical errors but do not display them to the user.
// const e_DEBUG = true;  // Enable debug mode to allow displaying of errors
// const e_HTTP_STATIC = 'https://static.mydomain.com/';  // Use a static subdomain for js/css/images etc.
// const e_MOD_REWRITE_STATIC = true; // Rewrite static image urls.
// const e_GIT = 'path-to-git';  // Path to GIT for developers
// const X_FRAME_SAMEORIGIN = false; // Option to override X-Frame-Options


return [
    'database' => [
        'server'   => '{$server}',
        'user'     => '{$user}',
        'password' => '{$password}',
        'db'       => '{$db}',
        'prefix'   => '{$prefix}',
        'charset'  => 'utf8mb4',
    ],
    // Uncomment any line below to override the default directory layout.
    'paths' => [
{$v24PathLines}    ],
    'other' => [
        'site_path' => '{$hash}',
    ]
];
";
	}

	function raise_error($details)
	{
		$this->debug_info[] = array (
		'info' => array (
			'details' => $details,
			// IGNORE_ARGS so a captured frame cannot carry a password or the
			// provisioning token into the rendered error output.
			'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
			)
		);
	}

	function display_required()
	{
		if(empty($this->required))
		{
			return;
		}
		$this->required = array_filter($this->required);
		if(!empty($this->required))
		{
			$this->template->SetTag("required","<div class='message'>". implode("<br />",$this->required)."</div>");
			$this->required = array();
		}
	}

	/**
	 * Stage 1
	 * @return null
	 */
	private function stage_1()
	{
		global $e_forms;
		$this->stage = 1;
		installLog::clear();
		installLog::add('Stage 1 started');


		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_003);
		$this->template->SetTag("stage_title", LANINS_004);
		$this->template->SetTag("percent", 10);
		$this->template->SetTag("bartype", 'warning');
		
		$e_forms->start_form("language_select", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));
		$e_forms->add_select_item("language", $this->get_languages(), "English");
		$this->finish_form();
		$this->add_button("start", LAN_CONTINUE);
		$output = "
			<div style='text-align: center;'>
				<div class='alert alert-info alert-block text-center'>
					<label for='language'>".LANINS_005."</label>
				</div>\n
				<br />\n
				<div class='col-md-offset-4 col-md-6'>
				".$e_forms->return_form()."
				</div><br />
			</div>";
		$this->template->SetTag("stage_content", $output);
		installLog::add('Stage 1 completed');

		return null;
	}

	private function stage_2()
	{
		global $e_forms;
		$this->stage = 2;
		installLog::add('Stage 2 started');

		if(!empty($_POST['language']))
		{
			$this->previous_steps['language'] = $_POST['language'];
		}
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_021);
		$this->template->SetTag("stage_title", LANINS_022);
		$this->template->SetTag("percent", 25);
		$this->template->SetTag("bartype", 'warning');
		
		if(!isset($this->previous_steps['mysql']['createdb']))
		{
			$this->previous_steps['mysql']['createdb'] = 1; // default to yes. 	
		}
		
		// $this->template->SetTag("onload", "document.getElementById('name').focus()");
		// $page_info = nl2br(LANINS_023);
		$page_info = "<div class='alert alert-block alert-info'>".LANINS_141."</div>";
		$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));
		$isrequired = (($_SERVER['SERVER_ADDR'] === "127.0.0.1") || ($_SERVER['SERVER_ADDR'] === "localhost") || ($_SERVER['SERVER_ADDR'] === "::1") || preg_match('/^192\.168\.\d{1,3}\.\d{1,3}$/',$_SERVER['SERVER_ADDR'])) ? "" :  "required='required'"; // Deals with IP V6, and 192.168.x.x address ranges, could be improved to validate x.x to a valid IP but for this use, I dont think its required to be that picky.

		$output = "
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			<table class='table table-striped table-bordered' >
				<tr>
					<td style='border-top: 1px solid #999;'><label for='server'>".LANINS_024."</label>".HELPICON."
					<span class='field-help'>".LANINS_030."</span></td>
					<td style='border-top: 1px solid #999;'>
						<input class='form-control input-large' type='text' id='server' name='server' autofocus size='40' value='".varset($this->previous_steps['mysql']['server'],'localhost')."' maxlength='100' required='required' />
						
					</td>
				</tr>
				
				<tr>
					<td><label for='name'>".LANINS_025."</label>".HELPICON."<span class='field-help'>".LANINS_031."</span></td>
					<td>
						<input class='form-control input-large' type='text' name='name' id='name' value='".varset($this->previous_steps['mysql']['user'])."' size='40'  maxlength='100' required='required' />
					</td>
				</tr>
				
				<tr>
					<td><label for='password'>".LANINS_026."</label>".HELPICON."<span class='field-help'>".LANINS_032."</span></td>
					<td>
						<input class='form-control input-large' type='password' name='password' size='40' id='password' value='".varset($this->previous_steps['mysql']['password'])."' maxlength='100' {$isrequired}  pattern='[^\x22]+' />

					</td>
				</tr>
				
				<tr>
					<td><label for='db'>".LANINS_027."</label>".HELPICON."<span class='field-help'>".LANINS_033."</span></td>
					<td class='form-inline'>
						<input class='form-control input-large' type='text' name='db' size='20' id='db' value='".varset($this->previous_steps['mysql']['db'])."' maxlength='100' required='required' pattern='^[a-zA-Z0-9][a-zA-Z0-9_-]*' />
						<label class='checkbox-inline'><input type='checkbox' name='createdb' value='1' ".($this->previous_steps['mysql']['createdb'] ==1 ? "checked='checked'" : "")." /><small>".LANINS_028."</small></label>
						
					</td>
				</tr>
				
				<tr>
					<td><label for='prefix'>".LANINS_029."</label>".HELPICON."<span class='field-help'>".LANINS_034."</span></td>
					<td>
						<input class='form-control input-large' type='text' name='prefix' size='20' id='prefix' value='e107_'  pattern='[a-z0-9]*_$' maxlength='100' required='required' />
					</td>
				</tr>
			</table>
			<br /><br />
			</div>
			\n";
			
		$e_forms->add_plain_html($output);



		$this->finish_form();
		$this->add_button("submit", LAN_CONTINUE);
		$this->template->SetTag("stage_content", $page_info.$e_forms->return_form());
		installLog::add('Stage 2 completed');
	}

	/**
	 *	Replace hash paths and create folders if needed.
	 *
	 *	@return null
	 */	
	private function updatePaths()
	{
		$hash = $this->e107->makeSiteHash($this->previous_steps['mysql']['db'],$this->previous_steps['mysql']['prefix']);
		$this->e107->site_path = $hash;	
		
		$this->previous_steps['paths']['hash'] = $hash;

		installLog::add("Directory Hash Set: ".$hash);

		$omit = array('FILES_DIRECTORY','WEB_IMAGES_DIRECTORY');
		
		foreach($this->e107->e107_dirs as $dir => $p)
		{
			if(in_array($dir, $omit)) { continue; }	
			
			$this->e107->e107_dirs[$dir] = str_replace("[hash]", $hash, $this->e107->e107_dirs[$dir]);
					
			if(!is_dir($this->e107->e107_dirs[$dir]))
			{
				@mkdir($this->e107->e107_dirs[$dir]);	
			}					
		}

		return null;
	}

	private function stage_3()
	{

		global $e_forms;

		$this->stage = 3;
		$alertType = 'warning';
		installLog::add('Stage 3 started');

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_036);
		$this->template->SetTag("onload", "document.getElementById('name').focus()");
		$this->template->SetTag("percent", 40);
		$this->template->SetTag("bartype", 'warning');
		$tp = e107::getParser();
	
		if(!empty($_POST['server']))
		{
			$this->previous_steps['mysql']['server']    = trim($tp->filter($_POST['server']));
			$this->previous_steps['mysql']['user']      = trim($tp->filter($_POST['name']));
			$this->previous_steps['mysql']['password']  = trim($tp->filter($_POST['password']));
			$this->previous_steps['mysql']['db']        = trim($tp->filter($_POST['db']));
			$this->previous_steps['mysql']['createdb']  = isset($_POST['createdb']) && $_POST['createdb'] == true;
			$this->previous_steps['mysql']['prefix']    = trim($tp->filter($_POST['prefix']));

			$this->setDb();
		}
		
		if(!empty($_POST['overwritedb']))
		{
			$this->previous_steps['mysql']['overwritedb'] = 1;
		}
					
		$success = $this->check_name($this->previous_steps['mysql']['db']) && $this->check_name($this->previous_steps['mysql']['prefix'], TRUE);
		
		if ($success)
		{
			$success = $this->checkDbFields($this->previous_steps['mysql']);		// Check for invalid characters
		}
		
		if(!$success || $this->previous_steps['mysql']['server'] == "" || $this->previous_steps['mysql']['user'] == "")
		{
			$this->stage = 3;
			$this->template->SetTag("stage_num", LANINS_021);
			$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));
			$head = LANINS_039."<br /><br />\n";
			$output = "
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			<table class='table table-bordered table-striped'>
				<tr>
					<td style='border-top: 1px solid #999;'><label for='server'>".LANINS_024."</label></td>
					<td style='border-top: 1px solid #999;'><input class='form-control' type='text' id='server' name='server' size='40' value='{$this->previous_steps['mysql']['server']}' maxlength='100' required /></td>
					<td style='width: 40%; border-top: 1px solid #999;'>".LANINS_030."</td>
				</tr>

				<tr>
					<td><label for='name'>".LANINS_025."</label></td>
					<td><input class='form-control' type='text' name='name' id='name' size='40' value='{$this->previous_steps['mysql']['user']}' maxlength='100' onload='this.focus()'  /></td>
					<td>".LANINS_031."</td>
				</tr>

				<tr>
					<td><label for='password'>".LANINS_026."</label></td>
					<td><input class='form-control' type='password' name='password' id='password' size='40' value='{$this->previous_steps['mysql']['password']}' maxlength='100' /></td>
					<td>".LANINS_032."</td>
				</tr>

				<tr>
					<td><label for='db'>".LANINS_027."</label></td>
					<td><input type='text' name='db' id='db' size='20' value='{$this->previous_steps['mysql']['db']}' maxlength='100' />
						<br /><label class='defaulttext'><input type='checkbox' name='createdb' " .($this->previous_steps['mysql']['createdb'] == 1 ? " checked='checked'" : "") . " value='1' />".LANINS_028."</label></td>
					<td>".LANINS_033."</td>
				</tr>

				<tr>
					<td><label for='prefix'>".LANINS_029."</label></td>
					<td><input type='text' name='prefix' id='prefix' size='20' value='{$this->previous_steps['mysql']['prefix']}'  maxlength='100' /></td>
					<td>".LANINS_034."</td>
				</tr>";
				
			if (!$success)
			{
				$output .= "<tr><td colspan='3'>".LANINS_105."</td></tr>";
			}
			
			$output .= "
			</table>
			<br /><br />
			</div>
			\n";
			$e_forms->add_plain_html($output);
			$this->add_button("submit", LAN_CONTINUE);
			$this->template->SetTag("stage_title", LANINS_040);
		}
		else
		{
			$this->template->SetTag("stage_title", LANINS_037.($this->previous_steps['mysql']['createdb'] == 1 ? LANINS_038 : ""));		

			$sql = e107::getDb();
			if (!$res = $sql->connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']))

	//		if (!$res = @mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']))
			{
				$success = FALSE;
				$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));
				$page_content = LANINS_041.nl2br("\n\n<b>".LANINS_083."\n</b><i>".$sql->getLastErrorText()."</i>");
				
				$alertType = 'error';
			}
			elseif(($this->previous_steps['mysql']['createdb'] == 1) && empty($this->previous_steps['mysql']['overwritedb']) && $sql->database($this->previous_steps['mysql']['db'], $this->previous_steps['mysql']['prefix']))
			{
				$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));
				$head = str_replace('[x]', '<b>'.$this->previous_steps['mysql']['db'].'</b>', "<div class='alert alert-warning'>". LANINS_127."</div>");
				$alertType = 'error';
				$this->add_button('overwritedb', LANINS_128);
			/*	$e_forms->add_plain_html("
				<input type='submit' id='overwritedb' name='overwritedb' value=\"".LANINS_128." &raquo;\" class='btn btn-large btn-primary' />"
				
				);*/

				$this->finish_form(3);
				$this->template->SetTag("stage_content", "<div class='alert alert-block alert-{$alertType}'>".$head."</div>".$e_forms->return_form());
				installLog::add('Stage 3 completed');
				return; 
			}
			else
			{
				$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));
				$page_content = "<span class='glyphicon glyphicon-ok'></span> ".LANINS_042;
				// @TODO Check database version here?
/*
				$mysql_note = mysql_get_server_info();
				if (version_compare($mysql_note, MIN_MYSQL_VERSION, '>='))
				{
				    $success = FALSE;

				}
*/
				// Do brute force for now - Should be enough
				
				if(!empty($this->previous_steps['mysql']['overwritedb']))
				{
					if($this->dbqry('DROP DATABASE `'.$this->previous_steps['mysql']['db'].'` '))
					{
						$page_content .= "<br /><span class='glyphicon glyphicon-ok'></span>  ".LANINS_136;
					}
					else 
					{
						$success = false;
						$page_content .= "<br /><br />".LANINS_043.nl2br("\n\n<b>".LANINS_083."\n</b><i>".e107::getDb()->getLastErrorText()."</i>");
					}
						
				}
				
				if($this->previous_steps['mysql']['createdb'] == 1)
				{
					$notification = "<br /><span class='glyphicon glyphicon-ok'></span> ".LANINS_044;
				    $query = 'CREATE DATABASE `'.$this->previous_steps['mysql']['db'].'` CHARACTER SET `utf8mb4` ';
					
				}
				else
				{
					$notification = "<br /><span class='glyphicon glyphicon-ok'></span>  ".LANINS_137;
				    $query = 'ALTER DATABASE `'.$this->previous_steps['mysql']['db'].'` CHARACTER SET `utf8mb4` ';
				}

				if (!$this->dbqry($query))
				{
					$success = false;
					$alertType = 'error';
					$page_content .= "<br /><br />";
					$page_content .= (empty($this->previous_steps['mysql']['createdb'])) ? LANINS_129 : LANINS_043;


					$page_content .= nl2br("\n\n<b>".LANINS_083."\n</b><i>".e107::getDb()->getLastErrorText()."</i>");
				}
				else
				{
                    $this->dbqry('SET NAMES `utf8mb4`');

					$page_content .= $notification; // "
				}
			}
			
			if($success)
			{

				// $page_content .= "<br /><br />".LANINS_045."<br /><br />";
				$this->add_button("submit", LAN_CONTINUE);
				$alertType = 'success';
			}
			else 
			{
				$this->add_button("back", LAN_CONTINUE);
			}
			$head = $page_content;
		}
		if ($success)
		{
			$this->finish_form();
		}
		else
		{
			$this->finish_form(3);
		}

		$this->template->SetTag("stage_content", "<div class='alert alert-block alert-{$alertType}'>".$head."</div>".$e_forms->return_form());

		installLog::add('Stage 3 completed');

		return null;
	}

	private function stage_4()
	{
		global $e_forms;

		$this->stage = 4;
		installLog::add('Stage 4 started');

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_007);
		$this->template->SetTag("stage_title", LANINS_008);
		$this->template->SetTag("percent", 50);
		$this->template->SetTag("bartype", 'warning');
		$not_writable = $this->check_writable_perms();		// Some directories MUST be writable
		$opt_writable = $this->check_writable_perms('can_write');		// Some directories CAN optionally be writable
		$version_fail = false;
		$perms_errors = "";
		$mysql_pass = false;

		$this->setDb();

		if(count($not_writable))
		{
			$perms_pass = false;
			foreach ($not_writable as $file)
			{
				$perms_errors .= (substr($file, -1) === "/" ? LANINS_010a : LANINS_010)."<br /><b>{$file}</b><br />\n";
			}
			$perms_notes = LANINS_018;
		}
		elseif (count($opt_writable))
		{
			$perms_pass = true;
			foreach ($opt_writable as $file)
			{
				$perms_errors .= (substr($file, -1) === "/" ? LANINS_010a : LANINS_010)."<br /><b>{$file}</b><br />\n";
			}
			$perms_notes = LANINS_106;
		}
		elseif (install_config_state()['mode'] === 'installed')
		{	// Refuse to install over an already-configured site. The pending lock
			// (token only) is expected and allowed; a completed config is not.
			$perms_pass = FALSE;
			$perms_errors = LANINS_121;
			$perms_notes = "<span class='glyphicon glyphicon-remove'></span> ".LANINS_122;
		}
		else
		{
			$perms_pass = true;
			$perms_errors = "&nbsp;";
			$perms_notes = "<span class='glyphicon glyphicon-ok'></span> ".LANINS_017;
		}

		if(!function_exists("mysql_connect") && !defined('PDO::ATTR_DRIVER_NAME'))
		{
			$version_fail = true;
			$mysql_note = LAN_ERROR;
			$mysql_help = LANINS_012;
		}
		elseif (!e107::getDb()->connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']))
//		elseif (!@mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']))
		{
			$mysql_note = LAN_ERROR;
			$mysql_help = LANINS_013;
		}
		else
		{
		//	$mysql_note = mysql_get_server_info();
			$mysql_note = e107::getDb()->getServerInfo();

			if($this->pdo == true)
			{
				$mysql_note .= " (PDO)";
			}

			if (version_compare($mysql_note, MIN_MYSQL_VERSION, '>='))
			{
				$mysql_help = "<span class='glyphicon glyphicon-ok'></span> ".LANINS_017;
				$mysql_pass = true;
			}
			else
			{
				$mysql_help = "<span class='glyphicon glyphicon-remove'></span> ".LANINS_105;
			}
		}

		$php_version = PHP_VERSION;

		if(version_compare($php_version, MIN_PHP_VERSION, ">="))
		{
			$php_help = "<span class='glyphicon glyphicon-ok'></span> ".LANINS_017;
		}
		else
		{
			$php_help = "<span class='glyphicon glyphicon-remove'></span> ".LANINS_019;
		}



		$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));


		
		$permColor	= ($perms_pass == true) ? "text-success" : "text-danger";
		$PHPColor 	= ($version_fail == false) ? "text-success" : "text-danger";
		$mysqlColor	= ($mysql_pass == true) ? "text-success" : "text-danger";


		$extensionCheck = array(
			'pdo'      => array('label' => "PDO (MySQL)", 'status' => extension_loaded('pdo_mysql'), 'url' => 'https:/php.net/manual/en/book.pdo.php'),
			'xml'      => array('label' => LANINS_050, 'status' => function_exists('utf8_encode') && class_exists('DOMDocument', false), 'url' => 'http://php.net/manual/en/ref.xml.php'),
			'exif'     => array('label' => LANINS_048, 'status' => function_exists('exif_imagetype'), 'url' => 'http://php.net/manual/en/book.exif.php'),
			'fileinfo' => array('label' => "FileInfo. Extension", 'status' => extension_loaded('fileinfo'), 'url' => 'https://www.php.net/manual/en/book.fileinfo'),
			'curl'      => array('label' => 'Curl Library', 'status' => function_exists('curl_version'), 'url' => 'http://php.net/manual/en/book.curl.php'),
			'gd'        => array('label' => 'GD Library', 'status' => function_exists('gd_info'), 'url' => 'http://php.net/manual/en/book.image.php'),
			'mb'        => array('label' => 'MB String Library', 'status' => function_exists('mb_strimwidth'), 'url' => 'http://php.net/manual/en/book.mbstring.php'),
		);



		$output = "
			<table class='table table-striped table-bordered' style='width: 100%; margin-left: auto; margin-right: auto;'>
				<tr>
					<td style='width: 20%;'>".LANINS_014."</td>
					<td style='width: 40%;'>{$perms_errors}</td>
					<td class='{$permColor}' style='width: 40%;'>{$perms_notes}</td>
				</tr>
				
				<tr>
					<td>".LANINS_015."</td>
					<td>{$php_version}</td>
					<td class='{$PHPColor}'>{$php_help}</td>
				</tr>
				
				<tr>
					<td>MySQL</td>
					<td>{$mysql_note}</td>
					<td class='{$mysqlColor}'>{$mysql_help}</td>
				</tr>";

				foreach($extensionCheck as $ext)
				{
					$statusText = ($ext['status'] === true) ? LANINS_051 : LANINS_052;
					$statusColor = ($ext['status'] === true) ? "text-success" : "text-error";
					$statusIcon = ($ext['status'] === true) ? "<i class='glyphicon glyphicon-ok'></i> ".LANINS_017 : str_replace(array("[x]",'[y]'), array($ext['label'], "<a href='".$ext['url']."'>php.net</a>"), LANINS_145);

					$output .= "
					<tr>
						<td>".$ext['label']."</td>
						<td>".$statusText."</td>
						<td class='".$statusColor."'>".$statusIcon."</td>
					</tr>";
				}


				$output .= "
			</table>\n";

		if(!$perms_pass || (($extensionCheck['xml']['status'] !== true)))
		{
			$this->add_button("retest_perms", LANINS_009);
			$this->stage = 3; // make the installer jump back a step
		}
		elseif (!$version_fail && ($extensionCheck['xml']['status'] == true))
		{
			$this->add_button("continue_install", LAN_CONTINUE);
		}

		$this->finish_form();
		$this->template->SetTag("stage_content", $output.$e_forms->return_form());
		installLog::add('Stage 4 completed');
	}

	/**
	 * Install stage 5 - collect Admin Login Data.
	 *
	 * @return string|null HTML form of stage 5.
	 */
	private function stage_5()
	{
		global $e_forms;

		$this->setDb();
		$this->updatePaths(); // update dynamic paths and create media and system directories - requires mysql info.

		
		$this->stage = 5;
		installLog::add('Stage 5 started');

		$this->display_required();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_046);
		$this->template->SetTag("stage_title", defset('LANINS_147', 'Administration'));
		// $this->template->SetTag("onload", "document.getElementById('u_name').focus()");
		$this->template->SetTag("percent", 60);
		$this->template->SetTag("bartype", 'warning');

		$e_forms->start_form("admin_info", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));
		$output = "
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			<table class='table table-striped table-bordered'>
				<colgroup>
					<col style='width:35%' />
					<col  />
				</colgroup>
				<tr>
					<td><label for='u_name'>".LANINS_072."</label>".HELPICON."<span class='field-help'>".LANINS_073."</span></td>
					<td>
						<input class='form-control' type='text' autofocus name='u_name' id='u_name' placeholder='admin' size='30' required='required' value='".(isset($this->previous_steps['admin']['user']) ? $this->previous_steps['admin']['user'] : "")."' maxlength='60' />
						
					</td>
				</tr>
				
				<tr>
					<td><label for='d_name'>".LANINS_074."</label>".HELPICON."<span class='field-help'>".LANINS_123."</span></td>
					<td>
						<input class='form-control' type='text' name='d_name' id='d_name' size='30' placeholder='Administrator'  value='".(isset($this->previous_steps['admin']['display']) ? $this->previous_steps['admin']['display'] : "")."' maxlength='60' />
	
					</td>
				</tr>
				
				<tr>
					<td><label for='pass1'>".LANINS_076."</label>".HELPICON."<span class='field-help'>".LANINS_124."</span></td>
					<td>
						<input class='form-control' type='password' name='pass1' size='30' id='pass1' value='' maxlength='60' required='required' />
						
					</td>
				</tr>
				
				<tr>
					<td><label for='pass2'>".LANINS_078."</label>".HELPICON."<span class='field-help'>".LANINS_079."</span></td>
					<td>
						<input class='form-control' type='password' name='pass2' size='30' id='pass2' value='' maxlength='60' required='required' />
						
					</td>
				</tr>
				
				<tr>
					<td><label for='email'>".LANINS_080."</label>".HELPICON."<span class='field-help'>".LANINS_081."</span></td>
					<td>
						<input class='form-control' type='text' name='email' size='30' id='email' required='required' placeholder='admin@mysite.com' value='".(isset($this->previous_steps['admin']['email']) ? $this->previous_steps['admin']['email'] : '')."' maxlength='100' />
					
					</td>
				</tr>
				</table>
				
				<table class='table table-striped table-bordered'>
				<colgroup>
					<col style='width:35%' />
					<col  />
				</colgroup>
				<tr>
					<td><label for='admincss'>".LANINS_146."</label></td>
					<td>";

				$d = $this->get_theme_xml('bootstrap3');
				$opts = array();

				foreach($d['css'] as $val)
				{
					$key = $val['name'];

					if($key !== 'css/modern-light.css' && ($key !== 'css/modern-dark.css'))
					{
						continue;
					}

					$opts[$key] = array (
							'title'         => $val['info'],
							'preview'       => e_THEME."bootstrap3/".$val['thumbnail'],
							'description'   =>'',
							'category'=>''
							);


				}

				$output .= $this->thumbnailSelector('admincss', $opts, 'css/modern-dark.css');

				$output .= "
						
					
					</td>
				</tr>
			</table>
			<br /><br />
			</div>
			\n";
		$e_forms->add_plain_html($output);
		$this->finish_form();
		$this->add_button("submit", LAN_CONTINUE);
		$this->template->SetTag("stage_content", $e_forms->return_form());
		installLog::add('Stage 5 completed');

		return null;
	}

	/**
	 * Collect User's Website Preferences
	 *
	 * @return string|null HTML form of stage 6.
	 */
	private function stage_6()
	{
		global $e_forms;
		$tp = e107::getParser();
		$this->stage = 6;
		installLog::add('Stage 6 started');

		// -------------------- Save Step 5 Data -------------------------
		if(!vartrue($this->previous_steps['admin']['user']) || varset($_POST['u_name']))
		{
			$_POST['u_name'] = str_replace(array("'", '"'), "", $_POST['u_name']);
			$this->previous_steps['admin']['user'] = $tp->filter($_POST['u_name']);
		}

		if(!vartrue($this->previous_steps['admin']['display']) || varset($_POST['d_name']))
		{
			$_POST['d_name'] = str_replace(array("'", '"'), "", $_POST['d_name']);
			if ($_POST['d_name'] == "")
			{
				$this->previous_steps['admin']['display'] = $tp->filter($_POST['u_name']);
			}
			else
			{
				$this->previous_steps['admin']['display'] = $tp->filter($_POST['d_name']);
			}
		}

		if(!vartrue($this->previous_steps['admin']['email']) || varset($_POST['email']))
		{
			$this->previous_steps['admin']['email'] = $tp->filter($_POST['email'],'email');
		}

		if(varset($_POST['pass1']) || !vartrue($this->previous_steps['admin']['password']))
		{
			if($_POST['pass1'] != $_POST['pass2'])
			{
				$this->required['pass1'] = LANINS_049; // passwords don't match.
			}
			elseif(!vartrue($_POST['pass1']))
			{
				$this->required['pass1'] = LANINS_077;
			}
			else
			{
				$this->previous_steps['admin']['password'] = $_POST['pass1'];
			}
		}

		if(!empty($_POST['admincss']))
		{
			$this->previous_steps['prefs']['admincss'] = $tp->filter($_POST['admincss']);
		}
		else // empty
		{
			$this->previous_steps['prefs']['admincss'] = 'css/bootstrap-dark.min.css';
		}

		// -------------   Validate Step 5 Data. --------------------------
		if(!vartrue($this->previous_steps['admin']['user']) || !vartrue($this->previous_steps['admin']['password']))
		{
			$this->required['u_name'] = LANINS_086; //
		}

		if(!empty($this->required['u_name']) || !empty($this->required['pass1']))
		{
			return $this->stage_5();
		}

		// required for various core routines
		if(!defined('USERNAME'))
		{
			define('USERNAME', $this->previous_steps['admin']['user']);
			define('USEREMAIL', $this->previous_steps['admin']['email']);
		}

		// ------------- Step 6 Form --------------------------------
		$this->display_required();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_056);
		$this->template->SetTag("stage_title", LANINS_117); // Website Preferences;
		// $this->template->SetTag("onload", "document.getElementById('sitename').focus()");
		$this->template->SetTag("percent", 70);
		$this->template->SetTag("bartype", 'warning');

		$e_forms->start_form("pref_info", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));
		$output = "
			<div style='width: 100%; padding-left: auto; padding-right: auto; margin-bottom:20px'>
			<table class='table table-striped'>
			  	<colgroup>
					<col class='col-label'  />
					<col class='col-control'  />
      			</colgroup>
				<tr>
					<td><label for='sitename'>".LANINS_107."</label></td>
					<td>
						<input class='form-control' type='text' autofocus placeholder=\"".LANINS_108."\" required='required' name='sitename' id='sitename' size='30' value='".(vartrue($_POST['sitename']) ? $_POST['sitename'] : "")."' maxlength='60' />
					</td>
				</tr>

				<tr>
					<td><label>".LANINS_109."</label></td>
					<td style='padding-right:0'>

							";

				$themes = $this->get_themes();

				$opts = array();

				foreach($themes as $val)
				{

					/*if($val != 'bootstrap3' && $val != 'voux')
					{
						continue;
					}*/



					$themeInfo 	= $this->get_theme_xml($val);



					$opts[$val] = array(
						'title' =>vartrue($themeInfo['@attributes']['name']),
						'category' 	=> vartrue($themeInfo['category']),
						'preview'   =>  e_THEME.$val."/".$themeInfo['thumbnail'],
						'description'   => vartrue($themeInfo['info'])
					);


	/*				$title 		= vartrue($themeInfo['@attributes']['name']);
					$category 	= vartrue($themeInfo['category']);
					$preview    = e_THEME.$val."/".$themeInfo['thumbnail'];
					$description = vartrue($themeInfo['info']);

					if(!is_readable($preview))
					{
						continue;
					}


					$thumbnail = "<img class='img-responsive img-fluid thumbnail'  src='".$preview ."' alt='".$val."' />";


					$selected = ($val === DEFAULT_INSTALL_THEME) ? " checked" : "";

					$output .= "
									<div class='col-md-6 theme-cell' >
										<label class='theme-selection' title=\"".$description."\"><input type='radio' name='sitetheme' value='{$val}' required='required' $selected />
										<div>".$thumbnail."
										<h5>".$title." <small>(".$category.")</small><span class='glyphicon glyphicon-ok text-success'></span></h5>
										</div>
										</label>
									</div>";*/
				}

				$output .= $this->thumbnailSelector('sitetheme', $opts, DEFAULT_INSTALL_THEME);


				$output .= "

					</td>

				</tr>
				
				<tr>
					<td><label for='install_plugins'>".LANINS_118."</label></td>
					<td>
						<input type='checkbox' name='install_plugins' checked='checked' id='install_plugins' value='1' />
						<span class='field-help'>".LANINS_119."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='generate_content'>".LANINS_111."</label></td>
					<td>
						<input type='checkbox' name='generate_content' checked='checked' id='generate_content' value='1' />
						<span class='field-help'>".LANINS_112."</span>
					</td>
				</tr>
			</table>
			<br /><br />
			</div>
			\n";
		$e_forms->add_plain_html($output);
		$this->finish_form();
		$this->add_button("submit", LAN_CONTINUE);
		$this->template->SetTag("stage_content", $e_forms->return_form());
		installLog::add('Stage 6 completed');

		return null;
	}


	private function thumbnailSelector($name, $opts, $default='')
	{

		$ret = '';

		foreach($opts as $key=>$val)
		{

			if(!is_readable($val['preview']) || !is_file($val['preview']))
			{
				continue;
			}


			$thumbnail = "<img class='img-responsive img-fluid thumbnail'  src='".$val['preview'] ."' alt='".$key."' />";


			$selected = ($key === $default) ? " checked" : "";

			$categoryInfo = !empty($val['category']) ? "<small>(".$val['category'].")</small>" : "";

			$ret .= "
					<div class='col-md-6 theme-cell' >
						<label class='theme-selection' title=\"".$val['description']."\"><input type='radio' name='".$name."' value='{$key}' required='required' $selected />
							<div>".$thumbnail."
								<h5>".$val['title']." ".$categoryInfo."<span class='glyphicon glyphicon-ok text-success'></span></h5>
							</div>
						</label>
					</div>";
		}


		return $ret;

	}

	/**
	 * Resolve any remaining "[hash]" placeholders in $this->e107->e107_dirs
	 * and strip the duplicated site_path segment from the multisite
	 * SYSTEM_DIRECTORY and MEDIA_DIRECTORY entries.
	 *
	 * Normally updatePaths() (called from stage_5) has already cleared every
	 * "[hash]" before stage_7 runs, but if stage_7() or import_configuration()
	 * is invoked without updatePaths() running first (e.g. a custom migration
	 * script reusing install internals), the previous implementation left a
	 * literal "[hash]" substring in every derived directory key. See #5631.
	 *
	 * @return null
	 */
	private function resolveSitePathPlaceholders()
	{
		foreach($this->e107->e107_dirs as $key => $path)
		{
			if(is_string($path) && strpos($path, '[hash]') !== false)
			{
				$this->e107->e107_dirs[$key] = str_replace('[hash]', $this->e107->site_path, $path);
			}
		}

		$this->e107->e107_dirs['SYSTEM_DIRECTORY'] = str_replace("/".$this->e107->site_path,"",$this->e107->e107_dirs['SYSTEM_DIRECTORY']);
		$this->e107->e107_dirs['MEDIA_DIRECTORY']  = str_replace("/".$this->e107->site_path,"",$this->e107->e107_dirs['MEDIA_DIRECTORY']);

		return null;
	}

	private function stage_7()
	{
		global $e_forms;
		$tp = e107::getParser();

		$this->resolveSitePathPlaceholders();

		$this->stage = 7;
		installLog::add('Stage 7 started');

		// required for various core routines
		if(!defined('USERNAME'))
		{
			define('USERNAME', $this->previous_steps['admin']['user']);
			define('USEREMAIL', $this->previous_steps['admin']['email']);
		}

		if(varset($_POST['sitename']))
		{
			$this->previous_steps['prefs']['sitename'] = $tp->filter($_POST['sitename']);
		}

		if(varset($_POST['sitetheme']))
		{
			$this->previous_steps['prefs']['sitetheme'] = $tp->filter($_POST['sitetheme']);
		}

		if(varset($_POST['generate_content']))
		{
			$this->previous_steps['generate_content'] = $tp->filter($_POST['generate_content'],'int');
		}

		if(varset($_POST['install_plugins']))
		{
			$this->previous_steps['install_plugins'] = $tp->filter($_POST['install_plugins'],'int');
		}

		// Validate
		if(!vartrue($this->previous_steps['prefs']['sitename']))
		{
			$this->required['sitename'] = LANINS_113; // 'Please enter a website name.'; // should be used to highlight the required field. (using css for example)
		}
		if(!vartrue($this->previous_steps['prefs']['sitetheme']))
		{
			 $this->required['sitetheme'] = LANINS_114; // 'Please select a theme.';
		}

		if(!empty($this->required['sitetheme']) || !empty($this->required['sitename']))
		{
			return $this->stage_6();
		}

		// The finished e107_config.php is written at the end of stage 8, after the
		// database is provisioned. Until then e107_config.php stays in its pending
		// lock state, so a half-finished install never looks installed.

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_058);
		$this->template->SetTag("stage_title", LANINS_055);
		$this->template->SetTag("percent", 80);
		$this->template->SetTag("bartype", 'warning');

		$e_forms->start_form("confirmation", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] === "debug" ? "?debug" : ""));
		$page = '<div class="alert alert-success">'.nl2br(LANINS_057).'</div>';
		$this->finish_form();
		$this->add_button("submit", LAN_CONTINUE);



		$this->template->SetTag("stage_content", $page.$e_forms->return_form());
		installLog::add('Stage 7 completed');

	    return null;
	}

	/**
	 *	Stage 8 - actually create database and set up the site
	 *
	 *	@return null
	 */
	private function stage_8()
	{
		global $e_forms;

		// A recovery resume (cookie or pasted blob) never carries the admin login
		// password, since it is kept out of the recoverable copies. Re-collect it
		// before it is used to create the administrator account.
		if(!empty($this->previous_steps['admin']['user']) && !vartrue($this->previous_steps['admin']['password']))
		{
			$this->required['password'] = LANINS_026;
			return $this->stage_5();
		}

		//$system_dir = str_replace("/".$this->e107->site_path,"",$this->e107->e107_dirs['SYSTEM_DIRECTORY']);
		//$media_dir = str_replace("/".$this->e107->site_path,"",$this->e107->e107_dirs['MEDIA_DIRECTORY']);

		// required for various core routines
		if(!defined('USERNAME'))
		{
			define('USERNAME', $this->previous_steps['admin']['user']);
			define('USEREMAIL', $this->previous_steps['admin']['email']);
		}

		$this->setDb();

		define('THEME', e_THEME.$this->previous_steps['prefs']['sitetheme'].'/');
		define('THEME_ABS', e_THEME_ABS.$this->previous_steps['prefs']['sitetheme'].'/');
		define('USERCLASS_LIST', '253,247,254,250,251,0');

		$this->stage = 8;
		installLog::add('Stage 8 started');

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_120);
		$this->template->SetTag("stage_title", LANINS_071);
		$this->template->SetTag("percent", 100);
		$this->template->SetTag("bartype", 'success');
	
		$htaccessError = $this->htaccess();
		$this->saveFileTypes();

		$e_forms->start_form("confirmation", "index.php");

			$errors = $this->create_tables();
			if (!empty($errors))
			{
				installLog::add('Errors creating tables: '.$errors);
				$page = $errors."<br />";
				$alertType = 'error';
			}
			else
			{
				$alertType = 'success';
				installLog::add('Tables created successfully');
				$this->import_configuration();

				// Write the finished config last: its database credentials are the
				// "installed" marker the top-of-file guard keys on. The site hash
				// is recomputed server-side, never read from the client-supplied
				// wizard state.
				$sitePath = $this->e107->makeSiteHash($this->previous_steps['mysql']['db'], $this->previous_steps['mysql']['prefix']);
				$this->write_config($this->buildConfigFile($this->previous_steps, $sitePath));

				$page = nl2br(LANINS_125)."<br />";
				$page .= (is_writable('e107_config.php')) ? "<br />".str_replace("e107_config.php","<b>e107_config.php</b>",LANINS_126) : "";
				
				if($htaccessError)
				{
					$page .= "<br />".$htaccessError;
				}	
				$this->add_button('submit', LAN_CONTINUE);
			}

		$this->stats();
		$this->finish_form();

		$this->template->SetTag("stage_content", "<div class='alert alert-block alert-{$alertType}'>".$page."</div>".$e_forms->return_form());
		installLog::add('Stage 8 completed');

		e107::getMessage()->reset(false, false, true);

		return null;
	}

	private function saveFileTypes()
	{
		$data = '<?xml version="1.0" encoding="utf-8"?>
<e107Filetypes>
	<class name="member" type="zip,gz,jpg,jpeg,png,gif,webp,xml,pdf" maxupload="2M" />
	<class name="admin" type="zip,gz,jpg,jpeg,png,gif,webp,xml,pdf" maxupload="10M" />
	<class name="main" type="zip,gz,rar,jpg,jpeg,png,gif,webp,xml,pdf,ppt,pptx,mov,mp4,mp3,doc,docx,xls,xlsm,mp3,mp4,wav,ogg,webm,mid,midi,torrent,txt,dmg,msi" maxupload="50M" />
</e107Filetypes>';

		return file_put_contents($this->e107->e107_dirs['SYSTEM_DIRECTORY']."filetypes.xml",$data);

	}



	protected function stats()
	{
		global $e_forms;

		$data = array('name'=>$this->previous_steps['prefs']['sitename'], 'theme'=>$this->previous_steps['prefs']['sitetheme'], 'language'=>$this->previous_steps['language'], 'url'=>$_SERVER['SCRIPT_URL'],'version'=> defset('e_VERSION'), 'php'=>defset('PHP_VERSION'));
		$base = base64_encode(http_build_query($data, '','&'));
		$url = "https://e107.org/e-install/".$base;
		$e_forms->add_plain_html("<img src='".$url."' style='width:1px; height:1px' />");

	}



	/**
	 *	htaccess - handle the .htaccess file
	 *
	 *	@return string $error
	 */	
	protected function htaccess()
	{
		$error = "";
			
		if(!file_exists(".htaccess"))
		{
			if(!rename("e107.htaccess",".htaccess"))
			{
				$error = LANINS_142;
			}
			elseif($_SERVER['QUERY_STRING'] === "debug")
			{
				rename(".htaccess","e107.htaccess");
				$error = "DEBUG: Rename from e107.htaccess to .htaccess was successful";		
			}
		}
		elseif(file_exists("e107.htaccess"))
		{
			$srch = array('[b]','[/b]');
			$repl = array('<b>','</b>');
			$error = str_replace($srch,$repl, LANINS_144); // too early to use e107::getParser() so use str_replace();
		}		
		return $error;	
	}
					
	/**
	 * Run the full install pipeline non-interactively for create_tables_unattended().
	 *
	 * Mirrors the install-time side effects of stage_8() (constants, htaccess
	 * rename, filetypes.xml write, table creation, configuration import) but
	 * without the form-rendering plumbing.
	 *
	 * @return array{errors: ?string, htaccess: string}
	 */
	public function runUnattendedInstall()
	{
		if(!defined('USERNAME'))
		{
			define('USERNAME', $this->previous_steps['admin']['user']);
			define('USEREMAIL', $this->previous_steps['admin']['email']);
		}

		$this->setDb();

		if(!defined('THEME'))
		{
			define('THEME', e_THEME.$this->previous_steps['prefs']['sitetheme'].'/');
			define('THEME_ABS', e_THEME_ABS.$this->previous_steps['prefs']['sitetheme'].'/');
		}
		if(!defined('USERCLASS_LIST'))
		{
			define('USERCLASS_LIST', '253,247,254,250,251,0');
		}

		$htaccessError = $this->htaccess();
		$this->saveFileTypes();

		installLog::add('Unattended install started');
		$errors = $this->create_tables();
		if(!empty($errors))
		{
			return array('errors' => $errors, 'htaccess' => $htaccessError);
		}

		installLog::add('Tables created successfully');
		$this->import_configuration();
		installLog::add('Unattended install completed');

		return array('errors' => null, 'htaccess' => $htaccessError);
	}

	/**
	 * Import and generate preferences and default content.
	 *
	 * @return boolean
	 */
	public function import_configuration()
	{
		installLog::add('Starting configuration import');

		// PRE-CONFIG start - create and register blank config instances - do not load!
		$config_aliases = array(
			'core',
			'core_backup',
			'emote', 
			'menu',
			'search',
			'notify',
		);
		foreach ($config_aliases as $alias) 
		{
			e107::getConfig($alias, false)->clearPrefCache();
		}
		// PRE-CONFIG end		

		// Basic stuff to get the handlers/classes to work.


		// $udirs = "admin/|plugins/|temp";
		// $e_SELF = $_SERVER['PHP_SELF'];
		// $e_HTTP = preg_replace("#".$udirs."#i", "", substr($e_SELF, 0, strrpos($e_SELF, "/"))."/");

		// define("MAGIC_QUOTES_GPC", (ini_get('magic_quotes_gpc') ? true : false));
		// define('CHARSET', 'utf-8');
		// define("e_LANGUAGE", $this->previous_steps['language']);
		// define('e_SELF', 'http://'.$_SERVER['HTTP_HOST']) . ($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_FILENAME']);

		$themeImportFile = array();
		$themeImportFile[0] = $this->e107->e107_dirs['THEMES_DIRECTORY'].$this->previous_steps['prefs']['sitetheme']."/install.xml";
		$themeImportFile[1] = $this->e107->e107_dirs['THEMES_DIRECTORY'].$this->previous_steps['prefs']['sitetheme']."/install/install.xml";
		// $themeImportFile[3] = $this->e107->e107_dirs['CORE_DIRECTORY']. "xml/default_install.xml";

		$XMLImportfile = false;

		if(vartrue($this->previous_steps['generate_content']))
		{
			foreach($themeImportFile as $file)
			{
				if(is_readable($file))
				{
					$XMLImportfile = $file;
					break;
				}
			}
		}

		$tp = e107::getParser();

		define('PREVIEWTHEMENAME',""); // Notice Removal.

//		include_lan($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$this->previous_steps['language']."/lan_prefs.php");
		include_lan($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$this->previous_steps['language']."/admin/lan_theme.php");


		$coreConfig = $this->e107->e107_dirs['CORE_DIRECTORY']. "xml/default_install.xml";		
		$ret = e107::getXml()->e107Import($coreConfig, 'replace', true, false); // Add core pref values
		installLog::add('Attempting to Write Core Prefs.');
		installLog::add(print_r($ret, true));

		/*
		if($XMLImportfile) // We cannot rely on themes to include all prefs..so use 'replace'. 
		{
			$ret2 = e107::getXml()->e107Import($XMLImportfile, 'replace', true, false); // Overwrite specific core pref and tables entries. 
			installLog::add('Attempting to write Theme Prefs/Tables (install.xml)');
			installLog::add(print_r($ret2, true));
		}
		
	*/	//Create default plugin-table entries.
		// e107::getConfig('core')->clearPrefCache();
		installLog::add('Updating Plugin Tables');
		e107::getPlug();
	//	e107::getPlugin()->update_plugins_table('update');
		installLog::add('Plugins table updated');

		// Install Theme-required plugins
		if(vartrue($this->previous_steps['install_plugins']) && ($themeInfo = $this->get_theme_xml($this->previous_steps['prefs']['sitetheme'])) && isset($themeInfo['plugins']['plugin']))
		{
			$themeName = $this->previous_steps['prefs']['sitetheme'];
			foreach($themeInfo['plugins']['plugin'] as $k=>$plug)
			{
				$this->install_plugin($plug['@attributes']['name']);
				installLog::add('Theme-related ('.$themeName.') plugin installed: '.$plug['@attributes']['name']);
			}
		}

		installLog::add('Updating addon Prefs');

		e107::getSingleton('e107plugin')->save_addon_prefs('update'); // save plugin addon pref-lists. eg. e_latest_list.
		installLog::add('Addon prefs saved');

		// do this AFTER any required plugins are installated. 
		if($XMLImportfile) // We cannot rely on themes to include all prefs..so use 'replace'.
		{
			$ret2 = e107::getXml()->e107Import($XMLImportfile, 'replace', true, false); // Overwrite specific core pref and tables entries.
			installLog::add('Attempting to write Theme Prefs/Tables (install.xml)');
			installLog::add(print_r($ret2, true));
		}


		$tm = e107::getSingleton('themeHandler');
		$tm->noLog = true; // false to enable log
		$tm->setTheme($this->previous_steps['prefs']['sitetheme'], false);

		// Admin log fix - don't allow logs to be called inside pref handler
		// FIX
		e107::getConfig()->setParam('nologs', true); // change to false to enable log
		$pref = e107::getConfig()->getPref();

		// Set Preferences defined during install - overwriting those that may exist in the XML.

		$this->previous_steps['prefs']['sitelanguage'] 		= $this->previous_steps['language'];
		$this->previous_steps['prefs']['sitelang_init']		= $this->previous_steps['language'];

		$this->previous_steps['prefs']['siteadmin'] 		= $this->previous_steps['admin']['display'];
		$this->previous_steps['prefs']['siteadminemail'] 	= $this->previous_steps['admin']['email'];
		$this->previous_steps['prefs']['install_date']  	= time();
		$this->previous_steps['prefs']['siteurl']			= e_HTTP;

		$this->previous_steps['prefs']['sitetag']			= "e107 Website System";
		$this->previous_steps['prefs']['sitedisclaimer']	= '';

		$this->previous_steps['prefs']['replyto_name']		= $this->previous_steps['admin']['display'];
		$this->previous_steps['prefs']['replyto_email']		= $this->previous_steps['admin']['email'];

		// Cookie name fix, ended up with 406 error when non-latin words used
		$cookiename 										= preg_replace('/[^a-z0-9]/i', '', trim($this->previous_steps['prefs']['sitename']));
		$this->previous_steps['prefs']['cookie_name']		= ($cookiename ? substr($cookiename, 0, 4).'_' : 'e_').'cookie';
		
		### URL related prefs
		// set all prefs so that they are available, required for adminReadModules() - it checks which plugins are installed
		e107::getConfig()->setPref($this->previous_steps['prefs']);
		
		$url_modules = eRouter::adminReadModules();
		$url_locations = eRouter::adminBuildLocations($url_modules);
		$url_config = eRouter::adminBuildConfig(array(), $url_modules);

		$this->previous_steps['prefs']['url_aliases']		= array();
		$this->previous_steps['prefs']['url_config']	= $url_config;
		$this->previous_steps['prefs']['url_modules']		= $url_modules;
		$this->previous_steps['prefs']['url_locations']		= $url_locations;


		eRouter::clearCache();
		installLog::add('Core URL config set to default state');

		$us = e107::getUserSession();

		if($us->passwordAPIExists() === true)
		{
			$this->previous_steps['prefs']['passwordEncoding'] = PASSWORD_E107_PHP;
			$pwdEncoding = PASSWORD_E107_PHP;
		}
		else
		{
			$pwdEncoding = PASSWORD_E107_MD5; // default already in default_install.xml
		}

		// Set prefs, save
		e107::getConfig()->setPref($this->previous_steps['prefs']);
		e107::getConfig()->save(FALSE,TRUE, FALSE); // save preferences made during install.
		installLog::add('Core prefs set to install choices');

		// Create the admin user - replacing any that may be been included in the XML.

		$hash = $us->HashPassword($this->previous_steps['admin']['password'],$this->previous_steps['admin']['user'], $pwdEncoding);

		$ip = $_SERVER['REMOTE_ADDR'];
		$userp = "1, '{$this->previous_steps['admin']['display']}', '{$this->previous_steps['admin']['user']}', '', '".$hash."', '', '{$this->previous_steps['admin']['email']}', '', '', 0, ".time().", 0, 0, 0, 0, 0, '{$ip}', 0, '', 0, 1, '', '', '0', '', ".time().", ''";
	//	$qry = "REPLACE INTO {$this->previous_steps['mysql']['prefix']}user VALUES ({$userp})";
		$this->dbqry("REPLACE INTO {$this->previous_steps['mysql']['prefix']}user VALUES ({$userp})" );
		installLog::add('Admin user created');

		// Add Default user-extended values;
		$extendedQuery = "REPLACE INTO `{$this->previous_steps['mysql']['prefix']}user_extended` (`user_extended_id` ,	`user_hidden_fields`) VALUES ('1', NULL 	);";
		$this->dbqry($extendedQuery);

		// Create FULLTEXT indexes derived from e_search configurations
		$this->createSearchIndexes();

		e107::getDb()->close();
	//	mysql_close($this->dbLink);
		
		e107::getMessage()->reset(false, false, true);

		unset($tp, $pref);
		return false;
	}

	/**
	 * Create FULLTEXT indexes derived from e_search addon configurations.
	 *
	 * @return void
	 */
	protected function createSearchIndexes()
	{
		installLog::add('Creating FULLTEXT indexes from e_search configurations');

		// Clear config cache and reload from database to ensure e_search_list is available
		e107::getConfig('core')->clearPrefCache()->load(null, true);

		require_once(e_HANDLER . 'db_verify_class.php');

		$dbv = new db_verify();
		$dbv->compareAll();
		$dbv->compileResults();

		// Filter fixList to only include index fixes (FULLTEXT indexes)
		$fixList = $dbv->fixList;
		$indexFixes = array();
		foreach($fixList as $file => $tables)
		{
			foreach($tables as $table => $fields)
			{
				foreach($fields as $field => $modes)
				{
					if(in_array('index', $modes))
					{
						$indexFixes[$file][$table][$field] = $modes;
					}
				}
			}
		}

		if(!empty($indexFixes))
		{
			$dbv->runFix($indexFixes);
			installLog::add('FULLTEXT indexes created successfully');
		}
		else
		{
			installLog::add('No FULLTEXT indexes needed');
		}

		e107::getMessage()->reset(false, false, true);
	}

	/**
	 * Install a Theme required plugin.
	 *
	 * @param string $plugpath - plugin folder name
	 * @return null
	 */
	public function install_plugin($plugpath)
	{
		$plugin_handler = e107::getPlugin();
		$plugin_handler->XmlTables('uninstall', ['plugin_path' => $plugpath], ['delete_tables' => true]);
		$plugin_handler->install($plugpath);

		e107::getMessage()->reset(false, false, true);
		
		return null;
	}

	/**
	 * Check a DB name or table prefix - anything starting with a numeric followed by 'e' causes problems.
	 * Return TRUE if acceptable, FALSE if unacceptable
	 * Empty string returns the value of $blank_ok (caller should set TRUE for prefix, FALSE for DB name)
	 *
	 * @param string $str
	 * @param boolean $blank_ok [optional]
	 * @return boolean
	 */
	function check_name($str, $blank_ok = FALSE)
	{
		if ($str == '')
		{
			return $blank_ok;
		}
		if (preg_match("#^\d+[e|E]#", $str))
		{
			return false;
		}
		return TRUE;
	}

	/**
	 * checkDbFields - Check an array of db-related fields for illegal characters
	 *
	 * @param array $fields
	 * @return boolean TRUE for OK, FALSE for invalid character
	 */
	function checkDbFields($fields)
	{
		if (!is_array($fields))
		{
			return false;
		}
		foreach (array('server', 'user', 'db', 'prefix') as $key)
		{
			if (isset($fields[$key]) && strtr($fields[$key], "';", '    ') != $fields[$key])
			{
				return FALSE;		// Invalid character found
			}
		}
		return TRUE;
	}

	function get_lan_file()
	{
		if(!empty($_POST['language']))
		{
			$this->previous_steps['language'] = $_POST['language'];
		}		
		
		if(!isset($this->previous_steps['language']))
		{
			$this->previous_steps['language'] = "English";
		}

		include_lan($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$this->previous_steps['language']."/lan_installer.php");
		// $this->lan_file = "{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}{$this->previous_steps['language']}/lan_installer.php";
		// if(is_readable($this->lan_file))
		// {
		//		include($this->lan_file);
		// }
		// elseif(is_readable("{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}English/lan_installer.php"))
		// {
		//		include("{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}English/lan_installer.php");
		// }
		// else
		// {
		//		$this->raise_error("Fatal: Could not get valid language file for installation.");
		// }
	}

	/**
	 * get_languages - check language folder for language names
	 *
	 * @return array $lanlist
	 */
	function get_languages()
	{
		$handle = opendir($this->e107->e107_dirs['LANGUAGES_DIRECTORY']);
		$lanlist = array();		
		while ($file = readdir($handle))
		{
			if ($file !== "." && $file !== ".." && $file !== "/" && $file !== "CVS" && $file !== 'index.html')
			{
				if(file_exists("./{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}{$file}/lan_installer.php"))
				{
					$lanlist[] = $file;
				}
			}
		}
		closedir($handle);
		return $lanlist;
	}

	/**
	 * get_themes - check theme folder for theme names
	 *
	 * @return array $themelist
	 */
	function get_themes()
	{
		return ['bootstrap5', 'voux'];

		$handle = opendir($this->e107->e107_dirs['THEMES_DIRECTORY']);
		$themelist = array();
		while ($file = readdir($handle))
		{
			if (is_dir($this->e107->e107_dirs['THEMES_DIRECTORY'].$file) && $file !== '_blank')
			{
				if(is_readable("./{$this->e107->e107_dirs['THEMES_DIRECTORY']}{$file}/theme.xml"))
				{
					$themelist[] = $file;
				}
			}
		}
		closedir($handle);
		return $themelist;
	}

	/**
	 * get_theme_xml - check theme.xml file of specific theme
	 *
	 * @param string $theme_folder
	 * @return array|bool $xmlArray OR boolean FALSE if result is no array
	 */	
	function get_theme_xml($theme_folder)
	{
		if(!defined("SITEURL"))
		{
			define("SITEURL","");
		}
		$path = $this->e107->e107_dirs['THEMES_DIRECTORY'].$theme_folder."/theme.xml";

		if(!is_readable($path))
		{
			return FALSE;
		}

	//	require_once($this->e107->e107_dirs['HANDLERS_DIRECTORY']."theme_handler.php");
	//	$tm = new themeHandler;
		$xmlArray = e107::getTheme($theme_folder, $this->debug)->get();

		return (is_array($xmlArray)) ? $xmlArray : false;
	}

	/**
	 * finish_form - pass data along forms
	 *
	 * @param bool $force_stage [optional]
	 * @return null
	 */	
	function finish_form($force_stage = false)
	{
		global $e_forms;
		if($this->previous_steps)
		{
			if($this->token !== null)
			{
				// Hand the full state back signed; the server trusts only what it
				// can verify with the lock token next request.
				$e_forms->add_hidden_data("previous_steps", install_state_sign($this->previous_steps, $this->token));

				// Recovery copies (cookie + on-disk lock) omit the login password
				// and are refreshed only while the config is still pending, so the
				// finished config written at stage 8 is never clobbered.
				$recovery = install_state_sign($this->strippedSteps(), $this->token);
				install_set_state_cookie($recovery);

				if($this->stage <= 6)
				{
					$this->write_config($this->buildPendingConfig($this->token, $recovery));
				}
			}
			else
			{
				// Stage 1 only: no lock yet and no state worth signing.
				$e_forms->add_hidden_data("previous_steps", install_state_encode($this->previous_steps));
			}
		}
		$e_forms->add_hidden_data("stage", ($force_stage ? $force_stage : ($this->stage + 1)));

		return null;
	}

	/**
	 * check_writable_perms - check writable permissions
	 *
	 * @param string $list [default 'must_write']
	 * @return array $bad_files
	 */	
	function check_writable_perms($list = 'must_write')
	{
		$bad_files = array();
		
		$system_dirs = $this->e107->e107_dirs;
		$system_dirs['MEDIA_DIRECTORY'] = str_replace("[hash]/","", $system_dirs['MEDIA_DIRECTORY']);
		$system_dirs['SYSTEM_DIRECTORY'] = str_replace("[hash]/","", $system_dirs['SYSTEM_DIRECTORY']);
		
		$data['must_write'] = 'e107_config.php|{$MEDIA_DIRECTORY}|{$SYSTEM_DIRECTORY}'; // all-sub folders are created on-the-fly
		
		$data['can_write'] = '{$PLUGINS_DIRECTORY}|{$THEMES_DIRECTORY}|{$WEB_DIRECTORY}cache|{$WEB_DIRECTORY}lib';
		if (!isset($data[$list]))
		{
			return $bad_files;
		}

		$find = array();
		$replace = array();

		foreach ($system_dirs as $dir_name => $value)
		{
			$find[] = "{\${$dir_name}}";
			$replace[] = "./$value";
		}
		$data[$list] = str_replace($find, $replace, $data[$list]);
		$files = explode("|", trim($data[$list]));
		foreach ($files as $file)
		{
			if(!is_writable($file))
			{
				$bad_files[] = str_replace("./", "", $file);
			}
		}
		return $bad_files;
	}

	/**
	 * Create Core MySQL tables
	 *
	 * @return string|FALSE error code or FALSE if no errors are detected
	 */
	public function create_tables()
	{
	//	$link = mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']);



		$sql = e107::getDb();

		installLog::add("Starting Table Creation");

		$link = $sql->connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']);

		if(!$link)
		{
			return nl2br(LANINS_084."\n\n<b>".LANINS_083."\n</b><i>".$sql->getLastErrorText()."</i>");
		}

		installLog::add("DB Connection made");

	//	$dbLink = $link;		// Needed for mysql_close() to work round bug in PHP 5.3
	//	$db_selected = mysql_select_db($this->previous_steps['mysql']['db'], $link);
		$db_selected = $sql->database($this->previous_steps['mysql']['db'],$this->previous_steps['mysql']['prefix']);
		if(!$db_selected)
		{
			return nl2br(LANINS_085." '{$this->previous_steps['mysql']['db']}'\n\n<b>".LANINS_083."\n</b><i>".e107::getDb()->getLastErrorText()."</i>");
		}

		installLog::add("DB Database Selected");

		$filename = "{$this->e107->e107_dirs['CORE_DIRECTORY']}sql/core_sql.php";
		$fd = fopen ($filename, "r");
		$sql_data = fread($fd, filesize($filename));
		$sql_data = preg_replace("#\/\*.*?\*\/#mis", '', $sql_data);		// Strip comments
		fclose ($fd);

		if (!$sql_data)
		{
			return nl2br(LANINS_060)."<br /><br />";
		}

		preg_match_all("/create(.*?)(?:myisam|innodb);/si", $sql_data, $result );

		// Force UTF-8 again
		$this->dbqry('SET NAMES `utf8mb4`');

		$srch = array("CREATE TABLE","(");
		$repl = array("DROP TABLE IF EXISTS","");

		foreach ($result[0] as $sql_table)
		{
			$sql_table = preg_replace("/create table\s/si", "CREATE TABLE {$this->previous_steps['mysql']['prefix']}", $sql_table);

			// Drop existing tables before creating.
			$tmp = explode("\n",$sql_table);
			$drop_table = str_replace($srch,$repl,$tmp[0]);
			$this->dbqry($drop_table);

			if (!$this->dbqry($sql_table))
			{
				installLog::add("Query Failed in ".$filename." : ".$sql_table, 'error');
				return nl2br(LANINS_061."\n\n<b>".LANINS_083."\n</b><i>".e107::getDb()->getLastErrorText()."</i>");
			}
		}

		return FALSE;

	}

	function write_config($data)
	{
		$e107_config = 'e107_config.php';
		$fp = @fopen($e107_config, 'w');
		if (!@fwrite($fp, $data))
		{
			@fclose ($fp);
			return nl2br(LANINS_070);
		}
		@fclose ($fp);
		@chmod($e107_config,0644); // correct permissions. 
		return false;
	}




	private function setDb()
	{
		$sqlInfo = array(
				'mySQLserver'       => $this->previous_steps['mysql']['server'],
				'mySQLuser'         => $this->previous_steps['mysql']['user'],
				'mySQLpassword'     => $this->previous_steps['mysql']['password'],
				'mySQLdefaultdb'    => $this->previous_steps['mysql']['db'],
				'mySQLprefix'       => $this->previous_steps['mysql']['prefix']
		);

		$this->e107->initInstallSql($sqlInfo);
	}


	private function dbqry($qry)
	{
		$sql = e107::getDb();
		$return =  $sql->db_Query($qry);

		if($return === false)
		{
			installLog::add('Query Failed: '.$qry, 'error');
		}

		return $return;

		/*if($error = $sql->getLastErrorNumber())
		{
			$errorInfo = 'Query Error [#'.$error.']: '.$sql->getLastErrorText()."\nQuery: {$qry}";
			$this->debug_db_info['db_error_log'][] = $errorInfo;
			return false;
		}

		return true;*/
	}
}

class e_forms 
{
	public  $form;
	private $opened;

	function start_form($id, $action, $method = "post" )
	{
		$this->form = "\n<form method='{$method}' id='{$id}' action='{$action}'>\n";
		$this->opened = true;
	}

	function add_select_item($id, $labels, $selected)
	{
		$this->form .= "
		<select class='form-control input-large' name='{$id}' id='{$id}'>\n";
		foreach ($labels as $label)
		{
			$this->form .= "<option value='".$label."' ".($label == $selected ? " selected='selected'" : "").">{$label}</option>\n";
		}
		$this->form .= "</select>\n";
	}
	


	function add_hidden_data($id, $data)
	{
		$this->form .= "<input type='hidden' name='{$id}' value='{$data}' />\n";
	}

	function add_plain_html($html_data)
	{
		$this->form .= $html_data;
	}

	function return_form()
	{
		if($this->opened == true)
		{
			$this->form .= "</form>\n";
		}
		$this->opened = false;
		return $this->form;
	}
}

function create_tables_unattended()
{
	//If username or password not specified, exit
	if(!isset($_GET['username']) || !isset($_GET['password']))
	{
		return false;
	}

	$mySQLserver = null;
	$mySQLuser = null;
	$mySQLpassword = null;
	$mySQLdefaultdb = null;
	$mySQLprefix = null;

	if(file_exists('e107_config.php'))
	{
		$config = @include('e107_config.php');
	} else {
		return false;
	}

	if(is_array($config) && !empty($config['database'])) // New e107_config.php format. v2.4+
	{
		$dbInfo = $config['database'];
		$mySQLserver    = $dbInfo['server']   ?? null;
		$mySQLuser      = $dbInfo['user']     ?? null;
		$mySQLpassword  = $dbInfo['password'] ?? null;
		$mySQLdefaultdb = $dbInfo['db']       ?? null;
		$mySQLprefix    = $dbInfo['prefix']   ?? null;
	}

	//If mysql info not set, config file is not created properly
	if(!isset($mySQLuser) || !isset($mySQLpassword) || !isset($mySQLdefaultdb) || !isset($mySQLprefix))
	{
		return false;
	}

	// Refuse to re-provision a database that already holds an e107 install (a
	// replayed unattended run, or a pre-existing/upgraded site). This precedes
	// and returns identically to the credential check below, so it is not a
	// credential oracle. The probe uses a dedicated db instance so its table
	// list never pollutes the connection runUnattendedInstall() reuses.
	$probe = e107::getDb('install_provision_check');
	if($probe->connect($mySQLserver, $mySQLuser, $mySQLpassword)
		&& $probe->database($mySQLdefaultdb, $mySQLprefix)
		&& !empty($probe->tables()))
	{
		return false;
	}

	// If specified username and password does not match the ones in config, exit
	if(!hash_equals((string) $mySQLuser, (string) $_GET['username'])
		|| !hash_equals((string) $mySQLpassword, (string) $_GET['password']))
	{
		return false;
	}

	$einstall = new e_install();
	$einstall->previous_steps['mysql']['server'] 	= $mySQLserver;
	$einstall->previous_steps['mysql']['user']		= $mySQLuser;
	$einstall->previous_steps['mysql']['password'] 	= $mySQLpassword;
	$einstall->previous_steps['mysql']['db'] 		= $mySQLdefaultdb;
	$einstall->previous_steps['mysql']['prefix'] 	= $mySQLprefix;

	$einstall->previous_steps['language'] 			= (isset($_GET['language']) ? $_GET['language'] : 'English');

	$einstall->previous_steps['admin']['display']  	= (isset($_GET['admin_display']) ? $_GET['admin_display'] : 'admin');
	$einstall->previous_steps['admin']['user']  	= (isset($_GET['admin_user']) ? $_GET['admin_user'] : 'admin');
	$einstall->previous_steps['admin']['password']  = (isset($_GET['admin_password']) ? $_GET['admin_password'] : 'admin_password');
	$einstall->previous_steps['admin']['email']  	= (isset($_GET['admin_email']) ? $_GET['admin_email'] : 'admin_email@xxx.com');

	$einstall->previous_steps['generate_content'] 	= isset($_GET['gen']) ? (int) $_GET['gen'] : 1;
	$einstall->previous_steps['install_plugins'] 	= isset($_GET['plugins']) ? (int) $_GET['plugins'] : 1;
	$einstall->previous_steps['prefs']['sitename'] 	= isset($_GET['sitename']) ? urldecode($_GET['sitename']) : LANINS_113;
	$einstall->previous_steps['prefs']['sitetheme'] = isset($_GET['theme']) ? urldecode($_GET['theme']) : DEFAULT_INSTALL_THEME;

	$result = $einstall->runUnattendedInstall();
	if(!empty($result['errors']))
	{
		installLog::add('Unattended install failed: '.$result['errors'], 'error');
		return false;
	}

	return true;
}

class SimpleTemplate
{
	private $Tags = array();
	private $open_tag = "{";
	private $close_tag = "}";

	function __construct()
	{
		define("TEMPLATE_TYPE_FILE", 0);
		define("TEMPLATE_TYPE_DATA", 1);
	}

	function SetTag($TagName, $Data)
	{
		$this->Tags[$TagName] = array(	'Tag'  => $TagName,
		'Data' => $Data
		);
	}
/*
	function RemoveTag($TagName)
	{
		unset($this->Tags[$TagName]);
	}

	function ClearTags()
	{
		$this->Tags = array();
	}*/

	function ParseTemplate($Template, $template_type = TEMPLATE_TYPE_FILE)
	{
		if($template_type == TEMPLATE_TYPE_DATA)
		{
			$TemplateData = $Template;
		}
		else
		{
			$TemplateData = file_get_contents($Template);
		}
		foreach ($this->Tags as $Tag)
		{
			$TemplateData = str_replace($this->open_tag.$Tag['Tag'].$this->close_tag, $Tag['Data'], $TemplateData);
		}

		return $TemplateData;
	}
}

function template_data()
{

	return '<!DOCTYPE html>
	<html lang="en">
	  <head>
		<meta charset="utf-8">
		<title>{installation_heading} :: {stage_pre}{stage_num} - {stage_title}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="'.e_THEME.'bootstrap3/css/bootstrap-dark.min.css" rel="stylesheet">
		<link href="'.e_THEME.'bootstrap3/admin_style.css" rel="stylesheet">
		<link rel="icon" href="favicon.ico" type="image/x-icon" />
		<style>
		
		body 					{  padding-top: 40px; padding-bottom: 40px; background-color: #181818; }
		.container-narrow 		{ margin: 0 auto; max-width: 800px; }
		.container-narrow > hr 	{ margin: 30px 0; }
		.nav 					{ margin-top:35px; }
		.buttons-bar 			{ margin: 20px 30px 10px 0px }
		.tooltip-inner 			{ font-size:110%; }
		div.masthead 			{ margin-bottom:30px }
		h4						{ margin-left:10px; margin-bottom:20px; color:#181818; }
		#version				{ position:relative; left:50px; top:-20px; }
		.well					{ border-radius: 12px }

		.theme-cell             { margin-bottom:15px; padding-left:0; padding-right:5px }
		.theme-cell .thumbnail  { margin-bottom:5px; height:170px; width:auto }
		.theme-cell h5          { padding-left:8px; margin-top:0; font-weight:bold }

		label.theme-selection > input { visibility: hidden;  position: absolute; 	}
		label.theme-selection > input + div{  cursor:pointer;  border:2px solid transparent; border-radius:6px }
		label.theme-selection > input:checked + div {    border:2px solid #337ab7; 	}
		label.theme-selection > input + div span { visibility: hidden; float:right; margin-right:10px; color:#337ab7	}
		label.theme-selection > input:checked + div span { visibility: initial;	}
		div.tooltip { width:320px }



		</style>
		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		  <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.js"></script>
		<![endif]-->
	  </head>
	  <body>

		<div class="container-narrow">

		  <div class="masthead">
			<ul class="nav nav-pills pull-right" >
			  <li style="width:200px;text-align:center" ><a href="#" >'.LANINS_134.' &#58  {stage_pre} {stage_num} '.LANINS_135.' 8</a>
			  <div class="progress progress-{bartype}">
				<div class="progress-bar bar" style="width: {percent}%"></div>
			</div>
			</li>
			 </ul>
			<h3 class="muted">
			<img src="'.e_IMAGE.'admin_images/credits_logo.png" alt="e107" />
			</h3>
			
		  </div>

			<div class="panel panel-default">
					  <div class="panel-heading">
					    <h3 class="panel-title">{stage_title}</h3>
					  </div>
					  <div class="panel-body">
						{stage_content}
					  </div>
					</div>



		  <div class="footer">
			<p class="pull-left">&copy; e107 Inc. '.date("Y").'</p>
			<p class="pull-right">'.LAN_VERSION.' &#58 '.e_VERSION.'</p>
		  </div>
		 <div>{debug_info}</div>
		</div> <!-- /container -->

		<!-- The javascript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<script src="https://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

		<script>
		
		$(document).ready(function()
		{
		
			$(".e-tip,input,textarea,select,label").each(function(c) {
						
				var t = $(this).nextAll(".field-help");
				t.hide();
		
				$(this).tooltip({
					title: function() {
						var tip = t.html();
						return tip;
					},
					fade: true,
					html: true,
					placement: "right",
					delay: { show: 200, hide: 200 }
				});
			});

			// disable validation for back-button.
			$(".no-validate").click(function () {
				$("form").attr( "novalidate",true );
			});
		
		});
		
		</script>
		
	  </body>
	</html>
	';

}

/**
* Render a Fatal error and halt installation.
*
* @param $error
*/
function die_fatal_error($error)
{

	define("e_IMAGE","e107_images/");
	define("e_JS","e107_web/js/");
	define("e_THEME", "e107_themes/");
	define("e_LANGUAGEDIR", "e107_languages/");
	
	include_lan(e_LANGUAGEDIR."English/English.php");
	include_lan(e_LANGUAGEDIR."English/lan_installer.php");
	
	$var = array();
	$var["installation_heading"] 	= LANINS_001;
	$var["stage_pre"] 				= LANINS_002;
	$var["stage_num"] 				=  LANINS_003;
	$var["stage_title"] 			= LAN_ERROR;
	$var["percent"] 				= 10;
	$var["bartype"] 				= 'danger';
	$var['stage_content']			= "<div class='alert alert-error alert-block'>".$error."</div>";
	$var['debug_info'] 				= '';
	
	$template = template_data();
	
	foreach($var as $k=>$val)
	{
		$template = str_replace("{".$k."}", $val, $template);	
		
	}
	echo $template;
	exit;		
}
	
