<?php

error_reporting(E_ERROR | E_PARSE);

if(!function_exists("print_a")) {
	function print_a($var) {
		return '<pre>'.htmlentities(print_r($var, true), null, "UTF-8").'</pre>';
	}
}

header("Content-type: text/html; charset=utf-8");

$installer_folder_name = 'e107_install';

require_once("./{$installer_folder_name}/defaults.php");
require_once("./{$HANDLERS_DIRECTORY}e107_class.php");

$e107_paths = compact('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'DOWNLOADS_DIRECTORY');
$e107 = new e107($e107_paths, __FILE__);
unset($e107_paths);

$e107->e107_dirs['INSTALLER'] = "{$installer_folder_name}/";

require_once("./{$installer_folder_name}/install_template_class.php");
require_once("./{$installer_folder_name}/installer_handling_class.php");
$e_install = new e_install();
require_once("./{$installer_folder_name}/forms_class.php");
$e_forms = new e_forms();

$e_install->template->SetTag("installer_css_http", e_HTTP.$installer_folder_name."/style.css");
$e_install->template->SetTag("installer_folder_http", e_HTTP.$installer_folder_name."/");
$e_install->template->SetTag("files_dir_http", e_FILE_ABS);

if(!$_POST['stage']) {
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