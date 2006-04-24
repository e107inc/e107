<?php

/* Default Options and Paths for Installer */
$MySQLPrefix	     = 'e107_';

$ADMIN_DIRECTORY     = "e107_admin/";
$FILES_DIRECTORY     = "e107_files/";
$IMAGES_DIRECTORY    = "e107_images/";
$THEMES_DIRECTORY    = "e107_themes/";
$PLUGINS_DIRECTORY   = "e107_plugins/";
$HANDLERS_DIRECTORY  = "e107_handlers/";
$LANGUAGES_DIRECTORY = "e107_languages/";
$HELP_DIRECTORY      = "e107_docs/help/";
$DOWNLOADS_DIRECTORY = "e107_files/downloads/";

/* End configurable variables */

if(isset($_GET['object'])) {
	get_object($_GET['object']);
	die();
}

define("e107_INIT", TRUE);
error_reporting(E_ALL);

// setup some php options
ini_set('magic_quotes_runtime',     0);
ini_set('magic_quotes_sybase',      0);
ini_set('arg_separator.output',     '&amp;');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid',    0);

//  Ensure thet '.' is the first part of the include path
$inc_path = explode(PATH_SEPARATOR, ini_get('include_path'));
if($inc_path[0] != ".") {
	array_unshift($inc_path, ".");
	$inc_path = implode(PATH_SEPARATOR, $inc_path);
	ini_set("include_path", $inc_path);
}
unset($inc_path);

if(!function_exists("file_get_contents")) {
	die("e107 requires PHP 4.3 or greater to work correctly.");
}

if(!function_exists("mysql_connect")) {
	die("e107 requires PHP to be installed or compiled with the MySQL extension to work correctly, please see the MySQL manual for more information.");
}

if(!function_exists("print_a")) {
	function print_a($var) {
		return '<pre>'.htmlentities(print_r($var, true), null, "UTF-8").'</pre>';
	}
}

header("Content-type: text/html; charset=utf-8");

$installer_folder_name = 'e107_install';

include_once("./{$HANDLERS_DIRECTORY}e107_class.php");

$e107_paths = compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'DOWNLOADS_DIRECTORY');
$e107 = new e107($e107_paths, __FILE__);
unset($e107_paths);

$e107->e107_dirs['INSTALLER'] = "{$installer_folder_name}/";

$e_install = new e_install();
$e_forms = new e_forms();

$e_install->template->SetTag("installer_css_http", $_SERVER['PHP_SELF']."?object=stylesheet");
$e_install->template->SetTag("installer_folder_http", e_HTTP.$installer_folder_name."/");
$e_install->template->SetTag("files_dir_http", e_FILE_ABS);

if(!isset($_POST['stage'])) {
	$_POST['stage'] = 1;
}
$_POST['stage'] = intval($_POST['stage']);

switch ($_POST['stage']) {
	case 1:
		$e_install->stage_1();
		break;
	case 2:
		$e_install->stage_2();
		break;
	case 3:
		$e_install->stage_3();
		break;
	case 4:
		$e_install->stage_4();
		break;
	case 5:
		$e_install->stage_5();
		break;
	case 6:
		$e_install->stage_6();
		break;
	case 7:
		$e_install->stage_7();
		break;
	default:
		$e_install->raise_error("Install stage information from client makes no sense to me.");
}

if($_SERVER['QUERY_STRING'] == "debug"){
	$e_install->template->SetTag("debug_info", print_a($e_install));
} else {
	$e_install->template->SetTag("debug_info", (count($e_install->debug_info) ? print_a($e_install->debug_info)."Backtrace:<br />".print_a($e_install) : ""));
}

echo $e_install->template->ParseTemplate(template_data(), TEMPLATE_TYPE_DATA);



class e_install {

	var $required_php = "4.3";

	var $paths;
	var $template;
	var $debug_info;
	var $e107;
	var $previous_steps;
	var $stage;
	var $post_data;

	function e_install() {
		$this->template = new SimpleTemplate();
		while (@ob_end_clean());
		global $e107;
		$this->e107 = $e107;
		if(isset($_POST['previous_steps'])) {
			$this->previous_steps = unserialize(base64_decode($_POST['previous_steps']));
			unset($_POST['previous_steps']);
		} else {
			$this->previous_steps = array();
		}
		$this->post_data = $_POST;
	}

	function raise_error($details){
		$this->debug_info[] = array (
		'info' => array (
		'details' => $details,
		'backtrace' => debug_backtrace()
		)
		);
	}

	function stage_1(){
		global $e_forms;
		$this->stage = 1;
		$this->get_lan_file();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_003);
		$this->template->SetTag("stage_title", LANINS_004);
		$e_forms->start_form("language_select", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$e_forms->add_select_item("language", $this->get_languages(), "English");
		$this->finish_form();
		$e_forms->add_button("submit", LANINS_006);
		$this->template->SetTag("stage_content", "<div style='text-align: center;'><label for='language'>".LANINS_005."</label>\n<br /><br /><br />\n".$e_forms->return_form()."</div>");
	}

	function stage_2(){
		global $e_forms;
		$this->stage = 2;
		$this->get_lan_file();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_021);
		$this->template->SetTag("stage_title", LANINS_022);
		$page_info = nl2br(LANINS_023);
		$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$output = "
			<br /><br />
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			  <table cellspacing='0'>
			    <tr>
			      <td style='border-top: 1px solid #999;' class='row-border'><label for='server'>".LANINS_024."</label></td>
			      <td style='border-top: 1px solid #999;' class='row-border'><input class='tbox' type='text' id='server' name='server' size='40' value='localhost' maxlength='100' /></td>
				  <td style='width: 40%; border-top: 1px solid #999;' class='row-border'>".LANINS_030."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='name'>".LANINS_025."</label></td>
			      <td class='row-border'><input class='tbox' type='text' name='name' id='name' size='40' value='' maxlength='100' /></td>
				  <td class='row-border'>".LANINS_031."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='password'>".LANINS_026."</label></td>
			      <td class='row-border'><input class='tbox' type='password' name='password' size='40' id='password' value='' maxlength='100' /></td>
				  <td class='row-border'>".LANINS_032."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='db'>".LANINS_027."</label></td>
			      <td class='row-border'><input type='text' name='db' size='20' id='db' value='' maxlength='100' />
			      <label class='defaulttext'><input type='checkbox' name='createdb' value='1' />".LANINS_028."</label></td>
				  <td class='row-border'>".LANINS_033."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='prefix'>".LANINS_029."</label></td>
			      <td class='row-border'><input type='text' name='prefix' size='20' id='prefix' value='e107_'  maxlength='100' /></td>
				  <td class='row-border'>".LANINS_034."</td>
			    </tr>
			  </table>
			</div>
			<br /><br />\n";
		$e_forms->add_plain_html($output);
		$this->finish_form();
		$e_forms->add_button("submit", LANINS_035);
		$this->template->SetTag("stage_content", $page_info.$e_forms->return_form());
	}

	function stage_3(){
		global $e_forms;
		$this->stage = 3;
		$this->get_lan_file();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_036);
		$this->previous_steps['mysql']['server'] = $_POST['server'];
		$this->previous_steps['mysql']['user'] = $_POST['name'];
		$this->previous_steps['mysql']['password'] = $_POST['password'];
		$this->previous_steps['mysql']['db'] = $_POST['db'];
		$this->previous_steps['mysql']['createdb'] = $_POST['createdb'];
		$this->previous_steps['mysql']['prefix'] = $_POST['prefix'];
		if($this->previous_steps['mysql']['server'] == "" || $this->previous_steps['mysql']['user'] == "" | $this->previous_steps['mysql']['db'] == "") {
			$this->stage = 3;
			$this->template->SetTag("stage_num", LANINS_021);
			$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
			$head = LANINS_039."<br /><br />\n";
			$output = "
			<br /><br />
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			  <table cellspacing='0'>
			    <tr>
			      <td style='border-top: 1px solid #999;' class='row-border'><label for='server'>".LANINS_024."</label></td>
			      <td style='border-top: 1px solid #999;' class='row-border'><input class='tbox' type='text' id='server' name='server' size='40' value='{$this->previous_steps['mysql']['server']}' maxlength='100' /></td>
				  <td style='width: 40%; border-top: 1px solid #999;' class='row-border'>".LANINS_030."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='name'>".LANINS_025."</label></td>
			      <td class='row-border'><input class='tbox' type='text' name='name' id='name' size='40' value='{$this->previous_steps['mysql']['user']}' maxlength='100' /></td>
				  <td class='row-border'>".LANINS_031."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='password'>".LANINS_026."</label></td>
			      <td class='row-border'><input class='tbox' type='password' name='password' id='password' size='40' value='{$this->previous_steps['mysql']['password']}' maxlength='100' /></td>
				  <td class='row-border'>".LANINS_032."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='db'>".LANINS_027."</label></td>
			      <td class='row-border'><input type='text' name='db' id='db' size='20' value='{$this->previous_steps['mysql']['db']}' maxlength='100' />
			        <label class='defaulttext'><input type='checkbox' name='createdb'".($this->previous_steps['mysql']['createdb'] == 1 ? " checked='checked'" : "")." value='1'>".LANINS_028."</lebel></td>
				  <td class='row-border'>".LANINS_033."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='prefix'>".LANINS_029."</label></td>
			      <td class='row-border'><input type='text' name='prefix' id='prefix' size='20' value='{$this->previous_steps['mysql']['prefix']}'  maxlength='100' /></td>
				  <td class='row-border'>".LANINS_034."</td>
			    </tr>
			  </table>
			</div>
			<br /><br />\n";
			$e_forms->add_plain_html($output);
			$e_forms->add_button("submit", LANINS_035);
			$this->template->SetTag("stage_title", LANINS_040);
		} else {
			$success = true;
			$this->template->SetTag("stage_title", LANINS_037.($this->previous_steps['mysql']['createdb'] == 1 ? LANINS_038 : ""));
			if (!@mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password'])) {
				$success = false;
				$page_content = LANINS_041.nl2br("\n\n<b>".LANINS_083."\n</b><i>".mysql_error()."</i>");
			} else {
				$page_content = LANINS_042;
				if($this->previous_steps['mysql']['createdb'] == 1) {
					if (!mysql_query("CREATE DATABASE ".$this->previous_steps['mysql']['db'])) {
						$success = false;
						$page_content .= "<br /><br />".LANINS_043.nl2br("\n\n<b>".LANINS_083."\n</b><i>".mysql_error()."</i>");
					} else {
						$page_content .= "<br /><br />".LANINS_044;
					}
				}
			}
			if($success){
				$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
				$page_content .= "<br /><br />".LANINS_045."<br /><br />";
				$e_forms->add_button("submit", LANINS_035);
			}
			$head = $page_content;
		}
		$this->finish_form();
		$this->template->SetTag("stage_content", $head.$e_forms->return_form());
	}

	function stage_4(){
		global $e_forms;
		$this->stage = 4;
		$this->get_lan_file();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_007);
		$this->template->SetTag("stage_title", LANINS_008);
		$not_writable = $this->check_writable_perms();
		$version_fail = false;
		$perms_errors = "";
		if(count($not_writable)) {
			$perms_pass = false;
			foreach ($not_writable as $file) {
				$perms_errors .= (substr($file, -1) == "/" ? LANINS_010a : LANINS_010)."...<br /><b>{$file}</b><br />\n";
			}
			$perms_notes = LANINS_018;
		} else {
			$perms_pass = true;
			$perms_errors = "&nbsp;";
			$perms_notes = LANINS_017;
		}
		if(!function_exists("mysql_connect")) {
			$version_fail = true;
			$mysql_note = LANINS_011;
			$mysql_help = LANINS_012;
		} elseif (!@mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password'])) {
			$mysql_note = LANINS_011;
			$mysql_help = LANINS_013;
		} else {
			$mysql_note = mysql_get_server_info();
			$mysql_help = LANINS_017;
		}
		if(!function_exists("utf8_encode")) {
			$xml_installed = false;
		} else {
			$xml_installed = true;
		}

		$php_version = phpversion();
		if(version_compare($php_version, $this->required_php, ">=")) {
			$php_help = LANINS_017;
		} else {
			$php_help = LANINS_019;
		}
		$e_forms->start_form("versions", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		if(!$perms_pass) {
			$e_forms->add_button("retest_perms", LANINS_009);
			$this->stage = 3; // make the installer jump back a step
		} elseif ($perms_pass && !$version_fail && $xml_installed) {
			$e_forms->add_button("continue_install", LANINS_020);
		}
		$output = "
			<table style='width: 100%; margin-left: auto; margin-right: auto;'>
			  <tr>
			    <td style='width: 20%;'>".LANINS_014."</td>
			    <td style='width: 40%;'>{$perms_errors}</td>
			    <td style='width: 40%;'>{$perms_notes}</td>
			  </tr>
			  <tr>
			    <td>".LANINS_015."</td>
			    <td>{$php_version}</td>
			    <td>{$php_help}</td>
			  </tr>
			  <tr>
			    <td>".LANINS_016."</td>
			    <td>{$mysql_note}</td>
			    <td>{$mysql_help}</td>
			  </tr>
			  <tr>
			    <td>".LANINS_050."</td>
			    <td>".($xml_installed ? LANINS_051 : LANINS_052)."</td>
			    <td>".($xml_installed ? LANINS_017 : LANINS_053."<a href='http://php.net/manual/en/ref.xml.php' target='_blank'>php.net</a>".LANINS_054)."</td>
			  </tr>
			</table>\n<br /><br />\n\n";
		$this->finish_form();
		$this->template->SetTag("stage_content", $output.$e_forms->return_form());
	}



	function stage_5(){
		global $e_forms;
		$this->stage = 5;
		$this->get_lan_file();
		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_046);
		$this->template->SetTag("stage_title", LANINS_047);
		$e_forms->start_form("admin_info", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
		$output = "
			<div style='width: 100%; padding-left: auto; padding-right: auto;'>
			  <table cellspacing='0'>
			    <tr>
			      <td class='row-border'><label for='u_name'>".LANINS_072."</label></td>
			      <td class='row-border'><input class='tbox' type='text' name='u_name' id='u_name' size='30' value='".(isset($this->previous_steps['admin']['user']) ? $this->previous_steps['admin']['user'] : "")."' maxlength='60' /></td>
				  <td class='row-border'>".LANINS_073."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='d_name'>".LANINS_074."</label></td>
			      <td class='row-border'><input class='tbox' type='text' name='d_name' id='d_name' size='30' value='".(isset($this->previous_steps['admin']['display']) ? $this->previous_steps['admin']['display'] : "")."' maxlength='60' /></td>
				  <td class='row-border'>".LANINS_075."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='pass1'>".LANINS_076."</label></td>
			      <td class='row-border'><input type='password' name='pass1' size='30' id='pass1' value='' maxlength='60' /></td>
				  <td class='row-border'>".LANINS_077."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='pass2'>".LANINS_078."</label></td>
			      <td class='row-border'><input type='password' name='pass2' size='30' id='pass2' value='' maxlength='60' /></td>
				  <td class='row-border'>".LANINS_079."</td>
			    </tr>
			    <tr>
			      <td class='row-border'><label for='email'>".LANINS_080."</label></td>
			      <td class='row-border'><input type='text' name='email' size='30' id='email' value='".(isset($this->previous_steps['admin']['email']) ? $this->previous_steps['admin']['email'] : LANINS_082)."' maxlength='100' /></td>
				  <td class='row-border'>".LANINS_081."</td>
			    </tr>
			  </table>
			</div>
			<br /><br />\n";
		$e_forms->add_plain_html($output);
		$this->finish_form();
		$e_forms->add_button("submit", LANINS_035);
		$this->template->SetTag("stage_content", $e_forms->return_form());
	}

	function stage_6(){
		global $e_forms;
		$this->get_lan_file();
		$this->stage = 6;

		$_POST['u_name'] = str_replace(array("'", '"'), "", $_POST['u_name']);
		$_POST['d_name'] = str_replace(array("'", '"'), "", $_POST['d_name']);

		$this->previous_steps['admin']['user'] = $_POST['u_name'];
		if ($_POST['d_name'] == "") {
			$this->previous_steps['admin']['display'] = $_POST['u_name'];
		} else {
			$this->previous_steps['admin']['display'] = $_POST['d_name'];
		}
		$this->previous_steps['admin']['email'] = $_POST['email'];
		$this->previous_steps['admin']['password'] = $_POST['pass1'];

		if($_POST['pass1'] != $_POST['pass2']) {
			$this->template->SetTag("installation_heading", LANINS_001);
			$this->template->SetTag("stage_num", LANINS_046);
			$this->template->SetTag("stage_pre", LANINS_002);
			$this->template->SetTag("stage_title", LANINS_047);
			$e_forms->start_form("admin_info", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
			$page = LANINS_049."<br />".($_SERVER['QUERY_STRING'] == "debug" ? print_a($_POST, true) : "")."<br />";

			$this->finish_form(5);
			$e_forms->add_button("submit", LANINS_048);
		} else {

			$this->template->SetTag("installation_heading", LANINS_001);
			$this->template->SetTag("stage_pre", LANINS_002);
			$this->template->SetTag("stage_num", LANINS_056);
			$this->template->SetTag("stage_title", LANINS_055);

			$e_forms->start_form("confirmation", $_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING'] == "debug" ? "?debug" : ""));
			$page = nl2br(LANINS_057);
			$this->finish_form();
			$e_forms->add_button("submit", LANINS_035);
		}

		$this->template->SetTag("stage_content", $page.$e_forms->return_form());
	}

	function stage_7(){
		global $e_forms;
		$this->get_lan_file();

		$this->stage = 7;

		$this->template->SetTag("installation_heading", LANINS_001);
		$this->template->SetTag("stage_pre", LANINS_002);
		$this->template->SetTag("stage_num", LANINS_058);
		$this->template->SetTag("stage_title", LANINS_071);

		$config_file = "<?php

/*
+----------------------------------------------------+
|   e107 website system
|   e107_config.php
|
|   ©Steve Dunstan 2001-2002
|   http://e107.org
|   jalist@e107.org
|
|   Released under the terms and conditions of the
|   GNU General Public License (http://gnu.org).
+----------------------------------------------------+
This file has been generated by the installation script.
*/

\$mySQLserver    = '{$this->previous_steps['mysql']['server']}';
\$mySQLuser      = '{$this->previous_steps['mysql']['user']}';
\$mySQLpassword  = '{$this->previous_steps['mysql']['password']}';
\$mySQLdefaultdb = '{$this->previous_steps['mysql']['db']}';
\$mySQLprefix    = '{$this->previous_steps['mysql']['prefix']}';

\$ADMIN_DIRECTORY     = \"{$this->e107->e107_dirs['ADMIN_DIRECTORY']}\";
\$FILES_DIRECTORY     = \"{$this->e107->e107_dirs['FILES_DIRECTORY']}\";
\$IMAGES_DIRECTORY    = \"{$this->e107->e107_dirs['IMAGES_DIRECTORY']}\";
\$THEMES_DIRECTORY    = \"{$this->e107->e107_dirs['THEMES_DIRECTORY']}\";
\$PLUGINS_DIRECTORY   = \"{$this->e107->e107_dirs['PLUGINS_DIRECTORY']}\";
\$HANDLERS_DIRECTORY  = \"{$this->e107->e107_dirs['HANDLERS_DIRECTORY']}\";
\$LANGUAGES_DIRECTORY = \"{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}\";
\$HELP_DIRECTORY      = \"{$this->e107->e107_dirs['HELP_DIRECTORY']}\";
\$DOWNLOADS_DIRECTORY = \"{$this->e107->e107_dirs['DOWNLOADS_DIRECTORY']}\";

?>";

		$config_result = $this->write_config($config_file);
		$e_forms->start_form("confirmation", "index.php");
		if ($config_result) {
			$page = $config_result."<br />";
		} else {
			$errors = $this->create_tables();
			if ($errors == true) {
				$page = $errors."<br />";
			} else {
				$page = nl2br(LANINS_069)."<br />";
				$e_forms->add_button("submit", LANINS_035);
			}
		}
		$this->finish_form();
		$this->template->SetTag("stage_content", $page.$e_forms->return_form());
	}

	function get_lan_file(){
		if(!isset($this->previous_steps['language'])) {
			$this->previous_steps['language'] = "English";
		}
		$this->lan_file = "{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}{$this->previous_steps['language']}/lan_installer.php";
		if(is_readable($this->lan_file)){
			include($this->lan_file);
		} elseif(is_readable("{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}English/lan_installer.php")) {
			include("{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}English/lan_installer.php");
		} else {
			$this->raise_error("Fatal: Could not get valid language file for installation.");
		}
	}

	function get_languages() {
		$handle = opendir("{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}");
		while ($file = readdir($handle)) {
			if ($file != "." && $file != ".." && $file != "/" && $file != "CVS") {
				if(file_exists("./{$this->e107->e107_dirs['LANGUAGES_DIRECTORY']}{$file}/lan_installer.php")){
					$lanlist[] = $file;
				}
			}
		}
		closedir($handle);
		return $lanlist;
	}

	function finish_form($force_stage = false) {
		global $e_forms;
		if($this->previous_steps) {
			$e_forms->add_hidden_data("previous_steps", base64_encode(serialize($this->previous_steps)));
		}
		$e_forms->add_hidden_data("stage", ($force_stage ? $force_stage : ($this->stage + 1)));
	}

	function check_writable_perms(){
		$bad_files = array();
		$data = 'e107_config.php|{$FILES_DIRECTORY}cache/|{$FILES_DIRECTORY}public/|{$FILES_DIRECTORY}public/avatars/|{$PLUGINS_DIRECTORY}|{$THEMES_DIRECTORY}';
		foreach ($this->e107->e107_dirs as $dir_name => $value) {
			$find[] = "{\${$dir_name}}";
			$replace[] = "./$value";
		}
		$data = str_replace($find, $replace, $data);
		$files = explode("|", trim($data));
		foreach ($files as $file) {
			if(!is_writable($file)) {
				$bad_files[] = str_replace("./", "", $file);
			}
		}
		return $bad_files;
	}

	function create_tables() {

		$link = mysql_connect($this->previous_steps['mysql']['server'], $this->previous_steps['mysql']['user'], $this->previous_steps['mysql']['password']);
		if(!$link) {
			return nl2br(LANINS_084."\n\n<b>".LANINS_083."\n</b><i>".mysql_error($link)."</i>");
		}

		$db_selected = mysql_select_db($this->previous_steps['mysql']['db'], $link);
		if(!$db_selected) {
			return nl2br(LANINS_085." '{$this->previous_steps['mysql']['db']}'\n\n<b>".LANINS_083."\n</b><i>".mysql_error($link)."</i>");
		}

		$filename = "{$this->e107->e107_dirs['ADMIN_DIRECTORY']}sql/core_sql.php";
		$fd = fopen ($filename, "r");
		$sql_data = fread($fd, filesize($filename));
		fclose ($fd);

		if (!$sql_data) {
			return nl2br(LANINS_060)."<br /><br />";
		}

		preg_match_all("/create(.*?)myisam;/si", $sql_data, $result );

		foreach ($result[0] as $sql_table) {
			preg_match("/CREATE TABLE\s(.*?)\s\(/si", $sql_table, $match);
			$tablename = $match[1];
			preg_match_all("/create(.*?)myisam;/si", $sql_data, $result );
			$sql_table = preg_replace("/create table\s/si", "CREATE TABLE {$this->previous_steps['mysql']['prefix']}", $sql_table);
			if (!mysql_query($sql_table, $link)) {
				return nl2br(LANINS_061."\n\n<b>".LANINS_083."\n</b><i>".mysql_error($link)."</i>");
			}
		}

		$datestamp = time();

		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}news VALUES (0, '".LANINS_063."', '".LANINS_062."', '', '{$datestamp}', '0', '1', 1, 0, 0, 0, 0, '0', '', 'welcome.png', 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}news_category VALUES (0, 'Misc', 'icon26.png') ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, 'Home', 'index.php', '', '', 1, 1, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, 'Downloads', 'download.php', '', '', 1, 2, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, 'Members', 'user.php', '', '', 1, 3, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, 'Submit News', 'submitnews.php', '', '', 1, 4, 0, 0, 0) ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}links VALUES (0, 'Contact Us', 'contact.php', '', '', 1, 5, 0, 0, 0) ");  

		$udirs = "admin/|plugins/|temp";
		$e_SELF = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		$e_HTTP = preg_replace("#".$udirs."#i", "", substr($e_SELF, 0, strrpos($e_SELF, "/"))."/");

		$pref_language = isset($_POST['installlanguage']) ? $_POST['installlanguage'] : "English";

		if (file_exists($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$pref_language."/lan_prefs.php")) {
			include_once($this->e107->e107_dirs['LANGUAGES_DIRECTORY'].$pref_language."/lan_prefs.php");
		} else {
			include_once($this->e107->e107_dirs['LANGUAGES_DIRECTORY']."English/lan_prefs.php");
		}

		$site_admin_user = $this->previous_steps['admin']['display'];
		$site_admin_email = $this->previous_steps['admin']['email'];

		require_once("{$this->e107->e107_dirs['FILES_DIRECTORY']}def_e107_prefs.php");

		include_once("{$this->e107->e107_dirs['HANDLERS_DIRECTORY']}arraystorage_class.php");

		$tmp = ArrayData::WriteArray($pref);

		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('SitePrefs', '{$tmp}')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('SitePrefs_Backup', '{$tmp}')");

		$emote = 'a:60:{i:0;a:1:{s:2:"&|";s:7:"cry.png";}i:1;a:1:{s:3:"&-|";s:7:"cry.png";}i:2;a:1:{s:3:"&o|";s:7:"cry.png";}i:3;a:1:{s:3:":((";s:7:"cry.png";}i:4;a:1:{s:3:"~:(";s:7:"mad.png";}i:5;a:1:{s:4:"~:o(";s:7:"mad.png";}i:6;a:1:{s:4:"~:-(";s:7:"mad.png";}i:7;a:1:{s:2:":)";s:9:"smile.png";}i:8;a:1:{s:3:":o)";s:9:"smile.png";}i:9;a:1:{s:3:":-)";s:9:"smile.png";}i:10;a:1:{s:2:":(";s:9:"frown.png";}i:11;a:1:{s:3:":o(";s:9:"frown.png";}i:12;a:1:{s:3:":-(";s:9:"frown.png";}i:13;a:1:{s:2:":D";s:8:"grin.png";}i:14;a:1:{s:3:":oD";s:8:"grin.png";}i:15;a:1:{s:3:":-D";s:8:"grin.png";}i:16;a:1:{s:2:":?";s:12:"confused.png";}i:17;a:1:{s:3:":o?";s:12:"confused.png";}i:18;a:1:{s:3:":-?";s:12:"confused.png";}i:19;a:1:{s:3:"%-6";s:11:"special.png";}i:20;a:1:{s:2:"x)";s:8:"dead.png";}i:21;a:1:{s:3:"xo)";s:8:"dead.png";}i:22;a:1:{s:3:"x-)";s:8:"dead.png";}i:23;a:1:{s:2:"x(";s:8:"dead.png";}i:24;a:1:{s:3:"xo(";s:8:"dead.png";}i:25;a:1:{s:3:"x-(";s:8:"dead.png";}i:26;a:1:{s:2:":@";s:7:"gah.png";}i:27;a:1:{s:3:":o@";s:7:"gah.png";}i:28;a:1:{s:3:":-@";s:7:"gah.png";}i:29;a:1:{s:2:":!";s:8:"idea.png";}i:30;a:1:{s:3:":o!";s:8:"idea.png";}i:31;a:1:{s:3:":-!";s:8:"idea.png";}i:32;a:1:{s:2:":|";s:11:"neutral.png";}i:33;a:1:{s:3:":o|";s:11:"neutral.png";}i:34;a:1:{s:3:":-|";s:11:"neutral.png";}i:35;a:1:{s:2:"?!";s:12:"question.png";}i:36;a:1:{s:2:"B)";s:12:"rolleyes.png";}i:37;a:1:{s:3:"Bo)";s:12:"rolleyes.png";}i:38;a:1:{s:3:"B-)";s:12:"rolleyes.png";}i:39;a:1:{s:2:"8)";s:10:"shades.png";}i:40;a:1:{s:3:"8o)";s:10:"shades.png";}i:41;a:1:{s:3:"8-)";s:10:"shades.png";}i:42;a:1:{s:2:":O";s:12:"suprised.png";}i:43;a:1:{s:3:":oO";s:12:"suprised.png";}i:44;a:1:{s:3:":-O";s:12:"suprised.png";}i:45;a:1:{s:2:":p";s:10:"tongue.png";}i:46;a:1:{s:3:":op";s:10:"tongue.png";}i:47;a:1:{s:3:":-p";s:10:"tongue.png";}i:48;a:1:{s:2:":P";s:10:"tongue.png";}i:49;a:1:{s:3:":oP";s:10:"tongue.png";}i:50;a:1:{s:3:":-P";s:10:"tongue.png";}i:51;a:1:{s:2:";)";s:8:"wink.png";}i:52;a:1:{s:3:";o)";s:8:"wink.png";}i:53;a:1:{s:3:";-)";s:8:"wink.png";}i:54;a:1:{s:4:"!ill";s:7:"ill.png";}i:55;a:1:{s:7:"!amazed";s:10:"amazed.png";}i:56;a:1:{s:4:"!cry";s:7:"cry.png";}i:57;a:1:{s:6:"!dodge";s:9:"dodge.png";}i:58;a:1:{s:6:"!alien";s:9:"alien.png";}i:59;a:1:{s:6:"!heart";s:9:"heart.png";}}';
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('emote', '{$emote}') ");

		$menu_conf = 'a:23:{s:15:"comment_caption";s:15:"Latest Comments";s:15:"comment_display";s:2:"10";s:18:"comment_characters";s:2:"50";s:15:"comment_postfix";s:12:"[ more ... ]";s:13:"comment_title";i:0;s:15:"article_caption";s:8:"Articles";s:16:"articles_display";s:2:"10";s:17:"articles_mainlink";s:23:"Articles Front Page ...";s:21:"newforumposts_caption";s:18:"Latest Forum Posts";s:21:"newforumposts_display";s:2:"10";s:19:"forum_no_characters";s:2:"20";s:13:"forum_postfix";s:10:"[more ...]";s:11:"update_menu";s:20:"Update menu Settings";s:17:"forum_show_topics";s:1:"1";s:24:"newforumposts_characters";s:2:"50";s:21:"newforumposts_postfix";s:10:"[more ...]";s:19:"newforumposts_title";i:0;s:13:"clock_caption";s:11:"Date / Time";s:15:"reviews_caption";s:7:"Reviews";s:15:"reviews_display";s:2:"10";s:15:"reviews_parents";s:1:"1";s:16:"reviews_mainlink";s:21:"Review Front Page ...";s:16:"articles_parents";s:1:"1";}';
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('menu_pref', '{$menu_conf}') ");

		preg_match("/^(.*?)($|-)/", mysql_get_server_info(), $mysql_version);
		if (version_compare($mysql_version[1], '4.0.1', '<')) {
			$search_prefs = 'a:12:{s:11:\"user_select\";s:1:\"1\";s:9:\"time_secs\";s:2:\"60\";s:13:\"time_restrict\";s:1:\"0\";s:8:\"selector\";s:1:\"2\";s:9:\"relevance\";s:1:\"0\";s:13:\"plug_handlers\";N;s:10:\"mysql_sort\";b:0;s:11:\"multisearch\";s:1:\"1\";s:6:\"google\";s:1:\"0\";s:13:\"core_handlers\";a:5:{s:4:\"news\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"1\";}s:8:\"comments\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"2\";}s:5:\"users\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"3\";}s:9:\"downloads\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"4\";}s:5:\"pages\";a:6:{s:5:\"class\";s:1:\"0\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"order\";s:1:\"5\";}}s:17:\"comments_handlers\";a:2:{s:4:\"news\";a:3:{s:2:\"id\";i:0;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}s:8:\"download\";a:3:{s:2:\"id\";i:2;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}}s:9:\"php_limit\";s:0:\"\";}';
		} else {
			$search_prefs = 'a:12:{s:11:\"user_select\";s:1:\"1\";s:9:\"time_secs\";s:2:\"60\";s:13:\"time_restrict\";s:1:\"0\";s:8:\"selector\";s:1:\"2\";s:9:\"relevance\";s:1:\"0\";s:13:\"plug_handlers\";N;s:10:\"mysql_sort\";b:1;s:11:\"multisearch\";s:1:\"1\";s:6:\"google\";s:1:\"0\";s:13:\"core_handlers\";a:5:{s:4:\"news\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"1\";}s:8:\"comments\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"2\";}s:5:\"users\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"3\";}s:9:\"downloads\";a:6:{s:5:\"class\";s:1:\"0\";s:9:\"pre_title\";s:1:\"1\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:5:\"order\";s:1:\"4\";}s:5:\"pages\";a:6:{s:5:\"class\";s:1:\"0\";s:5:\"chars\";s:3:\"150\";s:7:\"results\";s:2:\"10\";s:9:\"pre_title\";s:1:\"0\";s:13:\"pre_title_alt\";s:0:\"\";s:5:\"order\";s:1:\"5\";}}s:17:\"comments_handlers\";a:2:{s:4:\"news\";a:3:{s:2:\"id\";i:0;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}s:8:\"download\";a:3:{s:2:\"id\";i:2;s:3:\"dir\";s:4:\"core\";s:5:\"class\";s:1:\"0\";}}s:9:\"php_limit\";s:0:\"\";}';
		}
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('search_prefs', '{$search_prefs}') ");

		$notify_prefs = mysql_escape_string("array ('event' => array ('usersup' => array ('type' => 'off', 'class' => '254', 'email' => '',),'userveri' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'login' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'logout' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'flood' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'subnews' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'newspost' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'newsupd' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), 'newsdel' => array ( 'type' => 'off', 'class' => '254', 'email' => '', ), ), )");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}core VALUES ('notify_prefs', '{$notify_prefs}') ");

		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}banner VALUES (0, 'e107', 'e107login', 'e107password', 'banner1.png', 'http://e107.org', 0, 0, 0, 0, 0, 0, '', 'campaign_one') ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}banner VALUES (0, 'e107', 'e107login', 'e107password', 'banner2.png', 'http://e107.org', 0, 0, 0, 0, 0, 0, '', 'campaign_one') ");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}banner VALUES (0, 'e107', 'e107login', 'e107password', 'banner3.png', 'http://e107.org', 0, 0, 0, 0, 0, 0, '', 'campaign_one') ");

		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (1, 'login_menu', 1, 1, '0', '', 'login_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (3, 'online_menu', 0, 0, '0', '', 'online_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (4, 'blogcalendar_menu', 0, 0, '0', '', 'blogcalendar_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (5, 'tree_menu', 0, 0, '0', '', 'tree_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (6, 'search_menu', 0, 0, '0', '', 'search_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (7, 'compliance_menu', 0, 0, '0', '', 'compliance_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (8, 'userlanguage_menu', 0, 0, '0', '', 'userlanguage_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (9, 'powered_by_menu', 2, 2, '0', '', 'powered_by_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (10, 'counter_menu', 0, 0, '0', '', 'counter_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (11, 'usertheme_menu', 0, 0, '0', '', 'usertheme_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (12, 'banner_menu', 0, 0, '0', '', 'banner_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (13, 'online_extended_menu', 2, 1, '0', '', 'online_extended_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (14, 'clock_menu', 0, 0, '0', '', 'clock_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (15, 'sitebutton_menu', 0, 0, '0', '', 'sitebutton_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (16, 'comment_menu', 0, 0, '0', '', 'comment_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (17, 'lastseen_menu', 0, 0, '0', '', 'lastseen/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (18, 'other_news_menu', 0, 0, '0', '', 'other_news_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (19, 'other_news2_menu', 0, 0, '0', '', 'other_news_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (20, 'admin_menu', 0, 0, '0', '', 'admin_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (21, 'rss_menu', 5, 1, '0', '', 'rss_menu/')");
		mysql_query("INSERT INTO `{$this->previous_steps['mysql']['prefix']}menus` VALUES (22, 'PCMag', 3, 1, '0', '', '1')");

		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}userclass_classes VALUES (1, 'PRIVATEMENU', 'Grants access to private menu items')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}userclass_classes VALUES (2, 'PRIVATEFORUM1', 'Example private forum class')");
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}plugin VALUES (0, 'Integrity Check', '0.03', 'integrity_check', 1) ");

		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}generic VALUES (0, 'wmessage', 1145848343, 1, '', 0, '[center]<img src=&#039;{e_IMAGE}splash.jpg&#039; style=&#039;width: 412px; height: 275px&#039; alt=&#039;&#039; />[/center]')");

		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}page VALUES (1, '', '[img]{e_IMAGE}pcmag.png[/img] ', 0, 1145843485, 0, 0, '', '', '', 'PCMag')");

		// Create the admin user
		$ip = $_SERVER['REMOTE_ADDR'];
		$userp = "1, '{$this->previous_steps['admin']['display']}', '{$this->previous_steps['admin']['user']}', '', '".md5($this->previous_steps['admin']['password'])."', '', '{$this->previous_steps['admin']['email']}', '', '', '', 0, ".time().", 0, 0, 0, 0, 0, 0, '{$ip}', 0, '', '', '', 0, 1, '', '', '0', '', ".time().", ''";
		mysql_query("INSERT INTO {$this->previous_steps['mysql']['prefix']}user VALUES ({$userp})" );
		mysql_close();

		return false;
	}

	function write_config($data) {
		$fp = @fopen("e107_config.php", "w");
		if (!@fwrite($fp, $data)) {
			@fclose ($fd);
			return nl2br(LANINS_070);
		}
		@fclose ($fd);
		return false;
	}
}

class e_forms {

	var $form;
	var $opened;

	function start_form($id, $action, $method = "post" ) {
		$this->form = "\n<form method='{$method}' id='{$id}' action='{$action}'>\n";
		$this->opened = true;
	}

	function add_select_item($id, $labels, $selected) {
		$this->form .= "
		<select name='{$id}' id='{$id}'>\n";
		foreach ($labels as $label) {
			$this->form .= "<option".($label == $selected ? " selected='selected'" : "").">{$label}</option>\n";
		}
		$this->form .= "</select>\n";
	}

	function add_button($id, $title, $align = "right", $type = "submit") {
		$this->form .= "<div style='text-align: {$align}; z-index: 10;'><input type='{$type}' id='{$id}' value='{$title}' /></div>\n";
	}

	function add_hidden_data($id, $data) {
		$this->form .= "<input type='hidden' name='{$id}' value='{$data}' />\n";
	}

	function add_plain_html($html_data) {
		$this->form .= $html_data;
	}

	function return_form() {
		if($this->opened == true) {
			$this->form .= "</form>\n";
		}
		$this->opened = false;
		return $this->form;
	}
}

class SimpleTemplate {

	var $Tags = array();
	var $open_tag = "{";
	var $close_tag = "}";

	function SimpleTemplate() {
		define("TEMPLATE_TYPE_FILE", 0);
		define("TEMPLATE_TYPE_DATA", 1);
	}

	function SetTag($TagName, $Data) {
		$this->Tags[$TagName] = array(	'Tag'  => $TagName,
		'Data' => $Data
		);
	}

	function RemoveTag($TagName) {
		unset($this->Tags[$TagName]);
	}

	function ClearTags() {
		$this->Tags = array();
	}

	function ParseTemplate($Template, $template_type = TEMPLATE_TYPE_FILE) {
		if($template_type == TEMPLATE_TYPE_DATA) {
			$TemplateData = $Template;
		} else {
			$TemplateData = file_get_contents($Template);
		}
		foreach ($this->Tags as $Tag) {
			$TemplateData = str_replace($this->open_tag.$Tag['Tag'].$this->close_tag, $Tag['Data'], $TemplateData);
		}
		return $TemplateData;
	}
}

function template_data() {
	$data = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<title>{installation_heading}</title>
<meta http-equiv=\"Content-type\" content=\"text/html; charset=utf-8\" />
<meta http-equiv=\"content-style-type\" content=\"text/css\" />
<link rel=\"stylesheet\" href=\"{installer_css_http}\" type=\"text/css\" />
</head>
<body>
<!-- Installer theme is a ripped version of 'Leaf' by Que, based on the nucleus cms theme by Ivan Fong aka Stanch. -->
<div id=\"header\">
  <h1>{installation_heading}</h1>
</div>
<div id=\"wrapper\">
  <div id=\"container\">
    <div id=\"content\">
      <div class=\"contentbody\">
        <h3>{stage_pre}{stage_num} - {stage_title}</h3>
		<br />
        <p>{stage_content}</p>
		{debug_info}
      </div>
    </div>
  </div>
  <div class=\"clearing\">&nbsp;</div>
</div>
<div id=\"footer\">&nbsp;</div>

</body>
</html>";
	return $data;
}

function get_object($name) {
	switch ($name) {
		case "stylesheet";
		header("Content-type: text/css");
		echo "#container{
	float:left;
	margin-right : -230px;
	width : 670px;
}
#content{
margin: 15px 0px 0px -15px;
}
#footer{
	background : url({$_SERVER['PHP_SELF']}?object=01_footer.jpg) top no-repeat;
	margin : auto;
	padding : 20px 0 0 0;
	width : 700px;
}
#header h1{
	font-size : 20px;
	left : 18px;
	line-height : 20px;
	margin : 0;
	position : absolute;
	top : 17px;
}
#header{
	background : url({$_SERVER['PHP_SELF']}?object=01_header01.jpg) no-repeat;
	height : 151px;
	margin : auto;
	position : relative;
	width : 700px;
}
#wrapper{
	background : white url({$_SERVER['PHP_SELF']}?object=01_bodybg.jpg) repeat-y;
	margin : auto;
	text-align:left;
	width : 700px;
}
.clearing{
	clear : both;
	height : 0;
}
.contentbody{
	margin : 0px;
	padding : 0 5px 20px 43px;
}
a:hover, a:active{
	color : black;
}
a:link, a:visited{
	color : #23598C;
	text-decoration : none;
}
h1 {
	color : #23598C;
}
body{
	background : #123454 url(images/01_bg.gif) top repeat-x;
	color : #4C4C4C;
	font-family : Trebuchet MS, \"Lucida Sans Unicode\", Arial, Lucida Sans, Tahoma, Sans-Serif;
	font-size : 13px;
	padding : 20px 0 20px 0;
	text-align : center;
}
h3{
	color : #3E565F;
	font-size : 16px;
	margin : 0 0 8px 0;
}
img{
	border : none;
}

.logoimage {
	padding-left: 600px;
	padding-top: 65px;
}

td {
	vertical-align: top;
}

.row-border {
	border-bottom: 1px solid #999;
	padding: 6px 3px 6px 3px;
}";
		break;

		/* The following data is base64 encoded to preserve binary state in ASCII file */

		case "01_footer.jpg":
			header("Content-type: image/jpeg");
			echo trim(base64_decode("
/9j/4AAQSkZJRgABAgEASABIAAD/4QPgRXhpZgAATU0AKgAAAAgABwESAAMAAAABAAEAAAEaAAUA
AAABAAAAYgEbAAUAAAABAAAAagEoAAMAAAABAAIAAAExAAIAAAAUAAAAcgEyAAIAAAAUAAAAhodp
AAQAAAABAAAAnAAAAMgAAABIAAAAAQAAAEgAAAABQWRvYmUgUGhvdG9zaG9wIDcuMAAyMDA1OjAy
OjI2IDA0OjE2OjUyAAAAAAOgAQADAAAAAf//AACgAgAEAAAAAQAAArygAwAEAAAAAQAAAA0AAAAA
AAAABgEDAAMAAAABAAYAAAEaAAUAAAABAAABFgEbAAUAAAABAAABHgEoAAMAAAABAAIAAAIBAAQA
AAABAAABJgICAAQAAAABAAACsgAAAAAAAABIAAAAAQAAAEgAAAAB/9j/4AAQSkZJRgABAgEASABI
AAD/7QAMQWRvYmVfQ00AAv/uAA5BZG9iZQBkgAAAAAH/2wCEAAwICAgJCAwJCQwRCwoLERUPDAwP
FRgTExUTExgRDAwMDAwMEQwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwBDQsLDQ4NEA4OEBQO
Dg4UFA4ODg4UEQwMDAwMEREMDAwMDAwRDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDP/AABEI
AAIAgAMBIgACEQEDEQH/3QAEAAj/xAE/AAABBQEBAQEBAQAAAAAAAAADAAECBAUGBwgJCgsBAAEF
AQEBAQEBAAAAAAAAAAEAAgMEBQYHCAkKCxAAAQQBAwIEAgUHBggFAwwzAQACEQMEIRIxBUFRYRMi
cYEyBhSRobFCIyQVUsFiMzRygtFDByWSU/Dh8WNzNRaisoMmRJNUZEXCo3Q2F9JV4mXys4TD03Xj
80YnlKSFtJXE1OT0pbXF1eX1VmZ2hpamtsbW5vY3R1dnd4eXp7fH1+f3EQACAgECBAQDBAUGBwcG
BTUBAAIRAyExEgRBUWFxIhMFMoGRFKGxQiPBUtHwMyRi4XKCkkNTFWNzNPElBhaisoMHJjXC0kST
VKMXZEVVNnRl4vKzhMPTdePzRpSkhbSVxNTk9KW1xdXl9VZmdoaWprbG1ub2JzdHV2d3h5ent8f/
2gAMAwEAAhEDEQA/ANro3/I+D/y9/R6v5v6H0W/zf/B/6P8A4NXP/rhXgiSkO5WDZ97/APrhS/8A
rhXgiSSn3v8A+uFL/wCuFeCJJKfe/wD64Uv/AK4V4Ikkp97/APrhS/8ArhXgiSSn3v8A+uFL/wCu
FeCJJKfe/wD64Uv/AK4V4Ikkp97/APrhQM7+g5P/AIoP5qzn+q5eFpJBRf/Z/+0ImFBob3Rvc2hv
cCAzLjAAOEJJTQQlAAAAAAAQAAAAAAAAAAAAAAAAAAAAADhCSU0D7QAAAAAAEABIAAAAAQABAEgA
AAABAAE4QklNBCYAAAAAAA4AAAAAAAAAAAAAP4AAADhCSU0EDQAAAAAABAAAAHg4QklNBBkAAAAA
AAQAAAAeOEJJTQPzAAAAAAAJAAAAAAAAAAABADhCSU0ECgAAAAAAAQAAOEJJTScQAAAAAAAKAAEA
AAAAAAAAAThCSU0D9QAAAAAASAAvZmYAAQBsZmYABgAAAAAAAQAvZmYAAQChmZoABgAAAAAAAQAy
AAAAAQBaAAAABgAAAAAAAQA1AAAAAQAtAAAABgAAAAAAAThCSU0D+AAAAAAAcAAA////////////
/////////////////wPoAAAAAP////////////////////////////8D6AAAAAD/////////////
////////////////A+gAAAAA/////////////////////////////wPoAAA4QklNBAAAAAAAAAIA
ADhCSU0EAgAAAAAAAgAAOEJJTQQIAAAAAAAQAAAAAQAAAkAAAAJAAAAAADhCSU0EHgAAAAAABAAA
AAA4QklNBBoAAAAAA0kAAAAGAAAAAAAAAAAAAAANAAACvAAAAAoAVQBuAHQAaQB0AGwAZQBkAC0A
NAAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAACvAAAAA0AAAAAAAAAAAAAAAAAAAAA
AQAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAQAAAAAAAG51bGwAAAACAAAABmJvdW5kc09iamMAAAAB
AAAAAAAAUmN0MQAAAAQAAAAAVG9wIGxvbmcAAAAAAAAAAExlZnRsb25nAAAAAAAAAABCdG9tbG9u
ZwAAAA0AAAAAUmdodGxvbmcAAAK8AAAABnNsaWNlc1ZsTHMAAAABT2JqYwAAAAEAAAAAAAVzbGlj
ZQAAABIAAAAHc2xpY2VJRGxvbmcAAAAAAAAAB2dyb3VwSURsb25nAAAAAAAAAAZvcmlnaW5lbnVt
AAAADEVTbGljZU9yaWdpbgAAAA1hdXRvR2VuZXJhdGVkAAAAAFR5cGVlbnVtAAAACkVTbGljZVR5
cGUAAAAASW1nIAAAAAZib3VuZHNPYmpjAAAAAQAAAAAAAFJjdDEAAAAEAAAAAFRvcCBsb25nAAAA
AAAAAABMZWZ0bG9uZwAAAAAAAAAAQnRvbWxvbmcAAAANAAAAAFJnaHRsb25nAAACvAAAAAN1cmxU
RVhUAAAAAQAAAAAAAG51bGxURVhUAAAAAQAAAAAAAE1zZ2VURVhUAAAAAQAAAAAABmFsdFRhZ1RF
WFQAAAABAAAAAAAOY2VsbFRleHRJc0hUTUxib29sAQAAAAhjZWxsVGV4dFRFWFQAAAABAAAAAAAJ
aG9yekFsaWduZW51bQAAAA9FU2xpY2VIb3J6QWxpZ24AAAAHZGVmYXVsdAAAAAl2ZXJ0QWxpZ25l
bnVtAAAAD0VTbGljZVZlcnRBbGlnbgAAAAdkZWZhdWx0AAAAC2JnQ29sb3JUeXBlZW51bQAAABFF
U2xpY2VCR0NvbG9yVHlwZQAAAABOb25lAAAACXRvcE91dHNldGxvbmcAAAAAAAAACmxlZnRPdXRz
ZXRsb25nAAAAAAAAAAxib3R0b21PdXRzZXRsb25nAAAAAAAAAAtyaWdodE91dHNldGxvbmcAAAAA
ADhCSU0EEQAAAAAAAQEAOEJJTQQUAAAAAAAEAAAAAzhCSU0EDAAAAAACzgAAAAEAAACAAAAAAgAA
AYAAAAMAAAACsgAYAAH/2P/gABBKRklGAAECAQBIAEgAAP/tAAxBZG9iZV9DTQAC/+4ADkFkb2Jl
AGSAAAAAAf/bAIQADAgICAkIDAkJDBELCgsRFQ8MDA8VGBMTFRMTGBEMDAwMDAwRDAwMDAwMDAwM
DAwMDAwMDAwMDAwMDAwMDAwMDAENCwsNDg0QDg4QFA4ODhQUDg4ODhQRDAwMDAwREQwMDAwMDBEM
DAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwM/8AAEQgAAgCAAwEiAAIRAQMRAf/dAAQACP/EAT8A
AAEFAQEBAQEBAAAAAAAAAAMAAQIEBQYHCAkKCwEAAQUBAQEBAQEAAAAAAAAAAQACAwQFBgcICQoL
EAABBAEDAgQCBQcGCAUDDDMBAAIRAwQhEjEFQVFhEyJxgTIGFJGhsUIjJBVSwWIzNHKC0UMHJZJT
8OHxY3M1FqKygyZEk1RkRcKjdDYX0lXiZfKzhMPTdePzRieUpIW0lcTU5PSltcXV5fVWZnaGlqa2
xtbm9jdHV2d3h5ent8fX5/cRAAICAQIEBAMEBQYHBwYFNQEAAhEDITESBEFRYXEiEwUygZEUobFC
I8FS0fAzJGLhcoKSQ1MVY3M08SUGFqKygwcmNcLSRJNUoxdkRVU2dGXi8rOEw9N14/NGlKSFtJXE
1OT0pbXF1eX1VmZ2hpamtsbW5vYnN0dXZ3eHl6e3x//aAAwDAQACEQMRAD8A2ujf8j4P/L39Hq/m
/ofRb/N/8H/o/wDg1c/+uFeCJKQ7lYNn3v8A+uFL/wCuFeCJJKfe/wD64Uv/AK4V4Ikkp97/APrh
S/8ArhXgiSSn3v8A+uFL/wCuFeCJJKfe/wD64Uv/AK4V4Ikkp97/APrhS/8ArhXgiSSn3v8A+uFA
zv6Dk/8Aig/mrOf6rl4WkkFF/9k4QklNBCEAAAAAAFUAAAABAQAAAA8AQQBkAG8AYgBlACAAUABo
AG8AdABvAHMAaABvAHAAAAATAEEAZABvAGIAZQAgAFAAaABvAHQAbwBzAGgAbwBwACAANwAuADAA
AAABADhCSU0EBgAAAAAABwAIAAAAAQEA/+ESSGh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8A
PD94cGFja2V0IGJlZ2luPSfvu78nIGlkPSdXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQnPz4KPD9h
ZG9iZS14YXAtZmlsdGVycyBlc2M9IkNSIj8+Cjx4OnhhcG1ldGEgeG1sbnM6eD0nYWRvYmU6bnM6
bWV0YS8nIHg6eGFwdGs9J1hNUCB0b29sa2l0IDIuOC4yLTMzLCBmcmFtZXdvcmsgMS41Jz4KPHJk
ZjpSREYgeG1sbnM6cmRmPSdodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgt
bnMjJyB4bWxuczppWD0naHR0cDovL25zLmFkb2JlLmNvbS9pWC8xLjAvJz4KCiA8cmRmOkRlc2Ny
aXB0aW9uIGFib3V0PSd1dWlkOjM4ZWY1NGQ0LTg3ZjAtMTFkOS1hMDM2LWFlYWQ3YjZlYzZhMycK
ICB4bWxuczp4YXBNTT0naHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyc+CiAgPHhhcE1N
OkRvY3VtZW50SUQ+YWRvYmU6ZG9jaWQ6cGhvdG9zaG9wOjM4ZWY1NGQyLTg3ZjAtMTFkOS1hMDM2
LWFlYWQ3YjZlYzZhMzwveGFwTU06RG9jdW1lbnRJRD4KIDwvcmRmOkRlc2NyaXB0aW9uPgoKPC9y
ZGY6UkRGPgo8L3g6eGFwbWV0YT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAK
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
IAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAog
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9J3cnPz7/7gAOQWRvYmUAZEAAAAAB/9sAhAABAQEB
AQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAgICAgICAgICAgIDAwMDAwMD
AwMDAQEBAQEBAQEBAQECAgECAgMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD
AwMDAwMDAwMDAwP/wAARCAANArwDAREAAhEBAxEB/90ABABY/8QBogAAAAYCAwEAAAAAAAAAAAAA
BwgGBQQJAwoCAQALAQAABgMBAQEAAAAAAAAAAAAGBQQDBwIIAQkACgsQAAIBAwQBAwMCAwMDAgYJ
dQECAwQRBRIGIQcTIgAIMRRBMiMVCVFCFmEkMxdScYEYYpElQ6Gx8CY0cgoZwdE1J+FTNoLxkqJE
VHNFRjdHYyhVVlcassLS4vJkg3SThGWjs8PT4yk4ZvN1Kjk6SElKWFlaZ2hpanZ3eHl6hYaHiImK
lJWWl5iZmqSlpqeoqaq0tba3uLm6xMXGx8jJytTV1tfY2drk5ebn6Onq9PX29/j5+hEAAgEDAgQE
AwUEBAQGBgVtAQIDEQQhEgUxBgAiE0FRBzJhFHEIQoEjkRVSoWIWMwmxJMHRQ3LwF+GCNCWSUxhj
RPGisiY1GVQ2RWQnCnODk0Z0wtLi8lVldVY3hIWjs8PT4/MpGpSktMTU5PSVpbXF1eX1KEdXZjh2
hpamtsbW5vZnd4eXp7fH1+f3SFhoeIiYqLjI2Oj4OUlZaXmJmam5ydnp+So6SlpqeoqaqrrK2ur6
/9oADAMBAAIRAxEAPwA6v/CWPvHYuU+M/wAjvitDkUpO3tsd9v8AIjGYOqqKKOfeGyN59dbH6+rP
7s0b1SV1fVbQyPW0kuS0RsIo8lRBQTKdMie4tjOl/a34WsDR6K+jKzHP2hsetD1HvIV5C9jdWOr/
ABhZNdPUFQuPsK5+0dbXeM3aKRWpqrVBNCzRSwzK0csUkZKyRuj2ZGVxYggEEe45qPz6HoJHTx/f
Sm/46J/tz73+fVtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR
69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+
fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/t
z79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/4
6J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/f
Sm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR
69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+
fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/t
z79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/4
6J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/f
Sm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR
69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+
fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/t
z79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/4
6J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/f
Sm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR
69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+
fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/t
z79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/4
6J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/f
Sm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR
69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR69/fSm/46J/tz79+fXtR64Sb1pgjfup9P6/X
/b+/fn1rUekJmt34TC0uQ3/vHN4/aewNjU1Vundu8s9UQ4/b2DxOAiGSrKnIZKrlgpoI0RF1eouq
vqCkC3u0cck0iRRIWdjQAZJPoOqO6Rq0srBY1FSTwAHz6+XX/s1Gxv8Ah03/AGef+Dbg/wBGn/Dh
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
"));
		break;
		case "01_bodybg.jpg":
			header("Content-type: image/jpeg");
			echo trim(base64_decode("
/9j/4AAQSkZJRgABAgEASABIAAD/4QOnRXhpZgAATU0AKgAAAAgABwESAAMAAAABAAEAAAEaAAUA
AAABAAAAYgEbAAUAAAABAAAAagEoAAMAAAABAAIAAAExAAIAAAAUAAAAcgEyAAIAAAAUAAAAhodp
AAQAAAABAAAAnAAAAMgAAABIAAAAAQAAAEgAAAABQWRvYmUgUGhvdG9zaG9wIDcuMAAyMDA1OjAy
OjI2IDA0OjE2OjIwAAAAAAOgAQADAAAAAf//AACgAgAEAAAAAQAAArygAwAEAAAAAQAAAAEAAAAA
AAAABgEDAAMAAAABAAYAAAEaAAUAAAABAAABFgEbAAUAAAABAAABHgEoAAMAAAABAAIAAAIBAAQA
AAABAAABJgICAAQAAAABAAACeQAAAAAAAABIAAAAAQAAAEgAAAAB/9j/4AAQSkZJRgABAgEASABI
AAD/7QAMQWRvYmVfQ00AAv/uAA5BZG9iZQBkgAAAAAH/2wCEAAwICAgJCAwJCQwRCwoLERUPDAwP
FRgTExUTExgRDAwMDAwMEQwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwBDQsLDQ4NEA4OEBQO
Dg4UFA4ODg4UEQwMDAwMEREMDAwMDAwRDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDP/AABEI
AAEAgAMBIgACEQEDEQH/3QAEAAj/xAE/AAABBQEBAQEBAQAAAAAAAAADAAECBAUGBwgJCgsBAAEF
AQEBAQEBAAAAAAAAAAEAAgMEBQYHCAkKCxAAAQQBAwIEAgUHBggFAwwzAQACEQMEIRIxBUFRYRMi
cYEyBhSRobFCIyQVUsFiMzRygtFDByWSU/Dh8WNzNRaisoMmRJNUZEXCo3Q2F9JV4mXys4TD03Xj
80YnlKSFtJXE1OT0pbXF1eX1VmZ2hpamtsbW5vY3R1dnd4eXp7fH1+f3EQACAgECBAQDBAUGBwcG
BTUBAAIRAyExEgRBUWFxIhMFMoGRFKGxQiPBUtHwMyRi4XKCkkNTFWNzNPElBhaisoMHJjXC0kST
VKMXZEVVNnRl4vKzhMPTdePzRpSkhbSVxNTk9KW1xdXl9VZmdoaWprbG1ub2JzdHV2d3h5ent8f/
2gAMAwEAAhEDEQA/APRug/8AIfTv/CtP/ntivr5VSRO5QNg/VSS+VUkEv1UkvlVJJT9VJL5VSSU/
VSS+VUklP1UkvlVJJT9VJL5VSSU/VSp9Y/5Izv8Awvb/ANQ5fMKSQ3Udn//Z/+0IYFBob3Rvc2hv
cCAzLjAAOEJJTQQlAAAAAAAQAAAAAAAAAAAAAAAAAAAAADhCSU0D7QAAAAAAEABIAAAAAQABAEgA
AAABAAE4QklNBCYAAAAAAA4AAAAAAAAAAAAAP4AAADhCSU0EDQAAAAAABAAAAHg4QklNBBkAAAAA
AAQAAAAeOEJJTQPzAAAAAAAJAAAAAAAAAAABADhCSU0ECgAAAAAAAQAAOEJJTScQAAAAAAAKAAEA
AAAAAAAAAThCSU0D9QAAAAAASAAvZmYAAQBsZmYABgAAAAAAAQAvZmYAAQChmZoABgAAAAAAAQAy
AAAAAQBaAAAABgAAAAAAAQA1AAAAAQAtAAAABgAAAAAAAThCSU0D+AAAAAAAcAAA////////////
/////////////////wPoAAAAAP////////////////////////////8D6AAAAAD/////////////
////////////////A+gAAAAA/////////////////////////////wPoAAA4QklNBAAAAAAAAAIA
ADhCSU0EAgAAAAAAAgAAOEJJTQQIAAAAAAAQAAAAAQAAAkAAAAJAAAAAADhCSU0EHgAAAAAABAAA
AAA4QklNBBoAAAAAA0kAAAAGAAAAAAAAAAAAAAABAAACvAAAAAoAVQBuAHQAaQB0AGwAZQBkAC0A
MwAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAACvAAAAAEAAAAAAAAAAAAAAAAAAAAA
AQAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAQAAAAAAAG51bGwAAAACAAAABmJvdW5kc09iamMAAAAB
AAAAAAAAUmN0MQAAAAQAAAAAVG9wIGxvbmcAAAAAAAAAAExlZnRsb25nAAAAAAAAAABCdG9tbG9u
ZwAAAAEAAAAAUmdodGxvbmcAAAK8AAAABnNsaWNlc1ZsTHMAAAABT2JqYwAAAAEAAAAAAAVzbGlj
ZQAAABIAAAAHc2xpY2VJRGxvbmcAAAAAAAAAB2dyb3VwSURsb25nAAAAAAAAAAZvcmlnaW5lbnVt
AAAADEVTbGljZU9yaWdpbgAAAA1hdXRvR2VuZXJhdGVkAAAAAFR5cGVlbnVtAAAACkVTbGljZVR5
cGUAAAAASW1nIAAAAAZib3VuZHNPYmpjAAAAAQAAAAAAAFJjdDEAAAAEAAAAAFRvcCBsb25nAAAA
AAAAAABMZWZ0bG9uZwAAAAAAAAAAQnRvbWxvbmcAAAABAAAAAFJnaHRsb25nAAACvAAAAAN1cmxU
RVhUAAAAAQAAAAAAAG51bGxURVhUAAAAAQAAAAAAAE1zZ2VURVhUAAAAAQAAAAAABmFsdFRhZ1RF
WFQAAAABAAAAAAAOY2VsbFRleHRJc0hUTUxib29sAQAAAAhjZWxsVGV4dFRFWFQAAAABAAAAAAAJ
aG9yekFsaWduZW51bQAAAA9FU2xpY2VIb3J6QWxpZ24AAAAHZGVmYXVsdAAAAAl2ZXJ0QWxpZ25l
bnVtAAAAD0VTbGljZVZlcnRBbGlnbgAAAAdkZWZhdWx0AAAAC2JnQ29sb3JUeXBlZW51bQAAABFF
U2xpY2VCR0NvbG9yVHlwZQAAAABOb25lAAAACXRvcE91dHNldGxvbmcAAAAAAAAACmxlZnRPdXRz
ZXRsb25nAAAAAAAAAAxib3R0b21PdXRzZXRsb25nAAAAAAAAAAtyaWdodE91dHNldGxvbmcAAAAA
ADhCSU0EEQAAAAAAAQEAOEJJTQQUAAAAAAAEAAAAAzhCSU0EDAAAAAAClQAAAAEAAACAAAAAAQAA
AYAAAAGAAAACeQAYAAH/2P/gABBKRklGAAECAQBIAEgAAP/tAAxBZG9iZV9DTQAC/+4ADkFkb2Jl
AGSAAAAAAf/bAIQADAgICAkIDAkJDBELCgsRFQ8MDA8VGBMTFRMTGBEMDAwMDAwRDAwMDAwMDAwM
DAwMDAwMDAwMDAwMDAwMDAwMDAENCwsNDg0QDg4QFA4ODhQUDg4ODhQRDAwMDAwREQwMDAwMDBEM
DAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwM/8AAEQgAAQCAAwEiAAIRAQMRAf/dAAQACP/EAT8A
AAEFAQEBAQEBAAAAAAAAAAMAAQIEBQYHCAkKCwEAAQUBAQEBAQEAAAAAAAAAAQACAwQFBgcICQoL
EAABBAEDAgQCBQcGCAUDDDMBAAIRAwQhEjEFQVFhEyJxgTIGFJGhsUIjJBVSwWIzNHKC0UMHJZJT
8OHxY3M1FqKygyZEk1RkRcKjdDYX0lXiZfKzhMPTdePzRieUpIW0lcTU5PSltcXV5fVWZnaGlqa2
xtbm9jdHV2d3h5ent8fX5/cRAAICAQIEBAMEBQYHBwYFNQEAAhEDITESBEFRYXEiEwUygZEUobFC
I8FS0fAzJGLhcoKSQ1MVY3M08SUGFqKygwcmNcLSRJNUoxdkRVU2dGXi8rOEw9N14/NGlKSFtJXE
1OT0pbXF1eX1VmZ2hpamtsbW5vYnN0dXZ3eHl6e3x//aAAwDAQACEQMRAD8A9G6D/wAh9O/8K0/+
e2K+vlVJE7lA2D9VJL5VSQS/VSS+VUklP1UkvlVJJT9VJL5VSSU/VSS+VUklP1UkvlVJJT9VKn1j
/kjO/wDC9v8A1Dl8wpJDdR2f/9kAOEJJTQQhAAAAAABVAAAAAQEAAAAPAEEAZABvAGIAZQAgAFAA
aABvAHQAbwBzAGgAbwBwAAAAEwBBAGQAbwBiAGUAIABQAGgAbwB0AG8AcwBoAG8AcAAgADcALgAw
AAAAAQA4QklNBAYAAAAAAAcACAAAAAEBAP/hEkhodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAv
ADw/eHBhY2tldCBiZWdpbj0n77u/JyBpZD0nVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkJz8+Cjw/
YWRvYmUteGFwLWZpbHRlcnMgZXNjPSJDUiI/Pgo8eDp4YXBtZXRhIHhtbG5zOng9J2Fkb2JlOm5z
Om1ldGEvJyB4OnhhcHRrPSdYTVAgdG9vbGtpdCAyLjguMi0zMywgZnJhbWV3b3JrIDEuNSc+Cjxy
ZGY6UkRGIHhtbG5zOnJkZj0naHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4
LW5zIycgeG1sbnM6aVg9J2h0dHA6Ly9ucy5hZG9iZS5jb20vaVgvMS4wLyc+CgogPHJkZjpEZXNj
cmlwdGlvbiBhYm91dD0ndXVpZDozOGVmNTRkMC04N2YwLTExZDktYTAzNi1hZWFkN2I2ZWM2YTMn
CiAgeG1sbnM6eGFwTU09J2h0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8nPgogIDx4YXBN
TTpEb2N1bWVudElEPmFkb2JlOmRvY2lkOnBob3Rvc2hvcDo2ZTEwMWQwMy04N2VlLTExZDktYTAz
Ni1hZWFkN2I2ZWM2YTM8L3hhcE1NOkRvY3VtZW50SUQ+CiA8L3JkZjpEZXNjcmlwdGlvbj4KCjwv
cmRmOlJERj4KPC94OnhhcG1ldGE+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAK
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgIAo8P3hwYWNrZXQgZW5kPSd3Jz8+/+4ADkFkb2JlAGRAAAAAAf/bAIQAAQEB
AQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQICAgICAgICAgICAwMDAwMD
AwMDAwEBAQEBAQEBAQEBAgIBAgIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMD
AwMDAwMDAwMDAwMD/8AAEQgAAQK8AwERAAIRAQMRAf/dAAQAWP/EAaIAAAAGAgMBAAAAAAAAAAAA
AAcIBgUECQMKAgEACwEAAAYDAQEBAAAAAAAAAAAABgUEAwcCCAEJAAoLEAACAQMEAQMDAgMDAwIG
CXUBAgMEEQUSBiEHEyIACDEUQTIjFQlRQhZhJDMXUnGBGGKRJUOhsfAmNHIKGcHRNSfhUzaC8ZKi
RFRzRUY3R2MoVVZXGrLC0uLyZIN0k4Rlo7PD0+MpOGbzdSo5OkhJSlhZWmdoaWp2d3h5eoWGh4iJ
ipSVlpeYmZqkpaanqKmqtLW2t7i5usTFxsfIycrU1dbX2Nna5OXm5+jp6vT19vf4+foRAAIBAwIE
BAMFBAQEBgYFbQECAxEEIRIFMQYAIhNBUQcyYRRxCEKBI5EVUqFiFjMJsSTB0UNy8BfhgjQlklMY
Y0TxorImNRlUNkVkJwpzg5NGdMLS4vJVZXVWN4SFo7PD0+PzKRqUpLTE1OT0laW1xdXl9ShHV2Y4
doaWprbG1ub2Z3eHl6e3x9fn90hYaHiImKi4yNjo+DlJWWl5iZmpucnZ6fkqOkpaanqKmqq6ytrq
+v/aAAwDAQACEQMRAD8As/8A+EtP/btzt3/xfbf/AP8AA+fHz2OPcD/kuRf80F/4/J0CeRv+SNN/
z0H/AI5H1tO4X/gPF/wX2B/Xoarw6fPfut9e9+691737r3Xvfuvde9+691737r3Xvfuvde9+6917
37r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfu
vde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691
737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvf
uvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+69
1737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691737r3Xv
fuvde9+691737r3Xvfuvde9+691737r3Xvfuvde9+691wk/Q3+t7917oNMz/AMXvDf8Aa4xf/uZD
715fn1T8Q+3r5of/AHcT/wDmbn/5+73OP/Okf9QH/WHqGv8Ancf+o/8A6zdf/9k="));
		break;

		case "01_header01.jpg":
			header("Content-type: image/jpeg");
			echo trim(base64_decode("
/9j/4AAQSkZJRgABAgEASABIAAD/4QhMRXhpZgAATU0AKgAAAAgABwESAAMAAAABAAEAAAEaAAUA
AAABAAAAYgEbAAUAAAABAAAAagEoAAMAAAABAAIAAAExAAIAAAAcAAAAcgEyAAIAAAAUAAAAjodp
AAQAAAABAAAApAAAANAACvyAAAAnEAAK/IAAACcQQWRvYmUgUGhvdG9zaG9wIENTMiBXaW5kb3dz
ADIwMDU6MDY6MTYgMTc6Mzc6MzkAAAAAA6ABAAMAAAAB//8AAKACAAQAAAABAAACvKADAAQAAAAB
AAAAlwAAAAAAAAAGAQMAAwAAAAEABgAAARoABQAAAAEAAAEeARsABQAAAAEAAAEmASgAAwAAAAEA
AgAAAgEABAAAAAEAAAEuAgIABAAAAAEAAAcWAAAAAAAAAEgAAAABAAAASAAAAAH/2P/gABBKRklG
AAECAABIAEgAAP/tAAxBZG9iZV9DTQAC/+4ADkFkb2JlAGSAAAAAAf/bAIQADAgICAkIDAkJDBEL
CgsRFQ8MDA8VGBMTFRMTGBEMDAwMDAwRDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAENCwsN
Dg0QDg4QFA4ODhQUDg4ODhQRDAwMDAwREQwMDAwMDBEMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwM
DAwM/8AAEQgAIwCgAwEiAAIRAQMRAf/dAAQACv/EAT8AAAEFAQEBAQEBAAAAAAAAAAMAAQIEBQYH
CAkKCwEAAQUBAQEBAQEAAAAAAAAAAQACAwQFBgcICQoLEAABBAEDAgQCBQcGCAUDDDMBAAIRAwQh
EjEFQVFhEyJxgTIGFJGhsUIjJBVSwWIzNHKC0UMHJZJT8OHxY3M1FqKygyZEk1RkRcKjdDYX0lXi
ZfKzhMPTdePzRieUpIW0lcTU5PSltcXV5fVWZnaGlqa2xtbm9jdHV2d3h5ent8fX5/cRAAICAQIE
BAMEBQYHBwYFNQEAAhEDITESBEFRYXEiEwUygZEUobFCI8FS0fAzJGLhcoKSQ1MVY3M08SUGFqKy
gwcmNcLSRJNUoxdkRVU2dGXi8rOEw9N14/NGlKSFtJXE1OT0pbXF1eX1VmZ2hpamtsbW5vYnN0dX
Z3eHl6e3x//aAAwDAQACEQMRAD8A7boHQOhO6F05zunYrnOxaXOc6mskk1sc5znOYXOc5x3Oc5X/
APm90H/ytxP+2K//ACCxOmdevxumYdDK63Nrx6WgkukxXXzAVn/nLlf6Gr/Od/5FQnm8QkQZHQkb
SQKdL/m90H/ytxP+2K//ACCX/N7oP/lbif8AbFf/AJBZv/ObL/0NX+c7/wAioH605c6UVf5zv/Ip
DmsR2kfskokB1f8Am90H/wArcT/tiv8A8gl/ze6D/wCVuJ/2xX/5BZQ+tGaeKKv853/kU/8AznzB
zRV/nO/8il96xfvH7Cix/IOp/wA3ug/+VuJ/2xX/AOQS/wCb3Qf/ACtxP+2K/wDyCyv+dOZ2oqP9
p3/kUv8AnRmd6Kh/ad/5FH7zj/eP2FHFH+QdX/m90H/ytxP+2K//ACCX/N7oP/lbif8AbFf/AJBZ
X/OnL/0NX+c7/wAil/zpy/8AQVf5zv8AyKP3iHc/YVccf5B1f+b3Qf8AytxP+2K//IJf83ug/wDl
bif9sV/+QWV/zpy/9BV/nO/8il/zpzP9BV/nO/8AIoHmcY/SP2FPFH+QdX/m90H/AMrcT/tiv/yC
X/N7oP8A5W4n/bFf/kFlf86Mv/QVf5zv/Ipf86Mz/QVf5zv/ACKX3nH3P2FXFF1f+b3Qf/K3E/7Y
r/8AIJf83ug/+VuJ/wBsV/8AkFlf86cz/QVf5zv/ACKX/OjM70Vf5zv/ACKB5rGP0j9klcUXV/5v
dB/8rcT/ALYr/wDIJf8AN7oP/lbif9sV/wDkFlf86Mz/AEFX+c7/AMipj6zZZ/wNX+c7/wAigebx
DeR+ySRRdL/m90H/AMrcT/tiv/yCodf6B0JvQuoub07Fa5uLcWltNYIIre5rmuazc1zXDc1zVD/n
Ll9qav8AOd/5FV+p9dyMjpmZQ+qsNsx7mktJkTVZxISjzuEyAEjZIG0lGn//0L9FsY9DR2pq/wDP
daJ6p7qgLXBlbewrqH/gdaXqErKyRPHPUfNL81tt19+nKgLZKpusM8pvU80YgALSdW+cgAQCo+uD
3kqn6rRymOQe2gThEDog2W76x+SXrKj6x8UvWTqRTe9YJesFQ9bzT+sjSqb3rJesFRFpPCffHP3B
NpVN31uw1S9Ydz8gqXqnjgeCXqeaVKpu+v4aJesqPqFSFpHJTZBNN31CSq+dnHDpZYdDZY2rcQXB
m4Pe659dZbZb6ba/5qt7N/8ApFAZBQ8gsyKwxxLdrg5rhBhwBb9E/Sbte5qUfmjxj0WOKv3Uir3W
yfrF0nGrY7GuvuvMgtsdv+0H+TjM2sw9v5lmP6NNbP6RXetK28WYtpHD6LDHxqeVztHTGU5dmQGs
ZY8Bvq/Sgf8AAsd+f/Lu+h/wq0m2AVuYNGiqxoHkKrAFPmjhOTGcQF2OLhFR/R4Ukh//0Yu3e2N0
bK+Nv+jr8VE7/wCX8ti8uSWXP+clt8x3R0fTjvn87/oqLt/8v/orzNJSQ+jG+l+/+V/0Uvf/ACv+
ivNEk77FPpfv/lf9FL3/AMr/AKK80SR+xT6X7/5X/RUhv/lf9FeZJIFIfT/ft/P+WxQ9/wDL/wCi
vM0kB9FPpnv/AJf/AEUhv8/ntXmaSR+iC+ne/wDlf9FL3/yv+ivMUk37FPp3v/lf9FL3/wAr/orz
FJI7dFPpvv8A5X/RTt3SZ3Rss52x/N2eC8xSTsfzR23Cer//2f/tDnhQaG90b3Nob3AgMy4wADhC
SU0EBAAAAAAABxwCAAACAAIAOEJJTQQlAAAAAAAQRgzyiSa4VtqwnAGhsKeQdzhCSU0D7QAAAAAA
EABIAAAAAQACAEgAAAABAAI4QklNBCYAAAAAAA4AAAAAAAAAAAAAP4AAADhCSU0EDQAAAAAABAAA
AHg4QklNBBkAAAAAAAQAAAAeOEJJTQPzAAAAAAAJAAAAAAAAAAABADhCSU0ECgAAAAAAAQAAOEJJ
TScQAAAAAAAKAAEAAAAAAAAAAThCSU0D9QAAAAAASAAvZmYAAQBsZmYABgAAAAAAAQAvZmYAAQCh
mZoABgAAAAAAAQAyAAAAAQBaAAAABgAAAAAAAQA1AAAAAQAtAAAABgAAAAAAAThCSU0D+AAAAAAA
cAAA/////////////////////////////wPoAAAAAP////////////////////////////8D6AAA
AAD/////////////////////////////A+gAAAAA/////////////////////////////wPoAAA4
QklNBAAAAAAAAAIAAThCSU0EAgAAAAAABAAAAAA4QklNBDAAAAAAAAIBAThCSU0ELQAAAAAABgAB
AAAABDhCSU0ECAAAAAAAEAAAAAEAAAJAAAACQAAAAAA4QklNBB4AAAAAAAQAAAAAOEJJTQQaAAAA
AANLAAAABgAAAAAAAAAAAAAAlwAAArwAAAALADAAMQBfAGgAZQBhAGQAZQByADAAMQAAAAEAAAAA
AAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAAAAACvAAAAJcAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAAAA
AAAAAAAAAAAAAAAQAAAAAQAAAAAAAG51bGwAAAACAAAABmJvdW5kc09iamMAAAABAAAAAAAAUmN0
MQAAAAQAAAAAVG9wIGxvbmcAAAAAAAAAAExlZnRsb25nAAAAAAAAAABCdG9tbG9uZwAAAJcAAAAA
UmdodGxvbmcAAAK8AAAABnNsaWNlc1ZsTHMAAAABT2JqYwAAAAEAAAAAAAVzbGljZQAAABIAAAAH
c2xpY2VJRGxvbmcAAAAAAAAAB2dyb3VwSURsb25nAAAAAAAAAAZvcmlnaW5lbnVtAAAADEVTbGlj
ZU9yaWdpbgAAAA1hdXRvR2VuZXJhdGVkAAAAAFR5cGVlbnVtAAAACkVTbGljZVR5cGUAAAAASW1n
IAAAAAZib3VuZHNPYmpjAAAAAQAAAAAAAFJjdDEAAAAEAAAAAFRvcCBsb25nAAAAAAAAAABMZWZ0
bG9uZwAAAAAAAAAAQnRvbWxvbmcAAACXAAAAAFJnaHRsb25nAAACvAAAAAN1cmxURVhUAAAAAQAA
AAAAAG51bGxURVhUAAAAAQAAAAAAAE1zZ2VURVhUAAAAAQAAAAAABmFsdFRhZ1RFWFQAAAABAAAA
AAAOY2VsbFRleHRJc0hUTUxib29sAQAAAAhjZWxsVGV4dFRFWFQAAAABAAAAAAAJaG9yekFsaWdu
ZW51bQAAAA9FU2xpY2VIb3J6QWxpZ24AAAAHZGVmYXVsdAAAAAl2ZXJ0QWxpZ25lbnVtAAAAD0VT
bGljZVZlcnRBbGlnbgAAAAdkZWZhdWx0AAAAC2JnQ29sb3JUeXBlZW51bQAAABFFU2xpY2VCR0Nv
bG9yVHlwZQAAAABOb25lAAAACXRvcE91dHNldGxvbmcAAAAAAAAACmxlZnRPdXRzZXRsb25nAAAA
AAAAAAxib3R0b21PdXRzZXRsb25nAAAAAAAAAAtyaWdodE91dHNldGxvbmcAAAAAADhCSU0EKAAA
AAAADAAAAAE/8AAAAAAAADhCSU0EEQAAAAAAAQEAOEJJTQQUAAAAAAAEAAAABDhCSU0EDAAAAAAH
MgAAAAEAAACgAAAAIwAAAeAAAEGgAAAHFgAYAAH/2P/gABBKRklGAAECAABIAEgAAP/tAAxBZG9i
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
bwB0AG8AcwBoAG8AcAAAABMAQQBkAG8AYgBlACAAUABoAG8AdABvAHMAaABvAHAAIABDAFMAMgAA
AAEAOEJJTQ+gAAAAAAD4bWFuaUlSRlIAAADsOEJJTUFuRHMAAADMAAAAEAAAAAEAAAAAAABudWxs
AAAAAwAAAABBRlN0bG9uZwAAAAAAAAAARnJJblZsTHMAAAABT2JqYwAAAAEAAAAAAABudWxsAAAA
AQAAAABGcklEbG9uZ14Sb90AAAAARlN0c1ZsTHMAAAABT2JqYwAAAAEAAAAAAABudWxsAAAABAAA
AABGc0lEbG9uZwAAAAAAAAAAQUZybWxvbmcAAAAAAAAAAEZzRnJWbExzAAAAAWxvbmdeEm/dAAAA
AExDbnRsb25nAAAAAAAAOEJJTVJvbGwAAAAIAAAAAAAAAAA4QklND6EAAAAAABxtZnJpAAAAAgAA
ABAAAAABAAAAAAAAAAEAAAAAOEJJTQQGAAAAAAAHAAIAAAABAQD/4TpqaHR0cDovL25zLmFkb2Jl
LmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pO
VGN6a2M5ZCI/Pgo8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSIz
LjEuMS0xMTEiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkv
MDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0i
IgogICAgICAgICAgICB4bWxuczp4YXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21t
LyIKICAgICAgICAgICAgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9z
VHlwZS9SZXNvdXJjZVJlZiMiPgogICAgICAgICA8eGFwTU06RG9jdW1lbnRJRD51dWlkOkM2M0I1
Q0Q1ODRERUQ5MTFCRDI1RUZFMTZCQzU3QkMzPC94YXBNTTpEb2N1bWVudElEPgogICAgICAgICA8
eGFwTU06SW5zdGFuY2VJRD51dWlkOkM3M0I1Q0Q1ODRERUQ5MTFCRDI1RUZFMTZCQzU3QkMzPC94
YXBNTTpJbnN0YW5jZUlEPgogICAgICAgICA8eGFwTU06RGVyaXZlZEZyb20gcmRmOnBhcnNlVHlw
ZT0iUmVzb3VyY2UiPgogICAgICAgICAgICA8c3RSZWY6aW5zdGFuY2VJRD51dWlkOkM0M0I1Q0Q1
ODRERUQ5MTFCRDI1RUZFMTZCQzU3QkMzPC9zdFJlZjppbnN0YW5jZUlEPgogICAgICAgICAgICA8
c3RSZWY6ZG9jdW1lbnRJRD51dWlkOkMzM0I1Q0Q1ODRERUQ5MTFCRDI1RUZFMTZCQzU3QkMzPC9z
dFJlZjpkb2N1bWVudElEPgogICAgICAgICA8L3hhcE1NOkRlcml2ZWRGcm9tPgogICAgICA8L3Jk
ZjpEZXNjcmlwdGlvbj4KICAgICAgPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIKICAgICAg
ICAgICAgeG1sbnM6dGlmZj0iaHR0cDovL25zLmFkb2JlLmNvbS90aWZmLzEuMC8iPgogICAgICAg
ICA8dGlmZjpPcmllbnRhdGlvbj4xPC90aWZmOk9yaWVudGF0aW9uPgogICAgICAgICA8dGlmZjpY
UmVzb2x1dGlvbj43MjAwMDAvMTAwMDA8L3RpZmY6WFJlc29sdXRpb24+CiAgICAgICAgIDx0aWZm
OllSZXNvbHV0aW9uPjcyMDAwMC8xMDAwMDwvdGlmZjpZUmVzb2x1dGlvbj4KICAgICAgICAgPHRp
ZmY6UmVzb2x1dGlvblVuaXQ+MjwvdGlmZjpSZXNvbHV0aW9uVW5pdD4KICAgICAgICAgPHRpZmY6
TmF0aXZlRGlnZXN0PjI1NiwyNTcsMjU4LDI1OSwyNjIsMjc0LDI3NywyODQsNTMwLDUzMSwyODIs
MjgzLDI5NiwzMDEsMzE4LDMxOSw1MjksNTMyLDMwNiwyNzAsMjcxLDI3MiwzMDUsMzE1LDMzNDMy
Ozk4M0M1QkM5MkRENEI4NzZEMjQyOTczQzUyQTEzOUJGPC90aWZmOk5hdGl2ZURpZ2VzdD4KICAg
ICAgPC9yZGY6RGVzY3JpcHRpb24+CiAgICAgIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIi
CiAgICAgICAgICAgIHhtbG5zOnhhcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyI+CiAg
ICAgICAgIDx4YXA6TW9kaWZ5RGF0ZT4yMDA1LTA2LTE2VDE3OjM3OjM5KzAxOjAwPC94YXA6TW9k
aWZ5RGF0ZT4KICAgICAgICAgPHhhcDpDcmVhdG9yVG9vbD5BZG9iZSBQaG90b3Nob3AgQ1MyIFdp
bmRvd3M8L3hhcDpDcmVhdG9yVG9vbD4KICAgICAgICAgPHhhcDpDcmVhdGVEYXRlPjIwMDUtMDYt
MTZUMTc6Mzc6MzkrMDE6MDA8L3hhcDpDcmVhdGVEYXRlPgogICAgICAgICA8eGFwOk1ldGFkYXRh
RGF0ZT4yMDA1LTA2LTE2VDE3OjM3OjM5KzAxOjAwPC94YXA6TWV0YWRhdGFEYXRlPgogICAgICA8
L3JkZjpEZXNjcmlwdGlvbj4KICAgICAgPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIKICAg
ICAgICAgICAgeG1sbnM6ZXhpZj0iaHR0cDovL25zLmFkb2JlLmNvbS9leGlmLzEuMC8iPgogICAg
ICAgICA8ZXhpZjpDb2xvclNwYWNlPi0xPC9leGlmOkNvbG9yU3BhY2U+CiAgICAgICAgIDxleGlm
OlBpeGVsWERpbWVuc2lvbj43MDA8L2V4aWY6UGl4ZWxYRGltZW5zaW9uPgogICAgICAgICA8ZXhp
ZjpQaXhlbFlEaW1lbnNpb24+MTUxPC9leGlmOlBpeGVsWURpbWVuc2lvbj4KICAgICAgICAgPGV4
aWY6TmF0aXZlRGlnZXN0PjM2ODY0LDQwOTYwLDQwOTYxLDM3MTIxLDM3MTIyLDQwOTYyLDQwOTYz
LDM3NTEwLDQwOTY0LDM2ODY3LDM2ODY4LDMzNDM0LDMzNDM3LDM0ODUwLDM0ODUyLDM0ODU1LDM0
ODU2LDM3Mzc3LDM3Mzc4LDM3Mzc5LDM3MzgwLDM3MzgxLDM3MzgyLDM3MzgzLDM3Mzg0LDM3Mzg1
LDM3Mzg2LDM3Mzk2LDQxNDgzLDQxNDg0LDQxNDg2LDQxNDg3LDQxNDg4LDQxNDkyLDQxNDkzLDQx
NDk1LDQxNzI4LDQxNzI5LDQxNzMwLDQxOTg1LDQxOTg2LDQxOTg3LDQxOTg4LDQxOTg5LDQxOTkw
LDQxOTkxLDQxOTkyLDQxOTkzLDQxOTk0LDQxOTk1LDQxOTk2LDQyMDE2LDAsMiw0LDUsNiw3LDgs
OSwxMCwxMSwxMiwxMywxNCwxNSwxNiwxNywxOCwyMCwyMiwyMywyNCwyNSwyNiwyNywyOCwzMDs1
N0U1QjRCMDVFNjY5RjYzNjY4NTJCNjgxNzMyRTY3NzwvZXhpZjpOYXRpdmVEaWdlc3Q+CiAgICAg
IDwvcmRmOkRlc2NyaXB0aW9uPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgog
ICAgICAgICAgICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iPgog
ICAgICAgICA8ZGM6Zm9ybWF0PmltYWdlL2pwZWc8L2RjOmZvcm1hdD4KICAgICAgPC9yZGY6RGVz
Y3JpcHRpb24+CiAgICAgIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiCiAgICAgICAgICAg
IHhtbG5zOnBob3Rvc2hvcD0iaHR0cDovL25zLmFkb2JlLmNvbS9waG90b3Nob3AvMS4wLyI+CiAg
ICAgICAgIDxwaG90b3Nob3A6Q29sb3JNb2RlPjM8L3Bob3Rvc2hvcDpDb2xvck1vZGU+CiAgICAg
ICAgIDxwaG90b3Nob3A6SGlzdG9yeS8+CiAgICAgIDwvcmRmOkRlc2NyaXB0aW9uPgogICA8L3Jk
ZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAog
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAK
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
IAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAog
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAK
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
IAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg
ICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgIAo8P3hwYWNrZXQgZW5kPSJ3
Ij8+/+4ADkFkb2JlAGSAAAAAAf/bAIQACAYGBgYGCAYGCAwIBwgMDgoICAoOEA0NDg0NEBEMDAwM
DAwRDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAEJCAgJCgkLCQkLDgsNCw4RDg4ODhERDAwM
DAwREQwMDAwMDBEMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwM/8AAEQgAlwK8AwEiAAIRAQMR
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
FXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs
2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFX
Zs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZs2bFXZj0zZj0xVCyf3qf6w/XnmL/wArB/4M3/Y/
np2T+9T/AFh+vPMX/lYP/Bm/7H8nH6Ze4fewl9Ufefuf/9GS/kb/AMobef8AbVl/6h7fOrx9BnKP
yN/5Q28/7asv/UPb51ePoMlk+uXvYw+ke5fmzZsiydmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZs
VdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmz
ZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVd
mzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmzZsVdmPTNmPTFULJ/ep/rD9eeYv
/Kv/APgzf9j+enZP71P9YfrzzH/5V/8A8Gb/ALH8nH6Z+4fewl9Ufefuf//SMPyuu7qy/L28mtJP
SlOsuvKitsbeCuzBhksXXdfoKXtP+ecX/NGQz8uSB+XV0O7a0wH0WsLfqGSVWAUVzU9q5csdSRCc
4ihsCQGED6I+5H/p7zD/AMtx/wCRcP8A1Tzfp7zD/wAt3/JOH/mjABkGN9TwzBGfU/6rP/TFNpj+
nfMP/Ld/yTh/6p5X6f8AMA/4/wD/AJJRf80YXczm5ZIZ8/8Aqs/9MVtMD5g8wf8ALcf+RcX/ADRl
f4g8w/8ALb/yTi/5owByzcsP5jN/qk/9MVtH/wCIPMP/AC2/8k4v+aM3+IPMP/Lb/wAk4v8AmjAH
LNyw/mM3+qT/ANMVso//ABB5h/5bf+ScX/NGb/EHmH/lt/5Jxf8ANGF/PL5Y/mM/+qS/0xW0f/iD
zD/y2/8AJOL/AJozHzB5h/5bf+ScX/NGF5bGlqe+EZ83+qz/ANMUElHt5j8xA7Xu3/GOL/mjGnzJ
5j/5bv8AknF/zRhazb7Zq5cM2T+fL/TFrMpd6Y/4k8x/8t3/ACTi/wCaMv8AxJ5j/wCW7/knD/zR
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"));
		break;
		case "01_hdot.gif":
			header("Content-type: image/gif");
			echo trim(base64_decode("
R0lGODlhAwABAJEAAAAAAP///1xcXP///yH5BAEAAAMALAAAAAADAAEAAAIC1FYAOw=="));
		break;
	}
}