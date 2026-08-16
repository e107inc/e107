#!/usr/bin/env php
<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Runs the scheduled tasks (Admin > Schedule Tasks) that are due.
 *
 * Call it once a minute, from the command line or over HTTP. Either way the
 * call carries the token shown in Admin > Schedule Tasks > Setup, and that
 * page also writes out ready-to-paste commands for the server it is running on.
 *
 * Over HTTP (runs under the PHP version selected for the site):
 *   curl -fsS -o /dev/null 'https://example.com/cron.php?token=TOKEN'
 *   wget -qO /dev/null 'https://example.com/cron.php?token=TOKEN'
 *   or any URL-based cron service.
 *
 * From the command line (runs under whatever php the shell finds):
 *   /usr/bin/php -q /var/www/example.com/cron.php token=TOKEN
 *   /var/www/example.com/cron.php token=TOKEN      (chmod 755 first)
 *
 * Over HTTP the response is text/plain: 200 "OK" when the token was accepted,
 * 403 when it was missing or wrong (under Apache's mod_php with
 * output_buffering off the shebang line above has already sent a 200 by the
 * time PHP runs; the body still says why). From the command line the exit
 * status is 0 when accepted and 1 when refused, and nothing is printed either
 * way.
 *
 * Tasks run as a guest over HTTP and as the first administrator from the
 * command line, so a task must not depend on who is running it. Use the URL
 * the Setup page shows (the site URL, not localhost or an IP), one caller
 * only or due tasks run twice, and https, because a token in a URL is written
 * to access logs and proxies. Regenerate the token in Admin > Schedule Tasks
 * if it may have leaked. Long tasks are still subject to the web server's and
 * any proxy's time limits when run over HTTP.
 *
 * A site that only ever calls this from the command line can keep it off the
 * web at the web server: Apache `RewriteRule ^cron\.php$ - [F,L]` in .htaccess
 * (not a <Files> block, which also matches e107_admin/cron.php and logs every
 * hit at error level), nginx `location = /cron.php { deny all; }`.
 */

$viaHttp = PHP_SAPI === "cli-server"
	|| !empty($_SERVER['REQUEST_METHOD'])
	|| !empty($_SERVER['HTTP_HOST'])
	|| !empty($_SERVER['SERVER_PROTOCOL']);

if($viaHttp)
{
	ignore_user_abort(true);
	@set_time_limit(0);
}
else
{
	$_E107['cli'] = true;
}

$_E107['debug'] = false;
$_E107['no_online'] = true;
$_E107['no_forceuserupdate'] = true;
$_E107['no_menus'] = true;
$_E107['allow_guest'] = true;
$_E107['no_maintenance'] = true;

require_once(realpath(__DIR__ . "/class2.php"));
require_once(e_HANDLER . "cron_class.php");

$cron = new cronScheduler();
$ran = $cron->run($viaHttp ? cronScheduler::VIA_HTTP : cronScheduler::VIA_CLI);

if($viaHttp)
{
	$cron->sendHttpResponse($ran);
}
elseif(!$ran)
{
	exit(1);
}
