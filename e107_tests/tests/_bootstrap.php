<?php
Codeception\Util\Autoload::addNamespace('', codecept_root_dir().'/tests/unit');

define('PARAMS_GENERATOR', realpath(codecept_root_dir()."/lib/config.php"));

$params = include(PARAMS_GENERATOR);

$app_path = $params['app_path'] ?: codecept_root_dir()."/e107";

// Relative path
if (substr($app_path, 0, 1) !== '/')
	$app_path = codecept_root_dir() . "/{$app_path}";

$original_app_path = realpath($app_path);

// Provide a way to register callbacks that execute before Codeception's
include(codecept_root_dir()."/lib/PriorityCallbacks.php");

// Ask the preparer where the app runs: in place, or in an isolated
// git worktree for deploy-based suites. The factory decides which.
// It matters for acceptance: the local deployer serves the app from
// the source tree, so writes routed through it must land there and
// not in a snapshot the web server never sees.
require_once(codecept_root_dir() . "/lib/preparers/PreparerFactory.php");
$deployer = isset($params['deployer']) ? $params['deployer'] : 'local';
$preparer = PreparerFactory::createForPath($original_app_path, $deployer);
$effective_app_path = $preparer->getAppPath();

// APP_PATH points to the prepared tree; all later code uses it.
define('APP_PATH', $effective_app_path);
define('PARAMS_SERIALIZED', serialize($params));
