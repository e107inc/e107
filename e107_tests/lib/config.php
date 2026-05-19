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

return $params;
