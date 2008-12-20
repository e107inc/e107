<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin configuration module - gsitemap
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/gsitemap/e_cron.php,v $
 * $Revision: 1.2 $
 * $Date: 2008-12-20 21:48:06 $
 * $Author: e107steved $
 *
*/

if (!defined('e107_INIT')) { exit; }
if(!plugInstalled('gsitemap'))
{ 
	return;
}

// -------- e_cron setup -----------------------------------------------------

$cron['name'] = "Update Records";
$cron['function'] = "gsitemap_myfunction";
$cron['description'] = "Dummy example.";

$cron2['name'] = "Test Email";
$cron2['function'] = "gsitemap_myfunction2";
$cron2['description'] = "Sends a test email to ".$pref['siteadminemail'];

$eplug_cron[] = $cron;
$eplug_cron[] = $cron2;

// ------------------------- Functions -----------------------------------------

function gsitemap_myfunction() // include plugin-folder in the function name.
{
    // Whatever code you wish.
}



function gsitemap_myfunction2()
{
    require_once(e_HANDLER."mail.php");
	$message = "Your Cron Job worked correctly. Sent at ".date("r").".";
    sendemail($pref['siteadminemail'], "e107 - TEST Email Sent by cron.", $message, $pref['siteadmin'],$pref['siteadminemail'], $pref['siteadmin']);
}




?>