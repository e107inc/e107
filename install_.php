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
define('MIN_PHP_VERSION',   '5.0');
define('MIN_MYSQL_VERSION', '4.1.2');
define('MAKE_INSTALL_LOG', false);

// ensure CHARSET is UTF-8 if used
//define('CHARSET', 'utf-8');

/* Default Options and Paths for Installer */
$MySQLprefix	     = 'e107_';
$HANDLERS_DIRECTORY  = "e107_handlers/"; // needed for e107 class init

define("e107_INIT", TRUE);
require_once("e107_admin/ver.php");

define("e_VERSION", $e107info['e107_version']);

/*define("e_UC_PUBLIC", 0);
define("e_UC_MAINADMIN", 250);
define("e_UC_READONLY", 251);
define("e_UC_GUEST", 252);
define("e_UC_MEMBER", 253);
define("e_UC_ADMIN", 254);
define("e_UC_NOBODY", 255);*/

define("E107_INSTALL",TRUE);

if($_SERVER['QUERY_STRING'] != "debug")
{
	error_reporting(0); // suppress all errors unless debugging. 
}
else
{
	error_reporting(E_ALL);	
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

define('MAGIC_QUOTES_GPC', (ini_get('magic_quotes_gpc') ? true : false));

$php_version = phpversion();
if(version_compare($php_version, MIN_PHP_VERSION, "<"))
{
	die('A minimum version of PHP '.MIN_PHP_VERSION.' is required');
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

if(!function_exists("mysql_connect"))
{
	die("e107 requires PHP to be installed or compiled with the MySQL extension to work correctly, please see the MySQL manual for more information.");
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
	die("e107 requires the realpath() function to be enabled and your host appears to have disabled it. This function is required for some <b>important</b> security checks and there is <b>NO workaround</b>. Please contact your host for more information.");
}

//obsolete $installer_folder_name = 'e107_install';
include_once("./{$HANDLERS_DIRECTORY}core_functions.php");
include_once("./{$HANDLERS_DIRECTORY}e107_class.php");

function check_class($whatever)
{
	return TRUE;
}

$override = array();

if(isset($_POST['previous_steps']))
{
	$tmp = unserialize(base64_decode($_POST['previous_steps']));
	$override = (isset($tmp['paths']['hash'])) ? array('site_path'=>$tmp['paths']['hash']) : array();
	unset($tmp);
}

//$e107_paths = compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'CACHE_DIRECTORY', 'DOWNLOADS_DIRECTORY', 'UPLOADS_DIRECTORY', 'MEDIA_DIRECTORY', 'LOGS_DIRECTORY', 'SYSTEM_DIRECTORY', 'CORE_DIRECTORY');
$e107_paths = array();
$e107 = e107::getInstance();
$e107->initInstall($e107_paths, realpath(dirname(__FILE__)), $override);
unset($e107_paths,$override);

### NEW Register Autoload - do it asap
if(!function_exists('spl_autoload_register'))
{
	// PHP >= 5.1.2 required
	die('Fatal exception - spl_autoload_* required.');
}

// register core autoload
e107::autoload_register(array('e107', 'autoload'));

// NEW - session handler
require_once(e_HANDLER.'session_handler.php');
define('e_SECURITY_LEVEL', e_session::SECURITY_LEVEL_NONE);
define('e_COOKIE', 'e107install');
e107::getSession(); // starts session, creates default namespace
// session_start();

function include_lan($path, $force = false)
{
	return e107::includeLan($path, $force);
}
//obsolete $e107->e107_dirs['INSTALLER'] = "{$installer_folder_name}/";

if(isset($_GET['create_tables']))
{
	create_tables_unattended();
	exit;
}

header('Content-type: text/html; charset=utf-8');

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
function cookie($name, $value, $expire=0, $path = e_HTTP, $domain = '', $secure = 0)
{
	setcookie($name, $value, $expire, $path, $domain, $secure);
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
	var $required = ""; 		//TODO - use for highlighting required fields with css/js.
	var $logFile;			// Name of log file, empty string if logging disabled
	var	$dbLink = NULL;		// DB link - needed for PHP5.3 bug
	var $session = null;

	//	public function __construct()
	function e_install()
	{
		// notice removal, required from various core routines
		define('USERID', 1);
		define('USER', true);
		define('ADMIN', true);
		
		// session instance
		$this->session = e107::getSession();

		$this->logFile = '';
		if (MAKE_INSTALL_LOG)
		{
			if(is_writable(dirname(__FILE__)))
			{
				$this->logFile = dirname(__FILE__).'/e107InstallLog.log';
			}
		}
		// $this->logLine('Query string: ');
		$this->template = new SimpleTemplate();
		while (@ob_end_clean());
		global $e107;
		$this->e107 = $e107;
		if(isset($_POST['previous_steps']))
		{
			$this->previous_steps = unserialize(base64_decode($_POST['previous_steps']));
			unset($_POST['previous_steps']);
		}
		else
		{
			$this->previous_steps = array();
		}
		$this->get_lan_file();
		$this->post_data = $_POST;

		$this->template->SetTag('required', '');
		if(isset($this->previous_steps['language']))
		{
			define("e_LANGUAGE", $this->previous_steps['language']);
			include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_admin.php");
		}
	}

	/**
	 *	Write a line of text to the log file (if enabled) - prepend time/date, append \n
	 *	Can always call this routine - it will return if logging disabled
	 *
	 *	@param	string $logLine - text to log
	 *	@return none
	 */
	protected function logLine($logLine)
	{
		if (!MAKE_INSTALL_LOG || ($this->logFile == '')) return;
		$logfp = fopen($this->logFile, 'a+');
		fwrite($logfp, ($now = time()).', '.gmstrftime('%y-%m-%d %H:%M:%S',$now).'  '.$logLine."\n");
		fclose($logfp);
	}

	function renderPage()
	{
		if(!isset($_POST['stage']))
		{
			$_POST['stage'] = 1;
		}
		$_POST['stage'] = intval($_POST['stage']);

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
			$this->template->SetTag("debug_info", print_a($this,TRUE));
		}
		else
		{
			$this->template->SetTag("debug_info", (count($this->debug_info) ? print_a($this->debug_info,TRUE)."Backtrace:<br />".print_a($this,TRUE) : ""));
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
		if(!$this->required)
		{
			return;
		}
		$this->required = array_filter($this->required);
		if(vartrue($this->required))
		{
			$this->template->SetTag("required","<div class='message'>". implode("<br />",$this->required)."</div>");
			$this->required = array();
		}
	}

	private function stage_1()
	{
		global $e_forms;
		$this->stage = 1;
		$this->logLine('Stage 1 started');

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_003);
		$this->template->SetTag("stage_title", LANINS_004);
		$this->template->SetTag("percent", 10);
		$this->template->SetTag("bartype", 'warning');
		
		$e_forms->start_form("language_select", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$e_forms->add_select_item("language", $this->get_languages(), "English");
		$this->finish_form();
		$e_forms->add_button("start", LANINS_035);
		$output = "
			<div style='text-align: center;'>
				<div class='alert alert-info alert-block'>
					<label for='language'>".LANINS_005."</label>
				</div>\n
				<br /><br /><br />\n
				".$e_forms->return_form()."
			</div>";
		$this->template->SetTag("stage_content", $output);
		$this->logLine('Stage 1 completed');
	}

	private function stage_2()
	{
		global $e_forms;
		$this->stage = 2;
		$this->logLine('Stage 2 started');
		$this->previous_steps['language'] = $_POST['language'];

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_021);
		$this->template->SetTag("stage_title", LANINS_022);
		$this->template->SetTag("percent", 25);
		$this->template->SetTag("bartype", 'warning');
		
		// $this->template->SetTag("onload", "document.getElementById('name').focus()");
		// $page_info = nl2br(LANINS_023);
		$page_info = "<div class='alert alert-block alert-info'>Please fill in the form below with your MySQL details. If you do not know this information, please contact your hosting provider. You may hover over each field for additional information.</div>";
		$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$output = "
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			<table class='table table-striped' >
				<tr>
					<td style='border-top: 1px solid #999;'><label for='server'>".LANINS_024."</label></td>
					<td style='border-top: 1px solid #999;'>
						<input class='tbox' type='text' id='server' name='server' autofocus size='40' value='localhost' maxlength='100' required='required' />
						<span class='field-help'>".LANINS_030."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='name'>".LANINS_025."</label></td>
					<td>
						<input class='tbox' type='text' name='name' id='name' size='40' value='' maxlength='100' required='required' />
						<span class='field-help'>".LANINS_031."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='password'>".LANINS_026."</label></td>
					<td>
						<input class='tbox' type='password' name='password' size='40' id='password' value='' maxlength='100' required='required' />
						<span class='field-help'>".LANINS_032."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='db'>".LANINS_027."</label></td>
					<td>
						<input type='text' name='db' size='20' id='db' value='' maxlength='100' required='required' />
						<label class='checkbox inline'><input type='checkbox' name='createdb' value='1' />".LANINS_028."</label>
						<span class='field-help'>".LANINS_033."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='prefix'>".LANINS_029."</label></td>
					<td>
						<input type='text' name='prefix' size='20' id='prefix' value='e107_'  pattern='[a-z0-9]*_$' maxlength='100' required='required' />
						<span class='field-help'>".LANINS_034."</span>
					</td>
				</tr>
			</table>
			<br /><br />
			</div>
			\n";
			
		$e_forms->add_plain_html($output);
		$this->finish_form();
		$e_forms->add_button("submit", LANINS_035);
		$this->template->SetTag("stage_content", $page_info.$e_forms->return_form());
		$this->logLine('Stage 2 completed');
	}

	/**
	 *	Replace hash paths and create folders if needed.
	 *
	 *	@param	none
	 *	@return none
	 */	
	private function updatePaths()
	{
		$hash = e107::makeSiteHash($this->previous_steps['mysql']['db'],$this->previous_steps['mysql']['prefix']);
		$this->e107->site_path = $hash;	
		
		$this->previous_steps['paths']['hash'] = $hash;

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
	}

	private function stage_3()
	{
		global $e_forms;
		$success = TRUE;
		$this->stage = 3;
		$this->logLine('Stage 3 started');

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_036);
		$this->template->SetTag("onload", "document.getElementById('name').focus()");
		$this->template->SetTag("percent", 40);
		$this->template->SetTag("bartype", 'warning');

		$this->previous_steps['mysql']['server'] = trim($_POST['server']);
		$this->previous_steps['mysql']['user'] = trim($_POST['name']);
		$this->previous_steps['mysql']['password'] = $_POST['password'];
		$this->previous_steps['mysql']['db'] = trim($_POST['db']);
		$this->previous_steps['mysql']['createdb'] = (isset($_POST['createdb']) && $_POST['createdb'] == TRUE ? TRUE : FALSE);
		$this->previous_steps['mysql']['prefix'] = trim($_POST['prefix']);
		
		
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
			<table cellspacing='0'>
				<tr>
					<td style='border-top: 1px solid #999;'><label for='server'>".LANINS_024."</label></td>
					<td style='border-top: 1px solid #999;'><input class='tbox' type='text' id='server' name='server' size='40' value='{$this->previous_steps['mysql']['server']}' maxlength='100' /></td>
					<td style='width: 40%; border-top: 1px solid #999;'>".LANINS_030."</td>
				</tr>

				<tr>
					<td><label for='name'>".LANINS_025."</label></td>
					<td><input class='tbox' type='text' name='name' id='name' size='40' value='{$this->previous_steps['mysql']['user']}' maxlength='100' onload='this.focus()'  /></td>
					<td>".LANINS_031."</td>
				</tr>

				<tr>
					<td><label for='password'>".LANINS_026."</label></td>
					<td><input class='tbox' type='password' name='password' id='password' size='40' value='{$this->previous_steps['mysql']['password']}' maxlength='100' /></td>
					<td>".LANINS_032."</td>
				</tr>

				<tr>
					<td><label for='db'>".LANINS_027."</label></td>
					<td><input type='text' name='db' id='db' size='20' value='{$this->previous_steps['mysql']['db']}' maxlength='100' />
						<br /><label class='defaulttext'><input type='checkbox' name='createdb'".($this->previous_steps['mysql']['createdb'] == 1 ? " checked='checked'" : "")." value='1' />".LANINS_028."</label></td>
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
			$e_forms->add_button("submit", LANINS_035);
			$this->template->SetTag("stage_title", LANINS_040);
		}
		else
		{
			$this->template->SetTag("stage_title", LANINS_037.($this->previous_steps['mysql']['createdb'] == 1 ? LANINS_038 : ""));
			if (!$res = @mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']))
			{
				$success = FALSE;
				$page_content = LANINS_041.nl2br("\n\n<b>".LANINS_083."\n</b><i>".mysql_error()."</i>");
				
				$alertType = 'error';
			}
			else
			{
				$page_content = "<i class='icon-ok'></i> ".LANINS_042;
				// @TODO Check database version here?
/*
				$mysql_note = mysql_get_server_info();
				if (version_compare($mysql_note, MIN_MYSQL_VERSION, '>='))
				{
				    $success = FALSE;

				}
*/
				// Do brute force for now - Should be enough

				$DB_ALREADY_EXISTS = mysql_select_db($this->previous_steps['mysql']['db'], $res);

				//TODO Add option to continue install even if DB exists.

				if($this->previous_steps['mysql']['createdb'] == 1 || !$DB_ALREADY_EXISTS)
				{
				    $query = 'CREATE DATABASE '.$this->previous_steps['mysql']['db'].' CHARACTER SET `utf8` ';
				}
				elseif($DB_ALREADY_EXISTS)
				{
				    $query = 'ALTER DATABASE '.$this->previous_steps['mysql']['db'].' CHARACTER SET `utf8` ';
				}

				if (!$this->dbqry($query))
				{
					$success = FALSE;
					$page_content .= "<br /><br />".LANINS_043.nl2br("\n\n<b>".LANINS_083."\n</b><i>".mysql_error()."</i>");
				}
				else
				{
                    $this->dbqry('SET NAMES `utf8`');
					$page_content .= "<br /><i class='icon-ok'></i> ".LANINS_044;
				}
			}
			if($success)
			{
				$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
				// $page_content .= "<br /><br />".LANINS_045."<br /><br />";
				$e_forms->add_button("submit", LANINS_035);
				$alertType = 'success';
			}
			else 
			{
				
				$e_forms->add_button("back", LANINS_035);
			}
			$head = $page_content;
		}
		if ($success)
			$this->finish_form();
		else
			$this->finish_form(3);
		$this->template->SetTag("stage_content", "<div class='alert alert-block alert-{$alertType}'>".$head."</div>".$e_forms->return_form());
		$this->logLine('Stage 3 completed');
	}

	private function stage_4()
	{
		global $e_forms;

		$this->stage = 4;
		$this->logLine('Stage 4 started');

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
			$perms_notes = "<i class='icon-remove'></i> ".LANINS_122;
		}
		else
		{
			$perms_pass = true;
			$perms_errors = "&nbsp;";
			$perms_notes = "<i class='icon-ok'></i> ".LANINS_017;
		}

		if(!function_exists("mysql_connect"))
		{
			$version_fail = true;
			$mysql_note = LANINS_011;
			$mysql_help = LANINS_012;
		}
		elseif (!@mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']))
		{
			$mysql_note = LANINS_011;
			$mysql_help = LANINS_013;
		}
		else
		{
			$mysql_note = mysql_get_server_info();
			if (version_compare($mysql_note, MIN_MYSQL_VERSION, '>='))
			{
				$mysql_help = "<i class='icon-ok'></i> ".LANINS_017;
				$mysql_pass = true;
			}
			else
			{
				$mysql_help = "<i class='icon-remove'></i> ".LANINS_105;
			}
		}
		if(!function_exists('utf8_encode'))
		{
			$xml_installed = false;
		}
		else
		{
			$xml_installed = true;
		}

		$php_version = phpversion();
		if(version_compare($php_version, MIN_PHP_VERSION, ">="))
		{
			$php_help = "<i class='icon-ok'></i> ".LANINS_017;
		}
		else
		{
			$php_help = "<i class='icon-remove'></i> ".LANINS_019;
		}
		$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		if(!$perms_pass)
		{
			$e_forms->add_button("retest_perms", LANINS_009);
			$this->stage = 3; // make the installer jump back a step
		}
		elseif ($perms_pass && !$version_fail && $xml_installed)
		{
			$e_forms->add_button("continue_install", LANINS_020);
		}
		
		$permColor	= ($perms_pass == true) ? "text-success" : "text-error";
		$PHPColor 	= ($version_fail == false) ? "text-success" : "text-error";
		$xmlColor	= ($xml_installed == true) ? "text-success" : "text-error";
		$mysqlColor	= ($mysql_pass == true) ? "text-success" : "text-error";
		
		$output = "
			<table class='table table-striped' style='width: 100%; margin-left: auto; margin-right: auto;'>
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
					<td>".LANINS_016."</td>
					<td>{$mysql_note}</td>
					<td class='{$mysqlColor}'>{$mysql_help}</td>
				</tr>
				
				<tr>
					<td>".LANINS_050."</td>
					<td>".($xml_installed ? LANINS_051 : LANINS_052)."</td>
					<td class='{$xmlColor}'>".($xml_installed ? "<i class='icon-ok'></i> ".LANINS_017 : LANINS_053)."</td>
				</tr>
			</table>\n";
		$this->finish_form();
		$this->template->SetTag("stage_content", $output.$e_forms->return_form());
		$this->logLine('Stage 4 completed');
	}

	/**
	 * Install stage 5 - collect Admin Login Data.
	 *
	 * @return string HTML form of stage 5.
	 */
	private function stage_5()
	{
		global $e_forms;
		
		$this->updatePaths(); // update dynamic paths and create media and system directories - requires mysql info. 	
		
		$this->stage = 5;
		$this->logLine('Stage 5 started');

		$this->display_required();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_046);
		$this->template->SetTag("stage_title", LANINS_047);
		// $this->template->SetTag("onload", "document.getElementById('u_name').focus()");
		$this->template->SetTag("percent", 60);
		$this->template->SetTag("bartype", 'warning');

		$e_forms->start_form("admin_info", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$output = "
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			<table class='table table-striped'>
				<tr>
					<td><label for='u_name'>".LANINS_072."</label></td>
					<td>
						<input class='tbox' type='text' autofocus name='u_name' id='u_name' placeholder='admin' size='30' required='required' value='".(isset($this->previous_steps['admin']['user']) ? $this->previous_steps['admin']['user'] : "")."' maxlength='60' />
						<span class='field-help'>".LANINS_073."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='d_name'>".LANINS_074."</label></td>
					<td>
						<input class='tbox' type='text' name='d_name' id='d_name' size='30' placeholder='Administrator'  value='".(isset($this->previous_steps['admin']['display']) ? $this->previous_steps['admin']['display'] : "")."' maxlength='60' />
						<span class='field-help'>".LANINS_123."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='pass1'>".LANINS_076."</label></td>
					<td>
						<input type='password' name='pass1' size='30' id='pass1' value='' maxlength='60' required='required' />
						<span class='field-help'>".LANINS_124."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='pass2'>".LANINS_078."</label></td>
					<td>
						<input type='password' name='pass2' size='30' id='pass2' value='' maxlength='60' required='required' />
						<span class='field-help'>".LANINS_079."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='email'>".LANINS_080."</label></td>
					<td>
						<input type='text' name='email' size='30' id='email' required='required' placeholder='admin@mysite.com' value='".(isset($this->previous_steps['admin']['email']) ? $this->previous_steps['admin']['email'] : '')."' maxlength='100' />
					<span class='field-help'>".LANINS_081."</span>
					</td>
				</tr>
			</table>
			<br /><br />
			</div>
			\n";
		$e_forms->add_plain_html($output);
		$this->finish_form();
		$e_forms->add_button("submit", LANINS_035);
		$this->template->SetTag("stage_content", $e_forms->return_form());
		$this->logLine('Stage 5 completed');
	}

	/**
	 * Collect User's Website Preferences
	 *
	 * @return string HTML form of stage 6.
	 */
	private function stage_6()
	{
		global $e_forms;
		$this->stage = 6;
		$this->logLine('Stage 6 started');

		// -------------------- Save Step 5 Data -------------------------
		if(!vartrue($this->previous_steps['admin']['user']) || varset($_POST['u_name']))
		{
			$_POST['u_name'] = str_replace(array("'", '"'), "", $_POST['u_name']);
			$this->previous_steps['admin']['user'] = $_POST['u_name'];
		}

		if(!vartrue($this->previous_steps['admin']['display']) || varset($_POST['d_name']))
		{
			$_POST['d_name'] = str_replace(array("'", '"'), "", $_POST['d_name']);
			if ($_POST['d_name'] == "")
			{
				$this->previous_steps['admin']['display'] = $_POST['u_name'];
			}
			else
			{
				$this->previous_steps['admin']['display'] = $_POST['d_name'];
			}
		}

		if(!vartrue($this->previous_steps['admin']['email']) || varset($_POST['email']))
		{
			$this->previous_steps['admin']['email'] = $_POST['email'];
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

		// -------------   Validate Step 5 Data. --------------------------
		if(!vartrue($this->previous_steps['admin']['user']) || !vartrue($this->previous_steps['admin']['password']))
		{
			$this->required['u_name'] = LANINS_086; //
		}

		if(vartrue($this->required['u_name']) || vartrue($this->required['pass1']))
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
						<input class='tbox' type='text' autofocus placeholder=\"My Website\" required='required' name='sitename' id='sitename' size='30' value='".(vartrue($_POST['sitename']) ? $_POST['sitename'] : "")."' maxlength='60' />
					</td>
				</tr>

				<tr>
					<td><label>".LANINS_109."</label><br />".LANINS_110."</td>
					<td>
						<table class='table' >
							<thead>
								<tr>
									<th>".LANINS_115."</th>
									<th>".LANINS_116."</th>
								</tr>
							</thead>
							<tbody>";

				$themes = $this->get_themes();

				foreach($themes as $val)
				{
					$themeInfo 	= $this->get_theme_xml($val);
					$title 		= vartrue($themeInfo['@attributes']['name']);
					$category 	= vartrue($themeInfo['category']);

					$output .= "
								<tr>
									<td>
										<label class='radio'><input type='radio' name='sitetheme' value='{$val}' required='required' />{$title}</label>
									</td>
									<td>{$category}</td>
								</tr>";
				}

				$output .= "
							</tbody>
						</table>
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
		$e_forms->add_button("submit", LANINS_035);
		$this->template->SetTag("stage_content", $e_forms->return_form());
		$this->logLine('Stage 6 completed');
	}

	private function stage_7()
	{
		global $e_forms;
		
		$this->e107->e107_dirs['SYSTEM_DIRECTORY'] = str_replace("[hash]",$this->e107->site_path,$this->e107->e107_dirs['SYSTEM_DIRECTORY']);	
		$this->e107->e107_dirs['CACHE_DIRECTORY'] = str_replace("[hash]",$this->e107->site_path,$this->e107->e107_dirs['CACHE_DIRECTORY']);

		$this->stage = 7;
		$this->logLine('Stage 7 started');

		// required for various core routines
		if(!defined('USERNAME'))
		{
			define('USERNAME', $this->previous_steps['admin']['user']);
			define('USEREMAIL', $this->previous_steps['admin']['email']);
		}

		if(varset($_POST['sitename']))
		{
			$this->previous_steps['prefs']['sitename'] = $_POST['sitename'];
		}

		if(varset($_POST['sitetheme']))
		{
			$this->previous_steps['prefs']['sitetheme'] = $_POST['sitetheme'];
		}

		if(varset($_POST['generate_content']))
		{
			$this->previous_steps['generate_content'] = $_POST['generate_content'];
		}

		if(varset($_POST['install_plugins']))
		{
			$this->previous_steps['install_plugins'] = $_POST['install_plugins'];
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

		if(vartrue($this->required['sitetheme']) || vartrue($this->required['sitename']))
		{
			return $this->stage_6();
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
		$e_forms->add_button("submit", LANINS_035);

		$this->template->SetTag("stage_content", $page.$e_forms->return_form());
		$this->logLine('Stage 7 completed');
	}

	/**
	 *	Stage 8 - actually create database and set up the site
	 *
	 *	@return none
	 */
	private function stage_8()
	{
		global $e_forms;
	
		$system_dir = str_replace("/".$this->e107->site_path,"",$this->e107->e107_dirs['SYSTEM_DIRECTORY']);
		$media_dir = str_replace("/".$this->e107->site_path,"",$this->e107->e107_dirs['MEDIA_DIRECTORY']);

		// required for various core routines
		if(!defined('USERNAME'))
		{
			define('USERNAME', $this->previous_steps['admin']['user']);
			define('USEREMAIL', $this->previous_steps['admin']['email']);
		}

		$this->stage = 8;
		$this->logLine('Stage 8 started');

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_120);
		$this->template->SetTag("stage_title", LANINS_071);
		$this->template->SetTag("percent", 100);
		$this->template->SetTag("bartype", 'success');
	
		$htaccessError = $this->htaccess();

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
 * This file has been generated by the installation script.
 */

\$mySQLserver    = '{$this->previous_steps['mysql']['server']}';
\$mySQLuser      = '{$this->previous_steps['mysql']['user']}';
\$mySQLpassword  = '{$this->previous_steps['mysql']['password']}';
\$mySQLdefaultdb = '{$this->previous_steps['mysql']['db']}';
\$mySQLprefix    = '{$this->previous_steps['mysql']['prefix']}';

\$ADMIN_DIRECTORY     = '{$this->e107->e107_dirs['ADMIN_DIRECTORY']}';
\$FILES_DIRECTORY     = '{$this->e107->e107_dirs['FILES_DIRECTORY']}';
\$IMAGES_DIRECTORY    = '{$this->e107->e107_dirs['IMAGES_DIRECTORY']}';
\$THEMES_DIRECTORY    = '{$this->e107->e107_dirs['THEMES_DIRECTORY']}';
\$PLUGINS_DIRECTORY   = '{$this->e107->e107_dirs['PLUGINS_DIRECTORY']}';
\$HANDLERS_DIRECTORY  = '{$this->e107->e107_dirs['HANDLERS_DIRECTORY']}';
\$LANGUAGES_DIRECTORY = '{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}';
\$HELP_DIRECTORY      = '{$this->e107->e107_dirs['HELP_DIRECTORY']}';
\$MEDIA_DIRECTORY	 = '{$media_dir}';
\$SYSTEM_DIRECTORY    = '{$system_dir}';

";

		$config_result = $this->write_config($config_file);
		$e_forms->start_form("confirmation", "index.php");
		if ($config_result)
		{
			$page = $config_result."<br />";
			$this->logLine('Error writing config file: '.$config_result);
			$alertType = 'warning';
		}
		else
		{
			$this->logLine('Config file written successfully');
			$errors = $this->create_tables();
			if ($errors == true)
			{
				$this->logLine('Errors creating tables: '.$errors);
				$page = $errors."<br />";
				$alertType = 'error';
			}
			else
			{
				$alertType = 'success';
				$this->logLine('Tables created successfully');
				$this->import_configuration();
				$page = nl2br(LANINS_069)."<br />";
				
				if($htaccessError)
				{
					
					$page .= "<p class='text-warning'>".$htaccessError."</p>";		
				}	
				$e_forms->add_button('submit', LANINS_035);
			}
		}
		$this->finish_form();
		$this->template->SetTag("stage_content", "<div class='alert alert-block alert-{$alertType}'>".$page."</div>".$e_forms->return_form());
		$this->logLine('Stage 8 completed');
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
				$error = "IMPORTANT: Please rename e107.htaccess to .htaccess";
			}
			elseif($_SERVER['QUERY_STRING'] == "debug")
			{
				rename(".htaccess","e107.htaccess");
				$error = "DEBUG: Rename from e107.htaccess to .htaccess was successful";		
			}
		}
		else
		{		
			$error = "IMPORTANT: Please copy and paste the contents of the <b>e107.htaccess</b> into your <b>.htaccess</b> file. Please take care NOT to overwrite any existing data that may be in it.";				
		}		
		return $error;	
	}
					
	/**
	 * Import and generate preferences and default content.
	 *
	 * @return boolean
	 */
	 //FIXME always return FALSE???
	public function import_configuration()
	{
		$this->logLine('Starting configuration import');

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

		include_lan($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$this->previous_steps['language']."/lan_prefs.php");
		include_lan($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$this->previous_steps['language']."/admin/lan_theme.php");

		// [SecretR] should work now - fixed log errors (argument noLogs = true) change to false to enable log
		
		$coreConfig = $this->e107->e107_dirs['CORE_DIRECTORY']. "xml/default_install.xml";		
		e107::getXml()->e107Import($coreConfig, 'add', true, false); // Add core pref values
		$this->logLine('Core prefs written');
		
		if($XMLImportfile) // We cannot rely on themes to include all prefs..so use 'replace'. 
		{
			e107::getXml()->e107Import($XMLImportfile, 'replace', true, false); // Overwrite specific core pref and tables entries. 
			$this->logLine('Theme Prefs/Tables (install.xml) written');
		}
		
		//Create default plugin-table entries.
		// e107::getConfig('core')->clearPrefCache();
		e107::getPlugin()->update_plugins_table('update');
		$this->logLine('Plugins table updated');

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
						$this->logLine('Theme-related plugin installed: '.$plug['@attributes']['name']);
					}
				}
			}
		}

		e107::getSingleton('e107plugin')->save_addon_prefs('update'); // save plugin addon pref-lists. eg. e_latest_list.
		$this->logLine('Addon prefs saved');

		$tm = e107::getSingleton('themeHandler');
		$tm->noLog = true; // false to enable log
		$tm->setTheme($this->previous_steps['prefs']['sitetheme']);

		// Admin log fix - don't allow logs to be called inside pref handler
		e107::getConfig('core')->setParam('nologs', false); // change to true to enable log
		$pref = e107::getConfig('core')->getPref();

		// Set Preferences defined during install - overwriting those that may exist in the XML.

		$this->previous_steps['prefs']['sitelanguage'] 		= $this->previous_steps['language'];
		$this->previous_steps['prefs']['sitelang_init']		= $this->previous_steps['language'];

		$this->previous_steps['prefs']['siteadmin'] 		= $this->previous_steps['admin']['display'];
		$this->previous_steps['prefs']['siteadminemail'] 	= $this->previous_steps['admin']['email'];
		$this->previous_steps['prefs']['install_date']  	= time();
		$this->previous_steps['prefs']['siteurl']			= e_HTTP;

		$this->previous_steps['prefs']['sitetag']			= LAN_PREF_2;
		$this->previous_steps['prefs']['sitedisclaimer']	= LAN_PREF_3;

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
		$this->logLine('Core URL config set to default state');

		// Set prefs, save
		e107::getConfig('core')->setPref($this->previous_steps['prefs']);
		e107::getConfig('core')->save(FALSE,TRUE); // save preferences made during install.
		$this->logLine('Core prefs set to install choices');

		// Create the admin user - replacing any that may be been included in the XML.
		$ip = $_SERVER['REMOTE_ADDR'];
		$userp = "1, '{$this->previous_steps['admin']['display']}', '{$this->previous_steps['admin']['user']}', '', '".md5($this->previous_steps['admin']['password'])."', '', '{$this->previous_steps['admin']['email']}', '', '', 0, ".time().", 0, 0, 0, 0, 0, '{$ip}', 0, '', 0, 1, '', '', '0', '', ".time().", ''";
		$qry = "REPLACE INTO {$this->previous_steps['mysql']['prefix']}user VALUES ({$userp})";
		$this->dbqry("REPLACE INTO {$this->previous_steps['mysql']['prefix']}user VALUES ({$userp})" );
		$this->logLine('Admin user created');
		mysql_close($this->dbLink);
		return false;
	}

	/**
	 * Install a Theme required plugin.
	 *
	 * @param string $plugpath - plugin folder name
	 * @return void
	 */
	public function install_plugin($plugpath) //FIXME - requires default plugin table entries, see above.
	{
		e107::getDb()->db_Select_gen("SELECT * FROM #plugin WHERE plugin_path = '".$plugpath."' LIMIT 1");
		$row = e107::getDb()->db_Fetch(MYSQL_ASSOC);
		e107::getSingleton('e107plugin')->install_plugin($row['plugin_id']);
		return;
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
	 * @param none
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
	 * @param none
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
	 * @return array $xmlArray OR boolean FALSE if result is no array
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

		require_once($this->e107->e107_dirs['HANDLERS_DIRECTORY']."theme_handler.php");

		$tm = new themeHandler;
		$xmlArray = $tm->parse_theme_xml($theme_folder);

		// $xml = e107::getXml();
		// $xmlArray = $xml->loadXMLfile($path,'advanced');
		return (is_array($xmlArray)) ? $xmlArray : FALSE;
	}

	/**
	 * finish_form - pass data along forms
	 *
	 * @param string $force_stage [optional]
	 * @return none
	 */	
	function finish_form($force_stage = false)
	{
		global $e_forms;
		if($this->previous_steps)
		{
			$e_forms->add_hidden_data("previous_steps", base64_encode(serialize($this->previous_steps)));
		}
		$e_forms->add_hidden_data("stage", ($force_stage ? $force_stage : ($this->stage + 1)));
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

		$e107_config = 'e107_config.php';
		if (!file_exists($e107_config)) 
		{	// fix to create empty config file before it is checked
			file_put_contents($e107_config, '');
		}
		
		$data['must_write'] = 'e107_config.php|{$MEDIA_DIRECTORY}|{$SYSTEM_DIRECTORY}'; // all-sub folders are created on-the-fly
		
		$data['can_write'] = '{$PLUGINS_DIRECTORY}|{$THEMES_DIRECTORY}';
		if (!isset($data[$list])) return $bad_files;
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
		$link = mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']);
		if(!$link)
		{
			return nl2br(LANINS_084."\n\n<b>".LANINS_083."\n</b><i>".mysql_error($link)."</i>");
		}

		$this->dbLink = $link;		// Needed for mysql_close() to work round bug in PHP 5.3
		$db_selected = mysql_select_db($this->previous_steps['mysql']['db'], $link);
		if(!$db_selected)
		{
			return nl2br(LANINS_085." '{$this->previous_steps['mysql']['db']}'\n\n<b>".LANINS_083."\n</b><i>".mysql_error($link)."</i>");
		}

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

			if (!$this->dbqry($sql_table, $link))
			{
				return nl2br(LANINS_061."\n\n<b>".LANINS_083."\n</b><i>".mysql_error($link)."</i>");
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
		return false;
	}

	function dbqry($qry)
	{
		mysql_query($qry);

		if(mysql_errno())
		{
			$errorInfo = 'Query Error [#'.mysql_errno().']: '.mysql_error()."\nQuery: {$qry}";
			echo $errorInfo."<br />";
			exit;
			$this->debug_db_info['db_error_log'][] = $errorInfo;
			//$this->debug_db_info['db_log'][] = $qry;
			return false;
		}
		//$this->debug_db_info['db_log'][] = $qry;
		return true;
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
		<select name='{$id}' id='{$id}'>\n";
		foreach ($labels as $label)
		{
			$this->form .= "<option".($label == $selected ? " selected='selected'" : "").">{$label}</option>\n";
		}
		$this->form .= "</select>\n";
	}

	function add_button($id, $title, $align = "right", $type = "submit")
	{
		$this->form .= "<div class='buttons-bar inline' style='text-align: {$align}; z-index: 10;'>";
		if($id != 'start')
		{
			$this->form .= "<a class='btn btn-large ' href='javascript:history.go(-1)'>&laquo; Back</a>&nbsp;";
		}
		if($id != 'back')
		{			
			$this->form .= "<input type='{$type}' id='{$id}' value='{$title} &raquo;' class='btn btn-large btn-primary' />";
		}
		$this->form .= "</div>\n";
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
	@include('e107_config.php');
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

	$einstall = new e_install;
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
	$einstall->previous_steps['prefs']['sitetheme'] = isset($_GET['theme']) ? urldecode($_GET['theme']) : 'jayya';

	@include_once("./{$HANDLERS_DIRECTORY}e107_class.php");
	$e107_paths = compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'CACHE_DIRECTORY', 'DOWNLOADS_DIRECTORY', 'UPLOADS_DIRECTORY');
	$e107 = e107::getInstance();
	$e107->init($e107_paths, realpath(dirname(__FILE__)));

	$einstall->e107 = &$e107;

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

	function SimpleTemplate()
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
		<link href="'.e_JS.'bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="'.e_THEME.'bootstrap/admin_style.css" rel="stylesheet">
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
			  <li class="active" style="width:200px;text-align:center" ><a href="#" >Installation: {stage_pre} {stage_num} of 8</a>
			  <div class="progress progress-{bartype}">
				<div class="bar" style="width: {percent}%"></div>
			</div>
			</li>
			 </ul>
			<h3 class="muted">
			<img src="'.e_IMAGE.'admin_images/credits_logo.png" alt="e107" />
			</h3>
			
		  </div>

		  <div class="well">
		  <h4>{stage_title}</h4>
			{stage_content}
		  </div>

		  <div class="footer">
			<p class="pull-left">&copy; e107 Inc. '.date("Y").'</p>
			<p class="pull-right">Version: '.e_VERSION.'</p> 
		  </div>
		 <div>{debug_info}</div>
		</div> <!-- /container -->

		<!-- The javascript
		================================================== -->
		<!-- Placed at the end of the document so the pages load faster -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script src="'.e_JS.'bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="'.e_JS.'core/admin.jquery.js"></script>
		<script type="text/javascript" src="'.e_JS.'core/all.jquery.js"></script>
	  </body>
	</html>
	';
	return $data;
}


