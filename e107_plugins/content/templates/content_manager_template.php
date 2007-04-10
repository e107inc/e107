<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/content/templates/content_manager_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-04-10 14:34:39 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
global $sc_style, $content_shortcodes;

$sc_style['CM_ICON|manager_edit']['pre'] = " | ";
$sc_style['CM_ICON|manager_edit']['post'] = "";

$sc_style['CM_ICON|manager_submit']['pre'] = " | ";
$sc_style['CM_ICON|manager_submit']['post'] = "";

$sc_style['CM_SUBHEADING|manager']['pre'] = "<div class='forumheader3'>";
$sc_style['CM_SUBHEADING|manager']['post'] = "</div>";

// ##### CONTENT CONTENTMANAGER LIST --------------------------------------------------
if(!isset($CONTENT_CONTENTMANAGER_TABLE_START)){
	$CONTENT_CONTENTMANAGER_TABLE_START = "";
}
if(!isset($CONTENT_CONTENTMANAGER_TABLE)){
	$CONTENT_CONTENTMANAGER_TABLE = "
	<div class='fcaption'>{CM_HEADING|manager}</div>
	{CM_SUBHEADING|manager}
	<div class='forumheader3' style='margin-bottom:20px;'>
		{CM_ICON|manager_new} {CM_ICON|manager_edit} {CM_ICON|manager_submit}
	</div>";
}

if(!isset($CONTENT_CONTENTMANAGER_TABLE_END)){
	$CONTENT_CONTENTMANAGER_TABLE_END = "";
}
// ##### ----------------------------------------------------------------------

?>