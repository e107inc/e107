<?php
/**
 * Boot e107's core without class2.php and without the site_path pin, and
 * report the folder it derives for the site. e107SiteHashBootTest runs this
 * in a subprocess because the suite's own instance is initialised once and
 * {@see e107::_init()} returns early after that.
 *
 * Usage: define E107_BOOT_APP (the app root) before requiring this file. The
 * connection array is built exactly as class2.php builds it on this branch.
 * Prints one JSON line on stdout.
 */

if(!defined('E107_BOOT_APP'))
{
	fwrite(STDERR, "boot: define E107_BOOT_APP before requiring boot.php\n");
	exit(2);
}

$e107BootApp = rtrim(E107_BOOT_APP, '/\\') . '/';
chdir($e107BootApp);

define('e107_INIT', true);
define('e_ROOT', $e107BootApp);

ob_start();
include $e107BootApp . 'e107_config.php';
ob_end_clean();

$e107BootHandlers = !empty($HANDLERS_DIRECTORY) ? $HANDLERS_DIRECTORY : 'e107_handlers/';

require $e107BootApp . $e107BootHandlers . 'core_functions.php';
require $e107BootApp . $e107BootHandlers . 'e107_class.php';

$e107BootPaths = array();
foreach(array('ADMIN_DIRECTORY', 'FILES_DIRECTORY', 'IMAGES_DIRECTORY', 'THEMES_DIRECTORY', 'PLUGINS_DIRECTORY', 'HANDLERS_DIRECTORY', 'LANGUAGES_DIRECTORY', 'HELP_DIRECTORY', 'DOWNLOADS_DIRECTORY', 'UPLOADS_DIRECTORY', 'SYSTEM_DIRECTORY', 'MEDIA_DIRECTORY', 'CACHE_DIRECTORY', 'LOGS_DIRECTORY', 'CORE_DIRECTORY', 'WEB_DIRECTORY') as $e107BootName)
{
	if(isset($$e107BootName))
	{
		$e107BootPaths[$e107BootName] = $$e107BootName;
	}
}

unset($E107_CONFIG['site_path']);

$e107BootSql = compact('mySQLserver', 'mySQLuser', 'mySQLpassword', 'mySQLdefaultdb', 'mySQLprefix');

$e107BootKnownBad = e107::getInstance()->makeSiteHash('', '');
$e107BootKnownBadExisted = is_dir($e107BootApp . 'e107_media/' . $e107BootKnownBad)
	|| is_dir($e107BootApp . 'e107_system/' . $e107BootKnownBad);

$e107Boot = e107::getInstance()->initCore($e107BootPaths, e_ROOT, $e107BootSql, varset($E107_CONFIG, array()));

echo json_encode(array(
	'site_path' => $e107Boot->site_path,
	'expected' => $e107Boot->makeSiteHash($mySQLdefaultdb, $mySQLprefix),
	'known_bad' => $e107BootKnownBad,
	'media' => e107::getFolder('media'),
	'system' => e107::getFolder('system'),
	'known_bad_existed' => $e107BootKnownBadExisted,
	'known_bad_exists' => is_dir(e107::getFolder('media_base') . $e107BootKnownBad)
		|| is_dir(e107::getFolder('system_base') . $e107BootKnownBad),
)), "\n";
