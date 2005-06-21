<?
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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/e_linkgen.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-21 22:46:38 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

// Usage: sublink_type[x]['title'].
//  x should be the same as the plugin folder.

if(is_readable(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_forum_admin.php")){
	require_once(e_PLUGIN."forum/languages/".e_LANGUAGE."/lan_forum_admin.php");
}
	$sublink_type['forum']['title'] = FORLAN_155; // "News Categories";
	$sublink_type['forum']['table'] = "forum";
	$sublink_type['forum']['query'] = "forum_parent !='0' ORDER BY forum_order ASC";
    $sublink_type['forum']['url'] = $PLUGINS_DIRECTORY."forum/forum_viewforum.php?#";
	$sublink_type['forum']['fieldid'] = "forum_id";
	$sublink_type['forum']['fieldname'] = "forum_name";
	$sublink_type['forum']['fielddiz'] = "forum_description";


?>

