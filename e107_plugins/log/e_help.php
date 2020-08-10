<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	Stats logging plugin - admin help text
 *
 * $URL$
 * $Id$
 */


/**
 *	e107 Stats logging plugin
 *
 *	@package	e107_plugins
 *	@subpackage	log
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }

e107::includeLan(e_PLUGIN.'log/languages/'.e_LANGUAGE."_log_help.php");

if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';

switch ($action)
{
	case 'export' :
		$text = LAN_STAT_HELP_04;
		break;
	case 'rempage' :
		$text = LAN_STAT_HELP_03;
		break;
	case 'history' :
		$text = LAN_STAT_HELP_02;
		break;
	default :
		$text = LAN_STAT_HELP_05;
}
$ns -> tablerender(LAN_STAT_HELP_01, $text);
unset($text);
