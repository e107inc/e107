<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Loads one e107 entry script in a process of its own, for scriptsTest.
 *
 * The process is the verdict. PHP diagnostics go to stderr, so the caller
 * judges on the exit status and stderr alone and never has to pick error text
 * out of the page the script rendered. Warnings and notices are the point:
 * on the CLI SAPI they leave the exit status at 0 and write nothing to stderr
 * unless display_errors says stderr, which is why the sweep that inspected
 * output only after a non-zero exit could never see one.
 *
 * exit() is a pass. It is how an e107 entry script finishes, and with one
 * script per process it can no longer hide the scripts after it. What is not
 * a pass is never reaching the script at all, which the shutdown handler
 * reports because a silent no-op otherwise looks identical to a clean run.
 *
 * Usage: php probe.php <appPath> <targetScript> [--remote-addr=IP]
 *                      [--admin-area] [--admin-header]
 *
 * --remote-addr gives the child an address of its own. e107 flood-controls by
 * IP and bans at around a hundred hits, and every child otherwise presents the
 * same empty CLI REMOTE_ADDR, so a sweep of this size bans itself and takes
 * the rest of the suite with it. One script is one visitor.
 *
 * --admin-area defines ADMIN_AREA, which e107::inAdminDir() reads, before the
 * bootstrap. Without it e107 decides the admin area from the running script's
 * own path, that path is this file, and the admin language file never loads,
 * so every admin script dies on an undefined LAN_ constant. --admin-header
 * additionally loads e107_admin/admin.php, which the includes/ and layouts/
 * fragments are written to be included by.
 *
 * Keep this file in PHP 5.6 syntax: e107_tests/tests is inside the floor
 * lint's sweep, and release/v2.3.x runs its suite on php:5.6.
 */

if(!isset($argv) || count($argv) < 3)
{
	fwrite(STDERR, "probe: usage: probe.php <appPath> <targetScript> [--remote-addr=IP] [--admin-area] [--admin-header]\n");
	exit(2);
}

$e107ProbeClass2      = rtrim($argv[1], '/\\') . '/class2.php';
$e107ProbeTarget      = realpath($argv[2]);
$e107ProbeFlags       = array_slice($argv, 3);
$e107ProbeAdminArea   = in_array('--admin-area', $e107ProbeFlags, true);
$e107ProbeAdminHeader = in_array('--admin-header', $e107ProbeFlags, true);
$e107ProbeRemoteAddr  = '';

foreach($e107ProbeFlags as $e107ProbeFlag)
{
	if(strpos($e107ProbeFlag, '--remote-addr=') === 0)
	{
		$e107ProbeRemoteAddr = (string) substr($e107ProbeFlag, strlen('--remote-addr='));
	}
}

if(!is_file($e107ProbeClass2))
{
	fwrite(STDERR, "probe: no class2.php under " . $argv[1] . "\n");
	exit(2);
}

if($e107ProbeTarget === false || !is_file($e107ProbeTarget))
{
	fwrite(STDERR, "probe: no script at " . $argv[2] . "\n");
	exit(2);
}

$e107ProbeReached = false;

register_shutdown_function('e107ProbeReportUnreached');

function e107ProbeReportUnreached()
{
	if(!empty($GLOBALS['e107ProbeReached']))
	{
		return;
	}

	fwrite(STDERR, "probe: the run ended before reaching " . $GLOBALS['e107ProbeTarget'] . "\n");

	$fatal = error_get_last();
	$types = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;

	if($fatal !== null && ($fatal['type'] & $types))
	{
		fwrite(STDERR, sprintf("probe: %s in %s on line %d\n", $fatal['message'], $fatal['file'], $fatal['line']));
	}
}

// Deliberately without $_E107['cli']: that flag suppresses the output buffer
// class2.php starts for a browser, and error.php sends its status header
// after rendering the page header, which is legal with the buffer and a
// warning without it. The sweep is for what a browser hits.
$_E107 = array();

// The bootstrap reads both of these off the running script, which is this
// file, so they have to say the script under test instead.
$_SERVER['SCRIPT_FILENAME'] = $e107ProbeTarget;
$_SERVER['SCRIPT_NAME']     = $e107ProbeTarget;

if($e107ProbeRemoteAddr !== '')
{
	$_SERVER['REMOTE_ADDR'] = $e107ProbeRemoteAddr;
}

if($e107ProbeAdminArea)
{
	define('ADMIN_AREA', true);
}

// What the bootstrap raises belongs to the bootstrap. It is identical for
// every script in the sweep, and class2.php resets error_reporting itself,
// so silencing the display is the only thing that holds. A bootstrap that
// dies is still reported, by the shutdown handler above.
ini_set('display_errors', 0);

require_once $e107ProbeClass2;

$e107ProbeBootstrapped = get_included_files();

// class2.php installs a handler that would swallow what this exists to catch,
// and leaves error_reporting at E_ERROR | E_PARSE.
restore_error_handler();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 'stderr');
ini_set('log_errors', 0);
ini_set('html_errors', 0);

if(!defined('SEP'))
{
	define('SEP', " <span class='fa fa-angle-double-right e-breadcrumb'></span> ");
}

e107::loadAdminIcons();

foreach(array('banner', 'page', 'gsitemap') as $e107ProbePlugin)
{
	e107::getConfig()->setPref('plug_installed/' . $e107ProbePlugin, '1.0');
}

$pref = e107::getPref();
$ns   = e107::getRender();
$tp   = e107::getParser();
$frm  = e107::getForm();

if($e107ProbeAdminHeader)
{
	require_once e_ADMIN . 'admin.php';
}

if(in_array($e107ProbeTarget, $e107ProbeBootstrapped, true))
{
	fwrite(STDERR, "probe: " . $e107ProbeTarget . " was already loaded by the bootstrap, so nothing was tested\n");
	exit(3);
}

$e107ProbeReached = true;

// admin.php pulls in whichever admin style the prefs select, so some of the
// fragments under includes/ are already loaded by the time the sweep reaches
// them. They ran, under the header they are written for, which is the whole
// of what loading them separately would prove.
if(in_array($e107ProbeTarget, get_included_files(), true))
{
	echo "probe: loaded by e107_admin/admin.php, which is how a browser reaches it\n";
	exit(0);
}

require_once $e107ProbeTarget;
