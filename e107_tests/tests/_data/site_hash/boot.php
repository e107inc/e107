<?php
/**
 * Boot e107's core without class2.php and without the site_path pin, and
 * report the folder it derives for the site. e107SiteHashBootTest runs this
 * in a subprocess because the suite's own instance is initialised once and
 * {@see e107::_init()} returns early after that.
 *
 * Usage: define E107_BOOT_APP (the app root) and E107_BOOT_SHAPE before
 * requiring this file. The shape is 'legacy' for the mySQL-prefixed array the
 * old e107_config.php format gives class2.php, or 'stripped' for the bare keys
 * class2.php builds from it on this branch. Define E107_BOOT_SERVER to replace
 * the server setting the config holds. Either config format on disk is read.
 * Prints one JSON line on stdout.
 */

if(!defined('E107_BOOT_APP') || !defined('E107_BOOT_SHAPE'))
{
	fwrite(STDERR, "boot: define E107_BOOT_APP and E107_BOOT_SHAPE before requiring boot.php\n");
	exit(2);
}

$e107BootApp = rtrim(E107_BOOT_APP, '/\\') . '/';
chdir($e107BootApp);

define('e107_INIT', true);
define('e_ROOT', $e107BootApp);

ob_start();
$e107BootConfig = include $e107BootApp . 'e107_config.php';
ob_end_clean();

if(is_array($e107BootConfig) && !empty($e107BootConfig['database']))
{
	$e107BootPaths = $e107BootConfig['paths'];
	$e107BootHandlers = !empty($e107BootPaths['handlers']) ? $e107BootPaths['handlers'] : 'e107_handlers/';
	$E107_CONFIG = isset($e107BootConfig['other']) ? $e107BootConfig['other'] : array();
	$e107BootLegacy = array(
		'mySQLserver' => $e107BootConfig['database']['server'],
		'mySQLuser' => $e107BootConfig['database']['user'],
		'mySQLpassword' => $e107BootConfig['database']['password'],
		'mySQLdefaultdb' => $e107BootConfig['database']['db'],
		'mySQLprefix' => $e107BootConfig['database']['prefix'],
	);
}
else
{
	$e107BootHandlers = !empty($HANDLERS_DIRECTORY) ? $HANDLERS_DIRECTORY : 'e107_handlers/';
	$e107BootLegacy = compact('mySQLserver', 'mySQLuser', 'mySQLpassword', 'mySQLdefaultdb', 'mySQLprefix');
	$e107BootPaths = null;
}

require $e107BootApp . $e107BootHandlers . 'core_functions.php';
require $e107BootApp . $e107BootHandlers . 'e107_class.php';

if($e107BootPaths === null)
{
	$e107BootPaths = array();
	foreach(array_keys(e107::getInstance()->overridableDirs()) as $e107BootName)
	{
		if(isset($$e107BootName))
		{
			$e107BootPaths[$e107BootName] = $$e107BootName;
		}
	}
}

unset($E107_CONFIG['site_path']);

if(defined('E107_BOOT_SERVER'))
{
	$e107BootLegacy['mySQLserver'] = E107_BOOT_SERVER;
}

$e107BootSql = $e107BootLegacy;

if(E107_BOOT_SHAPE === 'stripped')
{
	$e107BootSql = array_combine(array_map(function($k)
	{
		return str_replace('mySQL', '', $k);
	}, array_keys($e107BootLegacy)), $e107BootLegacy);
	$e107BootSql['db'] = $e107BootSql['defaultdb'];
}

$e107BootKnownBad = e107::getInstance()->makeSiteHash('', '');
$e107BootKnownBadExisted = is_dir($e107BootApp . 'e107_media/' . $e107BootKnownBad)
	|| is_dir($e107BootApp . 'e107_system/' . $e107BootKnownBad);

$e107Boot = e107::getInstance()->initCore($e107BootPaths, e_ROOT, $e107BootSql, varset($E107_CONFIG, array()));

echo json_encode(array(
	'site_path' => $e107Boot->site_path,
	'expected' => $e107Boot->makeSiteHash($e107BootLegacy['mySQLdefaultdb'], $e107BootLegacy['mySQLprefix']),
	'known_bad' => $e107BootKnownBad,
	'media' => e107::getFolder('media'),
	'system' => e107::getFolder('system'),
	'known_bad_existed' => $e107BootKnownBadExisted,
	'known_bad_exists' => is_dir(e107::getFolder('media_base') . $e107BootKnownBad)
		|| is_dir(e107::getFolder('system_base') . $e107BootKnownBad),
)), "\n";
