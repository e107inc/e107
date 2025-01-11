<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/comment_menu/comment_menu_template.php,v $
|     $Revision: 11346 $
|     $Date: 2010-02-17 13:56:14 -0500 (Wed, 17 Feb 2010) $
|     $Author: secretr $
+----------------------------------------------------------------------------+
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
	{CM_AUTHOR}<br /><br />
	";
}
?>