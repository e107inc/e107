<?php

use Symfony\Component\Yaml\Yaml;

$params = [];

foreach ([
	'config.sample.yml',
	'config.yml',
	'config.local.yml'
         ] as $config_filename)
{
	$absolute_config_path = codecept_root_dir() . '/' . $config_filename;
	if (file_exists($absolute_config_path))
		$params = array_replace_recursive($params, Yaml::parse(file_get_contents($absolute_config_path)));
}

return $params;
