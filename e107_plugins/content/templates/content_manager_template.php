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
|     $Revision: 1.2 $
|     $Date: 2007-03-13 16:51:05 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
global $sc_style, $content_shortcodes;

$sc_style['CONTENT_CONTENTMANAGER_ICONEDIT']['pre'] = " | ";
$sc_style['CONTENT_CONTENTMANAGER_ICONEDIT']['post'] = "";

$sc_style['CONTENT_CONTENTMANAGER_ICONSUBM']['pre'] = " | ";
$sc_style['CONTENT_CONTENTMANAGER_ICONSUBM']['post'] = "";

$sc_style['CONTENT_CONTENTMANAGER_CATEGORY_SUBHEADING']['pre'] = "<div class='forumheader3'>";
$sc_style['CONTENT_CONTENTMANAGER_CATEGORY_SUBHEADING']['post'] = "</div>";

// ##### CONTENT CONTENTMANAGER LIST --------------------------------------------------
if(!isset($CONTENT_CONTENTMANAGER_TABLE_START)){
	$CONTENT_CONTENTMANAGER_TABLE_START = "";
}
if(!isset($CONTENT_CONTENTMANAGER_TABLE)){
	$CONTENT_CONTENTMANAGER_TABLE = "
	<div class='fcaption'>{CONTENT_CONTENTMANAGER_CATEGORY}</div>
	{CONTENT_CONTENTMANAGER_CATEGORY_SUBHEADING}
	<div class='forumheader3' style='margin-bottom:20px;'>
		{CONTENT_CONTENTMANAGER_ICONNEW} {CONTENT_CONTENTMANAGER_ICONEDIT} {CONTENT_CONTENTMANAGER_ICONSUBM}
	</div>";
}

if(!isset($CONTENT_CONTENTMANAGER_TABLE_END)){
	$CONTENT_CONTENTMANAGER_TABLE_END = "";
}
// ##### ----------------------------------------------------------------------

?>