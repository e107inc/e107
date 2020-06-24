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
define('MIN_PHP_VERSION',   '5.6');
define('MIN_MYSQL_VERSION', '4.1.2');
define('MAKE_INSTALL_LOG', true);

// ensure CHARSET is UTF-8 if used
//define('CHARSET', 'utf-8');

/* Default Options and Paths for Installer */
$MySQLprefix	     = 'e107_';
$HANDLERS_DIRECTORY  = "e107_handlers/"; // needed for e107 class init

header('Content-type: text/html; charset=utf-8');

define("e107_INIT", TRUE);
define("DEFAULT_INSTALL_THEME", 'voux');

$e107info = array();
require_once("e107_admin/ver.php");

define("e_VERSION", $e107info['e107_version']);

$e_ROOT = realpath(dirname(__FILE__)."/");
if ((substr($e_ROOT,-1) !== '/') && (substr($e_ROOT,-1) !== '\\') )
{
	$e_ROOT .= DIRECTORY_SEPARATOR;  // Should function correctly on both windows and Linux now.
}
define('e_ROOT', $e_ROOT);
unset($e_ROOT);


class installLog
{

	const logFile = "e107InstallLog.log";


	static function exceptionHandler(Exception $exception)
	{
		$message = $exception->getMessage();
		self::add($message, "error");
	}

	static function errorHandler($errno, $errstr, $errfile, $errline)
	{

		$error = "Error on line $errline in file ".$errfile." : ".$errstr;

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
		        self::add($error, "warn");

		}

	}


	static function clear()
	{
		if(!is_writable(dirname(__FILE__)) || !MAKE_INSTALL_LOG)
		{
			return null;
		}

		$logFile = dirname(__FILE__).'/'.self::logFile;
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
		if(!is_writable(dirname(__FILE__)) || !MAKE_INSTALL_LOG)
		{
			return null;
		}

		$logFile = dirname(__FILE__).'/'.self::logFile; // e107InstallLog.log';

		$now    = time();
		$message = $now.', '.gmstrftime('%y-%m-%d %H:%M:%S',$now)."\t".$type."\t".$message."\n";

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

if($_SERVER['QUERY_STRING'] != "debug")
{
	error_reporting(0); // suppress all errors unless debugging.
}
else
{
	error_reporting(E_ALL);
}

if($_SERVER['QUERY_STRING'] == 'clear')
{
	unset($_SESSION);
}

//error_reporting(E_ALL);

function e107_ini_set($var, $value)
{
	if (function_exists('ini_set'))
	{
		ini_set($var, $value);
	}
}

// setup some php options
e107_ini_set('magic_quotes_runtime',     0);
e107_ini_set('magic_quotes_sybase',      0);
e107_ini_set('arg_separator.output',     '&amp;');
e107_ini_set('session.use_only_cookies', 1);
e107_ini_set('session.use_trans_sid',    0);

if (function_exists('date_default_timezone_set'))
{
	date_default_timezone_set('UTC');
}

define('MAGIC_QUOTES_GPC', (ini_get('magic_quotes_gpc') ? true : false));

$php_version = phpversion();
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
if($inc_path[0] != ".")
{
	array_unshift($inc_path, ".");
	$inc_path = implode(PATH_SEPARATOR, $inc_path);
	e107_ini_set("include_path", $inc_path);
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
		if(trim($function) == "realpath")
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

$override = array();

if(isset($_POST['previous_steps']))
{
	$tmp = unserialize(base64_decode($_POST['previous_steps']));

	// Save unfiltered admin password (#4004) - " are transformed into &#34;
	$tmpadminpass1 = $tmp['admin']['password']; 
	
	$tmp = filter_var_array($tmp, FILTER_SANITIZE_STRING); 

	// Restore unfiltered admin password
	$tmp['admin']['password'] = $tmpadminpass1;

	$override = (isset($tmp['paths']['hash'])) ? array('site_path'=>$tmp['paths']['hash']) : array();
	unset($tmp);
	unset($tmpadminpass1);
}

//$e107_paths = compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'CACHE_DIRECTORY', 'DOWNLOADS_DIRECTORY', 'UPLOADS_DIRECTORY', 'MEDIA_DIRECTORY', 'LOGS_DIRECTORY', 'SYSTEM_DIRECTORY', 'CORE_DIRECTORY');
$e107_paths = array();
$e107 = e107::getInstance();
$ebase = realpath(dirname(__FILE__));
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
	unset($force);
	return include($path);
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
 * @param integer $expire seconds
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
	var $paths;
	var $template;
	var $debug_info;
	var $debug_db_info;
	var $e107;
	var $previous_steps;
	var $stage;
	var $post_data;
	var $required = array();
	var	$dbLink = NULL;		// DB link - needed for PHP5.3 bug
	var $session = null;
	protected $pdo = false;
	protected $debug = false;

	//	public function __construct()
	function __construct()
	{
		// notice removal, required from various core routines
		define('USERID', 1);
		define('USER', true);
		define('ADMIN', true);
	//	define('e_UC_MAINADMIN', 250);
		define('E107_DEBUG_LEVEL',0);

		if($_SERVER['QUERY_STRING'] == "debug")
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
		if(isset($_POST['previous_steps']))
		{
			$this->previous_steps = unserialize(base64_decode($_POST['previous_steps']));

			// Save unfiltered admin password (#4004) - " are transformed into &#34;
			$tmpadminpass2 = $this->previous_steps['admin']['password']; 
			
			$this->previous_steps = $tp->filter($this->previous_steps);

			// Restore unfiltered admin password
			$this->previous_steps['admin']['password'] = $tmpadminpass2;

			unset($_POST['previous_steps']);
			unset($tmpadminpass2);
		}
		else
		{
			$this->previous_steps = array();
		}
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
		if($id != 'start')
		{
			//		$this->form .= "<a class='btn btn-large ' href='javascript:history.go(-1)'>&laquo; ".LAN_BACK."</a>&nbsp;";
			$prevStage = ($this->stage - 1);
			$e_forms->form .= "<button class='btn btn-default btn-secondary btn-large no-validate ' name='back' value='".$prevStage."' type='submit'>&laquo; ".LAN_BACK."</button>&nbsp;";
		}
		if($id != 'back')
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
		$_POST['stage'] = intval($_POST['stage']);

		if(!empty($_POST['back']))
		{
			$_POST['stage'] = intval($_POST['back']);
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

		if($_SERVER['QUERY_STRING'] == "debug")
		{
			$this->template->SetTag("debug_info", print_a($this->previous_steps,TRUE));
		}
		else
		{
			$this->template->SetTag("debug_info", (!empty($this->debug_info) ? print_a($this->debug_info,TRUE)."Backtrace:<br />".print_a($this,TRUE) : ""));
		}

		echo $this->template->ParseTemplate(template_data(), TEMPLATE_TYPE_DATA);
	}

	function raise_error($details)
	{
		$this->debug_info[] = array (
		'info' => array (
			'details' => $details,
			'backtrace' => debug_backtrace()
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
	//	installLog::add('Stage 1 started');


		
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_003);
		$this->template->SetTag("stage_title", LANINS_004);
		$this->template->SetTag("percent", 10);
		$this->template->SetTag("bartype", 'warning');
		
		$e_forms->start_form("language_select", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
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
		$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$isrequired = (($_SERVER['SERVER_ADDR'] == "127.0.0.1") || ($_SERVER['SERVER_ADDR'] == "localhost") || ($_SERVER['SERVER_ADDR'] == "::1") || preg_match('^192\.168\.\d{1,3}\.\d{1,3}$',$_SERVER['SERVER_ADDR'])) ? "" :  "required='required'"; // Deals with IP V6, and 192.168.x.x address ranges, could be improved to validate x.x to a valid IP but for this use, I dont think its required to be that picky.

		$output = "
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			<table class='table table-striped table-bordered' >
				<tr>
					<td style='border-top: 1px solid #999;'><label for='server'>".LANINS_024."</label></td>
					<td style='border-top: 1px solid #999;'>
						<input class='form-control input-large' type='text' id='server' name='server' autofocus size='40' value='".varset($this->previous_steps['mysql']['server'],'localhost')."' maxlength='100' required='required' />
						<span class='field-help'>".LANINS_030."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='name'>".LANINS_025."</label></td>
					<td>
						<input class='form-control input-large' type='text' name='name' id='name' value='".varset($this->previous_steps['mysql']['user'])."' size='40'  maxlength='100' required='required' />
						<span class='field-help'>".LANINS_031."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='password'>".LANINS_026."</label></td>
					<td>
						<input class='form-control input-large' type='password' name='password' size='40' id='password' value='".varset($this->previous_steps['mysql']['password'])."' maxlength='100' {$isrequired}  pattern='[^\x22]+' />
						<span class='field-help'>".LANINS_032."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='db'>".LANINS_027."</label></td>
					<td class='form-inline'>
						<input class='form-control input-large' type='text' name='db' size='20' id='db' value='".varset($this->previous_steps['mysql']['db'])."' maxlength='100' required='required' pattern='^[a-zA-Z][a-zA-Z0-9_-]*' />
						<label class='checkbox-inline'><input type='checkbox' name='createdb' value='1' ".($this->previous_steps['mysql']['createdb'] ==1 ? "checked='checked'" : "")." /><small>".LANINS_028."</small></label>
						<span class='field-help'>".LANINS_033."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='prefix'>".LANINS_029."</label></td>
					<td>
						<input class='form-control input-large' type='text' name='prefix' size='20' id='prefix' value='e107_'  pattern='[a-z0-9]*_$' maxlength='100' required='required' />
						<span class='field-help'>".LANINS_034."</span>
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
			$this->previous_steps['mysql']['createdb']  = (isset($_POST['createdb']) && $_POST['createdb'] == true ? true : false);
			$this->previous_steps['mysql']['prefix']    = trim($tp->filter($_POST['prefix']));

			$this->setDb();
		}
		
		if(!empty($_POST['overwritedb']))
		{
			$this->previous_steps['mysql']['overwritedb'] = 1;
		}
					
		$success = $this->check_name($this->previous_steps['mysql']['db'], FALSE) && $this->check_name($this->previous_steps['mysql']['prefix'], TRUE);
		
		if ($success)
		{
			$success = $this->checkDbFields($this->previous_steps['mysql']);		// Check for invalid characters
		}
		
		if(!$success || $this->previous_steps['mysql']['server'] == "" || $this->previous_steps['mysql']['user'] == "")
		{
			$this->stage = 3;
			$this->template->SetTag("stage_num", LANINS_021);
			$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
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
				$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
				$page_content = LANINS_041.nl2br("\n\n<b>".LANINS_083."\n</b><i>".$sql->getLastErrorText()."</i>");
				
				$alertType = 'error';
			}
			elseif(($this->previous_steps['mysql']['createdb'] == 1) && empty($this->previous_steps['mysql']['overwritedb']) && $sql->database($this->previous_steps['mysql']['db'], $this->previous_steps['mysql']['prefix']))
			{
				$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
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
				$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
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
				    $query = 'CREATE DATABASE `'.$this->previous_steps['mysql']['db'].'` CHARACTER SET `utf8` ';
					
				}
				else
				{
					$notification = "<br /><span class='glyphicon glyphicon-ok'></span>  ".LANINS_137;
				    $query = 'ALTER DATABASE `'.$this->previous_steps['mysql']['db'].'` CHARACTER SET `utf8` ';
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
                    $this->dbqry('SET NAMES `utf8`');

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
		$not_writable = $this->check_writable_perms('must_write');		// Some directories MUST be writable
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
				$perms_errors .= (substr($file, -1) == "/" ? LANINS_010a : LANINS_010)."<br /><b>{$file}</b><br />\n";
			}
			$perms_notes = LANINS_018;
		}
		elseif (count($opt_writable))
		{
			$perms_pass = true;
			foreach ($opt_writable as $file)
			{
				$perms_errors .= (substr($file, -1) == "/" ? LANINS_010a : LANINS_010)."<br /><b>{$file}</b><br />\n";
			}
			$perms_notes = LANINS_106;
		}
		elseif (filesize('e107_config.php') > 1)
		{	// Must start from an empty e107_config.php
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

		$php_version = phpversion();

		if(version_compare($php_version, MIN_PHP_VERSION, ">="))
		{
			$php_help = "<span class='glyphicon glyphicon-ok'></span> ".LANINS_017;
		}
		else
		{
			$php_help = "<span class='glyphicon glyphicon-remove'></span> ".LANINS_019;
		}



		$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));


		
		$permColor	= ($perms_pass == true) ? "text-success" : "text-danger";
		$PHPColor 	= ($version_fail == false) ? "text-success" : "text-danger";
		$mysqlColor	= ($mysql_pass == true) ? "text-success" : "text-danger";


		$extensionCheck = array(
			'pdo'   => array('label'=> "PDO (MySQL)",       'status' => extension_loaded('pdo_mysql'),          'url'=> 'https:/php.net/manual/en/book.pdo.php'),
			'xml'   => array('label'=> LANINS_050,          'status' => function_exists('utf8_encode') && class_exists('DOMDocument', false),  'url'=> 'http://php.net/manual/en/ref.xml.php'),
			'exif'  => array('label'=> LANINS_048,          'status' => function_exists('exif_imagetype'),      'url'=> 'http://php.net/manual/en/book.exif.php'),
			'curl'  => array('label'=> 'Curl Library',      'status' => function_exists('curl_version'),        'url'=> 'http://php.net/manual/en/book.curl.php'),
			'gd'    => array('label'=> 'GD Library',        'status' => function_exists('gd_info'),             'url'=> 'http://php.net/manual/en/book.image.php'),
			'mb'    => array('label'=> 'MB String Library', 'status' => function_exists('mb_strimwidth'),       'url'=> 'http://php.net/manual/en/book.mbstring.php'),
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
		elseif ($perms_pass && !$version_fail && ($extensionCheck['xml']['status'] == true))
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
	 * @return string HTML form of stage 5.
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

		$e_forms->start_form("admin_info", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$output = "
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			<table class='table table-striped table-bordered'>
				<colgroup>
					<col style='width:35%' />
					<col  />
				</colgroup>
				<tr>
					<td><label for='u_name'>".LANINS_072."</label></td>
					<td>
						<input class='form-control' type='text' autofocus name='u_name' id='u_name' placeholder='admin' size='30' required='required' value='".(isset($this->previous_steps['admin']['user']) ? $this->previous_steps['admin']['user'] : "")."' maxlength='60' />
						<span class='field-help'>".LANINS_073."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='d_name'>".LANINS_074."</label></td>
					<td>
						<input class='form-control' type='text' name='d_name' id='d_name' size='30' placeholder='Administrator'  value='".(isset($this->previous_steps['admin']['display']) ? $this->previous_steps['admin']['display'] : "")."' maxlength='60' />
						<span class='field-help'>".LANINS_123."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='pass1'>".LANINS_076."</label></td>
					<td>
						<input class='form-control' type='password' name='pass1' size='30' id='pass1' value='' maxlength='60' required='required' />
						<span class='field-help'>".LANINS_124."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='pass2'>".LANINS_078."</label></td>
					<td>
						<input class='form-control' type='password' name='pass2' size='30' id='pass2' value='' maxlength='60' required='required' />
						<span class='field-help'>".LANINS_079."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='email'>".LANINS_080."</label></td>
					<td>
						<input class='form-control' type='text' name='email' size='30' id='email' required='required' placeholder='admin@mysite.com' value='".(isset($this->previous_steps['admin']['email']) ? $this->previous_steps['admin']['email'] : '')."' maxlength='100' />
					<span class='field-help'>".LANINS_081."</span>
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

					if(empty($val['thumbnail']))
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

				$output .= $this->thumbnailSelector('admincss', $opts, 'css/bootstrap-dark.min.css');

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
	 * @return string HTML form of stage 6.
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

		$e_forms->start_form("pref_info", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
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

	private function stage_7()
	{
		global $e_forms;
		$tp = e107::getParser();

		$this->e107->e107_dirs['SYSTEM_DIRECTORY'] = str_replace("[hash]",$this->e107->site_path,$this->e107->e107_dirs['SYSTEM_DIRECTORY']);	
		$this->e107->e107_dirs['CACHE_DIRECTORY']  = str_replace("[hash]",$this->e107->site_path,$this->e107->e107_dirs['CACHE_DIRECTORY']);
		$this->e107->e107_dirs['SYSTEM_DIRECTORY'] = str_replace("/".$this->e107->site_path,"",$this->e107->e107_dirs['SYSTEM_DIRECTORY']);
		$this->e107->e107_dirs['MEDIA_DIRECTORY']  = str_replace("/".$this->e107->site_path,"",$this->e107->e107_dirs['MEDIA_DIRECTORY']);

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

		$config_file = "<?php
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

\$mySQLserver    = '{$this->previous_steps['mysql']['server']}';
\$mySQLuser      = '{$this->previous_steps['mysql']['user']}';
\$mySQLpassword  = '{$this->previous_steps['mysql']['password']}';
\$mySQLdefaultdb = '{$this->previous_steps['mysql']['db']}';
\$mySQLprefix    = '{$this->previous_steps['mysql']['prefix']}';
\$mySQLcharset   = 'utf8';

\$ADMIN_DIRECTORY     = '{$this->e107->e107_dirs['ADMIN_DIRECTORY']}';
\$FILES_DIRECTORY     = '{$this->e107->e107_dirs['FILES_DIRECTORY']}';
\$IMAGES_DIRECTORY    = '{$this->e107->e107_dirs['IMAGES_DIRECTORY']}';
\$THEMES_DIRECTORY    = '{$this->e107->e107_dirs['THEMES_DIRECTORY']}';
\$PLUGINS_DIRECTORY   = '{$this->e107->e107_dirs['PLUGINS_DIRECTORY']}';
\$HANDLERS_DIRECTORY  = '{$this->e107->e107_dirs['HANDLERS_DIRECTORY']}';
\$LANGUAGES_DIRECTORY = '{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}';
\$HELP_DIRECTORY      = '{$this->e107->e107_dirs['HELP_DIRECTORY']}';
\$MEDIA_DIRECTORY	  = '{$this->e107->e107_dirs['MEDIA_DIRECTORY']}';
\$SYSTEM_DIRECTORY    = '{$this->e107->e107_dirs['SYSTEM_DIRECTORY']}';


// -- Optional --
// define('e_DEBUG', true); // Enable debug mode to allow displaying of errors
// define('e_HTTP_STATIC', 'https://static.mydomain.com/');  // Use a static subdomain for js/css/images etc. 
// define('e_MOD_REWRITE_STATIC', true); // Rewrite static image urls. 
// define('e_LOG_CRITICAL', true); // log critical errors but do not display them to user. 
// define('e_GIT', 'path-to-git');  // Path to GIT for developers
// define('X-FRAME-SAMEORIGIN', false); // Option to override X-Frame-Options 
// define('e_PDO, true); // Enable PDO mode (used in PHP > 7 and when mysql_* methods are not available)

";
/*
if($this->pdo == true)
{
	$config_file .= 'define("e_PDO", true);';
	$config_file .= "\n\n";
}*/


		$config_result = $this->write_config($config_file);		

		if ($config_result)
		{
			// $page = $config_result."<br />";
			installLog::add('Error writing config file: '.$config_result);
		}
		else
		{
            installLog::add('Config file written successfully');
		}
		 

		// Data is okay - Continue.

		// $this->previous_steps['prefs']['sitename'] 		= $_POST['sitename'];
		// $this->previous_steps['prefs']['sitetheme'] 		= $_POST['sitetheme'];
		// $this->previous_steps['generate_content'] 		= $_POST['generate_content'];

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_058);
		$this->template->SetTag("stage_title", LANINS_055);
		$this->template->SetTag("percent", 80);
		$this->template->SetTag("bartype", 'warning');

		$e_forms->start_form("confirmation", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
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
				$page = nl2br(LANINS_125)."<br />";
				$page .= (is_writable('e107_config.php')) ? "<br />".str_replace("e107_config.php","<b>e107_config.php</b>",LANINS_126) : "";
				
				if($htaccessError)
				{
					$page .= "<br />".$htaccessError;
				}	
				$this->add_button('submit', LAN_CONTINUE);
			}
		 
		$this->finish_form();
		$this->stats();
		$this->template->SetTag("stage_content", "<div class='alert alert-block alert-{$alertType}'>".$page."</div>".$e_forms->return_form());
		installLog::add('Stage 8 completed');

		e107::getMessage()->reset(false, false, true);

		return null;
	}

	private function saveFileTypes()
	{
		$data = '<?xml version="1.0" encoding="utf-8"?>
<e107Filetypes>
	<class name="253" type="zip,gz,jpg,jpeg,png,gif,xml,pdf" maxupload="2M" />
</e107Filetypes>';

		return file_put_contents($this->e107->e107_dirs['SYSTEM_DIRECTORY']."filetypes.xml",$data);

	}



	protected function stats()
	{
		global $e_forms;

		$data = array('name'=>$this->previous_steps['prefs']['sitename'], 'theme'=>$this->previous_steps['prefs']['sitetheme'], 'language'=>$this->previous_steps['language'], 'url'=>$_SERVER['HTTP_REFERER']);;
		$base = base64_encode(http_build_query($data, null, '&'));
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
			elseif($_SERVER['QUERY_STRING'] == "debug")
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
		if(vartrue($this->previous_steps['install_plugins']))
		{
			if($themeInfo = $this->get_theme_xml($this->previous_steps['prefs']['sitetheme']))
			{
				if(isset($themeInfo['plugins']['plugin']))
				{
					foreach($themeInfo['plugins']['plugin'] as $k=>$plug)
					{
						$this->install_plugin($plug['@attributes']['name']);
						installLog::add('Theme-related plugin installed: '.$plug['@attributes']['name']);
					}
				}
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
		e107::getConfig('core')->setParam('nologs', true); // change to false to enable log
		$pref = e107::getConfig('core')->getPref();

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
		e107::getConfig('core')->setPref($this->previous_steps['prefs']); 
		
		$url_modules = eRouter::adminReadModules();
		$url_locations = eRouter::adminBuildLocations($url_modules);
		$url_config = eRouter::adminBuildConfig(array(), $url_modules);
		
		$this->previous_steps['prefs']['url_aliases']		= array();
		$this->previous_steps['prefs']['url_config']		= $url_config;
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
		e107::getConfig('core')->setPref($this->previous_steps['prefs']);
		e107::getConfig('core')->save(FALSE,TRUE, FALSE); // save preferences made during install.
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

		e107::getDb()->close();
	//	mysql_close($this->dbLink);
		
		e107::getMessage()->reset(false, false, true);

		unset($tp, $pref);
		return false;
	}

	/**
	 * Install a Theme required plugin.
	 *
	 * @param string $plugpath - plugin folder name
	 * @return null
	 */
	public function install_plugin($plugpath)
	{
		e107::getPlugin()->install_plugin($plugpath);
	//	e107::getPlugin()->install_plugin($row['plugin_id']);
		
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
			return $blank_ok;
		if (preg_match("#^\d+[e|E]#", $str))
			return FALSE;
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
		if (!is_array($fields)) return FALSE;
		foreach (array('server', 'user', 'db', 'prefix') as $key)
		{
			if (isset($fields[$key]))
			{
				if (strtr($fields[$key],"';", '    ') != $fields[$key])
				{
					return FALSE;		// Invalid character found
				}
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
			if ($file != "." && $file != ".." && $file != "/" && $file != "CVS" && $file != 'index.html') 
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
		$handle = opendir($this->e107->e107_dirs['THEMES_DIRECTORY']);
		$themelist = array();
		while ($file = readdir($handle))
		{
			if (is_dir($this->e107->e107_dirs['THEMES_DIRECTORY'].$file) && $file !='_blank')
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
			$e_forms->add_hidden_data("previous_steps", base64_encode(serialize($this->previous_steps)));
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
		if (!isset($data[$list])) return $bad_files;

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

		$this->dbLink = $link;		// Needed for mysql_close() to work round bug in PHP 5.3
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
		$this->dbqry('SET NAMES `utf8`');

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
			/*	if($this->debug)
				{
					echo "<h3>filename</h3>";
					var_dump($filename);

					echo "<h3>sql_table</h3>";
					var_dump($sql_table);
					echo "<h3>result[0]</h3>";
					var_dump($result[0]);
				}*/
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
	var $form;
	var $opened;

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
		@include('e107_config.php');
	} else {
		return false;
	}	
	
	//If mysql info not set, config file is not created properly
	if(!isset($mySQLuser) || !isset($mySQLpassword) || !isset($mySQLdefaultdb) || !isset($mySQLprefix))
	{
		return false;
	}

	// If specified username and password does not match the ones in config, exit
	if($_GET['username'] !== $mySQLuser || $_GET['password'] !== $mySQLpassword)
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

	$einstall->previous_steps['generate_content'] 	= isset($_GET['gen']) ? intval($_GET['gen']) : 1;
	$einstall->previous_steps['install_plugins'] 	= isset($_GET['plugins']) ? intval($_GET['plugins']) : 1;
	$einstall->previous_steps['prefs']['sitename'] 	= isset($_GET['sitename']) ? urldecode($_GET['sitename']) : LANINS_113;
	$einstall->previous_steps['prefs']['sitetheme'] = isset($_GET['theme']) ? urldecode($_GET['theme']) : 'bootstrap3';

	//@include_once("./{$HANDLERS_DIRECTORY}e107_class.php");
	//$e107_paths = compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'CACHE_DIRECTORY', 'DOWNLOADS_DIRECTORY', 'UPLOADS_DIRECTORY');
	//$e107 = e107::getInstance();
	//$e107->init($e107_paths, realpath(dirname(__FILE__)));

	//$einstall->e107 = &$e107;

	//FIXME - does not appear to work for import_configuration. ie. tables are blank except for user table.

	$einstall->create_tables();
	$einstall->import_configuration();
	return true;
}

class SimpleTemplate
{
	var $Tags = array();
	var $open_tag = "{";
	var $close_tag = "}";

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

	function RemoveTag($TagName)
	{
		unset($this->Tags[$TagName]);
	}

	function ClearTags()
	{
		$this->Tags = array();
	}

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

	$data = '<!DOCTYPE html>
	<html lang="en">
	  <head>
		<meta charset="utf-8">
		<title>{installation_heading} :: {stage_pre}{stage_num} - {stage_title}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="'.e_THEME.'bootstrap3/css/bootstrap-dark.min.css" rel="stylesheet">
		<link href="'.e_THEME.'bootstrap3/admin_style.css" rel="stylesheet">
		<link rel="icon" href="favicon.ico" type="image/x-icon" />
		<style type="text/css">
		
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
		  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
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
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<script src="https://netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" type="text/javascript"></script>

		<script type="text/javascript">
		
		$(document).ready(function()
		{
		
			$("input,textarea,select,label,.e-tip").each(function(c) {
						
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
	return $data;
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
	
	include(e_LANGUAGEDIR."English/English.php");
	include(e_LANGUAGEDIR."English/lan_installer.php");
	
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
	
