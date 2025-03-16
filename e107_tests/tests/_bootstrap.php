<?php

use Codeception\Util\Autoload;
use Codeception\Configuration;

Autoload::addNamespace('', codecept_root_dir() . '/tests/unit');

define('PARAMS_GENERATOR', realpath(codecept_root_dir() . "/lib/config.php"));
$params = include(PARAMS_GENERATOR);

$app_path = $params['app_path'] ?: codecept_root_dir() . "/e107";
if(substr($app_path, 0, 1) !== '/')
{
	$app_path = codecept_root_dir() . "/{$app_path}";
}
define('APP_PATH', realpath($app_path));
define('PARAMS_SERIALIZED', serialize($params));
// define('e_PLUGIN', APP_PATH . '/e107_plugins/');

$pluginsDir = realpath(codecept_root_dir() . '/../e107_plugins/');
$pluginTestDirs = [];

$logFile = codecept_output_dir() . '/bootstrap.log';
file_put_contents($logFile, '');
file_put_contents($logFile, "Time: " . date(DATE_ATOM) . "\n", FILE_APPEND);
file_put_contents($logFile, "Root Dir: " . codecept_root_dir() . "\n", FILE_APPEND);
file_put_contents($logFile, "Plugins Dir: $pluginsDir\n", FILE_APPEND);

if($pluginsDir && is_dir($pluginsDir))
{
	$dirs = glob($pluginsDir . '/*/tests', GLOB_ONLYDIR);
	file_put_contents($logFile, "Glob Pattern: $pluginsDir/*/tests\n", FILE_APPEND);
	file_put_contents($logFile, "Found Dirs: " . (empty($dirs) ? 'None' : implode(', ', $dirs)) . "\n", FILE_APPEND);
	foreach($dirs as $testDir)
	{
		$pluginName = basename(dirname($testDir));
		$relativePath = '../e107_plugins/' . $pluginName . '/tests';
		$pluginTestDirs[] = $relativePath; // Just paths, no array
	}
}
else
{
	file_put_contents($logFile, "Plugins Dir not found or not a directory\n", FILE_APPEND);
}
file_put_contents($logFile, "Included: " . json_encode($pluginTestDirs, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

if(!empty($pluginTestDirs))
{
	Configuration::append(['include' => $pluginTestDirs]);
	codecept_debug("Dynamic includes added: " . json_encode($pluginTestDirs));
}
else
{
	codecept_debug("No plugin test directories found");
}

include(codecept_root_dir() . "/lib/PriorityCallbacks.php");