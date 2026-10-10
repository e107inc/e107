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

// Set by the suite runner (lib/sandbox) for each process it starts: the
// sandbox's URL, app path and database. The last layer, so it wins.
$sandbox = getenv('E107_TEST_PARAMS');
if ($sandbox !== false && $sandbox !== '')
{
	$params = array_replace_recursive($params, json_decode($sandbox, true));
}

if (!empty($params['url']))
{
	$params['tls_url'] = preg_replace('#^http:#', 'https:', $params['url']);
}

if (!empty($params['app_path']))
{
	$params['app_path'] = rtrim($params['app_path'], '/\\') . '/';
}

return $params;
