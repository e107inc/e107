<?php
use Codeception\Util\Autoload;
use Codeception\Configuration;

// Load unit tests namespace
Autoload::addNamespace('', codecept_root_dir() . '/tests/unit');

// Load parameters
define('PARAMS_GENERATOR', realpath(codecept_root_dir() . "/lib/config.php"));
$params = include(PARAMS_GENERATOR);

// Define APP_PATH
$app_path = $params['app_path'] ?: codecept_root_dir() . "/e107";
if (substr($app_path, 0, 1) !== '/') {
    $app_path = codecept_root_dir() . "/{$app_path}";
}
define('APP_PATH', realpath($app_path));
define('PARAMS_SERIALIZED', serialize($params));

// Debug logging
$logFile = codecept_output_dir() . '/bootstrap.log';
file_put_contents($logFile, '');
file_put_contents($logFile, "Time: " . date(DATE_ATOM) . "\n", FILE_APPEND);
file_put_contents($logFile, "Root Dir: " . codecept_root_dir() . "\n", FILE_APPEND);
file_put_contents($logFile, "App Path: " . APP_PATH . "\n", FILE_APPEND);
if (defined('e_PLUGIN')) {
    file_put_contents($logFile, "e_PLUGIN already defined as: " . e_PLUGIN . "\n", FILE_APPEND);
} else {
    file_put_contents($logFile, "e_PLUGIN not defined yet\n", FILE_APPEND);
    // Let e107 define e_PLUGIN later; avoid redefinition
    // define('e_PLUGIN', APP_PATH . '/e107_plugins/');
}

// Dynamic plugin test autoloading
$pluginsDir = realpath(codecept_root_dir() . '/../e107_plugins/');
$pluginTestDirs = [];

file_put_contents($logFile, "Plugins Dir: $pluginsDir\n", FILE_APPEND);

if ($pluginsDir && is_dir($pluginsDir)) {
    $dirs = glob($pluginsDir . '/*/tests', GLOB_ONLYDIR);
    file_put_contents($logFile, "Glob Pattern: $pluginsDir/*/tests\n", FILE_APPEND);
    file_put_contents($logFile, "Found Dirs: " . (empty($dirs) ? 'None' : implode(', ', $dirs)) . "\n", FILE_APPEND);
    foreach ($dirs as $testDir) {
        $pluginName = basename(dirname($testDir));
        $relativePath = '../e107_plugins/' . $pluginName . '/tests';
        $pluginTestDirs[] = $relativePath;
        Autoload::addNamespace("Tests\\" . ucfirst($pluginName), $testDir);
        file_put_contents($logFile, "Added namespace: Tests\\" . ucfirst($pluginName) . " => $testDir\n", FILE_APPEND);
    }
} else {
    file_put_contents($logFile, "Plugins Dir not found or not a directory\n", FILE_APPEND);
}
file_put_contents($logFile, "Included: " . json_encode($pluginTestDirs, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
file_put_contents($logFile, "e_PLUGIN after scan: " . (defined('e_PLUGIN') ? e_PLUGIN : 'not defined') . "\n", FILE_APPEND);

// Skip Configuration::append to avoid config errors
// if (!empty($pluginTestDirs)) {
//     Configuration::append(['include' => $pluginTestDirs]);
//     codecept_debug("Dynamic includes added: " . json_encode($pluginTestDirs));
// } else {
//     codecept_debug("No plugin test directories found");
// }

// Include required e107 file
include(codecept_root_dir() . "/lib/PriorityCallbacks.php");