#!/usr/bin/env php
<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Handles incoming requests to fire off regularly-scheduled tasks (cron jobs).
 *
 * @example
 * Using wget:
 *   /usr/bin/wget -O - -q http://example.com/cron.php?token=TOKEN > /dev/null 2>&1
 * Using curl:
 *   /usr/bin/curl --silent --compressed http://example.com/cron.php?token=TOKEN > /dev/null 2>&1
 * Using lynx:
 *   /usr/bin/lynx -source http://example.com/cron.php?token=TOKEN > /dev/null 2>&1
 * Using PHP:
 *   /usr/bin/php -q /var/www/example.com/cron.php token=TOKEN
 *   /usr/bin/php -q /var/www/example.com/cron.php TOKEN
 * Using as Shell script:
 *   /var/www/example.com/cron.php token=TOKEN
 *   /var/www/example.com/cron.php TOKEN
 */

$_E107['cli'] = true;
$_E107['debug'] = false;
$_E107['no_online'] = true;
$_E107['no_forceuserupdate'] = true;
$_E107['no_menus'] = true;
$_E107['allow_guest'] = true; // allow crons to run while in members-only mode.
$_E107['no_maintenance'] = true;

if ((PHP_SAPI === "apache" || PHP_SAPI === "litespeed"))
{
	if($_E107['debug'])
	{
		error_log("e107: cron.php was blocked from executing with PHP_SAPI: ".php_sapi_name());
	}
	echo "<h1>Access Denied</h1>";
    exit;
}

require_once(realpath(__DIR__ . "/class2.php"));

if(!empty($_E107['debug']))
{
	error_reporting(E_ALL);
}

require_once(e_HANDLER . "cron_class.php");

$cron = new cronScheduler();
$cron->run();
