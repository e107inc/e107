<?php

$params = include(codecept_root_dir()."/lib/config.php");

$app_path = $params['app_path'] ?: codecept_root_dir()."/e107";

// Relative path
if (substr($app_path, 0, 1) !== '/')
	$app_path = codecept_root_dir() . "/${app_path}";

define('APP_PATH', realpath($app_path));
