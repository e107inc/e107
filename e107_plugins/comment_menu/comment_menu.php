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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/comment_menu/comment_menu.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_PLUGIN."comment_menu/comment_menu_shortcodes.php");
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;

if (file_exists(THEME."comment_menu_template.php")){
	require_once(THEME."comment_menu_template.php");
}else{
	require_once(e_PLUGIN."comment_menu/comment_menu_template.php");
}

$data = $cobj->getCommentData(intval($menu_pref['comment_display']));

$text = '';
// no posts yet ..
if(empty($data) || !is_array($data)){
	$text = CM_L1;
}

global $row;
foreach($data as $row){
	$text .= $tp->parseTemplate($COMMENT_MENU_TEMPLATE, true, $comment_menu_shortcodes);
}

$ns->tablerender($menu_pref['comment_caption'], $text, 'comment');

?>