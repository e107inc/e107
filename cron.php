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
 * Command line only. A request that arrives through a web server is refused,
 * whichever SAPI that server uses. The test is the request, not the SAPI name:
 * on a great deal of shared hosting the command line binary is a CGI build, and
 * a crontab line calling it is a shell invocation like any other.
 *
 * @example
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

// A web server states the request in the environment it hands PHP, and a shell
// states none of it, whichever binary the shell is running.
$viaHttp = PHP_SAPI === "cli-server"
	|| !empty($_SERVER['REQUEST_METHOD'])
	|| !empty($_SERVER['HTTP_HOST'])
	|| !empty($_SERVER['SERVER_PROTOCOL']);

if ($viaHttp)
{
	error_log("e107: cron.php refused a request that arrived over HTTP (PHP_SAPI: ".PHP_SAPI."); cron.php is command line only");
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
