<?php

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

include_once("./{$installer_folder_name}/defaults.php");
include_once("./{$HANDLERS_DIRECTORY}e107_class.php");

$e107_paths = compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'DOWNLOADS_DIRECTORY');
$e107 = new e107($e107_paths, __FILE__);
unset($e107_paths);

$e107->e107_dirs['INSTALLER'] = "{$installer_folder_name}/";

include_once("./{$installer_folder_name}/install_template_class.php");
include_once("./{$installer_folder_name}/installer_handling_class.php");
$e_install = new e_install();
include_once("./{$installer_folder_name}/forms_class.php");
$e_forms = new e_forms();

$e_install->template->SetTag("installer_css_http", e_HTTP.$installer_folder_name."/style.css");
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

echo $e_install->template->ParseTemplate("./{$installer_folder_name}/installer_template.html");

?>