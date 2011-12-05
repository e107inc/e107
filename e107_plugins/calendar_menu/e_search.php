<?php
/*
 * e107 website system
 *
 * Copyright (C) 2002-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Event calendar - search shim
 *
 * $URL$
 * $Id$
 */
if (!defined('e107_INIT')) { exit(); }

include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'_search.php');

$search_info[] = array('sfile' => e_PLUGIN.'calendar_menu/search/search_parser.php', 'qtype' => CM_SCH_LAN_1, 'refpage' => 'calendar.php');

?>
