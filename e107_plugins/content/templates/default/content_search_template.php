<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
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