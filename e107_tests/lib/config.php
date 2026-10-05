<?php

use Symfony\Component\Yaml\Yaml;

$params = [];

foreach ([
	'config.sample.yml',
	'config.yml',
	// Written by e107_tests/bin/e107-tests when a Docker test env is up.
	// Sits between config.yml (user/CI) and config.local.yml (personal
	// overrides) so the cascade is: sample → yml → docker → local.
	'config.docker.yml',
	'config.local.yml'
         ] as $config_filename)
{
	$absolute_config_path = codecept_root_dir() . '/' . $config_filename;
	if (file_exists($absolute_config_path))
		$params = array_replace_recursive($params, Yaml::parse(file_get_contents($absolute_config_path)));
}

if (!empty($params['app_path']))
{
	$params['app_path'] = rtrim($params['app_path'], '/\\') . '/';
}

$db = isset($params['db']) && is_array($params['db']) ? $params['db'] : [];
$db['driver'] = empty($db['driver']) ? 'mysql' : $db['driver'];

switch ($db['driver'])
{
	case 'mysql':
		$db['dsn'] = 'mysql:host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $db['dbname'];
		$db['populator'] = '';
		break;

	case 'sqlite':
		if (!in_array(isset($params['deployer']) ? $params['deployer'] : 'none', ['local', 'none'], true))
		{
			throw new InvalidArgumentException("db.driver 'sqlite' needs the 'local' deployer: the site and the suites have to share one database file, and deployer '" . $params['deployer'] . "' puts the site somewhere else.");
		}

		// Codeception reads the part after "sqlite:" relative to this directory.
		$db['dsn'] = 'sqlite:' . $db['path'];
		$db['populator'] = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/sqlite-fixture.php') . " '\$dump' '\$dsn'";
		break;

	default:
		throw new InvalidArgumentException("Unknown db.driver '" . $db['driver'] . "' in the test configuration; use 'mysql' or 'sqlite'.");
}

$db['mysql_compat'] = !empty($db['mysql_compat']);
$params['db'] = $db;

return $params;
