<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
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