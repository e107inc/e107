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
 * https://your-e107-website.com/cron.php?token=83ab94d77c2ea6c3422146f1ea4ab2e83c43de03
 */

$_E107['cli'] = true;
$_E107['debug'] = false;
$_E107['no_online'] = true;
$_E107['no_forceuserupdate'] = true;
$_E107['no_menus'] = true;
$_E107['allow_guest'] = true; // allow crons to run while in members-only mode.
$_E107['no_maintenance'] = true;

require_once(realpath(dirname(__FILE__) . "/class2.php"));
require_once(e_HANDLER . "cron_class.php");

$cron = new cronScheduler();
$cron->run();
