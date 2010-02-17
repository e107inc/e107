<?php
/* $Id$ */
if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content_search.php");

$search_info[] = array( 'sfile' => e_PLUGIN.'content/search/search_parser.php', 'qtype' => CONT_SCH_LAN_1, 'refpage' => 'content.php', 'advanced' => e_PLUGIN.'content/search/search_advanced.php');

