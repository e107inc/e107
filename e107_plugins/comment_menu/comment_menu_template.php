<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Comment menu default template
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/comment_menu/comment_menu_template.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-18 01:05:28 $
 * $Author: e107coders $
*/

$sc_style['CM_TYPE']['pre'] = "[";
$sc_style['CM_TYPE']['post'] = "]";

$sc_style['CM_AUTHOR']['pre'] = CM_L13." <b>";
$sc_style['CM_AUTHOR']['post'] = "</b>";

$sc_style['CM_DATESTAMP']['pre'] = " ".CM_L11." ";
$sc_style['CM_DATESTAMP']['post'] = "";

$sc_style['CM_COMMENT']['pre'] = "";
$sc_style['CM_COMMENT']['post'] = "<br /><br />";

if (!isset($COMMENT_MENU_TEMPLATE)){
	$COMMENT_MENU_TEMPLATE = "
	{CM_ICON} {CM_URL_PRE}{CM_TYPE} {CM_HEADING}{CM_URL_POST}<br />
	{CM_AUTHOR} {CM_DATESTAMP}<br />
	{CM_COMMENT}";
}
?>