<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/default/content_search_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

global $sc_style, $content_shortcodes;

$sc_style['CONTENT_SEARCH_TABLE_SELECT']['pre'] = "<td style='width:10%; white-space:nowrap; padding-right:10px;'>";
$sc_style['CONTENT_SEARCH_TABLE_SELECT']['post'] = "</td>";

$sc_style['CONTENT_SEARCH_TABLE_ORDER']['pre'] = "<td style='width:10%; white-space:nowrap; padding-right:10px;'>";
$sc_style['CONTENT_SEARCH_TABLE_ORDER']['post'] = "</td>";

$sc_style['CONTENT_SEARCH_TABLE_KEYWORD']['pre'] = "<td>";
$sc_style['CONTENT_SEARCH_TABLE_KEYWORD']['post'] = "</td>";

// ##### CONTENT SEARCH LIST --------------------------------------------------
if(!isset($CONTENT_SEARCH_TABLE)){
	$CONTENT_SEARCH_TABLE = "
	<table style='width:98%; text-align:left;' border='0'>
	<tr>
	{CONTENT_SEARCH_TABLE_SELECT}
	{CONTENT_SEARCH_TABLE_ORDER}
	{CONTENT_SEARCH_TABLE_KEYWORD}
	</tr>
	</table><br />";
}
// ##### ----------------------------------------------------------------------

?>