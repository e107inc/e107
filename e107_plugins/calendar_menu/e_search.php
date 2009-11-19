<?php
/* $Id: e_search.php,v 1.3 2009-11-19 11:45:49 marj_nl_fr Exp $ */
if (!defined('e107_INIT')) { exit(); }

include_lan(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE."_search.php");

$search_info[] = array('sfile' => e_PLUGIN.'calendar_menu/search/search_parser.php', 'qtype' => CM_SCH_LAN_1, 'refpage' => 'calendar.php');

?>