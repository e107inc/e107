<?php
Codeception\Util\Autoload::addNamespace('', codecept_root_dir().'/tests/unit');

define('PARAMS_GENERATOR', realpath(codecept_root_dir()."/lib/config.php"));

$params = include(PARAMS_GENERATOR);

$app_path = $params['app_path'] ?: codecept_root_dir()."/e107";

// Relative path
if (substr($app_path, 0, 1) !== '/')
	$app_path = codecept_root_dir() . "/${app_path}";

define('APP_PATH', realpath($app_path));
define('PARAMS_SERIALIZED', serialize($params));

// Provide a way to register callbacks that execute before Codeception's
include(codecept_root_dir()."/lib/PriorityCallbacks.php");
