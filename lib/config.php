<?php

use Symfony\Component\Yaml\Yaml;

$params_sample = [];
$params = [];
$params_local = [];

if (file_exists(codecept_root_dir() . '/config.sample.yml'))
        $params_sample = Yaml::parse(file_get_contents(codecept_root_dir() . '/config.sample.yml'));
if (file_exists(codecept_root_dir() . '/config.yml'))
        $params = Yaml::parse(file_get_contents(codecept_root_dir() . '/config.yml'));
if (file_exists(codecept_root_dir() . '/config.local.yml'))
        $params_local = Yaml::parse(file_get_contents(codecept_root_dir() . '/config.local.yml'));

$params_merged = array_merge($params_sample, $params, $params_local);

return $params_merged;
