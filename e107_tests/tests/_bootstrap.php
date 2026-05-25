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

// Phase 1: create a disposable worktree if git is available.
// The worktree captures the current dirty state so tests run in
// an isolated copy, leaving the main tree untouched.
require_once(codecept_root_dir() . "/lib/preparers/PreparerFactory.php");
$preparer = PreparerFactory::createForPath($original_app_path);
$effective_app_path = $original_app_path;
if ($preparer instanceof GitPreparer)
{
	$preparer->snapshot();
	$effective_app_path = $preparer->getWorktreePath();
}

// Phase 2: APP_PATH points to the worktree (or original if no
// worktree was created). All subsequent code uses this path.
define('APP_PATH', $effective_app_path);
define('PARAMS_SERIALIZED', serialize($params));
