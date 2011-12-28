<?php
/**
 * e107 website system
 * 
 * Copyright (C) e107 Inc (e107.org)
 * 
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * $URL$
 * $Id$
 */

define('MAKE_INSTALL_LOG', true);  # Set this to 'true' if you want a log file to be created during the installation process

#
# Default Options and Paths for Installer
#

$e107_paths['MySQLPrefix']	        = 'e107_';
$e107_paths['ADMIN_DIRECTORY']     = 'e107_admin/';
$e107_paths['FILES_DIRECTORY']     = 'e107_files/';
$e107_paths['IMAGES_DIRECTORY']    = 'e107_images/';
$e107_paths['THEMES_DIRECTORY']    = 'e107_themes/';
$e107_paths['PLUGINS_DIRECTORY']   = 'e107_plugins/';
$e107_paths['HANDLERS_DIRECTORY']  = 'e107_handlers/';
$e107_paths['LANGUAGES_DIRECTORY'] = 'e107_languages/';
$e107_paths['HELP_DIRECTORY']      = 'e107_docs/help/';
$e107_paths['DOWNLOADS_DIRECTORY'] = 'e107_files/downloads/';

#
# --- End of configurable variables ---
#

define('e107_INIT',  TRUE);
define('e_UC_ADMIN', 254);
define('MIN_MYSQL_UTF8_VERSION', '4.1.2');

error_reporting(E_ALL);

if (!function_exists('file_get_contents'))  die('e107 requires PHP 4.3 or greater to work correctly.');
if (!function_exists('mysql_connect'))      die('e107 requires PHP to be installed or compiled with the MySQL extension to work correctly, please see the MySQL manual for more information.');

function e107_ini_set($var, $value)
{
	if (function_exists('ini_set'))  ini_set($var, $value);
}

# Setup some PHP options
e107_ini_set('magic_quotes_sybase',      0);
e107_ini_set('magic_quotes_runtime',     0);
e107_ini_set('arg_separator.output',     '&amp;');
e107_ini_set('session.use_trans_sid',    0);
e107_ini_set('session.use_only_cookies', 1);


# Ensure that '.' is the first part of the include path
$inc_path = explode(PATH_SEPARATOR, ini_get('include_path'));
if ($inc_path[0] != '.')
{
	array_unshift($inc_path, '.');
	$inc_path = implode(PATH_SEPARATOR, $inc_path);
	e107_ini_set('include_path', $inc_path);
}
unset($inc_path);


# Check for the realpath(). Some hosts (I'm looking at you, Awardspace) are totally dumb and
# they think that disabling realpath() will somehow (I'm assuming) help improve their pathetic
# local security. Fact is, it just prevents apps from doing their proper local inclusion security
# checks. So, we refuse to work with these people.
$functions_ok = true;
$disabled_functions = ini_get('disable_functions');

if (trim($disabled_functions) != '')
{
	$disabled_functions = explode( ',', $disabled_functions );
	
	foreach ($disabled_functions as $function)  if (trim($function) == 'realpath')  $functions_ok = false;
}

if (false === $functions_ok || false === function_exists('realpath'))
{
	die('e107 requires the realpath() function to be enabled and your host appears to have disabled it. This function is required for some <b>important</b> security checks and <b>There is NO workaround</b>. Please contact your host for more information.');
}

if (!function_exists('print_a'))
{
	function print_a($var)
	{
		return '<pre>'.htmlentities(print_r($var, true), null, 'UTF-8').'</pre>';
	}
}

if (!isset($_POST['stage']))  $_POST['stage'] = 1;

header('Content-type: text/html; charset=utf-8');

$e_install = new e_install($e107_paths);
$e_install->run_stage(intval($_POST['stage']));
$e_install->set_messages();

echo $e_install->template->ParseTemplate($e_install->stage_template());

#
#	End of installation handler		------------
#

class e_install
{
	var		$e107;
	var		$stage;
	var		$logFile        = '';
	var		$template;
	var		$post_data      = array();
	var		$debug_info     = '';
	var		$required_php   = '4.3';
	var		$previous_steps = array();
	var 	$required_field;
	
	var	$_error         = '';
	var	$_messages      = array('error'=>array(), 'info'=>array(), 'debug'=>array());
	var	$_mark_required = false;
	var	$_prefix_accept = false;
	
	
	public function __construct($e107_paths)
	{
		include_once "./{$e107_paths['HANDLERS_DIRECTORY']}e107_class.php";
		
		while (@ob_end_clean());
		
		if (MAKE_INSTALL_LOG)  $this->logFile = dirname(__FILE__).'/e107InstallLog.log';
		
		if (isset($_POST['previous_steps']))
		{
			$this->previous_steps = unserialize(base64_decode($_POST['previous_steps']));
			unset($_POST['previous_steps']);
		}
		
		$this->e107      = new e107($e107_paths, realpath(dirname(__FILE__)));
		$this->template  = new SimpleTemplate();
		$this->post_data = $_POST;
		
		$e107info = array();
		include_once "./{$e107_paths['ADMIN_DIRECTORY']}ver.php";
		
		//$this->e107->e107_dirs['INSTALLER'] = $install_folder.'/';
		$this->template->SetTag('files_dir_http',        e_FILE_ABS);
		$this->template->SetTag('image_dir_http',        e_FILE_ABS.'install/images/');
		$this->template->SetTag('app_version',       	 $e107info['e107_version']);
		//$this->template->SetTag('installer_css_http',    $_SERVER['PHP_SELF'].'?object=stylesheet');
		//$this->template->SetTag('installer_folder_http', e_HTTP.$install_folder.'/');
		
		$this->required_field = '<span class="rfield">*&nbsp;</span>';
	}
	
	public function e_install($e107_paths)
	{	# PHP 4 compat
		$this->__construct($e107_paths);
	}
	
	public function run_stage($stage)
	{
		$stage = 'stage_'.$stage;
		
		if (!method_exists($this, $stage))  $stage='stage_invalid';
		
		$this->$stage();
	}
	
	public function stage_1()
	{
		$this->stage = 1;
		$this->get_lan_file();
		$this->logLine("\n".LANINS_002.' '.LANINS_003.' [ '.LANINS_004.' ]: --- '.LANINS_109.' ---');
		
		# Prepare HTML
		$this->set_message(LANINS_005);
		$html =			'<form method="post" id="language_select" action="'.$_SERVER['PHP_SELF'].('debug' == $_SERVER['QUERY_STRING'] ? '?debug' : '').'">
							<table>
								<colgroup>
									<col style="width: 50%;" />
									<col style="width: 50%;" />
								</colgroup>
								<tbody>
									<tr>
										<td class="stage right v-mid">
											'.LANINS_004.'
										</td>
										<td class="stage">
											<select name="language" id="language" class="tbox focusme">';
		foreach ($this->get_languages() as $v)  $html.= '
												<option value="'.$v.'"'.('English' == $v ? ' selected="selected"' : '').'>'.$v.'&nbsp;</option>';
		$html.= '
											</select>
										</td>
									</tr>
								</tbody>
							</table>
							<div class="navigation">
								<input type="hidden" name="stage" value="'.($this->stage+1).'" />'.(!$this->previous_steps ? '' : '
								<input type="hidden" name="previous_steps" value="'.base64_encode(serialize($this->previous_steps)).'" />').'
								<input type="submit" id="submit" class="button" value="'.LANINS_006.'" />
							</div>
						</form>';
		
		# Set contents
		$this->template->SetTag('installation_heading', sprintf(LANINS_001, $this->template->getTag('app_version')));
		$this->template->SetTag('stage_num',            LANINS_003);
		$this->template->SetTag('stage',            	$this->stage);
		$this->template->SetTag('stage_step',           sprintf(LANINS_002a, LANINS_003));
		$this->template->SetTag('stage_title',          LANINS_004);
		$this->template->SetTag('static_title',         LANINS_004b);
		$this->template->SetTag('stage_content',        $html);
	}
	
	public function stage_2()
	{
		$this->stage = 2;
		
		if (!$this->_error)
		{
			$this->previous_steps['language'] = $_POST['language'];
			$this->get_lan_file();
			$this->logLine("\t\t".LANINS_004a.': '.$_POST['language']."\n".LANINS_002.' '.LANINS_003.' [ '.LANINS_004.' ]: --- '.LANINS_110.' ---'."\n\n".LANINS_002.' '.LANINS_021.' [ '.LANINS_022.' ]: --- '.LANINS_109.' ---');
			$this->set_message(nl2br(LANINS_023));
		}
		else
		{
			$this->logLine("\t\t".LANINS_011.":\n\t\t\t".implode(":\n\t\t\t", $this->_messages['error']));
		}
		
		# Prepare HTML
		$html =			'<form method="post" id="versions" action="'.$_SERVER['PHP_SELF'].('debug' == $_SERVER['QUERY_STRING'] ? '?debug' : '').'">
							<table>
								<colgroup>
									<col style="width: 20%;" />
									<col style="width: 40%;" />
									<col style="width: 40%;" />
								</colgroup>
								<tbody>
									<tr>
										<td class="stage">'.$this->required_field.LANINS_024.'</td>
										<td class="stage"><input class="tbox focusme'.($this->_mark_required && !$_POST['server'] ? ' required' : '').'" id="server" name="server" size="40" value="localhost" maxlength="100" type="text" /></td>
										<td class="stage"><div class="hint">'.LANINS_030.'</div></td>
									</tr>
									<tr>
										<td class="stage">'.$this->required_field.LANINS_025.'</td>
										<td class="stage"><input class="tbox'.($this->_mark_required && !$_POST['name'] ? ' required' : '').'" id="name" name="name"'.(isset($_POST['name']) ? 'value="'.$this->previous_steps['mysql']['user'].'"' : '').' size="40" maxlength="100" type="text" /></td>
										<td class="stage"><div class="hint">'.LANINS_031.'</div></td>
									</tr>
									<tr>
										<td class="stage">'.LANINS_026.'</td>
										<td class="stage"><input class="tbox" id="password" name="password" size="40"'.(isset($_POST['password']) ? 'value="'.$this->previous_steps['mysql']['password'].'"' : '').' maxlength="100" type="password" /></td>
										<td class="stage"><div class="hint">'.LANINS_032.'</div></td>
									</tr>
									<tr>
										<td class="stage">'.$this->required_field.LANINS_027.'</td>
										<td class="stage"><input class="tbox'.($this->_mark_required && !$_POST['db'] ? ' required' : '').'" name="db"'.(isset($_POST['db']) ? 'value="'.$this->previous_steps['mysql']['db'].'"' : '').' size="40" id="db" maxlength="100" type="text" /><br /><label class="defaulttext"><input name="createdb" value="1" type="checkbox" '.(isset($_POST['createdb']) && $this->previous_steps['mysql']['createdb'] ? ' checked="checked"' : '').' /> '.LANINS_028.'</label></td>
										<td class="stage"><div class="hint">'.LANINS_033.'</div></td>
									</tr>
									<tr>
										<td class="stage">'.LANINS_DB_UTF8_CAPTION.'</td>
										<td class="stage"><label class="defaulttext"><input class="MT05" id="db_utf8" name="db_utf8" value="1" type="checkbox"'.(!isset($this->previous_steps['mysql']['db_utf8']) || true === $this->previous_steps['mysql']['db_utf8'] ? ' checked="checked"' : '').' /> '.LANINS_DB_UTF8_LABEL.'</label></td>
										<td class="stage"><div class="hint">'.LANINS_DB_UTF8_TOOLTIP.'</div></td>
									</tr>
									<tr>
										<td class="stage">'.LANINS_029.'</td>
										<td class="stage"><input class="tbox'.($this->_mark_required && false === $this->_prefix_accept ? ' required' : '').'" name="prefix" size="40" id="prefix" value="'.(isset($_POST['prefix']) ? $this->previous_steps['mysql']['prefix'] : 'e107_').'" maxlength="100" type="text" /></td>
										<td class="stage"><div class="hint">'.($this->_mark_required && false === $this->_prefix_accept ? LANINS_105 : LANINS_034).'</div></td>
									</tr>
								</tbody>
							</table>
							<div class="navigation">
								<input type="hidden" name="stage" value="'.($this->stage+1).'" />'.(!$this->previous_steps ? '' : '
								<input type="hidden" name="previous_steps" value="'.base64_encode(serialize($this->previous_steps)).'" />').'
								<input type="submit" id="submit" class="button" value="'.LANINS_035.'" />
							</div>
						</form>';
		
		# Set contents
		$this->template->SetTag('installation_heading', sprintf(LANINS_001, $this->template->getTag('app_version')));
		$this->template->SetTag('stage_num',            LANINS_021);
		$this->template->SetTag('stage',            	$this->stage);
		$this->template->SetTag('stage_step',           sprintf(LANINS_002a, LANINS_021));
		$this->template->SetTag('stage_title',          LANINS_022);
		$this->template->SetTag('static_title',         LANINS_022a);
		$this->template->SetTag('stage_content',        $html);
	}
	
	public function stage_3()
	{
		$this->stage = 3;
		$this->get_lan_file();
		
		$this->previous_steps['mysql']['db']       = trim($_POST['db']);
		$this->previous_steps['mysql']['user']     = trim($_POST['name']);
		$this->previous_steps['mysql']['server']   = trim($_POST['server']);
		$this->previous_steps['mysql']['prefix']   = trim($_POST['prefix']);
		$this->previous_steps['mysql']['db_utf8']  = (isset($_POST['db_utf8'])  && $_POST['db_utf8']  ? true : false);
		$this->previous_steps['mysql']['createdb'] = (isset($_POST['createdb']) && $_POST['createdb'] ? true : false);
		$this->previous_steps['mysql']['password'] = $_POST['password'];
		
		$success = $this->_check_name($this->previous_steps['mysql']['db']) && $this->_check_name($this->previous_steps['mysql']['prefix']) ? $this->_checkDbFields($this->previous_steps['mysql']) : false;
		
		if (!$success || '' == $this->previous_steps['mysql']['server'] || '' == $this->previous_steps['mysql']['db'] || '' == $this->previous_steps['mysql']['user'])
		{	# Required fields empty - gettings back to step 2
			$this->_error         = true;
			$this->_mark_required = true;
			$this->_prefix_accept = $success;
			$this->set_message(LANINS_039, 'error');
			
			$this->stage_2();
			
			$this->template->SetTag('stage_title',      LANINS_022.' - '.LANINS_040);
			
			return;
		}
		
		if (!@mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']))
		{	# Invalid mySQL credentials
			$this->_error         = true;
			$this->_mark_required = true;
			$this->_prefix_accept = true;
			$this->set_message(LANINS_041.'<br /><br /><b>'.LANINS_083.':</b><br /><i>'.mysql_error().'</i>', 'error');
			
			$this->stage_2();
			
			$this->template->SetTag('stage_title',      LANINS_022.' - '.LANINS_040);
			
			return;
		}
		
		$query = '';
		
		preg_match('/^(.*?)($|-)/', mysql_get_server_info(), $mysql_version);
		
		if (version_compare($mysql_version[1], MIN_MYSQL_UTF8_VERSION, '>='))
		{	# Attempt to create utf8 database if requested
			$db_utf8 = '';
			if ($this->previous_steps['mysql']['db_utf8'])
			{
				$db_utf8 = ' CHARACTER SET `utf8` ';
				@mysql_query('SET NAMES `utf8`');
			}
			
			if ($this->previous_steps['mysql']['createdb'])
			{	# Create new UTF-8 DB
				$query = 'CREATE DATABASE '.$this->previous_steps['mysql']['db'].$db_utf8;
			}
			elseif($db_utf8)
			{	# Set existing non-UTF-8 DB to UTF-8 charset
				$query = 'ALTER DATABASE '.$this->previous_steps['mysql']['db'].$db_utf8;
			}
		}
		else
		{	# MySQL is not UTF-8 compatible - reset db_utf8
			$this->previous_steps['mysql']['db_utf8'] = 0;
			
			if ($this->previous_steps['mysql']['createdb'] == 1)
			{	# Create non-UTF-8 DB
				$query = 'CREATE DATABASE '.$this->previous_steps['mysql']['db'];
			}
		}
		
		$success = (bool) ($query ? mysql_query($query) : mysql_select_db($this->previous_steps['mysql']['db']));
		
		if (!$success)
		{
			$this->_error         = true;
			$this->_mark_required = true;
			$this->_prefix_accept = true;
			$this->set_message(($this->previous_steps['mysql']['createdb'] ? LANINS_043.'<br /><br />' : '').nl2br('<b>'.LANINS_083.':</b><br /><i>'.mysql_error().'</i>'), 'error');
			
			$this->stage_2();
			
			$this->template->SetTag('stage_title',      LANINS_022.' - '.LANINS_040);
			
			return;
		}
		
		$this->logLine("\t\t\t".print_a($this->previous_steps['mysql'])."\n".LANINS_002.' '.LANINS_021.' [ '.LANINS_022.' ]: --- '.LANINS_110.' ---'."\n\n".LANINS_002.' '.LANINS_036.' [ '.LANINS_037.LANINS_038.' ]: --- '.LANINS_109.' ---');
		
		$this->set_message(LANINS_042.'<br /><br />
						'.($this->previous_steps['mysql']['createdb'] ? LANINS_044 : LANINS_054).'<br /><br />
						'.LANINS_045);
		
		$html =			'<form method="post" id="language_select" action="'.$_SERVER['PHP_SELF'].('debug' == $_SERVER['QUERY_STRING'] ? '?debug' : '').'">
							<div class="navigation-top"><!-- --></div>
							<div class="navigation">
								<input type="hidden" name="stage" value="'.($this->stage+1).'" />'.(!$this->previous_steps ? '' : '
								<input type="hidden" name="previous_steps" value="'.base64_encode(serialize($this->previous_steps)).'" />').'
								<input type="submit" class="button" value="'.LANINS_035.'" />
							</div>
						</form>';
		
		# Set contents
		$this->template->SetTag('installation_heading', sprintf(LANINS_001, $this->template->getTag('app_version')));
		$this->template->SetTag('stage_num',            LANINS_036);
		$this->template->SetTag('stage',            	$this->stage);
		$this->template->SetTag('stage_step',           sprintf(LANINS_002a, LANINS_036));
		$this->template->SetTag('stage_title',          LANINS_037.(isset($this->previous_steps['mysql']['createdb']) && $this->previous_steps['mysql']['createdb'] ? LANINS_038 : ''));
		$this->template->SetTag('static_title',         LANINS_038a);
		$this->template->SetTag('stage_content',        $html);
	}
	
	public function stage_4()
	{
		$this->stage = 4;
		$this->get_lan_file();
		
		if (!isset($_POST['retest_perms']))  $this->logLine("\n".LANINS_002.' '.LANINS_036.' [ '.LANINS_037.LANINS_038.' ]: --- '.LANINS_110.' ---'."\n\n".LANINS_002.' '.LANINS_007.' [ '.LANINS_008.' ]: --- '.LANINS_109.' ---');
		else                                 $this->logLine("\n\t\t".LANINS_009);
		
		$version_fail = false;
		$perms_errors = array();
		$not_writable = $this->_check_writable_perms('must_write');	# Some directories MUST be writable
		$opt_writable = $this->_check_writable_perms('can_write');	# Some directories CAN optionally be writable
		
		if ($not_writable)
		{
			$perms_pass   = false;
			$perms_notes  = '<div class="error">'.LANINS_018.'</div>';
			foreach ($not_writable as $file)  $perms_errors[] = (substr($file, -1) == '/' ? LANINS_010a : LANINS_010).'<br /><b>'.$file.'</b>';
		}
		elseif ($opt_writable)
		{
			$perms_pass   = true;
			$perms_notes  = '<div class="warning">'.LANINS_106.'</div>';
			foreach ($opt_writable as $file)  $perms_errors[] = (substr($file, -1) == '/' ? LANINS_010a : LANINS_010).'<br /><b>'.$file.'</b>';
		}
		elseif (filesize('e107_config.php') > 1)
		{	# Must start from an empty e107_config.php
			$perms_pass   = false;
			$perms_notes  = '<div class="error">'.LANINS_122.'</div>';
			$perms_errors = '<div class="error-text">'.LANINS_121.'</div>';
		}
		else
		{
			$perms_pass   = true;
			$perms_notes  = '<div class="success">'.LANINS_017.'</div>';
			$perms_errors = LANINS_104;
		}
		
		if (!function_exists('mysql_connect'))
		{
			$version_fail = true;
			$mysql_note   = '<div class="error-text">'.LANINS_011.'</div>';
			$mysql_help   = '<div class="error">'.LANINS_012.'</div>';
		}
		elseif (!@mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']))
		{
			$mysql_note   = '<div class="error-text">'.LANINS_011.'</div>';
			$mysql_help   = '<div class="error">'.LANINS_013.'</div>';
		}
		else
		{
			$mysql_note   = mysql_get_server_info();
			$mysql_help   = '<div class="success">'.LANINS_017.'</div>';
		}
		
		if (is_array($perms_errors))  $perms_errors = '<div class="error-text">'.implode($perms_errors).'</div>';
		
		$xml_installed    = function_exists('utf8_encode');
		$php_version      = phpversion();
		$php_help         = version_compare($php_version, $this->required_php, '>=') ? '<div class="success">'.LANINS_017.'</div>' : '<div class="error">'.LANINS_019.'</div>';
		
		if (!$perms_pass)     $this->logLine("\n\t\t".LANINS_011.': '.$perms_errors);
		if ($version_fail)    $this->logLine("\n\t\t".LANINS_011.': '.$mysql_help);
		if (!$xml_installed)  $this->logLine("\n\t\t".LANINS_011.': '.LANINS_053);
		
		$html             =
						'<form method="post" id="versions" action="'.$_SERVER['PHP_SELF'].('debug' == $_SERVER['QUERY_STRING'] ? '?debug' : '').'">
							<table>
								<tbody>
									<colgroup>
										<col style="width: 20%;" />
										<col style="width: 40%;" />
										<col style="width: 40%;" />
									</colgroup>
									<tr>
										<td class="stage v-mid">'.LANINS_014.':</td>
										<td class="stage v-mid">'.$perms_errors.'</td>
										<td class="stage v-mid">'.$perms_notes.'</td>
									</tr>
									<tr>
										<td class="stage v-mid">'.LANINS_015.':</td>
										<td class="stage v-mid">'.$php_version.'</td>
										<td class="stage v-mid">'.$php_help.'</td>
									</tr>
									<tr>
										<td class="stage v-mid">'.LANINS_016.':</td>
										<td class="stage v-mid">'.$mysql_note.'</td>
										<td class="stage v-mid">'.$mysql_help.'</td>
									</tr>
									<tr>
										<td class="stage v-mid">'.LANINS_050.':</td>
										<td class="stage v-mid">'.($xml_installed ? LANINS_051 : '<div class="error-text">'.LANINS_052.'</div>').'</td>
										<td class="stage v-mid"><div class="'.($xml_installed ? 'success">'.LANINS_017 : 'error">'.LANINS_053).'</div></td>
									</tr>
								</tbody>
							</table>
							<div class="navigation">
								<input type="hidden" name="stage" value="'.($perms_pass ? $this->stage+1 : $this->stage).'" />'.(!$this->previous_steps ? '' : '
								<input type="hidden" name="previous_steps" value="'.base64_encode(serialize($this->previous_steps)).'" />').''.($perms_pass && !$version_fail && $xml_installed ? '
								<input type="submit" name="continue_install" class="button" value="'.LANINS_020.'" />' : '
								<input type="submit" name="retest_perms" class="button" value="'.LANINS_009.'" />').'
							</div>
						</form>';
		
		# Set contents
		$this->template->SetTag('installation_heading', sprintf(LANINS_001, $this->template->getTag('app_version')));
		$this->template->SetTag('stage_num',            LANINS_007);
		$this->template->SetTag('stage',            	$this->stage);
		$this->template->SetTag('stage_step',           sprintf(LANINS_002a, LANINS_007));
		$this->template->SetTag('stage_title',          LANINS_008);
		$this->template->SetTag('static_title',         LANINS_008a);
		$this->template->SetTag('stage_content',        $html);
	}
	
	public function stage_5()
	{
		$this->stage = 5;
		$this->get_lan_file();
		
		if (!$this->_error)  $this->logLine("\n".LANINS_002.' '.LANINS_007.' [ '.LANINS_008.' ]: --- '.LANINS_110.' ---'."\n\n".LANINS_002.' '.LANINS_046.' [ '.LANINS_047.' ]: --- '.LANINS_109.' ---');
		else                 { $this->logLine("\n\t\t".LANINS_011.": \n\t\t\t".implode("\n\t\t\t", $this->_messages['error'])); $this->_mark_required = true; } 
		
		$html =			'<form method="post" id="admin_info" action="'.$_SERVER['PHP_SELF'].('debug' == $_SERVER['QUERY_STRING'] ? '?debug' : '').'">
							<table cellspacing="0">
								<tbody>
									<colgroup>
										<col style="width: 25%;" />
										<col style="width: 25%;" />
										<col style="width: 50%;" />
									</colgroup>
									<tr>
										<td class="stage"><label for="u_name">'.$this->required_field.LANINS_072.'</label></td>
										<td class="stage"><input class="tbox'.($this->_mark_required && !$_POST['u_name'] ? ' focusme required' : '').'" name="u_name" id="u_name" size="30" value="'.(isset($_POST['u_name']) ? $this->previous_steps['admin']['user'] : 'admin').'" maxlength="60" type="text" /></td>
										<td class="stage">'.LANINS_073.'</td>
									</tr>
									<tr>
										<td class="stage"><label for="d_name">'.$this->required_field.LANINS_074.'</label></td>
										<td class="stage"><input class="tbox'.($this->_mark_required && !$_POST['d_name'] ? ' required' : '').'" name="d_name" id="d_name" size="30" value="'.(isset($_POST['d_name']) ? $this->previous_steps['admin']['display'] : 'Administrator').'" maxlength="60" type="text" /></td>
										<td class="stage">'.LANINS_075.'</td>
									</tr>
									<tr>
										<td class="stage"><label for="pass1">'.$this->required_field.LANINS_076.'</label></td>
										<td class="stage"><input class="tbox focusme'.($this->_mark_required && !$_POST['pass1'] ? ' required' : '').'" name="pass1" size="30" id="pass1" value="" maxlength="60" type="password" /></td>
										<td class="stage">'.LANINS_077.'</td>
									</tr>
									<tr>
										<td class="stage"><label for="pass2">'.$this->required_field.LANINS_078.'</label></td>
										<td class="stage"><input class="tbox'.($this->_mark_required && !$_POST['pass2'] ? ' required' : '').'" name="pass2" size="30" id="pass2" value="" maxlength="60" type="password" /></td>
										<td class="stage">'.LANINS_079.'</td>
									</tr>
									<tr>
										<td class="stage"><label for="email">'.$this->required_field.LANINS_080.'</label></td>
										<td class="stage"><input class="tbox'.($this->_mark_required && !$_POST['email'] ? ' required' : '').'" name="email" size="30" id="email" value="'.(isset($_POST['email']) ? $this->previous_steps['admin']['email'] : LANINS_082).'" maxlength="100" type="text" /></td>
										<td class="stage">'.LANINS_081.'</td>
									</tr>
								</tbody>
							</table>
							<div class="navigation">
								<input type="hidden" name="stage" value="'.($this->stage+1).'" />'.(!$this->previous_steps ? '' : '
								<input type="hidden" name="previous_steps" value="'.base64_encode(serialize($this->previous_steps)).'" />').'
								<input id="submit" class="button" value="'.LANINS_035.'" type="submit" />
							</div>
						</form>';
		
		# Set contents
		$this->template->SetTag('installation_heading', sprintf(LANINS_001, $this->template->getTag('app_version')));
		$this->template->SetTag('stage_num',            LANINS_046);
		$this->template->SetTag('stage',            	$this->stage);
		$this->template->SetTag('stage_step',           sprintf(LANINS_002a, LANINS_046));
		$this->template->SetTag('stage_title',          LANINS_047);
		$this->template->SetTag('static_title',         LANINS_047a);
		$this->template->SetTag('stage_content',        $html);
	}
	
	public function stage_6()
	{
		$this->stage = 6;
		$this->get_lan_file();
		
		$_POST['u_name'] = str_replace(array("'", '"'), '', $_POST['u_name']);
		$_POST['d_name'] = str_replace(array("'", '"'), '', $_POST['d_name']);
		
		$this->previous_steps['admin']['user']     = $_POST['u_name'];
		$this->previous_steps['admin']['display']  = $_POST['d_name'] ? $_POST['d_name'] : $_POST['u_name'];
		$this->previous_steps['admin']['email']    = $_POST['email'];
		$this->previous_steps['admin']['password'] = $_POST['pass1'];
		
		if ('' == trim($_POST['u_name']) || '' == trim($_POST['email']) || '' == trim($_POST['pass1']))
		{
			if ('debug' == $_SERVER['QUERY_STRING'])  $this->set_message(print_a($_POST, true), 'debug');
			
			$this->_error = true;
			$this->set_message(LANINS_086, 'error');
			
			$this->stage_5();
			
			$this->template->SetTag('stage_title',      LANINS_047.' - '.LANINS_040);
			
			return;
		}
		elseif($_POST['pass1'] != $_POST['pass2'])
		{
			if ('debug' == $_SERVER['QUERY_STRING'])  $this->set_message(print_a($_POST, true), 'debug');
			
			$this->_error = true;
			$this->set_message(LANINS_049, 'error');
			
			$this->stage_5();
			
			$this->template->SetTag('stage_title',      LANINS_047.' - '.LANINS_040);
			
			return;
		}
		
		$this->logLine("\n".LANINS_002.' '.LANINS_046.' [ '.LANINS_047.' ]: --- '.LANINS_110.' ---'."\n\n".LANINS_002.' '.LANINS_056.' [ '.LANINS_055.' ]: --- '.LANINS_109.' ---');
		
		$this->set_message(LANINS_057);
		
		$html = 		'<form method="post" id="admin_info" action="'.$_SERVER['PHP_SELF'].('debug' == $_SERVER['QUERY_STRING'] ? '?debug' : '').'">
							<div class="navigation-top"><!-- --></div>
							<div class="navigation">
								<input type="hidden" name="stage" value="'.($this->stage+1).'" />'.(!$this->previous_steps ? '' : '
								<input type="hidden" name="previous_steps" value="'.base64_encode(serialize($this->previous_steps)).'" />').'
								<input id="submit" class="button" value="'.LANINS_035.'" type="submit" />
							</div>
						</form>';
		
		# Set contents
		$this->template->SetTag('installation_heading', sprintf(LANINS_001, $this->template->getTag('app_version')));
		$this->template->SetTag('stage_num',            LANINS_056);
		$this->template->SetTag('stage',            	$this->stage);
		$this->template->SetTag('stage_step',           sprintf(LANINS_002a, LANINS_056));
		$this->template->SetTag('stage_title',          LANINS_055);
		$this->template->SetTag('static_title',         LANINS_055a);
		$this->template->SetTag('stage_content',        $html);
	}
	
	public function stage_7()
	{
		$this->stage = 7;
		$this->get_lan_file();
		$this->logLine("\n".LANINS_002.' '.LANINS_056.' [ '.LANINS_055.' ]: --- '.LANINS_109.' ---'."\n\n".LANINS_002.' '.LANINS_058.' [ '.LANINS_071.' ]: --- '.LANINS_109.' ---');
		
		$config_result = $this->_write_config();
		
		if ($config_result) 
		{
			$html = '';
			$this->set_message($config_result, 'error'); 
			$this->_error = true;
			$this->logLine("\t".'Error writing e107_config.php config file: '.$config_result);
		} 
		else 
		{
			$this->logLine("\t".'The e107_config.php config file is written successfully');
			$errors = $this->_create_tables();
			
			if ($errors) 
			{
				$this->set_message($errors, 'error');
				$html = '';
				$this->_error = true;
				$this->logLine("\t".'Error writing database content: '.$errors);
			}
			else 
			{
				$this->logLine("\t".'Database content successfully created');
				$this->set_message(LANINS_069);
				
				$html = '<form method="post" id="confirmation" action="index.php">
							<div class="navigation-top"><!-- --></div>
							<div class="navigation">
								<input type="hidden" name="stage" value="'.($this->stage+1).'" />'.(!$this->previous_steps ? '' : '
								<input type="hidden" name="previous_steps" value="'.base64_encode(serialize($this->previous_steps)).'" />').'
								<input type="submit" id="submit" class="button" value="'.LANINS_035.'" />
							</div>
						</form>';
			}
		}
		
		# Set contents
		$this->template->SetTag('installation_heading', sprintf(LANINS_001, $this->template->getTag('app_version')));
		$this->template->SetTag('stage_num',            LANINS_058);
		$this->template->SetTag('stage',            	$this->stage);
		$this->template->SetTag('stage_step',           sprintf(LANINS_002a, LANINS_058));
		$this->template->SetTag('stage_title',          $this->_error ? LANINS_071b : LANINS_071);
		$this->template->SetTag('static_title',         $this->_error ? LANINS_071c : LANINS_071a);
		$this->template->SetTag('stage_content',        $html);
		
		$this->logLine("\n".LANINS_002.' '.LANINS_058.' [ '.LANINS_071.' ]: --- '.LANINS_110.' ---');
	}
	
	public function stage_invalid()
	{
		$this->set_message(LANINS_000, 'error');
		
		$this->template->SetTag('installation_heading', LANINS_001);
		$this->template->SetTag('stage_num',            LANINS_011);
		$this->template->SetTag('stage',            	0);
		$this->template->SetTag('stage_step',           '');
		$this->template->SetTag('stage_title',          LANINS_125);
		$this->template->SetTag('static_title',         LANINS_125a);
		$this->template->SetTag('stage_content',        '');
	}
	
	public function stage_template()
	{ 
		// <span class="tinytext">{stage_step}</span>
		//{stage_num}
		//<h1>{installation_heading}</h1>
		return 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>'.LANINS_TITLE.' {installation_heading} - {static_title}</title>
	<link rel="icon" href="'.e_HTTP.'favicon.ico" type="image/x-icon" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta http-equiv="content-style-type" content="text/css" />
	<style type="text/css">
		html, body					{ width:100%; }
		html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr,
		acronym, address, big, cite, code, del, dfn, em, font, img, ins, kbd, q, s, samp, small, strike, strong, sub,
		sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead,
		tr, th, td					{ margin:0; padding:0; border:0; }
		h1, h2, h3, h4, h5, h6		{ line-height:normal; }
		ol, ul						{ list-style:none; }
		body						{ line-height:1; }
		q:before, q:after,
		blockquote:before,
		blockquote:after			{ content:\'\'; content:none; }
		:focus						{ outline:0; }
		ins							{ text-decoration:none; }
		del							{ text-decoration:line-through; }
		table						{ border-collapse:collapse; border-spacing:0; }
		textarea.tbox,
		input.input-text			{ width:auto; padding:5px; color:#111111; background-color:#FFFFFF; border:1px solid #CCCCCC; }
		textarea.tbox:focus,
		input.input-text:focus		{ background-color:#EEEEEE; border:1px solid #CCCCCC; }
		body						{ background:#FFFFFF url(e107_files/install/images/mainbg.png) repeat-x 0 0; font:12px/16px Arial,Helvetica,sans-serif; color:#656565; }
		h1							{ font:normal 26px Arial,Helvetica,sans-serif; }
		h2							{ font:normal 22px Arial,Helvetica,sans-serif; }
		h3							{ font:normal 18px Arial,Helvetica,sans-serif; }
		h4							{ font:normal 16px Arial,Helvetica,sans-serif; }
		img							{ border:0; }
		a							{ color:#3399CC; text-decoration:none; cursor:pointer; }
		a:hover						{ color:#333333; text-decoration:underline; }
		*							{ margin:0; padding:0; }
		* .clearfix					{ display:block; }
		* html .clearfix			{ height:1%; }
		.MT05						{ margin-top:5px; }
		.MT20						{ margin-top:15px; }
		.clear						{ clear:both; }
		.clearfix					{ display:inline-block; }
		.clearfix:after				{ width:0; height:0; clear:both; content:\' \'; display:block; font-size:0; line-height:0; visibility:hidden; }
		.tbold						{ font-weight:bold; }
		.tnormal					{ font-weight:normal; }
		.fitalic					{ font-style:italic; }
		.fnormal					{ font-style:normal; }
		.right						{ text-align:right !important; }
		.smalltext					{ font-size:12px; font-weight:normal; }
		.smallblacktext				{ font-size:12px; font-weight:bold; }
		.f-left					{ float:left; }
		.caption,
		.bodytable,
		.mediumtext,
		.defaulttext				{ font-size:13px; font-weight:normal; }
		.fborder					{ padding:3px; margin-top:3px; text-align:left; background-color:transparent; border:0px none; }
		.v-top						{ vertical-align:top; }
		.v-mid						{ vertical-align:middle; }
		.install-logo				{ padding:38px 0px 15px 25px; }
		.install-heading			{ font-size:14px; margin-top:117px; margin-left:125px; }
		.wrapper					{ width:1000px; margin:0 auto; }
		.headerbg 					{ background:url(e107_files/install/images/headerbg.png) repeat-x scroll 0 0 transparent; height:238px; width:100%; position:relative; z-index:10; }
		.stage-1					{ height:300px; position:relative; z-index:10; background:url(e107_files/install/images/1.png) no-repeat 100% 0; }
		.stage-2					{ height:300px; position:relative; z-index:10; background:url(e107_files/install/images/2.png) no-repeat 100% 0; }
		.stage-3					{ height:300px; position:relative; z-index:10; background:url(e107_files/install/images/3.png) no-repeat 100% 0; }
		.stage-4					{ height:300px; position:relative; z-index:10; background:url(e107_files/install/images/4.png) no-repeat 100% 0; }
		.stage-5					{ height:300px; position:relative; z-index:10; background:url(e107_files/install/images/5.png) no-repeat 100% 0; }
		.stage-6					{ height:300px; position:relative; z-index:10; background:url(e107_files/install/images/6.png) no-repeat 100% 0; }
		.stage-7					{ height:300px; position:relative; z-index:10; background:url(e107_files/install/images/7.png) no-repeat 100% 0; }
		.stage-7-finished			{ height:300px; position:relative; z-index:10; background:url(e107_files/install/images/8.png) no-repeat 100% 0; }
		.hint						{ background-color:#FFFFD5; border:1px solid #FFCC00; font-size:12px; padding:7px; margin-top:5px; -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; }
		.info						{ font-size:14px; padding:10px 25px 15px 25px; }
		.orange						{ font-size:22px; color:#ED7700; font-style:italic; }
		.tinytext					{ font-size:12px; margin-top:10px; padding:0px 35px; }
		.smalltext					{ font-size:14px; }
		.headerbg h1 				{ padding-left:0px;  font-style:italic; font-weight:bold; font-size: 20px }
		.stage-title				{ margin:30px 35px 0px 40px; font-size:22px;}
		.main-container				{ background:#FFFFFF url(e107_files/install/images/contentbg.png) repeat-x 0 0; margin:0 auto ; width:100%; margin:10px 0; padding:20px 0px; }
		.main-content				{ padding:0 0px 10px; }
		.main-content table			{ width:100%; margin:0 auto ; }
		.top-content				{ margin:0 20px 30px 20px; background-color:#FFFFFF; padding:20px 20px; display:block; }
		.top-content-title			{ font-size:16px; font-weight:bold; padding-bottom:10px; margin-bottom:10px; text-shadow:2px 2px 2px #CCCCCC; background:url(e107_files/install/images/rightbox_title_bg.png) repeat-x 0 100%; }
		.stage						{ padding:10px 5px 10px; text-align:left; background-color:transparent; border-bottom:1px solid #CCCCCC; }
		.tbox						{ margin:5px 5px; padding:5px 10px; font-size:14px; font-weight:normal; color:#111111; background-color:#FFFFFF; border:1px solid #CCCCCC; border-radius:3px; -moz-border-radius:3px; -webkit-border-radius:3px; }
		.tbox:focus					{ border:1px solid #CCCCCC; background-color:#EEEEEE; }
		.button, .button:focus		{ margin:5px 0; padding:5px 10px; text-align:center; font-weight:bold; cursor:pointer; background-color:#FFFFFF; border:1px #CCCCCC solid; border-radius:3px; -moz-border-radius:3px; -webkit-border-radius:3px; }
		.button:hover				{ color:#3399CC; border:1px #EEEEEE solid; }
		.error						{ color:#FF5C5C; background-color:#FFCECE; border:1px solid #FF5C5C; padding:10px 20px; -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; }
		.warning					{ color:#FF9900 }
		.rfield						{ color:#FF9900; font-weight: bolder !important; }
		.success					{ color:green; }
		.required					{ border:1px solid #FF5C5C; }
		.error-text					{ color:#FF5C5C; }
		.navigation					{ padding:8px 3px; font-weight:bold; text-align:center; vertical-align:middle; border:1px solid #CCCCCC; border-top:0px none; background-color:#F9F9F9; z-index:10; }
		.navigation-top				{ width:100%; height:1px; background-color:#CCCCCC; }
		select.tbox, option.tbox	{ padding:3px; margin: 3px 5px }
		checkbox, label				{ margin:0 5px; }
		blockquote, q				{ quotes:none; }
	</style>
</head>
<body>
	<div class="wrapper">
		<div class="headerbg">
			<div class="stage-{stage}'.(!$this->_error && 7 == $this->template->getTag('stage') ? '-finished' : '').'">
				<div class="install-logo f-left"><img src="e107_files/install/images/e_logo.png" alt="" /></div>
				<div class="install-heading f-left">{installation_heading}</div>
				<div class="clear"><!-- --></div>
				<div class="stage-title">'.LANINS_002.' / <span class="orange">{static_title}</span></div>
			</div>
		</div>
		<div class="main-container">
			<div id="content" class="top-content clearfix">
				<div class="top-content-title clearfix">
					{stage_title}
				</div>
				{stage_errors}
				{stage_info}
				<div class="main-content">
					{stage_content}
				</div>
				{debug_info}
			</div>
		</div>
	</div>
	<script type="text/javascript">
		var e107_install = {
		   init: function() {
		      // Focus the first available to be focused element on the page
		      if (document.getElementsByClassName(\'focusme\')[\'length\']) {
		         document.getElementsByClassName(\'focusme\')[0].focus();
		      }
		   }
		}
		
		e107_install.init();
	</script>
</body>
</html>';
	}
	
	
	/**
	 * Language loader - loads the language file or raises an error on failure
	 * 
	 * @return	void
	 */
	public function get_lan_file()
	{
		if (!isset($this->previous_steps['language']))
			$this->previous_steps['language'] = 'English';
		
		$this->lan_file = $this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$this->previous_steps['language'].'/lan_installer.php';
		
		if (is_readable($this->lan_file))
			include_once $this->lan_file;
		elseif (is_readable($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].'English/lan_installer.php'))
			include_once $this->e107->e107_dirs['LANGUAGES_DIRECTORY'].'English/lan_installer.php';
		else
			$this->set_message('Fatal: Could not get valid language file for installation.', 'error');
	}
	
	/**
	 * Available languages getter
	 *
	 * @return	array	Available languages array
	 */
	public function get_languages()
	{
		$handle  = opendir($this->e107->e107_dirs['LANGUAGES_DIRECTORY']);
		$lan_dir = $this->e107->e107_dirs['LANGUAGES_DIRECTORY'];
		
		while ($file = readdir($handle))
		{
			if (!in_array($file, array('.','..','/','CVS','.svn')) && file_exists("./{$lan_dir}{$file}/lan_installer.php"))
				$lanlist[] = $file;
		}
		closedir($handle);
		return $lanlist;
	}
	
	/**
	 * Log writer - writes a line to the log file
	 *
	 * @param	string	$logLine
	 * @return	void
	 */
	public function logLine($logLine)
	{
		if (!MAKE_INSTALL_LOG || '' == $this->logFile)  return;
		
		if (!is_file($this->logFile))  file_put_contents($this->logFile, '[ '.($now = time()).', '.gmstrftime('%y-%m-%d %H:%M:%S', $now)."]\n");
		
		$logfp = fopen($this->logFile, 'a+');
		fwrite($logfp, $logLine."\n");
		fclose($logfp);
	}
	
	/**
	 * Messages handler
	 *
	 * @param	string	$message	The message
	 * @param	string	$type		Type of the message - defines where should it go and how to be shown
	 * @return	void
	 */
	public function set_message($message, $type='info')
	{
		if (!in_array($type, array_keys($this->_messages)))  return;
		
		if ('debug' != $type)  $this->_messages[$type][] = $message;
		
		$this->_messages['debug'][] = array (
			'info'      => $message,
			'backtrace' => debug_backtrace(),
		);
	}
	
	/**
	 * Prepares and sets debug info for output
	 * 
	 * @return	void
	 */
	public function set_messages()
	{
		$this->template->SetTag('stage_errors', (empty($this->_messages['error']) ? '' :
				'<div class="error">
					'.implode("\n\t\t\t\t\t", $this->_messages['error']).'
				</div>'
		));
		$this->template->SetTag('stage_info', (empty($this->_messages['info']) ? '' :
				'<div class="info">
					'.implode("\n\t\t\t\t\t", $this->_messages['info']).'
				</div>'
		));
		$this->template->SetTag('debug_info', ('debug' != $_SERVER['QUERY_STRING'] ? '' :
				'<div class="debug-container">
					<div class="debug-info">
						<h3>'.LANINS_123.':</h3>
						'.print_a($e_install->debug_info).'
					</div>
					<div class="backtrace-info">
						<h3>'.LANINS_124.':</h3>
						'.print_a($e_install).'
					</div>
				</div>'
		));
	}
	
	/**
	 * DB name or table prefix validator - anything starting with a numeric followed by 'e' causes problems
	 *
	 * @param	string	$str		The name to be checked
	 * @param	bool	$blank_ok	Whether to accept blank value (should be set TRUE for prefix, FALSE for DB name)
	 * @return	bool				Valid or not
	 */
	protected function _check_name($str, $blank_ok=false)
	{
		if ('' == $str)                       return $blank_ok;
		if (preg_match('#^\d+[e|E]#', $str))  return false;
		return true;
	}
	
	/**
	 * Check an array of db-related fields for illegal characters
	 *
	 * @param	array	$fields
	 * @return	bool	true=OK, false=invalid char
	 */
	protected function _checkDbFields($fields)
	{
		if (!is_array($fields))  return false;
		
		foreach (array('db', 'user', 'server', 'prefix') as $key)
		{
			if (isset($fields[$key]) && strtr($fields[$key], "';", '    ') != $fields[$key])  return false; # Invalid character found
		}
		
		return true;
	}
	
	/**
	 * Check whether files/directories are writable
	 * 
	 * @param	str		$list	List type [can_write|must_write]
	 * @return	array			Failed files/directories list
	 */
	protected function _check_writable_perms($list='must_write')
	{
		$bad_files = array();
		$data['must_write'] = 'e107_config.php';
		$data['can_write']  = '{$FILES_DIRECTORY}cache/|{$FILES_DIRECTORY}public/|{$FILES_DIRECTORY}public/avatars/|{$PLUGINS_DIRECTORY}|{$THEMES_DIRECTORY}';
		
		if (!isset($data[$list]))  return $bad_files;
		
		foreach ($this->e107->e107_dirs as $dir_name=>$value) 
		{
			$find[]    = "{\${$dir_name}}";
			$replace[] = './'.$value;
		}
		
		$data[$list] = str_replace($find, $replace, $data[$list]);
		$files       = explode('|', trim($data[$list]));
		
		foreach ($files as $file)  if (!is_writable($file))  $bad_files[] = str_replace('./', '', $file);
		
		return $bad_files;
	}
	
	/**
	 * DB tables creator - creates all necessary DB tables and content
	 *
	 * @return	mixed	bool false on success (no errors) | string (error message) on error
	 */
	protected function _create_tables()
	{
		$link = mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']);
		
		if (!$link)  return nl2br(LANINS_084."\n\n<b>".LANINS_083.":\n</b><i>".mysql_error($link)."</i>");
		
		$db_selected = mysql_select_db($this->previous_steps['mysql']['db'], $link);
		
		if (!$db_selected)  return nl2br(LANINS_085." '{$this->previous_steps['mysql']['db']}'\n\n<b>".LANINS_083.":\n</b><i>".mysql_error($link).'</i>');
		
		$filename = $this->e107->e107_dirs['ADMIN_DIRECTORY'].'sql/core_sql.php';
		$fd       = fopen ($filename, 'r');
		$sql_data = fread($fd, filesize($filename));
		
		fclose ($fd);
		
		if (!$sql_data)  return nl2br(LANINS_060).'<br /><br />';
		
		preg_match_all('/create(.*?)(?:myisam|innodb);/si', $sql_data, $result );
		
		# Force UTF-8 again
		if ($this->previous_steps['mysql']['db_utf8'])  @mysql_query('SET NAMES `utf8`');
		
		foreach ($result[0] as $sql_table)
		{
//			preg_match("/CREATE TABLE\s(.*?)\s\(/si", $sql_table, $match);
//			$tablename = $match[1];
//			
//			preg_match_all("/create(.*?)myisam;/si", $sql_data, $result );
			$sql_table = preg_replace("/create table\s/si", "CREATE TABLE {$this->previous_steps['mysql']['prefix']}", $sql_table);
			
			if (!mysql_query($sql_table, $link))  return nl2br(LANINS_061."\n\n<b>".LANINS_083.":\n</b><i>".mysql_error($link)."</i>");
		}
		$this->logLine('All tables created');
		$datestamp = time();
		
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}news VALUES (0, '".LANINS_063."', '".LANINS_NEWS."', '', '{$datestamp}', '0', '1', 1, 0, 0, 0, 0, '0', '', 'welcome.png', 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}news_category VALUES (0, '".LANINS_087."', 'icon26.png') ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, '".LANINS_088."', 'index.php', '', '', 1, 1, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, '".LANINS_098."', 'news.php', '', '', 1, 2, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, '".LANINS_089."', 'download.php', '', '', 1, 3, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, '".LANINS_092."', 'contact.php', '', '', 1, 4, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, '".LANINS_090."', 'user.php', '', '', 2, 1, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, '".LANINS_091."', 'submitnews.php', '', '', 2, 2, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, '".LANINS_099."', 'http://e107.org/', '', '', 3, 1, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, '".LANINS_103."', 'http://e107.org/plugins/', '', '', 3, 2, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, '".LANINS_111."', 'http://e107.org/themes/', '', '', 3, 3, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, '".LANINS_112."', 'http://wiki.e107.org/', '', '', 3, 4, 0, 0, 0) ");
		
		$udirs  = 'admin/|plugins/|temp';
		$e_SELF = $_SERVER['PHP_SELF'];
		$e_HTTP = preg_replace('#'.$udirs.'#i', '', substr($e_SELF, 0, strrpos($e_SELF, '/')).'/');
		
		$pref_language = isset($this->previous_steps['language']) ? $this->previous_steps['language'] : 'English';
		
		if (file_exists($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$pref_language.'/lan_prefs.php'))
			include_once $this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$pref_language.'/lan_prefs.php';
		else
			include_once $this->e107->e107_dirs['LANGUAGES_DIRECTORY'].'English/lan_prefs.php';
		
		$site_admin_user  = $this->previous_steps['admin']['display'];
		$site_admin_email = $this->previous_steps['admin']['email'];
		
		require_once $this->e107->e107_dirs['FILES_DIRECTORY'].'def_e107_prefs.php';
		include_once $this->e107->e107_dirs['HANDLERS_DIRECTORY'].'arraystorage_class.php';
		
		$tmp = ArrayData::WriteArray($pref);
		
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('SitePrefs', '{$tmp}')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('SitePrefs_Backup', '{$tmp}')");
		
		$emote = 'a:60:{i:0;a:1:{s:2:"&|";s:7:"cry.png";}i:1;a:1:{s:3:"&-|";s:7:"cry.png";}i:2;a:1:{s:3:"&o|";s:7:"cry.png";}i:3;a:1:{s:3:":((";s:7:"cry.png";}i:4;a:1:{s:3:"~:(";s:7:"mad.png";}i:5;a:1:{s:4:"~:o(";s:7:"mad.png";}i:6;a:1:{s:4:"~:-(";s:7:"mad.png";}i:7;a:1:{s:2:":)";s:9:"smile.png";}i:8;a:1:{s:3:":o)";s:9:"smile.png";}i:9;a:1:{s:3:":-)";s:9:"smile.png";}i:10;a:1:{s:2:":(";s:9:"frown.png";}i:11;a:1:{s:3:":o(";s:9:"frown.png";}i:12;a:1:{s:3:":-(";s:9:"frown.png";}i:13;a:1:{s:2:":D";s:8:"grin.png";}i:14;a:1:{s:3:":oD";s:8:"grin.png";}i:15;a:1:{s:3:":-D";s:8:"grin.png";}i:16;a:1:{s:2:":?";s:12:"confused.png";}i:17;a:1:{s:3:":o?";s:12:"confused.png";}i:18;a:1:{s:3:":-?";s:12:"confused.png";}i:19;a:1:{s:3:"%-6";s:11:"special.png";}i:20;a:1:{s:2:"x)";s:8:"dead.png";}i:21;a:1:{s:3:"xo)";s:8:"dead.png";}i:22;a:1:{s:3:"x-)";s:8:"dead.png";}i:23;a:1:{s:2:"x(";s:8:"dead.png";}i:24;a:1:{s:3:"xo(";s:8:"dead.png";}i:25;a:1:{s:3:"x-(";s:8:"dead.png";}i:26;a:1:{s:2:":@";s:7:"gah.png";}i:27;a:1:{s:3:":o@";s:7:"gah.png";}i:28;a:1:{s:3:":-@";s:7:"gah.png";}i:29;a:1:{s:2:":!";s:8:"idea.png";}i:30;a:1:{s:3:":o!";s:8:"idea.png";}i:31;a:1:{s:3:":-!";s:8:"idea.png";}i:32;a:1:{s:2:":|";s:11:"neutral.png";}i:33;a:1:{s:3:":o|";s:11:"neutral.png";}i:34;a:1:{s:3:":-|";s:11:"neutral.png";}i:35;a:1:{s:2:"?!";s:12:"question.png";}i:36;a:1:{s:2:"B)";s:12:"rolleyes.png";}i:37;a:1:{s:3:"Bo)";s:12:"rolleyes.png";}i:38;a:1:{s:3:"B-)";s:12:"rolleyes.png";}i:39;a:1:{s:2:"8)";s:10:"shades.png";}i:40;a:1:{s:3:"8o)";s:10:"shades.png";}i:41;a:1:{s:3:"8-)";s:10:"shades.png";}i:42;a:1:{s:2:":O";s:12:"suprised.png";}i:43;a:1:{s:3:":oO";s:12:"suprised.png";}i:44;a:1:{s:3:":-O";s:12:"suprised.png";}i:45;a:1:{s:2:":p";s:10:"tongue.png";}i:46;a:1:{s:3:":op";s:10:"tongue.png";}i:47;a:1:{s:3:":-p";s:10:"tongue.png";}i:48;a:1:{s:2:":P";s:10:"tongue.png";}i:49;a:1:{s:3:":oP";s:10:"tongue.png";}i:50;a:1:{s:3:":-P";s:10:"tongue.png";}i:51;a:1:{s:2:";)";s:8:"wink.png";}i:52;a:1:{s:3:";o)";s:8:"wink.png";}i:53;a:1:{s:3:";-)";s:8:"wink.png";}i:54;a:1:{s:4:"!ill";s:7:"ill.png";}i:55;a:1:{s:7:"!amazed";s:10:"amazed.png";}i:56;a:1:{s:4:"!cry";s:7:"cry.png";}i:57;a:1:{s:6:"!dodge";s:9:"dodge.png";}i:58;a:1:{s:6:"!alien";s:9:"alien.png";}i:59;a:1:{s:6:"!heart";s:9:"heart.png";}}';
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('emote', '{$emote}') ");
		
		# Set up menu prefs so they can be 'lanned'
		$new_block = Array ( 
			'comment_caption' 			=> LANINS_096,	# 'Latest Comments'
			'comment_display' 			=> '10',
			'comment_characters' 		=> '50',
			'comment_postfix' 			=> LANINS_097,	# '[more ...]'
			'comment_title' 			=> 0,
// obsolete	'article_caption' 			=> LANINS_098,	# 'Articles'
// obsolete	'articles_display' 			=> '10',
// obsolete	'articles_mainlink' 		=> LANINS_099,	# 'Articles Front Page ...'
			'newforumposts_caption' 	=> LANINS_100,	# 'Latest Forum Posts'
			'newforumposts_display' 	=> '10',
			'forum_no_characters' 		=> '20',
			'forum_postfix' 			=> LANINS_097,	# '[more ...]'
			'update_menu' 				=> LANINS_101,	# 'Update menu Settings'
			'forum_show_topics' 		=> '1',
			'newforumposts_characters'	=> '50',
			'newforumposts_postfix' 	=> LANINS_097,	# '[more ...]'
			'newforumposts_title' 		=> 0,
			'clock_caption' 			=> LANINS_102,	# 'Date / Time'
// obsolete	'reviews_caption'			=> LANINS_103,	# 'Reviews'
// obsolete	'reviews_display'			=> '10',
// obsolete	'reviews_parents'			=> '1',
// obsolete	'reviews_mainlink'			=> LANINS_104,	# 'Review Front Page ...'
// obsolete	'articles_parents' 			=> '1'
		);
		
		$menu_conf    = serialize($new_block);
		$notify_prefs = mysql_real_escape_string("array ('event' => array ('usersup' => array ('type' => 'off', 'class' => '254', 'email' => '',),'userveri' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'login' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'logout' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'flood' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'subnews' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'newspost' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'newsupd' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'newsdel' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), ), )");
		
		preg_match('/^(.*?)($|-)/', mysql_get_server_info(), $mysql_version);
		
		if (version_compare($mysql_version[1], '4.0.1', '<'))
			$search_prefs = 'a:12:{s:11:\"user_select\";s:1:\"1\";s:9:\"time_secs\";s:2:\"60\";s:13:\"time_restrict\";s:1:\"0\";s:8:\"selector\";s:1:\"2\";s:9:\"relevance\";s:1:\"0\";s:13:\"plug_handlers\";N;s:10:\"mysql_sort\";b:0;s:11:\"multisearch\";s:1:\"1\";s:6:\"google\";s:1:\"0\";s:13:\"core_handlers\";a:5:{s:4:\"news\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"1\";}s:8:\"comments\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"2\";}s:5:\"users\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"3\";}s:9:\"downloads\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"4\";}s:5:\"pages\";a:6:{s:5:\"class\";s:1:\"0\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"order\";s:1:\"5\";}}s:17:\"comments_handlers\";a:2:{s:4:\"news\";a:3:{s:2:\"id\";i:0;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}s:8:\"download\";a:3:{s:2:\"id\";i:2;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}}s:9:\"php_limit\";s:0:\"\";}';
		else
			$search_prefs = 'a:12:{s:11:\"user_select\";s:1:\"1\";s:9:\"time_secs\";s:2:\"60\";s:13:\"time_restrict\";s:1:\"0\";s:8:\"selector\";s:1:\"2\";s:9:\"relevance\";s:1:\"0\";s:13:\"plug_handlers\";N;s:10:\"mysql_sort\";b:1;s:11:\"multisearch\";s:1:\"1\";s:6:\"google\";s:1:\"0\";s:13:\"core_handlers\";a:5:{s:4:\"news\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"1\";}s:8:\"comments\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"2\";}s:5:\"users\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"3\";}s:9:\"downloads\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"4\";}s:5:\"pages\";a:6:{s:5:\"class\";s:1:\"0\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"order\";s:1:\"5\";}}s:17:\"comments_handlers\";a:2:{s:4:\"news\";a:3:{s:2:\"id\";i:0;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}s:8:\"download\";a:3:{s:2:\"id\";i:2;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}}s:9:\"php_limit\";s:0:\"\";}';
		
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('menu_pref', '{$menu_conf}') ");
		
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('search_prefs', '{$search_prefs}') ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('notify_prefs', '{$notify_prefs}') ");
		
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}banner VALUES (0, 'e107', 'e107login', 'e107password', 'banner1.png', 'http://e107.org', 0, 0, 0, 0, 0, 0, '', 'campaign_one') ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}banner VALUES (0, 'e107', 'e107login', 'e107password', 'banner2.png', 'http://e107.org', 0, 0, 0, 0, 0, 0, '', 'campaign_one') ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}banner VALUES (0, 'e107', 'e107login', 'e107password', 'banner3.png', 'http://e107.org', 0, 0, 0, 0, 0, 0, '', 'campaign_one') ");
		
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (1, 'login_menu', 0, 0, '0', '', 'login_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (3, 'online_menu', 0, 0, '0', '', 'online_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (4, 'blogcalendar_menu', 0, 0, '0', '', 'blogcalendar_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (5, 'tree_menu', 0, 0, '0', '', 'tree_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (6, 'search_menu', 0, 0, '0', '', 'search_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (7, 'compliance_menu', 0, 0, '0', '', 'compliance_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (8, 'userlanguage_menu', 0, 0, '0', '', 'userlanguage_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (9, 'powered_by_menu', 1, 1, '0', '2-index.php', 'powered_by_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (10, 'counter_menu', 0, 0, '0', '', 'counter_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (11, 'usertheme_menu', 0, 0, '0', '', 'usertheme_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (12, 'banner_menu', 0, 0, '0', '', 'banner_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (13, 'online_extended_menu', 0, 0, '0', '', 'online_extended_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (14, 'clock_menu', 0, 0, '0', '', 'clock_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (15, 'sitebutton_menu', 0, 0, '0', '', 'sitebutton_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (16, 'comment_menu', 0, 0, '0', '', 'comment_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (17, 'lastseen_menu', 0, 0, '0', '', 'lastseen/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (18, 'other_news_menu', 0, 0, '0', '', 'other_news_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (19, 'other_news2_menu', 0, 0, '0', '', 'other_news_menu/')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (20, 'admin_menu', 0, 0, '0', '', 'admin_menu/')");
//		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (21, 'rss_menu', 5, 1, '0', '', 'rss_menu/')");
//		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (22, 'PCMag', 3, 1, '0', '', '1')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (21, 'e107_plugins', 2, 1, '0', '', '1')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (22, 'e107_themes', 3, 1, '0', '', '2')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}menus VALUES (23, 'e107_handbook', 4, 1, '0', '', '3')");

		
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}userclass_classes VALUES (1, 'PRIVATEMENU', '".LANINS_093."',".e_UC_ADMIN.")");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}userclass_classes VALUES (2, 'PRIVATEFORUM1', '".LANINS_094."',".e_UC_ADMIN.")");
//		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}plugin VALUES (0, '".LANINS_095."', '0.03', 'Integrity Check', 1, '') ");
		
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}generic VALUES (0, 'wmessage', {$datestamp}, 1, '', 0, '[img=style=float: left; margin-right: 20px]{e_IMAGE}splash.jpg[/img]".LANINS_WELCOME."')");
		
		// TODO - more and better custom menus
		//mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}page VALUES (1, '', '[img]{e_IMAGE}pcmag.png[/img] ', 0, 1145843485, 0, 0, '', '', '', 'PCMag')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}page VALUES (1, '".LANINS_103."', '[link=http://e107.org/plugins][img]{e_IMAGE}e107_plugins.png[/img][/link]', 0, {$datestamp}, 0, 0, '', '', '', 'e107_plugins')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}page VALUES (2, '".LANINS_111."', '[link=http://e107.org/themes][img]{e_IMAGE}e107_themes.png[/img][/link]', 0, {$datestamp}, 0, 0, '', '', '', 'e107_themes')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}page VALUES (3, '".LANINS_112."', '[link=http://e107.org/handbook][img]{e_IMAGE}e107_handbook.png[/img][/link]', 0, {$datestamp}, 0, 0, '', '', '', 'e107_handbook')");
		$this->logLine('Sample custom menu added');
		
		# Create the admin user
		$ip    = $_SERVER['REMOTE_ADDR'];
		$userp = "1, '{$this->previous_steps['admin']['display']}', '{$this->previous_steps['admin']['user']}', '', '".md5($this->previous_steps['admin']['password'])."', '', '{$this->previous_steps['admin']['email']}', '', '', '', 0, ".time().", 0, 0, 0, 0, 0, 0, '{$ip}', 0, '', '', '', 0, 1, '', '', '0', '', ".time().", ''";
		
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}user VALUES ({$userp})" );
		
		$this->logLine('User record added');
		
		if (mysql_close($link))	# $link should be specified due to PHP5.3 (http://bugs.php.net/bug.php?id=48754)
		{
			$this->logLine('Database manipulation complete');
		}
		else
		{
			$this->logLine('Error closing database: '.mysql_error());
			return 'Error closing database: '.mysql_error();
		}
		
		return false;
	}
	
	/**
	 * e107_config.php Config file generator
	 *
	 * @return	mixed	bool false on success (no errors) | string (error message) on error
	 */
	protected function _write_config()
	{
		$fp   = @fopen('e107_config.php', 'w');
		$data = 
"<?php
/**
 *  e107 Website Content Management System
 *  
 *  ©e107 Inc 2008-2012
 *  http://e107.org
 *  
 *  Released under the terms and conditions of the
 *  GNU General Public License (http://gnu.org).
 *  
 *  This file has been auto-generated during installation process
 *  
 *  e107_config.php
 */

\$mySQLserver         = '{$this->previous_steps['mysql']['server']}';
\$mySQLuser           = '{$this->previous_steps['mysql']['user']}';
\$mySQLpassword       = '{$this->previous_steps['mysql']['password']}';
\$mySQLdefaultdb      = '{$this->previous_steps['mysql']['db']}';
\$mySQLprefix         = '{$this->previous_steps['mysql']['prefix']}';
\$mySQLcharset        = '".($this->previous_steps['mysql']['db_utf8'] ? 'utf8' : '')."';	# \$mySQLcharset can only contain 'utf8' or ''

\$ADMIN_DIRECTORY     = '{$this->e107->e107_dirs['ADMIN_DIRECTORY']}';
\$FILES_DIRECTORY     = '{$this->e107->e107_dirs['FILES_DIRECTORY']}';
\$IMAGES_DIRECTORY    = '{$this->e107->e107_dirs['IMAGES_DIRECTORY']}';
\$THEMES_DIRECTORY    = '{$this->e107->e107_dirs['THEMES_DIRECTORY']}';
\$PLUGINS_DIRECTORY   = '{$this->e107->e107_dirs['PLUGINS_DIRECTORY']}';
\$HANDLERS_DIRECTORY  = '{$this->e107->e107_dirs['HANDLERS_DIRECTORY']}';
\$LANGUAGES_DIRECTORY = '{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}';
\$HELP_DIRECTORY      = '{$this->e107->e107_dirs['HELP_DIRECTORY']}';
\$DOWNLOADS_DIRECTORY = '{$this->e107->e107_dirs['DOWNLOADS_DIRECTORY']}';";
		
		if (!@fwrite($fp, $data))
		{
			@fclose ($fp);
			return nl2br(LANINS_070);
		}
		@fclose ($fp);
		return false;
	}
}

class SimpleTemplate
{
	public	$Tags      = array();
	public	$open_tag  = '{';
	public	$close_tag = '}';
	
	public function GetTag($TagName, $Default='')
	{
		return isset($this->Tags[$TagName]) ? $this->Tags[$TagName]['Data'] : $Default;
	}
	
	public function SetTag($TagName, $Data)
	{
		$this->Tags[$TagName] = array(
			'Tag'  => $TagName,
			'Data' => $Data
		);
		
		return $this;
	}
	
	public function RemoveTag($TagName)
	{
		if (isset($this->Tags[$TagName]))  unset($this->Tags[$TagName]);
		
		return $this;
	}
	
	public function ClearTags()
	{
		$this->Tags = array();
		
		return $this;
	}
	
	public function ParseTemplate($Template, $from_file=false)
	{
		$TemplateData = $from_file ? file_get_contents($Template) : $Template;
		
		foreach ($this->Tags as $Tag)  $TemplateData = str_replace($this->open_tag.$Tag['Tag'].$this->close_tag, $Tag['Data'], $TemplateData);
		
		return $TemplateData;
	}
}